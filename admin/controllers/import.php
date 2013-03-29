<?php
/**
 * @package      ITPrism Components
 * @subpackage   CrowdFunding
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

jimport( 'joomla.application.component.controllerform' );

/**
 * CrowdFunding currency controller
 *
 * @package     ITPrism Components
 * @subpackage  CrowdFunding
  */
class CrowdFundingControllerImport extends JControllerForm {
    
    // Check the table in so it can be edited.... we are done with it anyway
    private    $defaultLink = 'index.php?option=com_crowdfunding';
    
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
        
        $link    = "";
        $task    = $this->getTask();
        $data    = $app->input->post->get('jform', array(), 'array');
        $file    = $app->input->files->get('jform', array(), 'array');
        $data    = array_merge($data, $file);
        
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
            
            $this->defaultLink .= "&view=import&type=".$task;
            
            $this->setMessage($model->getError(), "notice");
            $this->setRedirect(JRoute::_($this->defaultLink, false));
            return;
        }
            
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.archive');
        
        try{
            
            $file     = JArrayHelper::getValue($data, "data");
            $filePath = $model->uploadFile($file);
            
            $fileName = JFile::getName($filePath);
            
            // Extract file if it is archive
            $ext      = JString::strtolower( JFile::getExt($fileName) );
            if(strcmp($ext, "zip") == 0) {
                
                $destFolder  = JPath::clean( $app->getCfg("tmp_path") ).DIRECTORY_SEPARATOR."currencies";
                $filePath    = $model->extractFile($filePath, $destFolder);

            } 
            
            $resetId  = JArrayHelper::getValue($data, "reset_id", false, "bool");
            $model->importCurrencies($filePath, $resetId);
            
        } catch ( Exception $e ) {
            
            JLog::add($e->getMessage());
            
            $code = $e->getCode();
            switch($code) {
                
                case ITPrismErrors::CODE_WARNING:
                    
                    $this->setMessage($e->getMessage(), "notice");
                    $link = $this->defaultLink."&view=import&type=".$task;
                    $this->setRedirect(JRoute::_($link, false));
                    return;
                    
                break;
                
                case ITPrismErrors::CODE_HIDDEN_WARNING:
                    
                    $this->setMessage(JText::_("COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED"), "notice");
                    $this->setRedirect(JRoute::_($this->defaultLink."&view=".$task, false));
                    return;
                    
                break;
                
                default:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'), ITPrismErrors::CODE_ERROR);
                break;
            }
            
        }
        
        $link = $this->defaultLink."&view=".$task;
        $this->setRedirect(JRoute::_($link, false), JText::_('COM_CROWDFUNDING_CURRENCIES_IMPORTED'));
        
    }
    
    public function locations() {
        
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $msg     = "";
        $link    = "";
        $task    = $this->getTask();
        $data    = $app->input->post->get('jform', array(), 'array');
        $file    = $app->input->files->get('jform', array(), 'array');
        $data    = array_merge($data, $file);
        
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
            
            $this->defaultLink .= "&view=import&type=".$task;
            
            $this->setMessage($model->getError(), "notice");
            $this->setRedirect(JRoute::_($this->defaultLink, false));
            return;
        }
            
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.archive');
        
        try{
            
            $file     = JArrayHelper::getValue($data, "data");
            
            $filePath = $model->uploadFile($file);
            $fileName = JFile::getName($filePath);
            
            // Extract file if it is archive
            $ext      = JString::strtolower( JFile::getExt($fileName) );
            if(strcmp($ext, "zip") == 0) {
                
                $destFolder  = JPath::clean( $app->getCfg("tmp_path") ).DIRECTORY_SEPARATOR."locations";
                if(is_dir($destFolder)) {
                    JFolder::delete($destFolder);
                }
                
                $filePath    = $model->extractFile($filePath, $destFolder);

            } 
            
            $resetId  = JArrayHelper::getValue($data, "reset_id", false, "bool");
            $model->importLocations($filePath, $resetId);
            
        } catch ( Exception $e ) {
            
            JLog::add($e->getMessage());
            
            $code = $e->getCode();
            switch($code) {
                
                case ITPrismErrors::CODE_WARNING:
                    
                    $this->setMessage($e->getMessage(), "notice");
                    $link = $this->defaultLink."&view=import&type=".$task;
                    $this->setRedirect(JRoute::_($link, false));
                    return;
                    
                break;
                
                case ITPrismErrors::CODE_HIDDEN_WARNING:
                    
                    $this->setMessage(JText::_("COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED"), "notice");
                    $this->setRedirect(JRoute::_($this->defaultLink."&view=".$task, false));
                    return;
                    
                break;
                
                default:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'), ITPrismErrors::CODE_ERROR);
                break;
            }
            
        }
        
        $msg  = JText::_('COM_CROWDFUNDING_LOCATIONS_IMPORTED');
        $link = $this->defaultLink."&view=".$task;
        $this->setRedirect(JRoute::_($link, false), $msg);
        
    }
    
    
    public function cancel() {
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $view = $app->getUserState("import.context", "currencies");
        
        $link = $this->defaultLink."&view=".$view;
        $this->setRedirect( JRoute::_($link, false) );
        
    }
    
}