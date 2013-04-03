<?php
/**
 * @package      ITPrism Components
 * @subpackage   CrowdFunding
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die;
jimport('joomla.application.categories');

class CrowdFundingCategories extends JCategories {
    
	public function __construct($options = array()) {
		$options['table']     = '#__crowdf_projects';
		$options['extension'] = 'com_crowdfunding';
		parent::__construct($options);
	}
	
}