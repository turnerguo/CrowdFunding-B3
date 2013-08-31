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
 * This controller receives requests from the payment gateways.
 * 
 * @package		CrowdFunding
 * @subpackage	Payments
 */
class CrowdFundingControllerNotifier extends JControllerLegacy {
   
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
    public function getModel($name = 'Notifier', $prefix = '', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Catch the response from PayPal and store data about transaction
     */
    public function notify() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $params    = $app->getParams("com_crowdfunding");
        
        // Check for disabled payment functionality
        if($params->get("debug_payment_disabled", 0)) {
            $error  = JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED");
            $error .= "\n". JText::sprintf("COM_CROWDFUNDING_TRANSACTION_DATA", var_export($_REQUEST, true));
			JLog::add($error);
			return null;
        }
        
        // Save data
        try {
            
            // Events
            $dispatcher	       = JDispatcher::getInstance();
            
            // Event Notify
            JPluginHelper::importPlugin('crowdfundingpayment');
            $results     = $dispatcher->trigger('onPaymenNotify', array('com_crowdfunding.notify', $params));
            
            $transaction = null;
            $project     = null;
            $reward      = null;
            $paymentGateway = null;
            
            if(!empty($results)) {
                foreach($results as $result) {
                    if(!empty($result) AND isset($result["transaction"])) {
                        $transaction    = JArrayHelper::getValue($result, "transaction");
                        $project        = JArrayHelper::getValue($results[0], "project");
                        $reward         = JArrayHelper::getValue($results[0], "reward");
                        $paymentGateway = JArrayHelper::getValue($results[0], "payment_service");
                        break;
                    }
                }
            }
            
            // If there is no transaction data, the status might be pending or another one.
            // So, we have to stop the script execution.
            if(empty($transaction)) {
                return;
            }
            
            // Clear the name of the payment gateway.
            $filter = new JFilterInput();
            $paymentGatewayClear = JString::strtolower($filter->clean($paymentGateway, ALNUM));
            
            // Event After Payment
            JPluginHelper::importPlugin('crowdfundingpayment');
            $dispatcher->trigger('onAfterPayment', array('com_crowdfunding.notify.'.$paymentGatewayClear, &$transaction, $params, $project, $reward));
        		
        } catch (Exception $e) {
            
            JLog::add($e->getMessage());
            $input = "INPUT:" .var_export($app->input, true)."\n";
            JLog::add($input);
            
            // Send notification about the error to the administrator.
            $model = $this->getModel();
            $model->sendMailToAdministrator();
            
        }
        
    }
    
}
