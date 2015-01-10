<?php
/**
 * @package      CrowdFunding
 * @subpackage   Statistics
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport("crowdfunding.statistics.project");

/**
 * This class loads statistics about users.
 *
 * @package      CrowdFunding
 * @subpackage   Statistics
 */
class CrowdFundingStatisticsUsers
{
    /**
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    /**
     * Initialize the object.
     *
     * <code>
     * $ids = array(1,2,3);
     *
     * $statistics   = new CrowdFundingStatisticsUsers(JFactory::getDbo(), $ids);
     * </code>
     *
     * @param JDatabaseDriver $db  Database Driver
     * @param array $ids Users IDs
     */
    public function __construct(JDatabaseDriver $db, $ids)
    {
        $this->db  = $db;
        $this->ids = $ids;
    }

    /**
     * Count and return projects number of users.
     *
     * <code>
     * $usersIds = array(1,2,3);
     *
     * $statistics     = new CrowdFundingStatisticsUsers(JFactory::getDbo(), $usersIds);
     * $projectsNumber = $statistics->getProjectsNumber();
     * </code>
     *
     * @return array
     */
    public function getProjectsNumber()
    {
        // If there are no IDs, return empty array.
        if (!$this->ids) {
            return array();
        }

        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("a.user_id, COUNT(*) as number")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.user_id IN (" . implode(",", $this->ids) . ")")
            ->group("a.user_id");

        $this->db->setQuery($query);

        $results = $this->db->loadObjectList("user_id");

        if (!$results) {
            $results = array();
        }

        return $results;
    }

    /**
     * Count and return transactions number.
     *
     * <code>
     * $usersIds = array(1,2,3);
     *
     * $statistics         = new CrowdFundingStatisticsUsers(JFactory::getDbo(), $usersIds);
     * $transactionsNumber = $statistics->getTransactionsNumber();
     * </code>
     *
     * @return array
     */
    public function getTransactionsNumber()
    {
        // If there are no IDs, return empty array.
        if (!$this->ids) {
            return array();
        }

        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("a.investor_id, COUNT(*) as number")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.investor_id IN (" . implode(",", $this->ids) . ")")
            ->group("a.investor_id");

        $this->db->setQuery($query);

        $results = $this->db->loadObjectList("investor_id");

        if (!$results) {
            $results = array();
        }

        return $results;
    }

    /**
     * Count and return transactions number.
     *
     * <code>
     * $usersIds = array(1,2,3);
     *
     * $statistics         = new CrowdFundingStatisticsUsers(JFactory::getDbo(), $usersIds);
     * $transactionsNumber = $statistics->getAmounts();
     * </code>
     *
     * @return array
     */
    public function getAmounts()
    {
        // If there are no IDs, return empty array.
        if (!$this->ids) {
            return array();
        }

        $statistics = array(
            "invested" => array(),
            "received" => array()
        );

        // Count invested amount and transactions.
        $query = $this->db->getQuery(true);
        $query
            ->select("a.investor_id, COUNT(*) AS number, SUM(a.txn_amount) AS amount")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.investor_id IN (" . implode(",", $this->ids) . ")")
            ->group("a.investor_id");

        $this->db->setQuery($query);

        $results = $this->db->loadObjectList("investor_id");

        if (!$results) {
            $results = array();
        }

        $statistics["invested"] = $results;

        // Count received amount and transactions.
        $query = $this->db->getQuery(true);
        $query
            ->select("a.receiver_id, COUNT(*) AS number, SUM(a.txn_amount) AS amount")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.receiver_id IN (" . implode(",", $this->ids) . ")")
            ->group("a.receiver_id");

        $this->db->setQuery($query);

        $results = $this->db->loadObjectList("receiver_id");

        if (!$results) {
            $results = array();
        }

        $statistics["received"] = $results;

        return $statistics;
    }
}
