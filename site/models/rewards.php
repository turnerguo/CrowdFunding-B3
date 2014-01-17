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

jimport('joomla.application.component.model');

class CrowdFundingModelRewards extends JModelLegacy {
    
	/**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Reward', $prefix = 'CrowdFundingTable', $config = array()){
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
		$value     = $app->input->getInt("id");
		$this->setState($this->getName().'.id', $value);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
		
    }
    
    public function getItems($projectId) {
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query
            ->select("a.id, a.amount, a.title, a.description, a.number, a.distributed, a.delivery, a.image_thumb")
            ->from($db->quoteName("#__crowdf_rewards", "a"))
            ->where("a.project_id = ". (int)$projectId)
            ->where("a.published = 1");
        
        $db->setQuery($query);
        return $db->loadAssocList();
        
    }
    
    public function validate($data) {
        
        if(empty($data) OR !is_array($data)) {
            throw new InvalidArgumentException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REWARDS"));
        }
        
        $filter = JFilterInput::getInstance();
        
        foreach($data as $key => $item) {
            
            // Filter data
            if(!is_numeric($item["amount"])) {
                $item["amount"] = 0;
            }

            $item["title"]       = $filter->clean($item["title"], "string");
            $item["title"]       = JString::trim($item["title"]);
            $item["title"]       = JString::substr($item["title"], 0, 128);

            $item["description"] = $filter->clean($item["description"], "string");
            $item["description"] = JString::trim($item["description"]);
            $item["description"] = JString::substr($item["description"], 0, 500);
            
            $item["number"]      = (int)$item["number"];
            
            $item["delivery"]    = JString::trim($item["delivery"]);
            $item["delivery"]    = $filter->clean($item["delivery"], "string");
            
            if(!empty($item["delivery"])) {
                $date     = new JDate($item["delivery"]);
                $unixTime = $date->toUnix();
                if($unixTime < 0) {
                    $item["delivery"] = "";
                }
            }
            
            if(!$item["title"]) {
                throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_TITLE"));
            }
            
            if(!$item["description"]) {
                throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_DESCRIPTION"));
            }
            
            if(!$item["amount"]) {
                throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"));
            }
            
            $data[$key] = $item;
        }
        
        return $data;
    }
    
    /**
     * Method to save the form data.
     *
     * @return	mixed		The record id on success, null on failure.
     * @since	1.6
     */
    public function save($data, $projectId) {
        
        $ids = array();
        
        foreach($data as $item) {
            
            // Load a record from the database
            $row    = $this->getTable();
            $itemId = JArrayHelper::getValue($item, "id", 0, "integer");
            
            if(!empty($itemId)) {
                $keys = array("id" => $itemId, "project_id" => $projectId);
                $row->load($keys);
                if(!$row->id) {
                    throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REWARD"));
                }
            }
            
            $amount         = JArrayHelper::getValue($item, "amount");
            $title          = JArrayHelper::getValue($item, "title");
            $description    = JArrayHelper::getValue($item, "description");
            $number         = JArrayHelper::getValue($item, "number");
            $delivery       = JArrayHelper::getValue($item, "delivery");
        
            $row->set("amount",      $amount);
            $row->set("title",       $title);
            $row->set("description", $description);
            $row->set("number",       $number);
            $row->set("delivery",    $delivery);
            $row->set("project_id",  $projectId);
            
            $row->store();
            
            $ids[] = $row->id;
            
        }
        
        return $ids;
        
    }
    
    public function remove($rewardId, $imagesFolder) {
    
        // Get reward row.
        $table = $this->getTable();
        $table->load($rewardId);
        
        if(!$table->id) {
            continue;
        }
        
        // Delete the images from filesystem.
        $this->deleteImages($table, $imagesFolder);
        
        $table->delete();
    }
    
    /**
     * Set the reward as trashed, if user want to remove it 
     * but it is part of transaction.
     *
     * @param $rewardId integer
     * @param $userId integer
     * 
     *
     * @todo move it in other model or class. It have to be part of item object.
     */
    public function trash($rewardId) {
    
        if(!empty($rewardId)) {
            
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            
            $query
                ->update($db->quoteName("#__crowdf_rewards"))
                ->set($db->quoteName("published") ."=". $db->quote("-2"))
                ->where($db->quoteName("id") ."=". (int)$rewardId );
            
            $db->setQuery($query);
            $db->execute();
            
        }
    
    }

    /**
     * This method check for selected reward from user.
     * It checks, if the reward is part of transactions.
     * 
     * @param $rewardId integer
     * @return bool
     * 
     * @todo move it in other model or class. It have to be part of item object.
     */
    public function isSelectedByUser($rewardId) {
        
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        // Validate reward
        $query
            ->select("COUNT(*)")
            ->from($db->quoteName("#__crowdf_transactions"). " AS a")
            ->where("a.reward_id = ". (int)$rewardId);
        
        $db->setQuery($query, 0, 1);
        $number = $db->loadResult();
        
        return (!$number) ? false : true;
    }
    
    /**
     * Set a state as SENT or NOT SENT in the transaction table
     * 
     * @param integer $txnId
     * @param itneger $state
     * @param itneger $userId
     * 
     * @todo move it in other model or class. It have to be part of item object.
     */
    public function changeState($txnId, $state, $userId) {
    
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
            
        $query
            ->update("#__crowdf_transactions")
            ->set("reward_state = ".(int)$state)
            ->where("id = ".(int)$txnId)
            ->where("receiver_id = " .(int)$userId);
    
        $db->setQuery($query);
        $db->execute();
    
    }
    
    /**
     * Upload images.
     *
     * @param  array $images
     * 
     * @return array
     */
    public function uploadImages($files, $destFolder, $userId, $rewardsIds) {
    
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Load parameters.
        $params          = JComponentHelper::getParams($this->option);
        
        // Joomla! media extension parameters
        $mediaParams     = JComponentHelper::getParams("com_media");
        
        jimport("itprism.file");
        jimport("itprism.file.image");
        jimport("itprism.file.uploader.local");
        jimport("itprism.file.validator.size");
        jimport("itprism.file.validator.image");
        
        $KB              = 1024 * 1024;
        
        $uploadMaxSize   = $mediaParams->get("upload_maxsize") * $KB;
        $mimeTypes       = explode(",", $mediaParams->get("upload_mime"));
        $imageExtensions = explode(",", $mediaParams->get("image_extensions"));
        
        $images          = array();
        
        foreach($files as $rewardId => $image) {
            
            // If the image is set to not valid reward, continue to next one.
            // It is impossible to store image to invalid reward.
            if(!in_array($rewardId, $rewardsIds)) {
                continue;
            }
    
            $uploadedFile  = JArrayHelper::getValue($image, 'tmp_name');
            $uploadedName  = JString::trim(JArrayHelper::getValue($image, 'name'));
            
            $file           = new ITPrismFileImage();
        
            if(!empty($uploadedName)) {
                // Prepare size validator.
                $fileSize       = (int)JArrayHelper::getValue($image, 'size');;
                $sizeValidator  = new ITPrismFileValidatorSize($fileSize, $uploadMaxSize);
        
                // Prepare image validator.
                $imageValidator = new ITPrismFileValidatorImage($uploadedFile, $uploadedName);
        
                // Get allowed mime types from media manager options
                $imageValidator->setMimeTypes($mimeTypes);
        
                // Get allowed image extensions from media manager options
                $imageValidator->setImageExtensions($imageExtensions);
        
                $file
                    ->addValidator($sizeValidator)
                    ->addValidator($imageValidator);
        
                // Validate the file
                try {
                    $file->validate();
                } catch (Exception $e) {
                    continue;
                }
            
                // Generate temporary file name
                $ext   = JString::strtolower(JFile::makeSafe(JFile::getExt($image['name'])));
            
                jimport("itprism.string");
                $generatedName = new ITPrismString();
                $generatedName->generateRandomString(12, "reward_");
            
                $destFile   = $destFolder.DIRECTORY_SEPARATOR.$generatedName.".".$ext;
            
                // Prepare uploader object.
                $uploader    = new ITPrismFileUploaderLocal($image);
                $uploader->setDestination($destFile);
            
                // Upload temporary file
                $file->setUploader($uploader);
            
                $file->upload();
            
                // Get file
                $imageSource = $file->getFile();
                if(!is_file($imageSource)){
                    continue;
                }
            
                // Generate thumbnails.
            
                // Create thumbnail.
                $generatedName->generateRandomString(12, "reward_thumb_");
                $options = array(
                    "width"  => $params->get("rewards_image_thumb_width", 200),
                    "height" => $params->get("rewards_image_thumb_height", 200),
                    "destination" => $destFolder.DIRECTORY_SEPARATOR.$generatedName.".".$ext
                ); 
                $thumbSource = $file->createThumbnail($options);
                
                // Create square image.
                $generatedName->generateRandomString(12, "reward_square_");
                $options = array(
                    "width"  => $params->get("rewards_image_square_width", 50),
                    "height" => $params->get("rewards_image_square_height", 50),
                    "destination" => $destFolder.DIRECTORY_SEPARATOR.$generatedName.".".$ext
                );
                $squareSource = $file->createThumbnail($options);
                 
                $names = array("image" => "", "thumb" => "", "square" => "");
                $names['image']  = basename($imageSource);
                $names["thumb"]  = basename($thumbSource);
                $names["square"] = basename($squareSource);
    
                $images[$rewardId] = $names;
            }
        }
        
        return $images;
    }
    
    /**
     * Save reward images to the reward.
     *
     * @param array $images
     *
     */
    public function storeImages($images, $imagesFolder){
    
        if(!$images OR !is_array($images)) {
            throw new InvalidArgumentException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_IMAGES"));
        }
        
        $result = array();
        
        jimport("itprism.file");
        jimport("itprism.file.remover.local");
    
        foreach($images as $rewardId => $pictures) {
    
            // Get reward row.
            $table = $this->getTable();
            $table->load($rewardId);
            
            if(!$table->id) {
                continue;
            }
            
            // Delete the images from filesystem.
            $this->deleteImages($table, $imagesFolder);
            
            $image  = JArrayHelper::getValue($pictures, "image");
            $thumb  = JArrayHelper::getValue($pictures, "thumb");
            $square = JArrayHelper::getValue($pictures, "square");
            
            $table->set("image", $image);
            $table->set("image_thumb", $thumb);
            $table->set("image_square", $square);
            
            $table->store();
    
        }
    
    }
    
    public function removeImage($rewardId, $imagesFolder) {
    
        // Get reward row.
        $table = $this->getTable();
        $table->load($rewardId);
        
        if(!$table->id) {
            throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REWARD"));
        }
    
        // Delete the images from filesystem.
        $this->deleteImages($table, $imagesFolder);
        
        $table->set("image", null);
        $table->set("image_thumb", null);
        $table->set("image_square", null);
        
        $table->store(true);
    
    }
    
    protected function deleteImages(&$table, $imagesFolder) {
        
        // Remove image.
        if(!empty($table->image)) {
            $fileSource = $imagesFolder.DIRECTORY_SEPARATOR.$table->image;
            if(JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }
        
        // Remove thumbnail.
        if(!empty($table->image_thumb)) {
            $fileSource = $imagesFolder.DIRECTORY_SEPARATOR.$table->image_thumb;
            if(JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }
        
        // Remove square image.
        if(!empty($table->image_square)) {
            $fileSource = $imagesFolder.DIRECTORY_SEPARATOR.$table->image_square;
            if(JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }
        
    }
    
}