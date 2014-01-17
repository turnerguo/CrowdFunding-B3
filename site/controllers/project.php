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

/**
 * CrowdFunding project controller
 *
 * @package     CrowdFunding
 * @subpackage  Components
  */
class CrowdFundingControllerProject extends ITPrismControllerFormFrontend {
    
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
    public function getModel($name = 'Project', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
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
        /** @var $app JSite **/
        
		// Get the data from the form POST
		$data    = $app->input->post->get('jform', array(), 'array');
        $itemId  = JArrayHelper::getValue($data, "id");
        $terms   = JArrayHelper::getValue($data, "terms", false, "bool");
        
        $redirectOptions = array(
            "view"   => "project",
            "id"     => $itemId
        );
        
        $model   = $this->getModel();
        /** @var $model CrowdFundingModelProject **/
        
        // Get component parameters
        $params  = JComponentHelper::getParams($this->option);
        
        $form    = $model->getForm($data, false);
        /** @var $form JForm **/
        
        if(!$form){
            throw new Exception($model->getError(), 500);
        }
            
        // Test if the data is valid.
        $validData = $model->validate($form, $data);
        
        // Check for errors.
        if($validData === false){
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }
        
        if(!empty($itemId)) { // Validate owner if the item is not new.
            
            $userId = JFactory::getUser()->id;
        
            if(!$model->isOwner($itemId, $userId)){
                $redirectOptions = array("view" => "discover");
                $this->displayWarning(JText::_('COM_CROWDFUNDING_INVALID_ITEM'), $redirectOptions);
                return;
            }
            
            $this->isNew = false;
        
        } else { // Verify terms of use during the process of creating a project.
        
            if($params->get("project_terms", 0) AND !$terms) {
                $redirectOptions = array("view" => "project");
                $this->displayWarning(JText::_("COM_CROWDFUNDING_ERROR_TERMS_NOT_ACCEPTED"), $redirectOptions);
                return;
            }
        
        }
        
        // Include the content plugins for validate content.
        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('content');
        
        // Trigger the onContentValidate event.
        $context    = $this->option.".basic.validate";
        $results    = $dispatcher->trigger("onContentValidate", array($context, &$validData, $params));
        
        foreach($results as $result) {
            
            if ($result["success"] == false) {
                $this->displayWarning(JArrayHelper::getValue($result, "message"), $redirectOptions);
                return;
            }
            
        }
        
        try {
            
            // Get image
            $image   = $app->input->files->get('jform', array(), 'array');
            $image   = JArrayHelper::getValue($image, "image");
            
            // Upload image
            if(!empty($image['name'])) {
            
                $imageNames    = $model->uploadImage($image);
                if(!empty($imageNames["image"])) {
                    $validData = array_merge($validData, $imageNames);
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
        
        // Redirect to next page
        $redirectOptions = array(
            "view"   => "project",
            "layout" => "funding",
            "id"     => $itemId
        );
        
        $this->displayMessage(JText::_("COM_CROWDFUNDING_PROJECT_SUCCESSFULY_SAVED"), $redirectOptions);
			
    }
    
	/**
     * Delete image
     */
    public function removeImage() {
        
        // Check for request forgeries.
        JSession::checkToken("get") or jexit(JText::_('JINVALID_TOKEN'));
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $userId  = JFactory::getUser()->id;
        
        // Check for registered user
        if(!$userId) {
            $redirectOptions = array(
                "force_direction" => "index.php?option=com_users&view=login"
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), $redirectOptions);
            return;
        }
        
        // Get item id
        $itemId  = $app->input->get->getInt("id");
        $redirectOptions = array(
            "view" => "project"
        );
        
        // Check for registered user
        if(!$itemId) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_INVALID_IMAGE'), $redirectOptions);
            return;
        }
        
        try {
            
            $model = $this->getModel();
            $model->removeImage($itemId, $userId);
            
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
        
        $redirectOptions["id"] = $itemId;
        $this->displayMessage(JText::_('COM_CROWDFUNDING_IMAGE_DELETED'), $redirectOptions);
        
    }
    
}