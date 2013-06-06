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

/**
 * Get a list of items
 * 
 * @author Todor Iliev
 */
class CrowdFundingModelTransactions extends JModelList {
    
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
            	'amount', 'a.txn_amount',
                'service_provider', 'a.service_provider',
            	'name', 'b.name',
                'title', 'c.title'
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
        
        // Load the filter state.
        $value = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $value);

        // Load the component parameters.
        $params = JComponentHelper::getParams($this->option);
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.txn_date', 'asc');
        
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
        $id.= ':' . $this->getState('filter.search');

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
                'a.id, a.txn_amount, a.txn_date, a.txn_currency, a.txn_id, a.txn_status, ' .
                'a.project_id, a.reward_id, a.receiver_id, a.service_provider, '.
                'b.name AS beneficiary, '.
                'c.title AS project, ' .
                'd.title AS reward, '.
                'e.name AS sender '
            )
        );
        $query->from($db->quoteName('#__crowdf_transactions').' AS a');
        $query->innerJoin($db->quoteName('#__users').' AS b ON a.receiver_id = b.id');
        $query->innerJoin($db->quoteName('#__crowdf_projects').' AS c ON a.project_id = c.id');
        $query->leftJoin($db->quoteName('#__crowdf_rewards').' AS d ON a.reward_id = d.id');
        
        $query->innerJoin($db->quoteName('#__users').' AS e ON a.investor_id = e.id');

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = '.(int) substr($search, 3));
            } else if(stripos($search, 'bid:') === 0) {
                $query->where('a.receiver_id = '.(int) substr($search, 4));
            } else if(stripos($search, 'pid:') === 0) {
                $query->where('a.project_id = '.(int) substr($search, 4));
            } else {
                $escaped = $db->escape($search, true);
                $quoted  = $db->quote("%" . $escaped . "%", false);
                $query->where('b.name LIKE '.$quoted);
            }
        }

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }
    
    protected function getOrderString() {
        
        $orderCol   = $this->getState('list.ordering');
        $orderDirn  = $this->getState('list.direction');
        
        return $orderCol.' '.$orderDirn;
    }
}