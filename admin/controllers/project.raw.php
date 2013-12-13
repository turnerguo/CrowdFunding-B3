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
 * CrowdFunding project controller
 *
 * @package     ITPrism Components
 * @subpackage  CrowdFunding
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
	 * Deletes Extra Image
	 *
	 */
	public function removeExtraImage() {
	     
	    $app = JFactory::getApplication();
	    /** @var $app JAdministrator **/
	
	    // Get the model
	    $model  = $this->getModel();
	    /** @var $model CrowdFundingModelProject **/
	     
	    $userId   = $app->input->post->getInt("user_id");
	    $imageId  = $app->input->post->getInt("id");
	    
	    // Get the folder where the images are stored.
	    $imagesFolder = CrowdFundingHelper::getImagesFolder($userId);
	    
	    try {
	
	        jimport('joomla.filesystem.file');
	        
	        // Get the model
	        $model = $this->getModel();
	        $model->removeExtraImage($imageId, $imagesFolder);
	
	    } catch ( Exception $e ) {
	        JLog::add($e->getMessage());
	        throw new Exception($e->getMessage());
	    }
	
	    $response = array(
            "success" => true,
            "title"=> JText::_('COM_CROWDFUNDING_SUCCESS'),
            "text" => JText::_('COM_CROWDFUNDING_IMAGE_DELETED'),
            "data" => array("item_id" => $imageId)
	    );
	
	    echo json_encode($response);
	    JFactory::getApplication()->close();
	}
	
}