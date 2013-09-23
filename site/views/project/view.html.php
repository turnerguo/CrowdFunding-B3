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

jimport('joomla.application.categories');
jimport('joomla.application.component.view');

class CrowdFundingViewProject extends JViewLegacy {
    
    protected $form       = null;
    protected $state      = null;
    protected $item       = null;
    
    protected $option     = null;
    
    public function __construct($config){
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->getCmd("option");
    }
    
    /**
     * Display the view
     *
     */
    public function display($tpl = null){
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $this->userId = JFactory::getUser()->id;
        if(!$this->userId) {
            $this->setLayout("intro");
        }
        
        // HTML Helpers
        JHtml::addIncludePath(ITPRISM_PATH_LIBRARY.'/ui/helpers');
        
        $this->layout = $this->getLayout();
        
        switch($this->layout) {
            
            case "rewards":
                $this->prepareRewards();
                break;
                
            case "story":
                $this->prepareStory();
                break;
                
            case "funding":
                $this->prepareFunding();
                break;

            case "intro":
                $this->prepareIntro();
                break;
                
            default: // Basic data for project
                $this->prepareBasic();
                break;
        }
        
        $this->version = new CrowdFundingVersion();
        
        $this->prepareDebugMode();
        $this->prepareDocument();
        
        parent::display($tpl);
    }
    
    /**
     * Check the system for debug mode
     */
    protected function prepareDebugMode() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/

        $this->disabledButton = "";
        
        // Check for maintenance (debug) state
        $params = $this->state->get("params");
        $this->debugMode = $params->get("debug_project_adding_disabled", 0);
        if($this->debugMode) {
            $msg = JString::trim($params->get("debug_disabled_functionality_msg"));
            if(!$msg) {
                $msg = JText::_("COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG");
            }
            $app->enqueueMessage($msg, "notice");
            
            $this->disabledButton = 'disabled="disabled"';
        }
        
    }
    
    /**
     * Display default page 
     */
    protected function prepareIntro() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $model             = JModelLegacy::getInstance("Intro", "CrowdFundingModel", $config = array('ignore_request' => false));
        $this->state       = $model->getState();
        $this->params      = $this->state->get("params");
        
        $articleId         = $this->params->get("project_intro_article", 0);
        $this->article     = $model->getItem($articleId);
        
        $this->pathwayName = JText::_("COM_CROWDFUNDING_START_PROJECT_BREADCRUMB");
        
    }
    
    protected function prepareBasic() {
        
        $model             = JModelLegacy::getInstance("Project", "CrowdFundingModel", $config = array('ignore_request' => false));
        
        $this->state       = $model->getState();
        $this->params      = $this->state->get("params");
        
        // Get item
        $itemId            = $this->state->get('project.id');
        $this->item        = $model->getItem($itemId, $this->userId);
        
        // Set a flag that describes the item as new.
        $this->isNew       = false;
        if(!$this->item->id) {
            $this->isNew = true;
        }
        
        $this->form        = $model->getForm();
            
        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");
        $this->imageSmall  = $this->item->get("image_small");
        
        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_BASIC");
        
    } 
    
    protected function prepareFunding() {
        
        $model             = JModelLegacy::getInstance("Funding", "CrowdFundingModel", $config = array('ignore_request' => false));
        
        // Initialise variables
        $this->state       = $model->getState();
        $this->params      = $this->state->get("params");
        
        // Get item
        $itemId            = $this->state->get('funding.id');
        $this->item        = $model->getItem($itemId, $this->userId);
        
        $this->form        = $model->getForm();
            
        // Get currency
        jimport("crowdfunding.currency");
        $currencyId        = $this->params->get("project_currency");
        $this->currency    = CrowdFundingCurrency::getInstance($currencyId);
        
        // Set minimum values - days, amount,...
        $this->minAmount   = $this->params->get("project_amount_minimum", 100);
        $this->maxAmount   = $this->params->get("project_amount_maximum");
        
        $this->minDays     = $this->params->get("project_days_minimum", 30);
        $this->maxDays     = $this->params->get("project_days_maximum");
        
        // Prepare funding duration type
        $this->prepareFundingDurationType();
        
        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_FUNDING");
        
    } 
    
    private function prepareFundingDurationType() {
        
        $this->fundingDuration     = $this->params->get("project_funding_duration");
        
        switch($this->fundingDuration) {
        
            case "days": // Only days type is enabled
                $this->checkedDays = 'checked="checked"';
                break;
        
            case "date": // Only date type is enabled
                $this->checkedDate = 'checked="checked"';
                break;
        
            default: // Both ( days and date ) types are enabled
        
                $this->checkedDays = 0;
                $this->checkedDate = "";
                
                if(!empty($this->item->funding_days)) {
                    $this->checkedDays = 'checked="checked"';
                    $this->checkedDate = '';
                } else if (CrowdFundingHelper::isValidDate($this->item->funding_end)) {
                    $this->checkedDays = '';
                    $this->checkedDate = 'checked="checked"';
                }
        
                // If missing both, select days
                if(!$this->checkedDays AND !$this->checkedDate) {
                    $this->checkedDays = 'checked="checked"';
                }
                break;
        
        }
    }
    
    protected function prepareStory() {
        
        $model             = JModelLegacy::getInstance("Story", "CrowdFundingModel", $config = array('ignore_request' => false));
        
        // Initialise variables
        $this->state       = $model->getState();
        
        // Get item
        $itemId            = $this->state->get('story.id');
        $this->item        = $model->getItem($itemId, $this->userId);
        
        $this->form        = $model->getForm();
        $this->params      = $this->state->get("params");
            
        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");
        $this->pitchImage  = $this->item->get("pitch_image");
        
        $this->pWidth      = $this->params->get("pitch_image_width", 600);
        $this->pHeight     = $this->params->get("pitch_image_height", 400);
        
        // Prepare extra images folder
        if($this->params->get("extra_images", 0) AND !empty($this->userId)) {

            jimport('joomla.filesystem.folder');
            
            $userDestinationFolder = CrowdFundingHelper::getImagesFolder($this->userId);
            if(!JFolder::exists($userDestinationFolder)) {
                JFolder::create($userDestinationFolder);
                
                $userDestinationFolderIndex = JPath::clean($userDestinationFolder."/index.html");
                $bufffer = "<!DOCTYPE html><title></title>";
                
                jimport('joomla.filesystem.file');
                JFile::write($userDestinationFolderIndex, $bufffer);
            }
            
            jimport("crowdfunding.images");
            $this->images = new CrowdFundingImages($itemId);
            
            $this->extraImagesUri = CrowdFundingHelper::getImagesFolderUri($this->userId);
        }
            
        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_STORY");
        
    } 
    
    protected function prepareRewards() {
        
        $model             = JModelLegacy::getInstance("Rewards", "CrowdFundingModel", $config = array('ignore_request' => false));
        
        // Initialise variables
        $this->state       = $model->getState();
        $this->projectId   = $this->state->get("rewards.id");
        $this->params      = $this->state->get("params");
        
        $this->items       = $model->getItems($this->projectId);
        
        // Get project and validate it
        jimport("crowdfunding.project");
        $this->item        = CrowdFundingProject::getInstance($this->projectId);
        if(!$this->item->id OR ($this->item->user_id != $this->userId)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), ITPrismErrors::CODE_ERROR);
        }
        
        jimport("crowdfunding.currency");
        $currencyId        = $this->params->get("project_currency");
		$this->currency    = CrowdFundingCurrency::getInstance($currencyId);
		
        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_REWARDS");
        
    } 
    
    /**
     * Prepares the document
     */
    protected function prepareDocument(){

        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
         // Prepare page suffix
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
        
        $menus = $app->getMenu();
        
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu  = $menus->getActive();
        
        if($menu) {
            $this->params->def('page_heading', $menu->title);
        } else {
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_RAISE_DEFAULT_PAGE_TITLE'));
        }
        
        // Prepare page title
        $title = $menu->title;
        if(!$title){
            $title = $app->getCfg('sitename');
        }elseif($app->getCfg('sitename_pagetitles', 0)){ // Set site name if it is necessary ( the option 'sitename' = 1 )
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        }
            
        $this->document->setTitle($title);
        
        // Meta Description
        $this->document->setDescription($this->params->get('menu-meta_description'));
        
        // Meta keywords
        $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        
        // Add current layout into breadcrumbs 
        $pathway    = $app->getPathway();
        $pathway->addItem($this->pathwayName);
        
        // Styles
        $this->document->addStyleSheet('media/'.$this->option.'/css/site/style.css');
        
        // Scripts
        JHtml::_('bootstrap.framework');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('formbehavior.chosen', 'select');
        
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        
        switch($this->layout) {
            
            case "rewards":
                
                // Scripts
                JHtml::_('bootstrap.loadCSS');
                JHtml::_('itprism.ui.pnotify');
		        $this->document->addScript('media/'.$this->option.'/js/helper.js');
		        $this->document->addScript('media/'.$this->option.'/js/site/project_rewards.js');
		        
                break;
                
            case "funding":
		        $this->document->addScript('media/'.$this->option.'/js/site/project_funding.js');
                break;

            case "story":
                
                // Scripts
                JHtml::_('itprism.ui.bootstrap_fileuploadstyle');
                
                if($this->params->get("extra_images", 0)) {
                    JHtml::_('itprism.ui.fileupload');
                    JHtml::_('itprism.ui.pnotify');
                    $this->document->addScript('media/'.$this->option.'/js/helper.js');
                }
                
                $this->document->addScript('media/'.$this->option.'/js/site/project_story.js');
                
                break;
                    
            default: // Basic
                
                // Scripts
                JHtml::_('itprism.ui.bootstrap_fileuploadstyle');
                JHtml::_('itprism.ui.bootstrap_maxlength');
                JHtml::_('itprism.ui.bootstrap_typeahead');
                JHtml::_('itprism.ui.parsley');
		        $this->document->addScript('media/'.$this->option.'/js/site/project_basic.js');
		        
		        // Load language string in JavaScript
		        JText::script('COM_CROWDFUNDING_THIS_VALUE_IS_REQUIRED');
                break;
        }
		
    }

}