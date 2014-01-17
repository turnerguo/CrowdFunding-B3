<?php
/**
 * @package      CrowdFunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined( "_JEXEC" ) or die;

$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

$app	= JFactory::getApplication();

$option = $app->input->get("option");
$view   = $app->input->get("view");

// If option is not "com_crowdfunding" and view is not "details",
// do not display anything.
if( (strcmp($option, "com_crowdfunding") != 0) OR (strcmp($view, "details") != 0) ) {
    echo JText::_("MOD_CROWDFUNDINGINFO_ERROR_INVALID_VIEW");
    return;
}

$projectId       = $app->input->getInt("id");
if(!$projectId) {
    echo JText::_("MOD_CROWDFUNDINGINFO_ERROR_INVALID_PROJECT");
    return;
}

$componentParams = JComponentHelper::getParams("com_crowdfunding");

// Get currency
jimport("crowdfunding.currency");
$currencyId      = $componentParams->get("project_currency");
$currency        = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId);

jimport("crowdfunding.project");
$project         = CrowdFundingProject::getInstance($projectId);
$fundedAmount    = $currency->getAmountString($project->getGoal());

require JModuleHelper::getLayoutPath('mod_crowdfundinginfo', $params->get('layout', 'default'));