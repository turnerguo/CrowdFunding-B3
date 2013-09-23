<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
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
class CrowdFundingControllerBacking extends JController {
    
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
        
        // Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		// Authorise the user
		$user   = JFactory::getUser();
        if(!$user->authorise("crowdfunding.donate", "com_crowdfunding")) {
            $this->setRedirect(JRoute::_("index.php?option=com_users&view=login", false), JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), "notice");
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
		// Get the data from the form
		$itemId       = $app->input->post->getInt('id', 0);
		$rewardId     = $app->input->post->getInt('rid', 0);
		$userId       = (int)$user->get("id");
		
		// Anonymous user ID
		$aUserId      = "";
		
        $model        = $this->getModel();
        /** @var $model CrowdFundingModelBacking **/
        
        // Get the item
        $item   = $model->getItem($itemId);
        
        // Check for valid project
        if(empty($item->id))  {
            $this->setRedirect(JRoute::_("index.php?option=com_crowdfunding&view=discover", false), JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), "notice");
            return;
        }
        
        // Get params
        $params        = JComponentHelper::getParams("com_crowdfunding");
        
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
            $terms = $app->input->post->get("terms", 0, "int");
            if(!$terms) {
                $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug);
                $this->setRedirect(JRoute::_($link, false), JText::_("COM_CROWDFUNDING_ERROR_TERMS_NOT_ACCEPTED"), "notice");
                return;
            }
        }
        
        // Check for valid amount
        $amount       = $app->input->post->get("amount", 0, "float");
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
    
}