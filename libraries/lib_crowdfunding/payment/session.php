<?php
/**
 * @package      CrowdFunding
 * @subpackage   Payments
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage payment session.
 * The session is used for storing data in the process of requests between application and payment services.
 *
 * @package      CrowdFunding
 * @subpackage   Payments
 */
class CrowdFundingPaymentSession
{
    protected $id;
    protected $user_id;
    protected $project_id;
    protected $reward_id;
    protected $record_date;
    protected $txn_id;
    protected $token;
    protected $gateway;
    protected $auser_id;

    protected $intention_id;

    /**
     * Database object.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    /**
     * Initialize the object.
     *
     * <code>
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Set the database object.
     *
     * <code>
     * $paymentSession    = new CrowdFundingPaymentSession();
     * $paymentSession->setDb(JFactory::getDbo());
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
     * $keys = array(
     *  "project_id" = 1,
     *  "intention_id" = 2
     * );
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($keys);
     * </code>
     *
     * @param array $keys
     *
     * @throws UnexpectedValueException
     */
    public function load($keys)
    {
        if (!$keys) {
            throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_INVALID_PAYMENTSESSION_KEYS"));
        }

        $query = $this->db->getQuery(true);
        $query
            ->select(
                "a.id, a.user_id, a.project_id, a.reward_id, a.record_date, " .
                "a.txn_id, a.token, a.gateway, a.auser_id, a.intention_id"
            )
            ->from($this->db->quoteName("#__crowdf_payment_sessions", "a"));

        if (!is_array($keys)) {
            $query->where("a.id = " . (int)$keys);
        } else {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName("a." . $key) . "=" . $this->db->quote($value));
            }
        }

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!$result) {
            $result = array();
        }

        $this->bind($result);
    }

    /**
     * Set data to object properties.
     *
     * <code>
     * $data = (
     *  "user_id"  => 2,
     *  "intention_id" => 3
     * );
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->bind($data);
     * </code>
     *
     * @param array $data
     * @param array $ignored This is a name of an index, that will be ignored and will not be set as object parameter.
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
     * Store the data in database.
     *
     * <code>
     * $data = (
     *  "user_id"  => 2,
     *  "intention_id" => 3
     * );
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->bind($data);
     * $paymentSession->store();
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

    protected function insertObject()
    {
        $recordDate   = (!$this->record_date) ? "NULL" : $this->db->quote($this->record_date);

        $query = $this->db->getQuery(true);
        $query
            ->insert($this->db->quoteName("#__crowdf_payment_sessions"))
            ->set($this->db->quoteName("user_id") . "=" . $this->db->quote($this->user_id))
            ->set($this->db->quoteName("project_id") . "=" . $this->db->quote($this->project_id))
            ->set($this->db->quoteName("reward_id") . "=" . $this->db->quote($this->reward_id))
            ->set($this->db->quoteName("record_date") . "=" . $recordDate)
            ->set($this->db->quoteName("txn_id") . "=" . $this->db->quote($this->txn_id))
            ->set($this->db->quoteName("token") . "=" . $this->db->quote($this->token))
            ->set($this->db->quoteName("gateway") . "=" . $this->db->quote($this->gateway))
            ->set($this->db->quoteName("auser_id") . "=" . $this->db->quote($this->auser_id))
            ->set($this->db->quoteName("intention_id") . "=" . $this->db->quote($this->intention_id));

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    protected function updateObject()
    {
        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName("#__crowdf_payment_sessions"))
            ->set($this->db->quoteName("user_id") . "=" . $this->db->quote($this->user_id))
            ->set($this->db->quoteName("project_id") . "=" . $this->db->quote($this->project_id))
            ->set($this->db->quoteName("reward_id") . "=" . $this->db->quote($this->reward_id))
            ->set($this->db->quoteName("record_date") . "=" . $this->db->quote($this->record_date))
            ->set($this->db->quoteName("txn_id") . "=" . $this->db->quote($this->txn_id))
            ->set($this->db->quoteName("token") . "=" . $this->db->quote($this->token))
            ->set($this->db->quoteName("gateway") . "=" . $this->db->quote($this->gateway))
            ->set($this->db->quoteName("auser_id") . "=" . $this->db->quote($this->auser_id))
            ->set($this->db->quoteName("intention_id") . "=" . $this->db->quote($this->intention_id))
            ->where($this->db->quoteName("id") . "=" . $this->db->quote($this->id));

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Remove a payment session record from database.
     *
     * <code>
     * $keys = (
     *  "user_id"  => 2,
     *  "intention_id" => 3
     * );
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($keys);
     * $paymentSession->delete();
     * </code>
     */
    public function delete()
    {
        $query = $this->db->getQuery(true);
        $query
            ->delete($this->db->quoteName("#__crowdf_payment_sessions"))
            ->where($this->db->quoteName("id") . "=" . (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->reset();
    }

    /**
     * Reset object properties.
     *
     * <code>
     * $keys = (
     *  "user_id"  => 2,
     *  "intention_id" => 3
     * );
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($keys);
     *
     * if (!$paymentSession->getToken()) {
     *     $paymentSession->reset();
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
     * Return payment session ID.
     *
     * <code>
     * $keys = (
     *  "user_id"  => 2,
     *  "intention_id" => 3
     * );
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($keys);
     *
     * if (!$paymentSession->getId()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * Set user ID to the object.
     *
     * <code>
     * $paymentSessionId = 1;
     * $userId = 2;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setUserId($userId);
     * </code>
     *
     * @param int $userId
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Return user ID which is part of current payment session.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $userId = $paymentSession->getUserId();
     * </code>
     *
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->user_id;
    }

    /**
     * Set the ID of the anonymous user.
     *
     * <code>
     * $paymentSessionId = 1;
     * $anonymousUserId = 2;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setAnonymousUserId($anonymousUserId);
     * </code>
     *
     * @param int $auserId
     *
     * @return self
     */
    public function setAnonymousUserId($auserId)
    {
        $this->auser_id = $auserId;

        return $this;
    }

    /**
     * Return the ID (hash) of anonymous user which is part of current payment session.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $anonymousUserId = $paymentSession->getAnonymousUserId();
     * </code>
     *
     * @return string
     */
    public function getAnonymousUserId()
    {
        return $this->auser_id;
    }

    /**
     * Set a project ID.
     *
     * <code>
     * $paymentSessionId = 1;
     * $projectId = 2;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setProjectId($projectId);
     * </code>
     *
     * @param int $projectId
     *
     * @return self
     */
    public function setProjectId($projectId)
    {
        $this->project_id = $projectId;

        return $this;
    }

    /**
     * Return project ID.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $projectId = $paymentSession->getProjectId();
     * </code>
     *
     * @return int
     */
    public function getProjectId()
    {
        return (int)$this->project_id;
    }

    /**
     * Set a reward ID.
     *
     * <code>
     * $paymentSessionId = 1;
     * $rewardId = 2;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setRewardId($rewardId);
     * </code>
     *
     * @param int $rewardId
     *
     * @return self
     */
    public function setRewardId($rewardId)
    {
        $this->reward_id = $rewardId;

        return $this;
    }

    /**
     * Return reward ID.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $rewardId = $paymentSession->getRewardId();
     * </code>
     *
     * @return int
     */
    public function getRewardId()
    {
        return (int)$this->reward_id;
    }

    /**
     * Set the date of the database record.
     *
     * <code>
     * $paymentSessionId = 1;
     * $date = "01-01-2014";
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setRecordDateId($date);
     * </code>
     *
     * @param string $recordDate
     *
     * @return self
     */
    public function setRecordDate($recordDate)
    {
        $this->record_date = $recordDate;

        return $this;
    }

    /**
     * Return the date of current record.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $date = $paymentSession->getRecordDate();
     * </code>
     *
     * @return int
     */
    public function getRecordDate()
    {
        return $this->record_date;
    }

    /**
     * Set the ID of transaction that comes from payment gateway.
     *
     * <code>
     * $paymentSessionId = 1;
     * $txnId = "GEN123456";
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setTransactionId($txnId);
     * </code>
     *
     * @param string $txnId
     *
     * @return self
     */
    public function setTransactionId($txnId)
    {
        $this->txn_id = $txnId;

        return $this;
    }

    /**
     * Return ID of transaction that comes from payment service.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $txnId = $paymentSession->getTransactionId();
     * </code>
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->txn_id;
    }

    /**
     * Set the name of the payment gateway.
     *
     * <code>
     * $paymentSessionId = 1;
     * $name = "PayPal";
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setGateway($name);
     * </code>
     *
     * @param string $gateway
     *
     * @return self
     */
    public function setGateway($gateway)
    {
        $this->gateway = $gateway;

        return $this;
    }

    /**
     * Return the name of payment service.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $name = $paymentSession->getGateway();
     * </code>
     *
     * @return string
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * Set a token of transaction that comes from a payment gateway.
     *
     * <code>
     * $paymentSessionId = 1;
     * $token = "TOKEN12345";
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setToken($token);
     * </code>
     *
     * @param string $token
     *
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Return a token that comes from payment service.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $token = $paymentSession->getToken();
     * </code>
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set intention ID.
     *
     * <code>
     * $paymentSessionId = 1;
     * $intentionId = 2;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setIntentionId($intentionId);
     * </code>
     *
     * @param int $intentionId
     *
     * @return self
     */
    public function setIntentionId($intentionId)
    {
        $this->intention_id = $intentionId;

        return $this;
    }

    /**
     * Return the ID of intention.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $intentionId = $paymentSession->getIntentionId();
     * </code>
     *
     * @return int
     */
    public function getIntentionId()
    {
        return (int)$this->intention_id;
    }

    /**
     * Check if payment session has been handled from anonymous user.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * if (!$paymentSession->isAnonymous()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function isAnonymous()
    {
        return (!$this->auser_id) ? false : true;
    }

    /**
     * Return object properties.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new CrowdFundingPaymentSession(JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $properties = $paymentSession->getProperties();
     * </code>
     *
     * @return array
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
