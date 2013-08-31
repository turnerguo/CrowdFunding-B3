<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * 
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
     * 
     */
    public function mollieideal() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $plugin = JPluginHelper::getPlugin('crowdfundingpayment', 'mollieideal');
        $pluginParams = new JRegistry($plugin->params);
        
        $partnerId = $pluginParams->get("partner_id");
        
        $projectId = $app->input->getInt("project_id");
        $bankId    = $app->input->getInt("bank_id");
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
            
            // @todo do it to work with anonymous users
            
            jimport("itprism.payment.mollie.ideal");
            $paymentGateway = new ITPrismPaymentMollieIdeal($partnerId);
            
            // Enable test mode
            if($pluginParams->get('testmode', 1)) {
                $paymentGateway->enableTestmode();
            }
            
            // Get project
            jimport("crowdfunding.project");
            $project    = CrowdFundingProject::getInstance($projectId);
            
            if(!$project->id) {
                $response = array(
                    "success" => false,
                    "title"   => JText::_("COM_CROWDFUNDING_FAIL"),
                    "text"    => JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT")
                );
                
                echo json_encode($response);
                JFactory::getApplication()->close();
            }
            
            // Prepare description
            $paymentOptions["description"] = JString::substr($project->title, 0, 29);
            
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
            $userId   = JFactory::getUser()->id;
            $rewardId = $app->input->get("reward_id");
            
            // Get intention and store the data,
            // which is needed for transaction validation.
            $intentionKeys = array(
                "user_id"         => $userId,
                "project_id"      => $projectId,
            );
            
            jimport("crowdfunding.intention");
            $intention = new CrowdFundingIntention($intentionKeys);
            
            // Update intention data.
            $intentionData = array(
                "txn_id"        => $txnId,
                "gateway"       => "Mollie iDEAL",
            );
            
            // Set main data if it is a new intention. 
            if(!$intention->getId()) {
                
                $recordDate = new JDate();
                
                $intentionData["user_id"] = $userId;
                $intentionData["project_id"] = $projectId;
                $intentionData["reward_id"] = $rewardId;
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
     * @todo validate reward ( availableility, correct project,.. )
     */
    public function banktransfer() {
    
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Get plugin parameters
        $plugin       = JPluginHelper::getPlugin('crowdfundingpayment', 'banktransfer');
        $pluginParams = new JRegistry($plugin->params);
        
        // Get component parameters
        $componentHelper = JComponentHelper::getParams("com_crowdfunding");
        
        $projectId    = $app->input->getInt("project_id");
        $amount       = $app->input->getFloat("amount");
        $userId       = JFactory::getUser()->id;
        
        $uri          = JUri::getInstance();
    
        // Save data
        try {
            
            // Get project
            jimport("crowdfunding.project");
            $project    = CrowdFundingProject::getInstance($projectId);
    
            if(!$project->id) {
                $response = array(
                    "success" => false,
                    "title"   => JText::_("COM_CROWDFUNDING_FAIL"),
                    "text"    => JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT")
                );
    
                echo json_encode($response);
                JFactory::getApplication()->close();
            }
            
            jimport("crowdfunding.currency");
            $currencyId = $componentHelper->get("project_currency");
            $currency   = CrowdFundingCurrency::getInstance($currencyId);
            
            // Prepare return URL
            $returnUrl = JString::trim($pluginParams->get('return_url'));
            if(!$returnUrl) {
                $returnUrl = $uri->toString(array("scheme", "host")).JRoute::_(CrowdFundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatslug(), "share"), false);
            }
    
            // Get intention and store the data,
            // which is needed for transaction validation.
            $intentionKeys = array(
                "user_id"         => $userId,
                "project_id"      => $projectId,
            );
    
            jimport("crowdfunding.intention");
            $intention = new CrowdFundingIntention($intentionKeys);
    
            // Set main data if it is a new intention.
            if(!$intention->id) {
    
                $response = array(
                    "success" => false,
                    "title"   => JText::_("COM_CROWDFUNDING_FAIL"),
                    "text"    => JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT")
                );
                
                echo json_encode($response);
                JFactory::getApplication()->close();
    
            }
    
            jimport("itprism.string");
            $transactionData = array(
                "txn_amount"   => $amount,
                "txn_currency" => $currency->abbr,
                "txn_status"   => "pending",
                "txn_id"       => JString::strtoupper(ITPrismString::generateRandomString(12, "BT")),
                "project_id"   => $projectId,
                "reward_id"    => $intention->reward_id,
                "investor_id"  => $userId,
                "receiver_id"  => $project->user_id,
                "service_provider"  => "Bank Transfer"
            );
            
            jimport("crowdfunding.transaction");
            $transaction = new CrowdFundingTransaction();
            $transaction->bind($transactionData);
            
            $transaction->store();
            
            // Remove intention
            $intention->delete();
            
            // Initialize step one
            $projectContext = "com_crowdfunding.backing.project".$projectId;
            $app->setUserState($projectContext.".step1", false);
    
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
