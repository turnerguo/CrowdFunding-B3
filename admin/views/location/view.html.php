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

class CrowdFundingViewLocation extends JView {
    
    protected $state;
    protected $item;
    protected $form;
    
    protected $documentTitle;
    protected $option;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
    /**
     * Display the view
     */
    public function display($tpl = null){
        
        $this->state= $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');
        
        // Prepare actions, behaviors, scritps and document
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
        
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        
        $this->documentTitle = $isNew ? JText::_('COM_CROWDFUNDING_ADD_LOCATION')
		                              : JText::_('COM_CROWDFUNDING_EDIT_LOCATION');
		                             
        if(!$isNew) {
            JToolBarHelper::title($this->documentTitle, 'itp-edit-location');
        } else {
            JToolBarHelper::title($this->documentTitle, 'itp-add-location');
        }
		                             
        JToolBarHelper::apply('location.apply');
        JToolBarHelper::save2new('location.save2new');
        JToolBarHelper::save('location.save');
    
        if(!$isNew){
            JToolBarHelper::cancel('location.cancel', 'JTOOLBAR_CANCEL');
        }else{
            JToolBarHelper::cancel('location.cancel', 'JTOOLBAR_CLOSE');
        }
        
    }
    
	/**
	 * Method to set up the document properties
	 * @return void
	 */
	protected function setDocument() {
	    
		$this->document->setTitle($this->documentTitle);
		
		// Scripts
		JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');
        
		$this->document->addScript(JURI::root() . 'media/'.$this->option.'/js/admin/'.JString::strtolower($this->getName()).'.js');
	}
	

}