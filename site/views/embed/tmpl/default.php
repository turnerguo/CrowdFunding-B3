<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="embed-code<?php echo $this->params->get("pageclass_sfx"); ?>">
	
	<div class="row-fluid">
    	<div class="span11">
    	    <h2><?php echo JText::_("COM_CROWDFUNDING_WIDGET"); ?></h2>
	        <p><?php echo JText::_("COM_CROWDFUNDING_WIDGET_HELP"); ?></p>
        	<textarea class="embed-code"><?php echo $this->escape($this->embedCode);?></textarea>
    	</div>
	</div>
	
</div>
<div class="clearfix">&nbsp;</div>