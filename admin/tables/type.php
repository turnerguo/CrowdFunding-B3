<?php
/**
 * @package      CrowdFunding
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

class CrowdFundingTableType extends JTable {
    
    public function __construct($db) {
        parent::__construct('#__crowdf_types', 'id', $db);
    }
    
}