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
 * This class contains methods that are used for managing a country.
 *
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingCountry {
    
    protected $id;
    protected $name;
    protected $code;
    protected $code4;
    protected $latitude;
    protected $longitude;
    protected $currency;
    protected $timezone;
    
    protected $db;
    
    public function __construct(JDatabase $db) {
        
        $this->db = $db;
        
    }
    
    public function load($keys) {
        
        $query = $this->db->getQuery(true);
        
        $query
            ->select("a.id, a.name, a.code, a.code4, a.latitude, a.longitude, a.currency, a.code")
            ->from($this->db->quoteName("#__crowdf_countries", "a"));
        
        if(!is_scalar($keys)) {
            foreach($keys as $key => $value) {
                $query->where($this->db->quoteName("a.".$key) ."=".$this->db->quote($value));
            }
        } else {
            $query->where("a.id = " .(int)$keys); 
        }
        
        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();
        
        if(!empty($result)) {
            $this->bind($result);
        }
        
    }
    
    public function bind($data, $ignore = array()) {
        
        foreach($data as $key => $value) {
            
            if(!in_array($key, $ignore)) {
                $this->$key = $value;
            }            
            
        }
    }
    
    public function getCode() {
        return $this->code;
    }
    
    public function getCode4() {
        return $this->code4;
    }
    
    public function getName() {
        return $this->name;
    }
    
	public function getLatitue() {
        return $this->latitude;
    }

	public function getLongtitu() {
        return $this->longitude;
    }

	public function getCurrency() {
        return $this->currency;
    }

	public function getTimezone() {
        return $this->timezone;
    }

    
    
}
