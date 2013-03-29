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
jimport('joomla.application.categories');

class CrowdFundingViewTransactions extends JView {
    
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
        $listOrder        = $this->escape($this->state->get('list.ordering'));
        $listDirn         = $this->escape($this->state->get('list.direction'));
        $saveOrder        = (strcmp($listOrder, 'a.ordering') != 0 ) ? false : true;
        
        $this->listOrder  = $listOrder;
        $this->listDirn   = $listDirn;
        $this->saveOrder  = $saveOrder;
        
        if(!empty($this->items)) {
            $this->currencies   = CrowdFundingHelper::getCurrencies("abbr");
        }
        
        // Add submenu
        CrowdFundingHelper::addSubmenu($this->getName());
        
        // Prepare actions
        $this->addToolbar();
        $this->setDocument();
        
        // Include HTML helper
        JHtml::addIncludePath(JPATH_COMPONENT_SITE.'/helpers/html');
        JLoader::register('JHtmlString', JPATH_LIBRARIES.'/joomla/html/html/string.php');
        
        parent::display($tpl);
    }
    
    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar(){
        
        // Set toolbar items for the page
        JToolBarHelper::title(JText::_('COM_CROWDFUNDING_TRANSACTIONS_MANAGER'), 'itp-transactions');
        JToolBarHelper::editList('transaction.edit');
        JToolBarHelper::divider();
        JToolBarHelper::publishList("transactions.publish");
        JToolBarHelper::unpublishList("transactions.unpublish");
        JToolBarHelper::divider();
        JToolBarHelper::deleteList(JText::_("COM_CROWDFUNDING_DELETE_ITEMS_QUESTION"), "transactions.delete");
        JToolBarHelper::divider();
        JToolBarHelper::custom('projects.backToDashboard', "itp-dashboard-back", "", JText::_("COM_CROWDFUNDING_DASHBOARD"), false);
        
    }
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() {
	    
	    JHtml::_('behavior.tooltip');
	    
		$this->document->setTitle(JText::_('COM_CROWDFUNDING_TRANSACTIONS_MANAGER'));
	}
    
}