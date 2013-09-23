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

class CrowdFundingViewCountries extends JView {
    
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
        JToolBarHelper::title(JText::_('COM_CROWDFUNDING_COUNTRIES_MANAGER'), 'itp-countries');
        JToolBarHelper::addNew('country.add');
        JToolBarHelper::editList('country.edit');
        JToolBarHelper::divider();
        
		// Add custom buttons
		$bar = JToolBar::getInstance('toolbar');
		
		// Import
		$link = JRoute::_('index.php?option=com_crowdfunding&view=import&type=countries');
		$bar->appendButton('Link', 'upload', JText::_("COM_CROWDFUNDING_IMPORT"), $link);
		
		// Export
		$link = JRoute::_('index.php?option=com_crowdfunding&task=export.download&format=raw&type=countries');
		$bar->appendButton('Link', 'export', JText::_("COM_CROWDFUNDING_EXPORT"), $link);
        
        JToolBarHelper::divider();
        JToolBarHelper::deleteList(JText::_("COM_CROWDFUNDING_DELETE_ITEMS_QUESTION"), "countries.delete");
        JToolBarHelper::divider();
        JToolBarHelper::custom('countries.backToDashboard', "itp-dashboard-back", "", JText::_("COM_CROWDFUNDING_DASHBOARD"), false);
        
    }
    
	/**
	 * Method to set up the document properties
	 * @return void
	 */
	protected function setDocument() {
	    
		$this->document->setTitle(JText::_('COM_CROWDFUNDING_COUNTRIES_MANAGER'));
		
		JHtml::_('behavior.tooltip');
	}
    
}