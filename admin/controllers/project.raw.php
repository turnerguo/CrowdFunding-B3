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
 * CrowdFunding project controller
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
     * Deletes extra image.
     */
    public function removeExtraImage()
    {
        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        $userId  = $this->input->post->getInt("user_id");
        $imageId = $this->input->post->getInt("id");

        // Get the folder where the images are stored.
        $imagesFolder = CrowdFundingHelper::getImagesFolder($userId);

        try {

            jimport('joomla.filesystem.file');

            // Get the model
            $model = $this->getModel();
            $model->removeExtraImage($imageId, $imagesFolder);

        } catch (Exception $e) {

            JLog::add($e->getMessage());

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDING_IMAGE_DELETED'))
            ->setData(array("item_id" => $imageId))
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
    public function loadLocation()
    {
        // Get the input
        $query = $this->input->get->get('query', "", 'string');

        jimport('itprism.response.json');
        $response = new ITPrismResponseJson();

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdFundingModelProject */

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
}
