<?php
/**
 * @package      CrowdFunding
 * @subpackage   Payments
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage intentions.
 *
 * @package      CrowdFunding
 * @subpackage   Payments
 */
class CrowdFundingIntention
{
    protected $id;
    protected $user_id;
    protected $project_id;
    protected $reward_id;
    protected $record_date;
    protected $gateway;
    protected $gateway_data;
    protected $auser_id;
    protected $session_id;

    /**
     * This is a unique string where is stored a unique key from a payment gateway.
     * That can be transaction ID, token,...
     *
     * @var mixed
     */
    protected $unique_key;

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
     * $intention    = new CrowdFundingIntention(JFactory::getDbo());
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
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
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
     * Load intention data from database.
     *
     * <code>
     * $keys = array(
     *  "user_id" => 1
     * );
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($keys);
     * </code>
     *
     * @param int|array $keys Intention keys.
     */
    public function load($keys)
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.user_id, a.project_id, a.reward_id, a.record_date, " .
                "a.unique_key, a.gateway, a.gateway_data, a.auser_id, a.session_id"
            )
            ->from($this->db->quoteName("#__crowdf_intentions", "a"));

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

        // Decode gateway data.
        $gatewayData = JArrayHelper::getValue($result, "gateway_data");
        $this->gateway_data = (!empty($gatewayData)) ? (array)json_decode($gatewayData, true) : (array)$gatewayData;

        $ignored     = array("gateway_data");
        $this->bind($result, $ignored);
    }

    /**
     * Set data to object properties.
     *
     * <code>
     * $data = array(
     *  "user_id" => 1,
     *  "gateway" => "PayPal"
     * );
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->bind($data);
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
     *  "user_id" => 1,
     *  "gateway" => "PayPal"
     * );
     *
     * $intention    = new CrowdFundingIntention(JFactory::getDbo());
     * $intention->bind($data);
     * $intention->store();
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
        // Encode the gateway data to JSON format.
        $gatewayData = $this->encodeDataToJson();

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName("#__crowdf_intentions"))
            ->set($this->db->quoteName("user_id") . "=" . $this->db->quote($this->user_id))
            ->set($this->db->quoteName("project_id") . "=" . $this->db->quote($this->project_id))
            ->set($this->db->quoteName("reward_id") . "=" . $this->db->quote($this->reward_id))
            ->set($this->db->quoteName("unique_key") . "=" . $this->db->quote($this->unique_key))
            ->set($this->db->quoteName("gateway") . "=" . $this->db->quote($this->gateway))
            ->set($this->db->quoteName("gateway_data") . "=" . $this->db->quote($gatewayData))
            ->set($this->db->quoteName("auser_id") . "=" . $this->db->quote($this->auser_id))
            ->set($this->db->quoteName("session_id") . "=" . $this->db->quote($this->session_id))
            ->where($this->db->quoteName("id") ."=". (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        $recordDate   = (!$this->record_date) ? "NULL" : $this->db->quote($this->record_date);

        // Encode the gateway data to JSON format.
        $gatewayData = $this->encodeDataToJson();

        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName("#__crowdf_intentions"))
            ->set($this->db->quoteName("user_id") . "=" . $this->db->quote($this->user_id))
            ->set($this->db->quoteName("project_id") . "=" . $this->db->quote($this->project_id))
            ->set($this->db->quoteName("reward_id") . "=" . $this->db->quote($this->reward_id))
            ->set($this->db->quoteName("record_date") . "=" . $recordDate)
            ->set($this->db->quoteName("unique_key") . "=" . $this->db->quote($this->unique_key))
            ->set($this->db->quoteName("gateway") . "=" . $this->db->quote($this->gateway))
            ->set($this->db->quoteName("gateway_data") . "=" . $this->db->quote($gatewayData))
            ->set($this->db->quoteName("auser_id") . "=" . $this->db->quote($this->auser_id))
            ->set($this->db->quoteName("session_id") . "=" . $this->db->quote($this->session_id));

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    protected function encodeDataToJson()
    {
        if (!is_array($this->gateway_data)) {
            $this->gateway_data = array();
        }
        return json_encode($this->gateway_data);
    }

    /**
     * Remove intention record from database.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention(JFactory::getDbo());
     * $intention->load($intentionId);
     * $intention->delete();
     * </code>
     */
    public function delete()
    {
        $query = $this->db->getQuery(true);

        $query
            ->delete($this->db->quoteName("#__crowdf_intentions"))
            ->where($this->db->quoteName("id") ."=". (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->reset();
    }

    /**
     * Reset the properties of the current object.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * if (!$intention->getId()) {
     *     $intention->reset();
     * }
     * </code>
     */
    public function reset()
    {
        $properties = $this->getProperties();

        foreach ($properties as $key => $value) {
            $this->$key = null;
        }
    }

    /**
     * Return intention ID.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * if (!$intention->getId()) {
     * ...
     * }
     * </code>
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the ID of intention.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention(JFactory::getDbo());
     *
     * $intention->setId($intentionId);
     * </code>
     */
    public function setId($id)
    {
        $this->id = (int)$id;

        return $this;
    }

    /**
     * Return user ID.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $userId = $intention->getUserId();
     * </code>
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Return user ID (hash) of anonymous user.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $anonymousUserId = $intention->getAnonymousUserId();
     * </code>
     */
    public function getAnonymousUserId()
    {
        return $this->auser_id;
    }

    /**
     * Return project ID.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $projectId = $intention->getProjectIdUserId();
     * </code>
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * Return reward ID.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $rewardId = $intention->getRewardId();
     * </code>
     */
    public function getRewardId()
    {
        return $this->reward_id;
    }

    /**
     * Return the date of the record.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $date = $intention->getRecordDate();
     * </code>
     */
    public function getRecordDate()
    {
        return $this->record_date;
    }

    /**
     * Set gateway name.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $intention->setGateway("PayPal");
     * </code>
     *
     * @param string $name
     *
     * @return self
     */
    public function setGateway($name)
    {
        $this->gateway = $name;

        return $this;
    }

    /**
     * Return gateway name.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $gateway = $intention->getGateway();
     * </code>
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * Return gateway data.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $gatewayData = $intention->getGatewayData();
     * </code>
     */
    public function getGatewayData()
    {
        return $this->gateway_data;
    }

    /**
     * Set a gateway data.
     *
     * <code>
     * $intentionId  = 1;
     * $data        = array(
     *    "token" => "TOKEN1234"
     * );
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $intention->setGatewayData($data);
     * </code>
     *
     * @param array $data
     *
     * @return self
     */
    public function setGatewayData(array $data)
    {
        $this->gateway_data = $data;

        return $this;
    }

    /**
     * Return a value of a gateway data.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $gateway = $intention->getData("token");
     * </code>
     */
    public function getData($key, $default = null)
    {
        return (!isset($this->gateway_data[$key])) ? $default : $this->gateway_data[$key];
    }

    /**
     * Set a gateway data value.
     *
     * <code>
     * $intentionId  = 1;
     * $token        = "TOKEN1234";
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $intention->setData("token", $token);
     * </code>
     *
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    public function setData($key, $value)
    {
        $this->gateway_data[$key] = $value;

        return $this;
    }

    /**
     * Return a unique key that comes from a payment gateway.
     * That can be transaction ID, token,...
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $uniqueKey = $intention->getUniqueKey();
     * </code>
     */
    public function getUniqueKey()
    {
        return $this->unique_key;
    }

    /**
     * Set  unique key that comes from a payment gateway.
     * That can be transaction ID, token,...
     *
     * <code>
     * $intentionId  = 1;
     * $token        = "TOKEN1234";
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $intention->setUniqueKey($token);
     * </code>
     *
     * @param string $key
     * @return self
     */
    public function setUniqueKey($key)
    {
        $this->unique_key = $key;

        return $this;
    }

    /**
     * Store the unique key into database.
     *
     * <code>
     * $intentionId  = 1;
     * $token        = "TOKEN1234";
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $intention->setUniqueKey($token);
     * $intention->storeUniqueKey();
     * </code>
     *
     * @return self
     */
    public function storeUniqueKey()
    {
        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName("#__crowdf_intentions"))
            ->set($this->db->quoteName("unique_key") ."=". $this->db->quote($this->unique_key))
            ->where($this->db->quoteName("id") ."=". (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();

        return $this;
    }

    /**
     * Return session ID.
     *
     * <code>
     * $intentionId  = 1;
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $sessionId = $intention->getSessionId();
     * </code>
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * Set session ID.
     *
     * <code>
     * $intentionId  = 1;
     * $sessionId    = "SESSION_ID_1234";
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * $intention->setSessionId($sessionId);
     * </code>
     *
     * @param string $sessionId
     * @return self
     */
    public function setSessionId($sessionId)
    {
        $this->session_id = $sessionId;

        return $this;
    }

    /**
     * Check if the intention record is of an anonymous user.
     *
     * <code>
     * $intentionId  = 1;
     * $token        = "TOKEN1234";
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($intentionId);
     *
     * if (!$intention->isAnonymous()) {
     * ...
     * }
     * </code>
     */
    public function isAnonymous()
    {
        return (!$this->auser_id) ? false : true;
    }

    /**
     * Returns an associative array of object properties.
     *
     * <code>
     * $keys = array(
     *  "user_id" => 1
     * );
     *
     * $intention    = new CrowdFundingIntention();
     * $intention->setDb(JFactory::getDbo());
     * $intention->load($keys);
     *
     * $properties = $intention->getProperties();
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
                break;
            }
        }

        return $vars;
    }
}
