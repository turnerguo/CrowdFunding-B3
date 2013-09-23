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

// No direct access
defined('_JEXEC') or die;

jimport('itprism.controller.admin');

/**
 * CrowdFunding rewards controller class
 *
 * @package      CrowdFunding
 * @subpackage   Components
  */
class CrowdFundingControllerRewards extends ITPrismControllerAdmin {
    
    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Reward', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
}