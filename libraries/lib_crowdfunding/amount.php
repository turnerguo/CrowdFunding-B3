<?php
/**
 * @package      CrowdFunding
 * @subpackage   Amounts
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing an amount.
 *
 * @package      CrowdFunding
 * @subpackage   Amounts
 */
class CrowdFundingAmount
{
    /**
     * Amount value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Currency object.
     *
     * @var CrowdFundingCurrency
     */
    protected $currency;

    /**
     * Initialize the object.
     *
     * <code>
     * $amount = 1,500.25;
     *
     * $amount   = new CrowdFundingAmount($amount);
     * </code>
     *
     * @param float $value
     */
    public function __construct($value = 0.00)
    {
        $this->value = $value;
    }

    /**
     * Set the currency object.
     *
     * <code>
     * $currencyId = 1;
     * $currency   = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId);
     *
     * $amount   = new CrowdFundingAmount();
     * $amount->setCurrency($currency);
     * </code>
     *
     * @param CrowdFundingCurrency $currency
     *
     * @return self
     */
    public function setCurrency(CrowdFundingCurrency $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Return the currency object.
     *
     * <code>
     * $amount   = new CrowdFundingAmount();
     * $currency = $amount->getCurrency();
     * </code>
     *
     * @return null|CrowdFundingCurrency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * This method returns an amount as currency, with a symbol and currency code.
     *
     * <code>
     * // Get currency object.
     * $currencyId = 1;
     * $currency   = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId);
     *
     * // Create amount object.
     * $amount = 1500.25;
     *
     * $amount   = new CrowdFundingAmount($amount);
     * $amount->setCurrency($currency);
     *
     * // Return $1,500.25 or 1,500.25USD.
     * echo $amount->formatCurrency();
     * </code>
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function formatCurrency()
    {
        if ($this->currency instanceof CrowdFundingCurrency) {
            return $this->currency->getAmountString($this->value);
        } else {
            throw new RuntimeException(JText::_("LIB_CROWDFUNDING_CURRENCY_NOT_DEFINED"));
        }
    }

    /**
     * This method formats an amount as decimal value depending of options or locale.
     *
     * <code>
     * // Get currency object.
     * $currencyId = 1;
     * $currency   = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId);
     *
     * // Create amount object.
     * $amount = 1500.25;
     *
     * $amount   = new CrowdFundingAmount($amount);
     * $amount->setCurrency($currency);
     *
     * // Return 1,500.25
     * echo $amount->format();
     * </code>
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function format()
    {
        if ($this->currency instanceof CrowdFundingCurrency) {
            return $this->currency->getAmountValue($this->value);
        } else {
            throw new RuntimeException(JText::_("LIB_CROWDFUNDING_CURRENCY_NOT_DEFINED"));
        }
    }

    /**
     * Use this method to parse a currency string.
     *
     * <code>
     * // Get currency.
     * $currencyId = 1;
     * $currency   = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId);
     *
     * $amount   = 1,500.25;
     * $amount   = new CrowdFundingAmount($amount);
     * $amount->setCurrency($currency);
     *
     * // Will return 1500.25.
     * $goal = $currency->parse();
     * </code>
     *
     * @return float
     */
    public function parse()
    {
        if ($this->currency instanceof CrowdFundingCurrency) {

            $intl             = (bool)$this->currency->getOption("intl", false);
            $fractionDigits   = abs($this->currency->getOption("fraction_digits", 2));

            // Use PHP Intl library to format the amount.
            if ($intl and extension_loaded('intl')) { // Generate currency string using PHP NumberFormatter ( Internationalization Functions )

                $locale = $this->currency->getOption("locale");

                // Get current locale code.
                if (!$locale) {
                    $lang   = JFactory::getLanguage();
                    $locale = str_replace("-", "_", $lang->getTag());
                }

                $numberFormat = new NumberFormatter($locale, NumberFormatter::DECIMAL);
                $numberFormat->setAttribute(NumberFormatter::FRACTION_DIGITS, $fractionDigits);

                $result = $numberFormat->parse($this->value, NumberFormatter::TYPE_DOUBLE);

            } else {
                $result = $this->parseAmount($this->value);
            }

        } else {
            $result = $this->parseAmount($this->value);
        }

        return (float)$result;
    }

    /**
     * Format amount string to decimal value.
     *
     * @param $value
     *
     * @return float
     */
    protected function parseAmount($value)
    {
        // Parse a string like this 1.560,25. The result is 1560.25.
        if (1 === preg_match("/\.?[0-9]{3},[0-9]{1,3}$/i", $value)) {
            $value = str_replace(".", "", $value);
            $value = str_replace(",", ".", $value);
            return (float)$value;
        }

        // Parse a string like this 45,00. The result is 45.00.
        if (1 === preg_match("/,[0-9]{1,3}$/i", $value)) {
            $value = str_replace(",", ".", $value);
            return (float)$value;
        }

        // Parse a string like this 1,560.25. The result is 1560.25.
        if (1 === preg_match("/^[0-9]+,[0-9]{3}\./i", $value)) {
            $value = str_replace(",", "", $value);
            return (float)$value;
        }

        return (float)$value;
    }

    /**
     * Set the amount value.
     *
     * <code>
     * $amount   = 1,500.25;
     *
     * $amount   = new CrowdFundingAmount();
     * $amount->setValue($amount);
     * </code>
     *
     * @param float $value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
