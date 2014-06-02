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

jimport('joomla.application.component.controller');

/**
 * CrowdFunding story controller.
 *
 * @package     CrowdFunding
 * @subpackage  Components
 */
class CrowdFundingControllerStory extends JControllerLegacy
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
    public function getModel($name = 'Story', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function addExtraImage()
    {
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
            JFactory::getApplication()->close();
        }

        $projectId = $this->input->post->get("id");

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdFundingModelStory * */

        // Validate project owner.
        jimport("crowdfunding.validator.project.owner");
        $validator = new CrowdFundingValidatorProjectOwner(JFactory::getDbo(), $projectId, $userId);
        if (!$projectId or !$validator->isValid()) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $files = $this->input->files->get("files");
        if (!$files) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_FILE_UPLOAD'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        // Get component parameters
        $params = $app->getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        // Prepare the size of additional thumbnails
        $thumbWidth  = $params->get("extre_image_thumb_width", 100);
        $thumbHeight = $params->get("extre_image_thumb_height", 100);
        if ($thumbWidth < 25 or $thumbHeight < 25) {
            $thumbWidth  = 50;
            $thumbHeight = 50;
        }

        $scale = $app->input->post->get("extra_images_thumb_scale", JImage::SCALE_INSIDE);
        $images = array();

        try {

            // Get the folder where the images will be stored
            $destination = CrowdFundingHelper::getImagesFolder($userId);

            $options = array(
                "thumb_width"  => $thumbWidth,
                "thumb_height" => $thumbHeight,
                "thumb_scale"  => $scale,
                "destination"  => $destination,
            );

            // Get the folder where the images will be stored
            $imagesUri = CrowdFundingHelper::getImagesFolderUri($userId);

            $images = $model->uploadExtraImages($files, $options);
            $images = $model->storeExtraImage($images, $projectId, $imagesUri);

        } catch (Exception $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDING_IMAGE_SAVED'))
            ->setData($images)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }


    /**
     * Delete an extra image.
     *
     */
    public function removeExtraImage()
    {
        // Create response object
        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        $userId = JFactory::getUser()->get("id");
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        // Get image ID.
        $imageId = $this->input->post->get("id");

        // Get the folder where the images are stored.
        $imagesFolder = CrowdFundingHelper::getImagesFolder($userId);

        try {

            // Get the model
            $model = $this->getModel();
            /** @var $model CrowdFundingModelStory */

            $model->removeExtraImage($imageId, $imagesFolder, $userId);

        } catch (RuntimeException $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception($e->getMessage());
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDING_IMAGE_DELETED'))
            ->setData(array("item_id" => $imageId))
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }
}
