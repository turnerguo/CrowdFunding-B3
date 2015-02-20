<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="row-fluid">
	<div class="span12">
		<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="reportForm" id="reportForm" autocomplete="off" >

			<?php echo $this->form->getControlGroup('subject'); ?>
			<?php echo $this->form->getControlGroup('description'); ?>
			<?php echo $this->form->getControlGroup('email'); ?>

			<?php if(!$this->item){ ?>
				<?php echo $this->form->getControlGroup('project'); ?>
			<?php } else { ?>
				<?php echo $this->form->getControlGroup('title'); ?>
			<?php } ?>

			<?php echo $this->form->getControlGroup('captcha'); ?>
			<?php echo $this->form->getControlGroup('id'); ?>

			<?php echo JHtml::_('form.token'); ?>

			<input type="hidden" name="task" value="report.send" />
			<button type="submit" class="btn btn-primary">
				<?php echo JText::_("COM_CROWDFUNDING_SUBMIT");?>
			</button>

		</form>
	</div>
</div>
<div class="clearfix">&nbsp;</div>