<?php
/**
 * @package      ITPrism Components
 * @subpackage   CrowdFunding
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die;

class CrowdFundingTableReward extends JTable {
    
    protected $available = 0;
    
	public function __construct( $db ) {
        parent::__construct( '#__crowdf_rewards', 'id', $db );
    }
    
    public function load($keys = null, $reset = true) {
        parent::load($keys, $reset);
        
        // Calculate available
        $this->available = $this->calcualteAvailable();
                
    }
    
    /**
     * Check for limited reward. 
     * If there is a number of rewards, it is limited.
     */
    public function isLimited() {
	    return ( !empty($this->number) ) ? true : false;
	}
	
	/**
	 * 
	 * Calculate the number of rewards
	 * that are available for distribution
	 */
	protected function calcualteAvailable() {
        
        if($this->isLimited()) {
            return $this->number - $this->distributed; 
        }
        return 0;
    } 
    
	/**
	 * Return available number of items
     * @return integer $available
     */
    public function getAvailable() {
        return $this->available;
    }
}