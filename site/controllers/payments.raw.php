<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
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
    
    protected   $paymentProcessContext;
    protected   $paymentProcess;
    
    protected   $projectId;
    
    public function __construct($config = array()) {
    
        parent::__construct($config);
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Get project id.
        $this->projectId = $app->input->getUint("pid");
        
        // Prepare log object
        $registry = JRegistry::getInstance("com_crowdfunding");
        
        $fileName  = $registry->get("logger.file");
        $tableName = $registry->get("logger.table");
        
        $file      = JPath::clean(JFactory::getApplication()->getCfg("log_path") .DIRECTORY_SEPARATOR. $fileName);
        
        $this->log = new ITPrismLog();
        $this->log->addWriter(new ITPrismLogWriterDatabase(JFactory::getDbo(), $tableName));
        $this->log->addWriter(new ITPrismLogWriterFile($file));
        
        // Create an object that contains a data used during the payment process.
        $this->paymentProcessContext     = CrowdFundingConstants::PAYMENT_PROCESS_CONTEXT.$this->projectId;
        $this->paymentProcess            = $app->getUserState($this->paymentProcessContext);
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
            
            $item = $this->prepareItem($this->projectId, $params);
            
            $uri         = JURI::getInstance();
            $redirectUrl = $uri->toString(array("scheme", "host")).JRoute::_(CrowdFundingHelperRoute::getBackingRoute($item->id, $item->catid));
            
            $context = 'com_crowdfunding.payments.preparepayment.'.JString::strtolower($paymentService);
            
            // Import CrowdFunding Payment Plugins
            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('crowdfundingpayment');

            // Trigger onContentPreparePayment event.
            $results    = $dispatcher->trigger("onPaymentsPreparePayment", array($context, $params));
            
            // Get the result, that comes from the plugin.
            if(!empty($results)) {
                foreach($results as $result) {
                    if(!is_null($result) AND is_array($result)) {
                        $output = &$result; 
                        break;
                    }
                }
            }
            
        } catch (UnexpectedValueException $e) {
        
            // Send response to the browser
            $response
                ->failure()
                ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                ->setText($e->getMessage())
                ->setRedirectUrl($redirectUrl);
            
            echo $response;
            JFactory::getApplication()->close();
            
        } catch (Exception $e) {
        
            // Store log data in the database
            $this->log->add(
                JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"),
                "CONTROLLER_PAYMENTS_RAW_ERROR",
                $e->getMessage()
            );
        
            // Send response to the browser
            $response
                ->failure()
                ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                ->setText(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"))
                ->setRedirectUrl($redirectUrl);
            
            echo $response;
            JFactory::getApplication()->close();
            
        }
        
        // Check the response
        $success = JArrayHelper::getValue($output, "success");
        if(!$success) { // If there is an error...
            
            // Initialize the payment process object.
            $paymentProcess           = new JData();
            $paymentProcess->step1    = false;
            $app->setUserState($this->paymentProcessContext, $paymentProcess);
            
            $uri         = JURI::getInstance();
            $redirectUrl = $uri->toString(array("scheme", "host")).JRoute::_(CrowdFundingHelperRoute::getBackingRoute($item->id, $item->catid));
            
            // Send response to the browser
            $response
                ->failure()
                ->setTitle(JArrayHelper::getValue($output, "title"))
                ->setText(JArrayHelper::getValue($output, "text"))
                ->setData(JArrayHelper::getValue($output, "data"))
                ->setRedirectUrl($redirectUrl);
            
        } else { // If all is OK...
            
            // Send response to the browser
            $response
                ->success()
                ->setTitle(JArrayHelper::getValue($output, "title"))
                ->setText(JArrayHelper::getValue($output, "text"))
                ->setData(JArrayHelper::getValue($output, "data"));
            
        }
        
        echo $response;
        JFactory::getApplication()->close();
    }
    
    protected function prepareItem($projectId, $params) {
    
        jimport("crowdfunding.project");
        $project    = new CrowdFundingProject($projectId);
        if(!$project->getId()) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"));
        }
    
        if($project->isCompleted()) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_COMPLETED_PROJECT"));
        }
    
        // Get currency
        jimport("crowdfunding.currency");
        $currencyId         = $params->get("project_currency");
        $this->currency     = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId);
    
        $item               = new stdClass();
    
        $item->id           = $project->getId();
        $item->catid        = $project->getCategoryId();
        $item->title        = $project->getTitle();
        $item->slug         = $project->getSlug();
        $item->catslug      = $project->getCatSlug();
        $item->rewardId     = $this->paymentProcess->rewardId;
        $item->amount       = $this->paymentProcess->amount;
        $item->currency     = $this->currency->getAbbr();
    
        return $item;
    }
    
}
