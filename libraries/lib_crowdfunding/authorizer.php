<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for authorizing system objects.
 *
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingAuthorizer {
    
    protected $userId;
    
    /**
     * Database driver.
     * 
     * @var JDatabase
     */
    protected $db;
    
    public function __construct(JDatabase $db, $userId) {
        $this->db     = $db;
        $this->userId = (int)$userId;
    }
    
    public function authorizeProject($projectId) {
        
        $query = $this->db->getQuery(true);
        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id       = ".(int)$projectId)
            ->where("a.user_id  = ".(int)$this->userId);
        
        $this->db->setQuery($query);
        $result = $this->db->loadResult();
        
        return (bool)$result;
        
    }
    
    public function authorizeReward($rewardId) {
    
        $query = $this->db->getQuery(true);
        $query
            ->select("b.user_id")
            ->from($this->db->quoteName("#__crowdf_rewards", "a"))
            ->innerJoin($this->db->quoteName("#__crowdf_projects", "b") . " ON a.project_id = b.id")
            ->where("a.id = ".(int)$rewardId);
    
        $this->db->setQuery($query);
        $userId = $this->db->loadResult();
    
        return (bool)($this->userId == $userId);
    
    }
    
}
