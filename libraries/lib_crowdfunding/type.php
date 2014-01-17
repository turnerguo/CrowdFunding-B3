<?php
/**
 * @package      CrowdFunding
 * @subpackage   Library
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingTableType", JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_crowdfunding" . DIRECTORY_SEPARATOR . "tables" . DIRECTORY_SEPARATOR . "type.php");

class CrowdFundingType {
    
    protected $id           = "";
    protected $title        = "";
    protected $description  = "";
    protected $params       = array();
    
    public function __construct($title = "", $description = "") {
    
        $this->title        = $title;
        $this->description  = $description;
    
    }
    
    /**
     * Set the class that manage a type record.
     *
     * @param JTable $table
     *
     * @return self
     *
     * <code>
     *
     * $type    = new CrowdFundingType();
     * $type->setTable(new CrowdFundingTableType(JFactory::getDbo()));
     *
     * </code>
     */
    public function setTable(JTable $table) {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Load a type data from database.
     *
     * @param $keys ID or Array with IDs
     * @param $reset Reset the record values.
     * 
     * @return self
     *
     * <code>
     *
     * $typeId  = 1;
     * $type    = new CrowdFundingType();
     * $type->setTable(new CrowdFundingTableType(JFactory::getDbo()));
     * $type->load($typeId);
     * 
     * </code>
     */
    public function load($keys, $reset = true) {
        
        $this->table->load($keys, $reset);
        $data = $this->table->getProperties();
        
        $this->bind($data);
        
        return $this;
    }

    public function bind($data) {
        
        $this->setId(JArrayHelper::getValue($data, "id"));
        $this->setTitle(JArrayHelper::getValue($data, "title"));
        $this->setDescription(JArrayHelper::getValue($data, "description"));
        $this->setParams(JArrayHelper::getValue($data, "params"));
        
        return $this;
    }

    public function setId($id) {
        $this->id = (int)$id;
        return $this;
    }
    
    public function getId() {
        return $this->id;
    }


    public function setTitle($title) {
        $this->title = strip_tags($title);
        return $this;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }
    
    public function setParams($params) {
        
        if(is_string($params)) {
            
            $this->params = json_decode($params, true);
            
            // If is invalid JSON string, return empty array.
            if(!$this->params) {
                $this->params = array();
            }
            
        } else {
            
            if(is_object($params)) {
                $this->params = JArrayHelper::fromObject($params);
            } else if(is_array($params)) {
                $this->params = $params;
            }
            
        }
        
        return $this;
    }
    
    public function getParams() {
        return $this->params;
    }
    
    /**
     * Return description of the type.
     * 
     * @return string
     * 
     * <code>
     * 
     * $typeId  = 1;
     * $type    = new CrowdFundingType();
     * $type->setTable(new CrowdFundingTableType(JFactory::getDbo()));
     * $type->load($typeId);
     * 
     * $description = $type->getDescription();
     * 
     * </code>
     */
    public function getDescription() {
        return $this->description;
    }

    public function isRewardsEnabled() {
        
        $rewards = false;
        if(isset($this->params["rewards"]))  {
            $rewards = (!$this->params["rewards"]) ? false : true;
        } 
        
        return $rewards;
    }
    
}