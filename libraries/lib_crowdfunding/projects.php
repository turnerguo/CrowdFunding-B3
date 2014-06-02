<?php
/**
 * @package      CrowdFunding
 * @subpackage   Projects
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage projects.
 *
 * @package      CrowdFunding
 * @subpackage   Projects
 */
class CrowdFundingProjects implements Iterator, Countable, ArrayAccess
{
    protected $items = array();

    /**
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    protected $position = 0;

    /**
     * Initialize the object.
     *
     * <code>
     * $projects    = new CrowdFundingProjects(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db Database object.
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Set a database object.
     *
     * <code>
     * $projects    = new CrowdFundingProjects();
     * $projects->setDb(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     *
     * @return self
     */
    public function setDb(JDatabaseDriver $db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Load projects from database.
     *
     * <code>
     * $ids = array(1,2,3);
     * $options = array(
     *  "published" => CrowdFundingConstants::PUBLISHED,
     *  "approved" => CrowdFundingConstants::APPROVED
     * );
     *
     * $projects    = new CrowdFundingProjects();
     * $projects->setDb(JFactory::getDbo());
     * $projects->load($ids, $options);
     *
     * foreach ($projects as $project) {
     *      echo $project->title;
     *      echo $project->funding_start;
     * }
     *
     * </code>
     *
     * @param array $ids
     * @param array $options
     *
     * @throws UnexpectedValueException
     */
    public function load($ids = array(), $options = array())
    {
        // Set the newest ids.
        if (!is_array($ids)) {
            throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_PROJECTS_IDS_ARRAY"));
        }

        JArrayHelper::toInteger($ids);
        if (!$ids) {
            return;
        }

        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.title, a.alias")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id IN ( " . implode(",", $ids) . " )");

        // Filter by state published.
        $published = JArrayHelper::getValue($options, "published", 0, "int");
        if (!empty($published)) {
            $query->where("a.published = " . (int)$published);
        }

        // Filter by state approved.
        $approved = JArrayHelper::getValue($options, "approved", 0, "int");
        if (!empty($approved)) {
            $query->where("a.approved = " . (int)$approved);
        }

        $this->db->setQuery($query);
        $results = $this->db->loadObjectList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return (!isset($this->items[$this->position])) ? null : $this->items[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->items[$this->position]);
    }

    public function count()
    {
        return (int)count($this->items);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * Count and return rewards number of the projects.
     *
     * <code>
     * $projectsIds = array(1,2,3);
     *
     * $projects    = new CrowdFundingProjects(JFactory::getDbo());
     * $projects->load($projectsIds);
     * $rewardsNumber = $projects->getRewardsNumber();
     * </code>
     *
     * @param array $ids Projects IDs
     *
     * @return array
     */
    public function getRewardsNumber($ids = array())
    {
        // If it is missing IDs as parameter, get the IDs of the current items.
        if (!$ids and !empty($this->items)) {

            $ids = array();
            foreach ($this->items as $item) {
                $ids[] = $item->id;
            }

        }

        // If there are no IDs, return empty array.
        if (!$ids) {
            return array();
        }

        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("a.project_id, COUNT(*) as number")
            ->from($this->db->quoteName("#__crowdf_rewards", "a"))
            ->where("a.project_id IN (" . implode(",", $ids) . ")")
            ->group("a.project_id");

        $this->db->setQuery($query);

        $results = $this->db->loadObjectList("project_id");

        if (!$results) {
            $results = array();
        }

        return $results;
    }

    /**
     * Count and return transactions number.
     *
     * <code>
     * $projectsIds = array(1,2,3);
     *
     * $projects           = new CrowdFundingProjects(JFactory::getDbo());
     * $transactionsNumber = $projects->getTransactionsNumber($projectsIds);
     * </code>
     *
     * @param array $ids Projects IDs
     *
     * @return array
     */
    public function getTransactionsNumber($ids = array())
    {
        // If it is missing IDs as parameter, get the IDs of the current items.
        if (!$ids and !empty($this->items)) {

            $ids = array();
            foreach ($this->items as $item) {
                $ids[] = $item->id;
            }

        }

        // If there are no IDs, return empty array.
        if (!$ids) {
            return array();
        }

        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("a.project_id, COUNT(*) as number")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.project_id IN (" . implode(",", $ids) . ")")
            ->group("a.project_id");

        $this->db->setQuery($query);

        $results = $this->db->loadObjectList("project_id");

        if (!$results) {
            $results = array();
        }

        return $results;
    }
}
