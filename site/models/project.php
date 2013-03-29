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

jimport('joomla.application.component.modelform');

class CrowdFundingModelProject extends JModelForm {
    
    protected $mediaParams;
    protected $item;
    
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'CrowdFundingTable', $config = array()){
        return JTable::getInstance($type, $prefix, $config);
    }
    
	/**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState() {
        
        parent::populateState();
        
        $app = JFactory::getApplication("Site");
        /** @var $app JSite **/
        
		// Get the pk of the record from the request.
		$itemId = $app->input->getInt("id");
		$this->setState($this->getName() . '.id', $itemId);

		// Get item 
//	    $userId = JFactory::getUser()->id;
//	    $value  = $this->getItem($itemId, $userId);
//	    $this->setState("item", $value);
		    
		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
		
    }
    
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
        $form = $this->loadForm($this->option.'.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
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
        
		$data	    = $app->getUserState($this->option.'.edit.project.data', array());
		if(!$data) {
		    
		    $itemId = $this->getState($this->getName().'.id');
		    $userId = JFactory::getUser()->id;
		    
		    $data   = $this->getItem($itemId, $userId);
		    
		    if(!empty($data->location)) {
		        $locationName = $this->getLocationName($data->location, true);
		        if(!empty($locationName)) {
		            $data->location_preview = $locationName;
		        }
		    }
		    
		}

		return $data;
    }
    
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function getItem($pk, $userId) {
	    
	    if($this->item) {
	        return $this->item;
	    }
	    
		// Initialise variables.
		$table = $this->getTable();

		if ($pk > 0 AND $userId > 0) {
		    
		    $keys = array(
		    	"id"     => $pk, 
		    	"user_id"=> $userId
		    );
		    
			// Attempt to load the row.
			$return = $table->load($keys);

			// Check for a table object error.
			if ($return === false && $table->getError()) {
			    JLog::add($table->getError() . " [ CrowdFundingProject->getItem() ]");
				throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"), ITPrismErrors::CODE_ERROR);
			}
			
		}

		// Convert to the JObject before adding other data.
		$properties = $table->getProperties();
		$this->item = JArrayHelper::toObject($properties, 'JObject');

		if (property_exists($this->item, 'params')) {
			$registry = new JRegistry;
			$registry->loadString($this->item->params);
			$this->item->params = $registry->toArray();
		}
		
		return $this->item;
	}
	
    /**
     * Method to save the form data.
     *
     * @param	array		The form data.
     * @return	mixed		The record id on success, null on failure.
     * @since	1.6
     */
    public function save($data, $params = null) {
        
        $id          = JArrayHelper::getValue($data, "id");
        $title       = JArrayHelper::getValue($data, "title");
        $shortDesc   = JArrayHelper::getValue($data, "short_desc");
        $catId       = JArrayHelper::getValue($data, "catid");
        $location    = JArrayHelper::getValue($data, "location");
        
        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);
        
        $row->set("title",             $title);
        $row->set("short_desc",        $shortDesc);
        $row->set("catid",             $catId);
        $row->set("location",          $location);
        
        $this->prepareTable($row);
        
        $row->store();
        
        return $row->id;
        
    }
    
	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable(&$table) {
	    
	    $userId = JFactory::getUser()->id;
	    
		if (empty($table->id)) {

		    // Get maximum order number
			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db     = JFactory::getDbo();
				$query  = $db->getQuery(true);
				$query
				    ->select("MAX(ordering)")
				    ->from("#__crowdf_projects");
				
			    $db->setQuery($query, 0, 1);
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
			
			// Set published
			$table->set("published",         0);
			
			// Set user ID
			$table->set("user_id", $userId);
		} else {
		    
		    if($userId != $table->user_id) {
                throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_USER"), ITPrismErrors::CODE_ERROR);
            }
		}
		
	    // Save image
        $image = $this->saveImage();
        
        if(!empty($image["image"])){
            
            // Delete old image if I upload a new one
            if(!empty($table->image)){
                jimport('joomla.filesystem.file');
                
                $params       = JComponentHelper::getParams($this->option);
		        $imagesFolder = $params->get("images_directory", "images/projects");
		    
                // Remove an image from the filesystem
                $fileImage  = $imagesFolder .DIRECTORY_SEPARATOR. $table->image;
                $fileSmall  = $imagesFolder .DIRECTORY_SEPARATOR. $table->image_small;
                $fileSquare = $imagesFolder .DIRECTORY_SEPARATOR. $table->image_square;
               
                if(is_file($fileImage)) {
                    JFile::delete($fileImage);
                }
                
                if(is_file($fileSmall)) {
                    JFile::delete($fileSmall);
                }
                
                if(is_file($fileSquare)) {
                    JFile::delete($fileSquare);
                }
            
            }
            $table->set("image",         $image["image"]);
            $table->set("image_small",   $image["small"]);
            $table->set("image_square",  $image["square"]);
        }
        
	}
	

	/**
     * Saves the image and the thumb
     * 
     */
    protected function saveImage(){
        
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.path');
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $names         = array("image", "square", "small");
        $uploadedFile  = $app->input->files->get('jform');
        $uploadedFile  = JArrayHelper::getValue($uploadedFile, "image");
        
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
            	"image_width"     => $params->get("image_width"), 
            	"image_height"    => $params->get("image_height"),
            	"small_width"     => $params->get("image_small_width"),
                "small_height"    => $params->get("image_small_height"),
            	"square_width"    => $params->get("image_square_width"),
                "square_height"   => $params->get("image_square_height"),
            );
            
            $names = $this->uploadImage($uploadedFile['tmp_name'],$uploadedFile['name'], $imagesFolder, $options);
            
        }

        return $names;
    
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
        $imageName     = $generatedName . "_image.png";
        $smallName     = $generatedName . "_small.png";
        $squareName    = $generatedName . "_square.png";
        
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
        $smallFile   = $destFolder . DIRECTORY_SEPARATOR. $smallName;
        $squareFile  = $destFolder . DIRECTORY_SEPARATOR. $squareName;
        
        // Create commoin image
        $width       = JArrayHelper::getValue($options, "image_width",  200);
        $height      = JArrayHelper::getValue($options, "image_height", 200);
        $image->resize($width, $height, false);
        $image->toFile($imageFile, IMAGETYPE_PNG);
        
        // Create small image
        $width       = JArrayHelper::getValue($options, "small_width",  100);
        $height      = JArrayHelper::getValue($options, "small_height", 100);
        $image->resize($width, $height, false);
        $image->toFile($smallFile, IMAGETYPE_PNG);
            
        // Create square image
        $width       = JArrayHelper::getValue($options, "square_width",  50);
        $height      = JArrayHelper::getValue($options, "square_height", 50);
        $image->resize($width, $height, false);
        $image->toFile($squareFile, IMAGETYPE_PNG);
        
        // Remove the temporary 
        if(is_file($newFile)){
            JFile::delete($newFile);
        }
        
        return $names = array(
            "image"  => $imageName,
            "small"  => $smallName,
            "square" => $squareName
        );
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
        if(!empty($row->image)){
            jimport('joomla.filesystem.file');
            
            $params       = JComponentHelper::getParams($this->option);
		    $imagesFolder = $params->get("images_directory", "images/projects");
		    
            // Remove an image from the filesystem
            $fileImage  = $imagesFolder.DIRECTORY_SEPARATOR.$row->image;
            $fileSmall  = $imagesFolder.DIRECTORY_SEPARATOR.$row->image_small;
            $fileSquare = $imagesFolder.DIRECTORY_SEPARATOR.$row->image_square;

            if(is_file($fileImage)) {
                JFile::delete($fileImage);
            }
            
            if(is_file($fileSmall)) {
                JFile::delete($fileSmall);
            }
            
            if(is_file($fileSquare)) {
                JFile::delete($fileSquare);
            }
            
        }
        
        $row->set("image", "");
        $row->set("image_small", "");
        $row->set("image_square", "");
        $row->store();
    
    }
    
    public function getLocations($string) {
        
        $db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		
		$search = $db->quote($db->escape($string, true).'%');
		
		$query
		    ->select("id, CONCAT(name,', ',country_code) AS name")
		    ->from("#__crowdf_locations")
		    ->where($db->quoteName("name")." LIKE " . $search);
		
	    $db->setQuery($query, 0, 8);
		$results = $db->loadAssocList();
		
		return (array)$results;
		
    }
    
    /**
     * 
     * Load a location name from database
     * @param integer $id	 		 ID of the location
     * @param bool $includeCC	 Include country code
     */
    public function getLocationName($id, $includeCC = false ) {
        
        $db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		
		if(!$includeCC) {
		    
    		$query
    		    ->select("name")
    		    ->from("#__crowdf_locations")
    		    ->where($db->quoteName("id")." = " . (int)$id);
		
		} else {
		    
		    $query
    		    ->select("CONCAT(name, ', ', country_code) AS name")
    		    ->from("#__crowdf_locations")
    		    ->where($db->quoteName("id")." = " . (int)$id);
		}
		
	    $db->setQuery($query, 0, 1);
		$result = $db->loadResult();
		
		return $result;
		
    }
    
}