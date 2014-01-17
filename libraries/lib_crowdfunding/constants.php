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
 * CrowdFunding constants
 *
 * @package      CrowdFunding
 * @subpackage   Libraries
 */
class CrowdFundingConstants {
	
    // Payment Process
    const PAYMENT_PROCESS_CONTEXT = "payment_process_project";
    
    // States
    const PUBLISHED   = 1;
    const UNPUBLISHED = 2;
    const TRASHED     = -2;
    
    // Mail modes - html and plain text.
    const MAIL_MODE_HTML       = true;
    const MAIL_MODE_PLAIN_TEXT = false;
    
    // Logs
    const ENABLE_SYSTEM_LOG    = true;
    const DISABLE_SYSTEM_LOG   = false;
    
    // Project states
    const APPROVED = 1;
    const NOT_APPROVED = 0;
    
}