<?php
/**
 * @package      CrowdFunding
 * @subpackage   Countries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a country.
 *
 * @package      CrowdFunding
 * @subpackage   Countries
 */
class CrowdFundingCountry
{
    protected $id;
    protected $name;
    protected $code;
    protected $code4;
    protected $latitude;
    protected $longitude;
    protected $currency;
    protected $timezone;

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
     * $country   = new CrowdFundingCountry(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Set database object.
     *
     * <code>
     * $country   = new CrowdFundingCountry();
     * $country->setDb(JFactory::getDbo());
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
     * Load country data from database.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new CrowdFundingCountry(JFactory::getDbo());
     * $country->load($countryId);
     * </code>
     *
     * @param int $id
     */
    public function load($id)
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.code, a.code4, a.latitude, a.longitude, a.currency, a.code")
            ->from($this->db->quoteName("#__crowdf_countries", "a"))
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
     *  "name"  => "United Kingdom",
     *  "code4" => "gb_GB"
     * );
     *
     * // Ignore the data for index key "id".
     * $ignored = array("id");
     *
     * $country   = new CrowdFundingCountry(JFactory::getDbo());
     * $country->bind($data, $ignored);
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
     * Return country ID.
     *
     * <code>
     * $countryId  = 1;
     *
     * $country    = new CrowdFundingCountry(JFactory::getDbo());
     * $country->load($typeId);
     *
     * if (!$country->getId()) {
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
     * Return 2 symbols country code (en).
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new CrowdFundingCountry(JFactory::getDbo());
     * $country->load($countryId);
     *
     * $countryCode = $country->getCode();
     * </code>
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Return 4 symbols country code (en_GB).
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new CrowdFundingCountry(JFactory::getDbo());
     * $country->load($countryId);
     *
     * $countryCode = $country->getCode4();
     * </code>
     *
     * @return string
     */
    public function getCode4()
    {
        return $this->code4;
    }

    /**
     * Return country name.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new CrowdFundingCountry(JFactory::getDbo());
     * $country->load($countryId);
     *
     * $name = $country->getName();
     * </code>
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return country latitude.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new CrowdFundingCountry(JFactory::getDbo());
     * $country->load($countryId);
     *
     * $latitude = $country->getLatitude();
     * </code>
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Return country longitude.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new CrowdFundingCountry(JFactory::getDbo());
     * $country->load($countryId);
     *
     * $longitude = $country->getLongitude();
     * </code>
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Return country currency code (GBP).
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new CrowdFundingCountry(JFactory::getDbo());
     * $country->load($countryId);
     *
     * $currency = $country->getCurrency();
     * </code>
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Return country timezone.
     *
     * <code>
     * $countryId = 1;
     *
     * $country   = new CrowdFundingCountry(JFactory::getDbo());
     * $country->load($countryId);
     *
     * $timezone = $country->getTimezone();
     * </code>
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }
}
