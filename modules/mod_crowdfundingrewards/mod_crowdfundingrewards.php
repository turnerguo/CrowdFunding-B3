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

jimport("crowdfunding.init");
JLoader::register("CrowdFundingRewardsModuleHelper", JPATH_ROOT.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."mod_crowdfundingrewards".DIRECTORY_SEPARATOR."helper.php");

$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

$app	= JFactory::getApplication();

$option = $app->input->get("option");
$view   = $app->input->get("view");

// If option is not "com_crowdfunding" and view is not "details",
// do not display anything.
if( (strcmp($option, "com_crowdfunding") != 0) OR (strcmp($view, "details") != 0) ) {
    echo JText::_("MOD_CROWDFUNDINGREWARDS_ERROR_INVALID_VIEW");
    return;
}

$projectId       = $app->input->getInt("id");
if(!$projectId) {
    echo JText::_("MOD_CROWDFUNDINGREWARDS_ERROR_INVALID_PROJECT");
    return;
}
$componentParams = JComponentHelper::getParams("com_crowdfunding");

// Get currency
jimport("crowdfunding.currency");
$currencyId      = $componentParams->get("project_currency");
$currency        = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId);

jimport("crowdfunding.project");
jimport("crowdfunding.rewards");
$project         = CrowdFundingProject::getInstance($projectId);

$rewards         = $project->getRewards(array("state" => 1));

$layout          = $params->get('layout', 'default');

switch($layout) {
    
    case "square":
    case "thumbnail":
        
        // Get the folder where the images are saved.
        $userId = $project->getUserId();
        $rewardsImagesUri = CrowdFundingHelper::getImagesFolderUri($userId);
        
        JHtml::_("crowdfunding.jquery_fancybox");
        
        $js = '
jQuery(document).ready(function() {
    jQuery("a.js-rewards-images-gallery").fancybox();
});';
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration($js);
        
        break;
        
    default:
        break;
}

require JModuleHelper::getLayoutPath('mod_crowdfundingrewards', $layout);