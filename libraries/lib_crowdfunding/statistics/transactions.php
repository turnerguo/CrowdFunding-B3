<?php
/**
 * @package      CrowdFunding
 * @subpackage   Statistics
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This is a base class for transactions statistics.
 *
 * @package      CrowdFunding
 * @subpackage   Statistics
 */
abstract class CrowdFundingStatisticsTransactions
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
     * $statistics   = new CrowdFundingStatisticsTransactions(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver  $db Database Driver
     */
    public function __construct(JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    protected function getQuery()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.txn_date, a.txn_amount, a.txn_currency, a.txn_id, a.project_id, a.fee, " .
                "b.title"
            )
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->leftJoin($this->db->quoteName("#__crowdf_projects", "b") . " ON a.project_id = b.id");

        return $query;
    }
}
