<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

jimport('itprism.controller.form.backend');

/**
 * CrowdFunding transaction controller class.
 *
 * @package		CrowdFunding
 * @subpackage	Components
 * @since		1.6
 */
class CrowdFundingControllerTransaction extends ITPrismControllerFormBackend {
    
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
    public function getModel($name = 'Transaction', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    /**
     * Save an item
     */
    public function save($key = NULL, $urlVar = NULL){
        
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $data    = $app->input->post->get('jform', array(), 'array');
        $itemId  = JArrayHelper::getValue($data, "id");
        
        $redirectOptions = array(
            "task"  => $this->getTask(),
            "id"    => $itemId
        );
        
        $model   = $this->getModel();
        /** @var $model CrowdFundingModelTransaction **/
        
        $form    = $model->getForm($data, false);
        /** @var $form JForm **/
        
        if(!$form){
            throw new Exception($model->getError(), 500);
        }
            
        // Validate the form data
        $validData = $model->validate($form, $data);
        
        // Check for errors
        if($validData === false){
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }
            
        try {
            $itemId = $model->save($validData);
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_($this->text_prefix.'_ERROR_SYSTEM'));
        }
        
        $this->displayMessage(JText::_($this->text_prefix.'_TRANSACTION_SAVED'), $redirectOptions);
        
    }
    
}