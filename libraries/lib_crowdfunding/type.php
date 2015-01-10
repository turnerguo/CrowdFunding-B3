<?php
/**
 * @package      CrowdFunding
 * @subpackage   Types
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality for managing projects types.
 *
 * @package      CrowdFunding
 * @subpackage   Types
 */
class CrowdFundingType
{
    protected $id = 0;
    protected $title;
    protected $description;
    protected $params = array();

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
     * $type    = new CrowdFundingType(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     *
     * @return self
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db       = $db;
    }

    /**
     * Set a database object.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new CrowdFundingType();
     * $type->setDb(JFactory::getDbo());
     * $type->load($typeId);
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
     * Load a data about a type from database.
     *
     * @param int $id Type ID
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new CrowdFundingType(JFactory::getDbo());
     * $type->load($typeId);
     * </code>
     */
    public function load($id)
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.title, a.description, a.params")
            ->from($this->db->quoteName("#__crowdf_types", "a"))
            ->where("a.id = " . (int)$id);

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
     * $data = array(
     *  "title" => "A ticked for...",
     *  "amount" => "10.00"
     * );
     *
     * $type    = new CrowdFundingType();
     * $type->bind($data);
     * </code>
     *
     * @param array $data
     * @param array $ignored
     */
    public function bind($data, $ignored = array())
    {
        if (isset($data["params"])) {
            $this->setParams($data["params"]);
            unset($data["params"]);
        }

        foreach ($data as $key => $value) {
            if (!in_array($key, $ignored)) {
                $this->$key = $value;
            }
        }

    }

    /**
     * Return type ID.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new CrowdFundingType(JFactory::getDbo());
     * $type->load($typeId);
     *
     * if (!$type->getId()) {
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
     * Set type title.
     *
     * <code>
     * $typeId  = 1;
     * $title   = "Standard projects";
     *
     * $type    = new CrowdFundingType(JFactory::getDbo());
     * $type->load($typeId);
     *
     * $type->setTitle($title);
     * $type->store();
     * </code>
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Return type title.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new CrowdFundingType(JFactory::getDbo());
     * $type->load($typeId);
     *
     * echo $type->getTitle();
     * </code>
     *
     * @return int
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set type title.
     *
     * <code>
     * $typeId  = 1;
     * $description   = "My description...";
     *
     * $type    = new CrowdFundingType(JFactory::getDbo());
     * $type->load($typeId);
     *
     * $type->setDescription($title);
     * $type->store();
     * </code>
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set type title.
     *
     * <code>
     * $params = array(
     *  "rewards" = 1
     * );
     *
     * $type    = new CrowdFundingType(JFactory::getDbo());
     * $type->load($typeId);
     *
     * $type->setParams($params);
     * $type->store();
     * </code>
     *
     * @param string $params
     *
     * @return self
     */
    public function setParams($params)
    {
        if (is_string($params)) {
            $this->params = (array)json_decode($params, true);
        } elseif (is_object($params)) {
            $this->params = JArrayHelper::fromObject($params);
        } elseif (is_array($params)) {
            $this->params = $params;
        } else {
            $this->params = array();
        }

        return $this;
    }

    /**
     * Return type parameters.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new CrowdFundingType(JFactory::getDbo());
     * $type->load($typeId);
     *
     * $params = $type->getParams();
     * </code>
     *
     * @return int
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Return description of the type.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new CrowdFundingType();
     * $type->setDb(JFactory::getDbo());
     * $type->load($typeId);
     *
     * $description = $type->getDescription();
     * </code>
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Check the parameters of the type, if rewards are enabled for this type.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new CrowdFundingType(JFactory::getDbo());
     * $type->load($typeId);
     *
     * if (!$type->isRewardsEnabled()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function isRewardsEnabled()
    {
        $rewards = false;
        if (isset($this->params["rewards"])) {
            $rewards = (!$this->params["rewards"]) ? false : true;
        }

        return $rewards;
    }

    /**
     * Returns an associative array of object properties.
     *
     * <code>
     * $typeId = 1;
     *
     * $type    = new CrowdFundingType();
     * $type->setDb(JFactory::getDbo());
     * $type->load($typeId);
     *
     * $properties = $type->getProperties();
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
