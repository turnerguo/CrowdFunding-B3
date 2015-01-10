<?php
/**
 * @package      CrowdFunding
 * @subpackage   Locations
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage locations.
 *
 * @package      CrowdFunding
 * @subpackage   Locations
 */
class CrowdFundingLocations implements Iterator, Countable, ArrayAccess
{
    protected $items = array();

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
     * $locations   = new CrowdFundingLocations(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     */
    public function __construct(JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Load locations data by ID from database.
     *
     * <code>
     * $ids = array(1,2,3,4,5);
     *
     * $locations   = new CrowdFundingLocations(JFactory::getDbo());
     * $locations->load($ids);
     *
     * foreach($locations as $location) {
     *   echo $location["id"];
     *   echo $location["name"];
     * }
     *
     * </code>
     *
     * @param array $ids
     */
    public function load($ids = array())
    {
        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.latitude, a.longitude, a.country_code, a.state_code, a.timezone, a.published")
            ->from($this->db->quoteName("#__crowdf_locations", "a"));

        if (!empty($ids)) {
            JArrayHelper::toInteger($ids);
            $query->where("a.id IN ( " . implode(",", $ids) . " )");
        }

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
    }

    /**
     * Load locations data by string from database.
     *
     * <code>
     * $string = "Plovdiv";
     * 
     * $locations   = new CrowdFundingLocations(JFactory::getDbo());
     * $locations->loadByString($string);
     *
     * foreach($locations as $location) {
     *   echo $location["id"];
     *   echo $location["name"];
     * }
     * </code>
     *
     * @param string $string
     * @param int $mode  Filter mode.
     *
     * Example:
     *
     * # Filter modes
     * 0 = "string";
     * 1 = "string%";
     * 2 = "%string";
     * 3 = "%string%";
     */
    public function loadByString($string, $mode = 1)
    {
        $query  = $this->db->getQuery(true);

        switch ($mode) {

            case 1: // Beginning
                $searchFilter = $this->db->escape($string, true) . '%';
                break;

            case 2: // End
                $searchFilter =  '%'. $this->db->escape($string, true);
                break;

            case 3: // Both
                $searchFilter =  '%' . $this->db->escape($string, true) . '%';
                break;

            default: // NONE
                $searchFilter = $this->db->escape($string, true);
                break;
        }

        $search = $this->db->quote($searchFilter);

        $caseWhen = ' CASE WHEN ';
        $caseWhen .= $query->charLength('a.state_code', '!=', '0');
        $caseWhen .= ' THEN ';
        $caseWhen .= $query->concatenate(array('a.name', 'a.state_code', 'a.country_code'), ', ');
        $caseWhen .= ' ELSE ';
        $caseWhen .= $query->concatenate(array('a.name', 'a.country_code'), ', ');
        $caseWhen .= ' END as name';

        $query
            ->select("a.id, " . $caseWhen)
            ->from($this->db->quoteName("#__crowdf_locations", "a"))
            ->where($this->db->quoteName("a.name") . " LIKE " . $search);

        $this->db->setQuery($query, 0, 8);
        $results = $this->db->loadAssocList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
    }

    /**
     * Prepare an array that will be used as options in drop down form element.
     *
     * <code>
     * $string = "Plov";
     *
     * $locations   = new CrowdFundingLocations(JFactory::getDbo());
     * $locations->loadByString($string);
     *
     * $options = $locations->toOptions();
     * </code>
     *
     * @return array
     */
    public function toOptions()
    {
        $options = array();

        foreach ($this->items as $item) {
            $options[] = array(
                "id" => $item["id"],
                "name" => $item["name"]
            );
        }

        return $options;
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
}
