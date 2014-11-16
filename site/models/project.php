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

class CrowdFundingModelProject extends JModelForm
{
    protected $item;

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'CrowdFundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since    1.6
     */
    protected function populateState()
    {
        parent::populateState();

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get the pk of the record from the request.
        $itemId = $app->input->getInt("id");
        $this->setState($this->getName() . '.id', $itemId);

        // Load the parameters.
        $value = $app->getParams($this->option);
        $this->setState('params', $value);

    }

    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param    array   $data     An optional array of data for the form to interrogate.
     * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return    JForm    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite * */

        $data = $app->getUserState($this->option . '.edit.project.data', array());
        if (!$data) {

            $itemId = (int) $this->getState($this->getName() . '.id');
            $userId = JFactory::getUser()->get("id");

            $data = $this->getItem($itemId, $userId);

            if (!empty($data->location)) {

                // Load location from database.
                jimport("crowdfunding.location");
                $location = new CrowdFundingLocation(JFactory::getDbo());
                $location->load($data->location);
                $locationName = $location->getName(true);

                // Set the name to the form element.
                if (!empty($locationName)) {
                    $data->location_preview = $locationName;
                }
            }

        }

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer $pk     The id of the primary key.
     * @param   integer $userId The id of the user.
     *
     * @return  CrowdFundingTableProject  Object on success, false on failure.
     *
     * @throws Exception
     *
     * @since   11.1
     */
    public function getItem($pk, $userId)
    {
        if ($this->item) {
            return $this->item;
        }

        // Initialise variables.
        $table = $this->getTable();

        if ($pk > 0 and $userId > 0) {

            $keys = array(
                "id"      => $pk,
                "user_id" => $userId
            );

            // Attempt to load the row.
            $return = $table->load($keys);

            // Check for a table object error.
            if ($return === false) {
                throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"));
            }

        }

        // Convert to the JObject before adding other data.
        $properties = $table->getProperties();
        $this->item = JArrayHelper::toObject($properties, 'JObject');

        if (property_exists($this->item, 'params')) {
            $registry = new JRegistry;
            /** @var  $registry Joomla\Registry\Registry */

            $registry->loadString($this->item->params);
            $this->item->params = $registry->toArray();
        }

        return $this->item;
    }

    /**
     * Method to save the form data.
     *
     * @param    array    $data    The form data.
     *
     * @throws Exception
     *
     * @return int
     * @since    1.6
     */
    public function save($data)
    {
        $id        = JArrayHelper::getValue($data, "id");
        $title     = JArrayHelper::getValue($data, "title");
        $shortDesc = JArrayHelper::getValue($data, "short_desc");
        $catId     = JArrayHelper::getValue($data, "catid");
        $location  = JArrayHelper::getValue($data, "location");
        $typeId    = JArrayHelper::getValue($data, "type_id");

        // Load a record from the database
        $row = $this->getTable();
        /** @var $row CrowdFundingTableProject */

        $row->load($id);

        // If there is an id, the item is not new
        $isNew = true;
        if ($row->get("id")) {
            $isNew = false;
        }

        $row->set("title", $title);
        $row->set("short_desc", $shortDesc);
        $row->set("catid", $catId);
        $row->set("location", $location);
        $row->set("type_id", $typeId);

        $this->prepareTable($row, $data);

        $row->store();

        // Load the data and initialize some parameters.
        if ($isNew) {
            $row->load();
        }

        // Trigger the event onContentAfterSave.
        $this->triggerEventAfterSave($row, "basic", $isNew);

        return $row->get("id");

    }

    /**
     * This method executes the event onContentAfterSave.
     *
     * @param CrowdFundingTableProject $table
     * @param string $step
     * @param bool $isNew
     *
     * @throws Exception
     */
    protected function triggerEventAfterSave($table, $step, $isNew = false)
    {
        // Get properties
        $project = $table->getProperties();
        $project = JArrayHelper::toObject($project);

        // Generate context
        $context = $this->option . '.' . $step;

        // Include the content plugins for the change of state event.
        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('content');

        // Trigger the onContentAfterSave event.
        $results = $dispatcher->trigger("onContentAfterSave", array($context, &$project, $isNew));

        if (in_array(false, $results, true)) {
            throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_DURING_PROJECT_CREATING_PROCESS"));
        }
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   object $table
     * @param   array  $data
     *
     * @throws Exception
     *
     * @since    1.6
     */
    protected function prepareTable(&$table, $data)
    {
        $userId = JFactory::getUser()->get("id");

        if (!$table->get("id")) {

            // Get maximum order number
            // Set ordering to the last item if not set
            if (!$table->get("ordering")) {

                $db    = $this->getDbo();
                $query = $db->getQuery(true);

                $query
                    ->select("MAX(ordering)")
                    ->from($db->quoteName("#__crowdf_projects"));

                $db->setQuery($query, 0, 1);
                $max = $db->loadResult();

                $table->set("ordering", $max + 1);
            }

            // Set published
            $table->set("published", 0);

            // Set user ID
            $table->set("user_id", $userId);

        } else {
            if ($userId != $table->get("user_id")) {
                throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_USER"));
            }
        }

        if (!empty($data["image"])) {

            // Delete old image if I upload a new one
            if ($table->get("image")) {

                $params       = JComponentHelper::getParams($this->option);
                /** @var  $params Joomla\Registry\Registry */

                $imagesFolder = $params->get("images_directory", "images/crowdfunding");

                // Remove an image from the filesystem
                $fileImage  = $imagesFolder . DIRECTORY_SEPARATOR . $table->get("image");
                $fileSmall  = $imagesFolder . DIRECTORY_SEPARATOR . $table->get("image_small");
                $fileSquare = $imagesFolder . DIRECTORY_SEPARATOR . $table->get("image_square");

                if (is_file($fileImage)) {
                    JFile::delete($fileImage);
                }

                if (is_file($fileSmall)) {
                    JFile::delete($fileSmall);
                }

                if (is_file($fileSquare)) {
                    JFile::delete($fileSquare);
                }

            }
            $table->set("image", $data["image"]);
            $table->set("image_small", $data["image_small"]);
            $table->set("image_square", $data["image_square"]);
        }

        // If an alias does not exist, I will generate the new one using the title.
        if (!$table->get("alias")) {
            $table->set("alias", $table->get("title"));
        }
        $table->set("alias", JApplicationHelper::stringURLSafe($table->get("alias")));
    }

    /**
     * Upload and resize the image
     *
     * @param array $image
     *
     * @throws Exception
     *
     * @return array
     */
    public function uploadImage($image)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite * */

        $uploadedFile = JArrayHelper::getValue($image, 'tmp_name');
        $uploadedName = JArrayHelper::getValue($image, 'name');
        $errorCode    = JArrayHelper::getValue($image, 'error');

        // Load parameters.
        $params     = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $destFolder = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $params->get("images_directory", "images/crowdfunding"));

        $tmpFolder  = $app->get("tmp_path");

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams("com_media");
        /** @var  $mediaParams Joomla\Registry\Registry */

        jimport("itprism.file");
        jimport("itprism.file.uploader.local");
        jimport("itprism.file.validator.size");
        jimport("itprism.file.validator.image");
        jimport("itprism.file.validator.server");

        $file = new ITPrismFile();

        // Prepare size validator.
        $KB            = 1024 * 1024;
        $fileSize      = (int)$app->input->server->get('CONTENT_LENGTH');
        $uploadMaxSize = $mediaParams->get("upload_maxsize") * $KB;

        // Prepare file size validator
        $sizeValidator = new ITPrismFileValidatorSize($fileSize, $uploadMaxSize);

        // Prepare server validator.
        $serverValidator = new ITPrismFileValidatorServer($errorCode, array(UPLOAD_ERR_NO_FILE));

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
            ->addValidator($imageValidator)
            ->addValidator($serverValidator);

        // Validate the file
        if (!$file->isValid()) {
            throw new RuntimeException($file->getError());
        }

        // Generate temporary file name
        $ext = JFile::makeSafe(JFile::getExt($image['name']));

        jimport("itprism.string");
        $generatedName = new ITPrismString();
        $generatedName->generateRandomString(32);

        $tmpDestFile = $tmpFolder . DIRECTORY_SEPARATOR . $generatedName . "." . $ext;

        // Prepare uploader object.
        $uploader = new ITPrismFileUploaderLocal($uploadedFile);
        $uploader->setDestination($tmpDestFile);

        // Upload temporary file
        $file->setUploader($uploader);

        $file->upload();

        // Get file
        $tmpDestFile = JPath::clean($file->getFile());

        if (!is_file($tmpDestFile)) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'));
        }

        // Resize image
        $image = new JImage();
        $image->loadFile($tmpDestFile);
        if (!$image->isLoaded()) {
            throw new RuntimeException(JText::sprintf('COM_CROWDFUNDING_ERROR_FILE_NOT_FOUND', $tmpDestFile));
        }

        $imageName  = $generatedName . "_image.png";
        $smallName  = $generatedName . "_small.png";
        $squareName = $generatedName . "_square.png";

        $imageFile  = $destFolder . DIRECTORY_SEPARATOR . $imageName;
        $smallFile  = $destFolder . DIRECTORY_SEPARATOR . $smallName;
        $squareFile = $destFolder . DIRECTORY_SEPARATOR . $squareName;

        // Create main image
        $width  = $params->get("image_width", 200);
        $height = $params->get("image_height", 200);
        $image->resize($width, $height, false);
        $image->toFile($imageFile, IMAGETYPE_PNG);

        // Create small image
        $width  = $params->get("image_small_width", 100);
        $height = $params->get("image_small_height", 100);
        $image->resize($width, $height, false);
        $image->toFile($smallFile, IMAGETYPE_PNG);

        // Create square image
        $width  = $params->get("image_square_width", 50);
        $height = $params->get("image_square_height", 50);
        $image->resize($width, $height, false);
        $image->toFile($squareFile, IMAGETYPE_PNG);

        $names = array(
            "image"        => $imageName,
            "image_small"  => $smallName,
            "image_square" => $squareName
        );

        // Remove the temporary file.
        if (is_file($tmpDestFile)) {
            JFile::delete($tmpDestFile);
        }

        return $names;
    }

    /**
     * Delete image only
     *
     * @param integer $id Item id
     * @param integer $userId User id
     *
     * @throws Exception
     */
    public function removeImage($id, $userId)
    {
        $keys = array(
            "id" => $id,
            "user_id" => $userId
        );

        // Load category data
        $row = $this->getTable();
        $row->load($keys);

        // Delete old image if I upload the new one
        if ($row->get("image")) {
            jimport('joomla.filesystem.file');

            $params       = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $imagesFolder = $params->get("images_directory", "images/crowdfunding");

            // Remove an image from the filesystem
            $fileImage  = $imagesFolder . DIRECTORY_SEPARATOR . $row->get("image");
            $fileSmall  = $imagesFolder . DIRECTORY_SEPARATOR . $row->get("image_small");
            $fileSquare = $imagesFolder . DIRECTORY_SEPARATOR . $row->get("image_square");

            if (is_file($fileImage)) {
                JFile::delete($fileImage);
            }

            if (is_file($fileSmall)) {
                JFile::delete($fileSmall);
            }

            if (is_file($fileSquare)) {
                JFile::delete($fileSquare);
            }
        }

        $row->set("image", "");
        $row->set("image_small", "");
        $row->set("image_square", "");
        $row->store();
    }
}
