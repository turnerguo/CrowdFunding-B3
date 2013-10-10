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

jimport('joomla.application.component.model');

class CrowdFundingModelRewards extends JModel {
    
	/**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Reward', $prefix = 'CrowdFundingTable', $config = array()){
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
        
        parent::populateState();
        
        $app = JFactory::getApplication("Site");
        /** @var $app JSite **/
        
		// Get the pk of the record from the request.
		$value     = $app->input->getInt("id");
		$this->setState($this->getName().'.id', $value);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
		
    }
    
    public function getItems($projectId) {
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query
            ->select("a.id, a.amount, a.title, a.description, a.number, a.distributed, a.delivery")
            ->from($db->quoteName("#__crowdf_rewards") . " AS a")
            ->where("a.project_id = ". (int)$projectId)
            ->where("a.published = 1");
        
        $db->setQuery($query);
        return $db->loadAssocList();
        
    }
    
    public function validate($data, $projectId) {
        
        if(empty($data)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REWARDS"), ITPrismErrors::CODE_WARNING);
        }
        
        $filter = JFilterInput::getInstance();
        
        foreach($data as $key => $item) {
            
            // Filter data
            if(!is_numeric($item["amount"])) {
                $item["amount"] = 0;
            }

            $item["title"]       = $filter->clean($item["title"], "string");
            $item["title"]       = JString::trim($item["title"]);
            $item["title"]       = JString::substr($item["title"], 0, 128);

            $item["description"] = $filter->clean($item["description"], "string");
            $item["description"] = JString::trim($item["description"]);
            $item["description"] = JString::substr($item["description"], 0, 500);
            
            $item["number"]      = (int)$item["number"];
            
            $item["delivery"]    = JString::trim($item["delivery"]);
            $item["delivery"]    = $filter->clean($item["delivery"], "string");
            
            if(!empty($item["delivery"])) {
                $date     = new JDate($item["delivery"]);
                $unixTime = $date->toUnix();
                if($unixTime < 0) {
                    $item["delivery"] = "";
                }
            }
            
            if(!$item["title"]) {
                throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_TITLE"), ITPrismErrors::CODE_WARNING);
            }
            
            if(!$item["description"]) {
                throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_DESCRIPTION"), ITPrismErrors::CODE_WARNING);
            }
            
            if(!$item["amount"]) {
                throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"), ITPrismErrors::CODE_WARNING);
            }
            
            $data[$key] = $item;
        }
        
        return $data;
    }
    
    /**
     * Method to save the form data.
     *
     * @return	mixed		The record id on success, null on failure.
     * @since	1.6
     */
    public function save($data, $projectId) {
        
        foreach($data as $item) {
            
            // Load a record from the database
            $row    = $this->getTable();
            $itemId = JArrayHelper::getValue($item, "id");
            if($itemId) {
                $row->load($itemId);
            }
            
            $amount         = JArrayHelper::getValue($item, "amount");
            $title          = JArrayHelper::getValue($item, "title");
            $description    = JArrayHelper::getValue($item, "description");
            $number         = JArrayHelper::getValue($item, "number");
            $delivery       = JArrayHelper::getValue($item, "delivery");
        
            $row->set("amount",      $amount);
            $row->set("title",       $title);
            $row->set("description", $description);
            $row->set("number",       $number);
            $row->set("delivery",    $delivery);
            $row->set("project_id",  $projectId);
            
            $row->store();
            
        }
        
    }
    
    public function remove($rewardId, $userId) {
    
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
    
        $query
            ->delete()
            ->from("#__crowdf_rewards USING #__crowdf_rewards")
            ->innerJoin("#__crowdf_projects ON #__crowdf_rewards.project_id = #__crowdf_projects.id")
            ->where("#__crowdf_rewards.id = ". (int)$rewardId)
            ->where("(#__crowdf_projects.user_id = ". (int)$userId .")");
        
        $db->setQuery($query);
        $db->query();
    
    }
    
    /**
     * Set the reward as trashed, if user want to remove it 
     * but it is part of transaction.
     *
     * @param $rewardId integer
     * @param $userId integer
     * 
     *
     * @todo move it in other model or class. It have to be part of item object.
     */
    public function trash($rewardId, $userId) {
    
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
    
        // Validate reward
        $query
            ->select("a.id")
            ->from($db->quoteName("#__crowdf_rewards"). " AS a")
            ->innerJoin($db->quoteName("#__crowdf_projects"). " AS b ON a.project_id = b.id")
            ->where("a.id = ". (int)$rewardId)
            ->where("b.user_id = ".(int)$userId);
        
        $db->setQuery($query, 0, 1);
        $rewardId = $db->loadResult();
        
        if(!empty($rewardId)) {
            
            $query = $db->getQuery(true);
            
            $query
                ->update($db->quoteName("#__crowdf_rewards"))
                ->set($db->quoteName("published") ."=". $db->quote("-2"))
                ->where($db->quoteName("id") ."=". (int)$rewardId );
            
            $db->setQuery($query);
            $db->query();
            
        }
    
    }

    /**
     * This method check for selected reward from user.
     * It checks, if the reward is part of transactions.
     * 
     * @param $rewardId integer
     * @return bool
     * 
     * @todo move it in other model or class. It have to be part of item object.
     */
    public function isSelectedByUser($rewardId) {
        
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        // Validate reward
        $query
            ->select("COUNT(*)")
            ->from($db->quoteName("#__crowdf_transactions"). " AS a")
            ->where("a.reward_id = ". (int)$rewardId);
        
        $db->setQuery($query, 0, 1);
        $number = $db->loadResult();
        
        return (!$number) ? false : true;
    }
    
    /**
     * Set a state as SENT or NOT SENT in the transaction table
     * 
     * @param integer $txnId
     * @param itneger $state
     * @param itneger $userId
     * 
     * @todo move it in other model or class. It have to be part of item object.
     */
    public function changeState($txnId, $state, $userId) {
    
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
            
        $query
            ->update("#__crowdf_transactions")
            ->set("reward_state = ".(int)$state)
            ->where("id = ".(int)$txnId)
            ->where("receiver_id = " .(int)$userId);
    
        $db->setQuery($query);
        $db->query();
    
    }
    
}