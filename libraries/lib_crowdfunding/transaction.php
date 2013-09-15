<?php
/**
 * @package      CrowdFunding
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

JLoader::register("CrowdFundingTableTransaction", JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_crowdfunding".DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."transaction.php");

/**
 * This class provieds functionality that manage transactions.
 * 
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingTransaction extends CrowdFundingTableTransaction {
    
    public function __construct($id = 0) {
        
        $db = JFactory::getDbo();
        parent::__construct( $db );
        
        if(!empty($id)) {
            $this->load($id);
        }
    }
    
}
