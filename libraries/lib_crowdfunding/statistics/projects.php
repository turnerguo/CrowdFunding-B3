<?php
/**
 * @package      CrowdFunding
 * @subpackage   Statistics
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingConstants", CROWDFUNDING_PATH_LIBRARY."/contants.php");

/**
 * This is a base class for projects statistics.
 *
 * @package      CrowdFunding
 * @subpackage   Statistics
 */
abstract class CrowdFundingStatisticsProjects implements Iterator, Countable, ArrayAccess
{
    protected $data              = array();
    protected $allowedDirections = array("ASC", "DESC");

    protected $position = 0;

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
     * $statistics   = new CrowdFundingStatisticsProjects(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db  Database Driver
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
                "a.id, a.title, a.short_desc, a.image, a.image_small, a.image_square, a.hits, " .
                "a.goal, a.funded, a.created, a.funding_start, a.funding_end, a.funding_days, " .
                $query->concatenate(array("a.id", "a.alias"), ":") . " AS slug, " .
                $query->concatenate(array("b.id", "b.alias"), ":") . " AS catslug"
            )
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->leftJoin($this->db->quoteName("#__categories", "b") . " ON a.catid = b.id");

        return $query;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return (!isset($this->data[$this->position])) ? null : $this->data[$this->position];
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
        return isset($this->data[$this->position]);
    }

    public function count()
    {
        return (int)count($this->data);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function toArray()
    {
        return (array)$this->data;
    }

    /**
     * Prepare some main filters.
     *
     * @param JDatabaseQuery $query
     * @param array $options
     */
    protected function prepareFilters(&$query, $options)
    {
        // Filter by state.
        if (isset($options["state"])) {
            $published = (int)$options["state"];
            if (!$published) {
                $query->where("a.published = 0");
            } else {
                $query->where("a.published = 1");
            }
        }

        // Filter by approval state.
        if (isset($options["approved"])) {
            $approved = (int)$options["approved"];
            if (!$approved) {
                $query->where("a.approved = 0");
            } else {
                $query->where("a.approved = 1");
            }
        }
    }

    /**
     * Prepare result ordering.
     *
     * @param JDatabaseQuery $query
     * @param array $options
     */
    protected function prepareOrder(&$query, $options)
    {
        // Filter by state.
        if (isset($options["order"])) {

            // Prepare direction of ordering.
            $direction = (!isset($options["order_dir"])) ? "DESC" : $options["order_dir"];
            if (!in_array($direction, $this->allowedDirections)) {
                $direction = "DESC";
            }

            switch($options["order"]) {

                case CrowdFundingConstants::ORDER_BY_LOCATION_NAME: // Order by location name.
                    $query->order("l.name " .$direction);
                    break;

                case CrowdFundingConstants::ORDER_BY_NUMBER_OF_PROJECTS: // Order by location name.
                    $query->order("project_number " .$direction);
                    break;

                default: // Order by project title.
                    $query->order("a.title " .$direction);
                    break;

            }

        }
    }
}
