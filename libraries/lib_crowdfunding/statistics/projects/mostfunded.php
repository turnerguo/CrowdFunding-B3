<?php
/**
 * @package      CrowdFunding\Statistics
 * @subpackage   Projects
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport("crowdfunding.statistics.projects");

/**
 * This class loads statistics about projects.
 *
 * @package      CrowdFunding\Statistics
 * @subpackage   Projects
 */
class CrowdFundingStatisticsProjectsMostFunded extends CrowdFundingStatisticsProjects implements Iterator, Countable, ArrayAccess
{
    protected $data = array();

    protected $position = 0;

    /**
     * Load data about the most funded projects.
     *
     * <code>
     * $mostFunded = new CrowdFundingStatisticsProjectsMostFunded(JFactory::getDbo());
     * $mostFunded->load();
     *
     * foreach ($mostFunded as $project) {
     *      echo $project["title"];
     * }
     * </code>
     *
     * @param int $limit Number of result that will be loaded.
     */
    public function load($limit = 5)
    {
        // Get current date
        jimport("joomla.date.date");
        $date  = new JDate();
        $today = $date->toSql();

        $query = $this->getQuery();

        $query
            ->where("( a.published = 1 AND a.approved = 1 )")
            ->where("( a.funding_start <= " . $this->db->quote($today) . " AND a.funding_end >= " . $this->db->quote($today) . " )")
            ->order("a.funded DESC");

        $this->db->setQuery($query, 0, (int)$limit);

        $this->data = $this->db->loadAssocList();

        if (!$this->data) {
            $this->data = array();
        }
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
}
