<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

JLoader::register("CrowdFundingModelProjectItem", CROWDFUNDING_PATH_COMPONENT_SITE . "/models/projectitem.php");

class CrowdFundingModelExtras extends CrowdFundingModelProjectItem
{

}
