<?php
/**
 * @package      CrowdFunding
 * @subpackage   Currencies
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport("crowdfunding.currency");

/**
 * This class provides functionality that manage currencies.
 *
 * @package      CrowdFunding
 * @subpackage   Currencies
 */
class CrowdFundingCurrencies implements Iterator, Countable, ArrayAccess
{
    protected $items = array();
    protected $options;

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
     * $options    = new JRegistry();
     * $options->set("intl", true);
     * $options->set("format", "2/./,");
     *
     * $currencies   = new CrowdFundingCurrencies(JFactory::getDbo(), $options);
     * </code>
     *
     * @param JDatabaseDriver $db
     * @param @param null|Joomla\Registry\Registry $options
     */
    public function __construct(JDatabaseDriver $db, $options = null)
    {
        $this->db       = $db;

        // Set options.
        if (!is_null($options) and ($options instanceof JRegistry)) {
            $this->options  = $options;
        } else {
            $this->options  = new JRegistry;
        }
    }

    /**
     * Load currencies data by ID from database.
     *
     * <code>
     * $ids = array(1,2,3,4,5);
     * $currencies   = new CrowdFundingCurrencies(JFactory::getDbo());
     * $currencies->load($ids);
     *
     * foreach($currencies as $currency) {
     *   echo $currency["title"];
     *   echo $currency["abbr"];
     * }
     *
     * </code>
     *
     * @param array $ids
     */
    public function load($ids = array())
    {
        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.title, a.abbr, a.symbol, a.position")
            ->from($this->db->quoteName("#__crowdf_currencies", "a"));

        if (!empty($ids)) {
            JArrayHelper::toInteger($ids);
            $query->where("a.id IN ( " . implode(",", $ids) . " )");
        }

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
    }

    /**
     * Load currencies data by abbreviation from database.
     *
     * <code>
     * $ids = array("GBP", "EUR", "USD");
     * $currencies   = new CrowdFundingCurrencies(JFactory::getDbo());
     * $currencies->loadByAbbr($ids);
     *
     * foreach($currencies as $currency) {
     *   echo $currency["title"];
     *   echo $currency["abbr"];
     * }
     * </code>
     *
     * @param array $ids
     */
    public function loadByAbbr($ids = array())
    {
        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.title, a.abbr, a.symbol, a.position")
            ->from($this->db->quoteName("#__crowdf_currencies", "a"));

        if (!empty($ids)) {

            foreach ($ids as $key => $value) {
                $ids[$key] = $this->db->quote($value);
            }

            $query->where("a.abbr IN ( " . implode(",", $ids) . " )");
        }

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

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

    /**
     * Create a currency object by abbreviation and return it.
     *
     * <code>
     * $ids = array(1,2,3,4,5);
     * $currencies   = new CrowdFundingCurrencies(JFactory::getDbo());
     * $currencies->load($ids);
     *
     * $currency = $currencies->getCurrencyByAbbr("EUR");
     * </code>
     *
     * @param string $abbr
     *
     * @throws UnexpectedValueException
     *
     * @return null|CrowdFundingCurrency
     */
    public function getCurrencyByAbbr($abbr)
    {
        if (!$abbr) {
            throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_INVALID_CURRENCY_ABBREVIATION"));
        }

        $currency = null;

        foreach ($this->items as $item) {
            if (strcmp($abbr, $item["abbr"]) == 0) {

                $currency = new CrowdFundingCurrency();
                $currency->bind($item);

                if (!is_null($this->options) and ($this->options instanceof JRegistry)) {
                    $currency->setOption("intl", $this->options->get("locale_intl", false));
                    $currency->setOption("format", $this->options->get("amount_format", false));
                }

                break;
            }
        }

        return $currency;
    }

    /**
     * Create a currency object and return it.
     *
     * <code>
     * $ids = array(1,2,3,4,5);
     * $currencies   = new CrowdFundingCurrencies(JFactory::getDbo());
     * $currencies->load($ids);
     *
     * $currency = $currencies->getCurrency(1);
     * </code>
     *
     * @param int $id
     *
     * @throws UnexpectedValueException
     *
     * @return null|CrowdFundingCurrency
     */
    public function getCurrency($id)
    {
        if (!$id) {
            throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_INVALID_CURRENCY_ID"));
        }

        $currency = null;

        foreach ($this->items as $item) {

            if ($id == $item["id"]) {

                $currency = new CrowdFundingCurrency();
                $currency->bind($item);

                if (!is_null($this->options) and ($this->options instanceof JRegistry)) {
                    $currency->setOption("intl", $this->options->get("locale_intl", false));
                    $currency->setOption("format", $this->options->get("amount_format", false));
                }

                break;
            }

        }

        return $currency;
    }
}
