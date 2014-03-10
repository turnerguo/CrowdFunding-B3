<?php
/**
 * @package      CrowdFunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * CrowdFunding payment plugin class.
 *
 * @package      CrowdFunding
 * @subpackage   Plugin
 */
class CrowdFundingPaymentPlugin extends JPlugin {
    
    protected   $paymentService;
    
    protected   $log;
    protected   $textPrefix;
    protected   $debugType;
    
    public function __construct(&$subject, $config = array()) {
    
        parent::__construct($subject, $config);
    
        // Prepare log object
        $registry  = JRegistry::getInstance("com_crowdfunding");
        
        $fileName  = $registry->get("logger.file");
        $tableName = $registry->get("logger.table");
        
        // Create log object
        $this->log = new ITPrismLog();
        
        // Set database writer.
        $this->log->addWriter(new ITPrismLogWriterDatabase(JFactory::getDbo(), $tableName));
        
        // Set file writer.
        if(!empty($fileName)) {
            $file = JPath::clean(JFactory::getApplication()->getCfg("log_path") .DIRECTORY_SEPARATOR. $fileName);
            $this->log->addWriter(new ITPrismLogWriterFile($file));
        }
    
        // Load language
        $this->loadLanguage();
    }
    
    
    protected function updateReward(&$data) {
        
        // Get reward.
        $keys   = array(
        	"id"         => JArrayHelper::getValue($data, "reward_id"), 
        	"project_id" => JArrayHelper::getValue($data, "project_id")
        );
        
        jimport("crowdfunding.reward");
        $reward = new CrowdFundingReward($keys);
        
        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_REWARD_OBJECT"), $this->debugType, $reward->getProperties()) : null;
        
        // Check for valid reward.
        if(!$reward->getId()) {
            
            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix."_ERROR_INVALID_REWARD"),
                $this->debugType,
                array("data" => $data, "reward object" => $reward->getProperties())
            );
            
			$data["reward_id"] = 0;
			return null;
        }
        
        // Check for valida amount between reward value and payed by user
        $txnAmount = JArrayHelper::getValue($data, "txn_amount");
        if($txnAmount < $reward->getAmount()) {
            
            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix."_ERROR_INVALID_REWARD_AMOUNT"),
                $this->debugType,
                array("data" => $data, "reward object" => $reward->getProperties())
            );
            
			$data["reward_id"] = 0;
			return null;
        }
        
        // Verify the availability of rewards
        if($reward->isLimited() AND !$reward->getAvailable()) {
            
            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix."_ERROR_REWARD_NOT_AVAILABLE"),
                $this->debugType,
                array("data" => $data, "reward object" => $reward->getProperties())
            );
            
			$data["reward_id"] = 0;
			return null;
        }
        
        // Increase the number of distributed rewards 
        // if there is a limit.
        if($reward->isLimited()) {
            $reward->increaseDistributed();
            $reward->store();
        }
        
        return $reward;
    }
    
    /**
     * This method is invoked when the administrator changes transaction status from the backend.
     *
     * @param string 	This string gives information about that where it has been executed the trigger.
     * @param object 	A transaction data.
     * @param string    Old status
     * @param string    New status
     *
     * @return void
     */
    public function onTransactionChangeState($context, $item, $oldStatus, $newStatus) {
    
        $allowedContexts = array("com_crowdfunding.transaction", "com_crowdfundingfinance.transaction");
        if(!in_array($context, $allowedContexts)){
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
    
        if($app->isSite()) {
            return;
        }
    
        $doc     = JFactory::getDocument();
        /**  @var $doc JDocumentHtml **/
    
        // Check document type
        $docType = $doc->getType();
        if(strcmp("html", $docType) != 0){
            return;
        }
         
        // Verify the service provider.
        $paymentService = str_replace(" ", "", JString::strtolower(JString::trim($item->service_provider)));
        if(strcmp($this->paymentService, $paymentService) != 0) {
            return;
        }
    
        if(strcmp($oldStatus, "completed") == 0) { // Remove funds if someone change the status from completed to other one.
    
            jimport("crowdfunding.project");
            $project = new CrowdFundingProject($item->project_id);
    
            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_BCSNC"), $this->debugType, $project->getProperties()) : null;
    
            $project->removeFunds($item->txn_amount);
            $project->store();
    
            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_ACSNC"), $this->debugType, $project->getProperties()) : null;
    
        } else if(strcmp($newStatus, "completed") == 0) { // Add funds if someone change the status to completed.
    
            jimport("crowdfunding.project");
            $project = new CrowdFundingProject($item->project_id);
    
            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_BCSTC"), $this->debugType, $project->getProperties()) : null;
    
            $project->addFunds($item->txn_amount);
            $project->store();
    
            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix."_DEBUG_ACSTC"), $this->debugType, $project->getProperties()) : null;
        }
    
    }
    
    protected function sendMails($project, $transaction) {
    
        $app = JFactory::getApplication();
        /** @var $app JSite **/
    
        // Get website
        $uri     = JUri::getInstance();
        $website = $uri->toString(array("scheme", "host"));
    
        jimport("itprism.string");
        jimport("crowdfunding.email");
    
        $emailMode  = $this->params->get("email_mode", "plain");
    
        jimport("crowdfunding.string");
        $string = new ITPrismString($transaction->txn_amount);
        
        // Prepare data for parsing
        $data = array(
            "site_name"         => $app->getCfg("sitename"),
            "site_url"          => JUri::root(),
            "item_title"        => $project->title,
            "item_url"          => $website.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($project->slug, $project->catslug)),
            "amount"            => $string->getAmount($transaction->txn_currency),
            "transaction_id"    => $transaction->txn_id
        );
    
        // Send mail to the administrator
        $emailId = $this->params->get("admin_mail_id", 0);
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
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_HTML);
    
            } else { // Send as plain text.
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_PLAIN);
    
            }
    
            // Check for an error.
            if ($return !== true) {
    
                // Log error
                $this->log->add(
                    JText::_($this->textPrefix."_ERROR_MAIL_SENDING_ADMIN"),
                    $this->debugType
                );
    
            }
    
        }
    
        // Send mail to project owner
        $emailId = $this->params->get("creator_mail_id", 0);
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
    
            $user          = JFactory::getUser($transaction->receiver_id);
            $recipientName = $user->get("name");
            $recipientMail = $user->get("email");
    
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
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_HTML);
    
            } else { // Send as plain text.
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_PLAIN);
    
            }
    
            // Check for an error.
            if ($return !== true) {
    
                // Log error
                $this->log->add(
                    JText::_($this->textPrefix."_ERROR_MAIL_SENDING_PROJECT_OWNER"),
                    $this->debugType
                );
    
            }
        }
    
        // Send mail to backer
        $emailId    = $this->params->get("user_mail_id", 0);
        $investorId = $transaction->investor_id;
        if(!empty($emailId) AND !empty($investorId)) {
    
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
    
            $user          = JFactory::getUser($investorId);
            $recipientName = $user->get("name");
            $recipientMail = $user->get("email");
    
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
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_HTML);
    
            } else { // Send as plain text.
                $return  = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingEmail::MAIL_MODE_PLAIN);
    
            }
    
            // Check for an error.
            if ($return !== true) {
    
                // Log error
                $this->log->add(
                    JText::_($this->textPrefix."_ERROR_MAIL_SENDING_USER"),
                    $this->debugType
                );
    
            }
    
        }
    
    }
    
    /**
     * This method returns intention
     * based on user ID or anonymous hash user ID.
     *
     * @param array     The keys used to load data of the intention from database.
     *
     * @return CrowdFundingIntention
     */
    public function getIntention(array $options) {
    
        $userId    = JArrayHelper::getValue($options, "user_id");  
        $aUserId   = JArrayHelper::getValue($options, "auser_id");
        $projectId = JArrayHelper::getValue($options, "project_id");
        $token     = JArrayHelper::getValue($options, "token");
        $txnId     = JArrayHelper::getValue($options, "txn_id");
        
        // Prepare keys for anonymous user.
        if(!empty($aUserId)) {
    
            $intentionKeys = array(
                "auser_id"   => $aUserId,
                "project_id" => $projectId
            );
    
        } else if(!empty($userId)) {// Prepare keys for registered user.
    
            $intentionKeys = array(
                "user_id"    => $userId,
                "project_id" => $projectId
            );
    
        } else if(!empty($token)) { // Prepare keys for token.
            
            $intentionKeys = array(
                "token"    => $token,
            );
            
        } else if(!empty($txnId)) { // Prepare keys for transaction ID.
            
            $intentionKeys = array(
                "txn_id"   => $txnId,
            );
            
        } else {
            throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_INVALID_INTENTION_KEYS"));
        }
    
        jimport("crowdfunding.intention");
        $intention = new CrowdFundingIntention($intentionKeys);
    
        return $intention;
    }
    
}