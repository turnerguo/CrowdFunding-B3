<?php
/**
 * @package      CrowdFunding\Statistics
 * @subpackage   Projects
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
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
class CrowdFundingStatisticsProjectsLatest extends CrowdFundingStatisticsProjects
{
    /**
     * Load latest projects ordering by starting date of campaigns.
     *
     * <code>
     * $limit = 10;
     *
     * $latest = new CrowdFundingStatisticsProjectsLatest(JFactory::getDbo());
     * $latest->load($limit);
     *
     * foreach ($latest as $project) {
     *      echo $project["title"];
     *      echo $project["funding_start"];
     * }
     * </code>
     *
     * @param int $limit The number of results.
     */
    public function load($limit = 5)
    {
        $query = $this->getQuery();

        $query
            ->where("a.published = 1")
            ->where("a.approved = 1")
            ->order("a.funding_start DESC");

        $this->db->setQuery($query, 0, (int)$limit);

        $this->data = $this->db->loadAssocList();

        if (!$this->data) {
            $this->data = array();
        }
    }

    /**
     * Load latest projects ordering by created date.
     *
     * <code>
     * $limit = 10;
     *
     * $latest = new CrowdFundingStatisticsProjectsLatest(JFactory::getDbo());
     * $latest->loadByCreated($limit);
     *
     * foreach ($latest as $project) {
     *      echo $project["title"];
     *      echo $project["funding_start"];
     * }
     * </code>
     *
     * @param int $limit The number of results.
     */
    public function loadByCreated($limit = 5)
    {
        $query = $this->getQuery();

        $query
            ->where("a.published = 1")
            ->where("a.approved = 1")
            ->order("a.created DESC");

        $this->db->setQuery($query, 0, (int)$limit);

        $this->data = $this->db->loadAssocList();

        if (!$this->data) {
            $this->data = array();
        }
    }
}
