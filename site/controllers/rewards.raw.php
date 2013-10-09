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

jimport( 'joomla.application.component.controller' );

/**
 * CrowdFunding rewards controller
 *
 * @package     CrowdFunding
 * @subpackage  Components
 */
class CrowdFundingControllerRewards extends JControllerLegacy {
    
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
    
    /**
	 * Method to remove records via AJAX.
	 * @return  void
	 */
    public function remove() {
	    
	    // Get the input
		$app     = JFactory::getApplication();
		$pks     = $app->input->post->get('rid', array(), 'array');
        $userId  = JFactory::getUser()->id;
        
        // Sanitize the input
		JArrayHelper::toInteger($pks);
		
		// Validate user
        if(!$userId) {
            $response = array(
        		"success" => false,
            	"title"=> JText::_( 'COM_CROWDFUNDING_FAIL' ),
            	"text" => JText::_( 'COM_CROWDFUNDING_ERROR_NOT_LOG_IN' )
            );
            
            echo json_encode($response);
            JFactory::getApplication()->close();
        }
        
        // Validate primary keys
        if(!$pks) {
            $response = array(
        		"success" => false,
            	"title"=> JText::_( 'COM_CROWDFUNDING_FAIL' ),
            	"text" => JText::_( 'COM_CROWDFUNDING_ERROR_INVALID_REWARDS_SELECTED' )
            );
            
            echo json_encode($response);
            JFactory::getApplication()->close();
        }
        
		// Get the model
		$model = $this->getModel();

        try {
            
            $rewardId = JArrayHelper::getValue($pks, 0);
            
            // If the reward is part of transaction,
            // move set it as trashed.
            if($model->isSelectedByUser($rewardId)) {
                $model->trash($rewardId, $userId);
            } else {
                $model->remove($rewardId, $userId);
            }
            
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
        
        $response = array(
        	"success" => true,
            "title"=> JText::_( 'COM_CROWDFUNDING_SUCCESS' ),
        	"text" => JText::_( 'COM_CROWDFUNDING_REWARD_SUCCESSFULY_REMOVED' )
        );
            
        echo json_encode($response);
        JFactory::getApplication()->close();
        
	}
	
	
}