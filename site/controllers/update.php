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

jimport('joomla.application.component.controllerform');

/**
 * CrowdFunding update controller
 *
 * @package     CrowdFunding
 * @subpackage  Components
  */
class CrowdFundingControllerUpdate extends JControllerForm {
    
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
    public function getModel($name = 'Update', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function save() {
        
        // Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
 
        $userId = JFactory::getUser()->id;
        if(!$userId) {
            $this->setMessage(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), "notice");
            $this->setRedirect(JRoute::_("index.php?option=com_users&view=login", false));
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
		// Get the data from the form POST
		$data    = $app->input->post->get('jform', array(), 'array');
        $itemId  = JArrayHelper::getValue($data, "project_id");
        
        // Get project
        jimport("crowdfunding.project");
        $item   = CrowdFundingProject::getInstance($itemId);
        
        // Check for valid owner
        if($item->user_id != $userId) {
            $link = CrowdFundingHelperRoute::getDetailsRoute($item->getSlug(), $item->getCatSlug(), "updates");
            $this->setMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), "warning");
            $this->setRedirect(JRoute::_($link, false));
            return;
        }
        
        $model   = $this->getModel();
        /** @var $model CrowdFundingModelUpdate **/
        
        $form    = $model->getForm($data, false);
        /** @var $form JForm **/
        
        if(!$form){
            throw new Exception($model->getError(), 500);
        }

        // Test if the data is valid.
        $validData = $model->validate($form, $data);
        
        // Check for validation errors.
        if($validData === false){
            $errors = $form->getErrors();
            $error  = array_shift($errors);
            $msg    = $error->getMessage();
            
            $link = CrowdFundingHelperRoute::getDetailsRoute($item->getSlug(), $item->getCatSlug(), "updates");
            $this->setMessage($msg, "notice");
            $this->setRedirect(JRoute::_($link, false));
            return;
        }
        
        try {
            
            $model->save($validData);
            
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'), ITPrismErrors::CODE_ERROR);
        }
        
        // Redirect to next page
        $msg  = JText::_("COM_CROWDFUNDING_UPDATE_SUCCESSFULY_SAVED");
        $link = CrowdFundingHelperRoute::getDetailsRoute($item->getSlug(), $item->getCatSlug(), "updates");
        
		$this->setRedirect(JRoute::_($link, false), $msg);
			
    }
    
}