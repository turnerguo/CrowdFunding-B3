<?php
/**
 * @package      CrowdFunding
 * @subpackage   Categories
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage categories.
 *
 * @package      CrowdFunding
 * @subpackage   Categories
 */
class CrowdFundingCategories extends JCategories
{
    /**
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    public function __construct($options = array())
    {
        $options['table']     = '#__crowdf_projects';
        $options['extension'] = 'com_crowdfunding';
        parent::__construct($options);
    }

    /**
     * Set database object.
     *
     * <code>
     * $categories   = new CrowdFundingCategories();
     * $categories->setDb(JFactory::getDbo());
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
     * Count and return the number of subcategories.
     *
     * <code>
     * $ids = array(1, 2, 3, 4);
     *
     * $categories   = new CrowdFundingCategories();
     * $categories->setDb(JFactory::getDbo());
     *
     * $number = $categories->getChildNumber($ids);
     * </code>
     *
     * @param array $ids
     * @param array $options
     *
     * @return array
     */
    public function getChildNumber($ids, $options = array())
    {
        JArrayHelper::toInteger($ids);

        if (!$ids) {
            return array();
        }

        $query = $this->db->getQuery(true);

        $query
            ->select("a.parent_id, COUNT(*) as number")
            ->from($this->db->quoteName("#__categories", "a"))
            ->group("a.parent_id")
            ->where("a.parent_id IN (". implode(",", $ids) .")");

        // Filter by state.
        $state = JArrayHelper::getValue($options, "state");
        if (!is_null($state)) {
            $query->where("a.published = ". (int)$state);
        } else {
            $query->where("a.published IN (0,1)");
        }

        $this->db->setQuery($query);

        $results = $this->db->loadAssocList("parent_id");

        if (!$results) {
            $results = array();
        }

        return $results;
    }

    /**
     * Count and return the number of projects in categories.
     *
     * <code>
     * $ids = array(1, 2, 3, 4);
     *
     * $categories   = new CrowdFundingCategories();
     * $categories->setDb(JFactory::getDbo());
     *
     * $number = $categories->getProjectsNumber($ids);
     * </code>
     *
     * @param array $ids
     * @param array $options
     *
     * @return array
     */
    public function getProjectsNumber($ids, $options = array())
    {
        JArrayHelper::toInteger($ids);

        if (!$ids) {
            return array();
        }

        $query = $this->db->getQuery(true);

        $query
            ->select("a.catid, COUNT(*) as number")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->group("a.catid")
            ->where("a.catid IN (". implode(",", $ids) .")");

        // Filter by state.
        $state = JArrayHelper::getValue($options, "state");
        if (!is_null($state)) {
            $query->where("a.published = ". (int)$state);
        } else {
            $query->where("a.published IN (0,1)");
        }

        $this->db->setQuery($query);

        $results = $this->db->loadAssocList("catid");

        if (!$results) {
            $results = array();
        }

        return $results;
    }
}
