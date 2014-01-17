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

jimport('itprism.controller.form.frontend');

JLoader::register("CrowdFundingModelProject", JPATH_COMPONENT.DIRECTORY_SEPARATOR."models".DIRECTORY_SEPARATOR."project.php");

/**
 * CrowdFunding story controller
 *
 * @package     ITPrism Components
 * @subpackage  CrowdFunding
  */
class CrowdFundingControllerStory extends ITPrismControllerFormFrontend {
    
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
    public function getModel($name = 'Story', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function save($key = NULL, $urlVar = NULL) {
        
        // Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
 
		$userId = JFactory::getUser()->id;
        if(!$userId) {
            $redirectOptions = array(
                "force_direction" => "index.php?option=com_users&view=login"
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), $redirectOptions);
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
		// Get the data from the form POST
		$data    = $app->input->post->get('jform', array(), 'array');
        $itemId  = JArrayHelper::getValue($data, "id");
        
        $redirectOptions = array(
            "view"   => "project",
            "layout" => "story",
            "id"     => $itemId
        );
        
        $model   = $this->getModel();
        /** @var $model CrowdFundingModelStory **/
        
        $form    = $model->getForm($data, false);
        /** @var $form JForm **/
        
        if(!$form){
            throw new Exception($model->getError(), 500);
        }
            
        // Test if the data is valid.
        $validData = $model->validate($form, $data);
        
        // Check for validation errors.
        if($validData === false){
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }
        
        try {
            
            // Get image
            $image   = $app->input->files->get('jform', array(), 'array');
            $image   = JArrayHelper::getValue($image, "pitch_image");
            
            // Upload image
            if(!empty($image['name'])) {
            
                $imageName    = $model->uploadImage($image);
                if(!empty($imageName)) {
                    $validData["pitch_image"] = $imageName;
                }
            
            }
            
            $itemId = $model->save($validData);
            
            $redirectOptions["id"] = $itemId;
            
        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (InvalidArgumentException $e) {
            $this->displayWarning(JText::_("COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED"), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
        
        // Validate pitch image and video URL
        $item    = $model->getItem($itemId, $userId);
        if(!$item->pitch_image AND !$item->pitch_video) {
            
            // Redirect to next page
            $redirectOptions = array(
                "view"   => "project",
                "layout" => "story",
                "id"     => $itemId
            );
            
            $this->displayNotice(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PITCH_IMAGE_OR_VIDEO"), $redirectOptions);
            return;
        }
        
		// Redirect to next page
        $redirectOptions = array(
            "view"   => "project",
            "layout" => "rewards",
            "id"     => $itemId
        );
		$this->displayMessage(JText::_("COM_CROWDFUNDING_STORY_SUCCESSFULY_SAVED"), $redirectOptions);
    }
    
	/**
     * Delete image
     */
    public function removeImage() {
        
        // Check for request forgeries.
        JSession::checkToken("get") or jexit(JText::_('JINVALID_TOKEN'));
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Check for registered user
        $userId  = JFactory::getUser()->id;
        if(!$userId) {
            $redirectOptions = array(
                "force_direction" => "index.php?option=com_users&view=login"
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), $redirectOptions);
            return;
        }
        
        $itemId  = $app->input->get->getInt("id");
        $redirectOptions = array(
            "view"   => "project",
            "layout" => "story"
        );
        
        // Check for valid item
        if(!$itemId) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_INVALID_IMAGE'), $redirectOptions);
            return;
        }
        
        try {
            
            $model = $this->getModel();
            $model->removeImage($itemId, $userId);
            
        } catch ( Exception $e ) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
        
        $redirectOptions["id"] = $itemId;
        $this->displayMessage(JText::_('COM_CROWDFUNDING_IMAGE_DELETED'), $redirectOptions);
        
        
    }
    
}