<?php
/**
 * @package      CrowdFunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * CrowdFunding PayPal Payment Plugin
 *
 * @package      CrowdFunding
 * @subpackage   Plugins
 */
class plgCrowdFundingPaymentPayPal extends JPlugin {
    
    protected   $log;
    protected   $logFile = "plg_crowdfunding_paypal.php";
    
    public function __construct(&$subject, $config = array()) {
    
        parent::__construct($subject, $config);
    
        // Create log object
        $file = JPath::clean(JFactory::getApplication()->getCfg("log_path") .DIRECTORY_SEPARATOR. $this->logFile);
    
        $this->log = new CrowdFundingLog();
        $this->log->addWriter(new CrowdFundingLogWriterDatabase(JFactory::getDbo()));
        $this->log->addWriter(new CrowdFundingLogWriterFile($file));
    
        // Load language
        $this->loadLanguage();
    }
    
    /**
     * This method prepares a payment gateway - buttons, forms,...
     * That gateway will be displayed on the summary page as a payment option.
     *
     * @param string 	$context	This string gives information about that where it has been executed the trigger.
     * @param object 	$item	    A project data.
     * @param JRegistry $params	    The parameters of the component
     */
    public function onProjectPayment($context, $item, $params) {
        
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
       
        if(strcmp("com_crowdfunding.payment", $context) != 0){
            return;
        }
        
        // Load language
        $this->loadLanguage();
        
        // This is a URI path to the plugin folder
        $pluginURI = "plugins/crowdfundingpayment/paypal";
        
        $notifyUrl = $this->getNotifyUrl();
        $returnUrl = $this->getReturnUrl($item->slug, $item->catslug);
        $cancelUrl = $this->getCancelUrl($item->slug, $item->catslug);
        
        $html  =  "";
        $html .= '<h4><img src="'.$pluginURI.'/images/paypal_icon.png" width="36" height="32" alt="PayPal" />'.JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_TITLE").'</h4>';
        $html .= '<p>'.JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_INFO").'</p>';
        
        if(!$this->params->get('paypal_sandbox', 1)) {
            $html .= '<form action="'.JString::trim($this->params->get('paypal_url')).'" method="post">';
            $html .= '<input type="hidden" name="business" value="'.JString::trim($this->params->get('paypal_business_name')).'" />';
        }  else {
            $html .= '<form action="'.JString::trim($this->params->get('paypal_sandbox_url')).'" method="post">';
            $html .= '<input type="hidden" name="business" value="'.JString::trim($this->params->get('paypal_sandbox_business_name')).'" />';
        }
        
        $html .= '<input type="hidden" name="cmd" value="_xclick" />';
        $html .= '<input type="hidden" name="charset" value="utf-8" />';
        $html .= '<input type="hidden" name="currency_code" value="'.$item->currencyCode.'" />';
        $html .= '<input type="hidden" name="amount" value="'.$item->amount.'" />';
        $html .= '<input type="hidden" name="quantity" value="1" />';
        $html .= '<input type="hidden" name="no_shipping" value="1" />';
        $html .= '<input type="hidden" name="no_note" value="1" />';
        $html .= '<input type="hidden" name="tax" value="0" />';
        
        // Title
        $title = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_INVESTING_IN_S", htmlentities($item->title, ENT_QUOTES, "UTF-8"));
        $html .= '<input type="hidden" name="item_name" value="'.$title.'" />';
        
        // Get intention
        $userId        = JFactory::getUser()->id;
        $aUserId       = $app->getUserState("auser_id");
        
        $intention = CrowdFundingHelper::getIntention($userId, $aUserId, $item->id);
        
        // Prepare custom data
        $custom = array(
            "intention_id" =>  $intention->getId(),
            "gateway"	   =>  "PayPal"
        );
        
        $custom = base64_encode(json_encode($custom));
        
        $html .= '<input type="hidden" name="custom" value="'.$custom.'" />';
        
        // Set a link to logo
        $imageUrl = JString::trim($this->params->get('paypal_image_url'));
        if($imageUrl) {
            $html .= '<input type="hidden" name="image_url" value="'.$imageUrl.'" />';
        }
        
        $html .= '<input type="hidden" name="cancel_return" value="'.$cancelUrl.'" />';
        
        $html .= '<input type="hidden" name="return" value="'.$returnUrl.'" />';
        
        $html .= '<input type="hidden" name="notify_url" value="'.$notifyUrl.'" />';
        $html .= '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" alt="'.JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_BUTTON_ALT").'">
        <img alt="" border="0" width="1" height="1" src="https://www.paypal.com/en_US/i/scr/pixel.gif" >  
    	</form>';
        
        if($this->params->get('paypal_sandbox', 1)) {
            $html .= '<p class="sticky">'.JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_WORKS_SANDBOX").'</p>';
        }
        
        return $html;
        
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
       
        if(strcmp("com_crowdfunding.notify", $context) != 0){
            return null;
        }
        
        // Load language
        $this->loadLanguage();
        
        // Validate request method
        $requestMethod = $app->input->getMethod();
        if(strcmp("POST", $requestMethod) != 0) {
            $this->log->add(
                JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_REQUEST_METHOD"), 
                "PAYPAL_PAYMENT_PLUGIN_ERROR", 
                JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_TRANSACTION_REQUEST_METHOD", $requestMethod)
            );
            return null;
        }
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_RESPONSE"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $_POST) : null;
        
        // Decode custom data
        $custom    = JArrayHelper::getValue($_POST, "custom");
        $custom    = json_decode(base64_decode($custom), true);
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_CUSTOM"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $custom) : null;
        
        // Verify gateway. Is it PayPal? 
        if(!$this->isPayPalGateway($custom)) {
            $this->log->add(
                JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_PAYMENT_GATEWAY"), 
                "PAYPAL_PAYMENT_PLUGIN_ERROR", 
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
        
        jimport("itprism.payment.paypal.verify");
        $paypalVerify = new ITPrismPayPalVerify($url, $_POST);
        $paypalVerify->verify();
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_VERIFY_OBJECT"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $paypalVerify) : null;
        
        // Prepare the array that will be returned by this method
        $result = array(
        	"project"          => null, 
        	"reward"           => null, 
        	"transaction"      => null,
            "payment_service"  => "PayPal"
        );
        
        if($paypalVerify->isVerified()) {
            
            // Get currency
            jimport("crowdfunding.currency");
            $currencyId      = $params->get("project_currency");
            $currency        = CrowdFundingCurrency::getInstance($currencyId);
            
            // Get intention data
            $intentionId     = JArrayHelper::getValue($custom, "intention_id", 0, "int");
            
            jimport("crowdfunding.intention");
            $intention       = new CrowdFundingIntention($intentionId);
            
            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_INTENTION"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $intention->getProperties()) : null;
            
            // Validate transaction data
            $validData = $this->validateData($_POST, $currency->getAbbr(), $intention);
            if(is_null($validData)) {
                return $result;
            }
            
            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_VALID_DATA"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $validData) : null;
            
            // Get project.
            jimport("crowdfunding.project");
            $projectId = JArrayHelper::getValue($validData, "project_id");
            $project   = CrowdFundingProject::getInstance($projectId);
            
            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_PROJECT_OBJECT"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $project->getProperties()) : null;
            
            // Check for valid project
            if(!$project->getId()) {
                
                // Log data in the database
                $this->log->add(
                    JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_PROJECT"),
                    "PAYPAL_PAYMENT_PLUGIN_ERROR",
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
            JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_RESULT_DATA"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $result) : null;
            
            // Remove intention
            $intention->delete();
            unset($intention);
            
        } else {
            
            // Log error
            $this->log->add(
                JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_TRANSACTION_DATA"), 
                "PAYPAL_PAYMENT_PLUGIN_ERROR", 
                array("error message" => $paypalVerify->getError(), "paypalVerify" => $paypalVerify, "_POST" => $_POST)
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
       
        if(strcmp("com_crowdfunding.notify.paypal", $context) != 0){
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
                JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_TRANSACTION_DATA"),
                "PAYPAL_PAYMENT_PLUGIN_ERROR",
                $transaction
            );
            
            return null;
        }
        
        
        // Check currency
        if(strcmp($transaction["txn_currency"], $currency) != 0) {
            
            // Log data in the database
            $this->log->add(
                JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_TRANSACTION_CURRENCY"),
                "PAYPAL_PAYMENT_PLUGIN_ERROR",
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
                JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_RECEIVER"),
                "PAYPAL_PAYMENT_PLUGIN_ERROR",
                array("TRANSACTION DATA" => $transaction, "RECEIVER DATA" => $allowedReceivers)
            );
            
            return null;
        }
        
        return $transaction;
    }
    
    protected function updateReward(&$data) {
        
        // Get reward.
        jimport("crowdfunding.reward");
        $keys   = array(
        	"id"         => $data["reward_id"], 
        	"project_id" => $data["project_id"]
        );
        $reward = new CrowdFundingReward($keys);
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_REWARD_OBJECT"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $reward->getProperties()) : null;
        
        // Check for valid reward.
        if(!$reward->getId()) {
            
            // Log data in the database
            $this->log->add(
                JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_REWARD"),
                "PAYPAL_PAYMENT_PLUGIN_ERROR",
                array("data" => $data, "reward object" => $reward->getProperties())
            );
            
			$data["reward_id"] = 0;
			return null;
        }
        
        // Check for valida amount between reward value and payed by user
        $txnAmount = JArrayHelper::getValue($data, "txn_amount");
        if($txnAmount < $reward->getAmount()) {
            
            // Log data in the database
            $this->log->add(
                JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_REWARD_AMOUNT"),
                "PAYPAL_PAYMENT_PLUGIN_ERROR",
                array("data" => $data, "reward object" => $reward->getProperties())
            );
            
			$data["reward_id"] = 0;
			return null;
        }
        
        // Verify the availability of rewards
        if($reward->isLimited() AND !$reward->getAvailable()) {
            
            // Log data in the database
            $this->log->add(
                JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_REWARD_NOT_AVAILABLE"),
                "PAYPAL_PAYMENT_PLUGIN_ERROR",
                array("data" => $data, "reward object" => $reward->getProperties())
            );
            
			$data["reward_id"] = 0;
			return null;
        }
        
        // Increase the number of distributed rewards 
        // if there is a limit.
        if($reward->isLimited()) {
            $reward->increaseDistributed();
            $reward->store();
        }
        
        return $reward;
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
        JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_TRANSACTION_OBJECT"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $transaction->getProperties()) : null;
        
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
        
        $notifyPage = JString::trim($this->params->get('paypal_notify_url'));
        
        $uri        = JURI::getInstance();
        $domain     = $uri->toString(array("host"));
        
        if( false == strpos($notifyPage, $domain) ) {
            $notifyPage = JURI::root().str_replace("&", "&amp;", $notifyPage);
        }
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_NOTIFY_URL"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $notifyPage) : null;
        
        return $notifyPage;
        
    }
    
    protected function getReturnUrl($slug, $catslug) {
        
        $returnPage = JString::trim($this->params->get('paypal_return_url'));
        if(!$returnPage) {
            $uri        = JURI::getInstance();
            $returnPage = $uri->toString(array("scheme", "host")).JRoute::_(CrowdFundingHelperRoute::getBackingRoute($slug, $catslug, "share"), false);
        } 
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_RETURN_URL"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $returnPage) : null;
        
        return $returnPage;
        
    }
    
    protected function getCancelUrl($slug, $catslug) {
        
        $cancelPage = JString::trim($this->params->get('paypal_cancel_url'));
        if(!$cancelPage) {
            $uri        = JURI::getInstance();
            $cancelPage = $uri->toString(array("scheme", "host")).JRoute::_(CrowdFundingHelperRoute::getBackingRoute($slug, $catslug, "default"), false);
        } 
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_DEBUG_CANCEL_URL"), "PAYPAL_PAYMENT_PLUGIN_DEBUG", $cancelPage) : null;
        
        return $cancelPage;
    }
    
    protected function isPayPalGateway($custom) {
        
        $paymentGateway = JArrayHelper::getValue($custom, "gateway");

        if(strcmp("PayPal", $paymentGateway) != 0 ) {
            return false;
        }
        
        return true;
    }
    
    protected function sendMails($project, $transaction) {
    
        $app = JFactory::getApplication();
        /** @var $app JSite **/
    
        // Get website
        $uri     = JUri::getInstance();
        $website = $uri->toString(array("scheme", "host"));
    
        jimport("itprism.string");
        jimport("crowdfunding.email");
    
        $emailMode  = $this->params->get("email_mode", "plain");
    
        // Prepare data for parsing
        $data = array(
            "site_name"         => $app->getCfg("sitename"),
            "site_url"          => JUri::root(),
            "item_title"        => $project->title,
            "item_url"          => $website.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($project->slug, $project->catslug)),
            "amount"            => ITPrismString::getAmount($transaction->txn_amount, $transaction->txn_currency),
            "transaction_id"    => $transaction->txn_id
        );
    
        // Send mail to the administrator
        $emailId = $this->params->get("admin_mail_id", 0);
        if(!empty($emailId)) {
    
            $table    = new CrowdFundingTableEmail(JFactory::getDbo());
            $email    = new CrowdFundingEmail();
            $email->setTable($table);
            $email->load($emailId);
    
            if(!$email->getSenderName()) {
                $email->setSenderName($app->getCfg("fromname"));
            }
            if(!$email->getSenderEmail()) {
                $email->setSenderEmail($app->getCfg("mailfrom"));
            }
    
            $recipientName = $email->getSenderName();
            $recipientMail = $email->getSenderEmail();
    
            // Prepare data for parsing
            $data["sender_name"]     =  $email->getSenderName();
            $data["sender_email"]    =  $email->getSenderEmail();
            $data["recipient_name"]  =  $recipientName;
            $data["recipient_email"] =  $recipientMail;
    
            $email->parse($data);
            $subject    = $email->getSubject();
            $body       = $email->getBody($emailMode);
    
            $mailer  = JFactory::getMailer();
            if(strcmp("html", $emailMode) == 0) { // Send as HTML message
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_HTML);
    
            } else { // Send as plain text.
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_PLAIN);
    
            }
    
            // Check for an error.
            if ($return !== true) {
    
                // Log error
                $this->log->add(
                    JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_MAIL_SENDING_ADMIN"),
                    "PAYPAL_PAYMENT_PLUGIN_ERROR"
                );
    
            }
    
        }
    
        // Send mail to project owner
        $emailId = $this->params->get("creator_mail_id", 0);
        if(!empty($emailId)) {
    
            $table    = new CrowdFundingTableEmail(JFactory::getDbo());
            $email    = new CrowdFundingEmail();
            $email->setTable($table);
            $email->load($emailId);
    
            if(!$email->getSenderName()) {
                $email->setSenderName($app->getCfg("fromname"));
            }
            if(!$email->getSenderEmail()) {
                $email->setSenderEmail($app->getCfg("mailfrom"));
            }
    
            $user          = JFactory::getUser($transaction->receiver_id);
            $recipientName = $user->get("name");
            $recipientMail = $user->get("email");
    
            // Prepare data for parsing
            $data["sender_name"]     =  $email->getSenderName();
            $data["sender_email"]    =  $email->getSenderEmail();
            $data["recipient_name"]  =  $recipientName;
            $data["recipient_email"] =  $recipientMail;
    
            $email->parse($data);
            $subject    = $email->getSubject();
            $body       = $email->getBody($emailMode);
    
            $mailer  = JFactory::getMailer();
            if(strcmp("html", $emailMode) == 0) { // Send as HTML message
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_HTML);
    
            } else { // Send as plain text.
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_PLAIN);
    
            }
    
            // Check for an error.
            if ($return !== true) {
    
                // Log error
                $this->log->add(
                    JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_MAIL_SENDING_PROJECT_OWNER"),
                    "PAYPAL_PAYMENT_PLUGIN_ERROR"
                );
    
            }
        }
    
        // Send mail to backer
        $emailId    = $this->params->get("user_mail_id", 0);
        $investorId = $transaction->investor_id;
        if(!empty($emailId) AND !empty($investorId)) {
    
            $table    = new CrowdFundingTableEmail(JFactory::getDbo());
            $email    = new CrowdFundingEmail();
            $email->setTable($table);
            $email->load($emailId);
    
            if(!$email->getSenderName()) {
                $email->setSenderName($app->getCfg("fromname"));
            }
            if(!$email->getSenderEmail()) {
                $email->setSenderEmail($app->getCfg("mailfrom"));
            }
    
            $user          = JFactory::getUser($investorId);
            $recipientName = $user->get("name");
            $recipientMail = $user->get("email");
    
            // Prepare data for parsing
            $data["sender_name"]     =  $email->getSenderName();
            $data["sender_email"]    =  $email->getSenderEmail();
            $data["recipient_name"]  =  $recipientName;
            $data["recipient_email"] =  $recipientMail;
    
            $email->parse($data);
            $subject    = $email->getSubject();
            $body       = $email->getBody($emailMode);
    
            $mailer  = JFactory::getMailer();
            if(strcmp("html", $emailMode) == 0) { // Send as HTML message
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_HTML);
    
            } else { // Send as plain text.
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_PLAIN);
    
            }
    
            // Check for an error.
            if ($return !== true) {
    
                // Log error
                $this->log->add(
                    JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_MAIL_SENDING_USER"),
                    "PAYPAL_PAYMENT_PLUGIN_ERROR"
                );
    
            }
    
        }
    
    }
    
}