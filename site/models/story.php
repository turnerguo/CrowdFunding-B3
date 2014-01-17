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

// jimport('joomla.application.component.modelform');
JLoader::register("CrowdFundingModelProject", CROWDFUNDING_PATH_COMPONENT_SITE.DIRECTORY_SEPARATOR."models".DIRECTORY_SEPARATOR."project.php");

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
        
        $uploadedFile  = JArrayHelper::getValue($image, 'tmp_name');
        $uploadedName  = JArrayHelper::getValue($image, 'name');
        
        // Load parameters.
        $params        = JComponentHelper::getParams($this->option);
        $destFolder    = $params->get("images_directory", "images/crowdfunding");
        
        $tmpFolder       = $app->getCfg("tmp_path");
        
        // Joomla! media extension parameters
        $mediaParams     = JComponentHelper::getParams("com_media");
        
        jimport("itprism.file");
        jimport("itprism.file.uploader.local");
        jimport("itprism.file.validator.size");
        jimport("itprism.file.validator.image");
        
        $file           = new ITPrismFile();
        
        // Prepare size validator.
        $KB             = 1024 * 1024;
        $fileSize       = (int)$app->input->server->get('CONTENT_LENGTH');
        $uploadMaxSize  = $mediaParams->get("upload_maxsize") * $KB;
        
        $sizeValidator  = new ITPrismFileValidatorSize($fileSize, $uploadMaxSize);
        
        
        // Prepare image validator.
        $imageValidator = new ITPrismFileValidatorImage($uploadedFile, $uploadedName);
        
        // Get allowed mime types from media manager options
        $mimeTypes = explode(",", $mediaParams->get("upload_mime"));
        $imageValidator->setMimeTypes($mimeTypes);
        
        // Get allowed image extensions from media manager options
        $imageExtensions = explode(",", $mediaParams->get("image_extensions"));
        $imageValidator->setImageExtensions($imageExtensions);
        
        $file
            ->addValidator($sizeValidator)
            ->addValidator($imageValidator);
        
        // Validate the file
        $file->validate();

        // Generate temporary file name
        $ext   = JString::strtolower(JFile::makeSafe(JFile::getExt($image['name'])));
        
        jimport("itprism.string");
        $generatedName = new ITPrismString();
        $generatedName->generateRandomString(32);
        
        $tmpDestFile   = $tmpFolder.DIRECTORY_SEPARATOR.$generatedName.".".$ext;
        
        // Prepare uploader object.
        $uploader    = new ITPrismFileUploaderLocal($image);
        $uploader->setDestination($tmpDestFile);
        
        // Upload temporary file
        $file->setUploader($uploader);
        
        $file->upload();
        
        // Get file
        $tmpDestFile = $file->getFile();
        
        if(!is_file($tmpDestFile)){
            throw new Exception('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED');
        }

        // Resize image
        $image = new JImage();
        $image->loadFile($tmpDestFile);
        if (!$image->isLoaded()) {
            throw new Exception(JText::sprintf('COM_CROWDFUNDING_ERROR_FILE_NOT_FOUND', $tmpDestFile));
        }
        
        $imageName     = $generatedName . "_pimage.png";
        $imageFile     = $destFolder.DIRECTORY_SEPARATOR.$imageName;
        
        // Create main image
        $width         = $params->get("pitch_image_width", 600);
        $height        = $params->get("pitch_image_height", 400);
        $image->resize($width, $height, false);
        $image->toFile($imageFile, IMAGETYPE_PNG);
        
        // Remove the temporary file.
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
    
    public function uploadExtraImages($files, $options){
    
        $images      = array();
        $destination = JArrayHelper::getValue($options, "destination", "images/crowdfunding");
         
        jimport("itprism.file");
        jimport("itprism.file.image");
        jimport("itprism.file.uploader.local");
        jimport("itprism.file.validator.size");
        jimport("itprism.file.validator.image");
        jimport("itprism.string");
        
        // Joomla! media extension parameters
        $mediaParams     = JComponentHelper::getParams("com_media");
        
        // check for error
        foreach($files as $image){
    
            // Upload image
            if(!empty($image['name'])){
    
                $uploadedFile  = JArrayHelper::getValue($image, 'tmp_name');
                $uploadedName  = JArrayHelper::getValue($image, 'name');
                
                $file           = new ITPrismFile();
                
                // Prepare size validator.
                $KB             = 1024 * 1024;
                $fileSize       = JArrayHelper::getValue($image, "size");
                $uploadMaxSize  = $mediaParams->get("upload_maxsize") * $KB;
                
                $sizeValidator  = new ITPrismFileValidatorSize($fileSize, $uploadMaxSize);
                
                // Prepare image validator.
                $imageValidator = new ITPrismFileValidatorImage($uploadedFile, $uploadedName);
                
                // Get allowed mime types from media manager options
                $mimeTypes = explode(",", $mediaParams->get("upload_mime"));
                $imageValidator->setMimeTypes($mimeTypes);
                
                // Get allowed image extensions from media manager options
                $imageExtensions = explode(",", $mediaParams->get("image_extensions"));
                $imageValidator->setImageExtensions($imageExtensions);
                
                $file
                    ->addValidator($sizeValidator)
                    ->addValidator($imageValidator);
                
                // Validate the file
                $file->validate();
                
                // Generate file name
                $ext   = JString::strtolower(JFile::makeSafe(JFile::getExt($image['name'])));
                
                $generatedName = new ITPrismString();
                $generatedName->generateRandomString(6);
                
                $tmpDestFile   = $destination.DIRECTORY_SEPARATOR.$generatedName."_extra.".$ext;
                
                // Prepare uploader object.
                $uploader    = new ITPrismFileUploaderLocal($image);
                $uploader->setDestination($tmpDestFile);
                
                // Upload temporary file
                $file->setUploader($uploader);
                
                $file->upload();
                
                // Get file
                $imageSource = $file->getFile();
                
                if(!JFile::exists($imageSource)) {
                    throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED"));
                }
    
                // Create thumbnail
                $fileImage = new ITPrismFileImage($imageSource);
                $options["destination"] = $destination.DIRECTORY_SEPARATOR.$generatedName."_extra_thumb.".$ext;
                $thumbSource = $fileImage->createThumbnail($options);
                 
                $names = array("image" => "", "thumb" => "");
                $names['image'] = basename($imageSource);
                $names["thumb"] = basename($thumbSource);
    
                $images[] = $names;
    
            }
        }
    
        return $images;
    
    }
    
    /**
     * Save additional images to the project.
     * 
     * @param array $images
     *
     * @throws Exception
     */
    public function storeExtraImage($images, $projectId, $imagesUri){
    
        settype($images,    "array");
        settype($projectId, "integer");
        $result = array();
    
        if(!empty($images) AND !empty($projectId)){
    
            $image = array_shift($images);
    
            $db = JFactory::getDbo();
            /** @var $db JDatabaseMySQLi **/
    
            $query = $db->getQuery(true);
            $query
                ->insert($db->quoteName("#__crowdf_images"))
                ->set( $db->quoteName("image")      ."=". $db->quote($image["image"]))
                ->set( $db->quoteName("thumb")      ."=". $db->quote($image["thumb"]))
                ->set( $db->quoteName("project_id") ."=". (int)$projectId);
    
            $db->setQuery($query);
            $db->execute();
    
            $lastId = $db->insertid();
    
            // Add URI path to images
            $result = array(
                "id"     => $lastId,
                "image"  => $imagesUri."/".$image["image"],
                "thumb"  => $imagesUri."/".$image["thumb"]
            );
    
        }
    
        return $result;
    
    }
    
    /**
     * Only delete an additionl image.
     *
     * @param integer Image ID
     * @param string  A path to the images folder.
     */
    public function removeExtraImage($imageId, $imagesFolder, $userId){
    
        jimport("itprism.file.image");
        jimport("itprism.file.remover.local");
        jimport("crowdfunding.image.validator.owner");
        jimport("crowdfunding.image.remover.extra");
        
        $file = new ITPrismFileImage();
        
        // Validate owner of the project.
        $ownerValidator = new CrowdFundingImageValidatorOwner(JFactory::getDbo(), $imageId, $userId);
        $file->addValidator($ownerValidator);
        
        $file->validate();
            
        // Remove the image.
        $remover = new CrowdFundingImageRemoverExtra(JFactory::getDbo(), $imageId, $imagesFolder);
        $file->addRemover($remover);
        
        $file->remove();
    
    }
    
}