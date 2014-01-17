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

jimport('joomla.application.component.controller');

/**
 * Default controller
 *
 * @package		CrowdFunding
 * @subpackage	Components
  */
class CrowdFundingController extends JControllerLegacy {
    
    protected $option;
    
    public function __construct($config){
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
	public function display($cachable = false, $urlparams = array()) {

        $viewName      = $this->input->getCmd('view', 'dashboard');
        $this->input->set("view", $viewName);

        $doc = JFactory::getDocument();
        $doc->addStyleSheet("../media/".$this->option.'/css/admin/style.css');
        
        parent::display($cachable, $urlparams);
        return $this;
        
	}

}