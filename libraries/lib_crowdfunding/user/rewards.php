<?php
/**
 * @package      CrowdFunding
 * @subpackage   Users
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage user rewards.
 *
 * @package      CrowdFunding
 * @subpackage   Users
 */
class CrowdFundingUserRewards implements Iterator, Countable, ArrayAccess
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
     * $rewards   = new CrowdFundingUserRewards(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     */
    public function __construct(JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Load data about user rewards by user ID.
     *
     * <code>
     * $userId = 1;
     *
     * $rewards   = new CrowdFundingUserRewards(JFactory::getDbo());
     * $rewards->load($userId);
     *
     * foreach($rewards as $reward) {
     *   echo $reward["reward_id"];
     *   echo $reward["reward_name"];
     * }
     *
     * </code>
     *
     * @param int $id User ID
     */
    public function load($id)
    {
        $query = $this->getQuery();

        $query
            ->where("a.receiver_id = ". (int)$id)
            ->where("a.reward_id > 0");

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
    }

    /**
     * Load data about user rewards by reward ID.
     *
     * <code>
     * $userId = 1;
     *
     * $rewards   = new CrowdFundingUserRewards(JFactory::getDbo());
     * $rewards->load($userId);
     *
     * foreach($rewards as $reward) {
     *   echo $reward["reward_id"];
     *   echo $reward["reward_name"];
     * }
     *
     * </code>
     *
     * @param int $id Reward ID
     */
    public function loadByRewardId($id)
    {
        $query = $this->getQuery();

        $query
            ->where("a.reward_id = ". (int)$id);

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
    }

    protected function getQuery()
    {
        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id AS transaction_id, a.receiver_id, a.reward_state, a.txn_id, a.reward_id, a.project_id, " .
                "b.title AS reward_name, ".
                "c.name, c.email, " .
                "d.title AS project"
            )
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->leftJoin($this->db->quoteName("#__crowdf_rewards", "b"). " ON a.reward_id = b.id")
            ->leftJoin($this->db->quoteName("#__users", "c") . " ON a.receiver_id = c.id")
            ->leftJoin($this->db->quoteName("#__crowdf_projects", "d") . " ON a.project_id = d.id");

        return $query;
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
