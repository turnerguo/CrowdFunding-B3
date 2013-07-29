<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
* CrowdFunding is free software. This vpversion may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

defined('JPATH_PLATFORM') or die;

/**
 * This class provieds functionality that manage rewards.
 */
class CrowdFundingRewards extends ArrayObject {
    
    protected $db;
    
    protected static $instances = array();
    
    /**
     * Load or set rewards. 
     * 
     * @param integer   $id      Project ID
     * @param array     $rewards Rewards
     */
    public function __construct($id = 0) {
        
        $this->db = JFactory::getDbo();
        
        $rewards  = array();
        if(!empty($id)) {
            $rewards = $this->load($id);
        }
        
        parent::__construct($rewards);
    }

    public static function getInstance($id)  {
    
        if (empty(self::$instances[$id])){
            $item = new CrowdFundingRewards($id);
            self::$instances[$id] = $item;
        }
        
        return self::$instances[$id];
    }
      
    
    public function load($id) {
        
        $results = array();
        
        $query = $this->db->getQuery(true);
        
        $query
            ->select("a.id, a.title, a.description, a.amount")
            ->from($this->db->quoteName("#__crowdf_rewards") . " AS a")
            ->where("a.project_id = " .(int)$id);
        
        $this->db->setQuery($query);
        $results = $this->db->loadObjectList();
        
        if(!$results) {
            $results = array();
        }
        
        return $results;
    }
}
