<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

jimport("crowdfunding.statistics.project");

/**
 * This class loads statistics about transactions.
 */
class CrowdFundingStatisticsDatasetProject extends CrowdFundingStatisticsProject {
    
    public function getFullPeriodAmounts() {
        
        $query = $this->db->getQuery(true);
        $query->select("a.funding_start, a.funding_end")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id = ".(int)$this->id);
        
        $this->db->setQuery($query);
        $result = $this->db->loadObject();
        
        if(!CrowdFundingHelper::isValidDate($result->funding_start) OR !CrowdFundingHelper::isValidDate($result->funding_end)) {
            return array();
        }
        
        jimport("itprism.date");
        $date   = new ITPrismDate();
        $date1  = new ITPrismDate($result->funding_start);
        $date2  = new ITPrismDate($result->funding_end);
        
        $period = $date->getDaysPeriod($date1, $date2);
        
        $query = $this->db->getQuery(true);
        $query
            ->select("a.txn_date as date, SUM(a.txn_amount) as amount")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.project_id = ".(int)$this->id)
            ->group("DATE(a.txn_date)");
        
        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();
        
        if(!$results) {
            $results = array();
        }
        
        // Prepare data
        $data = array();
        foreach($results as $result) {
            $date = new JDate($result["date"]);
            $index = $date->format("d.m");
            $data[$index] = $result;
        }
        
        $dataset = array();
        foreach($period as $day) {
            $dayMonth = $day->format("d.m");
            if(isset($data[$dayMonth])) {
                $amount   = $data[$dayMonth]["amount"];
            } else {
                $amount = 0;
            }
            
            $dataset[] = array("date" => $dayMonth, "amount" => $amount);
        }

        return $dataset;
    }
    
    public function getFundedAmount() {
    
        $query = $this->db->getQuery(true);
        $query
            ->select("a.funded, a.goal")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id = ".(int)$this->id);
    
        $this->db->setQuery($query);
        $result = $this->db->loadObject();
    
        if(empty($result->funded) OR empty($result->goal)) {
            return array();
        }
    
        $dataset = array();
        
        $dataset["goal"] = array(
            "label"  => JText::_("LIB_CROWDFUNDING_GOAL"),
            "amount" => (float)$result->goal
        );
        
        $dataset["funded"] = array(
            "label"  => JText::_("LIB_CROWDFUNDING_FUNDED"),
            "amount" => (float)$result->funded
        );
        
        $remaining = (float)($result->goal - $result->funded);
        if($remaining < 0) {
            $remaining = 0;
        }
        
        $dataset["remaining"] = array(
            "label"  => JText::_("LIB_CROWDFUNDING_REMAINING"),
            "amount" => $remaining
        );
        
        return $dataset;
    }
}
