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
jimport('joomla.application.component.view');

class CrowdFundingViewBacking extends JViewLegacy {
    
    protected $state;
    protected $item;
    protected $params;
    
    protected $option;
    protected $layoutsBasePath;
    
    protected $paymentProcessContext;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
        
        $this->layoutsBasePath = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR."/layouts");
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
            $app->redirect(JRoute::_('index.php?option=com_crowdfunding&view=discover', false));
            return;
        }
        
        // Create an object that will contain the data during the payment process.
        $this->paymentProcessContext = CrowdFundingConstants::PAYMENT_PROCESS_CONTEXT.$this->item->id;
        $paymentProcess              = $app->getUserState($this->paymentProcessContext);
        
        if(!$paymentProcess) {
            $paymentProcess         = new JData();
            $paymentProcess->step1  = false;
        }
        
        // Images
        $this->imageFolder       = $this->params->get("images_directory", "images/crowdfunding");
        
        // Include HTML helper
        JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
        
        // Get currency
		jimport("crowdfunding.currency");
        $currencyId              = $this->params->get("project_currency");
        $this->currency          = CrowdFundingCurrency::getInstance($currencyId);
		
        // Set a link that points to project page
        $host  = JUri::getInstance()->toString(array("scheme", "host"));
        $this->item->link        = $host.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug), false);
        
        // Set a link to image
        $this->item->link_image  = $host."/".$this->imageFolder."/".$this->item->image;
        
        $this->layout      = $this->getLayout();
        
        switch($this->layout) {
            
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
	    
        $this->prepareDebugMode($paymentProcess);
		$this->prepareDocument();
		
		$this->version = new CrowdFundingVersion();
		
        parent::display($tpl);
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
            $app->redirect(JRoute::_("index.php?option=com_crowdfunding&view=backing&id=".(int)$this->item->id, false));
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
            
            $app->redirect(JRoute::_("index.php?option=com_crowdfunding&view=backing&id=".(int)$this->item->id, false));
        }
        
        if(!$this->item->days_left) {
            
            // Reset the flag for step 1
            $paymentProcess->step1 = false;
            $app->setUserState($this->paymentProcessContext, $paymentProcess);
            
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_PROJECT_COMPLETED"), "notice");
            $app->redirect(JRoute::_("index.php?option=com_crowdfunding&view=backing&id=".(int)$this->item->id, false));
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
                $app->redirect(JRoute::_("index.php?option=com_crowdfunding&view=backing&id=".(int)$this->item->id, false));
            }
        }
        
        // Validate amount
        $this->amount       = $paymentProcess->amount;
        if(!$this->amount) {
            
            // Reset the flag for step 1
            $paymentProcess->step1 = false;
            $app->setUserState($this->paymentProcessContext, $paymentProcess);
            
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"), "notice");
            $app->redirect(JRoute::_("index.php?option=com_crowdfunding&view=backing&id=".(int)$this->item->id, false));
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
        $dispatcher	        = JDispatcher::getInstance();
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
        $dispatcher	       = JDispatcher::getInstance();
        
        $offset            = 0;
    
        $results           = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.payment.share', &$this->item, &$this->params, $offset));
        
        $this->item->event = new stdClass();
        $this->item->event->afterDisplayContent = trim(implode("\n", $results));
    
        // Reset anonymous hash user ID.
        $app->setUserState("auser_id", "");
        
        // Store the new values of the payment process to the user sesstion.
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
    
    private function prepearePageHeading() {
        
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
    
    private function prepearePageTitle() {
        
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