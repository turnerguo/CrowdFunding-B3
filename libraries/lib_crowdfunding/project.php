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
     * @param float $amount
     */
    public function addFunds($amount) {
        $this->funded         = $this->funded + $amount;
        $this->fundedPercents = CrowdFundingHelper::calculatePercent($this->funded, $this->goal);
    }
    
    public function getRewards() {
        
        if(is_null($this->rewards)) {
            jimport("crowdfunding.rewards");
            $this->rewards = CrowdFundingRewards::getInstance($this->id);
        }
        
        return $this->rewards;
    }
}
