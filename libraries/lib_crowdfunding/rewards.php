<?php
/**
 * @package      CrowdFunding
 * @subpackage   Rewards
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage rewards.
 *
 * @package      CrowdFunding
 * @subpackage   Rewards
 */
class CrowdFundingRewards implements Iterator, Countable, ArrayAccess
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
     * $rewards   = new CrowdFundingRewards(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Create and initialize an object.
     *
     * <code>
     * $projectId = 1;
     *
     * $options = array(
     *     "state" = CrowdFundingConstants::PUBLISHED
     * );
     *
     * $rewards   = CrowdFundingRewards::getInstance(JFactory::getDbo(), $projectId, $options);
     * </code>
     *
     * @param JDatabaseDriver $db
     * @param                 $id
     * @param array           $options
     *
     * @return null|CrowdFundingRewards
     */
    public static function getInstance(JDatabaseDriver $db, $id, $options = array())
    {
        if (!isset(self::$instances[$id])) {
            $item                 = new CrowdFundingRewards($db);
            $item->load($id, $options);
            self::$instances[$id] = $item;
        }

        return self::$instances[$id];
    }

    /**
     * Set the database object.
     *
     * <code>
     * $rewards    = new CrowdFundingRewards();
     * $rewards->setDb(JFactory::getDbo());
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
     * Load rewards data from database, by project ID.
     *
     * <code>
     * $projectId = 1;
     *
     * $options = array(
     *  "state" => CrowdFundingConstants::PUBLISHED
     * );
     *
     * $rewards   = new CrowdFundingRewards(JFactory::getDbo());
     * $rewards->load($projectId, $options);
     *
     * foreach($rewards as $reward) {
     *   echo $reward->title;
     *   echo $reward->amount;
     * }
     * </code>
     *
     * @param int $id Project ID
     * @param array $options
     */
    public function load($id, $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.title, a.description, a.amount, a.number, a.distributed, " .
                "a.image, a.image_thumb, a.image_square"
            )
            ->from($this->db->quoteName("#__crowdf_rewards", "a"))
            ->where("a.project_id = " . (int)$id);

        // Get state
        $state = JArrayHelper::getValue($options, "state", 0, "int");
        if (!empty($state)) {
            $query->where("a.published = " . (int)$state);
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
