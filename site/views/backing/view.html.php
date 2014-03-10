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
jimport('joomla.application.component.view');

class CrowdFundingViewBacking extends JViewLegacy {
    
    protected $state;
    protected $item;
    protected $params;
    
    protected $option;
    protected $layoutsBasePath;
    
    protected $paymentProcessContext;
    protected $wizardType;
    
    public function __construct($config) {
        
        parent::__construct($config);
        
        $this->option = JFactory::getApplication()->input->get("option");
        
        $this->layoutsBasePath = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR."layouts");
        
    }
    
    public function display($tpl = null) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Get model state.
        $this->state       = $this->get('State');
        $this->item        = $this->get("Item");
        $this->params      = $this->state->get("params");
        
        if (!$this->item) {
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $app->redirect(JRoute::_(CrowdFundingHelperRoute::getDiscoverRoute(), false));
            return;
        }
        
        // Create an object that will contain the data during the payment process.
        $this->paymentProcessContext = CrowdFundingConstants::PAYMENT_PROCESS_CONTEXT.$this->item->id;
        $paymentProcess              = $app->getUserState($this->paymentProcessContext);
        
        // Create payment process object.
        if(!$paymentProcess) {
            $paymentProcess         = new JData();
            $paymentProcess->step1  = false;
        }
        
        // Images
        $this->imageFolder       = $this->params->get("images_directory", "images/crowdfunding");
        
        // Get currency
		jimport("crowdfunding.currency");
        $currencyId              = $this->params->get("project_currency");
        $this->currency          = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId);
		
        // Set a link that points to project page
        $host  = JUri::getInstance()->toString(array("scheme", "host"));
        $this->item->link        = $host.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug), false);
        
        // Set a link to image
        $this->item->link_image  = $host."/".$this->imageFolder."/".$this->item->image;
        
        // Get params
        $params           = JComponentHelper::getParams("com_crowdfunding");
        
        // Get wizard type
        $this->wizardType = $params->get("backing_wizard_type", "three_steps");
        
        $this->layout     = $this->getLayout();
        
        switch($this->layout) {
            
            case "login":
                $this->prepareLogin($paymentProcess);
                break;
                
            case "payment":
                $this->preparePayment($paymentProcess);
                break;
                
            case "share":
                $this->prepareShare($paymentProcess);
                break;
                
            default: //  Pledge and Rewards 
                $this->prepareRewards($paymentProcess);
                break;
        }
        
        // Get project type and check for enabled rewards.
        $type = CrowdFundingHelper::getProjectType($this->item->type_id);
        $this->rewardsEnabled     = true;
        if(!is_null($type) AND !$type->isRewardsEnabled()){
            $this->rewardsEnabled = false;
        }
        
        // Check days left. If there is no days, disable the button.
        $this->disabledButton = "";
        if(!$this->item->days_left) {
	        $this->disabledButton = 'disabled="disabled"';
	    }
	    
	    $this->paymentProcess = $paymentProcess;
	    
	    // Prepare the data of the layout
	    $this->layoutData = new JData(array(
            "layout"         => $this->layout,
            "item"           => $this->item,
            "paymentProcess" => $paymentProcess,
	    ));
	    
	    $this->version    = new CrowdFundingVersion();
	    
        $this->prepareDebugMode($paymentProcess);
		$this->prepareDocument();
		
        parent::display($tpl);
    }
    
    protected function prepareLogin(&$paymentProcess) {
    
        $app = JFactory::getApplication();
        /** @var $app JSite **/
    
        // Check for both user states. The user must have only one state, registered or anonymous.
        $userId  = JFactory::getUser()->id;
        $aUserId = $app->getUserState("auser_id");
    
        if(!empty($userId)) {
            
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_LOGGED_IN"), "notice");
            
            $link = CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug);
            $app->redirect(JRoute::_($link, false));
        }
        
        // Get the form.
        JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
        JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
        
        $form = JForm::getInstance('com_users.login', 'login', array('load_data' => false), false, false);
        
        $this->loginForm = $form;
        
        $options = $app->getUserState("com_crowdfunding.backing.login");
        $this->returnUrl = "index.php?option=com_crowdfunding&task=backing.step1".CrowdFundingHelper::generateUrlParams($options);
        
    }
    
    protected function prepareRewards(&$paymentProcess) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Get selected reward ID
        $this->rewardId     = $this->state->get("reward_id");
        
        // If it has been selected another reward, set the old one to 0.
        if($this->rewardId != $paymentProcess->rewardId) {
            $paymentProcess->rewardId = 0;
            $paymentProcess->step1    = false;
        }
        
        // Get amount from session
        $this->rewardAmount    = (!$paymentProcess->amount) ? 0 : $paymentProcess->amount;

        // Get rewards
        jimport("crowdfunding.rewards");
        $this->rewards         = new CrowdFundingRewards($this->item->id, array("state" => 1));
        
        // Compare amount with the amount of reward, that is selected.
        // If the amount of selected reward is larger than amount from session, 
        // use the amount of selected reward  
        if(!empty($this->rewardId)) {
            foreach($this->rewards as $reward) {
                if($this->rewardId == $reward->id) {
                    
                    if($this->rewardAmount < $reward->amount) {
                        $this->rewardAmount = $reward->amount;
                        
                        $paymentProcess->step1 = false;
                    }
                    
                    break;
                } 
            }
        }
        
        // Store the new values of the payment process to the user sesstion.
        $app->setUserState($this->paymentProcessContext, $paymentProcess);
        
    }
    
    protected function preparePayment(&$paymentProcess) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // If missing the flag"step1", redirect to first step.
        if(!$paymentProcess->step1) {
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"), "notice");
            $app->redirect(JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
        }
        
        // Check for both user states. The user must have only one state, registered or anonymous.
        $userId  = JFactory::getUser()->id;
        $aUserId = $app->getUserState("auser_id");
        
        if( (!empty($userId) AND !empty($aUserId)) OR (empty($userId) AND empty($aUserId))) {
            
            // Reset anonymous hash user ID and redirect to first step.
            $app->setUserState("auser_id", "");
            
            // Reset the flag for step 1
            $paymentProcess->step1 = false;
            $app->setUserState($this->paymentProcessContext, $paymentProcess);
            
            $app->redirect(JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
        }
        
        if(!$this->item->days_left) {
            
            // Reset the flag for step 1
            $paymentProcess->step1 = false;
            $app->setUserState($this->paymentProcessContext, $paymentProcess);
            
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_PROJECT_COMPLETED"), "notice");
            $app->redirect(JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
        }
        
        // Validate reward
        $this->reward = null;
        $keys = array(
            "id"         => $paymentProcess->rewardId,
            "project_id" => $this->item->id
        );
        
        jimport("crowdfunding.reward");
        $this->reward       = new CrowdFundingReward($keys);
        if($this->reward->getId()) {
            if( $this->reward->isLimited() AND !$this->reward->getAvailable() ) {
                
                // Reset the flag for step 1
                $paymentProcess->step1 = false;
                $app->setUserState($this->paymentProcessContext, $paymentProcess);
            
                $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_REWARD_NOT_AVAILABLE"), "notice");
                $app->redirect(JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
                
            }
        }
        
        // Validate amount
        $this->amount       = $paymentProcess->amount;
        if(!$this->amount) {
            
            // Reset the flag for step 1
            $paymentProcess->step1 = false;
            $app->setUserState($this->paymentProcessContext, $paymentProcess);
            
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"), "notice");
            $app->redirect(JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
            
        }
        
        $item               = new stdClass();
        
        $item->id           = $this->item->id;
        $item->title        = $this->item->title;
        $item->slug         = $this->item->slug;
        $item->catslug      = $this->item->catslug;
        $item->rewardId     = $paymentProcess->rewardId;
        $item->amount       = $paymentProcess->amount;
        $item->currencyCode = $this->currency->getAbbr();
        
        // Events
        JPluginHelper::importPlugin('crowdfundingpayment');
        $dispatcher	        = JEventDispatcher::getInstance();
        $results            = $dispatcher->trigger('onProjectPayment', array('com_crowdfunding.payment', $item, $this->params));
        
        $this->item->event  = new stdClass();
		$this->item->event->onProjectPayment = trim(implode("\n", $results));
		
    }
    
    protected function prepareShare(&$paymentProcess) {
    
        $app = JFactory::getApplication();
        /** @var $app JSite **/
    
        // Get amount from session
        $this->amount      = $paymentProcess->amount;
    
        // Get reward
        $this->reward      = null;
        if(!empty($paymentProcess->rewardId)) {
            
            $keys = array(
                "id"         => $paymentProcess->rewardId,
                "project_id" => $this->item->id
            );
            
            jimport("crowdfunding.reward");
            $this->reward  = new CrowdFundingReward($keys);
        }
    
        // Events
        JPluginHelper::importPlugin('content');
        $dispatcher	       = JEventDispatcher::getInstance();
        
        $offset            = 0;
    
        $results           = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.payment.share', &$this->item, &$this->params, $offset));
        
        $this->item->event = new stdClass();
        $this->item->event->afterDisplayContent = trim(implode("\n", $results));
    
        // Reset anonymous hash user ID.
        $app->setUserState("auser_id", "");
        
        // Initialize the payment process object.
        $paymentProcess           = new JData();
        $paymentProcess->step1    = false;
        $app->setUserState($this->paymentProcessContext, $paymentProcess);
    }
    
    /**
     * Check the system for debug mode
     */
    protected function prepareDebugMode(&$paymentProcess) {
    
        $app = JFactory::getApplication();
        /** @var $app JSite **/
    
        // Check for maintenance (debug) state
        $params = $this->state->get("params");
        if($params->get("debug_payment_disabled", 0)) {
            $msg = JString::trim($params->get("debug_disabled_functionality_msg"));
            if(!$msg) {
                $msg = JText::_("COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG");
            }
            $app->enqueueMessage($msg, "notice");
    
            $this->disabledButton = 'disabled="disabled"';
            
            // Store the new values of the payment process to the user sesstion.
            $paymentProcess->step1 = false;
            $app->setUserState($this->paymentProcessContext, $paymentProcess);
        }
    
    }
    
    /**
     * Prepare the document
     */
    protected function prepareDocument() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
        
        // Prepare page heading
        $this->prepearePageHeading();
        
        // Prepare page heading
        $this->prepearePageTitle();
        
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
        
        // Load JHtmlString
        JLoader::register('JHtmlString', JPATH_LIBRARIES.'/joomla/html/html/string.php');
        
        // Breadcrumb
        $pathway = $app->getPathWay();
        $currentBreadcrumb = JHtmlString::truncate($this->item->title, 16);
        $pathway->addItem($currentBreadcrumb, '');
        
        // Styles
        $this->document->addStyleSheet( 'media/'.$this->option.'/css/site/style.css');
        
        // Scripts
        JHtml::_('bootstrap.framework');
        $this->document->addScript('media/'.$this->option.'/js/site/backing.js');
    }
    
    protected function prepearePageHeading() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $app->getMenu();
        $menu  = $menus->getActive();
        
        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_BACKING_DEFAULT_PAGE_TITLE', $this->item->title));
        }
    
    }
    
    protected function prepearePageTitle() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Prepare page title
//        $title = $this->params->get('page_title', $this->item->title);
        $title = JText::sprintf("COM_CROWDFUNDING_INVESTING_IN", $this->escape($this->item->title) );
        
        // Add title before or after Site Name
        if (!$title) {
            $title = $app->getCfg('sitename');
        } elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        } elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }
        
        $this->document->setTitle($title);
    
    }
    
}