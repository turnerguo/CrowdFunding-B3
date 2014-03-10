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

jimport('joomla.application.component.modeladmin');

class CrowdFundingModelProject extends JModelAdmin {
    
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
     * Method to get the record form.
     *
     * @param   array   $data       An optional array of data for the form to interogate.
     * @param   boolean $loadData   True if the form is to load its own data (default case), false if not.
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true){
        
        // Get the form.
        $form = $this->loadForm($this->option.'.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
        if(empty($form)){
            return false;
        }
        
        return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData(){
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.project.data', array());
        if(empty($data)){
            $data = $this->getItem();
        }
        
        return $data;
    }
    
    /**
     * Save data into the DB
     * 
     * @param $data   The data about item
     * @return     Item ID
     */
    public function save($data){
        
        $id           = JArrayHelper::getValue($data, "id");
        $title        = JArrayHelper::getValue($data, "title");
        $alias        = JArrayHelper::getValue($data, "alias");
        $catId        = JArrayHelper::getValue($data, "catid");
        $typeId       = JArrayHelper::getValue($data, "type_id");
        $published    = JArrayHelper::getValue($data, "published");
        $approved     = JArrayHelper::getValue($data, "approved");
        $shortDesc    = JArrayHelper::getValue($data, "short_desc");
        
        $goal         = JArrayHelper::getValue($data, "goal");
        $funded       = JArrayHelper::getValue($data, "funded");
        $fundingType  = JArrayHelper::getValue($data, "funding_type");
        
        $pitchVideo   = JArrayHelper::getValue($data, "pitch_video");
        $pitchImage   = JArrayHelper::getValue($data, "pitch_image");
        $description  = JArrayHelper::getValue($data, "description");
        
        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);
        
        $row->set("title",          $title);
        $row->set("alias",          $alias);
        $row->set("catid",          $catId);
        $row->set("type_id",        $typeId);
        $row->set("published",      $published);
        $row->set("approved",       $approved);
        $row->set("short_desc",     $shortDesc);
        
        $row->set("goal",           $goal);
        $row->set("funded",         $funded);
        $row->set("funding_type",   $fundingType);
        
        $row->set("pitch_video",    $pitchVideo);
        $row->set("description",    $description);
        
        $this->prepareTableData($row, $data);
        
        $row->store();
        
        return $row->id;
    
    }
    
    /**
     * Prepare project images before saving.
     *
     * @param   object $table
     * @param   array  $data
     *
     * @since	1.6
     */
    protected function prepareTableData($table, $data) {
         
        // Prepare image.
        if(!empty($data["image"])){
    
            // Delete old image if I upload a new one
            if(!empty($table->image)){
    
                $params       = JComponentHelper::getParams($this->option);
                $imagesFolder = $params->get("images_directory", "images/crowdfunding");
    
                // Remove an image from the filesystem
                $fileImage  = JPath::clean(JPATH_ROOT.DIRECTORY_SEPARATOR.$imagesFolder .DIRECTORY_SEPARATOR. $table->image);
                $fileSmall  = JPath::clean(JPATH_ROOT.DIRECTORY_SEPARATOR.$imagesFolder .DIRECTORY_SEPARATOR. $table->image_small);
                $fileSquare = JPath::clean(JPATH_ROOT.DIRECTORY_SEPARATOR.$imagesFolder .DIRECTORY_SEPARATOR. $table->image_square);
                 
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
    
    
        // Prepare pitch image.
        if(!empty($data["pitch_image"])){
        
            // Delete old image if I upload a new one
            if(!empty($table->pitch_image)){
        
                $params       = JComponentHelper::getParams($this->option);
                $imagesFolder = $params->get("images_directory", "images/crowdfunding");
        
                // Remove an image from the filesystem
                $pitchImage  = JPath::clean(JPATH_ROOT.DIRECTORY_SEPARATOR.$imagesFolder .DIRECTORY_SEPARATOR. $table->pitch_image);
        
                if(is_file($pitchImage)) {
                    JFile::delete($pitchImage);
                }
            }
        
            $table->set("pitch_image", $data["pitch_image"]);
        
        }
        
        // If an alias does not exist, I will generate the new one using the title.
        if(!$table->alias) {
            $table->alias = $table->title;
        }
        $table->alias = JApplication::stringURLSafe($table->alias);
        
        // Prepare funding duration
        
        $durationType = JArrayHelper::getValue($data, "duration_type");
        $fundingEnd   = JArrayHelper::getValue($data, "funding_end");
        $fundingDays  = JArrayHelper::getValue($data, "funding_days");
         
        switch($durationType) {
        
            case "days":
        
                $table->funding_days = $fundingDays;
        
                // Clacluate end date
                if(!empty($table->funding_start)) {
                    $table->funding_end   = CrowdFundingHelper::calcualteEndDate($table->funding_start, $table->funding_days);
                } else {
                    $table->funding_end = "0000-00-00";
                }
        
                break;
        
            case "date":
        
                if(!CrowdFundingHelper::isValidDate($fundingEnd)) {
                    throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_DATE"), ITPrismErrors::CODE_WARNING);
                }
        
                jimport('joomla.utilities.date');
                $date = new JDate($fundingEnd);
        
                $table->funding_days = 0;
                $table->funding_end  = $date->toSql();
        
                break;
                 
            default:
                $table->funding_days = 0;
                $table->funding_end  = "0000-00-00";
                break;
        }
        
    }
    
	/**
	 * Method to change the approved state of one or more records.
	 *
	 * @param   array    A list of the primary keys to change.
	 * @param   integer  The value of the approved state.
	 */
	public function approve(array $pks, $value) {
	    
	    $table      = $this->getTable();
	    $pks        = (array)$pks;
	     
		$db      = JFactory::getDbo();
		
		$query   = $db->getQuery(true);
		$query
		    ->update($db->quoteName("#__crowdf_projects"))
		    ->set("approved = " . (int)$value)
		    ->where("id IN (".implode(",", $pks).")");

	    $db->setQuery($query);
	    $db->execute();
	    
	    // Trigger change state event
	    
	    $context = $this->option . '.' . $this->name;
	     
	    // Include the content plugins for the change of state event.
	    JPluginHelper::importPlugin('content');
	     
	    // Trigger the onContentChangeState event.
	    $dispatcher = JEventDispatcher::getInstance();
	    $result     = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));
	    
	    if (in_array(false, $result, true)) {
	        throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_CHANGE_STATE"), ITPrismErrors::CODE_WARNING);
	    }
	    
		// Clear the component's cache
		$this->cleanCache();

	}
	
	/**
	 * Method to toggle the featured setting of articles.
	 *
	 * @param   array    The ids of the items to toggle.
	 * @param   integer  The value to toggle to.
	 *
	 * @return  boolean  True on success.
	 */
	public function featured(array $pks, $value = 0) {
	    
		$db      = JFactory::getDbo();
		
		$query   = $db->getQuery(true);
		$query
		    ->update($db->quoteName("#__crowdf_projects"))
		    ->set("featured = " . (int)$value)
		    ->where("id IN (".implode(",", $pks).")");

	    $db->setQuery($query);
	    $db->execute();
	    
		// Clear the component's cache
		$this->cleanCache();

	}
	
	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 0) {
	    
	    $table      = $this->getTable();
	    $pks        = (array) $pks;
	    
	    // Access checks.
	    foreach ($pks as $i => $pk) {
	        
	        $table->reset();
	
	        if ($table->load($pk)) {
	            
	            if($value == CrowdFundingConstants::PUBLISHED) { // Publish a project

	                // Validate funding period
	                if(!$table->funding_days AND !CrowdFundingHelper::isValidDate($table->funding_end)) {
	                    throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_DURATION_PERIOD"), ITPrismErrors::CODE_WARNING);
	                }
	                
	                
	                // Calculate starting date if the user publish a project for first time.
	                if(!CrowdFundingHelper::isValidDate($table->funding_start)) {
	                    $fundindStart         = new JDate();
	                    $table->funding_start = $fundindStart->toSql();
	                    
	                    // If funding type is "days", calculate end date.
	                    if(!empty($table->funding_days)) {
	                        $table->funding_end = CrowdFundingHelper::calcualteEndDate($table->funding_start, $table->funding_days);
	                    }
	                }
	                
	                // Validate the period if the funding type is days
	                $params    = JComponentHelper::getParams($this->option);
	                
	                $minDays   = $params->get("project_days_minimum", 15);
	                $maxDays   = $params->get("project_days_maximum");
	                
	                if(CrowdFundingHelper::isValidDate($table->funding_end)) {
	                    
	                    if(!CrowdFundingHelper::isValidPeriod($table->funding_start, $table->funding_end, $minDays, $maxDays)) {
	                        if(!empty($maxDays)) {
	                            throw new Exception(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_MAX_DAYS", $minDays, $maxDays), ITPrismErrors::CODE_WARNING);
	                        } else {
	                            throw new Exception(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_DAYS", $minDays), ITPrismErrors::CODE_WARNING);
	                        }
	                    }
	                
	                }
	                
	                $table->published = CrowdFundingConstants::PUBLISHED;
	                $table->store();
	                
	            } else { // Set other states - unpublished, trash,...
	                $table->publish(array($pk), $value);
	            }
	        }
	    }
	
	    
	    // Trigger change state event
	    
	    $context = $this->option . '.' . $this->name;
	    
	    // Include the content plugins for the change of state event.
	    JPluginHelper::importPlugin('content');
	    
	    // Trigger the onContentChangeState event.
	    $dispatcher = JEventDispatcher::getInstance();
	    $result     = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));
	
	    if (in_array(false, $result, true)) {
	        throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_CHANGE_STATE"), ITPrismErrors::CODE_WARNING);
	    }
	
	    // Clear the component's cache
	    $this->cleanCache();
	
	}
	
	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param	object	A record object.
	 *
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($table) {
	    $condition   = array();
	    $condition[] = 'catid = '.(int) $table->catid;
	    return $condition;
	}
	
	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks) {
	    
	    $params       = JComponentHelper::getParams($this->option);
	    $folderImages = $params->get("images_directory", "images/crowdfunding");
	     
	    jimport("joomla.filesystem.path");
	    jimport("joomla.filesystem.file");
	    jimport("crowdfunding.project");
	    
	    foreach($pks as $id) {
	        
	        $project = new CrowdFundingProject($id);
	        
	        $this->deleteProjectImages($project, $folderImages);
	        $this->deleteAdditionalImages($project, $folderImages);
	        $this->removeIntentions($project);
	        $this->removeComments($project);
	        $this->removeUpdates($project);
	        $this->removeRewards($project);
	        $this->removeTransactions($project);
	        
	    }
	    
	    return parent::delete($pks);
	}
	
	protected function deleteAdditionalImages(CrowdFundingProject $project, $folderImages) {
	    
	    $db    = $this->getDbo();
	    
	    $projectId = $project->getId();
	    
	    // Get the extra image
	    $query = $db->getQuery(true);
	    $query
	       ->select("a.image, a.thumb")
	       ->from($db->quoteName("#__crowdf_images", "a"))
	       ->where("a.project_id =".(int)$projectId);
	    
	    $db->setQuery($query);
	    $results = $db->loadObjectList();
	    if(!$results) {
	        $results = array();
	    }
	    
	    // Remove 
	    foreach($results as $images) {
	        
	        $image = JPath::clean(JPATH_ROOT.DIRECTORY_SEPARATOR.$folderImages.DIRECTORY_SEPARATOR."user".$project->getUserId().DIRECTORY_SEPARATOR.$images->image);
	        if(JFile::exists($image)) {
	            JFile::delete($image);
	        }
	        
	        $thumb = JPath::clean(JPATH_ROOT.DIRECTORY_SEPARATOR.$folderImages.DIRECTORY_SEPARATOR."user".$project->getUserId().DIRECTORY_SEPARATOR.$images->thumb);
	        if(JFile::exists($thumb)) {
	            JFile::delete($thumb);
	        }
	    }
	    
	    // Delete records of the images
	    $query = $db->getQuery(true);
	    $query
    	    ->delete($db->quoteName("#__crowdf_images"))
    	    ->where($db->quoteName("project_id") ."=".(int)$projectId);
	     
	    $db->setQuery($query);
	    $db->execute();
	}
	
	protected function deleteProjectImages(CrowdFundingProject $project, $folderImages) {
	     
	    $db    = $this->getDbo();
	     
	    $images = array(
            "image"         => $project->getImage(),
            "image_square"  => $project->getSquareImage(),
            "image_small"   => $project->getSmallImage()
	    );
	     
	    // Remove
	    foreach($images as $image) {
	         
	        $imageFile = JPath::clean(JPATH_ROOT.DIRECTORY_SEPARATOR.$folderImages.DIRECTORY_SEPARATOR.$image);
	        if(JFile::exists($imageFile)) {
	            JFile::delete($imageFile);
	        }
	        
	    }
	    
	}
	
	protected function removeIntentions(CrowdFundingProject $project) {
	     
	    // Create query object
	    $db    = $this->getDbo();
	    $query = $db->getQuery(true);
	     
	    $query
    	    ->delete($db->quoteName("#__crowdf_intentions"))
    	    ->where($db->quoteName("project_id") ."=".(int)$project->getId());
	
	    $db->setQuery($query);
	    $db->execute();
	}
	
	protected function removeComments(CrowdFundingProject $project) {
	
	    // Create query object
	    $db    = $this->getDbo();
	    $query = $db->getQuery(true);
	
	    $query
    	    ->delete($db->quoteName("#__crowdf_comments"))
    	    ->where($db->quoteName("project_id") ."=".(int)$project->getId());
	
	    $db->setQuery($query);
	    $db->execute();
	}
	
	protected function removeUpdates(CrowdFundingProject $project) {
	
	    // Create query object
	    $db    = $this->getDbo();
	    $query = $db->getQuery(true);
	
	    $query
    	    ->delete($db->quoteName("#__crowdf_updates"))
    	    ->where($db->quoteName("project_id") ."=".(int)$project->getId());
	
	    $db->setQuery($query);
	    $db->execute();
	}
	
	protected function removeRewards(CrowdFundingProject $project) {
	
	    // Create query object
	    $db    = $this->getDbo();
	    $query = $db->getQuery(true);
	
	    $query
    	    ->delete($db->quoteName("#__crowdf_rewards"))
    	    ->where($db->quoteName("project_id") ."=".(int)$project->getId());
	
	    $db->setQuery($query);
        $db->execute();
	}
	
	protected function removeTransactions(CrowdFundingProject $project) {
	
	    // Create query object
	    $db    = $this->getDbo();
	    $query = $db->getQuery(true);
	
	    $query
    	    ->delete($db->quoteName("#__crowdf_transactions"))
    	    ->where($db->quoteName("project_id") ."=".(int)$project->getId());
	
	    $db->setQuery($query);
	    $db->execute();
	}
	
	/**
	 * Upload and resize the image
	 *
	 * @param array $image
	 * @return array
	 */
	public function uploadImage($image){
	
	    $app = JFactory::getApplication();
	    /** @var $app JAdministrator **/
	
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
	 * Upload a pitch image.
	 *
	 * @param  array $image
	 * 
	 * @return array
	 */
	public function uploadPitchImage($image) {
	
	    $app = JFactory::getApplication();
	    /** @var $app JSite **/
	
	    $app = JFactory::getApplication();
	    /** @var $app JSite **/
	
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
	        throw new Exception(JText::sprintf('COM_CROWDFUNDING_ERROR_FILE_NOT_FOUND', $tmpDestFile), ITPrismErrors::CODE_HIDDEN_WARNING);
	    }
	
	    $imageName     = $generatedName . "_pimage.png";
	    $imageFile     = JPath::clean($destFolder.DIRECTORY_SEPARATOR.$imageName);
	
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
	 * Delete image only.
	 *
	 * @param integer Item id
	 */
	public function removeImage($id){
	
	    // Load category data
	    $row = $this->getTable();
	    $row->load($id);
	
	    // Delete old image if I upload the new one
	    if(!empty($row->image)){
	        
	        $params       = JComponentHelper::getParams($this->option);
	        $imagesFolder = JPath::clean(JPATH_ROOT.DIRECTORY_SEPARATOR.$params->get("images_directory", "images/crowdfunding"));
	
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
	 * Delete pitch image.
	 *
	 * @param integer Item id
	 */
	public function removePitchImage($id){
	
	    // Load category data
	    $row = $this->getTable();
	    $row->load($id);
	
	    // Delete old image if I upload the new one
	    if(!empty($row->pitch_image)){
	
	        $params       = JComponentHelper::getParams($this->option);
	        $imagesFolder = JPath::clean(JPATH_ROOT.DIRECTORY_SEPARATOR.$params->get("images_directory", "images/crowdfunding"));
	
	        // Remove an image from the filesystem
	        $pitchImage   = $imagesFolder.DIRECTORY_SEPARATOR.$row->pitch_image;
	
	        if(is_file($pitchImage)) {
	            JFile::delete($pitchImage);
	        }
	
	    }
	
	    $row->set("pitch_image", "");
	    $row->store();
	
	}
	
	/**
	 * Only delete an additionl image
	 *
	 * @param integer Image ID
	 * @param string  A path to the images folder.
	 */
	public function removeExtraImage($id, $imagesFolder){
	
	    $db = JFactory::getDbo();
	    /** @var $db JDatabaseMySQLi **/
	
	    // Get the image
	    $query = $db->getQuery(true);
	    $query
    	    ->select("a.image, a.thumb")
    	    ->from($db->quoteName("#__crowdf_images", "a"))
    	    ->where("a.id = " . (int)$id );
	
	    $db->setQuery($query);
	    $row = $db->loadObject();
	     
	    if(!empty($row)){
	
	        // Remove the image from the filesystem
	        $file = JPath::clean($imagesFolder.DIRECTORY_SEPARATOR.$row->image);
	
	        if(is_file($file)) {
	            JFile::delete($file);
	        }
	
	        // Remove the thumbnail from the filesystem
	        $file = JPath::clean($imagesFolder.DIRECTORY_SEPARATOR. $row->thumb);
	        if(is_file($file)) {
	            JFile::delete($file);
	        }
	
	        // Delete the record
	        $query = $db->getQuery(true);
	        $query
    	        ->delete($db->quoteName("#__crowdf_images"))
    	        ->where($db->quoteName("id") ." = ". (int)$id );
	
	        $db->setQuery($query);
	        $db->execute();
	    }
	
	}
}