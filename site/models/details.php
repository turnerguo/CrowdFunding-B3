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

class CrowdFundingModelDetails extends JModelItem {
    
    protected $item;
    /**
	 * Model context string.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $context = 'com_crowdfunding.details';
    
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
     * @todo replace $_context with $context
     */
    protected function populateState() {
        
        $app     = JFactory::getApplication();
        $params  = $app->getParams();
        
        // Load the object state.
        $id = $app->input->getInt('id');
        $this->setState($this->context . '.id', $id);
        
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
            $id = $this->getState($this->context.'.id');
        }
        $storedId = $this->getStoreId($id);
        
        if (!isset($this->item[$storedId])) {
            $this->item[$storedId] = null;
            
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            
            $query
                ->select(
                	"a.id, a.title, a.short_desc, a.description, a.image, a.location, " .
                	"a.funded, a.goal, a.pitch_video, a.pitch_image, " .
                	"a.funding_start, a.funding_end, a.funding_days, a.funding_type,  " .
                	"a.catid, a.user_id, a.published, a.approved, a.hits, " .
                	$query->concatenate(array("a.id", "a.alias"), "-") . ' AS slug, ' .
                	$query->concatenate(array("b.id", "a.alias"), "-") . ' AS catslug' 
                )
                ->from($db->quoteName("#__crowdf_projects") . " AS a")
                ->innerJoin($db->quoteName("#__categories") . " AS b ON a.catid = b.id")
                ->where("a.id = " .(int)$id);

            $db->setQuery($query, 0, 1);
            $result = $db->loadObject();
            
            // Attempt to load the row.
            if (!empty($result)) {
                
                // Calculate end date
                if(!empty($result->funding_days)) {
                    $result->funding_end = (!CrowdFundingHelper::isValidDate($result->funding_start)) ? "0000-00-00" : CrowdFundingHelper::calcualteEndDate($result->funding_days, $result->funding_start); 
                }
                
                $result->funded_percents = CrowdFundingHelper::calculatePercent($result->funded, $result->goal);
                $result->days_left       = CrowdFundingHelper::calcualteDaysLeft($result->funding_days, $result->funding_start, $result->funding_end);
                
                $this->item[$storedId]   = $result;
                
            } 
        }
        
        return $this->item[$storedId];
    }

    /**
     * 
     * Load all rewards of a project
     * @param integer $id project ID
     * 
     * @deprecated v1.2
     */
    public function getRewards($id = null) {
        
        if (empty($id)) {
            $id = $this->getState($this->context.'.id');
        }
        
        $results = array();
        
        if (!empty($id)) {
            
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            
            $query
                ->select("id, title, description, amount")
                ->from("#__crowdf_rewards")
                ->where("project_id = " .(int)$id);

            $db->setQuery($query);
            $results = $db->loadObjectList();
            
        }
        
        return $results;
    }
    
    /**
     * Check for valid owner.
     * If the project is not published and not approved, 
     * only the owner will be able to view the project.
     * 
     * @param object $item
     * @param integer $userId
     * @return boolean
     */
    public function isRestricted($item, $userId) {
        
        if(empty($item->id) OR empty($item->user_id)) {
            return true;
        }
        
        // Check for the owner of the project.
        // If it is not published and not approved, only the owner will be able to view the project.
        if((!$item->published OR !$item->approved) AND ($item->user_id != $userId)) {
            return true;
        }
        
        return false;
        
    }
    
    /**
     * Increase number of hits.
     * 
     * @param integer $id
     */
    public function hit($id) {
        
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $query
            ->update($db->quoteName("#__crowdf_projects"))
            ->set($db->quoteName("hits") ." = hits + 1")
            ->where($db->quoteName("id") ."=". (int)$id);
        
        $db->setQuery($query);
        $db->query();
        
    }
}