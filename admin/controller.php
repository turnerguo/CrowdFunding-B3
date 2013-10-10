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

jimport('joomla.application.component.controller');

/**
 * Default controller
 *
 * @package		ITPrism Components
 * @subpackage	CrowdFunding
  */
class CrowdFundingController extends JController {
    
	public function display($cachable = false, $urlparams = array()) {

	    $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $option   = $app->input->getCmd("option");
        
        $document = JFactory::getDocument();
		/** @var $document JDocumentHtml **/
        
        // Add component style
        $document->addStyleSheet('../media/'.$option.'/css/admin/style.css');
        
        $viewName      = $app->input->getCmd('view', 'dashboard');
        $app->input->set("view", $viewName);

        parent::display($cachable, $urlparams);
        return $this;
	}

}