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
 * This is a base class for project statistics.
 */
 class CrowdFundingStatisticsProject {
    
     protected $id;
     
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
     * @param integer     Project ID
     */
    public function __construct(JDatabase $db, $id) {
        $this->db = $db;
        $this->id = intval($id);
    }

    /**
     * Count and return transactions number.
     *
     * @return array
     */
    public function getTransactionsNumber() {
    
        // Create a new query object.
        $query  = $this->db->getQuery(true);
    
        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.project_id = ".(int)$this->id);
    
        $this->db->setQuery($query);
    
        $result = $this->db->loadResult();
    
        if(!$result) {
            $result = 0;
        }
    
        return $result;
    }
    
}
