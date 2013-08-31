<?php
/**
 * @package      CrowdFunding
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This vpversion may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingTableIntention", JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_crowdfunding".DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."intention.php");

/**
 * This class provieds functionality that manage intention.
 * 
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingIntention {
    
    private $table;

    protected $db; 
    
    public function __construct($id) {
        
        $this->db       = JFactory::getDbo();
        $this->table    = new CrowdFundingTableIntention($this->db);
        
        if(!empty($id)) {
            $this->table->load($id);
        }
    }
    
    public function bind($src, $ignore = array()) {
        $this->table->bind($src, $ignore);
    }

    public function store($updateNulls = false) {
        $this->table->store($updateNulls);
    }
    
    public function getId() {
        return $this->table->id;
    }
    
    public function getUserId() {
        return $this->table->user_id;
    }
    
    public function getProjectId() {
        return $this->table->project_id;
    }
    
    public function getRewardId() {
        return $this->table->reward_id;
    }
    
    public function getRecordDate() {
        return $this->table->record_date;
    }
    
    public function getTransactionId() {
        return $this->table->txn_id;
    }
    
    public function getGateway() {
        return $this->table->gateway;
    }
    
    public function getTable() {
        return $this->table;
    }
    
    public function delete($pk = null) {
        $this->table->delete($pk);
    }
    
    public function __get($name) {
        
        if (isset($this->$name)) {
            return $this->$name;
        } else if (isset($this->table->$name)) {
            return $this->table->$name;
        }
        
        return null;
        
    }
}
