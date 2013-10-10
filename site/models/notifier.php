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

class CrowdFundingModelNotifier extends JModel {
    
    /**
     * Send mail to administrator and notify him about new transaction.
     * 
     */
    public function sendMailToAdministrator() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
         // Send email to the administrator
        $subject = JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_PROCESS_SUBJECT");
        $body    = JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_PROCESS_BODY");
        $return  = JFactory::getMailer()->sendMail($app->getCfg("mailfrom"), $app->getCfg("fromname"), $app->getCfg("mailfrom"), $subject, $body);
		
		// Check for an error.
		if ($return !== true) {
		    $error = JText::sprintf("COM_CROWDFUNDING_ERROR_MAIL_SENDING_ADMIN");
			JLog::add($error);
		}
        
    }
    
}