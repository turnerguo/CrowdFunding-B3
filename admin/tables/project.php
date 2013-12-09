<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

class CrowdFundingTableProject extends JTable {
    
    protected $fundedPercents = 0;
    protected $daysLeft       = 0;
    protected $slug           = "";
    protected $catslug        = "";
    
	public function __construct( $db ) {
        parent::__construct('#__crowdf_projects', 'id', $db);
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
        $query->leftJoin($this->_db->quoteName("#__categories") . " AS b ON a.catid = b.id");
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
        $this->fundedPercents  = (!$this->goal) ? 0 : CrowdFundingHelper::calculatePercent($this->funded, $this->goal);
                
        // Calculate end date
        if(!empty($this->funding_days)) {
            $this->funding_end = (!CrowdFundingHelper::isValidDate($this->funding_start)) ? "0000-00-00" : CrowdFundingHelper::calcualteEndDate($this->funding_start, $this->funding_days);
        }
        
        // Calcualte days left
        $this->daysLeft        = CrowdFundingHelper::calcualteDaysLeft($this->funding_days, $this->funding_start, $this->funding_end);
        
        return true;
    }
    
	/**
     * @return the $fundedPercents
     */
    public function getFundedPercents() {
        return $this->fundedPercents;
    }
    
    public function setFundedPercents($percent) {
        $this->fundedPercents = $percent;
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