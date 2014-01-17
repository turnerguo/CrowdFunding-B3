<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class CrowdFundingViewProject extends JViewLegacy {
    
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
        
        $this->params = $this->state->get("params");
        
        $imagesFolder      = $this->params->get("images_directory", "images/crowdfunding");
        $this->imagesUrl   = JUri::root().$imagesFolder;
        
        // Set minimum values - days, amount,...
        $this->minAmount   = $this->params->get("project_amount_minimum", 100);
        $this->maxAmount   = $this->params->get("project_amount_maximum");
        
        $this->minDays     = $this->params->get("project_days_minimum", 30);
        $this->maxDays     = $this->params->get("project_days_maximum");
        
        $this->prepareFundingDurationType();
        
        jimport("crowdfunding.images");
        $this->extraImages = new CrowdFundingImages($this->item->id);
        
        $this->extraImagesUri = "../".CrowdFundingHelper::getImagesFolderUri($this->item->user_id);
        
        // Prepare actions, behaviors, scritps and document
        $this->addToolbar();
        $this->setDocument();
        
        parent::display($tpl);
    }
    
    protected function prepareFundingDurationType() {
    
        $this->fundingDuration     = $this->params->get("project_funding_duration");
    
        switch($this->fundingDuration) {
    
            case "days": // Only days type is enabled
                $this->checkedDays = 'checked="checked"';
                break;
    
            case "date": // Only date type is enabled
                $this->checkedDate = 'checked="checked"';
                break;
    
            default: // Both ( days and date ) types are enabled
    
                $this->checkedDays = 0;
                $this->checkedDate = "";
    
                if(!empty($this->item->funding_days)) {
                    $this->checkedDays = 'checked="checked"';
                    $this->checkedDate = '';
                } else if (CrowdFundingHelper::isValidDate($this->item->funding_end)) {
                    $this->checkedDays = '';
                    $this->checkedDate = 'checked="checked"';
                }
    
                // If missing both, select days
                if(!$this->checkedDays AND !$this->checkedDate) {
                    $this->checkedDays = 'checked="checked"';
                }
                break;
    
        }
    }
    
    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar(){
        
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        
        $this->documentTitle= $isNew  ? JText::_('COM_CROWDFUNDING_NEW_PROJECT')
                                      : JText::_('COM_CROWDFUNDING_EDIT_PROJECT');

        JToolbarHelper::title($this->documentTitle);
        
        JToolbarHelper::apply('project.apply');
        JToolbarHelper::save('project.save');
    
        if(!$isNew){
            JToolbarHelper::cancel('project.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolbarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');
        }
        
    }
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() {
	    
		$this->document->setTitle($this->documentTitle);
        
		// Add scripts
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');
        
        JHtml::_('formbehavior.chosen', 'select');
        
        JHtml::_('bootstrap.tooltip');
        JHtml::_('itprism.ui.bootstrap_fileuploadstyle');
        
		$this->document->addScript('../media/'.$this->option.'/js/admin/'.JString::strtolower($this->getName()).'.js');
        
		$this->document->addStyleSheet("../media/".$this->option.'/css/admin/style.css');
	}

}