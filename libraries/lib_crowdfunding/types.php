<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

jimport("crowdfunding.type");

/**
 * This class provieds functionality that manage types.
 */
class CrowdFundingTypes implements Iterator, Countable, ArrayAccess {
    
    public $types = array();
    
    /**
     * Database driver.
     * 
     * @var JDatabaseMySQLi
     */
    protected $db;
    
    protected $position = 0;
    
    protected static $instance;
    
    /**
     * Create an object and load types.
     * 
     * @param JDatabase  Database object
     * @param array      Options
     */
    public function __construct(JDatabase $db, $options = array()) {
        
        $this->db = $db;
        $this->load($options);
        
    }

    public static function getInstance(JDatabase $db, $options = array())  {
    
        if (is_null(self::$instance)){
            self::$instance = new CrowdFundingTypes($db, $options);
        }
        
        return self::$instance;
    }
      
    
    public function load($options = array()) {
        
        $this->types = array();
        
        $orderString = "";
        
        $query = $this->db->getQuery(true);
        
        $query
            ->select("a.id, a.title, a.description, a.params")
            ->from($this->db->quoteName("#__crowdf_types", "a"));
        
        // Order by column
        if(isset($options["order_column"])) {
            $orderString .= $this->db->quoteName($options["order_column"]);
        }

        // Order direction
        if(isset($options["order_direction"])) {
            $orderString .= (strcmp("DESC", $options["order_direction"])) ? " DESC" : " ASC";
        }
        
        if(!empty($orderString)) {
            $query->order($orderString);
        }
        
        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();
        
        if(!empty($results)) {
            
            foreach($results as $result) {
                $type = new CrowdFundingType();
                $type->bind($result);
                $this->types[] = $type;
            }
            
        } else {
            $this->types = array();
        }
        
    }
    
    public function rewind() {
        $this->position = 0;
    }
    
    public function current() {
        return (!isset($this->types[$this->position])) ? null : $this->types[$this->position];
    }
    
    public function key() {
        return $this->position;
    }
    
    public function next() {
        ++$this->position;
    }
    
    public function valid() {
        return isset($this->types[$this->position]);
    }
    
    public function count() {
        return (int)count($this->types);
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->types[] = $value;
        } else {
            $this->types[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->types[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->types[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->types[$offset]) ? $this->types[$offset] : null;
    }
    
    public function getTypesAsOptions() {
        
        $options = array();
        
        foreach($this->types as $type) {
            $options[] = array("text" => $type->getTitle(), "value" => $type->getId());
        }
        
        return $options;
    }
}
