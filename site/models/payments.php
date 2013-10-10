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

class CrowdFundingModelPayments extends JModel {
    
    /**
     * This method validates reward and update the number 
     * of distributet units, if it is limited.
     * 
     * @param integer $rewardId
     * @param integer $projectId
     * @param integer $amount
     * 
     * @return integer If there is something wrong, return reward ID 0.
     */
    public function updateRewardBankTransfer($rewardId, $projectId, $amount) {
    
        jimport("crowdfunding.reward");
        
        $keys = array(
            "id"         => (int)$rewardId,
            "project_id" => (int)$projectId
        );
        
        $reward = new CrowdFundingReward($rewardId);
        
        // Check for valid reward
        if(!$reward->getId()) {
            $rewardId = 0;
            return $rewardId;
        }
    
        // Check for valid amount between reward value and payed by user
        if($amount < $reward->getAmount()) {
            $rewardId = 0;
            return $rewardId;
        }
    
        // Verify the availability of rewards
        if($reward->isLimited() AND !$reward->getAvailable()) {
            $rewardId = 0;
            return $rewardId;
        }
    
        return $rewardId;
    }
    
}