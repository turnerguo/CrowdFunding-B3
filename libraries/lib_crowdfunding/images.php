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
 * This class provieds functionality that manage the additional images..
 */
class CrowdFundingImages implements Iterator, Countable, ArrayAccess {
    
    public $images = array();
    
    /**
     * Database driver
     * 
     * @var JDatabaseMySQLi
     */
    protected $db;
    
    protected $position = 0;
    
    protected static $instances = array();
    
    /**
     * Initialize the object and load the images. 
     * 
     * @param integer   Project ID
     * @param array     Options
     */
    public function __construct($id = 0, $options = array()) {
        
        $this->db = JFactory::getDbo();
        
        if(!empty($id)) {
            $this->images = $this->load($id, $options);
        }
    }

    public static function getInstance($id, $options = array())  {
    
        if (empty(self::$instances[$id])){
            $item = new CrowdFundingImages($id, $options);
            self::$instances[$id] = $item;
        }
        
        return self::$instances[$id];
    }
      
    public function load($id, $options = array()) {
        
        $query = $this->db->getQuery(true);
        
        $query
            ->select("a.id, a.image, a.thumb, a.project_id")
            ->from($this->db->quoteName("#__crowdf_images") . " AS a")
            ->where("a.project_id = " .(int)$id);
        
        $orderDest = JArrayHelper::getValue($options, "order_destination", "DESC");
        if(!empty($orderDest)) {
            $query->order("a.id ". $this->db->escape($orderDest));
        }
        
        $this->db->setQuery($query);
        $results = $this->db->loadObjectList();
        
        if(!$results) {
            $results = array();
        }
        
        return $results;
    }
    
    public function rewind() {
        $this->position = 0;
    }
    
    public function current() {
        return (!isset($this->images[$this->position])) ? null : $this->images[$this->position];
    }
    
    public function key() {
        return $this->position;
    }
    
    public function next() {
        ++$this->position;
    }
    
    public function valid() {
        return isset($this->images[$this->position]);
    }
    
    public function count() {
        return (int)count($this->images);
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->images[] = $value;
        } else {
            $this->images[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->images[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->images[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->images[$offset]) ? $this->images[$offset] : null;
    }
}
