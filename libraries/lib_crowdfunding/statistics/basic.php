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
 * This class loads statistics about transactions.
 *
 * @package      CrowdFunding
 * @subpackage   Statistics
 */
class CrowdFundingStatisticsBasic
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
     * $statistics   = new CrowdFundingStatisticsBasic(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     */
    public function __construct(JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Get the number of all projects.
     *
     * <code>
     * $statistics   = new CrowdFundingStatisticsBasic(JFactory::getDbo());
     * $total = $statistics->getTotalProjects();
     * </code>
     */
    public function getTotalProjects()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_projects", "a"));

        $this->db->setQuery($query);
        $result = $this->db->loadResult();

        if (!$result) {
            $result = 0;
        }

        return $result;
    }

    /**
     * Get the number of all transactions.
     *
     * <code>
     * $statistics   = new CrowdFundingStatisticsBasic(JFactory::getDbo());
     * $total = $statistics->getTotalTransactions();
     * </code>
     */
    public function getTotalTransactions()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"));

        $this->db->setQuery($query);
        $result = $this->db->loadResult();

        if (!$result) {
            $result = 0;
        }

        return $result;
    }

    /**
     * Get total amount of all transactions.
     *
     * <code>
     * $statistics   = new CrowdFundingStatisticsBasic(JFactory::getDbo());
     * $total = $statistics->getTotalAmount();
     * </code>
     */
    public function getTotalAmount()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("SUM(a.txn_amount)")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"));

        $this->db->setQuery($query);
        $result = $this->db->loadResult();

        if (!$result) {
            $result = 0;
        }

        return $result;
    }
}
