<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
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
class CrowdFundingControllerBacking extends JControllerLegacy {
    
    protected $wizardType;
    
	/**
     * Method to get a model object, loading it if required.
     *
     * @param	string	$name	The model name. Optional.
     * @param	string	$prefix	The class prefix. Optional.
     * @param	array	$config	Configuration array for model. Optional.
     *
     * @return	object	The model.
     * @since	1.5
     */
    public function getModel($name = 'Backing', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function step1() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // If the system use wizard type in three steps, check the token.
        $requestMethod = $app->input->getMethod();
        if(strcmp("POST", $requestMethod) == 0) {
            // Check for request forgeries.
            JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        }
        
        // Get params
        $params           = JComponentHelper::getParams("com_crowdfunding");
        
        $this->wizardType = $params->get("backing_wizard_type", "three_steps");
        
        // Get the data from the form
        $itemId       = $app->input->getInt('id', 0);
        $rewardId     = $app->input->getInt('rid', 0);
        
        // Get user ID
        $user         = JFactory::getUser();
        $userId       = (int)$user->get("id");
        
        // Anonymous user ID
        $aUserId      = "";
        
        // Get amount
        $amount       = $app->input->get("amount", 0, "float");
        
        $model        = $this->getModel();
        /** @var $model CrowdFundingModelBacking **/
        
        // Get the item
        $item         = $model->getItem($itemId);
		
		// Authorise the user
        if(!$user->authorise("crowdfunding.donate", "com_crowdfunding")) {
            $this->prepareRedirect($item, $rewardId, $amount);
            return;
        }
        
        // Check for valid project
        if(empty($item->id))  {
            $this->setRedirect(JRoute::_("index.php?option=com_crowdfunding&view=discover", false), JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), "notice");
            return;
        }
        
        // Check for maintenance (debug) state
        if($params->get("debug_payment_disabled", 0)) {
            $msg = JString::trim($params->get("debug_disabled_functionality_msg"));
            if(!$msg) { $msg = JText::_("COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG");}
            
            $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug);
            $this->setRedirect(JRoute::_($link, false), $msg, "notice");
            return;
        }
        
		// Check for agreed conditions from the user
        if($params->get("backing_terms", 0)) {
            $terms = $app->input->get("terms", 0, "int");
            if(!$terms) {
                $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug);
                $this->setRedirect(JRoute::_($link, false), JText::_("COM_CROWDFUNDING_ERROR_TERMS_NOT_ACCEPTED"), "notice");
                return;
            }
        }
        
        // Check for valid amount
        if(!$amount) {
            $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug);
            $this->setRedirect(JRoute::_($link, false), JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"), "notice");
            return;
        }
        
        // Store payment process data
         
        // Get the payment process object and
        // store the selected data from the user.
        $paymentProcessContext    = CrowdFundingConstants::PAYMENT_PROCESS_CONTEXT.$item->id;
        $paymentProcess           = $app->getUserState($paymentProcessContext);
        $paymentProcess->step1    = true;
        $paymentProcess->amount   = $amount;
        $paymentProcess->rewardId = $rewardId;
        $app->setUserState($paymentProcessContext, $paymentProcess);
        
        // Set the last selected reward ID to user state,
        // which is used in the method "populateState" in the model. 
        $projectContext = $model->getProjectContext($item->id);
        $app->setUserState($projectContext.".rid", $rewardId);
        
        
        // Store intention
        
        // Generate hash user ID used for anonymous payment.
        if(!$userId) {
            
            $aUserId       = $app->getUserState("auser_id");
            if(!$aUserId) {
                jimport("itprism.string");
                $aUserId =  ITPrismString::generateRandomString(32);
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
        $intention = new CrowdFundingIntention($intentionKeys);
        
        $date   = new JDate();
        $custom = array(
            "user_id"    =>  $userId,
            "auser_id"   =>  $aUserId, // Anonymous user hash ID
            "project_id" =>  $item->id,
            "reward_id"  =>  $rewardId,
            "record_date" => $date->toSql()
        );
        
        $intention->bind($custom);
        $intention->store();
        
        // Redirect to next page
        $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug, "payment");
		$this->setRedirect(JRoute::_($link, false));
    }
    
    protected function prepareRedirect($item, $rewardId, $amount) {
        
        switch($this->wizardType) {
        
            case "four_steps":
        
                $app = JFactory::getApplication();
                /** @var $app JSite **/
                
                // Store the data for the payment process,
                // which comes from step 1.
                $options = array(
                    "id"     => $item->id,
                    "rid"    => $rewardId,
                    "amount" => $amount
                );
                
                $app->setUserState("com_crowdfunding.backing.login", $options);
                
                $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug, "login");
                $this->setRedirect(JRoute::_($link, false));
        
                break;
        
            default: // three steps wizard type
                
                $returnUrl = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug);
                $this->setRedirect(JRoute::_("index.php?option=com_users&view=login&return=".base64_encode($returnUrl), false), JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), "notice");
                
                break;
        }
        
    }
}