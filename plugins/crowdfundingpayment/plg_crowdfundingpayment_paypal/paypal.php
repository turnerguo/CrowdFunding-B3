<?php
/**
 * @package      CrowdFunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('crowdfunding.payment.plugin');

/**
 * CrowdFunding PayPal payment plugin.
 *
 * @package      CrowdFunding
 * @subpackage   Plugins
 */
class plgCrowdFundingPaymentPayPal extends CrowdFundingPaymentPlugin
{
    protected $paymentService = "paypal";

    protected $textPrefix = "PLG_CROWDFUNDINGPAYMENT_PAYPAL";
    protected $debugType = "PAYPAL_PAYMENT_PLUGIN_DEBUG";

    /**
     * This method prepares a payment gateway - buttons, forms,...
     * That gateway will be displayed on the summary page as a payment option.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param object    $item    A project data.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return string
     */
    public function onProjectPayment($context, &$item, &$params)
    {
        if (strcmp("com_crowdfunding.payment", $context) != 0) {
            return null;
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }

        // This is a URI path to the plugin folder
        $pluginURI = "plugins/crowdfundingpayment/paypal";

        $notifyUrl = $this->getNotifyUrl();
        $returnUrl = $this->getReturnUrl($item->slug, $item->catslug);
        $cancelUrl = $this->getCancelUrl($item->slug, $item->catslug);

        $html   = array();
        $html[] = '<div class="well">';

        $html[] = '<h4><img src="' . $pluginURI . '/images/paypal_icon.png" width="36" height="32" alt="PayPal" />' . JText::_($this->textPrefix . "_TITLE") . '</h4>';

        // Prepare payment receiver.
        $paymentReceiverOption = $this->params->get("paypal_payment_receiver", "site_owner");
        $paymentReceiverInput = $this->preparePaymentReceiver($paymentReceiverOption, $item->id);
        if (is_null($paymentReceiverInput)) {
            $html[] = $this->generateSystemMessage(JText::_($this->textPrefix . "_ERROR_PAYMENT_RECEIVER_MISSING"));
            return implode("\n", $html);
        }

        // Display additional information.
        $html[] = '<p>' . JText::_($this->textPrefix . "_INFO") . '</p>';

        // Start the form.
        if ($this->params->get('paypal_sandbox', 1)) {
            $html[] = '<form action="' . JString::trim($this->params->get('paypal_sandbox_url')) . '" method="post">';
        } else {
            $html[] = '<form action="' . JString::trim($this->params->get('paypal_url')) . '" method="post">';
        }

        $html[] = $paymentReceiverInput;

        $html[] = '<input type="hidden" name="cmd" value="_xclick" />';
        $html[] = '<input type="hidden" name="charset" value="utf-8" />';
        $html[] = '<input type="hidden" name="currency_code" value="' . $item->currencyCode . '" />';
        $html[] = '<input type="hidden" name="amount" value="' . $item->amount . '" />';
        $html[] = '<input type="hidden" name="quantity" value="1" />';
        $html[] = '<input type="hidden" name="no_shipping" value="1" />';
        $html[] = '<input type="hidden" name="no_note" value="1" />';
        $html[] = '<input type="hidden" name="tax" value="0" />';

        // Title
        $title  = JText::sprintf($this->textPrefix . "_INVESTING_IN_S", htmlentities($item->title, ENT_QUOTES, "UTF-8"));
        $html[] = '<input type="hidden" name="item_name" value="' . $title . '" />';

        // Get intention
        $userId  = JFactory::getUser()->get("id");
        $aUserId = $app->getUserState("auser_id");

        $intention = $this->getIntention(array(
            "user_id"    => $userId,
            "auser_id"   => $aUserId,
            "project_id" => $item->id
        ));

        // Prepare custom data
        $custom = array(
            "intention_id" => $intention->getId(),
            "gateway"      => "PayPal"
        );

        $custom = base64_encode(json_encode($custom));
        $html[] = '<input type="hidden" name="custom" value="' . $custom . '" />';

        // Set a link to logo
        $imageUrl = JString::trim($this->params->get('paypal_image_url'));
        if ($imageUrl) {
            $html[] = '<input type="hidden" name="image_url" value="' . $imageUrl . '" />';
        }

        // Set URLs
        $html[] = '<input type="hidden" name="cancel_return" value="' . $cancelUrl . '" />';
        $html[] = '<input type="hidden" name="return" value="' . $returnUrl . '" />';
        $html[] = '<input type="hidden" name="notify_url" value="' . $notifyUrl . '" />';

        $this->prepareLocale($html);

        // End the form.
        $html[] = '<img alt="" border="0" width="1" height="1" src="https://www.paypal.com/en_US/i/scr/pixel.gif" >';
        $html[] = '</form>';

        // Display a sticky note if the extension works in sandbox mode.
        if ($this->params->get('paypal_sandbox', 1)) {
            $html[] = '<div class="alert alert-info"><i class="icon-info-sign"></i> ' . JText::_($this->textPrefix . "_WORKS_SANDBOX") . '</div>';
        }

        $html[] = '</div>';

        return implode("\n", $html);
    }

    /**
     * This method processes transaction data that comes from PayPal instant notifier.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return null|array
     */
    public function onPaymentNotify($context, &$params)
    {
        if (strcmp("com_crowdfunding.notify.paypal", $context) != 0) {
            return null;
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("raw", $docType) != 0) {
            return null;
        }

        // Validate request method
        $requestMethod = $app->input->getMethod();
        if (strcmp("POST", $requestMethod) != 0) {
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_REQUEST_METHOD"),
                $this->debugType,
                JText::sprintf($this->textPrefix . "_ERROR_INVALID_TRANSACTION_REQUEST_METHOD", $requestMethod)
            );

            return null;
        }

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_RESPONSE"), $this->debugType, $_POST) : null;

        // Decode custom data
        $custom = JArrayHelper::getValue($_POST, "custom");
        $custom = json_decode(base64_decode($custom), true);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_CUSTOM"), $this->debugType, $custom) : null;

        // Verify gateway. Is it PayPal?
        if (!$this->isPayPalGateway($custom)) {
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_PAYMENT_GATEWAY"),
                $this->debugType,
                array("custom" => $custom, "_POST" => $_POST)
            );

            return null;
        }

        // Get PayPal URL
        if ($this->params->get('paypal_sandbox', 1)) {
            $url = JString::trim($this->params->get('paypal_sandbox_url', "https://www.sandbox.paypal.com/cgi-bin/webscr"));
        } else {
            $url = JString::trim($this->params->get('paypal_url', "https://www.paypal.com/cgi-bin/webscr"));
        }

        jimport("itprism.payment.paypal.ipn");
        $paypalIpn       = new ITPrismPayPalIpn($url, $_POST);
        $loadCertificate = (bool)$this->params->get("paypal_load_certificate", 0);
        $paypalIpn->verify($loadCertificate);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_VERIFY_OBJECT"), $this->debugType, $paypalIpn) : null;

        // Prepare the array that have to be returned by this method.
        $result = array(
            "project"         => null,
            "reward"          => null,
            "transaction"     => null,
            "payment_session" => null,
            "payment_service" => "PayPal"
        );

        if ($paypalIpn->isVerified()) {

            // Get currency
            jimport("crowdfunding.currency");
            $currencyId = $params->get("project_currency");
            $currency   = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId);

            // Get intention data
            $intentionId = JArrayHelper::getValue($custom, "intention_id", 0, "int");

            jimport("crowdfunding.intention");
            $intention = new CrowdFundingIntention(JFactory::getDbo());
            $intention->load($intentionId);

            // Get payment session as intention.
            if (!$intention->getId()) {

                $keys = array("intention_id" => $intentionId);

                jimport("crowdfunding.payment.session");
                $intention = new CrowdFundingPaymentSession(JFactory::getDbo());
                $intention->load($keys);

            }

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_INTENTION"), $this->debugType, $intention->getProperties()) : null;

            // Validate transaction data
            $validData = $this->validateData($_POST, $currency->getAbbr(), $intention);
            if (is_null($validData)) {
                return $result;
            }

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_VALID_DATA"), $this->debugType, $validData) : null;

            // Get project.
            jimport("crowdfunding.project");
            $projectId = JArrayHelper::getValue($validData, "project_id");
            $project   = CrowdFundingProject::getInstance(JFactory::getDbo(), $projectId);

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_PROJECT_OBJECT"), $this->debugType, $project->getProperties()) : null;

            // Check for valid project
            if (!$project->getId()) {

                // Log data in the database
                $this->log->add(
                    JText::_($this->textPrefix . "_ERROR_INVALID_PROJECT"),
                    $this->debugType,
                    $validData
                );

                return $result;
            }

            // Set the receiver of funds.
            $validData["receiver_id"] = $project->getUserId();

            // Save transaction data.
            // If it is not completed, return empty results.
            // If it is complete, continue with process transaction data
            $transactionData = $this->storeTransaction($validData, $project);
            if (is_null($transactionData)) {
                return $result;
            }

            // Update the number of distributed reward.
            $rewardId = JArrayHelper::getValue($transactionData, "reward_id");
            $reward   = null;
            if (!empty($rewardId)) {
                $reward = $this->updateReward($transactionData);

                // Validate the reward.
                if (!$reward) {
                    $transactionData["reward_id"] = 0;
                }
            }


            // Generate object of data, based on the transaction properties.
            $result["transaction"] = JArrayHelper::toObject($transactionData);

            // Generate object of data based on the project properties.
            $properties        = $project->getProperties();
            $result["project"] = JArrayHelper::toObject($properties);

            // Generate object of data based on the reward properties.
            if (!empty($reward)) {
                $properties       = $reward->getProperties();
                $result["reward"] = JArrayHelper::toObject($properties);
            }

            // Generate data object, based on the intention properties.
            $properties       = $intention->getProperties();
            $result["payment_session"] = JArrayHelper::toObject($properties);

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_RESULT_DATA"), $this->debugType, $result) : null;

            // Remove intention
            $txnStatus = (isset($result["transaction"]->txn_status)) ? $result["transaction"]->txn_status : null;
            $this->removeIntention($intention, $txnStatus);
            unset($intention);

        } else {

            // Log error
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_TRANSACTION_DATA"),
                $this->debugType,
                array("error message" => $paypalIpn->getError(), "paypalVerify" => $paypalIpn, "_POST" => $_POST)
            );

        }

        return $result;
    }

    /**
     * This method is invoked after complete payment.
     * It is used to be sent mails to user and administrator
     *
     * @param object $context
     * @param object $transaction Transaction data
     * @param Joomla\Registry\Registry $params Component parameters
     * @param object $project Project data
     * @param object $reward Reward data
     * @param object $paymentSession Payment session data.
     */
    public function onAfterPayment($context, &$transaction, &$params, &$project, &$reward, &$paymentSession)
    {
        if (strcmp("com_crowdfunding.notify.paypal", $context) != 0) {
            return;
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml * */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("raw", $docType) != 0) {
            return;
        }

        // Send mails
        $this->sendMails($project, $transaction, $params);
    }

    /**
     * Validate PayPal transaction.
     *
     * @param array  $data
     * @param string $currency
     * @param object  $intention
     *
     * @return array
     */
    protected function validateData($data, $currency, $intention)
    {
        $txnDate = JArrayHelper::getValue($data, "payment_date");
        $date    = new JDate($txnDate);

        // Prepare transaction data
        $transaction = array(
            "investor_id"      => (int)$intention->getUserId(),
            "project_id"       => (int)$intention->getProjectId(),
            "reward_id"        => ($intention->isAnonymous()) ? 0 : (int)$intention->getRewardId(),
            "service_provider" => "PayPal",
            "txn_id"           => JArrayHelper::getValue($data, "txn_id", null, "string"),
            "txn_amount"       => JArrayHelper::getValue($data, "mc_gross", null, "float"),
            "txn_currency"     => JArrayHelper::getValue($data, "mc_currency", null, "string"),
            "txn_status"       => JString::strtolower(JArrayHelper::getValue($data, "payment_status", null, "string")),
            "txn_date"         => $date->toSql(),
        );


        // Check Project ID and Transaction ID
        if (!$transaction["project_id"] or !$transaction["txn_id"]) {

            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_TRANSACTION_DATA"),
                $this->debugType,
                $transaction
            );

            return null;
        }


        // Check currency
        if (strcmp($transaction["txn_currency"], $currency) != 0) {

            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_TRANSACTION_CURRENCY"),
                $this->debugType,
                array("TRANSACTION DATA" => $transaction, "CURRENCY" => $currency)
            );

            return null;
        }


        // Check payment receiver.
        $allowedReceivers = array(
            JString::strtolower(JArrayHelper::getValue($data, "business")),
            JString::strtolower(JArrayHelper::getValue($data, "receiver_email")),
            JString::strtolower(JArrayHelper::getValue($data, "receiver_id"))
        );

        // Get payment receiver.
        $paymentReceiverOption = $this->params->get("paypal_payment_receiver", "site_owner");
        $paymentReceiver       = $this->getPaymentReceiver($paymentReceiverOption, $transaction["project_id"]);

        if (!in_array($paymentReceiver, $allowedReceivers)) {
            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_RECEIVER"),
                $this->debugType,
                array("TRANSACTION DATA" => $transaction, "RECEIVER" => $paymentReceiver, "RECEIVER DATA" => $allowedReceivers)
            );

            return null;
        }

        return $transaction;
    }

    /**
     * Save transaction data.
     *
     * @param array     $transactionData
     * @param object    $project
     *
     * @return null|array
     */
    protected function storeTransaction($transactionData, $project)
    {
        // Get transaction by txn ID
        jimport("crowdfunding.transaction");
        $keys        = array(
            "txn_id" => JArrayHelper::getValue($transactionData, "txn_id")
        );
        $transaction = new CrowdFundingTransaction(JFactory::getDbo());
        $transaction->load($keys);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_TRANSACTION_OBJECT"), $this->debugType, $transaction->getProperties()) : null;

        // Check for existed transaction
        if ($transaction->getId()) {

            // If the current status if completed,
            // stop the payment process.
            if ($transaction->isCompleted()) {
                return null;
            }

        }

        // Store the new transaction data.
        $transaction->bind($transactionData);
        $transaction->store();

        // If it is not completed (it might be pending or other status),
        // stop the process. Only completed transaction will continue
        // and will process the project, rewards,...
        if (!$transaction->isCompleted()) {
            return null;
        }

        // Set transaction ID.
        $transactionData["id"] = $transaction->getId();

        // If the new transaction is completed,
        // update project funded amount.
        $amount = JArrayHelper::getValue($transactionData, "txn_amount");
        $project->addFunds($amount);
        $project->updateFunds();

        return $transactionData;
    }

    protected function getNotifyUrl()
    {
        $page = JString::trim($this->params->get('paypal_notify_url'));

        $uri    = JUri::getInstance();
        $domain = $uri->toString(array("host"));

        if (false == strpos($page, $domain)) {
            $page = JUri::root() . str_replace("&", "&amp;", $page);
        }

        if (false === strpos($page, "payment_service=PayPal")) {
            $page .= "&amp;payment_service=PayPal";
        }

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_NOTIFY_URL"), $this->debugType, $page) : null;

        return $page;
    }

    protected function getReturnUrl($slug, $catslug)
    {
        $page = JString::trim($this->params->get('paypal_return_url'));
        if (!$page) {
            $uri  = JUri::getInstance();
            $page = $uri->toString(array("scheme", "host")) . JRoute::_(CrowdFundingHelperRoute::getBackingRoute($slug, $catslug, "share"), false);
        }

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_RETURN_URL"), $this->debugType, $page) : null;

        return $page;
    }

    protected function getCancelUrl($slug, $catslug)
    {
        $page = JString::trim($this->params->get('paypal_cancel_url'));
        if (!$page) {
            $uri  = JUri::getInstance();
            $page = $uri->toString(array("scheme", "host")) . JRoute::_(CrowdFundingHelperRoute::getBackingRoute($slug, $catslug, "default"), false);
        }

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_CANCEL_URL"), $this->debugType, $page) : null;

        return $page;
    }

    protected function isPayPalGateway($custom)
    {
        $paymentGateway = JArrayHelper::getValue($custom, "gateway");

        if (strcmp("PayPal", $paymentGateway) != 0) {
            return false;
        }

        return true;
    }

    protected function prepareLocale(&$html)
    {
        // Get country
        jimport("crowdfunding.country");
        $countryId = $this->params->get("paypal_country");
        $country   = new CrowdFundingCountry(JFactory::getDbo());
        $country->load($countryId);

        $code  = $country->getCode();
        $code4 = $country->getCode4();

        $button    = $this->params->get("paypal_button_type", "btn_buynow_LG");
        $buttonUrl = $this->params->get("paypal_button_url");

        // Generate a button
        if (!$this->params->get("paypal_button_default", 0)) {

            if (!$buttonUrl) {

                if (strcmp("US", $code) == 0) {
                    $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/' . $code4 . '/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '">';
                } else {
                    $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/' . $code4 . '/' . $code . '/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '">';
                }

            } else {
                $html[] = '<input type="image" name="submit" border="0" src="' . $buttonUrl . '" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '">';
            }

        } else { // Default button

            $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/en_US/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '">';

        }

        // Set locale
        $html[] = '<input type="hidden" name="lc" value="' . $code . '" />';
    }

    /**
     * Remove an intention record or create a payment session record.
     *
     * @param CrowdFundingIntention|CrowdFundingPaymentSession $intention
     * @param string                                           $txnStatus
     */
    protected function removeIntention($intention, $txnStatus)
    {
        // If status is NOT completed create a payment session.
        if (strcmp("completed", $txnStatus) != 0) {

            // If intention object is instance of CrowdFundingIntention,
            // create a payment session record and remove intention record.
            // If it is NOT instance of CrowdFundingIntention, do NOT remove the record,
            // because it will be used again when PayPal sends a response with status "completed".
            if ($intention instanceof CrowdFundingIntention) {

                jimport("crowdfunding.payment.session");
                $paymentSession = new CrowdFundingPaymentSession(JFactory::getDbo());
                $paymentSession
                    ->setUserId($intention->getUserId())
                    ->setAnonymousUserId($intention->getAnonymousUserId())
                    ->setProjectId($intention->getProjectId())
                    ->setRewardId($intention->getRewardId())
                    ->setRecordDate($intention->getRecordDate())
                    ->setUniqueKey($intention->getUniqueKey())
                    ->setGatewayData($intention->getGatewayData())
                    ->setIntentionId($intention->getId())
                    ->setSessionId($intention->getSessionId());

                $paymentSession->store();

                // DEBUG DATA
                JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_PAYMENT_SESSION"), $this->debugType, $paymentSession->getProperties()) : null;

                // Remove intention object.
                $intention->delete();
            }

            // If transaction status is completed, remove intention record.
        } elseif (strcmp("completed", $txnStatus) == 0) {
            $intention->delete();
        }
    }

    /**
     * Prepare a form element of payment receiver.
     *
     * @param $paymentReceiverOption
     * @param $itemId
     *
     * @return null|string
     */
    protected function preparePaymentReceiver($paymentReceiverOption, $itemId)
    {
        if ($this->params->get('paypal_sandbox', 1)) {
            return '<input type="hidden" name="business" value="' . JString::trim($this->params->get('paypal_sandbox_business_name')) . '" />';
        } else {

            if (strcmp("site_owner", $paymentReceiverOption) == 0) { // Site owner
                return '<input type="hidden" name="business" value="' . JString::trim($this->params->get('paypal_business_name')) . '" />';
            } else {

                if (!JComponentHelper::isEnabled("com_crowdfundingfinance")) {
                    return null;
                } else {

                    jimport("crowdfundingfinance.payout");
                    $payout = new CrowdFundingFinancePayout(JFactory::getDbo());
                    $payout->load($itemId);

                    if (!$payout->getPaypalEmail()) {
                        return null;
                    }

                    return '<input type="hidden" name="business" value="' . JString::trim($payout->getPaypalEmail()) . '" />';

                }

            }

        }

    }

    /**
     * Return payment receiver.
     *
     * @param $paymentReceiverOption
     * @param $itemId
     *
     * @return null|string
     */
    protected function getPaymentReceiver($paymentReceiverOption, $itemId)
    {

        if ($this->params->get('paypal_sandbox', 1)) {
            return JString::strtolower(JString::trim($this->params->get('paypal_sandbox_business_name')));
        } else {

            if (strcmp("site_owner", $paymentReceiverOption) == 0) { // Site owner
                return JString::strtolower(JString::trim($this->params->get('paypal_business_name')));
            } else {

                if (!JComponentHelper::isEnabled("com_crowdfundingfinance")) {
                    return null;
                } else {

                    jimport("crowdfundingfinance.payout");
                    $payout = new CrowdFundingFinancePayout(JFactory::getDbo());
                    $payout->load($itemId);

                    if (!$payout->getPaypalEmail()) {
                        return null;
                    }

                    return JString::strtolower(JString::trim($payout->getPaypalEmail()));
                }

            }

        }

    }
}
