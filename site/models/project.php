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

jimport('joomla.application.component.modelform');

class CrowdFundingModelProject extends JModelForm {
    
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
	 * @param   integer  $pk      The id of the primary key.
	 * @param   integer  $userId  The id of the user.
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
     * @since	1.6
     */
    public function save($data) {
        
        $id          = JArrayHelper::getValue($data, "id");
        $title       = JArrayHelper::getValue($data, "title");
        $shortDesc   = JArrayHelper::getValue($data, "short_desc");
        $catId       = JArrayHelper::getValue($data, "catid");
        $location    = JArrayHelper::getValue($data, "location");
        $typeId      = JArrayHelper::getValue($data, "type_id");
        
        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);
        
        // If there is an id, the item is not new
        $isNew     = true;
        if(!empty($row->id)) {
            $isNew = false;
        }
        
        $row->set("title",             $title);
        $row->set("short_desc",        $shortDesc);
        $row->set("catid",             $catId);
        $row->set("location",          $location);
        $row->set("type_id",           $typeId);
        
        $this->prepareTable($row, $data);
        
        $row->store();
        
        // Load the data and initialzie some parameters.
        if($isNew) {
            $row->load();
        }
        
        // Trigger the event
        
        // Get properties
        $project = $row->getProperties();
        $project = JArrayHelper::toObject($project);
        
        // Generate context
        $context = $this->option.'.'.$this->getName();
        
        // Include the content plugins for the change of state event.
        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('content');
         
        // Trigger the onContentAfterSave event.
        $results    = $dispatcher->trigger("onContentAfterSave", array($context, &$project, $isNew));
        
        if (in_array(false, $results, true)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_DURING_PROJECT_CREATING_PROCESS"), ITPrismErrors::CODE_WARNING);
        }
        
        return $project->id;
        
    }
    
	/**
	 * Prepare and sanitise the table prior to saving.
	 * 
	 * @param   object $table
	 * @param   array  $data
	 * 
	 * @since	1.6
	 */
	protected function prepareTable(&$table, $data) {
	    
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
			$table->set("published", 0);
			
			// Set user ID
			$table->set("user_id", $userId);
			
		} else {
		    if($userId != $table->user_id) {
                throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_USER"), ITPrismErrors::CODE_ERROR);
            }
		}
		
        if(!empty($data["image"])){
            
            // Delete old image if I upload a new one
            if(!empty($table->image)){
                
                $params       = JComponentHelper::getParams($this->option);
		        $imagesFolder = $params->get("images_directory", "images/crowdfunding");
		    
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
            $table->set("image",         $data["image"]);
            $table->set("image_small",   $data["image_small"]);
            $table->set("image_square",  $data["image_square"]);
        }
        
        
        // If an alias does not exist, I will generate the new one using the title.
        if(!$table->alias) {
            $table->alias = $table->title;
        }
        $table->alias = JApplication::stringURLSafe($table->alias);
	}
	

	/**
     * Upload and resize the image
     * 
     * @param array $image
     * @return array
     */
    public function uploadImage($image){
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $names         = array("image", "small", "square");
        
        $uploadedFile  = JArrayHelper::getValue($image, 'tmp_name');
        $uploadedName  = JArrayHelper::getValue($image, 'name');
        
        // Load parameters.
        $params          = JComponentHelper::getParams($this->option);
        $destFolder      = JPath::clean(JPATH_ROOT.DIRECTORY_SEPARATOR.$params->get("images_directory", "images/crowdfunding"));
        
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
        $ext   = JFile::makeSafe(JFile::getExt($image['name']));
        
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
            throw new Exception(JText::sprintf('COM_CROWDFUNDING_ERROR_FILE_NOT_FOUND', $tmpDestFile), ITPrismErrors::CODE_HIDDEN_WARNING);
        }
        
        $imageName     = $generatedName . "_image.png";
        $smallName     = $generatedName . "_small.png";
        $squareName    = $generatedName . "_square.png";
        
        $imageFile     = $destFolder.DIRECTORY_SEPARATOR.$imageName;
        $smallFile     = $destFolder.DIRECTORY_SEPARATOR.$smallName;
        $squareFile    = $destFolder.DIRECTORY_SEPARATOR.$squareName;
        
        // Create main image
        $width         = $params->get("image_width", 200);
        $height        = $params->get("image_height", 200);
        $image->resize($width, $height, false);
        $image->toFile($imageFile, IMAGETYPE_PNG);
        
        // Create small image
        $width       = $params->get("image_small_width", 100);
        $height      = $params->get("image_small_height", 100);
        $image->resize($width, $height, false);
        $image->toFile($smallFile, IMAGETYPE_PNG);
        
        // Create square image
        $width       = $params->get("image_square_width", 50);
        $height      = $params->get("image_square_height", 50);
        $image->resize($width, $height, false);
        $image->toFile($squareFile, IMAGETYPE_PNG);
        
        $names = array(
            "image"        => $imageName,
            "image_small"  => $smallName,
            "image_square" => $squareName
        );
        
        // Remove the temporary file.
        if(is_file($tmpDestFile)){
            JFile::delete($tmpDestFile);
        }
        
        return $names; 
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
        if(!empty($row->image)){
            jimport('joomla.filesystem.file');
            
            $params       = JComponentHelper::getParams($this->option);
		    $imagesFolder = $params->get("images_directory", "images/crowdfunding");
		    
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
    
    
    /**
     * Get a list with locations searching by string
     * 
     * @param string $string
     * @return array
     */
    public function getLocations($string) {
        
        $db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		
		$search = $db->quote($db->escape($string, true).'%');
		
		$caseWhen  = ' CASE WHEN ';
		$caseWhen .= $query->charLength('a.state_code', '!=', '0');
		$caseWhen .= ' THEN ';
		$caseWhen .= $query->concatenate(array('a.name', 'a.state_code', 'a.country_code'), ', ');
		$caseWhen .= ' ELSE ';
		$caseWhen .= $query->concatenate(array('a.name', 'a.country_code'), ', ');
		$caseWhen .= ' END as name';
		
		$query
		    ->select("a.id, ". $caseWhen)
		    ->from($db->quoteName("#__crowdf_locations") . " AS a")
		    ->where($db->quoteName("a.name")." LIKE " . $search);
		
	    $db->setQuery($query, 0, 8);
		$results = $db->loadAssocList();
		
		return (array)$results;
		
    }
    
    /**
     * Load a location name from database
     * 
     * @param integer  $id	 	 ID of the location
     * @param bool     $includeCC	 Include country code
     * 
     * @return mixed    string or null
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
    
    /**
     * Validate the owner of the project.
     * 
     * @param integer $itemId
     * @param integer $userId
     * @return boolean
     */
    public function isOwner($itemId, $userId) {
        
        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);
        
        $query
            ->select("COUNT(*)")
            ->from($db->quoteName("#__crowdf_projects") . " AS a")
            ->where("a.id = " . (int)$itemId)
            ->where("a.user_id = " . (int)$userId);
        
        $db->setQuery($query, 0, 1);
        $result = $db->loadResult();
        
        return (bool)$result;
        
    }
    
}