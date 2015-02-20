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
class CrowdFundingStatisticsProjectsMostFunded extends CrowdFundingStatisticsProjects
{
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
}
