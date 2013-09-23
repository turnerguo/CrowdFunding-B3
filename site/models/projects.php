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
defined( '_JEXEC' ) or die;

jimport( 'joomla.application.component.modellist' );

/**
 * Get a list of items
 * 
 * @author Todor Iliev
 */
class CrowdFundingModelProjects extends JModelList {
    
	 /**
     * Constructor.
     *
     * @param   array   An optional associative array of configuration settings.
     * @see     JController
     * @since   1.6
     */
    public function  __construct($config = array()) {
        
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
            	'goal', 'a.goal',
            	'funded', 'a.funded',
            	'funding_start', 'a.funding_start',
            	'funding_end', 'a.funding_end',
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
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null) {
        
        $app       = JFactory::getApplication();
        /** @var $app JSite **/

        $state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $state);
        
        $value = JFactory::getUser()->id;
        $this->setState('filter.user_id', $value);
         
        // Load the component parameters.
        $params = $app->getParams($this->option);
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.title', 'asc');
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
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.user_id');

        return parent::getStoreId($id);
    }
    
   /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery() {
        
        // Create a new query object.
        $db     = $this->getDbo();
        /** @var $db JDatabaseMySQLi **/
        $query  = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.image_square, a.goal, a.funded, '.
            	'a.funding_end, a.funding_days, a.funding_start, '.
                $query->concatenate(array("a.id", "a.alias"), "-") . ' AS slug, ' .
            	'a.published, a.approved, ' .
                'b.published AS catstate, ' .
            	$query->concatenate(array("b.id", "b.alias"), "-") . " AS catslug"
            )
        );
        $query->from($db->quoteName('#__crowdf_projects', "a"));
        $query->leftJoin($db->quoteName('#__categories', "b").' ON a.catid = b.id');

        // Filter by state
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('a.published = '.(int) $state);
        } else if ($state === '') {
            $query->where('(a.published IN (0, 1))');
        }

        $userId = $this->getState('filter.user_id');
        $query->where('a.user_id='.(int)$userId);
        
        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }
    
    protected function getOrderString() {
        
        $orderCol   = $this->getState('list.ordering');
        $orderDirn  = $this->getState('list.direction');
        
        return 'a.published DESC,'.$orderCol.' '.$orderDirn;
    }
}