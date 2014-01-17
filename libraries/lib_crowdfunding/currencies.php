<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

jimport("crowdfunding.currency");

/**
 * This class provieds functionality that manage currencies.
 */
class CrowdFundingCurrencies implements Iterator, Countable, ArrayAccess {
    
    protected $items  = array();
    
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
     * @param array     Projects IDs
     */
    public function __construct(JDatabase $db) {
        $this->db = $db;
    }

    public function load($ids = array()) {
        
        // Load project data
        $query = $this->db->getQuery(true);
        
        $query
            ->select("a.id, a.title, a.abbr, a.symbol, a.position")
            ->from($this->db->quoteName("#__crowdf_currencies", "a"));
    
        if(!empty($ids)) {
            JArrayHelper::toInteger($ids);
            $query->where("a.id IN ( " . implode(",", $ids) ." )");
        }
        
        $this->db->setQuery($query);
        $results = $this->db->loadObjectList();
        
        if(!$results) {
            $results = array();
        }
        
        $this->items = $results;
    }
    
    public function loadByAbbr($ids = array()) {
    
        // Load project data
        $query = $this->db->getQuery(true);
    
        $query
            ->select("a.id, a.title, a.abbr, a.symbol, a.position")
            ->from($this->db->quoteName("#__crowdf_currencies", "a"));
    
        if(!empty($ids)) {
            
            foreach($ids as $key => $value) {
                $ids[$key] = $this->db->quote($value);
            }
            
            $query->where("a.abbr IN ( " . implode(",", $ids) ." )");
        }
    
        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();
    
        if(!$results) {
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

    /**
     * Create a currency object by abbreviation and return it.
     * 
     * @param string $abbr
     * 
     * @throws UnexpectedValueException
     * 
     * @return CrowdFundingCurrency|NULL
     */
    public function getCurrencyByAbbr($abbr) {

        if(!$abbr) {
            throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_INVALID_CURRENCY_ABBREVIATION"));
        }
        
        $currency = null;
        
        foreach($this->items as $item) {
            if(strcmp($abbr, $item["abbr"]) == 0) {
                
                $currency = new CrowdFundingCurrency(JFactory::getDbo());
                $currency->bind($item);
                
                break;
            }
        }
        
        return $currency;
    }
    
    /**
     * Create a currency object and return it.
     *
     * @param string $id
     *
     * @throws UnexpectedValueException
     *
     * @return CrowdFundingCurrency|NULL
     */
    public function getCurrency($id) {
    
        if(!$id) {
            throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_INVALID_CURRENCY_ID"));
        }
    
        $currency = null;
    
        foreach($this->items as $item) {
            
            if($id == $item["id"]) {
    
                $currency = new CrowdFundingCurrency(JFactory::getDbo());
                $currency->bind($item);
    
                break;
            }
            
        }
    
        return $currency;
    }
    
}
