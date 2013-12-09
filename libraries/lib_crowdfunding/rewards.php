<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

/**
 * This class provieds functionality that manage rewards.
 */
class CrowdFundingRewards implements Iterator, Countable, ArrayAccess {
    
    public $rewards = array();
    
    /**
     * Database driver.
     * 
     * @var JDatabaseMySQLi
     */
    protected $db;
    
    protected $position = 0;
    
    protected static $instances = array();
    
    /**
     * Load or set rewards. 
     * 
     * @param integer   $id      Project ID
     * @param array     $rewards Rewards
     */
    public function __construct($id = 0, $options = array()) {
        
        $this->db = JFactory::getDbo();
        
        if(!empty($id)) {
            $this->load($id, $options);
        }
    }

    public static function getInstance($id, $options = array())  {
    
        if (empty(self::$instances[$id])){
            $item = new CrowdFundingRewards($id, $options);
            self::$instances[$id] = $item;
        }
        
        return self::$instances[$id];
    }
      
    
    public function load($id, $options = array()) {
        
        $query = $this->db->getQuery(true);
        
        $query
            ->select("a.id, a.title, a.description, a.amount")
            ->from($this->db->quoteName("#__crowdf_rewards") . " AS a")
            ->where("a.project_id = " .(int)$id);
        
        // Get state
        $state = JArrayHelper::getValue($options, "state", 0, "int");
        if(!empty($state)) {
            $query->where("a.published = ". (int)$state);
        }
        
        $this->db->setQuery($query);
        $results = $this->db->loadObjectList();
        
        if(!$results) {
            $results = array();
        }
        
        $this->rewards = $results;
    }
    
    public function rewind() {
        $this->position = 0;
    }
    
    public function current() {
        return (!isset($this->rewards[$this->position])) ? null : $this->rewards[$this->position];
    }
    
    public function key() {
        return $this->position;
    }
    
    public function next() {
        ++$this->position;
    }
    
    public function valid() {
        return isset($this->rewards[$this->position]);
    }
    
    public function count() {
        return (int)count($this->rewards);
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->rewards[] = $value;
        } else {
            $this->rewards[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->rewards[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->rewards[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->rewards[$offset]) ? $this->rewards[$offset] : null;
    }
}
