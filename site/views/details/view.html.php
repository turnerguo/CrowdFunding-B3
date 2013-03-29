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

class CrowdFundingViewDetails extends JView {
    
    protected $state;
    protected $item;
    protected $params;
    
    protected $option;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
    public function display($tpl = null) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Get model state.
        $this->state       = $this->get('State');
        $this->item        = $this->get("Item");
        $this->params      = $this->state->get("params");
        
        if (!$this->item) {
            $app = JFactory::getApplication();
            /** @var $app JSite **/
            
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $app->redirect(JRoute::_('index.php?option=com_crowdfunding&view=discover', false));
            return;
        }
        
        $this->rewards     = $this->get("Rewards");
        $this->imageFolder = $this->params->get("images_directory", "images/projects");
        
        // Include HTML helper
        JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
        
        // Get currency
        $currencyId           = $this->params->get("project_currency");
		$this->currency       = CrowdFundingHelper::getCurrency($currencyId);
		
        // Set a link to project page
        $host  = JFactory::getURI()->toString(array("scheme", "host"));
        $this->item->link        = $host.JRoute::_("index.php?option=com_crowdfunding&view=details&id=".(int)$this->item->id, false);
        
        // Set a link to image
        $this->item->link_image  = $host."/".$this->imageFolder."/".$this->item->image;
        
        // Screens
        $this->screen = $app->input->get->get("screen", "home");
        
        switch($this->screen) {
            
            case "updates":
                $this->prepareUpdatesScreen();
                break;
                
            case "comments":
                $this->prepareCommentsScreen();
                break;
                
            default: // Home
                break;
        }
        
        // Events
        JPluginHelper::importPlugin('content');
        $dispatcher	       = JDispatcher::getInstance();
        $this->item->event = new stdClass();
        $offset            = 0;
        
        $results           = $dispatcher->trigger('onContentBeforeDisplay', array('com_crowdfunding.details', &$this->item, &$this->params, $offset));
		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));
		
		$results           = $dispatcher->trigger('onContentAfterDisplayMedia', array('com_crowdfunding.details', &$this->item, &$this->params, $offset));
		$this->item->event->onContentAfterDisplayMedia = trim(implode("\n", $results));
		
		$this->prepareDocument();
		
        parent::display($tpl);
    }
    
    protected function prepareUpdatesScreen() {
        
        $model         = JModel::getInstance("Updates", "CrowdFundingModel");
        $this->items   = $model->getItems();
        $this->form    = $model->loadForm();
        
        $this->userId  = JFactory::getUser()->id;
        $this->isOwner = ($this->userId != $this->item->user_id) ? false : true;
        
        // Styles
        $this->document->addStyleSheet(JURI::root() . 'media/'.$this->option.'/css/jquery.pnotify.default.css');

        // Scripts
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        
        $this->document->addScript(JURI::root() . 'media/'.$this->option.'/js/jquery.pnotify.min.js');
        $this->document->addScript(JURI::root() . 'media/'.$this->option.'/js/helper.js');
        
        $this->document->addScript(JURI::root() . 'media/'.$this->option.'/js/site/updates.js');
    }
    
    protected function prepareCommentsScreen() {
        
        $model         = JModel::getInstance("Comments", "CrowdFundingModel");
        $this->items   = $model->getItems();
        $this->form    = $model->loadForm();
        
        $this->userId  = JFactory::getUser()->id;
        $this->isOwner = ($this->userId != $this->item->user_id) ? false : true;
        
        // Styles
        $this->document->addStyleSheet(JURI::root() . 'media/'.$this->option.'/css/jquery.pnotify.default.css');

        // Scripts
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        
        $this->document->addScript(JURI::root() . 'media/'.$this->option.'/js/jquery.pnotify.min.js');
        $this->document->addScript(JURI::root() . 'media/'.$this->option.'/js/helper.js');
        
        $this->document->addScript(JURI::root() . 'media/'.$this->option.'/js/site/comments.js');
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