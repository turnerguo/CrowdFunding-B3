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
 * This class loads statistics about transactions.
 */
class CrowdFundingStatisticsBasic {
    
    /**
     * Database driver
     *
     * @var JDatabaseMySQLi
     */
    protected $db;
    
    /**
     * Initialize the object.
     *
     * @param JDatabase   Database Driver
     */
    public function __construct(JDatabase $db) {
        $this->db = $db;
    }
    
    public function getTotalProjects() {
        
        $query = $this->db->getQuery(true);
        
        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_projects", "a"));
        
        $this->db->setQuery($query);
        $result = $this->db->loadResult();
        
        if(!$result) {
            $result = 0;
        }
        
        return $result;
        
    }
    
    public function getTotalTransactions() {
    
        $query = $this->db->getQuery(true);
    
        $query
        ->select("COUNT(*)")
        ->from($this->db->quoteName("#__crowdf_transactions", "a"));
    
        $this->db->setQuery($query);
        $result = $this->db->loadResult();
    
        if(!$result) {
            $result = 0;
        }
    
        return $result;
    
    }
    
    public function getTotalAmount() {
    
        $query = $this->db->getQuery(true);
    
        $query
            ->select("SUM(a.txn_amount)")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"));
    
        $this->db->setQuery($query);
        $result = $this->db->loadResult();
    
        if(!$result) {
            $result = 0;
        }
    
        return $result;
    
    }
}
