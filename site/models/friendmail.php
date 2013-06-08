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

jimport('joomla.application.component.modelform');

class CrowdFundingModelFriendMail extends JModelForm {
    
    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @since	1.6
     */
    protected function populateState() {
    
        parent::populateState();
    
        $app = JFactory::getApplication("Site");
        /** @var $app JSite **/
    
        // Get the pk of the record from the request.
        $value = $app->input->getInt("id");
        $this->setState($this->getName() . '.id', $value);
    
    }
    
    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param	array	$data		An optional array of data for the form to interogate.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     * @return	JForm	A JForm object on success, false on failure
     * @since	1.6
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm($this->option.'.friendmail', 'friendmail', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        
        $form->bind($data);
        
        return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	1.6
     */
    protected function loadFormData() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
		$data	    = $app->getUserState($this->option.'.edit.friendmail.data', array());

		return $data;
    }
    
    /**
     * Method to send mail to friend.
     *
     * @param	array		The form data.
     */
    public function send($data) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Send email to the administrator
        $subject     = JArrayHelper::getValue($data, "subject");
        $body        = JArrayHelper::getValue($data, "message");
        $from        = JArrayHelper::getValue($data, "sender");
        $fromName    = JArrayHelper::getValue($data, "sender_name");
        $recipient   = JArrayHelper::getValue($data, "receiver");
        
        $return  = JFactory::getMailer()->sendMail($from, $fromName, $app->getCfg("mailfrom"), $subject, $body);
        
        // Check for an error.
        if ($return !== true) {
            $error = JText::sprintf("COM_CROWDFUNDING_ERROR_MAIL_SENDING_FRIEND");
            JLog::add($error);
        }
        
        
    }
    
    
}