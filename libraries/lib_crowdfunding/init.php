<?php
/**
 * @package      ITPrism Libraries
 * @subpackage   ITPrism Initializators
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * ITPrism Library is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('JPATH_PLATFORM') or die;

if(!defined("CROWDFUNDING_COMPONENT_ADMINISTRATOR")) {
    define("CROWDFUNDING_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR. "components" . DIRECTORY_SEPARATOR ."com_crowdfunding");
}

if(!defined("CROWDFUNDING_COMPONENT_SITE")) {
    define("CROWDFUNDING_COMPONENT_SITE", JPATH_SITE . DIRECTORY_SEPARATOR. "components" . DIRECTORY_SEPARATOR ."com_crowdfunding");
}

// Import libraries
jimport('joomla.utilities.arrayhelper');
jimport("crowdfunding.version");
jimport("itprism.errors");

// Register helpers
JLoader::register("CrowdFundingCategories", CROWDFUNDING_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "category.php");
JLoader::register("CrowdFundingHelper", CROWDFUNDING_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "crowdfunding.php");
JLoader::register("CrowdFundingHelperRoute", CROWDFUNDING_COMPONENT_SITE . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "route.php");
