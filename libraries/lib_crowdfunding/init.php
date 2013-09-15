<?php
/**
 * @package      CrowdFunding
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

if(!defined("CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR")) {
    define("CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR. "components" . DIRECTORY_SEPARATOR ."com_crowdfunding");
}

if(!defined("CROWDFUNDING_PATH_COMPONENT_SITE")) {
    define("CROWDFUNDING_PATH_COMPONENT_SITE", JPATH_SITE . DIRECTORY_SEPARATOR. "components" . DIRECTORY_SEPARATOR ."com_crowdfunding");
}

if(!defined("CROWDFUNDING_PATH_LIBRARY")) {
    define("CROWDFUNDING_PATH_LIBRARY", JPATH_LIBRARIES . DIRECTORY_SEPARATOR. "crowdfunding");
}

if(!defined("ITPRISM_PATH_LIBRARY")) {
    define("ITPRISM_PATH_LIBRARY", JPATH_LIBRARIES . DIRECTORY_SEPARATOR. "itprism");
}

jimport('joomla.utilities.arrayhelper');

JLoader::register("CrowdFundingVersion", CROWDFUNDING_PATH_LIBRARY . DIRECTORY_SEPARATOR . "version.php");
JLoader::register("CrowdFundingConstants", CROWDFUNDING_PATH_LIBRARY . DIRECTORY_SEPARATOR . "constants.php");

// Helpers
JLoader::register("CrowdFundingCategories", CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "category.php");
JLoader::register("CrowdFundingHelper", CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "crowdfunding.php");
JLoader::register("CrowdFundingHelperRoute", CROWDFUNDING_PATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "route.php");

// ITPrism classes
JLoader::register("ITPrismErrors", ITPRISM_PATH_LIBRARY . DIRECTORY_SEPARATOR . "errors.php");