<?php
/**
 * @package      CrowdFunding
 * @subpackage   Images
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage the additional images..
 *
 * @package      CrowdFunding
 * @subpackage   Images
 */
class CrowdFundingImages implements Iterator, Countable, ArrayAccess
{
    protected $items = array();

    /**
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    protected $position = 0;

    protected static $instances = array();

    /**
     * Initialize the object.
     *
     * <code>
     * $images    = new CrowdFundingImages(JFactory::getDbo());
     * </code>
     * 
     * @param JDatabaseDriver  $db
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Set database object.
     *
     * <code>
     * $country   = new CrowdFundingImages();
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
     * Load images from database.
     * 
     * <code>
     * $projectId = 1;
     * 
     * $options = array(
     *  "order_direction" => "DESC"
     * );
     * 
     * $images    = new CrowdFundingImages(JFactory::getDbo());
     * $images->load($projectId, $options);
     *
     * foreach($images as $image) {
     *   echo '<img src="'.$image["thumb"].'" />';
     *   echo '<img src="'.$image["image"].'" />';
     * }
     * </code>
     * 
     * @param       $id
     * @param array $options
     *
     * @return array|mixed
     */
    public function load($id, $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.image, a.thumb, a.project_id")
            ->from($this->db->quoteName("#__crowdf_images", "a"))
            ->where("a.project_id = " . (int)$id);

        if (isset($options["order_direction"])) {
            $orderDir = (strcmp("DESC", $options["order_direction"])) ? "DESC" : "ASC";
            $query->order("a.id " . $this->db->escape($orderDir));
        }

        $this->db->setQuery($query);
        $results = $this->db->loadObjectList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
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
