<?php
/**
 * @package      CrowdFunding
 * @subpackage   Library
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingTableEmail", JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_crowdfunding" . DIRECTORY_SEPARATOR . "tables" . DIRECTORY_SEPARATOR . "email.php");

class CrowdFundingEmail {
    
    const MAIL_MODE_HTML    = true;
    const MAIL_MODE_PLAIN   = false;
    
    protected $subject;
    protected $body;
    protected $senderName;
    protected $senderEmail;
    
    protected $table;
    
    protected $replaceable = array(
        "{SITE_NAME}", 
        "{SITE_URL}", 
        "{ITEM_TITLE}", 
        "{ITEM_URL}", 
        "{SENDER_NAME}", 
        "{SENDER_EMAIL}", 
        "{RECIPIENT_NAME}", 
        "{RECIPIENT_EMAIL}",
        "{AMOUNT}",
        "{TRANSACTION_ID}",
    );

    public function __construct($subject = "", $body = "") {
    
        $this->subject = $subject;
        $this->body    = $body;
    
    }
    
    /**
     * Set the class that manage an email record.
     *
     * @param JTable $table
     *
     * @return self
     *
     * <code>
     *
     * $email    = new CrowdFundingEmail();
     * $email->setTable(new CrowdFundingTableEmail(JFactory::getDbo()));
     *
     * </code>
     */
    public function setTable(JTable $table) {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Load an email data from database.
     *
     * @param $keys ID or Array with IDs
     * @param $reset Reset the record values.
     * 
     * @return self
     *
     * <code>
     *
     * $emailId  = 1;
     * $email    = new CrowdFundingEmail();
     * $email->setTable(new CrowdFundingTableEmail(JFactory::getDbo()));
     * $email->load($emailId);
     * 
     * </code>
     */
    public function load($keys, $reset = true) {
        
        $this->table->load($keys, $reset);
        $data = $this->table->getProperties();
        
        $this->bind($data);
        
        return $this;
    }

    public function bind($data) {
        
        $this->setSubject(JArrayHelper::getValue($data,"subject"));
        $this->setBody(JArrayHelper::getValue($data,"body"));
        $this->setSenderName(JArrayHelper::getValue($data,"sender_name"));
        $this->setSenderEmail(JArrayHelper::getValue($data,"sender_email"));
        
        return $this;
    }

    public function getId() {
        return $this->table->id;
    }

    public function setSubject($subject) {
        $this->subject = strip_tags($subject);
        return $this;
    }
    
    public function getSubject() {
        return strip_tags($this->subject);
    }

    public function setBody($body) {
        $this->body = $body;
        return $this;
    }
    
    /**
     * Return body of the message.
     * 
     * @param string Mail type - html or plain ( plain text ).
     * 
     * @return string
     * 
     * <code>
     * 
     * $emailId  = 1;
     * $email    = new CrowdFundingEmail();
     * $email->setTable(new CrowdFundingTableEmail(JFactory::getDbo()));
     * $email->load($emailId);
     * 
     * $body    = $item->getBody("plain");
     * 
     * </code>
     */
    public function getBody($mode = "html") {
        
        $mode = JString::strtolower($mode);
        if(strcmp("plain", $mode) == 0) {
            $body = str_replace("<br />", "\n", $this->body);
            $body = strip_tags($body);
            
            return $body;
        } else {
            return $this->body;
        }
        
    }

    public function setSenderName($name) {
        $this->senderName = $name;
        return $this;
    }

    public function getSenderName() {
        return $this->senderName;
    }

    public function setSenderEmail($email) {
        $this->senderEmail = $email;
        return $this;
    }

    public function getSenderEmail() {
        return $this->senderEmail;
    }
    
    public function parse($data) {
        
        foreach($data as $key => $value) {
            
            // Prepare flag
            $search = "{".JString::strtoupper($key)."}";
            
            // Parse subject
            $this->subject = str_replace($search, $value, $this->subject);
            
            // Parse body
            $this->body = str_replace($search, $value, $this->body);
            
        }
        
        return $this;
    }
    
}