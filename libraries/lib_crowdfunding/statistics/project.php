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
 * This is a base class for project statistics.
 *
 * @package      CrowdFunding
 * @subpackage   Statistics
 */
class CrowdFundingStatisticsProject
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
     * $projectId    = 1;
     *
     * $statistics   = new CrowdFundingStatisticsProject(JFactory::getDbo(), $projectId);
     * </code>
     *
     * @param JDatabaseDriver $db Database Driver
     * @param int             $id Project ID
     */
    public function __construct(JDatabaseDriver $db, $id)
    {
        $this->db = $db;
        $this->id = (int)$id;
    }

    /**
     * Return the number of transactions.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdFundingStatisticsProject(JFactory::getDbo(), $projectId);
     * $numberOfTransactions = $statistics->getTransactionsNumber();
     * </code>
     *
     * @return int
     */
    public function getTransactionsNumber()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.project_id = " . (int)$this->id);

        $this->db->setQuery($query);

        $result = $this->db->loadResult();

        if (!$result) {
            $result = 0;
        }

        return $result;
    }

    /**
     * Calculate a project amount for full period of the campaign.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdFundingStatisticsProject(JFactory::getDbo(), $projectId);
     * $amount = $statistics->getFullPeriodAmounts();
     * </code>
     *
     * @return int
     */
    public function getFullPeriodAmounts()
    {
        $query = $this->db->getQuery(true);
        $query
            ->select("a.funding_start, a.funding_end")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id = " . (int)$this->id);

        $this->db->setQuery($query);
        $result = $this->db->loadObject();

        // Validate dates
        jimport("itprism.validator.date");
        $fundingStartDate = new ITPrismValidatorDate($result->funding_start);
        $fundingEndDate   = new ITPrismValidatorDate($result->funding_end);
        if (!$fundingStartDate->isValid() or !$fundingEndDate->isValid()) {
            return array();
        }

        $dataset = array();

        jimport("itprism.date");
        $date  = new ITPrismDate();
        $date1 = new ITPrismDate($result->funding_start);
        $date2 = new ITPrismDate($result->funding_end);

        $period = $date->getDaysPeriod($date1, $date2);

        $query = $this->db->getQuery(true);
        $query
            ->select("a.txn_date as date, SUM(a.txn_amount) as amount")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.project_id = " . (int)$this->id)
            ->group("DATE(a.txn_date)");

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!$results) {
            $results = array();
        }

        // Prepare data
        $data = array();
        foreach ($results as $result) {
            $date         = new JDate($result["date"]);
            $index        = $date->format("d.m");
            $data[$index] = $result;
        }

        /** @var $day JDate */
        foreach ($period as $day) {
            $dayMonth = $day->format("d.m");
            if (isset($data[$dayMonth])) {
                $amount = $data[$dayMonth]["amount"];
            } else {
                $amount = 0;
            }

            $dataset[] = array("date" => $dayMonth, "amount" => $amount);
        }

        return $dataset;
    }

    /**
     * Calculate three types of project amount - goal, funded amount and remaining amount.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdFundingStatisticsProject(JFactory::getDbo(), $projectId);
     * $data = $statistics->getFundedAmount();
     * </code>
     *
     * @return array
     *
     * # Example result:
     * array(
     *    "goal" = array("label" => "Goal", "amount" => 1000),
     *    "funded" = array("label" => "Funded", "amount" => 100),
     *    "remaining" = array("label" => "Remaining", "amount" => 900)
     * )
     */
    public function getFundedAmount()
    {
        $data = array();

        $query = $this->db->getQuery(true);
        $query
            ->select("a.funded, a.goal")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id = " . (int)$this->id);

        $this->db->setQuery($query);
        $result = $this->db->loadObject();
        /** @var $result object */

        if (empty($result->funded) or empty($result->goal)) {
            return $data;
        }

        $data["goal"] = array(
            "label"  => JText::_("LIB_CROWDFUNDING_GOAL"),
            "amount" => (float)$result->goal
        );

        $data["funded"] = array(
            "label"  => JText::_("LIB_CROWDFUNDING_FUNDED"),
            "amount" => (float)$result->funded
        );

        $remaining = (float)($result->goal - $result->funded);
        if ($remaining < 0) {
            $remaining = 0;
        }

        $data["remaining"] = array(
            "label"  => JText::_("LIB_CROWDFUNDING_REMAINING"),
            "amount" => $remaining
        );

        return $data;
    }

    /**
     * Return the number of comments.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdFundingStatisticsProject(JFactory::getDbo(), $projectId);
     * $numberOfComments = $statistics->getCommentsNumber();
     * </code>
     *
     * @return int
     */
    public function getCommentsNumber()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_comments", "a"))
            ->where("a.project_id = " . (int)$this->id);

        $this->db->setQuery($query);

        $result = $this->db->loadResult();

        if (!$result) {
            $result = 0;
        }

        return $result;
    }

    /**
     * Return the number of updates.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdFundingStatisticsProject(JFactory::getDbo(), $projectId);
     * $numberOfUpdates = $statistics->getUpdatesNumber();
     * </code>
     *
     * @return int
     */
    public function getUpdatesNumber()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_updates", "a"))
            ->where("a.project_id = " . (int)$this->id);

        $this->db->setQuery($query);

        $result = $this->db->loadResult();

        if (!$result) {
            $result = 0;
        }

        return $result;
    }
}
