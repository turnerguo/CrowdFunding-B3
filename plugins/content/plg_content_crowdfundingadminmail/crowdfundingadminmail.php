<?php
/**
 * @package		 CrowdFunding
 * @subpackage	 Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * This plugin send notification mails to the administrator. 
 *
 * @package		CrowdFunding
 * @subpackage	Plugins
 */
class plgContentCrowdFundingAdminMail extends JPlugin {
    
    public function onContentChangeState($context, $ids, $state) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/

        if($app->isAdmin()) {
            return;
        }

        if(strcmp("com_crowdfunding.project", $context) != 0){
            return;
        }
        
        // Check for enabled option for sending mail 
        // when user publish a project.
        if(!$this->params->get("send_when_published")) {
            return true;
        }

        jimport("crowdfunding.constants");
        
        JArrayHelper::toInteger($ids);
        
        if(!empty($ids) AND $state == CrowdFundingConstants::PUBLISHED) {
            
            $this->loadLanguage();
            
            $app      = JFactory::getApplication();
            
            $siteName = $app->getCfg("sitename");
            $mailer   = JFactory::getMailer();
            
            $projects = $this->getProjectsData($ids);
            
            if(!$projects) {
                return false;
            }
            
            foreach($projects as $project) {
                
                // Send email to user
                $subject = JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_MAIL_MSG_PROJECT_INFORMATION", $project->title);
                $body    = JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_MAIL_MSG_PROJECT_PUBLISHED", $app->getCfg("fromname"), $project->title, JUri::root(), $siteName);
                
                $return  = $mailer->sendMail($app->getCfg("mailfrom"), $app->getCfg("fromname"), $app->getCfg("mailfrom"), $subject, $body, CrowdFundingConstants::MAIL_MODE_HTML);
                
                // Check for an error.
                if ($return !== true) {
                    $error = JText::_("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_MAIL_SENDING_USER");
                    JLog::add($error);
                    return false;
                }
                
            }
            
        }
        
        return true;
        
    }
    
    /**
     * This method is executed when someone create a project.
     * 
     * @param string                      $context
     * @param CrowdFundingTableProject    $project
     * @param boolean                     $isNew
     * @return void|boolean
     */
    public function onContentAfterSave($context, $project, $isNew) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/

        if($app->isAdmin()) {
            return;
        }

        if(strcmp("com_crowdfunding.project", $context) != 0){
            return;
        }
        
        // Check for enabled option for sending mail 
        // when user create a project.
        if(!$this->params->get("send_when_create")) {
            return true;
        }
        
        jimport("crowdfunding.constants");
        
        if(!empty($project->id) AND $isNew) {
            
            $this->loadLanguage();
            
            $app      = JFactory::getApplication();
            $user     = JFactory::getUser();
            
            $siteName = $app->getCfg("sitename");
            $mailer   = JFactory::getMailer();
                
            // Send email to administrator
            $subject = JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_MAIL_MSG_PROJECT_INFORMATION", $project->title);
            $body    = JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_MAIL_MSG_PROJECT_CREATED", $app->getCfg("fromname"), $user->name, $project->title, JUri::root(), $siteName);
            
            $return  = $mailer->sendMail($app->getCfg("mailfrom"), $app->getCfg("fromname"), $app->getCfg("mailfrom"), $subject, $body, CrowdFundingConstants::MAIL_MODE_HTML);
            
            // Check for an error.
            if ($return !== true) {
                $error = JText::_("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_MAIL_SENDING_USER");
                JLog::add($error);
                return false;
            }
                
            
        }
        
        return true;
        
    }
    
    /**
     * Load data about projects
     * 
     * @param array $ids
     * @return array
     */
    private function getProjectsData($ids) {
        
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query
            ->select("a.title")
            ->from($db->quoteName("#__crowdf_projects") . " AS a")
            ->where("a.id IN (". implode(",", $ids). ")");
        
        $db->setQuery($query);
        $results = $db->loadObjectList();
        
        if(!$results) {
            $results = array();
        }
        return $results;
    }
}