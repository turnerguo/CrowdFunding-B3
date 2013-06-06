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

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');

class CrowdFundingModelUpdateItem extends JModelItem {
    
    protected $item;
    
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Update', $prefix = 'CrowdFundingTable', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }
    
    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState() {
        
        $app     = JFactory::getApplication();
        
        // Load the object state.
        $value = $app->input->getInt('id');
        $this->setState($this->getName().'.id', $value);
        
        $value = $app->input->getInt('project_id');
        $this->setState('project_id', $value);
        
        // Load the parameters.
        $params  = $app->getParams();
        $this->setState('params', $params);
    }
    
    /**
     * Method to get an ojbect.
     *
     * @param	integer	The id of the object to get.
     * @return	mixed	Object on success, false on failure.
     */
    public function getItem($id = null) {
        
        if (empty($id)) {
            $id = $this->getState($this->getName().'.id');
        }
        
        $storedId = $this->getStoreId($id);
        
        if (!isset($this->item[$storedId])) {
            $this->item[$storedId] = null;
            
            // Get a level row instance.
            $table = $this->getTable();
            $table->load($id);
            
            // Attempt to load the row.
            if ($table->id) {
                $this->item[$storedId] = $table;
            } 
        }
        
        return $this->item[$storedId];
    }
    
    public function remove($itemId, $userId = null) {
        
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query
            ->delete( $db->quoteName("#__crowdf_updates") )
            ->where("id = ". (int)$itemId);
            
        if(!empty($userId)) {
            $query->where("user_id=". (int)$userId);
        }
        
        $db->setQuery($query);
        $db->query();
            
    }
}