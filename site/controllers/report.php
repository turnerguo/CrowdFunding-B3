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

jimport('itprism.controller.form.frontend');

/**
 * CrowdFunding report controller
 *
 * @package     CrowdFunding
 * @subpackage  Components
 */
class CrowdFundingControllerReport extends ITPrismControllerFormFrontend
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
    public function getModel($name = 'Report', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function send()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get the data from the form POST
        $data   = $this->input->post->get('cfreport', array(), 'array');
        $itemId = JArrayHelper::getValue($data, "id");

        if (!$itemId) {
            $redirectOptions = array(
                "force_direction" => CrowdFundingHelperRoute::getReportRoute()
            );
            $this->displayNotice(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), $redirectOptions);
            return;
        }

        // Get project
        jimport("crowdfunding.project");
        $item = CrowdFundingProject::getInstance(JFactory::getDbo(), $itemId);

        $redirectOptions = array(
            "force_direction" => CrowdFundingHelperRoute::getReportRoute($item->getId())
        );

        $model = $this->getModel();
        /** @var $model CrowdFundingModelReport */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED"));
        }

        // Test if the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            $errors = $form->getErrors();
            $error  = array_shift($errors);
            $msg    = $error->getMessage();

            $this->displayNotice($msg, $redirectOptions);
            return;
        }

        try {

            $userId = JFactory::getUser()->get("id");

            if (!empty($userId)) {
                $validData["user_id"] = $userId;
            }

            $model->save($validData);

        } catch (RuntimeException $e) {
            $this->displayNotice($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        // Redirect to next page
        $this->displayNotice(JText::_("COM_CROWDFUNDING_REPORT_SENT_SUCCESSFULLY"), $redirectOptions);
    }
}
