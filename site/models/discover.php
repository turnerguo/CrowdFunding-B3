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

jimport('joomla.application.component.modellist');

class CrowdFundingModelDiscover extends JModelList {
    
    protected $items   = null;
    protected $numbers = null;
    protected $params  = null;
    
    /**
     * Constructor.
     *
     * @param   array   An optional associative array of configuration settings.
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array()){
        
        if(empty($config['filter_fields'])){
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'ordering', 'a.ordering'
            );
        }
        
        parent::__construct($config);
    }
    
	/**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     * @since   1.6
     */
    protected function populateState($ordering = 'ordering', $direction = 'ASC'){
        
        parent::populateState("a.ordering", "ASC");
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Load parameters
        $params     =  $app->getParams();
        $this->setState('params', $params);
        
        // Set limit
        $value      = $app->input->get("id", 0, "uint");
        $this->setState($this->context.'.category_id', $value);
        
        // Set limit
        $value      = $params->get("projects_limit", $app->getCfg('list_limit', 20));
        $this->setState('list.limit', $value);
        
        $value      = $app->input->getInt('limitstart', 0);
        $this->setState('list.start', $value);
        
    }
    
	/**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string      $id A prefix for the store id.
     * @return  string      A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '') {
        
        // Compile the store id.
        $id.= ':' . $this->getState($this->context.'.category_id');

        return parent::getStoreId($id);
    }
    
   /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery() {
        
        $db     = $this->getDbo();
        /** @var $db JDatabaseMySQLi **/
        
        // Create a new query object.
        $query  = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.short_desc, a.image, a.user_id, a.catid, ' .
                'a.goal, a.funded, a.funding_start, a.funding_end, a.funding_days, a.funding_type, ' .
                $query->concatenate(array("a.id", "a.alias"), "-") . ' AS slug, ' .
                'b.name AS user_name, ' .
                $query->concatenate(array("c.id", "c.alias"), "-") . " AS catslug"
            )
        );
        $query->from($db->quoteName('#__crowdf_projects').' AS a');
        $query->innerJoin($db->quoteName('#__users').' AS b ON a.user_id = b.id');
        $query->innerJoin($db->quoteName('#__categories').' AS c ON a.catid = c.id');

        // Filter by category ID
        $categoryId = $this->getState($this->context.".category_id", 0);
        if(!empty($categoryId)) {
            $query->where('a.catid = '.(int)$categoryId);
        }
        
        // Filter by state
        $query->where('a.published = 1');
        $query->where('a.approved = 1');

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }
    
    protected function getOrderString() {
        
        $params     = $this->getState("params");
        $order      = $params->get("discover_order", "start_date");
        $direction  = $params->get("discover_dirn", "desc");
        
        $allowedDirns = array("asc", "desc");
        if(!in_array($direction, $allowedDirns)) {
            $direction = "ASC";
        } else {
            $direction = JString::strtoupper( $direction );
        }
        
        switch($order) {
            
            case "ordering":
                $orderCol  = "a.ordering";
                break;
                
            case "added":
                $orderCol  = "a.id";
                break;
                
            default: // Start date
                $orderCol  = "a.funding_start";
                break;
        }
        
        $orderDirn  = $direction;
        
        return $orderCol.' '.$orderDirn;
    }
    
    public function prepareItems($items) {
        
        $result = array();
        $i      = 0;
        $x      = 1;
        
        if(!empty($items)) {
            foreach($items as $item) {
                
                $result[$i][$x] = $item;
                
                // Calculate funded
                $result[$i][$x]->funded_percents = CrowdFundingHelper::calculatePercent($item->funded, $item->goal);
                
                // Calcualte days left
                $result[$i][$x]->days_left       = CrowdFundingHelper::calcualteDaysLeft($item->funding_days, $item->funding_start, $item->funding_end);
                
                // Increase indexes
                if($x == 3) {
                    $x = 0;
                    $i++;
                }
                $x++;
            }
        }
        
        return $result;
    }
    
    
}