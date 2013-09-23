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

jimport( 'joomla.application.component.controller' );

/**
 * CrowdFunding project controller
 *
 * @package     ITPrism Components
 * @subpackage  CrowdFunding
  */
class CrowdFundingControllerProject extends JController {
    
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
    
	/**
	 * Deletes Extra Image
	 *
	 */
	public function removeExtraImage() {
	
	    $app = JFactory::getApplication();
	    /** @var $app JAdministrator **/
	
	    $userId      = JFactory::getUser()->id;
	    if(!$userId) {
	        $response = array(
	                "success" => false,
	                "title"=> JText::_( 'COM_CROWDFUNDING_FAIL' ),
	                "text" => JText::_( 'COM_CROWDFUNDING_ERROR_NOT_LOG_IN' ),
	        );
	
	        echo json_encode($response);
	        JFactory::getApplication()->close();
	    }
	
	    // Get the model
	    $model  = $this->getModel();
	    /** @var $model CrowdFundingModelProject **/
	
	    $imageId   = $app->input->post->get("id");
	    if(!$model->isImageOwner($imageId, $userId)) {
	        $response = array(
	                "success" => false,
	                "title"=> JText::_( 'COM_CROWDFUNDING_FAIL' ),
	                "text" => JText::_( 'COM_CROWDFUNDING_ERROR_INVALID_PROJECT' ),
	        );
	         
	        echo json_encode($response);
	        JFactory::getApplication()->close();
	    }
	     
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
	
	public function addExtraImage() {
	
	    $app = JFactory::getApplication();
	    /** @var $app JSite **/
	
	    $userId      = JFactory::getUser()->id;
	    if(!$userId) {
	        $response = array(
	                "success" => false,
	                "title"=> JText::_( 'COM_CROWDFUNDING_FAIL' ),
	                "text" => JText::_( 'COM_CROWDFUNDING_ERROR_NOT_LOG_IN' ),
	        );
	         
	        echo json_encode($response);
	        JFactory::getApplication()->close();
	    }
	     
	    // Get the model
	    $model  = $this->getModel();
	    /** @var $model CrowdFundingModelProject **/
	     
	    $projectId   = $app->input->post->get("id");
	    if(!$model->isOwner($projectId, $userId)) {
	        $response = array(
	                "success" => false,
	                "title"=> JText::_( 'COM_CROWDFUNDING_FAIL' ),
	                "text" => JText::_( 'COM_CROWDFUNDING_ERROR_INVALID_PROJECT' ),
	        );
	
	        echo json_encode($response);
	        JFactory::getApplication()->close();
	    }
	     
	    $files     = $app->input->files->get("files");
	    if(!$files) {
	        $response = array(
	                "success" => false,
	                "title"=> JText::_( 'COM_CROWDFUNDING_FAIL' ),
	                "text" => JText::_( 'COM_CROWDFUNDING_ERROR_FILE_UPLOAD' ),
	        );
	
	        echo json_encode($response);
	        JFactory::getApplication()->close();
	    }
	
	    // Get component parameters
	    $params      = $app->getParams("com_crowdfunding");
	
	    // Prepare the size of additional thumbnails
	    $thumbWidth  = $params->get("extre_image_thumb_width", 100);
	    $thumbHeight = $params->get("extre_image_thumb_height", 100);
	    if($thumbWidth < 25 OR $thumbHeight < 25 ) {
	        $thumbWidth  = 50;
	        $thumbHeight = 50;
	    }
	     
	    $scale     = $app->input->post->get("extra_images_thumb_scale", JImage::SCALE_INSIDE);
	     
	    try {
	
	        jimport('joomla.filesystem.folder');
	        jimport('joomla.filesystem.file');
	        jimport('joomla.filesystem.path');
	        jimport('joomla.image.image');
	        jimport('itprism.file.upload.image');
	
	        // Get the folder where the images will be stored
	        $destination = CrowdFundingHelper::getImagesFolder($userId);
	         
	        $options = array(
	                "thumb_width"  => $thumbWidth,
	                "thumb_height" => $thumbHeight,
	                "thumb_scale"  => $scale,
	                "destination"  => $destination
	        );
	         
	        // Get the folder where the images will be stored
	        $imagesUri = CrowdFundingHelper::getImagesFolderUri($userId);
	         
	        $images = $model->uploadExtraImages($files, $options);
	        $images = $model->storeExtraImage($images, $projectId, $imagesUri);
	
	    } catch (Exception $e) {
	        $response = array(
	                "success" => false,
	                "title"=> JText::_('COM_CROWDFUNDING_FAIL'),
	                "text" => JText::_('COM_CROWDFUNDING_ERROR_INVALID_FILE'),
	        );
	         
	        echo json_encode($response);
	        JFactory::getApplication()->close();
	    }
	
	    $response = array(
	            "success" => true,
	            "title"=> JText::_( 'COM_CROWDFUNDING_SUCCESS' ),
	            "text" => JText::_( 'COM_CROWDFUNDING_IMAGE_SAVED' ),
	            "data" => $images
	    );
	
	    echo json_encode($response);
	    JFactory::getApplication()->close();
	}
	
}