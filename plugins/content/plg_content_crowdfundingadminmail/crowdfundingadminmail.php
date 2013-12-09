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
jimport('crowdfunding.init');

/**
 * This plugin send notification mails to the administrator. 
 *
 * @package		CrowdFunding
 * @subpackage	Plugins
 */
class plgContentCrowdFundingAdminMail extends JPlugin {
    
    protected   $log;
    protected   $logFile = "plg_content_adminmail.php";
    
    public function __construct(&$subject, $config = array()) {
    
        parent::__construct($subject, $config);
        
        // Create log object
        $file = JPath::clean(JFactory::getApplication()->getCfg("log_path") .DIRECTORY_SEPARATOR. $this->logFile);
    
        $this->log = new CrowdFundingLog();
        $this->log->addWriter(new CrowdFundingLogWriterDatabase(JFactory::getDbo()));
        $this->log->addWriter(new CrowdFundingLogWriterFile($file));
    
        // Load language
        $this->loadLanguage();
    }
    
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
        $emailId = $this->params->get("send_when_published", 0);
        if(!$emailId) {
            return true;
        }

        JArrayHelper::toInteger($ids);
        
        if(!empty($ids) AND $state == CrowdFundingConstants::PUBLISHED) {
            
            $projects = $this->getProjectsData($ids);
            
            if(!$projects) {
                return false;
            }
            
            // Load class CrowdFundingEmail.
            jimport("crowdfunding.email");
            
            foreach($projects as $project) {
                
                // Send email to the administrator.
                $return = $this->sendMails($project, $emailId);
                
                // Check for error.
                if ($return !== true) {
                    
                    $this->log->add(
                        JText::_("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_MAIL_SENDING_USER"),
                        "PLG_CONTENT_ADMIN_EMAIL_ERROR"
                    );
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
        $emailId = $this->params->get("send_when_create", 0);
        if(!$emailId) {
            return true;
        }
        
        if(!empty($project->id) AND $isNew) {
            
            $user     = JFactory::getUser();
            
            // Load class CrowdFundingEmail.
            jimport("crowdfunding.email");
            
            // Send email to the administrator.
            $return = $this->sendMails($project, $emailId);
            
            // Check for error.
            if ($return !== true) {
                
                $this->log->add(
                    JText::_("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_MAIL_SENDING_USER"),
                    "PLG_CONTENT_ADMIN_EMAIL_ERROR"
                );
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
        
        $query->select("a.title");
        $query->select($query->concatenate(array("a.id", "a.alias"), "-") . " AS slug");
        $query->select($query->concatenate(array("b.id", "b.alias"), "-") . " AS catslug");
        
        $query
            ->from($db->quoteName("#__crowdf_projects", "a"))
            ->leftJoin($db->quoteName("#__categories", "b") . " ON a.catid = b.id")
            ->where("a.id IN (". implode(",", $ids). ")");
        
        $db->setQuery($query);
        $results = $db->loadObjectList();
        
        if(!$results) {
            $results = array();
        }
        return $results;
    }
    
    protected function sendMails($project, $emailId) {
    
        $app = JFactory::getApplication();
        /** @var $app JSite **/
    
        // Get website
        $uri     = JUri::getInstance();
        $website = $uri->toString(array("scheme", "host"));
    
        $emailMode  = $this->params->get("email_mode", "plain");
    
        // Prepare data for parsing
        $data = array(
            "site_name"         => $app->getCfg("sitename"),
            "site_url"          => JUri::root(),
            "item_title"        => $project->title,
            "item_url"          => $website.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($project->slug, $project->catslug)),
        );
    
        // Send mail to the administrator
        if(!empty($emailId)) {
    
            $table    = new CrowdFundingTableEmail(JFactory::getDbo());
            $email    = new CrowdFundingEmail();
            $email->setTable($table);
            $email->load($emailId);
    
            if(!$email->getSenderName()) {
                $email->setSenderName($app->getCfg("fromname"));
            }
            if(!$email->getSenderEmail()) {
                $email->setSenderEmail($app->getCfg("mailfrom"));
            }
    
            $recipientName = $email->getSenderName();
            $recipientMail = $email->getSenderEmail();
    
            // Prepare data for parsing
            $data["sender_name"]     =  $email->getSenderName();
            $data["sender_email"]    =  $email->getSenderEmail();
            $data["recipient_name"]  =  $recipientName;
            $data["recipient_email"] =  $recipientMail;
    
            $email->parse($data);
            $subject    = $email->getSubject();
            $body       = $email->getBody($emailMode);
    
            $mailer  = JFactory::getMailer();
            if(strcmp("html", $emailMode) == 0) { // Send as HTML message
                
                $result  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_HTML);
    
            } else { // Send as plain text.
                
                $result  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_PLAIN);
    
            }
    
            return $result;
    
        }
    
    }
}