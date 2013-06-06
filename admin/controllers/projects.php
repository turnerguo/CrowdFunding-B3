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

// No direct access
defined('_JEXEC') or die;

jimport('itprism.controller.admin');

/**
 * CrowdFunding projects controller
 *
 * @package      CrowdFunding
 * @subpackage   Components
  */
class CrowdFundingControllerProjects extends ITPrismControllerAdmin {
    
    public function __construct($config = array()) {
        
		parent::__construct($config);

		// Define task mappings.

		// Value = 0
		$this->registerTask('disapprove', 'approve');

		// Value = 1
		$this->registerTask('approve', 'approve');

	}
	
    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Project', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function approve() {
        
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid   = JRequest::getVar('cid', array(), '', 'array');
		$data  = array('approve' => 1, 'disapprove' => 0);
		$task  = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');

		$redirectData = array(
	        "view" => "projects"
        );
		
		// Make sure the item ids are integers
		JArrayHelper::toInteger($cid);
		if (empty($cid)) {
		    $this->displayNotice(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), $redirectData);
            return;
			
		}
		// Get the model.
		$model = $this->getModel();

        try{
            $model->approve($cid, $value);
        } catch ( Exception $e ){
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
        
		if ($value == 1) {
		    $msg = $this->text_prefix . '_N_ITEMS_APPROVED';
		} else {
		    $msg = $this->text_prefix . '_N_ITEMS_DISAPPROVED';
		}
		
		$this->displayMessage(JText::plural($msg, count($cid)), $redirectData);
		
	}
    
}