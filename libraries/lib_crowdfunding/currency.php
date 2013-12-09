<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingTableCurrency", JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_crowdfunding".DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."currency.php");
JLoader::register("CrowdFundingInterfaceTable", JPATH_LIBRARIES .DIRECTORY_SEPARATOR."crowdfunding".DIRECTORY_SEPARATOR."interface".DIRECTORY_SEPARATOR."table.php");

/**
 * This class contains methods that are used for managing currency.
 *
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingCurrency implements CrowdFundingInterfaceTable {
    
    /**
     * This is CrowdFunding Currency table object.
     * 
     * @var CrowdFundingTableCurrency
     */
    protected $table;
    protected static $instances = array();
    
    public function __construct($id = 0) {
        
        $this->table = new CrowdFundingTableCurrency(JFactory::getDbo());
        
        if(!empty($id)) {
            $this->table->load($id);
        }
    }
    
    public static function getInstance($id = 0)  {
    
        if (empty(self::$instances[$id])){
            $currency = new CrowdFundingCurrency($id);
            self::$instances[$id] = $currency;
        }
    
        return self::$instances[$id];
    }
    
    public function load($keys, $reset = true) {
        $this->table->load($keys, $reset);
    }
    
    public function bind($src, $ignore = array()) {
        $this->table->bind($src, $ignore);
    }
    
    public function store($updateNulls = false) {
        $this->table->store($updateNulls);
    }
    
    /**
     * This method generates an amount using symbol or code of the currency.
     * 
     * @param mixed This is a value used in the amount string. This can be float, integer,...
     * @param string This a locale code ( en_GB, de_DE,...)
     * @return string
     */
    public function getAmountString($value, $locale = null) {
        
        if(extension_loaded('intl')) { // Generate currency string using PHP NumberFormatter ( Internationalization Functions )
            
            // Get current locale code
            if(!$locale) {
                $lang   = JFactory::getLanguage();
                $locale = $lang->getName();
            }
            
            $numberFormat = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $amount       = $numberFormat->formatCurrency($value, $this->table->abbr);
            
        } else { // Generate a custom currency string.
        
            if(!empty($this->table->symbol)) { // Symbol
                
                if(0 == $this->table->position) { // symbol at beggining
                    $amount = $this->table->symbol.$value;
                } else { // symbol at end
                    $amount = $value.$this->table->symbol;
                }
                
            } else { // Code
                $amount = $value.$this->table->abbr;
            }
            
        }
        
        return $amount;
    }
    
    public function getAbbr() {
        return $this->table->abbr;
    }
    
    public function getSymbol() {
        return $this->table->symbol;
    }
    
}
