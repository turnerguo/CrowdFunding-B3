<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');

class CrowdFundingModelEmbed extends JModelItem {
    
    protected $item;
    /**
	 * Model context string.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $context = 'com_crowdfunding.embed';
    
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
                	"a.id, a.title, a.short_desc, a.image,  " .
                	"a.funded, a.goal, a.user_id, " .
                	"a.funding_start, a.funding_end, a.funding_days,  " . 
                	$query->concatenate(array("a.id", "a.alias"), "-") . ' AS slug, ' .
                	"b.name AS user_name, " .
                	$query->concatenate(array("c.id", "c.alias"), "-") . ' AS catslug '
            	)
                ->from("#__crowdf_projects AS a")
                ->innerJoin('#__users AS b ON a.user_id = b.id')
                ->innerJoin('#__categories AS c ON a.catid = c.id')
                ->where("a.id = " .(int)$id)
                ->where("a.published = 1")
                ->where("a.approved  = 1");

            $db->setQuery($query, 0, 1);
            $result = $db->loadObject();
            
            // Attempt to load the row.
            if (!empty($result)) {
                $result->funded_percents = CrowdFundingHelper::calculatePercent($result->funded, $result->goal);
                $result->days_left       = CrowdFundingHelper::calcualteDaysLeft($result->funding_days, $result->funding_start, $result->funding_end);
                $this->item[$storedId]   = $result;
            } 
        }
        
        return $this->item[$storedId];
    }
    
}