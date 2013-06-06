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
