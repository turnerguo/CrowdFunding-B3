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
 * Default controller
 *
 * @package        CrowdFunding
 * @subpackage     Components
 */
class CrowdFundingController extends JControllerLegacy
{
    public function display($cachable = false, $urlparams = array())
    {
        $option = $this->input->getCmd("option");

        $viewName = $this->input->getCmd('view', 'dashboard');
        $this->input->set("view", $viewName);

        $doc = JFactory::getDocument();
        $doc->addStyleSheet("../media/" . $option . '/css/backend.style.css');

        parent::display($cachable, $urlparams);

        return $this;
    }
}
