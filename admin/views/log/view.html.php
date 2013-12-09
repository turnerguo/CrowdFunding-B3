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

class CrowdFundingViewLog extends JViewLegacy {
    
    protected $state;
    protected $item;
    
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
        
        $layout = $this->getLayout();
        
        switch($layout) {
            
            case "view":
                $this->item  = $this->get('Item');
                break;
                
            case "files":
                $this->files = CrowdFundingHelper::getLogFiles();
                break;
            
        }
        
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
        
        $layout = $this->getLayout();
        
        switch($layout) {
        
            case "view":
                $this->documentTitle = JText::_('COM_CROWDFUNDING_VIEW_LOG_DATA');
                JToolbarHelper::custom("logs.delete", "delete", "", JText::_("JTOOLBAR_DELETE"), false);
                break;
        
            case "files":
                
                $this->documentTitle = JText::_('COM_CROWDFUNDING_VIEW_LOG_FILES');
                
                $bar = JToolbar::getInstance('toolbar');
                $bar->appendButton('Link', 'refresh', JText::_("COM_CROWDFUNDING_RELOAD"), JRoute::_("index.php?option=com_crowdfunding&view=log&layout=files"));
                
                break;
        
        }
        
		JToolbarHelper::title($this->documentTitle);
		
		JToolbarHelper::cancel('log.cancel', 'JTOOLBAR_CLOSE');
        
    }
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() {
	    
	    $this->document->setTitle($this->documentTitle);
	    
	    $layout = $this->getLayout();
	    
	    // Scripts
	    JHtml::_('bootstrap.tooltip');
	    
	    $this->document->addScript('../media/'.$this->option.'/js/admin/'.JString::strtolower($this->getName()).'.js');
	    
	    switch($layout) {
	    
	        case "files":
	    
	            // HTML Helpers
	            JHtml::addIncludePath(ITPRISM_PATH_LIBRARY.'/ui/helpers');
	            JHtml::_('itprism.ui.pnotify');
	    
	            $this->document->addScript('../media/'.$this->option.'/js/helper.js');
	            
	            // Load language string in JavaScript
	            JText::script('COM_CROWDFUNDING_DELETE_FILE_QUESTION');
	    
	            break;
	    
	    }
		
        
	}

}