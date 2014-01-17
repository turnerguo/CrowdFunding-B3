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
 * This class provieds functionality that manage transactions.
 */
class CrowdFundingTransactions implements Iterator, Countable, ArrayAccess {
    
    protected $items  = array();
    protected $ids    = array();
    
    /**
     * Database driver.
     * 
     * @var JDatabaseMySQLi
     */
    protected $db;
    
    protected $position = 0;
    
    /**
     * Initialize the object.
     * 
     * @param JDatabase Database object.
     */
    public function __construct(JDatabase $db) {
        $this->db = $db;
    }

    public function load($ids, $options = array()) {
        
        // Set the newest ids.
        if(!is_array($ids)) {
            throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_TRANSACTIONS_IDS_ARRAY"));
        }
        $this->ids = $ids;
        
        // Load project data
        $query = $this->db->getQuery(true);
        
        $query
            ->select("a.id, a.txn_date, a.txn_id, a.txn_amount, a.txn_currency, a.txn_status, " .
                    "a.extra_data, a.status_reason, a.project_id, a.reward_id, a.investor_id, " .
                    "a.receiver_id, a.service_provider, a.reward_state")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.id IN ( " . implode(",", $this->ids) ." )");
        
        // Filter by status.
        $status = JArrayHelper::getValue($options, "txn_status", null, "cmd");
        if(!empty($status)) {
            $query->where("a.txn_status = ". $this->db->quote($status));
        }
        
        $this->db->setQuery($query);
        $results = $this->db->loadObjectList();
        
        if(!empty($results)) {
            foreach($results as $key => $result) {
                if(!empty($result->extra_data)) {
                    $result->extra_data = json_decode($result->extra_data, true);
                    $results[$key]      = $result;
                }
            }
        } else {
            $results = array();
        }
        
        $this->items = $results;
    }
    
    public function rewind() {
        $this->position = 0;
    }
    
    public function current() {
        return (!isset($this->items[$this->position])) ? null : $this->items[$this->position];
    }
    
    public function key() {
        return $this->position;
    }
    
    public function next() {
        ++$this->position;
    }
    
    public function valid() {
        return isset($this->items[$this->position]);
    }
    
    public function count() {
        return (int)count($this->items);
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->items[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->items[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }
    
}
