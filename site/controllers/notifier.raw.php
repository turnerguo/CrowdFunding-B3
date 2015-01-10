<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * This controller receives requests from the payment gateways.
 *
 * @package        CrowdFunding
 * @subpackage     Payments
 */
class CrowdFundingControllerNotifier extends JControllerLegacy
{
    protected $log;

    protected $paymentProcessContext;
    protected $paymentProcess;

    protected $projectId;
    protected $context;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get project id.
        $this->projectId = $this->input->getUint("pid");

        // Prepare log object
        $registry = JRegistry::getInstance("com_crowdfunding");
        /** @var  $registry Joomla\Registry\Registry */

        $fileName  = $registry->get("logger.file");
        $tableName = $registry->get("logger.table");

        $file = JPath::clean(JFactory::getApplication()->get("log_path") . DIRECTORY_SEPARATOR . $fileName);

        $this->log = new ITPrismLog();
        $this->log->addWriter(new ITPrismLogWriterDatabase(JFactory::getDbo(), $tableName));
        $this->log->addWriter(new ITPrismLogWriterFile($file));

        // Create an object that contains a data used during the payment process.
        $this->paymentProcessContext = CrowdFundingConstants::PAYMENT_SESSION_CONTEXT . $this->projectId;
        $this->paymentProcess        = $app->getUserState($this->paymentProcessContext);

        // Prepare context
        $filter         = new JFilterInput();
        $paymentService = JString::trim(JString::strtolower($this->input->getCmd("payment_service")));
        $paymentService = $filter->clean($paymentService, "ALNUM");
        $this->context  = (!empty($paymentService)) ? 'com_crowdfunding.notify.' . $paymentService : 'com_crowdfunding.notify';

        // Prepare params
        $this->params = JComponentHelper::getParams("com_crowdfunding");
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    CrowdFundingModelNotifier    The model.
     * @since    1.5
     */
    public function getModel($name = 'Notifier', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Catch a response from payment service and store data about transaction.
     */
    public function notify()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Check for disabled payment functionality
        if ($this->params->get("debug_payment_disabled", 0)) {
            $error = JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED");
            $error .= "\n" . JText::sprintf("COM_CROWDFUNDING_TRANSACTION_DATA", var_export($_REQUEST, true));
            $this->log->add($error, "CONTROLLER_NOTIFIER_AJAX_ERROR");
            return;
        }

        // Get model object.
        $model = $this->getModel();

        $transaction    = null;
        $project        = null;
        $reward         = null;
        $paymentSession = null;

        // Save data
        try {

            // Events
            $dispatcher = JEventDispatcher::getInstance();

            // Event Notify
            JPluginHelper::importPlugin('crowdfundingpayment');
            $results = $dispatcher->trigger('onPaymentNotify', array($this->context, &$this->params));

            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!empty($result) and isset($result["transaction"])) {
                        $transaction    = JArrayHelper::getValue($result, "transaction");
                        $project        = JArrayHelper::getValue($result, "project");
                        $reward         = JArrayHelper::getValue($result, "reward");
                        $paymentSession = JArrayHelper::getValue($result, "payment_session");
                        break;
                    }
                }
            }

            // If there is no transaction data, the status might be pending or another one.
            // So, we have to stop the script execution.
            if (empty($transaction)) {

                // Remove the record of the payment session from database.
                $model->closePaymentSession($paymentSession);

                return;
            }

            // Event After Payment
            $dispatcher->trigger('onAfterPayment', array($this->context, &$transaction, &$this->params, &$project, &$reward, &$paymentSession));

        } catch (Exception $e) {

            $error = "ERROR MESSAGE: " .$e->getMessage() ."\n";
            $error .= "INPUT:" . var_export($app->input, true) . "\n";
            $this->log->add($error, "CONTROLLER_NOTIFIER_AJAX_ERROR");

            // Send notification about the error to the administrator.
            $model = $this->getModel();
            $model->sendMailToAdministrator();

        }

        // Remove the record of the payment session from database.
        $model = $this->getModel();
        $model->closePaymentSession($paymentSession);
    }

    /**
     * Catch a request from payment plugin via AJAX and process a transaction.
     */
    public function notifyAjax()
    {
        jimport('itprism.response.json');
        $response = new ITPrismResponseJson();

        // Check for disabled payment functionality
        if ($this->params->get("debug_payment_disabled", 0)) {

            // Log the error.
            $error  = JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED") ."\n";
            $error .= JText::sprintf("COM_CROWDFUNDING_TRANSACTION_DATA", var_export($_REQUEST, true));
            $this->log->add($error, "CONTROLLER_NOTIFIER_AJAX_ERROR");

            // Send response to the browser
            $response
                ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                ->setText(JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE"))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        // Get model object.
        $model = $this->getModel();

        $transaction    = null;
        $project        = null;
        $reward         = null;
        $paymentSession = null;
        $redirectUrl    = null;
        $message        = null;

        // Trigger the event
        try {

            // Import CrowdFunding Payment Plugins
            JPluginHelper::importPlugin('crowdfundingpayment');

            // Trigger onPaymentNotify event.
            $dispatcher = JEventDispatcher::getInstance();
            $results    = $dispatcher->trigger("onPaymentNotify", array($this->context, &$this->params));

            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!empty($result) and isset($result["transaction"])) {
                        $transaction        = JArrayHelper::getValue($result, "transaction");
                        $project            = JArrayHelper::getValue($result, "project");
                        $reward             = JArrayHelper::getValue($result, "reward");
                        $paymentSession     = JArrayHelper::getValue($result, "payment_session");
                        $redirectUrl        = JArrayHelper::getValue($result, "redirect_url");
                        $message            = JArrayHelper::getValue($result, "message");
                        break;
                    }
                }
            }

            // If there is no transaction data, the status might be pending or another one.
            // So, we have to stop the script execution.
            if (!$transaction) {

                // Remove the record of the payment session from database.
                $model->closePaymentSession($paymentSession);

                // Send response to the browser
                $response
                    ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                    ->setText(JText::_("COM_CROWDFUNDING_TRANSACTION_NOT_PROCESSED_SUCCESSFULLY"))
                    ->failure();

                echo $response;
                JFactory::getApplication()->close();
            }

            // Trigger the event onAfterPayment
            $dispatcher->trigger('onAfterPayment', array($this->context, &$transaction, &$this->params, &$project, &$reward, &$paymentSession));

            // Remove the record of the payment session from database.
            $model->closePaymentSession($paymentSession);

        } catch (Exception $e) {

            // Store log data to the database.
            $this->log->add(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"), "CONTROLLER_NOTIFIER_AJAX_ERROR", $e->getMessage());

            // Remove the record of the payment session from database.
            $model->closePaymentSession($paymentSession);

            // Send response to the browser
            $response
                ->failure()
                ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                ->setText(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"));

            // Send notification about the error to the administrator.
            $model->sendMailToAdministrator();

            echo $response;
            JFactory::getApplication()->close();

        }

        // Generate redirect URL
        if (!$redirectUrl) {
            $uri         = JUri::getInstance();
            $redirectUrl = $uri->toString(array("scheme", "host")) . JRoute::_(CrowdFundingHelperRoute::getBackingRoute($project->slug, $project->catslug, "share"));
        }

        if (!$message) {
            $message = JText::_("COM_CROWDFUNDING_TRANSACTION_PROCESSED_SUCCESSFULLY");
        }

        // Send response to the browser
        $response
            ->success()
            ->setTitle(JText::_("COM_CROWDFUNDING_SUCCESS"))
            ->setText($message)
            ->setRedirectUrl($redirectUrl);

        echo $response;
        JFactory::getApplication()->close();
    }
}
