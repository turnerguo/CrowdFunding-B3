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

class CrowdFundingModelNotifier extends JModelLegacy {
    
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