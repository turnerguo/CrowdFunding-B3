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

jimport('joomla.application.component.modelform');

class CrowdFundingModelFunding extends CrowdFundingModelProject {
    
    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param	array	$data		An optional array of data for the form to interogate.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     * @return	JForm	A JForm object on success, false on failure
     * @since	1.6
     */
    public function getForm($data = array(), $loadData = true) {

        // Get the form.
        $form = $this->loadForm($this->option.'.funding', 'funding', array('control' => 'jform', 'load_data' => $loadData));
        /** @var $form JForm **/
        
        if (empty($form)) {
            return false;
        }
        
        // Prepare date format for the calendar.
        $dateFormat = CrowdFundingHelper::getDateFormat(true);
        $form->setFieldAttribute("funding_end", "format", $dateFormat);
        
        return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	1.6
     */
    protected function loadFormData() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
		$data	    = $app->getUserState($this->option.'.edit.funding.data', array());
		if(!$data) {
		    
		    $itemId = $this->getState($this->getName().'.id');
		    $userId = JFactory::getUser()->id;
		    
		    $data   = $this->getItem($itemId, $userId);
		    
		    // Prepare date format.
		    $dateFormat        = CrowdFundingHelper::getDateFormat();
		    
		    // Validate end date. If the date is not valid, generate a valid one.
		    if(!CrowdFundingHelper::isValidDate($data->funding_end)) {
		    
		        $today  = new JDate();
		    
		        // Get minimum days.
		        $params = $this->getState("params");
		        $minDays = $params->get("project_days_minimum", 30);
		    
		        // Generate end date.
		        $data->funding_end = CrowdFundingHelper::calcualteEndDate($today->format("Y-m-d"), $minDays);
		    }
		    
		    $date              = new JDate($data->funding_end);
		    $data->funding_end = $date->format($dateFormat);
		    
		}

		return $data;
    }
    
    /**
     * Method to save the form data.
     *
     * @param	array		The form data.
     * @return	mixed		The record id on success, null on failure.
     * @since	1.6
     */
    public function save($data) {
        
        $id             = JArrayHelper::getValue($data, "id");
        $goal           = JArrayHelper::getValue($data, "goal");
        $fundingType    = JArrayHelper::getValue($data, "funding_type");
        $fundingEnd     = JArrayHelper::getValue($data, "funding_end", "0000-00-00");
        $fundingDays    = JArrayHelper::getValue($data, "funding_days", 0);
        $durationType   = JArrayHelper::getValue($data, "funding_duration_type");
        
        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);
        
        $row->set("goal",          $goal);
        $row->set("funding_type",  $fundingType);
        
        $data = array(
            "duration_type" => $durationType,
            "funding_end"   => $fundingEnd,
            "funding_days"  => $fundingDays,
        );
        
        $this->prepareTable($row, $data);
        
        $row->store();
        
        return $row->id;
        
    }
    
	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable(&$table, $data) {
	    
	    $durationType = JArrayHelper::getValue($data, "duration_type");
	    $fundingEnd   = JArrayHelper::getValue($data, "funding_end");
	    $fundingDays  = JArrayHelper::getValue($data, "funding_days");
	    
	    $userId = JFactory::getUser()->id;
	    
		if (empty($table->id) OR ($userId != $table->user_id)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), ITPrismErrors::CODE_ERROR);
		}
		
		switch($durationType) {
		    
		    case "days":
		        
		        $table->funding_days = $fundingDays;
		        
		        // Clacluate end date
		        if(!empty($table->funding_start)) {
		            $table->funding_end   = CrowdFundingHelper::calcualteEndDate($table->funding_start, $table->funding_days);
		        } else {
		            $table->funding_end = "0000-00-00";
		        }
		        
		        break;
		        
		    case "date":
		        
		        if(!CrowdFundingHelper::isValidDate($fundingEnd)) {
		            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_DATE"), ITPrismErrors::CODE_WARNING);
		        } 
		        
		        jimport('joomla.utilities.date');
		        $date = new JDate($fundingEnd);
                
		        $table->funding_days = 0;
		        $table->funding_end  = $date->toSql();
                
		        break;
	        
		    default:
		        $table->funding_days = 0;
		        $table->funding_end  = "0000-00-00";
		        break;
		}
		
	}
	
	/**
	 * Valudate funding data
	 * @param array $data
	 * @param JRegistry $params
	 */
	public function validateFundingData($data, $params) {
	    
	    $goal          = JArrayHelper::getValue($data, "goal", 0, "float");
        $minAmount     = $params->get("project_amount_minimum", 100);
        $maxAmount     = $params->get("project_amount_maximum");
        
        $minDays       = (int)$params->get("project_days_minimum", 15);
        $maxDays       = (int)$params->get("project_days_maximum");
        
        $fundingType   = JArrayHelper::getValue($data, "funding_duration_type");
         
        // Verify minimum amount
        if($goal < $minAmount) {
            throw new Exception( JText::_('COM_CROWDFUNDING_ERROR_INVALID_GOAL'), ITPrismErrors::CODE_WARNING );
        }
        
        // Verify maximum amount
        if(!empty($maxAmount) AND ($goal > $maxAmount)) {
            throw new Exception( JText::_('COM_CROWDFUNDING_ERROR_INVALID_GOAL'), ITPrismErrors::CODE_WARNING );
        }
        
	    // Validate funding type "days"
	    if(strcmp("days", $fundingType) == 0) {
	        
	        $days = JArrayHelper::getValue($data, "funding_days", 0, "integer");
	        if($days < $minDays) {
	            throw new Exception( JText::_('COM_CROWDFUNDING_ERROR_INVALID_DAYS'), ITPrismErrors::CODE_WARNING );
	        }
	        
	        if(!empty($maxDays) AND ($days > $maxDays)) {
	            throw new Exception( JText::_('COM_CROWDFUNDING_ERROR_INVALID_DAYS'), ITPrismErrors::CODE_WARNING );
	        }
	        
	    } else { // Validate funding type "date"
	        
	        $fundingDate    = JArrayHelper::getValue($data, "funding_end");
	    
            if(!CrowdFundingHelper::isValidDate($fundingDate)) {
                throw new Exception( JText::_('COM_CROWDFUNDING_ERROR_INVALID_DATE'), ITPrismErrors::CODE_WARNING );
            }
            
            // Get item and check it for published
            $itemId = JArrayHelper::getValue($data, "id");
            $userId = JFactory::getUser()->id;
            $item   = $this->getItem($itemId, $userId);

            // Validate date if user want to edit date, while the project is published.
            if($item->published) {
                
                if(!CrowdFundingHelper::isValidPeriod($item->funding_start, $fundingDate, $minDays, $maxDays)) {
                    
                    if(!empty($maxDays)) {
                        throw new Exception(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_MAX_DAYS", $minDays, $maxDays), ITPrismErrors::CODE_WARNING);
                    } else {
                        throw new Exception(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_DAYS", $minDays), ITPrismErrors::CODE_WARNING);
                    }
                    
                }
                
            }
           
	    }
	    
	}
	
}