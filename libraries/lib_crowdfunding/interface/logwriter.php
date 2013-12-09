<?php
/**
 * @package		 CrowdFunding
 * @subpackage	 Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

interface CrowdFundingLogWriter {
    
    public function setTitle($title);
    public function setType($type);
    public function setData($data);
    public function setDate($date);
    public function store();
    
}