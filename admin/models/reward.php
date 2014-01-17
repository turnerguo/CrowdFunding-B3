<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class CrowdFundingModelReward extends JModelAdmin {
    
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
     * Method to get the record form.
     *
     * @param   array   $data       An optional array of data for the form to interogate.
     * @param   boolean $loadData   True if the form is to load its own data (default case), false if not.
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true){
        
        // Get the form.
        $form = $this->loadForm($this->option.'.reward', 'reward', array('control' => 'jform', 'load_data' => $loadData));
        if(empty($form)){
            return false;
        }
        
        return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData(){
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.reward.data', array());
        if(empty($data)){
            $data = $this->getItem();
        }
        
        return $data;
    }
    
    /**
     * Save data into the DB
     * 
     * @param $data   The data about item
     * @return     Item ID
     */
    public function save($data){
        
        $id           = JArrayHelper::getValue($data, "id");
        $title        = JArrayHelper::getValue($data, "title");
        $description  = JArrayHelper::getValue($data, "description");
        $amount       = JArrayHelper::getValue($data, "amount");
        $number       = JArrayHelper::getValue($data, "number");
        $distributed  = JArrayHelper::getValue($data, "distributed");
        $delivery     = JArrayHelper::getValue($data, "delivery");
        $shipping     = JArrayHelper::getValue($data, "shipping");
        $published    = JArrayHelper::getValue($data, "published");
        $projectId    = JArrayHelper::getValue($data, "project_id");
        
        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);
        
        $row->set("title",          $title);
        $row->set("description",    $description);
        $row->set("amount",         $amount);
        $row->set("number",         $number);
        $row->set("distributed",    $distributed);
        $row->set("delivery",       $delivery);
        $row->set("shipping",       $shipping);
        $row->set("published",      $published);
        $row->set("project_id",     $projectId);
        
        $row->store(true);
        
        return $row->id;
    
    }
    
}