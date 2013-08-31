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
$currency        = CrowdFundingCurrency::getInstance($currencyId);

jimport("crowdfunding.project");
$project         = CrowdFundingProject::getInstance($projectId);
$fundedAmount    = $currency->getAmountString($project->goal);

require JModuleHelper::getLayoutPath('mod_crowdfundinginfo', $params->get('layout', 'default'));