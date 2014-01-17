<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing currency.
 *
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingCurrency {
    
    protected $id;
    protected $title;
    protected $abbr;
    protected $symbol;
    protected $position;
    
    protected $intl = false;
    
    /**
     * Database driver.
     * 
     * @var JDatabase
     */
    protected $db;
    
    protected static $instances = array();
    
    public function __construct(JDatabase $db, $id = 0) {
        
        $this->db = $db;
        
        if(!empty($id)) {
            $this->load($id);
        }
    }
    
    public static function getInstance(JDatabase $db, $id = 0)  {
    
        if (!isset(self::$instances[$id])){
            self::$instances[$id] = new CrowdFundingCurrency($db, $id);
        }
    
        return self::$instances[$id];
    }
    
    public function load($id) {
        
        $query = $this->db->getQuery(true);
        $query
            ->select("a.id, a.title, a.abbr, a.symbol, a.position")
            ->from($this->db->quoteName("#__crowdf_currencies", "a"))
            ->where("a.id = ".(int)$id);
        
        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();
        
        if(!$result) {
            $result = array();
        }
        
        $this->bind($result);
        
    }
    
    public function bind($data, $ignore = array()) {
        
        foreach($data as $key => $value) {
            if(!in_array($key, $ignore)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * This method generates an amount using symbol or code of the currency.
     * 
     * @param mixed This is a value used in the amount string. This can be float, integer,...
     * @param boolean A flag that disable using of PHP Intl, even though it is loaded into the system.
     * @param string This a locale code ( en_GB, de_DE,...)
     * @return string
     */
    public function getAmountString($value, $forceIntl = null, $locale = null) {
        
        // Change the flag for using PHP Intl library.
        if(!is_null($forceIntl) AND is_bool($forceIntl)) {
            $useIntl = $forceIntl; 
        } else {
            $useIntl = $this->intl;
        }
        
        // Use PHP Intl library.
        if($useIntl AND extension_loaded('intl')) { // Generate currency string using PHP NumberFormatter ( Internationalization Functions )
            
            // Get current locale code.
            if(!$locale) {
                $lang   = JFactory::getLanguage();
                $locale = $lang->getName();
            }
            
            $numberFormat = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $amount       = $numberFormat->formatCurrency($value, $this->abbr);
            
        } else { // Generate a custom currency string.
        
            if(!empty($this->symbol)) { // Symbol
                
                if(0 == $this->position) { // Symbol at beggining.
                    $amount = $this->symbol.$value;
                } else { // Symbol at end.
                    $amount = $value.$this->symbol;
                }
                
            } else { // Code
                $amount = $value.$this->abbr;
            }
            
        }
        
        return $amount;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getAbbr() {
        return $this->abbr;
    }
    
    public function getSymbol() {
        return $this->symbol;
    }
    
    public function enableIntl() {
        $this->intl = true;
    }
    
}
