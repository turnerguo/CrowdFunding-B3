<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * CrowdFunding project controller.
 *
 * @package     CrowdFunding
 * @subpackage  Components
 */
class CrowdFundingControllerProject extends JControllerLegacy
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    object    The model.
     * @since    1.5
     */
    public function getModel($name = 'Project', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @throws Exception
     * @return  void
     */
    public function loadLocation()
    {
        // Get the input
        $query = $this->input->get->get('query', "", 'string');

        jimport('itprism.response.json');
        $response = new ITPrismResponseJson();

        try {

            jimport("crowdfunding.locations");
            $locations = new CrowdFundingLocations(JFactory::getDbo());
            $locations->loadByString($query);

            $locationData = $locations->toOptions();

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $response
            ->setData($locationData)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @throws Exception
     * @return  void
     */
    public function loadProject()
    {
        // Get the input
        $query = $this->input->get->get('query', "", 'string');

        jimport('itprism.response.json');
        $response = new ITPrismResponseJson();

        try {

            $options = array(
                "published" => CrowdFundingConstants::PUBLISHED,
                "approved"  => CrowdFundingConstants::APPROVED,
            );

            jimport("crowdfunding.projects");
            $projects = new CrowdFundingProjects(JFactory::getDbo());
            $projects->loadByString($query, $options);

            $projectData = $projects->toOptions();

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $response
            ->setData($projectData)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }

    public function uploadImage()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        $userId = JFactory::getUser()->get("id");
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdFundingModelProject */

        $projectId = $this->input->post->get("id");

        // Validate project owner.
        if (!empty($projectId)) {
            jimport("crowdfunding.validator.project.owner");
            $validator = new CrowdFundingValidatorProjectOwner(JFactory::getDbo(), $projectId, $userId);
            if (!$validator->isValid()) {

                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setText(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'))
                    ->failure();

                echo $response;
                $app->close();
            }
        }

        $file = $this->input->files->get("project_image");
        if (!$file) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'))
                ->failure();

            echo $response;
            $app->close();
        }

        $temporaryUrl = "";

        try {

            // Get the folder where the images will be stored
            $temporaryFolder = CrowdFundingHelper::getTemporaryImagesFolder();

            $image      = $model->uploadImage($file, $temporaryFolder);
            $imageName  = basename($image);

            // Prepare URL to temporary image.
            $temporaryUrl = JUri::base(). CrowdFundingHelper::getTemporaryImagesFolderUri() . "/". $imageName;

            // Remove an old image if it exists.
            $oldImage = $app->getUserState(CrowdFundingConstants::TEMPORARY_IMAGE_CONTEXT);
            if (!empty($oldImage)) {
                $oldImage = JPath::clean($temporaryFolder . "/" . basename($oldImage));
                if (JFile::exists($oldImage)) {
                    JFile::delete($oldImage);
                }
            }

            // Set the name of the image in the session.
            $app->setUserState(CrowdFundingConstants::TEMPORARY_IMAGE_CONTEXT, $imageName);

        } catch (InvalidArgumentException $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            $app->close();

        } catch (RuntimeException $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            $app->close();

        } catch (Exception $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDING_IMAGE_SAVED'))
            ->setData($temporaryUrl)
            ->success();

        echo $response;
        $app->close();
    }

    public function cropImage()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        $userId = JFactory::getUser()->get("id");
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdFundingModelProject */

        $projectId = $this->input->post->get("id");

        // If there is a project, validate the owner.
        if (!empty($projectId)) {

            // Validate project owner.
            jimport("crowdfunding.validator.project.owner");
            $validator = new CrowdFundingValidatorProjectOwner(JFactory::getDbo(), $projectId, $userId);
            if (!$validator->isValid()) {

                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setText(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'))
                    ->failure();

                echo $response;
                $app->close();
            }

        }

        // Get the filename from the session.
        $fileName = basename($app->getUserState(CrowdFundingConstants::TEMPORARY_IMAGE_CONTEXT));
        $temporaryFile = JPath::clean(CrowdFundingHelper::getTemporaryImagesFolder() ."/". $fileName);

        if (!$fileName or !JFile::exists($temporaryFile)) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_FILE_DOES_NOT_EXIST'))
                ->failure();

            echo $response;
            $app->close();
        }

        $imageUrl = "";

        try {

            // Get the folder where the images will be stored
            $destination = CrowdFundingHelper::getTemporaryImagesFolder();

            $options = array(
                "width"    => $this->input->getFloat("width"),
                "height"   => $this->input->getFloat("height"),
                "x"        => $this->input->getFloat("x"),
                "y"        => $this->input->getFloat("y"),
                "destination"  => $destination,
            );

            // Resize the picture.
            $images     = $model->cropImage($temporaryFile, $options);
            $imageName  = basename(JArrayHelper::getValue($images, "image"));

            // Remove the temporary images if they exist.
            $temporaryImages = $app->getUserState(CrowdFundingConstants::CROPPED_IMAGES_CONTEXT);
            if (!empty($temporaryImages)) {
                $model->removeTemporaryImages($temporaryImages, $destination);
            }

            // If there is a project, store the images to database.
            // If there is NO project, store the images in the session.
            if (!empty($projectId)) {
                $model->updateImages($projectId, $images, $destination);
                $app->setUserState(CrowdFundingConstants::CROPPED_IMAGES_CONTEXT, null);

                // Get the folder of the images where the pictures will be stored.
                $imageUrl = JUri::base() . CrowdFundingHelper::getImagesFolderUri() ."/". $imageName;
            } else {
                $app->setUserState(CrowdFundingConstants::CROPPED_IMAGES_CONTEXT, $images);

                // Get the temporary folder where the images will be stored.
                $imageUrl = JUri::base() . CrowdFundingHelper::getTemporaryImagesFolderUri() ."/". $imageName;
            }

        } catch (RuntimeException $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            $app->close();

        } catch (Exception $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDING_IMAGE_SAVED'))
            ->setData($imageUrl)
            ->success();

        echo $response;
        $app->close();
    }

    public function cancelImageCrop()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        $userId = JFactory::getUser()->get("id");
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        try {

            // Get the folder where the images will be stored
            $temporaryFolder = CrowdFundingHelper::getTemporaryImagesFolder();

            // Remove old image if it exists.
            $oldImage = $app->getUserState(CrowdFundingConstants::TEMPORARY_IMAGE_CONTEXT);
            if (!empty($oldImage)) {
                $oldImage = JPath::clean($temporaryFolder . "/" . basename($oldImage));
                if (JFile::exists($oldImage)) {
                    JFile::delete($oldImage);
                }
            }

            // Set the name of the image in the session.
            $app->setUserState(CrowdFundingConstants::TEMPORARY_IMAGE_CONTEXT, null);

        } catch (Exception $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDING_IMAGE_RESET_SUCCESSFULLY'))
            ->success();

        echo $response;
        $app->close();
    }
}
