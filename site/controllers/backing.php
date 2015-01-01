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
 * CrowdFunding backing controller
 *
 * @package     ITPrism Components
 * @subpackage  CrowdFunding
 */
class CrowdFundingControllerBacking extends JControllerLegacy
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
    public function getModel($name = 'Backing', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function step2()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get params
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        // Get the data from the form
        $itemId   = $this->input->getInt('id', 0);

        // Get user ID
        $user   = JFactory::getUser();

        $model = $this->getModel();
        /** @var $model CrowdFundingModelBacking */

        // Get the item
        $item = $model->getItem($itemId);

        $returnUrl = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug);

        if (!$this->isAuthorisedStep2($item, $params, $user)) {

            $this->setRedirect(
                JRoute::_($returnUrl, false),
                JText::_('COM_CROWDFUNDING_ERROR_NO_PERMISSIONS'),
                "notice"
            );

            return;

        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get the payment process object and
        // store the selected data from the user.
        $paymentSessionContext    = CrowdFundingConstants::PAYMENT_SESSION_CONTEXT . $item->id;
        $paymentSession           = $app->getUserState($paymentSessionContext);

        $paymentSession->amount   = $this->input->getFloat("amount", 0.0);
        $paymentSession->rewardId = $this->input->getInt('rid', 0);

        // Set the value of terms to the session.
        if ($params->get("backing_terms", 0)) {
            $paymentSession->terms = $this->input->getInt('terms', 0);
        }

        $app->setUserState($paymentSessionContext, $paymentSession);
        
        // Redirect to next page
        $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug, "step2");
        $this->setRedirect(JRoute::_($link, false));
    }

    /**
     * Authorize step 2.
     *
     * @param object $item
     * @param Joomla\Registry\Registry $params
     * @param JUser $user
     *
     * @return bool
     */
    protected function isAuthorisedStep2($item, $params, $user)
    {
        $authorisedStep2 = true;

        // Trigger the event of a plugin that authorize step 2.
        JPluginHelper::importPlugin('crowdfundingpayment');
        $dispatcher = JEventDispatcher::getInstance();
        $results    = $dispatcher->trigger('onPaymentAuthorize', array('com_crowdfunding.payment.authorize', &$item, &$params, &$user));

        foreach ($results as $result) {
            if (false === $result) {
                $authorisedStep2 = false;
                break;
            }
        }

        return $authorisedStep2;
    }

    public function process()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Check for request forgeries.
        $requestMethod = $app->input->getMethod();
        if (strcmp("POST", $requestMethod) == 0) {
            JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        } else {
            JSession::checkToken("get") or jexit(JText::_('JINVALID_TOKEN'));
        }

        // Get params
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        // Get the data from the form
        $itemId   = $this->input->getInt('id', 0);
        $rewardId = $this->input->getInt('rid', 0);

        // Get amount
        $amount = CrowdFundingHelper::parseAmount($this->input->getString("amount"));

        // Get user ID
        $user   = JFactory::getUser();
        $userId = (int)$user->get("id");

        // Anonymous user ID
        $aUserId = "";

        $model   = $this->getModel();
        /** @var $model CrowdFundingModelBacking */

        // Get the item
        $item    = $model->getItem($itemId);

        $returnUrl = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug);

        // Authorise the user
        if (!$user->authorise("crowdfunding.donate", "com_crowdfunding")) {
            $this->setRedirect(
                JRoute::_($returnUrl, false),
                JText::_('COM_CROWDFUNDING_ERROR_NO_PERMISSIONS'),
                "notice"
            );
            return;
        }

        // Check for valid project
        if (empty($item->id)) {
            $this->setRedirect(
                JRoute::_(CrowdFundingHelperRoute::getDiscoverRoute()),
                JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'),
                "notice"
            );
            return;
        }

        // Check for maintenance (debug) state
        if ($params->get("debug_payment_disabled", 0)) {
            $msg = JString::trim($params->get("debug_disabled_functionality_msg"));
            if (!$msg) {
                $msg = JText::_("COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG");
            }

            $this->setRedirect(JRoute::_($returnUrl, false), $msg, "notice");
            return;
        }

        // Check for agreed conditions from the user
        if ($params->get("backing_terms", 0)) {
            $terms = $this->input->get("terms", 0, "int");
            if (!$terms) {
                $this->setRedirect(
                    JRoute::_($returnUrl, false),
                    JText::_("COM_CROWDFUNDING_ERROR_TERMS_NOT_ACCEPTED"),
                    "notice"
                );
                return;
            }
        }

        // Check for valid amount
        if (!$amount) {
            $this->setRedirect(
                JRoute::_($returnUrl, false),
                JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"),
                "notice"
            );
            return;
        }

        // Store payment process data

        // Get the payment process object and
        // store the selected data from the user.
        $paymentSessionContext    = CrowdFundingConstants::PAYMENT_SESSION_CONTEXT . $item->id;
        $paymentSession           = $app->getUserState($paymentSessionContext);
        $paymentSession->step1    = true;
        $paymentSession->amount   = $amount;
        $paymentSession->rewardId = $rewardId;
        $app->setUserState($paymentSessionContext, $paymentSession);

        // Create an intention.

        // Generate hash user ID used for anonymous payment.
        if (!$userId) {

            $aUserId = $app->getUserState("auser_id");
            if (!$aUserId) {
                // Generate a hash ID for anonymous user.
                jimport("itprism.string");
                $anonymousUserId = new ITPrismString();
                $anonymousUserId->generateRandomString(32);

                $aUserId = (string)$anonymousUserId;
                $app->setUserState("auser_id", $aUserId);
            }

            $intentionKeys = array(
                "auser_id"   => $aUserId,
                "project_id" => $item->id
            );

        } else {

            $intentionKeys = array(
                "user_id"    => $userId,
                "project_id" => $item->id
            );

        }

        jimport("crowdfunding.intention");
        $intention = new CrowdFundingIntention(JFactory::getDbo());
        $intention->load($intentionKeys);

        $date   = new JDate();
        $custom = array(
            "user_id"     => $userId,
            "auser_id"    => $aUserId, // Anonymous user hash ID
            "project_id"  => $item->id,
            "reward_id"   => $rewardId,
            "record_date" => $date->toSql(),
            "session_id"  => $paymentSession->session_id
        );

        $intention->bind($custom);
        $intention->store();

        // Redirect to next page
        $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug, "payment");
        $this->setRedirect(JRoute::_($link, false));
    }
}
