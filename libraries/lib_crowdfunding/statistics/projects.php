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
 * This class loads statistics about projects.
 */
class CrowdFundingStatisticsProjects implements Iterator, Countable, ArrayAccess {
    
    public $data = array();
    
    /**
     * Database driver
     * 
     * @var JDatabaseMySQLi
     */
    protected $db;
    
    protected $position = 0;
    
    /**
     * Initialize the object and load the data.
     * 
     * @param integer   Project ID
     * @param string    The type of the statistics ( funded, popular, latest,...)
     * @param integer   The number of results, which will be loaded.
     */
    public function __construct($type, $limit = 5) {
        
        $this->db   = JFactory::getDbo();
        $this->data = $this->load($type, $limit);
    }

    public function load($type, $limit) {
        
        switch($type) {
            
            case "funded":
                $this->data = $this->getMostFunded($limit);
                break;
                
            case "popular":
                $this->data = $this->getPopular($limit);
                break;
                
            case "latest":
                $this->data = $this->getLatest($limit);
                break;
                
            default:
                $this->data = array();
                break;
                
        }
        
        return $this->data;
    }
    
    protected function getMostFunded($limit) {
        
        // Get current date
        jimport("joomla.date.date");
        $date        = new JDate();
        $currentDate = $date->toSql();
        
        $query = $this->db->getQuery(true);
        
        $query
            ->select(
                "a.title, a.short_desc, a.image_square, a.goal, a.funded, a.funding_start, a.funding_end, a.funding_days, " .
                $query->concatenate(array("a.id", "a.alias"), ":") . " AS slug, " .
                $query->concatenate(array("b.id", "b.alias"), ":") . " AS catslug"
            )
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->leftJoin($this->db->quoteName("#__categories", "b") . " ON a.catid = b.id")
            ->where("( a.published = 1 AND a.approved = 1 )")
            ->where("( a.funding_start <= ". $this->db->quote($currentDate)." AND a.funding_end >= ". $this->db->quote($currentDate)." )")
            ->order("a.funded DESC");
        
        if(!$limit) {
            $limit = 5;
        }
        
        $this->db->setQuery($query, 0, (int)$limit);
        
        return $this->db->loadObjectList();
        
    }
    
    protected function getPopular($limit) {
    
        // Get current date
        jimport("joomla.date.date");
        $date        = new JDate();
        $currentDate = $date->toSql();
        
        $query = $this->db->getQuery(true);
        
        $query
        ->select(
            "a.title, a.short_desc, a.image_square, a.goal, a.funded, a.funding_start, a.funding_end, a.funding_days, " .
            $query->concatenate(array("a.id", "a.alias"), ":") . " AS slug, " .
            $query->concatenate(array("b.id", "b.alias"), ":") . " AS catslug"
        )
        ->from($this->db->quoteName("#__crowdf_projects", "a"))
        ->leftJoin($this->db->quoteName("#__categories", "b") . " ON a.catid = b.id")
        ->where("( a.published = 1 AND a.approved = 1 )")
        ->where("( a.funding_start <= ". $this->db->quote($currentDate)." AND a.funding_end >= ". $this->db->quote($currentDate)." )")
        ->order("a.hits DESC");
        
        if(!$limit) {
            $limit = 5;
        }
        
        $this->db->setQuery($query, 0, (int)$limit);
        
        return $this->db->loadObjectList();
    }
    
    protected function getLatest($limit) {
    
        // Get current date
        jimport("joomla.date.date");
        $date        = new JDate();
        $currentDate = $date->toSql();
        
        $query = $this->db->getQuery(true);
        
        $query
        ->select(
            "a.title, a.short_desc, a.image_square, a.goal, a.funded, a.funding_start, a.funding_end, a.funding_days, " .
            $query->concatenate(array("a.id", "a.alias"), ":") . " AS slug, " .
            $query->concatenate(array("b.id", "b.alias"), ":") . " AS catslug"
        )
        ->from($this->db->quoteName("#__crowdf_projects") . " AS a")
        ->innerJoin($this->db->quoteName("#__categories") . " AS b ON a.catid = b.id")
        ->where("a.published = 1")
        ->where("a.approved = 1")
        ->order("a.funding_start DESC");
        
        if(!$limit) {
            $limit = 5;
        }
        
        $this->db->setQuery($query, 0, (int)$limit);
        
        return $this->db->loadObjectList();
        
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
