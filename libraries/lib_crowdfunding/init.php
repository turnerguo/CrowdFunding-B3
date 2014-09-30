<?php
/**
 * @package      CrowdFunding
 * @subpackage   Library
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

if (!defined("CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR")) {
    define("CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_crowdfunding");
}

if (!defined("CROWDFUNDING_PATH_COMPONENT_SITE")) {
    define("CROWDFUNDING_PATH_COMPONENT_SITE", JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_crowdfunding");
}

if (!defined("CROWDFUNDING_PATH_LIBRARY")) {
    define("CROWDFUNDING_PATH_LIBRARY", JPATH_LIBRARIES . DIRECTORY_SEPARATOR . "crowdfunding");
}

// Register version and constants
JLoader::register("CrowdFundingVersion", CROWDFUNDING_PATH_LIBRARY . DIRECTORY_SEPARATOR . "version.php");
JLoader::register("CrowdFundingConstants", CROWDFUNDING_PATH_LIBRARY . DIRECTORY_SEPARATOR . "constants.php");

// Register some helpers
JLoader::register("CrowdFundingCategories", CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "category.php");
JLoader::register("CrowdFundingHelper", CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "crowdfunding.php");
JLoader::register("CrowdFundingHelperRoute", CROWDFUNDING_PATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "route.php");

// Register the most used classes
JLoader::register("CrowdFundingDate", CROWDFUNDING_PATH_LIBRARY . DIRECTORY_SEPARATOR . "date.php");
JLoader::register("CrowdFundingEmail", CROWDFUNDING_PATH_LIBRARY . DIRECTORY_SEPARATOR . "email.php");

// Register some Joomla! classes
JLoader::register(
    'JHtmlString',
    JPATH_LIBRARIES. DIRECTORY_SEPARATOR . "joomla" . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . 'string.php'
);

JLoader::register(
    "JHtmlCategory",
    JPATH_LIBRARIES . DIRECTORY_SEPARATOR . "joomla" . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "category.php"
);

// Include HTML helpers path
JHtml::addIncludePath(CROWDFUNDING_PATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'html');

// Register Observers
JLoader::register(
    "CrowdFundingObserverReward",
    CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR .DIRECTORY_SEPARATOR. "tables" .DIRECTORY_SEPARATOR. "observers" .DIRECTORY_SEPARATOR. "reward.php"
);
JObserverMapper::addObserverClassToClass('CrowdFundingObserverReward', 'CrowdFundingTableReward', array('typeAlias' => 'com_crowdfunding.reward'));

// Prepare logger
$registry = JRegistry::getInstance("com_crowdfunding");
/** @var  $registry Joomla\Registry\Registry */

$registry->set("logger.table", "#__crowdf_logs");
$registry->set("logger.file", "com_crowdfunding.php");

// Load library language
$lang = JFactory::getLanguage();
$lang->load('lib_crowdfunding', CROWDFUNDING_PATH_LIBRARY);
