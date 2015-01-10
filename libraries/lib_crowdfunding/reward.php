<?php
/**
 * @package      CrowdFunding
 * @subpackage   Rewards
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage a reward.
 *
 * @package      CrowdFunding
 * @subpackage   Rewards
 */
class CrowdFundingReward
{
    protected $id;
    protected $title;
    protected $description;
    protected $amount;
    protected $number;
    protected $distributed;
    protected $delivery;
    protected $shipping;
    protected $image;
    protected $image_thumb;
    protected $image_square;
    protected $published;
    protected $project_id;
    protected $user_id;
    protected $available = 0;

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
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver  $db
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Set the database object.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward();
     * $reward->setDb(JFactory::getDbo());
     * $reward->load($rewardId);
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
     * Load reward data from database by reward ID or combination of keys ( id, project_id,...).
     *
     * <code>
     * $keys = array(
     *  "id" => 1,
     *  "project_id" => 2
     * );
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($keys);
     * </code>
     *
     * @param int|array $keys Reward IDs.
     */
    public function load($keys)
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.title, a.description, a.amount, a.number, a.distributed, a.delivery, " .
                "a.shipping, a.image, a.image_thumb, a.image_square, a.published, a.project_id, " .
                "b.user_id"
            )
            ->from($this->db->quoteName("#__crowdf_rewards", "a"))
            ->innerJoin($this->db->quoteName("#__crowdf_projects", "b") . " ON a.project_id = b.id");

        if (!is_array($keys)) {
            $query->where("a.id = " . (int)$keys);
        } else {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName("a.".$key) . "=" . $this->db->quote($value));
            }
        }

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!$result) {
            $result = array();
        }

        $this->bind($result);

        // Calculate available
        $this->available = $this->calculateAvailable();
    }

    /**
     * Set data to object properties.
     *
     * <code>
     * $data = array(
     *  "title" => "A ticked for...",
     *  "amount" => "10.00"
     * );
     *
     * $reward    = new CrowdFundingReward();
     * $reward->bind($data);
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
     * Store data to database.
     *
     * <code>
     * $data = array(
     *  "title" => "Reward #1",
     *  "amount" => "10.00"
     * );
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->bind($data);
     * $reward->store();
     * </code>
     */
    public function store()
    {
        if (!$this->id) { // Insert
            $this->insertObject();
        } else { // Update
            $this->updateObject();
        }
    }

    protected function updateObject()
    {
        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName("#__crowdf_rewards"))
            ->set($this->db->quoteName("title") . "=" . $this->db->quote($this->title))
            ->set($this->db->quoteName("description") . "=" . $this->db->quote($this->description))
            ->set($this->db->quoteName("amount") . "=" . $this->db->quote($this->amount))
            ->set($this->db->quoteName("number") . "=" . (int)$this->number)
            ->set($this->db->quoteName("distributed") . "=" . (int)$this->distributed)
            ->set($this->db->quoteName("delivery") . "=" . $this->db->quote($this->delivery))
            ->set($this->db->quoteName("shipping") . "=" . $this->db->quote($this->shipping))
            ->set($this->db->quoteName("image") . "=" . $this->db->quote($this->image))
            ->set($this->db->quoteName("image_thumb") . "=" . $this->db->quote($this->image_thumb))
            ->set($this->db->quoteName("image_square") . "=" . $this->db->quote($this->image_square))
            ->set($this->db->quoteName("published") . "=" . $this->db->quote($this->published))
            ->set($this->db->quoteName("project_id") . "=" . (int)$this->project_id)
            ->where($this->db->quoteName("id") ."=". (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName("#__crowdf_rewards"))
            ->set($this->db->quoteName("title") . "=" . $this->db->quote($this->title))
            ->set($this->db->quoteName("description") . "=" . $this->db->quote($this->description))
            ->set($this->db->quoteName("amount") . "=" . $this->db->quote($this->amount))
            ->set($this->db->quoteName("number") . "=" . (int)$this->number)
            ->set($this->db->quoteName("distributed") . "=" . (int)$this->distributed)
            ->set($this->db->quoteName("delivery") . "=" . $this->db->quote($this->delivery))
            ->set($this->db->quoteName("shipping") . "=" . $this->db->quote($this->shipping))
            ->set($this->db->quoteName("image") . "=" . $this->db->quote($this->image))
            ->set($this->db->quoteName("image_thumb") . "=" . $this->db->quote($this->image_thumb))
            ->set($this->db->quoteName("image_square") . "=" . $this->db->quote($this->image_square))
            ->set($this->db->quoteName("published") . "=" . $this->db->quote($this->published))
            ->set($this->db->quoteName("project_id") . "=" . (int)$this->project_id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    /**
     * Return reward ID.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * if (!$reward->getId()) {
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
     * Return reward title.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $title = $reward->getTitle();
     * </code>
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return reward description.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $description = $reward->getDescription();
     * </code>
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return reward amount.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $amount = $reward->getAmount();
     * </code>
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Return the ID of the user which provides the reward.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $userId = $reward->getUserId();
     * </code>
     *
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->user_id;
    }

    /**
     * Return a reward image.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $image = $reward->getImage();
     * </code>
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Return the thumbnail of the reward.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $thumbnail = $reward->getImageThumbnail();
     * </code>
     *
     * @return string
     */
    public function getImageThumbnail()
    {
        return $this->image_thumb;
    }

    /**
     * Return the square image of the reward.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $square = $reward->getImageSquare();
     * </code>
     *
     * @return string
     */
    public function getImageSquare()
    {
        return $this->image_square;
    }

    /**
     * Return the date to which must be delivered the reward.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $date = $reward->getDeliveryDate();
     * </code>
     *
     * @return string
     */
    public function getDeliveryDate()
    {
        return $this->delivery;
    }

    /**
     * Return the number of the reward.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $number = $reward->getNumber();
     * </code>
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Return an ID of a project.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $projectId = $reward->getProjectId();
     * </code>
     *
     * @return int
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * Return the number of distributed rewards.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $distributed = $reward->getNumber();
     * </code>
     *
     * @return int
     */
    public function getDistributed()
    {
        return $this->distributed;
    }

    /**
     * Increase the number of distributed rewards.
     *
     * <code>
     * $rewardId = 1;
     *
     * $reward    = new CrowdFundingReward();
     * $reward->setDb(JFactory::getDbo());
     * $reward->load($rewardId);
     * $reward->increaseDistributed();
     * $reward->store();
     * </code>
     *
     * @param integer
     */
    public function increaseDistributed($number = 1)
    {
        $distributed = $this->distributed + $number;

        if ($distributed <= $this->number) {
            $this->distributed = $distributed;
            $this->available   = $this->number - $this->distributed;
        }
    }

    /**
     * Update the number of distributed rewards.
     *
     * <code>
     * $rewardId = 1;
     *
     * $reward    = new CrowdFundingReward();
     * $reward->setDb(JFactory::getDbo());
     * $reward->load($rewardId);
     * $reward->increaseDistributed();
     * $reward->updateDistributed();
     * </code>
     */
    public function updateDistributed()
    {
        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName("#__crowdf_rewards"))
            ->set($this->db->quoteName("distributed") . "=" . (int)$this->distributed)
            ->where($this->db->quoteName("id") ."=". (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Check for the type "limited" of the reward.
     * If there is a number of rewards, it is limited.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * if (!$reward->isLimited()) {
     * ....
     * }
     * </code>
     *
     * @return bool
     */
    public function isLimited()
    {
        return (!empty($this->number)) ? true : false;
    }

    /**
     * Return the number of the available rewards.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $available = $reward->getAvailable();
     * </code>
     *
     * @return integer
     */
    public function getAvailable()
    {
        return $this->available;
    }

    protected function calculateAvailable()
    {
        if ($this->isLimited()) {
            return $this->number - $this->distributed;
        }

        return 0;
    }

    /**
     * This method checks for selected reward from user.
     * It will be checked, if the reward is part of transactions.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * if ($reward->isSelectedByUser()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isSelectedByUser()
    {
        $query = $this->db->getQuery(true);

        // Count number of selections.
        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.reward_id = " . (int)$this->id);

        $this->db->setQuery($query, 0, 1);
        $number = $this->db->loadResult();

        return (!$number) ? false : true;
    }

    /**
     * Set the reward state as trashed.
     *
     * <code>
     * $rewardId  = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $reward->trash();
     * </code>
     */
    public function trash()
    {
        if (!$this->id) {
            throw new RuntimeException("LIB_CROWDFUNDING_INVALID_REWARD");
        }

        // Set the state to "trashed" value.
        jimport("crowdfunding.constants");
        $this->published = CrowdFundingConstants::TRASHED;

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName("#__crowdf_rewards"))
            ->set($this->db->quoteName("published") . "=" . $this->db->quote($this->published))
            ->where($this->db->quoteName("id") . "=" . (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Returns an associative array of object properties.
     *
     * <code>
     * $rewardId = 1;
     *
     * $reward    = new CrowdFundingReward(JFactory::getDbo());
     * $reward->load($rewardId);
     *
     * $properties = $reward->getProperties();
     * </code>
     *
     * @return  array
     */
    public function getProperties()
    {
        $vars = get_object_vars($this);

        foreach ($vars as $key => $value) {
            if (strcmp("db", $key) == 0) {
                unset($vars[$key]);
            }
        }

        return $vars;
    }
}
