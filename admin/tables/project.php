<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die;

class CrowdFundingTableProject extends JTable {
    
    protected $fundedPercents = 0;
    protected $daysLeft       = 0;
    protected $slug           = "";
    protected $catslug        = "";
    
	public function __construct( $db ) {
        parent::__construct( '#__crowdf_projects', 'id', $db );
    }
    
    /**
     * Method to load a row from the database by primary key and bind the fields
     * to the JTable instance properties.
     *
     * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
     * set the instance property value is used.
     * @param   boolean  $reset  True to reset the default values before loading the new row.
     *
     * @return  boolean  True if successful. False if row not found or on error (internal error state set in that case).
     *
     * @link    http://docs.joomla.org/JTable/load
     * @since   11.1
     */
    public function load($keys = null, $reset = true) {
        
        if (empty($keys)) {
            
            // If empty, use the value of the current key
            $keyName = $this->_tbl_key;
            $keyValue = $this->$keyName;
        
            // If empty primary key there's is no need to load anything
            if (empty($keyValue)) {
                return true;
            }
        
            $keys = array($keyName => $keyValue);
        
        } elseif (!is_array($keys)) {
            
            // Load by primary key.
            $keys = array($this->_tbl_key => $keys);
            
        }
        
        if ($reset) {
            $this->reset();
        }
        
        // Initialise the query.
        $query = $this->_db->getQuery(true);
        $query->select('a.*');
        $query->select($query->concatenate(array("a.id", "a.alias"), "-") . " AS slug");
        $query->select($query->concatenate(array("b.id", "b.alias"), "-") . " AS catslug");
        $query->from($this->_tbl . " AS a");
        $query->innerJoin($this->_db->quoteName("#__categories") . " AS b ON a.catid = b.id");
        $fields = array_keys($this->getProperties());
        
        foreach ($keys as $field => $value) {
            
            // Check that $field is in the table.
            if (!in_array($field, $fields)) {
                $e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CLASS_IS_MISSING_FIELD', get_class($this), $field));
                $this->setError($e);
                return false;
            }
            // Add the search tuple to the query.
            $query->where("a.".$this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
        }
        
        $this->_db->setQuery($query);
        
        try {
            $row = $this->_db->loadAssoc();
        } catch (RuntimeException $e) {
            $je = new JException($e->getMessage());
            $this->setError($je);
            return false;
        }
        
        // Check that we have a result.
        if (empty($row)) {
            $e = new JException(JText::_('JLIB_DATABASE_ERROR_EMPTY_ROW_RETURNED'));
            $this->setError($e);
            return false;
        }
        
        // Bind the object with the row and return.
        $this->bind($row);
        
        // Calculate funded percents
        $this->fundedPercents = $this->calculatePercent();
                
        // Calcualte days left
        $this->daysLeft       = $this->calcualteDaysLeft();
        
        return true;
    }
    
    protected function calculatePercent() {
        
        $value = 0;
        if($this->goal > 0) {
            $value = ($this->funded/$this->goal) * 100;
        }
        
	    return round($value, 2);
	}
	
	protected function calcualteDaysLeft() {
        
        // Calcualte days left
        $today         = new DateTime("today");
        
        // Calcualte ending date
        if(!empty($this->funding_days)) {
            
            $fundindStart = new JDate($this->funding_start);
            
            // Validate starting date. 
            // If there is not starting date, set number of day.
            if(0 > $fundindStart->toUnix()) {
                return (int)$this->funding_days;
            }
            
            $endingDate  = new DateTime($this->funding_start);
            $endingDate->modify("+".(int)$this->funding_days." days");
            
        } else {
            $endingDate  = new DateTime($this->funding_end);
        }
        
        $interval        = $today->diff($endingDate);
        $daysLeft        = $interval->format("%r%a");
        
        // If number is less than zero,
        // initialize it with 0 
        if($daysLeft < 0 ) {
            $daysLeft = 0;
        }
        return $daysLeft;
    } 
    
	/**
     * @return the $fundedPercents
     */
    public function getFundedPercents() {
        return $this->fundedPercents;
    }

	/**
     * @return the $daysLeft
     */
    public function getDaysLeft() {
        return $this->daysLeft;
    }
    
    public function getSlug() {
        return $this->slug;
    }
    
    public function getCatSlug() {
        return $this->catslug;
    }
    
}