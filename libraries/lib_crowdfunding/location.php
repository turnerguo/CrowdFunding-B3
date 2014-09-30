<?php
/**
 * @package      CrowdFunding
 * @subpackage   Locations
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a location.
 *
 * @package      CrowdFunding
 * @subpackage   Locations
 */
class CrowdFundingLocation
{
    protected $id;
    protected $name;
    protected $latitude;
    protected $longitude;
    protected $country_code;
    protected $state_code;
    protected $timezone;
    protected $published;

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
     * $location   = new CrowdFundingLocation(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Load location data from database.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new CrowdFundingLocation(JFactory::getDbo());
     * $location->load($locationId);
     * </code>
     *
     * @param int $id
     */
    public function load($id)
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.latitude, a.longitude, a.country_code, a.state_code, a.timezone, a.published")
            ->from($this->db->quoteName("#__crowdf_locations", "a"))
            ->where("a.id = " . (int)$id);

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!empty($result)) {
            $this->bind($result);
        }
    }

    /**
     * Set data to object properties.
     *
     * <code>
     * $data = (
     *  "id"    => 1,
     *  "name"  => "Plovdiv",
     *  "code4" => "GB"
     * );
     *
     * // Ignore the data for index key "id".
     * $ignored = array("code");
     *
     * $location   = new CrowdFundingLocation(JFactory::getDbo());
     * $location->bind($data, $ignored);
     * </code>
     *
     * @param array $data
     * @param array $ignored
     */
    public function bind($data, $ignored = array())
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $ignored)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Return location ID.
     *
     * <code>
     * $locationId  = 1;
     *
     * $location    = new CrowdFundingLocation(JFactory::getDbo());
     * $location->load($typeId);
     *
     * if (!$location->getId()) {
     * ....
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return a country code of the location.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new CrowdFundingLocation(JFactory::getDbo());
     * $location->load($locationId);
     *
     * $locationCode = $location->getCountryCode();
     * </code>
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * Return a state code of the location.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new CrowdFundingLocation(JFactory::getDbo());
     * $location->load($locationId);
     *
     * $locationCode = $location->getStateCode();
     * </code>
     *
     * @return string
     */
    public function getStateCode()
    {
        return $this->state_code;
    }

    /**
     * Return location name.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new CrowdFundingLocation(JFactory::getDbo());
     * $location->load($locationId);
     *
     * $name = $location->getName();
     * </code>
     *
     * @param bool $includeCountryCode Include or not the country code to the name.
     *
     * @return string
     */
    public function getName($includeCountryCode = false)
    {
        return (!$includeCountryCode) ? $this->name : $this->name . ", " . $this->country_code;
    }

    /**
     * Return location latitude.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new CrowdFundingLocation(JFactory::getDbo());
     * $location->load($locationId);
     *
     * $latitude = $location->getLatitude();
     * </code>
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Return location longitude.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new CrowdFundingLocation(JFactory::getDbo());
     * $location->load($locationId);
     *
     * $longitude = $location->getLongitude();
     * </code>
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Check if the location is published.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new CrowdFundingLocation(JFactory::getDbo());
     * $location->load($locationId);
     *
     * if (!$location->isPublished()) {
     * ....
     * }
     * </code>
     *
     * @return string
     */
    public function isPublished()
    {
        return (bool)$this->published;
    }

    /**
     * Return location timezone.
     *
     * <code>
     * $locationId = 1;
     *
     * $location   = new CrowdFundingLocation(JFactory::getDbo());
     * $location->load($locationId);
     *
     * $timezone = $location->getTimezone();
     * </code>
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }
}
