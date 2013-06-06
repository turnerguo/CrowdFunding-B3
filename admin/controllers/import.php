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

jimport('itprism.controller.form.backend');

/**
 * CrowdFunding import controller
 *
 * @package      CrowdFunding
 * @subpackage   Components
  */
class CrowdFundingControllerImport extends ITPrismControllerFormBackend {
    
    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Import', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function currencies() {
        
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $task    = $this->getTask();
        $data    = $app->input->post->get('jform', array(), 'array');
        $file    = $app->input->files->get('jform', array(), 'array');
        $data    = array_merge($data, $file);
        
        $redirectData = array(
            "view"  => "currencies",
        );
        
        $model   = $this->getModel();
        /** @var $model CrowdFundingModelImport **/
        
        $form    = $model->getForm($data, false);
        /** @var $form JForm **/
        
        if(!$form){
            throw new Exception($model->getError(), 500);
        }
            
        // Validate the form
        $validData = $model->validate($form, $data);
        
        // Check for errors.
        if($validData === false){
            $this->displayNotice($form->getErrors(), $redirectData);
            return;
        }
            
        $file     = JArrayHelper::getValue($data, "data");
        if(empty($file)) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), $redirectData);
            return;
        }
        
        try {
            
            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.path');
            jimport('joomla.filesystem.archive');
            jimport('itprism.file.upload');
            
            $destination  = JPath::clean( $app->getCfg("tmp_path") ) . DIRECTORY_SEPARATOR. JFile::makeSafe($file['name']);
            
            $upload = new ITPrismFileUpload($file);
            $upload->validate();
            $upload->upload($destination);
            
            $fileName = JFile::getName($destination);
            
            // Extract file if it is archive
            $ext      = JString::strtolower( JFile::getExt($fileName) );
            if(strcmp($ext, "zip") == 0) {
            
                $destFolder  = JPath::clean( $app->getCfg("tmp_path") ).DIRECTORY_SEPARATOR."currencies";
                if(is_dir($destFolder)) {
                    JFolder::delete($destFolder);
                }
            
                $filePath    = $model->extractFile($destination, $destFolder);
            
            }
           
            $resetId  = JArrayHelper::getValue($data, "reset_id", false, "bool");
            $model->importCurrencies($filePath, $resetId);
            
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'), ITPrismErrors::CODE_ERROR);
        }
        
        $this->displayMessage(JText::_('COM_CROWDFUNDING_CURRENCIES_IMPORTED'), $redirectData);
        
    }
    
    public function locations() {
        
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $task    = $this->getTask();
        $data    = $app->input->post->get('jform', array(), 'array');
        $file    = $app->input->files->get('jform', array(), 'array');
        $data    = array_merge($data, $file);
        
        $redirectData = array(
            "view"  => "locations",
        );
        
        $model   = $this->getModel();
        /** @var $model CrowdFundingModelImport **/
        
        $form    = $model->getForm($data, false);
        /** @var $form JForm **/
        
        if(!$form){
            throw new Exception($model->getError(), 500);
        }
            
        // Validate the form
        $validData = $model->validate($form, $data);
        
        // Check for errors.
        if($validData === false){
            $this->displayNotice($form->getErrors(), $redirectData);
            return;
        }
            
        $file     = JArrayHelper::getValue($data, "data");
        if(empty($file)) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), $redirectData);
            return;
        }
        
        try {
             
            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.path');
            jimport('joomla.filesystem.archive');
            jimport('itprism.file.upload');
            
            $destination  = JPath::clean( $app->getCfg("tmp_path") ) . DIRECTORY_SEPARATOR. JFile::makeSafe($file['name']);
            
            $upload = new ITPrismFileUpload($file);
            $upload->validate();
            $upload->upload($destination);
            
            $fileName = JFile::getName($destination);
            
            // Extract file if it is archive
            $ext      = JString::strtolower( JFile::getExt($fileName) );
            if(strcmp($ext, "zip") == 0) {
            
                $destFolder  = JPath::clean( $app->getCfg("tmp_path") ).DIRECTORY_SEPARATOR."locations";
                if(is_dir($destFolder)) {
                    JFolder::delete($destFolder);
                }
                
                $filePath    = $model->extractFile($destination, $destFolder);
            
            }
            
            $resetId  = JArrayHelper::getValue($data, "reset_id", false, "bool");
            $model->importLocations($filePath, $resetId);
            
            
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'), ITPrismErrors::CODE_ERROR);
        }
        
        $this->displayMessage(JText::_('COM_CROWDFUNDING_LOCATIONS_IMPORTED'), $redirectData);
        
    }
    
    
    public function cancel() {
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $view = $app->getUserState("import.context", "currencies");
        
        $link = $this->defaultLink."&view=".$view;
        $this->setRedirect( JRoute::_($link, false) );
        
    }
    
}