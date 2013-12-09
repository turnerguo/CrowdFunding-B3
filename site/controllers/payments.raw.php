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
   
    protected   $log;
    protected   $logFile = "controller_payments_raw.php";
    
    public function __construct($config = array()) {
    
        parent::__construct($config);
        
        $file = JPath::clean(JFactory::getApplication()->getCfg("log_path") .DIRECTORY_SEPARATOR. $this->logFile);
        
        $this->log = new CrowdFundingLog();
        $this->log->addWriter(new CrowdFundingLogWriterDatabase(JFactory::getDbo()));
        $this->log->addWriter(new CrowdFundingLogWriterFile($file));
        
    }
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

    public function preparePaymentAjax() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        jimport('itprism.response.json');
        $response = new ITPrismResponseJson();
        
        // Get component parameters
        $params = JComponentHelper::getParams("com_crowdfunding");
        
        // Check for disabled payment functionality
        if($params->get("debug_payment_disabled", 0)) {
        
            // Send response to the browser
            $response = array(
                "success" => false,
                "title"   => JText::_("COM_CROWDFUNDING_FAIL"),
                "text"    => JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE")
            );
        
            echo json_encode($response);
            JFactory::getApplication()->close();
        }
        
        $output         = array();
        $paymentService = $app->input->get("payment_service");
        
        // Trigger the event
        try {
            
            $context = 'com_crowdfunding.preparepayment.'.JString::strtolower($paymentService);
            
            // Import CrowdFunding Payment Plugins
            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('crowdfundingpayment');

            // Trigger onContentPreparePayment event.
            $results    = $dispatcher->trigger("onContentPreparePayment", array($context, $params));
            
            // Get the result, that comes from the plugin.
            if(!empty($results)) {
                foreach($results as $result) {
                    if(!is_null($result) AND is_array($result)) {
                        $output = &$result; 
                        break;
                    }
                }
            }
            
        } catch (Exception $e) {
        
            // Store log data in the database
            $this->log->add(
                JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"),
                "BANKTRANSFER_PAYMENT_PLUGIN_ERROR",
                $e->getMessage()
            );
        
            // Send response to the browser
            $response
                ->failure()
                ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                ->setText(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"));
            
            echo $response;
            JFactory::getApplication()->close();
            
        }
        
        // Send response to the browser
        $response
            ->success()
            ->setTitle(JArrayHelper::getValue($output, "title"))
            ->setText(JArrayHelper::getValue($output, "text"))
            ->setData(JArrayHelper::getValue($output, "data"));
        
        echo $response;
        JFactory::getApplication()->close();
    }
    
}
