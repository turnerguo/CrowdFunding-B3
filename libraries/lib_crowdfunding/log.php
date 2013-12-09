<?php
/**
 * @package      CrowdFunding
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingTableLog", JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_crowdfunding".DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."log.php");
JLoader::register("CrowdFundingInterfaceTable", JPATH_LIBRARIES.DIRECTORY_SEPARATOR."crowdfunding".DIRECTORY_SEPARATOR."interface".DIRECTORY_SEPARATOR."table.php");

/**
 * This class provides functionality that manages logs.
 * 
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingLog {
    
    protected $title;
    protected $type;
    protected $data;
    protected $recordDate;
    
    protected $writers = array();
    
	public function __construct($title = "", $type = "", $data = null ) {
        
        $this->setTitle($title);
        $this->setType($type);
        $this->setData($data);
            
    }
    
    public function addWriter(CrowdFundingLogWriter $writer) {
        $this->writers[] = $writer;
    }
    
    /**
     * @return the $title
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * @return the $type
     */
    public function getType() {
        return $this->type;
    }
    
    /**
     * @return the $data
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * @return the $record_date
     */
    public function getRecordDate() {
        return $this->recordDate;
    }
    
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
    
    public function setType($type) {
        $this->type = $type;
        return $this;
    }
    
    /**
     * Set data, information, etc.
     * 
     * @param string $data
     * @return self
     */
    public function setData($data) {
        
        if(!is_scalar($data)) {
            $data = var_export($data, true);
        }
        
        $this->data = $data;
        return $this;
    }
    
    public function setRecordDate($date) {
        $this->recordDate = $date;
        return $this;
    }
    
    /**
     * Store an information.
     *
     * @param string  $title
     * @param string  $type
     * @param mixed   $data
     */
    public function add($title, $type, $data = null) {
    
        $this->setTitle($title);
        $this->setType($type);
        $this->setData($data);
        
        $date       = new JDate();
        $this->setRecordDate($date->__toString());
        
        foreach($this->writers as $writer) {
            
            $writer
                ->setTitle($this->getTitle())
                ->setType($this->getType())
                ->setData($this->getData())
                ->setDate($this->getRecordDate())
                ->store();
                
        }
    
    }
    
}
