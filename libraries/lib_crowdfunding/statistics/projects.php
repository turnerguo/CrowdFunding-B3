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
 * This is a base class for projects statistics.
 */
abstract class CrowdFundingStatisticsProjects {
    
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

    protected function getQuery() {
        
        $query = $this->db->getQuery(true);
        
        $query
            ->select(
                "a.id, a.title, a.short_desc, a.image, a.image_small, a.image_square, a.hits, " .
                "a.goal, a.funded, a.created, a.funding_start, a.funding_end, a.funding_days, " .
                $query->concatenate(array("a.id", "a.alias"), ":") . " AS slug, " .
                $query->concatenate(array("b.id", "b.alias"), ":") . " AS catslug"
            )
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->leftJoin($this->db->quoteName("#__categories", "b") . " ON a.catid = b.id");
        
        return $query;
        
    }
    
}
