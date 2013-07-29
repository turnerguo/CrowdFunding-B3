<?php
/**
 * @package		 CrowdFunding
 * @subpackage	 Plugins
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
 * CrowdFunding User Mail Plugin
 *
 * @package		CrowdFunding
 * @subpackage	Plugins
 */
class plgContentCrowdFundingUserMail extends JPlugin {
    
    const PROJECT_STATE_APPROVED = 1;
    const MAIL_MODE_HTML         = true;
    
    public function onContentChangeState($context, $ids, $state) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/

        if(!$app->isAdmin()) {
            return;
        }

        if(strcmp("com_crowdfunding.project", $context) != 0){
            return;
        }
        
        // Check for enabled option for sending mail 
        // when administrator approve project.
        if(!$this->params->get("send_when_approved")) {
            return true;
        }
        
        JArrayHelper::toInteger($ids);
        
        if(!empty($ids) AND $state == self::PROJECT_STATE_APPROVED) {
            
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
                $subject = JText::sprintf("PLG_CONTENT_CROWDFUNDINGUSERMAIL_MAIL_MSG_PROJECT_INFORMATION", $project->title);
                $body    = JText::sprintf("PLG_CONTENT_CROWDFUNDINGUSERMAIL_MAIL_MSG_PROJECT_APPROVED", $project->name, $project->title, JUri::root(), $siteName, $app->getCfg("fromname"));
                
                $return  = $mailer->sendMail($app->getCfg("mailfrom"), $app->getCfg("fromname"), $project->email, $subject, $body, self::MAIL_MODE_HTML);
                
                // Check for an error.
                if ($return !== true) {
                    $error = JText::_("PLG_CONTENT_CROWDFUNDINGUSERMAIL_ERROR_MAIL_SENDING_USER");
                    JLog::add($error);
                    return false;
                }
                
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
            ->select("a.title, ".
                     "b.name, b.email"
                )
            ->from($db->quoteName("#__crowdf_projects") . " AS a")
            ->leftJoin($db->quoteName("#__users") . " AS b ON a.user_id = b.id")
            ->where("a.id IN (". implode(",", $ids). ")");
        
        $db->setQuery($query);
        $results = $db->loadObjectList();
        
        if(!$results) {
            $results = array();
        }
        return $results;
    }
}