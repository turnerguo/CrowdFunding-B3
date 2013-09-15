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

jimport('joomla.application.component.model');

class CrowdFundingModelPayments extends JModelLegacy {
    
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