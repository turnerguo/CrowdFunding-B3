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

/**
 * This class provieds functionality that manage projects.
 * 
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingProject extends CrowdFundingTableProject {
    
    protected static $instances = array();
    
    public    $funded  = 0;
    
    protected $rewards = null;
    
    public function __construct($id) {
        
        $db = JFactory::getDbo();
        parent::__construct( $db );
        
        if(!empty($id)) {
            $this->load($id);
        }
    }

    public static function getInstance($id = 0)  {
    
        if (empty(self::$instances[$id])){
            $item = new CrowdFundingProject($id);
            self::$instances[$id] = $item;
        }
    
        return self::$instances[$id];
    }
    
	/**
     * Add a new amount to current funded one.
     * Calculate funded percent.
     * 
     * @param float $amount
     */
    public function addFunds($amount) {
        $this->funded         = $this->funded + $amount;
        $this->fundedPercents = CrowdFundingHelper::calculatePercent($this->funded, $this->goal);
    }
    
    /**
     * Remove a some amount from current funded one.
     * Calculate funded percent.
     * 
     * @param float $amount
     */
    public function removeFunds($amount) {
        $this->funded         = $this->funded - $amount;
        $this->fundedPercents = CrowdFundingHelper::calculatePercent($this->funded, $this->goal);
    }
    
    public function getRewards($options = array()) {
        
        if(is_null($this->rewards)) {
            jimport("crowdfunding.rewards");
            $this->rewards = CrowdFundingRewards::getInstance($this->id, $options);
        }
        
        return $this->rewards;
    }
}
