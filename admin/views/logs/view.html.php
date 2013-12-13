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

class CrowdFundingViewLogs extends JView {
    
    protected $state;
    protected $items;
    protected $pagination;
    
    protected $option;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
    public function display($tpl = null){
        
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        
        // Prepare filters
        $this->listOrder  = $this->escape($this->state->get('list.ordering'));
        $this->listDirn   = $this->escape($this->state->get('list.direction'));
        $this->saveOrder  = (strcmp($this->listOrder, 'a.ordering') != 0 ) ? false : true;
        
        $this->types = CrowdFundingHelper::getLogTypes();
        
        $logFiles         = CrowdFundingHelper::getLogFiles();
        
        $this->numberLogFilse = count($logFiles);
        
        // Add submenu
        CrowdFundingHelper::addSubmenu($this->getName());
        
        // Prepare actions
        $this->addToolbar();
        $this->setDocument();
        
        parent::display($tpl);
    }
    
    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar(){
        
        // Set toolbar items for the page
        JToolBarHelper::title(JText::_('COM_CROWDFUNDING_LOGS_MANAGER'), 'itp-logs');
        
        $bar = JToolbar::getInstance('toolbar');
        $bar->appendButton('Link', 'eye-open', JText::sprintf("COM_CROWDFUNDING_VIEW_LOG_FILES_BUTTON", $this->numberLogFilse), JRoute::_("index.php?option=com_crowdfunding&view=log&layout=files"));
        
        $bar->appendButton('Link', 'refresh', JText::_("COM_CROWDFUNDING_RELOAD"), JRoute::_("index.php?option=com_crowdfunding&view=logs"));
        
        JToolbarHelper::custom('logs.removeall', "trash", "", JText::_("COM_CROWDFUNDING_DELETE_ALL"), false);
        
        JToolBarHelper::divider();
        JToolBarHelper::deleteList(JText::_("COM_CROWDFUNDING_DELETE_ITEMS_QUESTION"), "comments.delete");
        JToolBarHelper::divider();
        JToolBarHelper::custom('comments.backToDashboard', "itp-dashboard-back", "", JText::_("COM_CROWDFUNDING_DASHBOARD"), false);
        
    }
    
	/**
	 * Method to set up the document properties
	 * @return void
	 */
	protected function setDocument() {
	    
		$this->document->setTitle(JText::_('COM_CROWDFUNDING_LOGS_MANAGER'));
		
		// Scripts
		JHtml::_('behavior.tooltip');
	}
    
}