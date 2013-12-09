<?php
/**
 * @package      CrowdFunding
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingTableProject", JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_crowdfunding".DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."project.php");
JLoader::register("CrowdFundingInterfaceTable", JPATH_LIBRARIES .DIRECTORY_SEPARATOR."crowdfunding".DIRECTORY_SEPARATOR."interface".DIRECTORY_SEPARATOR."table.php");

/**
 * This class provieds functionality that manage projects.
 * 
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingProject implements CrowdFundingInterfaceTable {
    
    protected $rewards = null;
    protected $type    = null;
    
    protected static $instances = array();
    
    public function __construct($id) {
        
        $this->table = new CrowdFundingTableProject(JFactory::getDbo());
        
        if(!empty($id)) {
            $this->table->load($id);
        }
    }

    public static function getInstance($id = 0)  {
    
        if (empty(self::$instances[$id])){
            $item = new CrowdFundingProject($id);
            self::$instances[$id] = $item;
        }
    
        return self::$instances[$id];
    }
    
    public function load($keys, $reset = true) {
        $this->table->load($keys, $reset);
    }
    
    public function bind($src, $ignore = array()) {
        $this->table->bind($src, $ignore);
    }
    
    public function store($updateNulls = false) {
        $this->table->store($updateNulls);
    }
    
	/**
     * Add a new amount to current funded one.
     * Calculate funded percent.
     * 
     * @param float $amount
     */
    public function addFunds($amount) {
        $this->table->funded         = $this->table->funded + $amount;
        $this->table->setFundedPercents(CrowdFundingHelper::calculatePercent($this->table->funded, $this->table->goal));
    }
    
    /**
     * Remove a some amount from current funded one.
     * Calculate funded percent.
     * 
     * @param float $amount
     */
    public function removeFunds($amount) {
        $this->table->funded         = $this->table->funded - $amount;
        $this->table->setFundedPercents(CrowdFundingHelper::calculatePercent($this->table->funded, $this->table->goal));
    }
    
    public function getRewards($options = array()) {
        
        if(is_null($this->rewards)) {
            jimport("crowdfunding.rewards");
            $this->rewards = CrowdFundingRewards::getInstance($this->table->id, $options);
        }
        
        return $this->rewards;
    }
    
    /**
     * @return the $fundedPercents
     */
    public function getFundedPercents() {
        return $this->table->getFundedPercents();
    }
    
    /**
     * @return the $daysLeft
     */
    public function getDaysLeft() {
        return $this->table->getDaysLeft();
    }
    
    public function getSlug() {
        return $this->table->getSlug();
    }
    
    public function getCatSlug() {
        return $this->table->getCatSlug();
    }
    
    public function getId() {
        return $this->table->id;
    }
    
    public function getUserId() {
        return $this->table->user_id;
    }
    
    public function getTitle() {
        return $this->table->title;
    }
    
    public function getGoal() {
        return $this->table->goal;
    }
    
    public function getFunded() {
        return $this->table->funded;
    }
    
    public function getFundingType() {
        return $this->table->funding_type;
    }
    
    public function getFundingEnd() {
        return $this->table->funding_end;
    }
    
    public function getImage() {
        return $this->table->image;
    }
    
    public function getSquareImage() {
        return $this->table->image_square;
    }
    
    public function getSmallImage() {
        return $this->table->image_small;
    }
    
    public function getShortDesc() {
        return $this->table->short_desc;
    }
    
    public function getProperties($public = true) {
        return $this->table->getProperties($public);
    }
    
    public function isPublished() {
        return (!$this->table->published) ? false : true;
    }
    
    /**
     * Load and return project type.
     * 
     * @return CrowdFundingType
     */
    public function getType() {
    
        if(is_null($this->type) AND !empty($this->table->type_id)) {
            
            jimport("crowdfunding.type");
            $this->type = new CrowdFundingType($this->table->type_id);
            $this->type->setTable(new CrowdFundingTableType(JFactory::getDbo()));
            $this->type->load($this->table->type_id);
            
            if(!$this->type->getId()) { 
                $this->type = null;
            }
        }
    
        return $this->type;
    }
}
