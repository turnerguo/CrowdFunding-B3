<?php
/**
 * @package      CrowdFunding
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingTableTransaction", JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_crowdfunding".DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."transaction.php");
JLoader::register("CrowdFundingInterfaceTable", JPATH_LIBRARIES .DIRECTORY_SEPARATOR."crowdfunding".DIRECTORY_SEPARATOR."interface".DIRECTORY_SEPARATOR."table.php");

/**
 * This class provieds functionality that manage transactions.
 * 
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingTransaction implements CrowdFundingInterfaceTable {
    
    /**
     * This is CrowdFunding Transaction table object.
     *
     * @var CrowdFundingTableTransaction
     */
    protected $table;
    
    public function __construct($id = 0) {
        
        $this->table = new CrowdFundingTableTransaction(JFactory::getDbo());
        
        if(!empty($id)) {
            $this->table->load($id);
        }
        
    }
    
    public function load($keys, $reset = true) {
        $this->table->load($keys, $reset);
    }
    
    public function bind($src, $ignore = array()) {
        
        // Encode extra data to JSON format. 
        foreach($src as $key => $value) {
            if(strcmp("extra_data", $key) == 0) {
                if(is_array($value) OR is_object($value)) {
                    $src[$key] = json_encode($value);
                }
                break;
            }
        }
        
        $this->table->bind($src, $ignore);
    }
    
    public function store($updateNulls = false) {
        $this->table->store($updateNulls);
    }
    
    public function getId() {
        return $this->table->id;
    }
    
    public function isCompleted() {
        $result = (strcmp("completed", $this->table->txn_status) == 0);
        return (bool)$result;
    }
    
    public function isPending() {
        $result = (strcmp("pending", $this->table->txn_status) == 0);
        return (bool)$result;
    }
    
    public function getStatus() {
        return $this->table->txn_status;
    }
    
    public function getAmount() {
        return $this->table->txn_amount;
    }
    
    public function getCurrency() {
        return $this->table->txn_currency;
    }
    
    public function getProperties($public = true) {
        return $this->table->getProperties($public);
    }
    
    public function getTransactionId() {
        return $this->table->txn_id;
    }
    
    public function getInvestorId() {
        return $this->table->investor_id;
    }
    
    public function getReceiverId() {
        return $this->table->receiver_id;
    }
    
    public function getExtraData() {
        
        if(is_string($this->table->extra_data)) {
            $extraData = json_decode($this->table->extra_data, true);
        }
        
        if(!$extraData OR !is_array($extraData)) {
            $extraData = array();
        }
        
        return $extraData;
    }
    
}
