<?php
/**
 * @package      ITPrism Components
 * @subpackage   CrowdFunding
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

class CrowdFundingViewEmbed extends JView {
    
    protected $state;
    protected $item;
    protected $params;
    
    protected $option;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
    public function display($tpl = null) {
        
        // Get model state.
        $this->state       = $this->get('State');
        $this->item        = $this->get("Item");
        $this->params      = $this->state->get("params");
        
        $this->imageFolder = $this->params->get("images_directory", "images/projects");
        
        if (!$this->item) {
            $app = JFactory::getApplication();
            /** @var $app JSite **/
            
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $app->redirect(JRoute::_('index.php?option=com_crowdfunding&view=discover', false));
            return;
        }
        
        // Include HTML helper
        JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
        
        // Get currency
        $currencyId           = $this->params->get("project_currency");
		$this->currency       = CrowdFundingHelper::getCurrency($currencyId);
		
		// Get a social platform for integration
		$this->socialPlatform = $this->params->get("integration_social_platform");
		
        // Set a link to project page
        $host  = JFactory::getURI()->toString(array("scheme", "host"));
        $this->item->link        = $host.JRoute::_("index.php?option=com_crowdfunding&view=details&id=".(int)$this->item->id, false);
        
        // Set a link to image
        $this->item->link_image  = $host."/".$this->imageFolder."/".$this->item->image;
        
        // Generate embed link
        $this->embedLink = $host.JRoute::_("index.php?option=com_crowdfunding&view=embed&layout=widget&tmpl=component&id=".(int)$this->item->id, false);
		
		$this->prepareDocument();
		
        parent::display($tpl);
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
        
        // Add styles
        $this->document->addStyleSheet( 'media/'.$this->option.'/css/site/bootstrap.min.css');
        $this->document->addStyleSheet( 'media/'.$this->option.'/css/site/style.css');
        
        // Add scripts
        JHtml::_('behavior.framework');
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
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_DETAILS_DEFAULT_PAGE_TITLE', $this->item->title));
        }
    
    }
    
    private function prepearePageTitle() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Prepare page title
//        $title = $this->params->get('page_title', $this->item->title);
        $title = $this->item->title;
        
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