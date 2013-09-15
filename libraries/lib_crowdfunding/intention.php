<?php
/**
 * @package      CrowdFunding
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingTableIntention", JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_crowdfunding".DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."intention.php");
JLoader::register("CrowdFundingInterfaceTable", JPATH_LIBRARIES .DIRECTORY_SEPARATOR."crowdfunding".DIRECTORY_SEPARATOR."interface".DIRECTORY_SEPARATOR."table.php");

/**
 * This class provieds functionality that manage intentions.
 * 
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingIntention implements CrowdFundingInterfaceTable {
    
    protected $table;

    public function __construct($id) {
        
        $this->table    = new CrowdFundingTableIntention(JFactory::getDbo());
        $this->load($id);
        
    }
    
    public function bind($src, $ignore = array()) {
        $this->table->bind($src, $ignore);
    }
    
    public function load($keys = null, $reset = true) {
        $this->table->load($keys, $reset);
    }

    public function store($updateNulls = false) {
        $this->table->store($updateNulls);
    }
    
    public function getId() {
        return $this->table->id;
    }
    
    public function getUserId() {
        return $this->table->user_id;
    }
    
    public function getProjectId() {
        return $this->table->project_id;
    }
    
    public function getRewardId() {
        return $this->table->reward_id;
    }
    
    public function getRecordDate() {
        return $this->table->record_date;
    }
    
    public function getTransactionId() {
        return $this->table->txn_id;
    }
    
    public function getGateway() {
        return $this->table->gateway;
    }
    
    public function getTable() {
        return $this->table;
    }
    
    public function delete($pk = null) {
        $this->table->delete($pk);
    }
    
    public function isAnonymous() {
        return (!$this->table->auser_id) ? false : true;
    }
    
    public function getProperties() {
        return $this->table->getProperties();
    }
}
