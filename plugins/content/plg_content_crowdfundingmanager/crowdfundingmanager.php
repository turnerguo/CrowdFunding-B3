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

jimport('joomla.plugin.plugin');

/**
 * CrowdFunding Manager Plugin
 *
 * @package      CrowdFunding
 * @subpackage   Plugins
 */
class plgContentCrowdFundingManager extends JPlugin {
    
    public function onContentAfterDisplay($context, &$project, &$params, $page = 0) {
        
        if($this->isRestricted($context, $project)) {
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Get request data
        $this->currentOption  = $app->input->getCmd("option");
        $this->currentView    = $app->input->getCmd("view");
        $this->currentTask    = $app->input->getCmd("task");
        
        // Load language
        $this->loadLanguage();
        
        // Generate content
        $content      = '<div class="cf-manager">';
        if($this->params->get("display_title", 0)) {
            $content  .=  '<h4>'. JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_PROJECT_MANAGER") .'</h4>';
        } 
        
        if($this->params->get("display_toolbar", 0)) {
            $content      .= $this->getToolbar($project);
        }
        
        if($this->params->get("display_statistics", 0)) {
            $content      .= $this->getStatistics($project);
        }
        
		$content      .= '</div>';
		
		return $content;
    }
    
    private function isRestricted($context, $project) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        if($app->isAdmin()) {
            return true;
        }
        
        $doc     = JFactory::getDocument();
        /**  @var $doc JDocumentHtml **/
        
        // Check document type
        $docType = $doc->getType();
        if(strcmp("html", $docType) != 0){
            return true;
        }
         
        if(strcmp("com_crowdfunding.details", $context) != 0){
            return true;
        }
        
        $userId = JFactory::getUser()->id;
        if($userId != $project->user_id) {
            return true;
        }
        
    }
    
    private function getToolbar($project) {
        
        $html = array();
        $html[] = '<div class="cf-pm-toolbar">';
        
        if($project->published AND !$project->approved) {
            $html[] = '<p class="alert alert-info">'.JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_NOT_APPROVED_NOTIFICATION").'</p>';
        }
        
        // Edit
        $html[] = '<a href="'.JRoute::_(CrowdFundingHelperRoute::getFormRoute($project->id)).'" class="btn">';
        $html[] = '<i class="icon icon-edit"></i>';
        $html[] = JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_EDIT");
        $html[] = '</a>';
        
        // Publish button
        if(!$project->published) {
            $html[] = '<a href="'.JRoute::_("index.php?option=com_crowdfunding&task=projects.savestate&id=".$project->id."&state=1&".JSession::getFormToken()."=1&return=1").'" class="btn">';
            $html[] = '<i class="icon-ok-sign"></i>';
            $html[] = JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_PUBLISH");
            $html[] = '</a>';
            
        } else { // Unpublish button
            
            $html[] = '<a href="'.JRoute::_("index.php?option=com_crowdfunding&task=projects.savestate&id=".$project->id."&state=0&".JSession::getFormToken()."=1&return=1").'" class="btn">';
            $html[] = '<i class="icon-remove-sign"></i>';
            $html[] = JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_UNPUBLISH");
            $html[] = '</a>';
            
        }
        
        $html[] = '</div>';

        return implode("\n", $html);
    }
    
    public function getStatistics($project) {
        
        jimport("crowdfunding.currency");
        $params           = JComponentHelper::getParams("com_crowdfunding");
        $currencyId       = $params->get("project_currency");
        $currency         = CrowdFundingCurrency::getInstance($currencyId);
        
        $projectData = CrowdFundingHelper::getProjectData($project->id);
        
        $html   = array();
        
        $html[] = '<div class="row-fluid">';
             
        $html[] = '     <h5>'.JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_STATISTICS").'</h5>';
        
        $html[] = '     <div class="span4">';
        
        $html[] = '         <table class="table table-bordered">';
        
        // Hits
        $html[] = '             <tr>';
        $html[] = '                 <td>'.JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_HITS").'</td>';
        $html[] = '                 <td>'.(int)$project->hits.'</td>';
        $html[] = '             </tr>';
        
        // Updates
        $html[] = '             <tr>';
        $html[] = '                 <td>'.JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_UPDATES").'</td>';
        $html[] = '                 <td>'.JArrayHelper::getValue($projectData, "updates", 0, "integer").'</td>';
        $html[] = '             </tr>';
        
        // Comments
        $html[] = '             <tr>';
        $html[] = '                 <td>'.JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_COMMENTS").'</td>';
        $html[] = '                 <td>'.JArrayHelper::getValue($projectData, "comments", 0, "integer").'</td>';
        $html[] = '             </tr>';
        
        // Funders
        $html[] = '             <tr>';
        $html[] = '                 <td>'.JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_FUNDERS").'</td>';
        $html[] = '                 <td>'.JArrayHelper::getValue($projectData, "funders", 0, "integer").'</td>';
        $html[] = '             </tr>';
        
        // Raised
        $html[] = '             <tr>';
        $html[] = '                 <td>'.JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_RAISED").'</td>';
        $html[] = '                 <td>'.$currency->getAmountString($project->funded).'</td>';
        $html[] = '             </tr>';
        
        $html[] = '         </table>';
    
        $html[] = '     </div>';
            
        $html[] = '</div>';
        
        return implode("\n", $html);
        
    }
    
}