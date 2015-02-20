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
 * This class loads statistics about projects in locations.
 *
 * @package      CrowdFunding\Statistics
 * @subpackage   Locations
 */
class CrowdFundingStatisticsProjectsLocations extends CrowdFundingStatisticsProjects
{
    /**
     * Load latest projects ordering by starting date of campaigns.
     *
     * <code>
     * $options = array(
     *     "limit"    => 20,
     *     "state"    => CrowdFundingConstants::PUBLISHED,
     *     "approved" => CrowdFundingConstants::APPROVED,
     *     "order"    => CrowdFundingConstants::ORDER_BY_NAME,
     *     "order_dir"    => "DESC",
     *     "having"   => 5
     * );
     *
     * $locations = new CrowdFundingStatisticsLocationsProjects(JFactory::getDbo());
     * $locations->load($limit);
     *
     * foreach ($locations as $location) {
     *      echo $project["location_id"];
     *      echo $project["location_name"];
     *      echo $project["project_number"];
     * }
     * </code>
     *
     * @param array $options Some options that can be used to filter the result.
     */
    public function load($options = array())
    {
        $query = $this->getQuery();

        $query->select("a.location_id, COUNT(a.id) as project_number");
        $query->select("l.name as location_name");

        $query->innerJoin($this->db->quoteName("#__crowdf_locations", "l") . " ON a.location_id = l.id");
        $query->group("a.location_id");

        $this->prepareFilters($query, $options);
        $this->prepareOrder($query, $options);

        // Filter by number of projects in the results.
        if (isset($options["having"])) {
            if (!empty($options["having"])) {
                $query->having("project_number >= " . (int)$options["having"]);
            }
        }

        // Get the limit of results.
        $limit = (isset($options["limit"])) ?: 10;

        $this->db->setQuery($query, 0, (int)$limit);

        $this->data = $this->db->loadAssocList();

        if (!$this->data) {
            $this->data = array();
        }
    }
}
