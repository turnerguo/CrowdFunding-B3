<?php
/**
 * @package      CrowdFunding
 * @subpackage   Types
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport("crowdfunding.type");

/**
 * This class provides functionality for managing types.
 *
 * @package      CrowdFunding
 * @subpackage   Types
 */
class CrowdFundingTypes implements Iterator, Countable, ArrayAccess
{
    protected $types = array();

    /**
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    protected $position = 0;

    protected static $instance;

    /**
     * Initialize the object.
     *
     * <code>
     * $types    = new CrowdFundingTypes(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db Database object.
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Initialize and create an object.
     *
     * <code>
     * $options = array(
     *  "order_column" => "title", // id or title
     *  "order_direction" => "DESC",
     * );
     *
     * $types    = CrowdFundingTypes::getInstance(JFactory::getDbo(), $options);
     * </code>
     *
     * @param JDatabaseDriver $db
     * @param array $options
     *
     * @return self
     */
    public static function getInstance(JDatabaseDriver $db, $options = array())
    {
        if (is_null(self::$instance)) {
            self::$instance = new CrowdFundingTypes($db);
            self::$instance->load($options);
        }

        return self::$instance;
    }

    /**
     * Set a database object.
     *
     * <code>
     * $types    = new CrowdFundingTypes();
     * $types->setDb(JFactory::getDbo());
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
     * Load types data from database.
     *
     * <code>
     * $options = array(
     *  "order_column" => "title", // id or title
     *  "order_direction" => "DESC",
     * );
     *
     * $types    = new CrowdFundingTypes();
     * $types->setDb(JFactory::getDbo());
     * $types->load($options);
     *
     * foreach ($types as $type) {
     *      echo $type["title"];
     *      echo $type["description"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load($options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.title, a.description, a.params")
            ->from($this->db->quoteName("#__crowdf_types", "a"));

        // Order by column
        if (isset($options["order_column"])) {

            $orderString = $this->db->quoteName($options["order_column"]);

            // Order direction
            if (isset($options["order_direction"])) {
                $orderString .= (strcmp("DESC", $options["order_direction"])) ? " DESC" : " ASC";
            }

            $query->order($orderString);
        }

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!empty($results)) {

            foreach ($results as $result) {
                $type = new CrowdFundingType();
                $type->bind($result);
                $this->types[] = $type;
            }

        } else {
            $this->types = array();
        }

    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return (!isset($this->types[$this->position])) ? null : $this->types[$this->position];
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
        return isset($this->types[$this->position]);
    }

    public function count()
    {
        return (int)count($this->types);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->types[] = $value;
        } else {
            $this->types[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->types[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->types[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->types[$offset]) ? $this->types[$offset] : null;
    }
}
