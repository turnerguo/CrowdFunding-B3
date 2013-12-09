<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

jimport('itprism.controller.admin');

/**
 * CrowdFunding logs controller class
 *
 * @package      CrowdFunding
 * @subpackage   Components
  */
class CrowdFundingControllerLogs extends ITPrismControllerAdmin {
    
    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Log', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function removeAll() {
    
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
    
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
    
        $redirectData = array(
            "view" => $this->view_list
        );
    
        // Get the model.
        $model = $this->getModel();
        /** @var $model CrowdFundingModelLog **/
    
        try {
    
            $model->removeAll();
    
        } catch (Exception $e){
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
    
        $this->displayMessage(JText::_("COM_CROWDFUNDING_ALL_ITEMS_REMOVED_SUCCESSFULLY"), $redirectData);
    
    }
}