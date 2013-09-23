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

class CrowdFundingViewLocations extends JView {
    
    protected $items;
    protected $pagination;
    protected $state;
    
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
        $this->saveOrder  = (strcmp($this->listOrder, 'a.name') != 0 ) ? false : true;
        
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
        JToolBarHelper::title(JText::_('COM_CROWDFUNDING_LOCATIONS_MANAGER'), 'itp-locations');
        JToolBarHelper::addNew('location.add');
        JToolBarHelper::editList('location.edit');
        JToolBarHelper::divider();
        
        // Add custom buttons
		$bar = JToolBar::getInstance('toolbar');
		
		// Import
		$link = JRoute::_('index.php?option=com_crowdfunding&view=import&type=locations');
		$bar->appendButton('Link', 'upload', JText::_("COM_CROWDFUNDING_IMPORT_LOCATIONS"), $link);
		
		$link = JRoute::_('index.php?option=com_crowdfunding&view=import&type=states');
		$bar->appendButton('Link', 'upload', JText::_("COM_CROWDFUNDING_IMPORT_STATES"), $link);
		
		// Export
		$link = JRoute::_('index.php?option=com_crowdfunding&task=export.download&format=raw&type=locations');
		$bar->appendButton('Link', 'export', JText::_("COM_CROWDFUNDING_EXPORT"), $link);
		
        JToolBarHelper::divider();
        JToolBarHelper::publishList("locations.publish");
        JToolBarHelper::unpublishList("locations.unpublish");
        JToolBarHelper::divider();
        JToolBarHelper::deleteList(JText::_("COM_CROWDFUNDING_DELETE_ITEMS_QUESTION"), "locations.delete");
        JToolBarHelper::divider();
        JToolBarHelper::custom('locations.backToDashboard', "itp-dashboard-back", "", JText::_("COM_CROWDFUNDING_DASHBOARD"), false);
        
    }
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() {
	    
		$this->document->setTitle(JText::_('COM_CROWDFUNDING_LOCATIONS_MANAGER'));
		
		// Add scripts
		JHtml::_('behavior.tooltip');
		
	}

}