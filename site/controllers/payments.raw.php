<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * This controller provides functionality 
 * that helps to payment plugins to prepare their payment data.
 * 
 * @package		CrowdFunding
 * @subpackage	Payments
 * 
 */
class CrowdFundingControllerPayments extends JControllerLegacy {
   
	/**
     * Method to get a model object, loading it if required.
     *
     * @param	string	$name	The model name. Optional.
     * @param	string	$prefix	The class prefix. Optional.
     * @param	array	$config	Configuration array for model. Optional.
     *
     * @return	object	The model.
     * @since	1.5
     */
    public function getModel($name = 'Payments', $prefix = '', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Prepare data before payment via iDEAL ( Mollie ).
     */
    public function mollieideal() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $model = $this->getModel();
        /** @var CrowdFundingModelPayments **/
        
        // Get component parameters
        $componentParams = JComponentHelper::getParams("com_crowdfunding");
        
        // Check for disabled payment functionality
        if($componentParams->get("debug_payment_disabled", 0)) {
            $response = array(
                "success" => false,
                "title"   => JText::_("COM_CROWDFUNDING_FAIL"),
                "text"    => JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE")
            );
            
            echo json_encode($response);
            JFactory::getApplication()->close();
        }
        
        $plugin = JPluginHelper::getPlugin('crowdfundingpayment', 'mollieideal');
        $pluginParams = new JRegistry($plugin->params);
        
        $partnerId = $pluginParams->get("partner_id");
        
        $projectId = $app->input->getInt("project_id");
        $bankId    = $app->input->getAlnum("bank_id");
        $amount    = $app->input->get("amount", 0, "float");
        
        $uri        = JUri::getInstance();
        $domain     = $uri->toString(array("host"));
        
        $paymentOptions = array(
            "bank_id"      => $bankId,
            "amount"       => $amount * 100,
            "description"  => "",
            "return_url"   => "",
            "report_url"   => "",
        );
        
        // Save data
        try {
            
            jimport("itprism.payment.mollie.ideal");
            $paymentGateway = new ITPrismPaymentMollieIdeal($partnerId);
            
            // Enable test mode
            if($pluginParams->get('testmode', 1)) {
                $paymentGateway->enableTestmode();
            }
            
            // Get project
            jimport("crowdfunding.project");
            $project    = CrowdFundingProject::getInstance($projectId);
            
            if(!$project->getId()) {
                $response = array(
                    "success" => false,
                    "title"   => JText::_("COM_CROWDFUNDING_FAIL"),
                    "text"    => JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT")
                );
                
                echo json_encode($response);
                JFactory::getApplication()->close();
            }
            
            // Prepare description
            $paymentOptions["description"] = JString::substr($project->getTitle(), 0, 29);
            
            // Prepare return URL
            $returnUrl = JString::trim($pluginParams->get('returnurl'));
            if(!$returnUrl) {
                $returnUrl = $uri->toString(array("scheme", "host")).JRoute::_(CrowdFundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatslug(), "share"), false);
            }
            $paymentOptions["return_url"] = $returnUrl;
            
            // Prepare report URL
            $reportUrl  = JString::trim($pluginParams->get('reporturl'));
            if( false == strpos($reportUrl, $domain) ) {
                $reportUrl = JUri::root().$reportUrl;
            }
            $paymentOptions["report_url"] = $reportUrl;
            
            $paymentGateway->createPayment($paymentOptions);
            
            $url   = $paymentGateway->getBankURL();
            $txnId = $paymentGateway->getTransactionId();
            
            
            //  INTENTIONS
            
            // Prepare custom data
            
            $rewardId     = $app->input->get("reward_id");
            
            $userId       = JFactory::getUser()->id;
            $aUserId      = $app->getUserState("auser_id");
            
            $intention    = CrowdFundingHelper::getIntention($userId, $aUserId, $projectId);
            
            // Prepare intention data.
            $intentionData = array(
                "txn_id"        => $txnId,
                "gateway"       => "Mollie iDEAL",
            );
            
            // Set main data if it is a new intention. 
            if(!$intention->getId()) {
                
                $recordDate = new JDate();
                
                $intentionData["user_id"]     = $userId;
                $intentionData["auser_id"]    = $aUserId; // This is hash user ID used for anonymous users.
                $intentionData["project_id"]  = $projectId;
                $intentionData["reward_id"]   = $rewardId;
                $intentionData["record_date"] = $recordDate->toSql();
                
            }
            
            $intention->bind($intentionData);
            $intention->store();
            
        } catch (Exception $e) {
            
            JLog::add($e->getMessage());
            
            $response = array(
                "success" => false,
                "title"   => JText::_("COM_CROWDFUNDING_FAIL"),
                "text"    => $e->getMessage()
            );
            
            echo json_encode($response);
            JFactory::getApplication()->close();
            
        }
        
        $response = array(
            "success" => true,
            "title"   => JText::_('COM_CROWDFUNDING_SUCCESS'),
            "data"    => array(
                "url" => $url
            )
        );
        
        echo json_encode($response);
        JFactory::getApplication()->close();
        
    }
    
    
    /**
     * Register a bank transfer transaction.
     * 
     */
    public function banktransfer() {
    
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $model = $this->getModel();
        /** @var CrowdFundingModelPayments **/
        
        // Get plugin parameters
        $plugin       = JPluginHelper::getPlugin('crowdfundingpayment', 'banktransfer');
        $pluginParams = new JRegistry($plugin->params);
        
        // Get component parameters
        $componentParams = JComponentHelper::getParams("com_crowdfunding");
        
        // Check for disabled payment functionality
        if($componentParams->get("debug_payment_disabled", 0)) {
            $response = array(
                "success" => false,
                "title"   => JText::_("COM_CROWDFUNDING_FAIL"),
                "text"    => JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE")
            );
            
            echo json_encode($response);
            JFactory::getApplication()->close();
        }
        
        $projectId    = $app->input->getInt("project_id");
        $amount       = $app->input->getFloat("amount");
        
        $uri          = JUri::getInstance();
    
        // Save data
        try {
            
            // Get project
            jimport("crowdfunding.project");
            $project    = CrowdFundingProject::getInstance($projectId);
    
            if(!$project->getId()) {
                $response = array(
                    "success" => false,
                    "title"   => JText::_("COM_CROWDFUNDING_FAIL"),
                    "text"    => JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT")
                );
    
                echo json_encode($response);
                JFactory::getApplication()->close();
            }
            
            jimport("crowdfunding.currency");
            $currencyId = $componentParams->get("project_currency");
            $currency   = CrowdFundingCurrency::getInstance($currencyId);
            
            // Prepare return URL
            $returnUrl = JString::trim($pluginParams->get('return_url'));
            if(!$returnUrl) {
                $returnUrl = $uri->toString(array("scheme", "host")).JRoute::_(CrowdFundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatslug(), "share"), false);
            }
    
            
            // Intentions
            
            $userId       = JFactory::getUser()->get("id");
            $aUserId      = $app->getUserState("auser_id");
            
            // Reset anonymous user hash ID 
            if(!empty($aUserId)) {
                $app->setUserState("auser_id", "");
            }
            
            $intention    = CrowdFundingHelper::getIntention($userId, $aUserId, $projectId);
            
            // Validate intention record
            if(!$intention->getId()) {
    
                $response = array(
                    "success" => false,
                    "title"   => JText::_("COM_CROWDFUNDING_FAIL"),
                    "text"    => JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT")
                );
                
                echo json_encode($response);
                JFactory::getApplication()->close();
    
            }
            
            // Validate Reward
            // If the user is anonymous, the system will store 0 for reward ID. 
            // The anonymous users can't select rewards.
            $rewardId = ($intention->isAnonymous()) ? 0 : (int)$intention->getRewardId();
            if(!empty($rewardId)) {
                $rewardId= $model->updateRewardBankTransfer($rewardId, $projectId, $amount);
            }
            
            // Prepare transaction data
            jimport("itprism.string");
            $transactinoId   = JString::strtoupper(ITPrismString::generateRandomString(12, "BT"));
            $transactionData = array(
                "txn_amount"   => $amount,
                "txn_currency" => $currency->getAbbr(),
                "txn_status"   => "pending",
                "txn_id"       => $transactinoId,
                "project_id"   => $projectId,
                "reward_id"    => $rewardId, 
                "investor_id"  => (int)$userId,
                "receiver_id"  => (int)$project->getUserId(),
                "service_provider"  => "Bank Transfer"
            );
            
            // Store transaction data
            jimport("crowdfunding.transaction");
            $transaction = new CrowdFundingTransaction();
            $transaction->bind($transactionData);
            
            $transaction->store();
            
            // Remove intention
            $intention->delete();
            
            // Reset the values of the payment process ( the flag step 1 ).
            $paymentProcessContext = CrowdFundingConstants::PAYMENT_PROCESS_CONTEXT.$projectId;
            $paymentProcess        = $app->getUserState($paymentProcessContext);
            $paymentProcess->step1 = false;
            $app->setUserState($paymentProcessContext, $paymentProcess);
    
        } catch (Exception $e) {
    
            JLog::add($e->getMessage());
    
            $response = array(
                "success" => false,
                "title"   => JText::_("COM_CROWDFUNDING_FAIL"),
                "text"    => JText::_("COM_CROWDFUNDING_ERROR_SYSTEM")
            );
    
            echo json_encode($response);
            JFactory::getApplication()->close();
    
        }
    
        $language = JFactory::getLanguage();
        $language->load("plg_crowdfundingpayment_banktransfer", JPATH_ADMINISTRATOR);
        
        
        // Send mail to administrator
        if($pluginParams->get("send_admin_mail", 0)) {
            
            $subject = JText::_("PLG_CROWDFUNDINGPAYMENT_BANKTRANSFER_NEW_INVESTMENT_ADMIN_SUBJECT");
            $body    = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_BANKTRANSFER_NEW_INVESTMENT_ADMIN_BODY", $project->getTitle());
            $return  = JFactory::getMailer()->sendMail($app->getCfg("mailfrom"), $app->getCfg("fromname"), $app->getCfg("mailfrom"), $subject, $body);
            
            // Check for an error.
            if ($return !== true) {
                $error = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_BANKTRANSFER_ERROR_MAIL_SENDING_ADMIN");
                JLog::add($error);
            }
            
        }
        
        // Send mail to user
        if($pluginParams->get("send_user_mail", 0)) {
        
            jimport("itprism.string");
            $amount  = ITPrismString::getAmount($transaction->getAmount(), $transaction->getCurrency());
            
            $user    = JUser::getInstance($project->getUserId());
            
            $subject = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_BANKTRANSFER_NEW_INVESTMENT_USER_SUBJECT", $project->getTitle());
            $body    = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_BANKTRANSFER_NEW_INVESTMENT_USER_BODY", $amount, $project->getTitle(), $transactinoId, $transactinoId);
            $return  = JFactory::getMailer()->sendMail($app->getCfg("mailfrom"), $app->getCfg("fromname"), $app->getCfg("mailfrom"), $subject, $body);
        
            // Check for an error.
            if ($return !== true) {
                $error = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_BANKTRANSFER_ERROR_MAIL_SENDING_USER");
                JLog::add($error);
            }
        
        }
        
        $response = array(
            "success" => true,
            "title"   => JText::_('COM_CROWDFUNDING_SUCCESS'),
            "text"    => JText::sprintf('PLG_CROWDFUNDINGPAYMENT_PAYMENT_BANK_TRANSFER_TRANSACTION_REGISTERED', $transactionData["txn_id"], $transactionData["txn_id"]),
            "data"    => array(
                "return_url" => $returnUrl
            )
        );
    
        echo json_encode($response);
        JFactory::getApplication()->close();
    
    }
}
