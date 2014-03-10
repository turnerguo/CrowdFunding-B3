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
?>
<div class="cfbacking<?php echo $this->params->get("pageclass_sfx"); ?>">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>
	
	<div class="row-fluid">
		<div class="span12">
    		<?php 
        	  if(strcmp("three_steps", $this->wizardType) == 0) {
        		  $layout      = new JLayoutFile('payment_wizard', $this->layoutsBasePath);
    		  } else {
        		  $layout      = new JLayoutFile('payment_wizard_four_steps', $this->layoutsBasePath);
    		  }
        	  echo $layout->render($this->layoutData);
    		?>	
    	</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			<h2><?php echo JText::_("COM_CROWDFUNDING_LOGIN");?></h2>
			
			<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="form-horizontal">

        		<fieldset class="well">
        			<?php foreach ($this->loginForm->getFieldset('credentials') as $field) : ?>
        				<?php if (!$field->hidden) : ?>
        					<div class="control-group">
        						<div class="control-label">
        							<?php echo $field->label; ?>
        						</div>
        						<div class="controls">
        							<?php echo $field->input; ?>
        						</div>
        					</div>
        				<?php endif; ?>
        			<?php endforeach; ?>
        			<div class="control-group">
        				<div class="controls">
        					<button type="submit" class="btn btn-primary"><?php echo JText::_('JLOGIN'); ?></button>
        				</div>
        			</div>
        			<input type="hidden" name="return" value="<?php echo base64_encode($this->returnUrl); ?>" />
        			<?php echo JHtml::_('form.token'); ?>
        		</fieldset>
        	</form>
    	</div>
	</div>
	
	<div class="row-fluid">
    	<ul class="nav nav-tabs nav-stacked">
    		<li>
    			<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
    			<?php echo JText::_('COM_CROWDFUNDING_LOGIN_RESET'); ?></a>
    		</li>
    		<li>
    			<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
    			<?php echo JText::_('COM_CROWDFUNDING_LOGIN_REMIND'); ?></a>
    		</li>
    		<?php
    		$usersConfig = JComponentHelper::getParams('com_users');
    		if ($usersConfig->get('allowUserRegistration')) : ?>
    		<li>
    			<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
    				<?php echo JText::_('COM_CROWDFUNDING_LOGIN_REGISTER'); ?></a>
    		</li>
    		<?php endif; ?>
    	</ul>
    </div>
</div>
<div class="clearfix">&nbsp;</div>
<?php echo $this->version->backlink;?>