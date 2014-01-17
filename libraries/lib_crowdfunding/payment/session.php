<?php
/**
 * @package      CrowdFunding
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provieds functionality that manage payment session.
 * 
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingPaymentSession {

    protected $id;
    protected $user_id;
    protected $project_id;
    protected $reward_id;
    protected $record_date;
    protected $txn_id;
    protected $token;
    protected $gateway;
    protected $auser_id;
    
    protected $intention_id;
    
    /**
     * 
     * @var JDatabase
     */
    protected $db;
    
    public function __construct(JDatabase $db, $id = 0) {

        $this->db = $db;
        if(!empty($id)) {
            $this->load($id);
        }
        
    }
    
    public function load($keys) {
        
        $query = $this->db->getQuery(true);
        $query
            ->select("
                a.id, a.user_id, a.project_id, a.reward_id, a.record_date,  
                a.txn_id, a.token, a.gateway, a.auser_id, a.intention_id")
            ->from($this->db->quoteName("#__crowdf_payment_sessions", "a"));
        
        if(is_numeric($keys)) {
            $query->where("a.id = ".(int)$keys);
        } else if(is_array($keys)){
            foreach($keys as $key => $value) {
                $query->where($this->db->quoteName("a.".$key) ."=". $this->db->quote($value));
            }
        } else {
            throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_INVALID_PAYMENTSESSION_KEYS"));
        }
        
        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();
        
        if(!$result) {
            $result = array();
        }
        
        $this->bind($result);
        
    }

    public function bind($data, $ignore = array()) {
        
        foreach($data as $key => $value) {
            if(!in_array($key, $ignore)) {
                $this->$key = $value;
            }
        }
    }
    
    public function store() {
        
        if(!$this->id) {
            $this->insertObject();
        } else {
            $this->updateObject();
        }
        
    }
    
    protected function insertObject() {
        
        $query = $this->db->getQuery(true);
        $query
            ->insert($this->db->quoteName("#__crowdf_payment_sessions"))
            ->set($this->db->quoteName("user_id") ."=". $this->db->quote($this->user_id))
            ->set($this->db->quoteName("project_id") ."=". $this->db->quote($this->project_id))
            ->set($this->db->quoteName("reward_id") ."=". $this->db->quote($this->reward_id))
            ->set($this->db->quoteName("record_date") ."=". $this->db->quote($this->record_date))
            ->set($this->db->quoteName("txn_id") ."=". $this->db->quote($this->txn_id))
            ->set($this->db->quoteName("token") ."=". $this->db->quote($this->token))
            ->set($this->db->quoteName("gateway") ."=". $this->db->quote($this->gateway))
            ->set($this->db->quoteName("auser_id") ."=". $this->db->quote($this->auser_id))
            ->set($this->db->quoteName("intention_id") ."=". $this->db->quote($this->intention_id));
        
        $this->db->setQuery($query);
        $this->db->execute();
        
        $this->id = $this->db->insertid();
        
    }
    
    protected function updateObject() {
    
        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName("#__crowdf_payment_sessions"))
            ->set($this->db->quoteName("user_id") ."=". $this->db->quote($this->user_id))
            ->set($this->db->quoteName("project_id") ."=". $this->db->quote($this->project_id))
            ->set($this->db->quoteName("reward_id") ."=". $this->db->quote($this->reward_id))
            ->set($this->db->quoteName("record_date") ."=". $this->db->quote($this->record_date))
            ->set($this->db->quoteName("txn_id") ."=". $this->db->quote($this->txn_id))
            ->set($this->db->quoteName("token") ."=". $this->db->quote($this->token))
            ->set($this->db->quoteName("gateway") ."=". $this->db->quote($this->gateway))
            ->set($this->db->quoteName("auser_id") ."=". $this->db->quote($this->auser_id))
            ->set($this->db->quoteName("intention_id") ."=". $this->db->quote($this->intention_id))
            ->where($this->db->quoteName("id") ."=". $this->db->quote($this->id));
    
        $this->db->setQuery($query);
        $this->db->execute();
    
    }
    
    public function delete() {
    
        $query = $this->db->getQuery(true);
        $query
            ->delete($this->db->quoteName("#__crowdf_payment_sessions"))
            ->where($this->db->quoteName("id") ."=".(int)$this->id);
    
        $this->db->setQuery($query);
        $this->db->execute();
    
        $this->reset();
    
    }
    
    public function reset() {
    
        $properties = $this->getProperties();
    
        foreach($properties as $key => $value) {
            $this->$key = null;
        }
    
    }
    
    
    public function getId() {
        return $this->id;
    }
    
    public function setUserId($userId) {
        $this->user_id = $userId;
        return $this;
    }
    
    public function getUserId() {
        return $this->user_id;
    }
    
    public function setAnonymousUserId($auserId) {
        $this->auser_id = $auserId;
        return $this;
    }
    
    public function getAnonymousUserId() {
        return $this->auser_id;
    }
    
    public function setProjectId($projectId) {
        $this->project_id = $projectId;
        return $this;
    }
    
    public function getProjectId() {
        return $this->project_id;
    }
    
    public function setRewardId($rewardId) {
        $this->reward_id = $rewardId;
        return $this;
    }
    
    public function getRewardId() {
        return $this->reward_id;
    }
    
    public function setRecordDate($recordDate) {
        $this->record_date = $recordDate;
        return $this;
    }
    
    public function getRecordDate() {
        return $this->record_date;
    }
    
    public function setTransactionId($txnId) {
        $this->txn_id = $txnId;
        return $this;
    }
    
    public function getTransactionId() {
        return $this->txn_id;
    }
    
    public function setGateway($gateway) {
        $this->gateway = $gateway;
        return $this;
    }
    
    public function getGateway() {
        return $this->gateway;
    }
    
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }
    
    public function getToken() {
        return $this->token;
    }
    
    public function setIntentionId($intentionId) {
        $this->intention_id = $intentionId;
        return $this;
    }
    
    public function getIntentionId() {
        return $this->intention_id;
    }
    
    public function isAnonymous() {
        return (!$this->auser_id) ? false : true;
    }
    
    public function getProperties() {
        $vars = get_object_vars($this);
        
        foreach($vars as $key => $value) {
            if(strcmp("db", $key) == 0) {
                unset($vars[$key]);
            }
        }
        return $vars;
    }
    
}
