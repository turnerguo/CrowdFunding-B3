<?php
/**
 * @package      CrowdFunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
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
class plgCrowdFundingPaymentPayPal extends CrowdFundingPaymentPlugin {
    
    protected $paymentService = "paypal";
    
    protected $textPrefix     = "PLG_CROWDFUNDINGPAYMENT_PAYPAL";
    protected $debugType      = "PAYPAL_PAYMENT_PLUGIN_DEBUG";
    
    /**
     * This method prepares a payment gateway - buttons, forms,...
     * That gateway will be displayed on the summary page as a payment option.
     *
     * @param string 	$context	This string gives information about that where it has been executed the trigger.
     * @param object 	$item	    A project data.
     * @param JRegistry $params	    The parameters of the component
     */
    public function onProjectPayment($context, $item, $params) {
        
        if(strcmp("com_crowdfunding.payment", $context) != 0){
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/

        if($app->isAdmin()) {
            return;
        }

        $doc     = JFactory::getDocument();
        /**  @var $doc JDocumentHtml **/
        
        // Check document type
        $docType = $doc->getType();
        if(strcmp("html", $docType) != 0){
            return;
        }
       
        // This is a URI path to the plugin folder
        $pluginURI = "plugins/crowdfundingpayment/paypal";
        
        $notifyUrl = $this->getNotifyUrl();
        $returnUrl = $this->getReturnUrl($item->slug, $item->catslug);
        $cancelUrl = $this->getCancelUrl($item->slug, $item->catslug);
        
        $html   =  array();
        $html[] = '<h4><img src="'.$pluginURI.'/images/paypal_icon.png" width="36" height="32" alt="PayPal" />'.JText::_($this->textPrefix."_TITLE").'</h4>';
        $html[] = '<p>'.JText::_($this->textPrefix."_INFO").'</p>';
        
        if(!$this->params->get('paypal_sandbox', 1)) {
            $html[] = '<form action="'.JString::trim($this->params->get('paypal_url')).'" method="post">';
            $html[] = '<input type="hidden" name="business" value="'.JString::trim($this->params->get('paypal_business_name')).'" />';
        }  else {
            $html[] = '<form action="'.JString::trim($this->params->get('paypal_sandbox_url')).'" method="post">';
            $html[] = '<input type="hidden" name="business" value="'.JString::trim($this->params->get('paypal_sandbox_business_name')).'" />';
        }
        
        $html[] = '<input type="hidden" name="cmd" value="_xclick" />';
        $html[] = '<input type="hidden" name="charset" value="utf-8" />';
        $html[] = '<input type="hidden" name="currency_code" value="'.$item->currencyCode.'" />';
        $html[] = '<input type="hidden" name="amount" value="'.$item->amount.'" />';
        $html[] = '<input type="hidden" name="quantity" value="1" />';
        $html[] = '<input type="hidden" name="no_shipping" value="1" />';
        $html[] = '<input type="hidden" name="no_note" value="1" />';
        $html[] = '<input type="hidden" name="tax" value="0" />';
        
        // Title
        $title = JText::sprintf($this->textPrefix."_INVESTING_IN_S", htmlentities($item->title, ENT_QUOTES, "UTF-8"));
        $html[] = '<input type="hidden" name="item_name" value="'.$title.'" />';
        
        // Get intention
        $userId        = JFactory::getUser()->id;
        $aUserId       = $app->getUserState("auser_id");
        
        $intention     = $this->getIntention(array(
            "user_id"       => $userId,
            "auser_id"      => $aUserId,
            "project_id"    => $item->id
        ));
        
        // Prepare custom data
        $custom = array(
            "intention_id" =>  $intention->getId(),
            "gateway"	   =>  "PayPal"
        );
        
        $custom = base64_encode(json_encode($custom));
        $html[] = '<input type="hidden" name="custom" value="'.$custom.'" />';
        
        // Set a link to logo
        $imageUrl = JString::trim($this->params->get('paypal_image_url'));
        if($imageUrl) {
            $html[] = '<input type="hidden" name="image_url" value="'.$imageUrl.'" />';
        }
        
        $html[] = '<input type="hidden" name="cancel_return" value="'.$cancelUrl.'" />';
        
        $html[] = '<input type="hidden" name="return" value="'.$returnUrl.'" />';
        
        $html[] = '<input type="hidden" name="notify_url" value="'.$notifyUrl.'" />';
        
        $this->prepareLocale($html);
        
        $html[] = '<img alt="" border="0" width="1" height="1" src="https://www.paypal.com/en_US/i/scr/pixel.gif" >';
        $html[] = '</form>';
        
        if($this->params->get('paypal_sandbox', 1)) {
            $html[] = '<p class="sticky">'.JText::_($this->textPrefix."_WORKS_SANDBOX").'</p>';
        }
        
        return implode("\n", $html);
        
    }
    
    /**
     * This method processes transaction data that comes from PayPal instant notifier.
     *  
     * @param string 	$context	This string gives information about that where it has been executed the trigger.
     * @param JRegistry $params	    The parameters of the component
     * 
     * @return null|array
     */
    public function onPaymenNotify($context, $params) {
        
        if(strcmp("com_crowdfunding.notify.paypal", $context) != 0){
            return null;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        if($app->isAdmin()) {
            return;
        }

        $doc     = JFactory::getDocument();
        /**  @var $doc JDocumentHtml **/
        
        // Check document type
        $docType = $doc->getType();
        if(strcmp("raw", $docType) != 0){
            return null;
        }
       
        // Validate request method
        $requestMethod = $app->input->getMethod();
        if(strcmp("POST", $requestMethod) != 0) {
            $this->log->add(
                JText::_($this->textPrefix."_ERROR_INVALID_REQUEST_METHOD"), 
                $this->debugType, 
                JText::sprintf($this->textPrefix."_ERROR_INVALID_TRANSACTION_REQUEST_METHOD", $requestMethod)
            );
            return null;
        }
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_RESPONSE"), $this->debugType, $_POST) : null;
        
        // Decode custom data
        $custom    = JArrayHelper::getValue($_POST, "custom");
        $custom    = json_decode(base64_decode($custom), true);
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_CUSTOM"), $this->debugType, $custom) : null;
        
        // Verify gateway. Is it PayPal? 
        if(!$this->isPayPalGateway($custom)) {
            $this->log->add(
                JText::_($this->textPrefix."_ERROR_INVALID_PAYMENT_GATEWAY"), 
                $this->debugType, 
                array("custom" => $custom, "_POST" => $_POST) 
            );
            return null;
        }
        
        // Get PayPal URL
        $sandbox      = $this->params->get('paypal_sandbox', 0); 
        if(!$sandbox) { 
            $url = JString::trim($this->params->get('paypal_url', "https://www.paypal.com/cgi-bin/webscr")); 
        } else { 
            $url = JString::trim($this->params->get('paypal_sandbox_url', "https://www.sandbox.paypal.com/cgi-bin/webscr"));
        }
        
        jimport("itprism.payment.paypal.ipn");
        $paypalIpn = new ITPrismPayPalIpn($url, $_POST);
        $loadCertificate = (bool)$this->params->get("paypal_load_certificate", 0);
        $paypalIpn->verify($loadCertificate);
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_VERIFY_OBJECT"), $this->debugType, $paypalIpn) : null;
        
        // Prepare the array that will be returned by this method
        $result = array(
        	"project"          => null, 
        	"reward"           => null, 
        	"transaction"      => null,
            "payment_service"  => "PayPal"
        );
        
        if($paypalIpn->isVerified()) {
            
            // Get currency
            jimport("crowdfunding.currency");
            $currencyId      = $params->get("project_currency");
            $currency        = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId);
            
            // Get intention data
            $intentionId     = JArrayHelper::getValue($custom, "intention_id", 0, "int");
            
            jimport("crowdfunding.intention");
            $intention       = new CrowdFundingIntention($intentionId);
            
            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_INTENTION"), $this->debugType, $intention->getProperties()) : null;
            
            // Validate transaction data
            $validData = $this->validateData($_POST, $currency->getAbbr(), $intention);
            if(is_null($validData)) {
                return $result;
            }
            
            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_VALID_DATA"), $this->debugType, $validData) : null;
            
            // Get project.
            jimport("crowdfunding.project");
            $projectId = JArrayHelper::getValue($validData, "project_id");
            $project   = CrowdFundingProject::getInstance($projectId);
            
            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_PROJECT_OBJECT"), $this->debugType, $project->getProperties()) : null;
            
            // Check for valid project
            if(!$project->getId()) {
                
                // Log data in the database
                $this->log->add(
                    JText::_($this->textPrefix."_ERROR_INVALID_PROJECT"),
                    $this->debugType,
                    $validData
                );
                
    			return $result;
            }
            
            // Set the receiver of funds
            $validData["receiver_id"] = $project->getUserId();
            
            // Save transaction data.
            // If it is not completed, return empty results.
            // If it is complete, continue with process transaction data
            if(!$this->storeTransaction($validData, $project)) {
                return $result;
            }
            
            // Validate and Update distributed value of the reward
            $rewardId  = JArrayHelper::getValue($validData, "reward_id");
            $reward    = null;
            if(!empty($rewardId)) {
                $reward = $this->updateReward($validData);
            }
            
            
            //  Prepare the data that will be returned
            
            $result["transaction"]    = JArrayHelper::toObject($validData);
            
            // Generate object of data based on the project properties
            $properties               = $project->getProperties();
            $result["project"]        = JArrayHelper::toObject($properties);
            
            // Generate object of data based on the reward properties
            if(!empty($reward)) {
                $properties           = $reward->getProperties();
                $result["reward"]     = JArrayHelper::toObject($properties);
            }
            
            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_RESULT_DATA"), $this->debugType, $result) : null;
            
            // Remove intention
            $intention->delete();
            unset($intention);
            
        } else {
            
            // Log error
            $this->log->add(
                JText::_($this->textPrefix."_ERROR_INVALID_TRANSACTION_DATA"), 
                $this->debugType, 
                array("error message" => $paypalIpn->getError(), "paypalVerify" => $paypalIpn, "_POST" => $_POST)
            );
            
        }
        
        return $result;
                
    }
    
    /**
     * This metod is executed after complete payment.
     * It is used to be sent mails to user and administrator
     * 
     * @param stdObject  Transaction data
     * @param JRegistry  Component parameters
     * @param stdObject  Project data
     * @param stdObject  Reward data
     */
    public function onAfterPayment($context, &$transaction, $params, $project, $reward) {
        
        if(strcmp("com_crowdfunding.notify.paypal", $context) != 0){
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        if($app->isAdmin()) {
            return;
        }

        $doc     = JFactory::getDocument();
        /**  @var $doc JDocumentHtml **/
        
        // Check document type
        $docType = $doc->getType();
        if(strcmp("raw", $docType) != 0){
            return;
        }
       
        // Send mails
        $this->sendMails($project, $transaction);
    }
    
	/**
     * Validate PayPal transaction
     * 
     * @param array $data
     * @param string $currency
     * @param array $intention
     */
    protected function validateData($data, $currency, $intention) {
        
        $txnDate = JArrayHelper::getValue($data, "payment_date");
        $date    = new JDate($txnDate);
        
        // Prepare transaction data
        $transaction = array(
            "investor_id"		     => (int)$intention->getUserId(),
            "project_id"		     => (int)$intention->getProjectId(),
            "reward_id"			     => ($intention->isAnonymous()) ? 0 : (int)$intention->getRewardId(),
        	"service_provider"       => "PayPal",
        	"txn_id"                 => JArrayHelper::getValue($data, "txn_id", null, "string"),
        	"txn_amount"		     => JArrayHelper::getValue($data, "mc_gross", null, "float"),
            "txn_currency"           => JArrayHelper::getValue($data, "mc_currency", null, "string"),
            "txn_status"             => JString::strtolower( JArrayHelper::getValue($data, "payment_status", null, "string") ),
            "txn_date"               => $date->toSql(),
        ); 
        
        
        // Check Project ID and Transaction ID
        if(!$transaction["project_id"] OR !$transaction["txn_id"]) {
            
            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix."_ERROR_INVALID_TRANSACTION_DATA"),
                $this->debugType,
                $transaction
            );
            
            return null;
        }
        
        
        // Check currency
        if(strcmp($transaction["txn_currency"], $currency) != 0) {
            
            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix."_ERROR_INVALID_TRANSACTION_CURRENCY"),
                $this->debugType,
                array("TRANSACTION DATA" => $transaction, "CURRENCY" => $currency)
            );
            
            return null;
        }
        
        
        // Check receiver
        $allowedReceivers = array(
            JString::strtolower(JArrayHelper::getValue($data, "business")),
            JString::strtolower(JArrayHelper::getValue($data, "receiver_email")),
            JString::strtolower(JArrayHelper::getValue($data, "receiver_id"))
        );
        
        if($this->params->get("paypal_sandbox", 0)) {
            $receiver = JString::strtolower(JString::trim($this->params->get("paypal_sandbox_business_name")));
        } else {
            $receiver = JString::strtolower(JString::trim($this->params->get("paypal_business_name")));
        }
        
        if(!in_array($receiver, $allowedReceivers)) {
            
            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix."_ERROR_INVALID_RECEIVER"),
                $this->debugType,
                array("TRANSACTION DATA" => $transaction, "RECEIVER DATA" => $allowedReceivers)
            );
            
            return null;
        }
        
        return $transaction;
    }
    
    /**
     * Save transaction.
     * 
     * @param array     $data
     * @param stdObject $project
     * 
     * @return boolean
     */
    protected function storeTransaction($data, $project) {
        
        // Get transaction by txn ID
        jimport("crowdfunding.transaction");
        $keys = array(
            "txn_id" => JArrayHelper::getValue($data, "txn_id")
        );
        $transaction = new CrowdFundingTransaction($keys);
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_TRANSACTION_OBJECT"), $this->debugType, $transaction->getProperties()) : null;
        
        // Check for existed transaction
        if($transaction->getId()) {
            
            // If the current status if completed,
            // stop the process.
            if($transaction->isCompleted()) {
                return false;
            } 
            
        }

        // Store the new transaction data.
        $transaction->bind($data);
        $transaction->store();
        
        // If it is not completed (it might be pending or other status),
        // stop the process. Only completed transaction will continue 
        // and will process the project, rewards,...
        if(!$transaction->isCompleted()) {
            return false;
        }
        
        // If the new transaction is completed, 
        // update project funded amount.
        $amount = JArrayHelper::getValue($data, "txn_amount");
        $project->addFunds($amount);
        $project->store();
        
        return true;
    }
    
    protected function getNotifyUrl() {
        
        $page = JString::trim($this->params->get('paypal_notify_url'));
        
        $uri        = JURI::getInstance();
        $domain     = $uri->toString(array("host"));
        
        if( false == strpos($page, $domain) ) {
            $page = JURI::root().str_replace("&", "&amp;", $page);
        }
        
        if(false === strpos($page, "payment_service=PayPal")) {
            $page .= "&amp;payment_service=PayPal";
        }
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_NOTIFY_URL"), $this->debugType, $page) : null;
        
        return $page;
        
    }
    
    protected function getReturnUrl($slug, $catslug) {
        
        $page = JString::trim($this->params->get('paypal_return_url'));
        if(!$page) {
            $uri        = JURI::getInstance();
            $page = $uri->toString(array("scheme", "host")).JRoute::_(CrowdFundingHelperRoute::getBackingRoute($slug, $catslug, "share"), false);
        } 
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_RETURN_URL"), $this->debugType, $page) : null;
        
        return $page;
        
    }
    
    protected function getCancelUrl($slug, $catslug) {
        
        $page = JString::trim($this->params->get('paypal_cancel_url'));
        if(!$page) {
            $uri  = JURI::getInstance();
            $page = $uri->toString(array("scheme", "host")).JRoute::_(CrowdFundingHelperRoute::getBackingRoute($slug, $catslug, "default"), false);
        } 
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_CANCEL_URL"), $this->debugType, $page) : null;
        
        return $page;
    }
    
    protected function isPayPalGateway($custom) {
        
        $paymentGateway = JArrayHelper::getValue($custom, "gateway");

        if(strcmp("PayPal", $paymentGateway) != 0 ) {
            return false;
        }
        
        return true;
    }
    
    protected function prepareLocale(&$html) {
    
        // Get country
        jimport("crowdfunding.country");
        $countryId = $this->params->get("paypal_country");
        $country   = new CrowdFundingCountry(JFactory::getDbo());
        $country->load($countryId);
    
        $code   = $country->getCode();
        $code4  = $country->getCode4();
    
        $button     = $this->params->get("paypal_button_type", "btn_buynow_LG");
        $buttonUrl  = $this->params->get("paypal_button_url");
    
        if(!$buttonUrl) {
    
            if(strcmp("US", $code) == 0) {
                $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/'.$code4.'/i/btn/'.$button.'.gif" alt="'.JText::_($this->textPrefix."_BUTTON_ALT").'">';
            } else {
                $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/'.$code4.'/'.$code.'/i/btn/'.$button.'.gif" alt="'.JText::_($this->textPrefix."_BUTTON_ALT").'">';
            }
    
        } else {
            $html[] = '<input type="image" name="submit" border="0" src="'.$buttonUrl.'" alt="'.JText::_($this->textPrefix."_BUTTON_ALT").'">';
        }
    
        $html[] = '<input type="hidden" name="lc" value="'.$code.'" />';
    
    }
    
}