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

class CrowdFundingViewComment extends JView {
    
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
        
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');
        
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
        
        $this->documentTitle = $isNew ? JText::_('COM_CROWDFUNDING_NEW_COMMENT')
		                              : JText::_('COM_CROWDFUNDING_EDIT_COMMENT');
        
		if(!$isNew) {
		    JToolBarHelper::title($this->documentTitle, 'itp-edit-comment');
		} else {
            JToolBarHelper::title($this->documentTitle, 'itp-new-comment');
		}
		                             
        JToolBarHelper::apply('comment.apply');
        JToolBarHelper::save('comment.save');
    
        if(!$isNew){
            JToolBarHelper::cancel('comment.cancel', 'JTOOLBAR_CANCEL');
        }else{
            JToolBarHelper::cancel('comment.cancel', 'JTOOLBAR_CLOSE');
        }
        
    }
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() {
	    
	    $this->document->setTitle($this->documentTitle);
	    
	    // Scripts
	    JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');
		
		$this->document->addScript('../media/'.$this->option.'/js/admin/'.strtolower($this->getName()).'.js');
        
	}

}