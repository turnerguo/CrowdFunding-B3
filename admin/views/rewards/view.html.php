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

class CrowdFundingViewRewards extends JView {
    
    protected $state;
    protected $items;
    protected $pagination;
    
    public function display($tpl = null){
        
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        
        // Prepare filters
        $this->listOrder  = $this->escape($this->state->get('list.ordering'));
        $this->listDirn   = $this->escape($this->state->get('list.direction'));
        $this->saveOrder  = (strcmp($this->listOrder, 'a.ordering') != 0 ) ? false : true;
        
        jimport("crowdfunding.currency");
        $currencyId       = $this->state->params->get("project_currency");
        $this->currency   = CrowdFundingCurrency::getInstance($currencyId);
        
        $projectId = $this->state->get("project_id");
        $this->projectTitle = CrowdFundingHelper::getProjectTitle($projectId);
        
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
        JToolBarHelper::title(JText::sprintf('COM_CROWDFUNDING_REWARDS_MANAGER', $this->projectTitle), 'itp-rewards');

        // Add custom buttons
        $bar = JToolBar::getInstance('toolbar');
        
        // Import
        $link = JRoute::_('index.php?option=com_crowdfunding&view=projects');
        $bar->appendButton('Link', 'itp-projects-back', JText::_("COM_CROWDFUNDING_BACK_TO_PROJECTS"), $link);
        
        JToolbarHelper::divider();
        JToolBarHelper::custom('projects.backToDashboard', "itp-dashboard-back", "", JText::_("COM_CROWDFUNDING_DASHBOARD"), false);
        
    }
    
	/**
	 * Method to set up the document properties.
	 * 
	 * @return void
	 */
	protected function setDocument() {
		$this->document->setTitle(JText::_('COM_CROWDFUNDING_REWARDS_MANAGER_BROWSER_TITLE'));
		
		// Scripts
		JHtml::_('behavior.tooltip');
	}
    
}