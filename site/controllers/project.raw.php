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

jimport( 'joomla.application.component.controller' );

/**
 * CrowdFunding project controller.
 *
 * @package     CrowdFunding
 * @subpackage  Components
  */
class CrowdFundingControllerProject extends JControllerLegacy {
    
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
    
	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function loadLocation() {
	    
		// Get the input
		$app     = JFactory::getApplication();
		$query   = $app->input->get->get('query', "", 'string');

		// Get the model
		$model = $this->getModel();
		/** @var $model CrowdFundingModelProject **/

        try {
            $locationData = $model->getLocations($query);
        } catch ( Exception $e ) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
        
        $response = array(
        	"success" => true,
            "data"    => $locationData
        );
            
        echo json_encode($response);
        
        JFactory::getApplication()->close();
		
	}
    
	
}