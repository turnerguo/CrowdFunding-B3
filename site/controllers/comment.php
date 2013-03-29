<?php
/**
 * @package      ITPrism Components
 * @subpackage   CrowdFunding
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

jimport( 'joomla.application.component.controller' );

/**
 * CrowdFunding comment controller
 *
 * @package     ITPrism Components
 * @subpackage  CrowdFunding
  */
class CrowdFundingControllerComment extends JController {
    
    protected $defaultLink = "index.php?option=com_crowdfunding";
    
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
    public function getModel($name = 'Comment', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function save() {
        
        // Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
 
        $userId = JFactory::getUser()->id;
        if(!$userId) {
            $this->setMessage(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), "notice");
            
            $link = $this->prepareRedirectLink("login_form");
            $this->setRedirect(JRoute::_($link, false));
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
		// Get the data from the form POST
		$data    = $app->input->post->get('jform', array(), 'array');
        $itemId  = JArrayHelper::getValue($data, "project_id");
        
        $model   = $this->getModel();
        /** @var $model CrowdFundingModelComment **/
        
        $form    = $model->getForm($data, false);
        /** @var $form JForm **/
        
        if(!$form){
            throw new Exception($model->getError(), 500);
        }
            
        // Test if the data is valid.
        $validData = $model->validate($form, $data);
        
        // Check for validation errors.
        if($validData === false){
            
            $this->setMessage($model->getError(), "notice");
            
            $link = $this->prepareRedirectLink("comments", $itemId);
            $this->setRedirect(JRoute::_($link, false));
            return;
        }
        
        try {
            
            $model->save($validData);
            
        } catch(Exception $e){
            
            JLog::add($e->getMessage());
            
            // Problem with uploading, so set a message and redirect to pages
            $code = $e->getCode();
            switch($code) {
                
                case ITPrismErrors::CODE_WARNING:
                    $this->setMessage($e->getMessage(), "notice");
                    $link = $this->prepareRedirectLink("comments", $itemId);
                    $this->setRedirect(JRoute::_($link, false));
                    return;
                break;
                
                case ITPrismErrors::CODE_HIDDEN_WARNING:
                    
                    $this->setMessage(JText::_("COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED"), "notice");
                    $link = $this->prepareRedirectLink("comments", $itemId);
                    $this->setRedirect(JRoute::_($link, false));
                    return;
                break;
                
                default:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'), ITPrismErrors::CODE_ERROR);
                break;
            }
            
        }
        
        // Redirect to next page
        $msg  = JText::_("COM_CROWDFUNDING_COMMENT_SUCCESSFULY_SEND");
        $link = $this->prepareRedirectLink("comments", $itemId);
		$this->setRedirect(JRoute::_($link, false), $msg);
			
    }
    
	/**
     * Prepare return link
     * @param string $direction
     * @param integer $itemId
     */
    protected function prepareRedirectLink($direction, $itemId = 0) {
        
        // Prepare redirection
        switch($direction) {
            
            case "login_form":
                $link = "index.php?option=com_users&view=login";
                break;
                
            case "comments":
                $link = $this->defaultLink."&view=details&screen=comments&id=".(int)$itemId;
                break;
                
            default: // List
                $link = $this->defaultLink."&view=descover";
                break;
        }
        
        return $link;
    }
    
}