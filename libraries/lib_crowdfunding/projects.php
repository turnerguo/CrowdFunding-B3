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
 * This class provieds functionality that manage projects.
 */
class CrowdFundingProjects implements Iterator, Countable, ArrayAccess {
    
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
     * @param array     Projects IDs
     */
    public function __construct(JDatabase $db, $ids) {
        
        $this->db = $db;
        
        if(!is_array($ids)) {
            throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_PROJECT_IDS_ARRAY"));
        }
        $this->ids = $ids;
        
    }

    public function load($ids = array(), $options = array()) {
        
        // Set the newest ids.
        if(!empty($ids)) {
            
            if(!is_array($ids)) {
                throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_PROJECT_IDS_ARRAY"));
            }
            
            $this->ids = $ids;
        }
        
        // Load project data
        $query = $this->db->getQuery(true);
        
        $query
            ->select("a.id, a.title, a.alias")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id IN ( " . implode(",", $this->ids) ." )");
        
        // Filter by state published.
        $published = JArrayHelper::getValue($options, "published", 0, "int");
        if(!empty($published)) {
            $query->where("a.published = ". (int)$published);
        }
        
        // Filter by state approved.
        $approved = JArrayHelper::getValue($options, "approved", 0, "int");
        if(!empty($approved)) {
            $query->where("a.approved = ". (int)$approved);
        }
        
        $this->db->setQuery($query);
        $results = $this->db->loadObjectList();
        
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
     * Count and return rewards number.
     *
     * @return array
     */
    public function getRewardsNumber() {
    
        if(!$this->ids) {
            return array();
        }
    
        // Create a new query object.
        $query  = $this->db->getQuery(true);
    
        $query
            ->select("a.project_id, COUNT(*) as number")
            ->from($this->db->quoteName("#__crowdf_rewards") . " AS a")
            ->where("a.project_id IN (".implode(",", $this->ids) .")")
            ->group("a.project_id");
    
        $this->db->setQuery($query);
    
        $results = $this->db->loadObjectList("project_id");
    
        if(!$results) {
            $results=  array();
        }
        
        return $results;
    }
    
    /**
     * Count and return transactions number.
     *
     * @return array
     */
    public function getTransactionsNumber() {
    
        if(!$this->ids) {
            return array();
        }
    
        // Create a new query object.
        $query  = $this->db->getQuery(true);
    
        $query
            ->select("a.project_id, COUNT(*) as number")
            ->from($this->db->quoteName("#__crowdf_transactions") . " AS a")
            ->where("a.project_id IN (".implode(",", $this->ids) .")")
            ->group("a.project_id");
    
        $this->db->setQuery($query);
    
        $results = $this->db->loadObjectList("project_id");
    
        if(!$results) {
            $results=  array();
        }
    
        return $results;
    }
}
