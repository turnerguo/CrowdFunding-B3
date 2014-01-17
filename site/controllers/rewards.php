<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
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
            $redirectOptions = array(
                "force_direction"   => "index.php?option=com_users&view=login"
            );
            $this->displayNotice(JText::_("COM_CROWDFUNDING_ERROR_NOT_LOG_IN"), $redirectOptions);
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
		// Get the data from the form POST
		$data       = $app->input->post->get('rewards', array(), 'array');
        $projectId  = $app->input->post->get('id', 0, 'int');
        
        $images     = $app->input->files->get('images', array(), 'array');
        
        $userId     = JFactory::getUser()->id;
        
        $redirectOptions = array(
            "view"   => "project",
            "layout" => "rewards",
            "id"     => $projectId
        );
        
        jimport("crowdfunding.authorizer");
        $auth = new CrowdFundingAuthorizer(JFactory::getDbo(), $userId);
        if(!$auth->authorizeProject($projectId)) {
            $this->displayError(JText::_("COM_CROWDFUNDING_INVALID_PROJECT"), $redirectOptions);
            return;
        }
        
        $model     = $this->getModel();
        /** @var $model CrowdFundingModelRewards **/
            
        try {
            
            $validData  = $model->validate($data);
            $rawardsIds = $model->save($validData, $projectId);
            
            $params        = JComponentHelper::getParams("com_crowdfunding");
            $imagesAllowed = $params->get("rewards_images", 0);
            
            // Upload images.
            if($imagesAllowed AND !empty($images) AND !empty($rawardsIds)) {
                
                // Get the folder where the images will be stored
                $imagesFolder    = CrowdFundingHelper::getImagesFolder($userId);
                
                jimport("joomla.filesystem.folder");
                if(!JFolder::exists($imagesFolder)) {
                    JFolder::create($imagesFolder);
                }
                
                $images = $model->uploadImages($images, $imagesFolder, $userId, $rawardsIds);
                
                if(!empty($images)) {
                    $model->storeImages($images, $imagesFolder);
                }
            }
            
        } catch (InvalidArgumentException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
        
        // Redirect to next page
        $this->displayMessage(JText::_("COM_CROWDFUNDING_REWARDS_SUCCESSFULY_SAVED"), $redirectOptions);
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
            $redirectOptions = array(
                "force_direction"   => JRoute::_("index.php?option=com_users&view=login", false)
            );
            $this->displayNotice(JText::_("COM_CROWDFUNDING_ERROR_NOT_LOG_IN"), $redirectOptions);
            return;
        }
    
        $redirectOptions = array(
            "view"   => "transactions"
        );
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
    
        $txnId   = $app->input->get->getInt('txn_id');
        $state   = $app->input->get->get('state');
        
        $state   = (!$state) ? 0 : 1;
        
        if(!$txnId) {
            $this->displayWarning(JText::_("COM_CROWDFUNDING_ERROR_INVALID_TRANSACTION"), $redirectOptions);
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
                    $this->displayWarning($e->getMessage(), $redirectOptions);
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
    
        $this->displayMessage($msg, $redirectOptions);
        	
    }
    
}