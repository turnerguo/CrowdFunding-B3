<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
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
    static $countries  = null;
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
    		JText::_('COM_CROWDFUNDING_COUNTRIES'),
    		'index.php?option='.self::$extension.'&view=countries',
    		$vName == 'countries'
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
    		JText::_('COM_CROWDFUNDING_TYPES'),
    		'index.php?option='.self::$extension.'&view=types',
    		$vName == 'types'
        );
		
		JSubMenuHelper::addEntry(
        	JText::_('COM_CROWDFUNDING_EMAILS'),
        	'index.php?option='.self::$extension.'&view=emails',
        	$vName == 'emails'
        );
		
		JSubMenuHelper::addEntry(
    		JText::_('COM_CROWDFUNDING_LOGS'),
    		'index.php?option='.self::$extension.'&view=logs',
    		$vName == 'logs'
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
    
    public static function getCountries() {
    
        if(is_null(self::$countries)) {
    
            $db     = JFactory::getDbo();
            $query  = $db->getQuery(true);
    
            $query
            ->select("a.id, a.name, a.code")
            ->from($db->quoteName("#__crowdf_countries") . " AS a");
    
            $db->setQuery($query);
            self::$countries = $db->loadObjectList();
        }
    
        return self::$countries;
    
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
    
    public static function getLogTypes() {
    
        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);
    
        $query
            ->select("type AS value, type AS text")
            ->from("#__crowdf_logs")
            ->group("type");
    
        $db->setQuery($query);
        $types = $db->loadAssocList();
    
        if(!$types) {
            $types = array();
        }
    
        return $types;
    
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
	 * @param string This is starting date
	 * @param int    This is period in days.
	 */
	public static function calcualteEndDate($fundingStart, $fundingDays) {
	    
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
    
        // Get only date and remove the time
        $date          = new DateTime($fundingStart);
        $fundingStart  = $date->format("Y-m-d");
        $date          = new DateTime($fundingEnd);
        $fundingEnd    = $date->format("Y-m-d");
        
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
    
    public static function getSocialProfile($userId, $type) {
    
        $profile = null;
    
        switch($type) {
    
            case "socialcommunity":
    
                if(!defined("SOCIALCOMMUNITY_PATH_COMPONENT_SITE")) {
                    define("SOCIALCOMMUNITY_PATH_COMPONENT_SITE", JPATH_SITE . DIRECTORY_SEPARATOR. "components" . DIRECTORY_SEPARATOR ."com_socialcommunity");
                }
    
                JLoader::register("SocialCommunityHelperRoute", SOCIALCOMMUNITY_PATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "route.php");
    
                jimport("itprism.integrate.profile.socialcommunity");
    
                $profile = ITPrismIntegrateProfileSocialCommunity::getInstance($userId);
    
                // Set path to pictures
                $params  = JComponentHelper::getParams("com_socialcommunity");
                $path    = $params->get("images_directory", "images/profiles")."/";
    
                $profile->setPath($path);
    
                break;
    
            case "gravatar":
    
                jimport("itprism.integrate.profile.gravatar");
                $profile = ITPrismIntegrateProfileGravatar::getInstance($userId);
    
                break;
    
            case "kunena":
    
                jimport("itprism.integrate.profile.kunena");
                $profile = ITPrismIntegrateProfileKunena::getInstance($userId);
    
                break;
    
            case "jomsocial":
    
                jimport("itprism.integrate.profile.jomsocial");
                $profile = ITPrismIntegrateProfileJomSocial::getInstance($userId);
    
                break;
                
            case "easysocial":
            
                jimport("itprism.integrate.profile.easysocial");
                $profile = ITPrismIntegrateProfileEasySocial::getInstance($userId);
            
                break;
    
            default:
    
                break;
        }
    
        return $profile;
    }
    
    /**
     * This method returns intention
     * basd on user ID or anonymous hash user ID.
     *
     * @param $userId       Registered user ID
     * @param $aUserId      Anonymous user hash ID
     * @param $projectId    Project ID
     *
     * @return CrowdFundingIntention
     */
    public static function getIntention($userId, $aUserId, $projectId) {
    
        // Prepare keys for anonymous user.
        if(!empty($aUserId)) {
    
            $intentionKeys = array(
                    "auser_id"   => $aUserId,
                    "project_id" => $projectId
            );
    
        } else {// Prepare keys for registered user.
    
            $intentionKeys = array(
                    "user_id"    => $userId,
                    "project_id" => $projectId
            );
    
        }
    
        jimport("crowdfunding.intention");
        $intention = new CrowdFundingIntention($intentionKeys);
    
        return $intention;
    }
    
    /**
     * Generate a path to the folder, where the images are stored.
     *
     * @param number User Id.
     * @param string A base path to the folder. It can be JPATH_BASE, JPATH_ROOT, JPATH_SITE,... Default is JPATH_ROOT.
     *
     * @return string
     */
    public static function getImagesFolder($userId = 0, $path = JPATH_ROOT) {
    
        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params JRegistry **/
    
        jimport('joomla.filesystem.path');
        $folder = JPath::clean($path."/".$params->get("images_directory", "images/crowdfunding"));
    
        if(!empty($userId)) {
            $folder .= "/user".(int)$userId;
        }
    
        return $folder;
    }
    
    /**
     * Generate a URI path to the folder, where the images are stored.
     *
     * @param number User Id.
     *
     * @return string
     */
    public static function getImagesFolderUri($userId = 0) {
    
        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params JRegistry **/
    
        $uriImages = $params->get("images_directory", "images/crowdfunding");
    
        if(!empty($userId)) {
            $uriImages .= "/user".(int)$userId;
        }
    
        return $uriImages;
    }
    
    /**
     * Generate a URI string by a given list of parameters.
     *
     * @param array $params
     * @return string
     */
    public static function generateUrlParams($params) {
    
        $result = "";
        foreach($params as $key => $param) {
            $result .= "&".$key."=".$param;
        }
    
        return $result;
    }
    
    public static function getLogFiles() {
    
        jimport("joomla.filesystem.file");
        jimport("joomla.filesystem.path");
        jimport("joomla.filesystem.folder");
    
        // Read files in folder /logs
        $config    = JFactory::getConfig();
        $logFolder = $config->get("log_path");
    
        $files     = JFolder::files($logFolder);
        if(!is_array($files)) {
            $files = array();
        }
    
        foreach($files as $key => $file) {
            if(strcmp("index.html", $file) == 0) {
                unset($files[$key]);
            } else {
                $files[$key] = JPath::clean($logFolder.DIRECTORY_SEPARATOR.$files[$key]);
            }
        }
    
        // Check for file "error_log" in the main folder
        $errorLogFile = JPATH_ROOT.DIRECTORY_SEPARATOR."error_log";
        if(JFile::exists($errorLogFile)) {
            $files[] = JPath::clean($errorLogFile);
        }
    
        // Check for file "error_log" in admin folder
        $errorLogFile = JPATH_BASE.DIRECTORY_SEPARATOR."error_log";
        if(JFile::exists($errorLogFile)) {
            $files[] = JPath::clean($errorLogFile);
        }
    
        sort($files);
    
        return $files;
    }
    
    /**
     * Prepare date format.
     *
     * @param string $calendar
     * @return string
     */
    public static function getDateFormat($calendar = false) {
    
        $params     = JComponentHelper::getParams("com_crowdfunding");
        $dateFormat = $params->get("project_date_format", "%Y-%m-%d");
    
        if(!$calendar) {
            $dateFormat = str_replace("%", "", $dateFormat);
        }
    
        return $dateFormat;
    
    }
    
    /**
     * Create a project type object.
     *
     * @param integer $typeId
     * @return NULL|CrowdFundingType
     */
    public static function getProjectType($typeId) {
    
        if(!$typeId) {
            return null;
        }
    
        jimport("crowdfunding.type");
         
        $type   = new CrowdFundingType();
        $type->setTable( new CrowdFundingTableType(JFactory::getDbo()) );
        $type->load($typeId);
    
        if(!$type->getId()) {
            return null;
        }
    
        return $type;
    }
    
}