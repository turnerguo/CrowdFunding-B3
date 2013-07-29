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

class CrowdFundingModelProjectItem extends JModelItem {
    
    const     PROJECT_STATE_PUBLISHED = 1;
    
    protected $item = array();
    
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'CrowdFundingTable', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }
    
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState() {
        
        $app     = JFactory::getApplication();
        $params  = $app->getParams();
        
        // Load the object state.
        $id = $app->input->getInt('id');
        $this->setState('project.id', $id);
        
        // Load the parameters.
        $this->setState('params', $params);
    }
    
    /**
     * Method to get an ojbect.
     *
     * @param	integer	The id of the object to get.
     *
     * @return	mixed	Object on success, false on failure.
     */
    public function getItem($id = null) {
        
        if (empty($id)) {
            $id = $this->getState('project.id');
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

    /**
     * Publish or not an item. If state is going to be published,
     * we have to calculate end date.
     * 
     * @param integer $itemId
     * @param integer $state
     */
    public function saveState($itemId, $state) {
        
        $row   = $this->getItem($itemId);
        
        // Prepare data only if the user publish the project.
        if($state == CrowdFundingModelProjectItem::PROJECT_STATE_PUBLISHED) {
            $this->prepareTable($row);
        }
        
        $row->published = $state;
        $row->store();
        
        // Trigger the event
        
        $context = $this->option . '.project';
        $pks     = array($row->id);
        
        // Include the content plugins for the change of state event.
        JPluginHelper::importPlugin('content');
         
        // Trigger the onContentChangeState event.
        $dispatcher = JDispatcher::getInstance();
        $results    = $dispatcher->trigger("onContentChangeState", array($context, $pks, $state));
        
        if (in_array(false, $results, true)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_CHANGE_STATE"), ITPrismErrors::CODE_ERROR);
        }
        
    }
    
    protected function prepareTable(&$table) {
        
        $isValidStartDate = CrowdFundingHelper::isValidDate($table->funding_start);
        
        // Calculate starting date if the user publish a project for first time.
        if(!$isValidStartDate) {
            
            $fundindStart         = new JDate();
            $table->funding_start = $fundindStart->toSql();
            
        }
        
        // Get parameters
        $params    = JFactory::getApplication()->getParams();
        
        $minDays   = $params->get("project_days_minimum", 15);
        $maxDays   = $params->get("project_days_maximum");
        
        // Validate the period if there is an ending date
        $isValidEndDate = CrowdFundingHelper::isValidDate($table->funding_end);
        if($isValidEndDate) {
        
            if(!CrowdFundingHelper::isValidPeriod($table->funding_start, $table->funding_end, $minDays, $maxDays)) {
                
                if(!empty($maxDays)) {
                    throw new Exception(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_MAX_DAYS", $minDays, $maxDays), ITPrismErrors::CODE_WARNING);
                } else {
                    throw new Exception(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_DAYS", $minDays), ITPrismErrors::CODE_WARNING);
                }
            }
            
        }
        
    }
    
    /**
     * It does some validations to be sure about what the project is valid.
     * 
     * @param  object $item
     * @throws Exception
     */
    public function validate($item) {
        
        if(!$item->goal) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_GOAL"), ITPrismErrors::CODE_WARNING);
        }
        
        if(!$item->funding_type) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_FUNDING_TYPE"), ITPrismErrors::CODE_WARNING);
        }
        
        if(!CrowdFundingHelper::isValidDate($item->funding_end) AND !$item->funding_days) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_FUNDING_DURATION"), ITPrismErrors::CODE_WARNING);
        }
        
        if(!$item->pitch_image AND !$item->pitch_video) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PITCH_IMAGE_OR_VIDEO"), ITPrismErrors::CODE_WARNING);
        }
        
        $desc = JString::trim($item->description);
        if(!$desc) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_DESCRIPTION"), ITPrismErrors::CODE_WARNING);
        }
        
        if(!$this->countRewards($item->id)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REWARDS"), ITPrismErrors::CODE_WARNING);
        }
    }
    
    /**
     * This method counts the rewards of the project.
     * @param  integer $itemId    Project id
     * @return number
     */
    protected function countRewards($itemId) {
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query
            ->select("COUNT(*)")
            ->from($db->quoteName("#__crowdf_rewards"))
            ->where("project_id = ".(int)$itemId);
            
        $db->setQuery($query);
        $result = $db->loadResult();
        
        return (int)$result;
    }
}