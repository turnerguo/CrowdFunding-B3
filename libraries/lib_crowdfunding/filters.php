<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

/**
 * This class provieds functionality that manages filters.
 */
class CrowdFundingFilters {
    
    protected $options  = array();
    
    /**
     * Database driver.
     * 
     * @var JDatabaseMySQLi
     */
    protected $db;
    
    protected static $instance;
    
    /**
     * Initialize the object.
     * 
     * @param JDatabase Database object.
     */
    public function __construct(JDatabase $db) {
        $this->db = $db;
    }

    public static function getInstance(JDatabase $db)  {
    
        if (is_null(self::$instance)){
            self::$instance = new CrowdFundingFilters($db);
        }
    
        return self::$instance;
    }
    
    public function getPaymentStatuses() {
        
        return array(
            JHtml::_("select.option", "completed", JText::_("COM_CROWDFUNDING_COMPLETED")),
            JHtml::_("select.option", "pending", JText::_("COM_CROWDFUNDING_PENDING")),
            JHtml::_("select.option", "canceled", JText::_("COM_CROWDFUNDING_CANCELED")),
            JHtml::_("select.option", "refunded", JText::_("COM_CROWDFUNDING_REFUNDED")),
            JHtml::_("select.option", "failed", JText::_("COM_CROWDFUNDING_FAILED"))
        );
        
    }
    
    public function getRewardDistributionStatuses() {
    
        return array(
            JHtml::_("select.option", "none", JText::_("COM_CROWDFUNDING_NOT_SELECTE")),
            JHtml::_("select.option", "0", JText::_("COM_CROWDFUNDING_NOT_SENT")),
            JHtml::_("select.option", "1", JText::_("COM_CROWDFUNDING_SENT")),
        );
    
    }
    
    public function getProjectsTypes() {
    
        if(!isset($this->options["project_types"])) {

            $query = $this->db->getQuery(true);
        
            $query
                ->select("a.id AS value, a.title AS text")
                ->from($this->db->quoteName("#__crowdf_types", "a"));
        
            $this->db->setQuery($query);
            $results = $this->db->loadAssocList();
        
            if(!$results) {
                $results = array();
            }
        
            $this->options["project_types"] = $results;
            
        } else {
            $results = $this->options["project_types"];
        }
    
        return $results;
    
    }
    
    public function getPaymentServices() {
    
        if(!isset($this->options["payment_services"])) {
    
            $query = $this->db->getQuery(true);
    
            $query
                ->select("a.service_provider AS value, a.service_provider AS text")
                ->from($this->db->quoteName("#__crowdf_transactions", "a"))
                ->group("a.service_provider");
    
            $this->db->setQuery($query);
            $results = $this->db->loadAssocList();
    
            if(!$results) {
                $results = array();
            }
    
            $this->options["payment_services"] = $results;
    
        } else {
            $results = $this->options["payment_services"];
        }
    
        return $results;
    
    }
    
    
    public function getCountries($value = "id") {
    
        if(!isset($this->options["countries"])) {
    
            $query  = $this->db->getQuery(true);
    
            switch($value) {
                
                case "code":
                    $query ->select("a.code AS value, a.name AS text");
                    break;
                    
                case "code4":
                    $query ->select("a.code4 AS value, a.name AS text");
                    break;
                    
                default:
                    $query ->select("a.id AS value, a.name AS text");
                    break;
            }
            
            $query->from($this->db->quoteName("#__crowdf_countries") . " AS a");
    
            $this->db->setQuery($query);
            
            $results = $this->db->loadObjectList();
            
            $this->options["countries"] = $results;
            
        } else {
            $results = $this->options["countries"];
        }
        
        return $results;
    
    }
}
