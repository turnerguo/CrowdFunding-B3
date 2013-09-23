<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingTableReward", JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_crowdfunding".DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."reward.php");
JLoader::register("CrowdFundingInterfaceTable", JPATH_LIBRARIES .DIRECTORY_SEPARATOR."crowdfunding".DIRECTORY_SEPARATOR."interface".DIRECTORY_SEPARATOR."table.php");

/**
 * This class provieds functionality that manage rewards.
 */
class CrowdFundingReward implements CrowdFundingInterfaceTable {
    
    protected $table;
    protected $available = 0;
    
    public function __construct($id = 0) {
        
        $this->table = new CrowdFundingTableReward(JFactory::getDbo());
        $this->load($id);
        
        // Calculate available
        $this->available = $this->calcualteAvailable();
        
    }

    public function load($keys = null, $reset = true) {
        $this->table->load($keys, $reset);
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
    
    public function getTitle() {
        return $this->table->title;
    }
    
    public function getDescription() {
        return $this->table->description;
    }
    
    public function getAmount() {
        return $this->table->amount;
    }
    
    /**
     * Increase the number of distributed rewards.
     * 
     * @param integer
     */
    public function increaseDistributed($number = 1) {
        
        $distributed  = $this->table->distributed + $number;
        
        if($distributed <= $this->number) {
            $this->table->distributed = $distributed;
            $this->available   = $this->table->number - $this->table->distributed;
        }
        
    }
    
    /**
     * Check for the type "limited" of the reward. 
     * If there is a number of rewards, it is limited.
     * 
     * @return bool
     */
    public function isLimited() {
        return ( !empty($this->table->number) ) ? true : false;
    }
    
    /**
     * Calculate the number of the rewards, 
     * which are available for distribution
     * 
     * @return integer
     */
    protected function calcualteAvailable() {
    
        if($this->isLimited()) {
            return $this->table->number - $this->table->distributed;
        }
        
        return 0;
    }
    
    /**
     * Return the number of the available rewards.
     * 
     * @return integer
     */
    public function getAvailable() {
        return $this->available;
    }
    
    public function getProperties() {
        return $this->table->getProperties();
    }
}
