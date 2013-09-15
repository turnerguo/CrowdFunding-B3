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
        
        $custom = base64_encode( json_encode($custom) );
        
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
            return;
        }
       
        if(strcmp("com_crowdfunding.notify", $context) != 0){
            return;
        }
        
        // Validate request method
        $requestMethod = $app->input->getMethod();
        if(strcmp("POST", $requestMethod) != 0) {
            return null;
        }
        
        // Decode custom data
        $custom    = JArrayHelper::getValue($_POST, "custom");
        $custom    = json_decode(base64_decode($custom), true);
        
        // Verify gateway. Is it PayPal? 
        if(!$this->isPayPalGateway($custom)) {
            return null;
        }
        
        // Load language
        $this->loadLanguage();
        
        // Get PayPal URL
        $sandbox      = $this->params->get('paypal_sandbox', 0); 
        if(!$sandbox) { 
            $url = JString::trim($this->params->get('paypal_url', "https://www.paypal.com/cgi-bin/webscr")); 
        } else { 
            $url = JString::trim($this->params->get('paypal_sandbox_url', "https://www.sandbox.paypal.com/cgi-bin/webscr"));
        }
        
        jimport("itprism.paypal.verify");
        $paypalVerify = new ITPrismPayPalVerify($url, $_POST);
        $paypalVerify->verify();
        
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
            
            // Validate transaction data
            $validData = $this->validateData($_POST, $currency->getAbbr(), $intention);
            if(is_null($validData)) {
                return $result;
            }
            
            // Check for valid project
            jimport("crowdfunding.project");
            $projectId = JArrayHelper::getValue($validData, "project_id");
            
            $project   = CrowdFundingProject::getInstance($projectId);
            if(!$project->id) {
                $error  = JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_PROJECT");
                $error .= "\n". JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_TRANSACTION_DATA", var_export($validData, true));
    			JLog::add($error);
    			return $result;
            }
            
            // Set the receiver of funds
            $validData["receiver_id"] = $project->user_id;
            
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
            
            // Remove intention
            $intention->delete();
            unset($intention);
        }
        
        return $result;
                
    }
    
    /**
     * This metod is executed after complete payment.
     * It is used to be sent mails to user and administrator
     * 
     * @param object     $transaction   Transaction data
     * @param JRegistry  $params        Component parameters
     * @param object     $project       Project data
     * @param object     $reward        Reward data
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
        
        // Send email to the administrator
        if($this->params->get("paypal_send_admin_mail", 0)) {
        
            $subject = JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_NEW_INVESTMENT_ADMIN_SUBJECT");
            $body    = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_NEW_INVESTMENT_ADMIN_BODY", $project->title);
            $return  = JFactory::getMailer()->sendMail($app->getCfg("mailfrom"), $app->getCfg("fromname"), $app->getCfg("mailfrom"), $subject, $body);
            
            // Check for an error.
            if ($return !== true) {
                $error = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_MAIL_SENDING_ADMIN");
                JLog::add($error);
            }
        }
        
        // Send email to the user
        if($this->params->get("paypal_send_user_mail", 0)) {
        
            $amount   = $transaction->txn_amount.$transaction->txn_currency;
            
            $user     = JUser::getInstance($project->user_id);
            
             // Send email to the administrator
            $subject = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_NEW_INVESTMENT_USER_SUBJECT", $project->title);
            $body    = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_NEW_INVESTMENT_USER_BODY", $amount, $project->title );
            $return  = JFactory::getMailer()->sendMail($app->getCfg("mailfrom"), $app->getCfg("fromname"), $user->email, $subject, $body);
    		
    		// Check for an error.
    		if ($return !== true) {
    		    $error = JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_MAIL_SENDING_USER");
    			JLog::add($error);
    		}
    		
        }
        
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
            $error  = JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_TRANSACTION_DATA");
            $error .= "\n". JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_TRANSACTION_DATA", var_export($transaction, true));
            JLog::add($error);
            return null;
        }
        
        
        // Check currency
        if(strcmp($transaction["txn_currency"], $currency) != 0) {
            $error  = JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_TRANSACTION_CURRENCY");
            $error .= "\n". JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_TRANSACTION_DATA", var_export($transaction, true));
            $error .= "\n". JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_CURRENCY_DATA", var_export($currency, true));
            JLog::add($error);
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
            $error  = JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_RECEIVER");
            $error .= "\n". JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_TRANSACTION_DATA", var_export($transaction, true));
            $error .= "\n". JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_RECEIVER_DATA", var_export($allowedReceivers, true));
            JLog::add($error);
            return null;
        }
        
        return $transaction;
    }
    
    protected function updateReward(&$data) {
        
        jimport("crowdfunding.reward");
        $keys   = array(
        	"id"         => $data["reward_id"], 
        	"project_id" => $data["project_id"]
        );
        $reward = new CrowdFundingReward($keys);
        
        // Check for valid reward
        if(!$reward->getId()) {
            $error  = JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_REWARD");
            $error .= "\n". JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_TRANSACTION_DATA", var_export($data, true));
			JLog::add($error);
			
			$data["reward_id"] = 0;
			return null;
        }
        
        // Check for valida amount between reward value and payed by user
        $txnAmount = JArrayHelper::getValue($data, "txn_amount");
        if($txnAmount < $reward->getAmount()) {
            $error  = JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_INVALID_REWARD_AMOUNT");
            $error .= "\n". JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_TRANSACTION_DATA", var_export($data, true));
			JLog::add($error);
			
			$data["reward_id"] = 0;
			return null;
        }
        
        // Verify the availability of rewards
        if($reward->isLimited() AND !$reward->getAvailable()) {
            $error  = JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPAL_ERROR_REWARD_NOT_AVAILABLE");
            $error .= "\n". JText::sprintf("PLG_CROWDFUNDINGPAYMENT_PAYPAL_TRANSACTION_DATA", var_export($data, true));
			JLog::add($error);
			
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
     * Save transaction
     * 
     * @param array               $data
     * @param CrowdFundingProject $project
     * 
     * @return boolean
     */
    public function storeTransaction($data, $project) {
        
        // Get transaction by txn ID
        jimport("crowdfunding.transaction");
        $keys = array(
            "txn_id" => JArrayHelper::getValue($data, "txn_id")
        );
        
        $transaction = new CrowdFundingTransaction($keys);
        
        // Check for existed transaction
        if(!empty($transaction->id)) {
            
            // If the current status if completed,
            // stop the process.
            if(strcmp("completed", $transaction->txn_status) == 0) {
                return false;
            } 
            
        }

        // Store the new transaction data.
        $transaction->bind($data);
        $transaction->store();
        
        $txnStatus = JArrayHelper::getValue($data, "txn_status");
        
        // If it is not completed (it might be pending or other status),
        // stop the process. Only completed transaction will continue 
        // and will process the project, rewards,...
        $txnStatus = JArrayHelper::getValue($data, "txn_status");
        if(strcmp("completed", $txnStatus) != 0) {
            return false;
        }
        
        // If the new transaction is completed, 
        // update project funded amount.
        $amount = JArrayHelper::getValue($data, "txn_amount");
        $project->addFunds($amount);
        $project->store();
        
        return true;
    }
    
    
    private function getNotifyUrl() {
        
        $notifyPage = JString::trim($this->params->get('paypal_notify_url'));
        
        $uri        = JURI::getInstance();
        $domain     = $uri->toString(array("host"));
        
        if( false == strpos($notifyPage, $domain) ) {
            $notifyPage = JURI::root().str_replace("&", "&amp;", $notifyPage);
        }
        
        return $notifyPage;
        
    }
    
    private function getReturnUrl($slug, $catslug) {
        
        $returnPage = JString::trim($this->params->get('paypal_return_url'));
        if(!$returnPage) {
            $uri        = JURI::getInstance();
            $returnPage = $uri->toString(array("scheme", "host")).JRoute::_(CrowdFundingHelperRoute::getBackingRoute($slug, $catslug, "share"), false);
        } 
        
        return $returnPage;
        
    }
    
    private function getCancelUrl($slug, $catslug) {
        
        $cancelPage = JString::trim($this->params->get('paypal_cancel_url'));
        if(!$cancelPage) {
            $uri        = JURI::getInstance();
            $cancelPage = $uri->toString(array("scheme", "host")).JRoute::_(CrowdFundingHelperRoute::getBackingRoute($slug, $catslug, "default"), false);
        } 
        
        return $cancelPage;
    }
    
    private function isPayPalGateway($custom) {
        
        $paymentGateway = JArrayHelper::getValue($custom, "gateway");

        if(strcmp("PayPal", $paymentGateway) != 0 ) {
            return false;
        }
        
        return true;
    }
    
}