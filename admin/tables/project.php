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
class CrowdFundingTableProject extends JTable {
    
    protected $fundedPercents = 0;
    protected $daysLeft       = 0;
    
	public function __construct( $db ) {
        parent::__construct( '#__crowdf_projects', 'id', $db );
    }
    
    public function load($keys = null, $reset = true) {
        parent::load($keys, $reset);
        
        // Calculate funded percents
        $this->fundedPercents = $this->calculatePercent();
                
        // Calcualte days left
        $this->daysLeft       = $this->calcualteDaysLeft();
        
    }
    
    protected function calculatePercent() {
        $value = ($this->funded/$this->goal) * 100;
	    return round($value, 2);
	}
	
	protected function calcualteDaysLeft() {
        
        // Calcualte days left
        $today         = new DateTime("today");
        
        // Calcualte ending date
        if(!empty($this->funding_days)) {
            
            $fundindStart = new JDate($this->funding_start);
            
            // Validate starting date. 
            // If there is not starting date, set number of day.
            if(0 > $fundindStart->toUnix()) {
                return (int)$this->funding_days;
            }
            
            $endingDate  = new DateTime($this->funding_start);
            $endingDate->modify("+".(int)$this->funding_days." days");
            
        } else {
            $endingDate  = new DateTime($this->funding_end);
        }
        
        $interval        = $today->diff($endingDate);
        $daysLeft        = $interval->format("%r%a");
        
        // If number is less than zero,
        // initialize it with 0 
        if($daysLeft < 0 ) {
            $daysLeft = 0;
        }
        return $daysLeft;
    } 
    
	/**
     * @return the $fundedPercents
     */
    public function getFundedPercents() {
        return $this->fundedPercents;
    }

	/**
     * @return the $daysLeft
     */
    public function getDaysLeft() {
        return $this->daysLeft;
    }
    
}