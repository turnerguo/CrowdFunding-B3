<?php
/**
 * @package      CrowdFunding
 * @subpackage   Plugins
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

jimport('joomla.application.component.helper');
jimport('joomla.plugin.plugin');

/**
* CrowdFunding Modules plugin
*
* @package 		CrowdFunding
* @subpackage	Plugins
*/
class plgSystemCrowdfundingModules extends JPlugin {
	
	public function onAfterDispatch() {
	     
	    if($this->isRestricted()) {
	        return;
	    }
	    
	    $app        = JFactory::getApplication();
	    /** @var $app JSite **/
	    
	    $view       = $app->input->getCmd("view");
	    $option     = $app->input->getCmd("option");
	    
	    // Allowed views for the module CrowdFunding Details
	    $allowedViews = array("backing", "embed");
	    
	    if($this->params->get("module_info_details_page", 0)) {
	        
	        if((strcmp($option, "com_crowdfunding") == 0) AND (strcmp($view, "details") != 0)) {

	            $doc = JFactory::getDocument();
	            /** @var $doc JDocumentHTML **/
	            	
	            $module = JModuleHelper::getModule("mod_crowdfundinginfo");
	            $seed   = substr(md5(uniqid(time() * rand(), true)), 0, 10);
	            $module->position = "fp".JApplication::getHash($seed);
	            
	        }
	        
	    }
	    
	    if($this->params->get("module_rewards_details_page", 0)) {
	         
	        if((strcmp($option, "com_crowdfunding") == 0) AND (strcmp($view, "details") != 0)) {
	    
	            $doc = JFactory::getDocument();
	            /** @var $doc JDocumentHTML **/
	    
	            $module = JModuleHelper::getModule("mod_crowdfundingrewards");
	            $seed   = substr(md5(uniqid(time() * rand(), true)), 0, 10);
	            $module->position = "fp".JApplication::getHash($seed);
	             
	        }
	         
	    }
	    
	    // Backing page
	    if($this->params->get("module_details_backing_page", 0)) {
	        
	        if((strcmp($option, "com_crowdfunding") == 0) AND !in_array($view, $allowedViews)) {
	             
	            $doc = JFactory::getDocument();
	            /** @var $doc JDocumentHTML **/
	             
	            $module = JModuleHelper::getModule("mod_crowdfundingdetails");
	            $seed   = substr(md5(uniqid(time() * rand(), true)), 0, 10);
	            $module->position = "fp".JApplication::getHash($seed);
	    
	        }
	    
	    }
	    
	    // Embed page
	    if($this->params->get("module_details_embed_page", 0)) {
	         
	        if((strcmp($option, "com_crowdfunding") == 0) AND !in_array($view, $allowedViews)) {
	    
	            $doc = JFactory::getDocument();
	            /** @var $doc JDocumentHTML **/
	    
	            $module = JModuleHelper::getModule("mod_crowdfundingdetails");
	            $seed   = substr(md5(uniqid(time() * rand(), true)), 0, 10);
	            $module->position = "fp".JApplication::getHash($seed);
	             
	        }
	         
	    }
	    
	}
	
	private function isRestricted() {
	     
	    $app = JFactory::getApplication();
	    /** @var $app JSite **/
	
	    if($app->isAdmin()) {
	        return true;
	    }
	
	    $document = JFactory::getDocument();
	    /** @var $document JDocumentHTML **/
	
	    $type = $document->getType();
	    if(strcmp("html",$type) != 0) {
	        return true;
	    }
	
	    // It works only for GET request
	    $method = $app->input->getMethod();
	    if(strcmp("GET", $method) !== 0) {
	        return true;
	    }
	
	    // Check component enabled
	    if (!JComponentHelper::isEnabled('com_crowdfunding', true)) {
	        return true;
	    }
	
	    return false;
	}
	
}