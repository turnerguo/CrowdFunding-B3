<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

jimport("crowdfunding.statistics.transactions");

/**
 * This class loads statistics about transactions.
 */
class CrowdFundingStatisticsTransactionsLatest extends CrowdFundingStatisticsTransactions implements Iterator, Countable, ArrayAccess {
    
    public $data = array();
    
    protected $position = 0;

    /**
     * Load latest projects ordering by starting campaings.
     * 
     * @param number The number of results.
     */
    public function load($limit = 5) {
        
        $query = $this->getQuery();
        
        $query->order("a.txn_date DESC");
        
        $this->db->setQuery($query, 0, (int)$limit);
        
        $this->data = $this->db->loadAssocList();
        
        if(!$this->data) {
            $this->data = array();
        }
        
    }
    
    public function rewind() {
        $this->position = 0;
    }
    
    public function current() {
        return (!isset($this->data[$this->position])) ? null : $this->data[$this->position];
    }
    
    public function key() {
        return $this->position;
    }
    
    public function next() {
        ++$this->position;
    }
    
    public function valid() {
        return isset($this->data[$this->position]);
    }
    
    public function count() {
        return (int)count($this->data);
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}
