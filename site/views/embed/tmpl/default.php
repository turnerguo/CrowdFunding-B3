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

// no direct access
defined('_JEXEC') or die;
$code = '<iframe src="'.$this->embedLink.'" width="280px" height="560px" frameborder="0" scrolling="no"></iframe>';
?>
<div class="row-fluid<?php echo $this->params->get("pageclass_sfx"); ?>">
	
	<h2><?php echo JText::_("COM_CROWDFUNDING_WIDGET"); ?></h2>
	<p><?php echo JText::_("COM_CROWDFUNDING_WIDGET_HELP"); ?></p>
	<div class="row-fluid">
    	<div class="span8">
        	<textarea class="embed-code"><?php echo $this->escape($code);?></textarea>
    	</div>
    	
    	<div class="span4">
    		<?php echo $this->loadTemplate("widget");?>
    	</div>
	</div>
	
</div>
<div class="clearfix">&nbsp;</div>
<?php echo $this->version->backlink;?>