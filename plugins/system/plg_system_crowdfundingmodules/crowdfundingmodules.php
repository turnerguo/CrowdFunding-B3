<?php
/**
 * @package      CrowdFunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
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
class plgSystemCrowdfundingModules extends JPlugin
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
        /** @var $document JDocumentHTML * */

        $type = $document->getType();
        if (strcmp("html", $type) != 0) {
            return;
        }

        // It works only for GET request
        $method = $app->input->getMethod();
        if (strcmp("GET", $method) !== 0) {
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
        $allowedViews = array("backing", "embed");

        if ($this->params->get("module_info_details_page", 0)) {

            if (!$isCrowdFundingComponent or !$isDetailsPage) {
                $this->hideModule("mod_crowdfundinginfo");
            }

        }

        if ($this->params->get("module_rewards_details_page", 0)) {

            if (!$isCrowdFundingComponent or !$isDetailsPage) {
                $this->hideModule("mod_crowdfundingrewards");

            } else { // Check project type. If the reawards are disable, hide the module.

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

        }

        // Module Profile Details page
        if ($this->params->get("module_profile_details_page", 0)) {

            if (!$isCrowdFundingComponent or !$isDetailsPage) {
                $this->hideModule("mod_crowdfundingprofile");
            }

        }

        // Backing page
        if ($this->params->get("module_details_backing_page", 0)) {
            if ((strcmp($option, "com_crowdfunding") != 0) or (strcmp($option, "com_crowdfunding") == 0 and !in_array($view, $allowedViews))) {
                $this->hideModule("mod_crowdfundingdetails");
            }
        }

        // Embed page
        if ($this->params->get("module_details_embed_page", 0)) {
            if ((strcmp($option, "com_crowdfunding") != 0) or (strcmp($option, "com_crowdfunding") == 0 and !in_array($view, $allowedViews))) {
                $this->hideModule("mod_crowdfundingdetails");
            }
        }

        // Module Filter Discover page
        if ($this->params->get("module_filters_discover_page", 0)) {
            if ((strcmp($option, "com_crowdfunding") != 0) or (strcmp($option, "com_crowdfunding") == 0 and strcmp($view, "discover") != 0)) {
                $this->hideModule("mod_crowdfundingfilters");
            }
        }
        
    }

    protected function hideModule($moduleName)
    {
        $module           = JModuleHelper::getModule($moduleName);
        $seed             = substr(md5(uniqid(time() * rand(), true)), 0, 10);
        $module->position = "fp" . JApplicationHelper::getHash($seed);
    }
}
