<?php
/**
 * @package      CrowdFunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');
jimport('joomla.plugin.plugin');

jimport('itprism.init');
jimport('crowdfunding.init');

/**
 * CrowdFunding Modules plugin
 *
 * @package        CrowdFunding
 * @subpackage     Plugins
 */
class plgSystemCrowdFundingModules extends JPlugin
{
    /**
     * @var Joomla\Registry\Registry
     */
    public $params;
    
    public function onAfterDispatch()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return;
        }

        $document = JFactory::getDocument();
        /** @var $document JDocumentHtml */

        $type = $document->getType();
        if (strcmp("html", $type) != 0) {
            return;
        }

        // It works only for GET and POST requests.
        $method = JString::strtolower($app->input->getMethod());
        if (!in_array($method, array("get", "post"))) {
            return;
        }

        // Check component enabled
        if (!JComponentHelper::isEnabled('com_crowdfunding', true)) {
            return;
        }

        $view   = $app->input->getCmd("view");
        $option = $app->input->getCmd("option");

        $isCrowdFundingComponent = (strcmp($option, "com_crowdfunding") == 0);
        $isDetailsPage           = (strcmp($option, "com_crowdfunding") == 0 and strcmp($view, "details") == 0);

        // Allowed views for the module CrowdFunding Details
        $allowedViewsModuleDetails = array("backing", "embed");
        $allowedViewsModuleFilters = array("discover", "category");

        // Module CrowdFunding Info (mod_crowdfundinginfo).
        if (!$isDetailsPage) {
            $this->hideModule("mod_crowdfundinginfo");
        }

        // Module CrowdFunding Rewards (mod_crowdfundingrewards).
        if (!$isDetailsPage) {

            $this->hideModule("mod_crowdfundingrewards");

        } else { // Check project type. If the rewards are disable, hide the module.

            $projectId = $app->input->getInt("id");
            if (!empty($projectId)) {

                jimport("crowdfunding.project");
                jimport("crowdfunding.type");

                $project = CrowdFundingProject::getInstance(JFactory::getDbo(), $projectId);
                $type    = $project->getType();

                // Hide the module CrowdFunding Rewards, if rewards are disabled for this type.
                if (!is_null($type) and !$type->isRewardsEnabled()) {
                    $this->hideModule("mod_crowdfundingrewards");
                }
            }
        }

        // Module CrowdFunding Profile.
        if (!$isDetailsPage) {
            $this->hideModule("mod_crowdfundingprofile");
        }

        // Module CrowdFunding Details (mod_crowdfundingdetails) on backing and embed pages.
        if (!$isCrowdFundingComponent or (strcmp($option, "com_crowdfunding") == 0 and !in_array($view, $allowedViewsModuleDetails))) {
            $this->hideModule("mod_crowdfundingdetails");
        }

        // Module CrowdFunding Filters (mod_crowdfundingfilters).
        if (!$isCrowdFundingComponent or (strcmp($option, "com_crowdfunding") == 0 and !in_array($view, $allowedViewsModuleFilters))) {
            $this->hideModule("mod_crowdfundingfilters");
        }

    }

    protected function hideModule($moduleName)
    {
        $module           = JModuleHelper::getModule($moduleName);
        if (!empty($module->id)) {
            $seed             = substr(md5(uniqid(time() * rand(), true)), 0, 10);
            $module->position = "fp" . JApplicationHelper::getHash($seed);
        }
    }
}
