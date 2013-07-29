<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined('_JEXEC') or die;

/**
 * It is CrowdFunding helper class
 *
 */
abstract class CrowdFundingHelper {
	
    static $currency   = null;
    static $currencies = null;
    static $extension  = "com_crowdfunding";
      
    static $statistics    = array();
    
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 * @since	1.6
	 */
	public static function addSubmenu($vName = 'dashboard') {
	    
	    JSubMenuHelper::addEntry(
			JText::_('COM_CROWDFUNDING_DASHBOARD'),
			'index.php?option='.self::$extension.'&view=dashboard',
			$vName == 'dashboard'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_CROWDFUNDING_CATEGORIES'),
			'index.php?option=com_categories&extension='.self::$extension.'',
			$vName == 'categories'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_CROWDFUNDING_PROJECTS'),
			'index.php?option='.self::$extension.'&view=projects',
			$vName == 'projects'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_CROWDFUNDING_TRANSACTIONS'),
			'index.php?option='.self::$extension.'&view=transactions',
			$vName == 'transactions'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_CROWDFUNDING_LOCATIONS'),
			'index.php?option='.self::$extension.'&view=locations',
			$vName == 'locations'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_CROWDFUNDING_CURRENCIES'),
			'index.php?option='.self::$extension.'&view=currencies',
			$vName == 'currencies'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_CROWDFUNDING_UPDATES'),
			'index.php?option='.self::$extension.'&view=updates',
			$vName == 'updates'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_CROWDFUNDING_COMMENTS'),
			'index.php?option='.self::$extension.'&view=comments',
			$vName == 'comments'
		);
		
		JSubMenuHelper::addEntry(
    		JText::_('COM_CROWDFUNDING_PLUGINS'),
    		'index.php?option=com_plugins&view=plugins&filter_search='.rawurlencode("crowdfunding"),
    		$vName == 'plugins'
        );
		
	}
	
    public static function getProjectTitle($projectId) {
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query->select("title")
            ->from("#__crowdf_projects")
            ->where("id = ". (int)$projectId);
        
        $db->setQuery($query);
        return $db->loadResult();
        
    }
    
    public static function getCurrency($currencyId, $force = false) {
        
        if(is_null(self::$currency) OR $force) {
            
            $db     = JFactory::getDbo();
            $query  = $db->getQuery(true);
            
            $query
                ->select("abbr, symbol, position")
                ->from("#__crowdf_currencies")
                ->where("id = ". (int)$currencyId);
            
            $db->setQuery($query);
            self::$currency = $db->loadAssoc();
        }
        
        return self::$currency;
        
    }
    
    public static function getCurrencies($index = "id", $force = false) {
        
        if(is_null(self::$currencies) OR $force) {
            
            $db     = JFactory::getDbo();
            $query  = $db->getQuery(true);
            
            $query
                ->select("id, title, abbr, symbol, position")
                ->from("#__crowdf_currencies");
            
            $db->setQuery($query);
            self::$currencies = $db->loadAssocList($index);
        }
        
        return self::$currencies;
        
    }
    
    public static function getProject($projectId, $fields = array("id")) {
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $selectFields = array();
        foreach($fields as $field) {
            $selectFields[] = $db->quoteName($field);
        }
        
        $query
            ->select($selectFields)
            ->from("#__crowdf_projects")
            ->where($db->quoteName("id")." = ". (int)$projectId);
        
        $db->setQuery($query);
        return $db->loadObject();
        
    }
	
	public static function calculatePercent($funded, $goal) {
	    $value = ($funded/$goal) * 100;
	    return round($value, 2);
	}
	
	/**
	 * 
	 * Calculate days left
	 * @param int $fundingDays
	 * @param string $fundingStart
	 * @param string $fundingEnd
	 */
	public static function calcualteDaysLeft($fundingDays, $fundingStart, $fundingEnd) {
        
        // Calcualte days left
        $today         = new DateTime("today");
        if(!empty($fundingDays)) {
            
            // Validate starting date. 
            // If there is not starting date, set number of day.
            if(!self::isValidDate($fundingStart)) {
                return (int)$fundingDays;
            }
            
            $endingDate  = new DateTime($fundingStart);
            $endingDate->modify("+".(int)$fundingDays." days");
            
        } else {
            $endingDate    = new DateTime($fundingEnd);
        }
        
        $interval      = $today->diff($endingDate);
        
        $daysLeft      = $interval->format("%r%a");
        
        // If the 
        if($daysLeft < 0 ) {
            $daysLeft = 0;
        }
        return $daysLeft;
    } 
    
    /**
	 * Calculate end date
	 * 
	 * @param int    $fundingDays
	 * @param string $fundingStart
	 */
	public static function calcualteEndDate($fundingDays, $fundingStart) {
	    
        // Calcualte days left
        $endingDate  = new DateTime($fundingStart);
        $endingDate->modify("+".(int)$fundingDays." days");
        
        return $endingDate->format("Y-m-d");
    }

    /**
     * Validate a date
     * 
     * @param string $string
     * @return boolean
     */
    public static function isValidDate($string) {
        
        $string = JString::trim($string);
        
        try {
            $date = new DateTime($string);
        } catch (Exception $e) {
            return false;
        }
        
        $month = $date->format('m');
        $day   = $date->format('d');
        $year  = $date->format('Y');
        
        if(checkdate($month, $day, $year)) {
            return true;
        } else {
            return false;
        }
        
    }
    
    /**
     * This module collects statistical data about project - number of updates, comments, funders,...
     *
     * @param integer $projectId
     * @return array
     */
    public static function getProjectData($projectId) {
    
        $db    = JFactory::getDbo();
    
        /// Updates
        if(!isset(self::$statistics[$projectId])) {
            self::$statistics[$projectId] = array(
                    "updates"   => null,
                    "comments"  => null,
                    "funders"   => null
            );
    
        }
    
        // Count updates
        if(is_null(self::$statistics[$projectId]["updates"])) {
    
            $query = $db->getQuery(true);
            $query
            ->select("COUNT(*) AS updates")
            ->from($db->quoteName("#__crowdf_updates"))
            ->where("project_id = ". (int)$projectId);
    
            $db->setQuery($query);
    
            self::$statistics[$projectId]["updates"] = $db->loadResult();
        }
    
        // Count comments
        if(is_null(self::$statistics[$projectId]["comments"])) {
    
            $query = $db->getQuery(true);
            $query
            ->select("COUNT(*) AS comments")
            ->from($db->quoteName("#__crowdf_comments"))
            ->where("project_id = ". (int)$projectId)
            ->where("published = 1");
    
            $db->setQuery($query);
    
            self::$statistics[$projectId]["comments"] = $db->loadResult();
        }
    
        // Count funders
        if(is_null(self::$statistics[$projectId]["funders"])) {
    
            $query = $db->getQuery(true);
            $query
            ->select("COUNT(*) AS funders")
            ->from($db->quoteName("#__crowdf_transactions"))
            ->where("project_id  = ". (int)$projectId);
    
            $db->setQuery($query);
    
            self::$statistics[$projectId]["funders"] = $db->loadResult();
        }
    
        return self::$statistics[$projectId];
    }
    
    /**
     * This method validates the period between minimum and maximum days.
     *
     * @param string $fundingStart
     * @param string $fundingEnd
     * @param integer $minDays
     * @param integer $maxDays
     * @return boolean
     */
    public static function isValidPeriod($fundingStart, $fundingEnd, $minDays, $maxDays) {
    
        // Get interval between starting and ending date
        $startingDate  = new DateTime($fundingStart);
        $endingDate    = new DateTime($fundingEnd);
        $interval      = $startingDate->diff($endingDate);
    
        $days          = $interval->format("%r%a");
    
        // Validate minimum dates
        if($days < $minDays) {
            return false;
        }
    
        if(!empty($maxDays) AND $days > $maxDays) {
            return false;
        }
    
        return true;
    }
}