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

class CrowdFundingViewBacking extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    protected $item;

    /**
     * @var CrowdFundingCurrency
     */
    protected $currency;

    protected $imageFolder;

    protected $layout;
    protected $rewardsEnabled;
    protected $disabledButton;
    protected $loginForm;
    protected $returnUrl;
    protected $layoutData;
    protected $rewardId;
    protected $rewards;
    protected $rewardAmount;
    protected $reward;
    protected $paymentAmount;

    /**
     * @var CrowdFundingAmount
     */
    protected $amount;

    protected $option;
    protected $layoutsBasePath;

    protected $paymentSessionContext;
    protected $paymentSession;
    
    protected $wizardType;
    protected $event;
    protected $secondStepTask;
    protected $fourSteps;

    protected $pageclass_sfx;

    /**
     * @var JApplicationSite
     */
    protected $app;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->app    = JFactory::getApplication();

        $this->option = $this->app->input->get("option");

        $this->layoutsBasePath = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "layouts");

    }

    public function display($tpl = null)
    {
        // Get model state.
        $this->state  = $this->get('State');
        $this->item   = $this->get("Item");

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        if (!$this->item) {
            $this->app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $this->app->redirect(JRoute::_(CrowdFundingHelperRoute::getDiscoverRoute(), false));
            return;
        }

        // Create an object that will contain the data during the payment process.
        $this->paymentSessionContext = CrowdFundingConstants::PAYMENT_SESSION_CONTEXT . $this->item->id;
        $paymentSession              = $this->app->getUserState($this->paymentSessionContext);

        // Create payment session object.
        if (!$paymentSession) {
            $paymentSession        = new JData();
            $paymentSession->step1 = false;
        }

        // Images
        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");

        // Get currency
        $currencyId     = $this->params->get("project_currency");
        $this->currency = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId, $this->params);

        $this->amount   = new CrowdFundingAmount();
        $this->amount->setCurrency($this->currency);

        // Set a link that points to project page
        $filter    = JFilterInput::getInstance();
        $host      = JUri::getInstance()->toString(array('scheme', 'host'));
        $host      = $filter->clean($host);

        $this->item->link =  $host . JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug), false);

        // Set a link to image
        $this->item->link_image = $host . "/" . $this->imageFolder . "/" . $this->item->image;

        // Get params
        $params = JComponentHelper::getParams("com_crowdfunding");

        // Get wizard type
        $this->wizardType = $params->get("backing_wizard_type", "three_steps");
        $this->fourSteps  = (strcmp("four_steps", $this->wizardType) != 0) ? false : true;

        // Import "crowdfundingpayment" plugins.
        JPluginHelper::importPlugin('crowdfundingpayment');

        $this->layout = $this->getLayout();

        switch ($this->layout) {

            case "step2":
                $this->prepareStep2();
                break;

            case "payment":
                $this->preparePayment($paymentSession);
                break;

            case "share":
                $this->prepareShare($paymentSession);
                break;

            default: //  Pledge and Rewards
                $this->prepareRewards($paymentSession);
                break;
        }

        // Get project type and check for enabled rewards.
        $this->rewardsEnabled = true;

        if (!empty($this->item->type_id)) {
            jimport("crowdfunding.type");
            $type = new CrowdFundingType(JFactory::getDbo());

            $type->load($this->item->type_id);

            if ($type->getId() and !$type->isRewardsEnabled()) {
                $this->rewardsEnabled = false;
            }
        }

        // Check days left. If there is no days, disable the button.
        $this->disabledButton = "";
        if (!$this->item->days_left) {
            $this->disabledButton = 'disabled="disabled"';
        }

        $this->paymentSession = $paymentSession;

        // Prepare the data of the layout
        $this->layoutData = new JData(array(
            "layout"         => $this->layout,
            "item"           => $this->item,
            "paymentSession" => $paymentSession,
        ));

        $this->prepareDebugMode($paymentSession);
        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * This method displays a content from a CrowdFunding Plugin.
     */
    protected function prepareStep2()
    {
        // Trigger the event on step 2 and display the content.
        $dispatcher = JEventDispatcher::getInstance();
        $results    = $dispatcher->trigger('onPaymentDisplay', array('com_crowdfunding.payment.step2', &$this->item, &$this->params));

        $result                 = (string)array_pop($results);

        $this->event            = new stdClass();
        $this->event->onDisplay = JString::trim($result);
    }

    protected function prepareRewards(&$paymentSession)
    {
        // Create payment session ID.
        jimport("itprism.string");
        $sessionId = new ITPrismString();
        $sessionId->generateRandomString(32);

        $paymentSession->session_id = (string)$sessionId;

        // Get selected reward ID
        $this->rewardId = $this->state->get("reward_id");

        // If it has been selected another reward, set the old one to 0.
        if ($this->rewardId != $paymentSession->rewardId) {
            $paymentSession->rewardId = 0;
            $paymentSession->step1    = false;
        }

        // Get amount from session
        $this->rewardAmount = (!$paymentSession->amount) ? 0 : $paymentSession->amount;

        // Get rewards
        jimport("crowdfunding.rewards");
        $this->rewards = new CrowdFundingRewards(JFactory::getDbo());
        $this->rewards->load($this->item->id, array("state" => 1));

        // Compare amount with the amount of reward, that is selected.
        // If the amount of selected reward is larger than amount from session,
        // use the amount of selected reward.
        if (!empty($this->rewardId)) {
            foreach ($this->rewards as $reward) {
                if ($this->rewardId == $reward["id"]) {

                    if ($this->rewardAmount < $reward["amount"]) {
                        $this->rewardAmount = $reward["amount"];

                        $paymentSession->step1 = false;
                    }

                    break;
                }
            }
        }

        // Store the new values of the payment process to the user session.
        $this->app->setUserState($this->paymentSessionContext, $paymentSession);

        if (!$this->fourSteps) {
            $this->secondStepTask = "backing.process";
        } else {
            $this->secondStepTask = "backing.step2";
        }
    }

    protected function preparePayment(&$paymentSession)
    {
        // If missing the flag "step1", redirect to first step.
        if (!$paymentSession->step1) {
            $this->app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"), "notice");
            $this->app->redirect(JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
        }

        // Check for both user states. The user must have only one state, registered or anonymous.
        $userId  = JFactory::getUser()->get("id");
        $aUserId = $this->app->getUserState("auser_id");

        if ((!empty($userId) and !empty($aUserId)) or (empty($userId) and empty($aUserId))) {

            // Reset anonymous hash user ID and redirect to first step.
            $this->app->setUserState("auser_id", "");

            // Reset the flag for step 1
            $paymentSession->step1 = false;
            $this->app->setUserState($this->paymentSessionContext, $paymentSession);

            $this->app->redirect(JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
        }

        if (!$this->item->days_left) {

            // Reset the flag for step 1
            $paymentSession->step1 = false;
            $this->app->setUserState($this->paymentSessionContext, $paymentSession);

            $this->app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_PROJECT_COMPLETED"), "notice");
            $this->app->redirect(JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
        }

        // Validate reward
        $this->reward = null;
        $keys         = array(
            "id"         => $paymentSession->rewardId,
            "project_id" => $this->item->id
        );

        jimport("crowdfunding.reward");
        $this->reward = new CrowdFundingReward(JFactory::getDbo());
        $this->reward->load($keys);

        if ($this->reward->getId()) {
            if ($this->reward->isLimited() and !$this->reward->getAvailable()) {

                // Reset the flag for step 1
                $paymentSession->step1 = false;
                $this->app->setUserState($this->paymentSessionContext, $paymentSession);

                $this->app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_REWARD_NOT_AVAILABLE"), "notice");
                $this->app->redirect(JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
            }
        }

        // Set the amount that will be displayed in the view.
        $this->paymentAmount = $paymentSession->amount;

        // Validate the amount.
        if (!$this->paymentAmount) {

            // Reset the flag for step 1
            $paymentSession->step1 = false;
            $this->app->setUserState($this->paymentSessionContext, $paymentSession);

            $this->app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"), "notice");
            $this->app->redirect(JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
        }

        $item = new stdClass();

        $item->id           = $this->item->id;
        $item->title        = $this->item->title;
        $item->slug         = $this->item->slug;
        $item->catslug      = $this->item->catslug;
        $item->rewardId     = $paymentSession->rewardId;
        $item->amount       = $paymentSession->amount;
        $item->currencyCode = $this->currency->getAbbr();

        // Events
        JPluginHelper::importPlugin('crowdfundingpayment');
        $dispatcher = JEventDispatcher::getInstance();
        $results    = $dispatcher->trigger('onProjectPayment', array('com_crowdfunding.payment', &$item, &$this->params));

        $this->item->event                   = new stdClass();
        $this->item->event->onProjectPayment = trim(implode("\n", $results));
    }

    protected function prepareShare(&$paymentSession)
    {
        // Get amount from session that will be displayed in the view.
        $this->paymentAmount = $paymentSession->amount;

        // Get reward
        $this->reward = null;
        if (!empty($paymentSession->rewardId)) {

            $keys = array(
                "id"         => $paymentSession->rewardId,
                "project_id" => $this->item->id
            );

            jimport("crowdfunding.reward");
            $this->reward = new CrowdFundingReward(JFactory::getDbo());
            $this->reward->load($keys);
        }

        // Events
        JPluginHelper::importPlugin('content');
        $dispatcher = JEventDispatcher::getInstance();

        $offset = 0;

        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.payment.share', &$this->item, &$this->params, $offset));

        $this->item->event                      = new stdClass();
        $this->item->event->afterDisplayContent = trim(implode("\n", $results));

        // Reset anonymous hash user ID.
        $this->app->setUserState("auser_id", "");

        // Initialize the payment process object.
        $paymentSession        = new JData();
        $paymentSession->step1 = false;
        $this->app->setUserState($this->paymentSessionContext, $paymentSession);
    }

    /**
     * Check the system for debug mode
     */
    protected function prepareDebugMode(&$paymentSession)
    {
        // Check for maintenance (debug) state
        $params = $this->state->get("params");
        if ($params->get("debug_payment_disabled", 0)) {
            $msg = JString::trim($params->get("debug_disabled_functionality_msg"));
            if (!$msg) {
                $msg = JText::_("COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG");
            }
            $this->app->enqueueMessage($msg, "notice");

            $this->disabledButton = 'disabled="disabled"';

            // Store the new values of the payment process to the user sesstion.
            $paymentSession->step1 = false;
            $this->app->setUserState($this->paymentSessionContext, $paymentSession);
        }

    }

    /**
     * Prepare the document
     */
    protected function prepareDocument()
    {
        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            $this->document->setDescription($this->item->short_desc);
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        // Breadcrumb
        $pathway           = $this->app->getPathWay();
        $currentBreadcrumb = JHtmlString::truncate($this->item->title, 16);
        $pathway->addItem($currentBreadcrumb, '');

        // Scripts
        JHtml::_('bootstrap.framework');
        $this->document->addScript('media/' . $this->option . '/js/site/backing.js');
    }

    protected function preparePageHeading()
    {
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $this->app->getMenu();
        $menu  = $menus->getActive();

        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_BACKING_DEFAULT_PAGE_TITLE', $this->item->title));
        }

    }

    protected function preparePageTitle()
    {
        // Prepare page title
//        $title = $this->params->get('page_title', $this->item->title);
        $title = JText::sprintf("COM_CROWDFUNDING_INVESTING_IN", $this->escape($this->item->title));

        switch ($this->getLayout()) {

            case "payment":
                $title .= " | " . JText::_("COM_CROWDFUNDING_PAYMENT_METHODS");
                break;

            case "share":
                $title .= " | " . JText::_("COM_CROWDFUNDING_SHARE");
                break;

        }

        // Add title before or after Site Name
        if (!$title) {
            $title = $this->app->get('sitename');
        } elseif ($this->app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
        } elseif ($this->app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
        }

        $this->document->setTitle($title);
    }
}
