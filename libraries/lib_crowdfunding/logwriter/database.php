<?php
/**
 * @package      CrowdFunding
 * @subpackage   Library
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingTableLog", JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_crowdfunding".DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."log.php");
JLoader::register("CrowdFundingLogWriter", JPATH_LIBRARIES . DIRECTORY_SEPARATOR . "crowdfunding" . DIRECTORY_SEPARATOR . "interface" . DIRECTORY_SEPARATOR . "logwriter.php");

class CrowdFundingLogWriterDatabase extends CrowdFundingTableLog implements CrowdFundingLogWriter {
    
    public $id;
    public $title;
    public $type;
    public $data;
    public $record_date;
    
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
    
    public function setType($type) {
        $this->type = $type;
        return $this;
    }
    
    public function setData($data) {
        $this->data = $data;
        return $this;
    }
    
    public function setDate($date) {
        
        $date = new JDate($date);
        
        $this->record_date = $date->toSql();
        return $this;
        
    }
    
    public function resetKey() {
    
        $key = $this->getKeyName();
        $this->$key  = null;
    
    }
    
    public function store() {
        
        $this->resetKey();
        
        parent::store(true);
    }
    
}