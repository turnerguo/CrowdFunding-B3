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

jimport('joomla.application.component.modeladmin');

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
		    
		    $itemId = $this->getState('project.id');
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
    public function save($data, $params = null) {
        
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
	    
		$pitchVideo     = JArrayHelper::getValue($data, "pitch_video");
        
		$table->set("pitch_video",   $pitchVideo);
		
	    // Save image
        $image = $this->saveImage();
        
        if(!empty($image)){
            
            // Delete old image if I upload a new one
            if(!empty($table->pitch_image)){
                jimport('joomla.filesystem.file');
                
                $params       = JComponentHelper::getParams($this->option);
		        $imagesFolder = $params->get("images_directory", "images/projects");
		    
                // Remove an image from the filesystem
                $pitchImage  = $imagesFolder .DIRECTORY_SEPARATOR. $table->pitch_image;
                
                if(is_file($pitchImage)) {
                    JFile::delete($pitchImage);
                }
            }
            $table->set("pitch_image", $image);
            
        }
        
	}
	
	
	/**
     * Save image
     * 
     */
    protected function saveImage(){
        
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.path');
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $name          = "";
        $uploadedFile  = $app->input->files->get('jform');
        $uploadedFile  = JArrayHelper::getValue($uploadedFile, "pitch_image");
        
        // Joomla! media extension parameters
        $this->mediaParams = JComponentHelper::getParams("com_media");
            
        // Check for errors
        $this->checkUploadErrors($uploadedFile);
        
        // Save Image
        if(!empty($uploadedFile['name'])){
            
            // Load the parameters.
		    $params       = JComponentHelper::getParams($this->option);
		    $imagesFolder = $params->get("images_directory", "images/projects");
		
            $options = array(
            	"pitch_image_width"    => $params->get("pitch_image_width"), 
            	"pitch_image_height"   => $params->get("pitch_image_height"),
            );
            
            $name = $this->uploadImage($uploadedFile['tmp_name'],$uploadedFile['name'], $imagesFolder, $options);
            
        }

        return $name;
    
    }
    
    protected function checkUploadErrors($uploadedFile){
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $serverContentLength = (int)$app->input->server->get('CONTENT_LENGTH');
        
        // Verify file size
        $mediaUploadMaxSize  = (int)$this->mediaParams->get("upload_maxsize", 0);
        $mediaUploadMaxSize  = $mediaUploadMaxSize * 1024 * 1024;
        
        $uploadMaxFileSize   = (int)ini_get('upload_max_filesize');
        $uploadMaxFileSize   = $uploadMaxFileSize * 1024 * 1024;
        
        $postMaxSize         = (int)(ini_get('post_max_size'));
        $postMaxSize         = $postMaxSize * 1024 * 1024;
        
        $memoryLimit         = (int)(ini_get('memory_limit'));
        $memoryLimit         = $memoryLimit * 1024 * 1024;
        
        if(
            $serverContentLength >  $mediaUploadMaxSize OR
			$serverContentLength >  $uploadMaxFileSize OR
			$serverContentLength >  $postMaxSize OR
			$serverContentLength >  $memoryLimit
		)
		 
		{ // Log error
		    $KB    = 1024 * 1024;
		    
		    $info = JText::sprintf("COM_CROWDFUNDING_ERROR_FILE_INFOMATION", 
		        round($serverContentLength/$KB, 0), 
		        round($mediaUploadMaxSize/$KB, 0), 
		        round($uploadMaxFileSize/$KB, 0), 
		        round($postMaxSize/$KB, 0), 
		        round($memoryLimit/$KB, 0)
	        );
	        
	        // Log error
		    JLog::add($info);
		    throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_WARNFILETOOLARGE"), ITPrismErrors::CODE_WARNING);
		}
		
        if(!empty($uploadedFile['error'])){
                
            switch($uploadedFile['error']){
                case UPLOAD_ERR_INI_SIZE:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_UPLOAD_ERR_INI_SIZE'), ITPrismErrors::CODE_HIDDEN_WARNING);
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_UPLOAD_ERR_FORM_SIZE'), ITPrismErrors::CODE_HIDDEN_WARNING);
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_UPLOAD_ERR_PARTIAL'), ITPrismErrors::CODE_HIDDEN_WARNING);
                case UPLOAD_ERR_NO_FILE:
//                    throw new Exception( JText::_( 'COM_CROWDFUNDING_ERROR_UPLOAD_ERR_NO_FILE' ), ITPrismErrors::CODE_HIDDEN_WARNING);
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_UPLOAD_ERR_NO_TMP_DIR'), ITPrismErrors::CODE_HIDDEN_WARNING);
                case UPLOAD_ERR_CANT_WRITE:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_UPLOAD_ERR_CANT_WRITE'), ITPrismErrors::CODE_HIDDEN_WARNING);
                case UPLOAD_ERR_EXTENSION:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_UPLOAD_ERR_EXTENSION'), ITPrismErrors::CODE_HIDDEN_WARNING);
                default:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_UPLOAD_ERR_UNKNOWN'), ITPrismErrors::CODE_HIDDEN_WARNING);
            }
        
        }
            
    }
    
    /**
     * 
     * Upload an image
     * @param string $uploadedFile Path and filename of the source
     * @param string $uploadedName Filename of the uploaded file
     * @param string $destFolder   Destination directory where the file will be saved
     * @param string $suffix	   File name suffix
     * @param string $options	   Options for resizing
     * 
     */
    protected function uploadImage($uploadedFile, $uploadedName, $destFolder, $options = array()) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $tmpFolder = $app->getCfg("tmp_path");
        
        jimport('joomla.image.image');
        $imageProperties = JImage::getImageFileProperties($uploadedFile);
        
        // Get allowed mime types from media manager options
        $mediaUploadMime = explode(",", $this->mediaParams->get("upload_mime"));
        if(!is_array($mediaUploadMime)) {
            $mediaUploadMime = array();
        }
        
        // Get allowed image extensions from media manager options
        $imageExtensions = explode(",", $this->mediaParams->get("image_extensions"));
        if(!is_array($imageExtensions)) {
            $imageExtensions = array();
        }
        
        // Check mime type of the file
        if(false === array_search($imageProperties->mime, $mediaUploadMime)){
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_IMAGE_TYPE'), ITPrismErrors::CODE_WARNING );
        }
        
        // Check file extension
        $ext     = JFile::getExt($uploadedName);
        $ext     = JFile::makeSafe($ext);
        
        if(false === array_search($ext, $imageExtensions)){
            throw new Exception(JText::sprintf('COM_CROWDFUNDING_ERROR_IMAGE_EXTENSIONS', $ext), ITPrismErrors::CODE_WARNING);
        }
        
        // Generate the name
        $generatedName = substr(JApplication::getHash(time()), 0, 50);
        $imageName     = $generatedName . "_pimage.png";
        
        $newFile       = $tmpFolder . DIRECTORY_SEPARATOR. $imageName;
        
        if(!JFile::upload($uploadedFile, $newFile)){
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), ITPrismErrors::CODE_WARNING);
        }
        
        if(!is_file($newFile)){
            throw new Exception('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED', ITPrismErrors::CODE_WARNING);
        }
        
        // Generate thumbnails
            
        // Resize image
        $image = new JImage();
        $image->loadFile($newFile);
        if (!$image->isLoaded()) {
            throw new Exception(JText::sprintf('COM_CROWDFUNDING_ERROR_FILE_NOT_FOUND', $newFile), ITPrismErrors::CODE_HIDDEN_WARNING);
        }
        
        $imageFile   = $destFolder . DIRECTORY_SEPARATOR. $imageName;
        
        // Create commoin image
        $width       = JArrayHelper::getValue($options, "pitch_image_width",  600);
        $height      = JArrayHelper::getValue($options, "pitch_image_height", 400);
        $image->resize($width, $height, false);
        $image->toFile($imageFile, IMAGETYPE_PNG);
        
        // Remove the temporary 
        if(is_file($newFile)){
            JFile::delete($newFile);
        }
        
        return $imageName;
    }
    
	/**
     * Delete image only
     *
     * @param integer Item id
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
		    $imagesFolder = $params->get("images_directory", "images/projects");
		    
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