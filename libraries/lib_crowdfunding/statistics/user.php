<?php
/**
 * @package      CrowdFunding
 * @subpackage   Statistics
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This is a base class for user statistics.
 *
 * @package      CrowdFunding
 * @subpackage   Statistics
 */
class CrowdFundingStatisticsUser
{
    protected $id;

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
     * $userId    = 1;
     *
     * $statistics   = new CrowdFundingStatisticsUser(JFactory::getDbo(), $userId);
     * </code>
     *
     * @param JDatabaseDriver $db Database Driver
     * @param int             $id
     */
    public function __construct(JDatabaseDriver $db, $id)
    {
        $this->db = $db;
        $this->id = (int)$id;
    }

    /**
     * Count and return projects number of users.
     *
     * <code>
     * $usersId = 1;
     *
     * $statistics     = new CrowdFundingStatisticsUser(JFactory::getDbo(), $usersId);
     * $projectsNumber = $statistics->getProjectsNumber();
     * </code>
     *
     * @return array
     */
    public function getProjectsNumber()
    {
        // If there are no IDs, return empty array.
        if (!$this->id) {
            return array();
        }

        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("COUNT(*) as number")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.user_id = " . (int)$this->id);

        $this->db->setQuery($query, 0, 1);

        $results = $this->db->loadResult();

        if (!$results) {
            $results = array();
        }

        return $results;
    }

    /**
     * Count and return transactions number.
     *
     * <code>
     * $usersId = 1;
     *
     * $statistics         = new CrowdFundingStatisticsUsers(JFactory::getDbo(), $usersId);
     * $transactionsNumber = $statistics->getAmounts();
     * </code>
     *
     * @return array
     */
    public function getAmounts()
    {
        // If there are no IDs, return empty array.
        if (!$this->id) {
            return array();
        }

        $statistics = array(
            "invested" => array(),
            "received" => array()
        );

        // Count invested amount and transactions.
        $query = $this->db->getQuery(true);
        $query
            ->select("COUNT(*) AS number, SUM(a.txn_amount) AS amount")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.investor_id = " . (int)$this->id);

        $this->db->setQuery($query);

        $results = $this->db->loadObject();

        if (!$results) {
            $results = array();
        }

        $statistics["invested"] = $results;

        // Count received amount and transactions.
        $query = $this->db->getQuery(true);
        $query
            ->select("COUNT(*) AS number, SUM(a.txn_amount) AS amount")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.receiver_id = ". (int)$this->id);

        $this->db->setQuery($query);

        $results = $this->db->loadObject();

        if (!$results) {
            $results = array();
        }

        $statistics["received"] = $results;

        return $statistics;
    }
}
