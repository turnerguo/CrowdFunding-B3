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

jimport('joomla.application.component.modelform');

class CrowdFundingModelStory extends CrowdFundingModelProject {
    
    
    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param	array	$data		An optional array of data for the form to interogate.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     * @return	JForm	A JForm object on success, false on failure
     * @since	1.6
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm($this->option.'.story', 'story', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        
        return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	1.6
     */
    protected function loadFormData() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
		$data	    = $app->getUserState($this->option.'.edit.story.data', array());
		if(!$data) {
		    
		    $itemId = $this->getState($this->getName().'.id');
		    $userId = JFactory::getUser()->id;
		    
		    $data   = $this->getItem($itemId, $userId);
		    
		}

		return $data;
    }
    
    /**
     * Method to save the form data.
     *
     * @param	array		The form data.
     * @return	mixed		The record id on success, null on failure.
     * @since	1.6
     */
    public function save($data) {
        
        $id             = JArrayHelper::getValue($data, "id");
        $description    = JArrayHelper::getValue($data, "description");
        
        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);
        
        $row->set("description",   $description);
        
        $this->prepareTable($row, $data);
        
        $row->store();
        
        return $row->id;
        
    }
    
	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable(&$table, $data) {
	    
	    $userId = JFactory::getUser()->id;
	    
		if (empty($table->id) OR ($userId != $table->user_id)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), ITPrismErrors::CODE_ERROR);
		}
	    
		// Prepare the video
		$pitchVideo     = JArrayHelper::getValue($data, "pitch_video");
		$table->set("pitch_video",   $pitchVideo);
		
		// Prepare the image
		if(!empty($data["pitch_image"])){
            
            // Delete old image if I upload a new one
            if(!empty($table->pitch_image)){
                
                $params       = JComponentHelper::getParams($this->option);
		        $imagesFolder = $params->get("images_directory", "images/crowdfunding");
		    
                // Remove an image from the filesystem
                $pitchImage  = $imagesFolder .DIRECTORY_SEPARATOR. $table->pitch_image;
                
                if(is_file($pitchImage)) {
                    JFile::delete($pitchImage);
                }
            }
            
            $table->set("pitch_image", $data["pitch_image"]);
            
        }
        
	}
	
    
    /**
     * Upload an image
     * 
     * @param  array $image
     * @return array
     */
    public function uploadImage($image) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $uploadedFile  = JArrayHelper::getValue($image, 'tmp_name');
        $uploadedName  = JArrayHelper::getValue($image, 'name');
        
        // Load parameters.
        $params        = JComponentHelper::getParams($this->option);
        $destFolder    = $params->get("images_directory", "images/crowdfunding");
        
        $tmpFolder       = $app->getCfg("tmp_path");
        
        // Joomla! media extension parameters
        $mediaParams     = JComponentHelper::getParams("com_media");
        
        $upload          = new ITPrismFileUploadImage($image);
        
        // Get allowed mime types from media manager options
        $mimeTypes = explode(",", $mediaParams->get("upload_mime"));
        $upload->setMimeTypes($mimeTypes);
        
        // Get allowed image extensions from media manager options
        $imageExtensions = explode(",", $mediaParams->get("image_extensions"));
        $upload->setImageExtensions($imageExtensions);
        
        $uploadMaxSize   = $mediaParams->get("upload_maxsize");
        $KB              = 1024 * 1024;
        $upload->setMaxFileSize( round($uploadMaxSize * $KB, 0) );
        
        // Validate the file
        $upload->validate();
        
        // Generate temporary file name
        $seed  = substr(md5(uniqid(time() * rand(), true)), 0, 10);
        $ext   = JFile::makeSafe(JFile::getExt($image['name']));
        
        $generatedName = JString::substr(JApplication::getHash($seed), 0, 32);
        $tmpDestFile   = $tmpFolder.DIRECTORY_SEPARATOR.$generatedName.".".$ext;
        
        // Upload temporary file
        $upload->upload($tmpDestFile);
        
        if(!is_file($tmpDestFile)){
            throw new Exception('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED');
        }

        // Resize image
        $image = new JImage();
        $image->loadFile($tmpDestFile);
        if (!$image->isLoaded()) {
            throw new Exception(JText::sprintf('COM_CROWDFUNDING_ERROR_FILE_NOT_FOUND', $tmpDestFile), ITPrismErrors::CODE_HIDDEN_WARNING);
        }
        
        $imageName     = $generatedName . "_pimage.png";
        $imageFile     = $destFolder.DIRECTORY_SEPARATOR.$imageName;
        
        // Create main image
        $width         = $params->get("pitch_image_width", 600);
        $height        = $params->get("pitch_image_height", 400);
        $image->resize($width, $height, false);
        $image->toFile($imageFile, IMAGETYPE_PNG);
        
        // Remove the temporary
        if(is_file($tmpDestFile)){
            JFile::delete($tmpDestFile);
        }
        
        return $imageName; 
    }
    
	/**
     * Delete image only
     *
     * @param integer Item id
     * @param integer User id
     */
    public function removeImage($id, $userId){
        
        // Load category data
        $row = $this->getTable();
        $row->load($id);
        
        // Verify the owner 
        if($row->user_id != $userId) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_USER"), ITPrismErrors::CODE_ERROR);
        }
        
        // Delete old image if I upload the new one
        if(!empty($row->pitch_image)){
            jimport('joomla.filesystem.file');
            
            $params       = JComponentHelper::getParams($this->option);
		    $imagesFolder = $params->get("images_directory", "images/crowdfunding");
		    
            // Remove an image from the filesystem
            $pitchImage  = $imagesFolder.DIRECTORY_SEPARATOR.$row->pitch_image;

            if(is_file($pitchImage)) {
                JFile::delete($pitchImage);
            }
            
        }
        
        $row->set("pitch_image", "");
        $row->store();
    
    }
    
}