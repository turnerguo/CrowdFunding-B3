<?php
/**
 * @package      CrowdFunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined( "_JEXEC" ) or die;

$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

$app	= JFactory::getApplication();

$option = $app->input->get("option");
$view   = $app->input->get("view");

$allowedViews = array("backing", "embed");

// If option is not "com_crowdfunding" and view is not "b",
// do not display anything.
if( (strcmp($option, "com_crowdfunding") != 0) OR (!in_array($view, $allowedViews)) ) {
    echo JText::_("MOD_CROWDFUNDINGDETAILS_ERROR_INVALID_VIEW");
    return;
}

$projectId       = $app->input->getInt("id");
if(!$projectId) {
    echo JText::_("MOD_CROWDFUNDINGDETAILS_ERROR_INVALID_PROJECT");
    return;
}

// Get project
jimport("crowdfunding.project");
$project         = CrowdFundingProject::getInstance($projectId);

if(empty($project->id)) {
    echo JText::_("MOD_CROWDFUNDINGDETAILS_ERROR_INVALID_PROJECT");
    return;
}

// Get component params
$componentParams   = JComponentHelper::getParams("com_crowdfunding");
$socialPlatform    = $componentParams->get("integration_social_platform");
$imageFolder       = $componentParams->get("images_directory", "images/crowdfunding");

// Get currency
jimport("crowdfunding.currency");
$currencyId      = $componentParams->get("project_currency");
$currency        = CrowdFundingCurrency::getInstance($currencyId);

// Get social platform and a link to the profile
jimport("itprism.integrate.profile.".JString::strtolower($socialPlatform));
$socialProfile      = CrowdFundingHelper::getSocialProfile($project->user_id, $socialPlatform);
$socialProfileLink  = (!$socialProfile) ? null : $socialProfile->getLink();

// Get amounts
$fundedAmount     = $currency->getAmountString($project->goal);
$raised           = $currency->getAmountString($project->funded);
 
// Prepare the value that I am going to display
$fundedPercents   = JHtml::_("crowdfunding.funded", $project->getFundedPercents());

$user = JFactory::getUser($project->user_id);

require JModuleHelper::getLayoutPath('mod_crowdfundingdetails', $params->get('layout', 'default'));