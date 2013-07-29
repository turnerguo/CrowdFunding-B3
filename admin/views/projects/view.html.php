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

class CrowdFundingViewProjects extends JView {
    
    protected $state;
    protected $items;
    protected $pagination;
    
    protected $option;
    
    public function __construct($config){
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
        
        jimport("crowdfunding.currency");
        $currencyId       = $this->state->params->get("project_currency");
        $this->currency   = CrowdFundingCurrency::getInstance($currencyId);
        
        $model = $this->getModel();
        
        // Get rewards number
        $projectsIds = array();
        foreach($this->items as $item) {
            $projectsIds[] = $item->id;
        }
        $this->rewards    = $model->getRewardsNumber($projectsIds);
        
        
        // Prepare options
        $this->approvedOptions = array(
            JHtml::_("select.option", 1, JText::_("COM_CROWDFUNDING_APPROVED")),
            JHtml::_("select.option", 0, JText::_("COM_CROWDFUNDING_DISAPPROVED")),
        );
        
        $this->featuredOptions = array(
            JHtml::_("select.option", 1, JText::_("COM_CROWDFUNDING_FEATURED")),
            JHtml::_("select.option", 0, JText::_("COM_CROWDFUNDING_NOT_FEATURED")),
        );
        
        
        // Add submenu
        CrowdFundingHelper::addSubmenu($this->getName());
        
        // Prepare actions
        $this->addToolbar();
        $this->setDocument();
        
        // Include HTML helper
        JHtml::addIncludePath(JPATH_COMPONENT_SITE.'/helpers/html');
        
        parent::display($tpl);
    }
    
    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar(){
        
        // Set toolbar items for the page
        JToolBarHelper::title(JText::_('COM_CROWDFUNDING_PROJECTS_MANAGER'), 'itp-projects');
        JToolBarHelper::publishList("projects.publish");
        JToolBarHelper::unpublishList("projects.unpublish");
        JToolBarHelper::divider();
        JToolBarHelper::custom('projects.approve', "itp-approve", "", JText::_("COM_CROWDFUNDING_APPROVE"), false);
        JToolBarHelper::custom('projects.disapprove', "itp-disapprove", "", JText::_("COM_CROWDFUNDING_DISAPPROVE"), false);
        
        JToolBarHelper::divider();
        JToolBarHelper::trash("projects.trash");
        JToolBarHelper::divider();
        JToolBarHelper::custom('projects.backToDashboard', "itp-dashboard-back", "", JText::_("COM_CROWDFUNDING_DASHBOARD"), false);
        
    }
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() {
	    
		$this->document->setTitle(JText::_('COM_CROWDFUNDING_PROJECTS_MANAGER'));
		
		// Scripts
		JHtml::_('behavior.multiselect');
		JHtml::_('behavior.tooltip');
		
	}
    
}