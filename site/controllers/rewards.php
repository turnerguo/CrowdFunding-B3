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

jimport('itprism.controller.admin');

/**
 * CrowdFunding rewards controller
 *
 * @package     CrowdFunding
 * @subpackage  Components
 */
class CrowdFundingControllerRewards extends ITPrismControllerAdmin {
    
	/**
     * Method to get a model object, loading it if required.
     *
     * @param	string	$name	The model name. Optional.
     * @param	string	$prefix	The class prefix. Optional.
     * @param	array	$config	Configuration array for model. Optional.
     *
     * @return	object	The model.
     * @since	1.5
     */
    public function getModel($name = 'Rewards', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function save() {
        
        // Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
 
        $userId = JFactory::getUser()->id;
        if(!$userId) {
            $redirectData = array(
                "force_direction"   => "index.php?option=com_users&view=login"
            );
            $this->displayNotice(JText::_("COM_CROWDFUNDING_ERROR_NOT_LOG_IN"), $redirectData);
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
		// Get the data from the form POST
		$data    = $app->input->post->get('rewards', array(), 'array');
        $itemId  = $app->input->post->get('id', 0, 'int');
        
        $redirectData = array(
            "view"   => "project",
            "layout" => "rewards",
            "id"     => $itemId
        );
        
        $model     = $this->getModel();
        /** @var $model CrowdFundingModelRewards **/
            
        try {
            
            $validData  = $model->validate($data, $itemId);
            $model->save($validData, $itemId);
            
        } catch (Exception $e) {
            
            // Problem with uploading, so set a message and redirect to pages
            $code = $e->getCode();
            switch($code) {
                
                case ITPrismErrors::CODE_WARNING:
                    $this->displayWarning($e->getMessage(), $redirectData);
                    return;
                break;
                
                default:
                    JLog::add($e->getMessage());
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'), ITPrismErrors::CODE_ERROR);
                break;
            }
            
        }
        
        // Redirect to next page
        $this->displayMessage(JText::_("COM_CROWDFUNDING_REWARDS_SUCCESSFULY_SAVED"), $redirectData);
    }

    
    /**
     * Method to change state of reward.
     * @return  void
     */
    public function changeState() {
    
        // Check for request forgeries.
        JSession::checkToken("get") or jexit(JText::_('JINVALID_TOKEN'));
    
        $userId = JFactory::getUser()->id;
        if(!$userId) {
            $redirectData = array(
                "force_direction"   => JRoute::_("index.php?option=com_users&view=login", false)
            );
            $this->displayNotice(JText::_("COM_CROWDFUNDING_ERROR_NOT_LOG_IN"), $redirectData);
            return;
        }
    
        $redirectData = array(
            "view"   => "transactions"
        );
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
    
        $txnId   = $app->input->get->getInt('txn_id');
        $state   = $app->input->get->get('state');
        
        $state   = (!$state) ? 0 : 1;
        
        if(!$txnId) {
            $this->displayWarning(JText::_("COM_CROWDFUNDING_ERROR_INVALID_TRANSACTION"), $redirectData);
            return;
        }
        
        $model     = $this->getModel();
        /** @var $model CrowdFundingModelRewards **/
    
        try {
    
            $model->changeState($txnId, $state, $userId);
    
        } catch (Exception $e) {
    
            // Problem with uploading, so set a message and redirect to pages
            $code = $e->getCode();
            switch($code) {
    
                case ITPrismErrors::CODE_WARNING:
                    $this->displayWarning($e->getMessage(), $redirectData);
                    return;
                    break;
    
                default:
                    JLog::add($e->getMessage());
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'), ITPrismErrors::CODE_ERROR);
                    break;
            }
    
        }
        
        if(!$state) {
            $msg = JText::_("COM_CROWDFUNDING_REWARD_HAS_BEEN_SET_AS_NOT_SENT");
        } else {
            $msg = JText::_("COM_CROWDFUNDING_REWARD_HAS_BEEN_SET_AS_SENT");
        }
    
        $this->displayMessage($msg, $redirectData);
        	
    }
    
}