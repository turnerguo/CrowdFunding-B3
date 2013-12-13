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

jimport('joomla.application.component.view');

class CrowdFundingViewFeatured extends JViewLegacy {
    
    protected $state      = null;
    protected $items      = null;
    protected $pagination = null;
    protected $params     = null;
    
    protected $option     = null;
    
    public function __construct($config){
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->getCmd("option");
    }
    
    public function display($tpl = null) {
        
        // Initialise variables
        $this->state          = $this->get("State");
        $this->items          = $this->get('Items');
        $this->pagination     = $this->get('Pagination');
        $this->params         = $params = $this->state->get("params");

        $this->imageWidth     = $this->params->get("image_width", 200);
        $this->imageHeight    = $this->params->get("image_height", 200);
        
        $model                = $this->getModel();
        /** @var @model CrowdFundingModelFeatured **/
        
        $this->numberInRow    = $this->params->get("featured_items_row", 3);
        $this->items          = $model->prepareItems($this->items,  $this->numberInRow); 
        
        // Get the folder with images
        $this->imageFolder    = $params->get("images_directory", "images/crowdfunding");
        
        // Get currency
        jimport("crowdfunding.currency");
        $currencyId           = $this->params->get("project_currency");
        $this->currency       = CrowdFundingCurrency::getInstance($currencyId);
		
        $this->displayCreator     = $this->params->get("integration_display_creator", true);

        // Prepare integration. Load avatars and profiles.
        if(!empty($this->displayCreator)) {
            $this->prepareIntegration($this->items, $this->params);
        }
		
        $this->version    = new CrowdFundingVersion();
        
        $this->prepareDocument();
        
        parent::display($tpl);
    }
    
    /**
     * Prepares the document
     */
    protected function prepareDocument(){
        
        // Prepare page suffix
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
        
        // Prepare page heading
        $this->prepearePageHeading();
        
        // Prepare page heading
        $this->prepearePageTitle();
        
        // Meta Description
        if($this->params->get('menu-meta_description')){
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }
        
        // Meta keywords
        if($this->params->get('menu-meta_keywords')){
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }
        
        // Styles
        
        // Load bootstrap thumbnails styles
        if($this->params->get("bootstrap_thumbnails", false)) {
            JHtml::_("itprism.ui.bootstrap_thumbnails");
        }
        
        $this->document->addStyleSheet( 'media/'.$this->option.'/css/site/style.css');
        
        // Script
        JHtml::_("crowdfunding.bootstrap");
        
    }
    
    private function prepearePageHeading() {
        
        $app      = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menus    = $app->getMenu();
		$menu     = $menus->getActive();
		
		// Prepare page heading
        if($menu){
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }else{
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_FEaTURED_DEFAULT_PAGE_TITLE'));
        }
		
    }
    
    private function prepearePageTitle() {
        
        $app      = JFactory::getApplication();
        /** @var $app JSite **/
        
		// Prepare page title
        $title    = $this->params->get('page_title', '');
        
        // Add title before or after Site Name
        if(!$title){
            $title = $app->getCfg('sitename');
        } elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		} elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		
        $this->document->setTitle($title);
		
    }
    
    /**
     * Prepare social profiles
     *
     * @param array     $items
     * @param JRegistry $params
     *
     * @todo Move it to a trait when traits become mass.
     */
    protected function prepareIntegration($items, $params) {
    
        // Get users IDs
        $usersIds = array();
        foreach($items as $item) {
            $usersIds[] = $item->user_id;
        }
        
        $this->socialProfiles        = null;
        
        // If there is now users, do not continue.
        if(!$usersIds) {
            return;
        }
    
        // Get a social platform for integration
        $socialPlatform        = $params->get("integration_social_platform");
        
        // Load the class
        if(!empty($socialPlatform)) {
            jimport("itprism.integrate.profiles");
            $this->socialProfiles   =  ITPrismIntegrateProfiles::factory($socialPlatform, $usersIds);
        }
    
    }
    
}