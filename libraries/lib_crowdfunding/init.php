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
    define("CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . "/components/com_crowdfunding");
}

if (!defined("CROWDFUNDING_PATH_COMPONENT_SITE")) {
    define("CROWDFUNDING_PATH_COMPONENT_SITE", JPATH_SITE . "/components/com_crowdfunding");
}

if (!defined("CROWDFUNDING_PATH_LIBRARY")) {
    define("CROWDFUNDING_PATH_LIBRARY", JPATH_LIBRARIES . "/crowdfunding");
}

// Register version and constants
JLoader::register("CrowdFundingVersion", CROWDFUNDING_PATH_LIBRARY . "/version.php");
JLoader::register("CrowdFundingConstants", CROWDFUNDING_PATH_LIBRARY . "/constants.php");

// Register some helpers
JLoader::register("CrowdFundingHelper", CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR . "/helpers/crowdfunding.php");
JLoader::register("CrowdFundingHelperRoute", CROWDFUNDING_PATH_COMPONENT_SITE . "/helpers/route.php");

// Register the most used classes
JLoader::register("CrowdFundingCategories", CROWDFUNDING_PATH_LIBRARY . "/categories.php");
JLoader::register("CrowdFundingDate", CROWDFUNDING_PATH_LIBRARY . "/date.php");
JLoader::register("CrowdFundingEmail", CROWDFUNDING_PATH_LIBRARY . "/email.php");
JLoader::register("CrowdFundingCurrency", CROWDFUNDING_PATH_LIBRARY . "/currency.php");
JLoader::register("CrowdFundingAmount", CROWDFUNDING_PATH_LIBRARY . "/amount.php");

// Register some Joomla! classes
JLoader::register('JHtmlString', JPATH_LIBRARIES . "/joomla/html/html/string.php");
JLoader::register("JHtmlCategory", JPATH_LIBRARIES . "/joomla/html/html/category.php");

// Include HTML helpers path
JHtml::addIncludePath(CROWDFUNDING_PATH_COMPONENT_SITE . '/helpers/html');

// Register Observers
JLoader::register("CrowdFundingObserverReward", CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR . "/tables/observers/reward.php");
JObserverMapper::addObserverClassToClass('CrowdFundingObserverReward', 'CrowdFundingTableReward', array('typeAlias' => 'com_crowdfunding.reward'));

// Prepare logger
$registry = JRegistry::getInstance("com_crowdfunding");
/** @var  $registry Joomla\Registry\Registry */

$registry->set("logger.table", "#__crowdf_logs");
$registry->set("logger.file", "com_crowdfunding.php");

// Load library language
$lang = JFactory::getLanguage();
$lang->load('lib_crowdfunding', CROWDFUNDING_PATH_LIBRARY);
