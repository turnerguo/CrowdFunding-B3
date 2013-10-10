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
<div class="cfbacking<?php echo $this->params->get("pageclass_sfx"); ?>">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>
	
	<div class="row-fluid">
		<div class="span12">
    		<?php 
    		if(strcmp("three_steps", $this->wizardType) == 0) {
                include $this->layoutsBasePath."/payment_wizard.php";
    		} else {
                include $this->layoutsBasePath."/payment_wizard_four_steps.php";
    		}?>	
    	</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			<h2><?php echo JText::_("COM_CROWDFUNDING_LOGIN");?></h2>
			
			<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post">

        		<fieldset>
        			<?php foreach ($this->loginForm->getFieldset('credentials') as $field): ?>
        				<?php if (!$field->hidden): ?>
        					<div class="login-fields"><?php echo $field->label; ?>
        					<?php echo $field->input; ?></div>
        				<?php endif; ?>
        			<?php endforeach; ?>
        			<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
        			<div class="login-fields">
        				<label id="remember-lbl" for="remember"><?php echo JText::_('JGLOBAL_REMEMBER_ME') ?></label>
        				<input id="remember" type="checkbox" name="remember" class="inputbox" value="yes"  alt="<?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>" />
        			</div>
        			<?php endif; ?>
        			<button type="submit" class="button"><?php echo JText::_('JLOGIN'); ?></button>
        			<input type="hidden" name="return" value="<?php echo base64_encode($this->returnUrl); ?>" />
        			<?php echo JHtml::_('form.token'); ?>
        		</fieldset>
        	</form>
    	</div>
	</div>
	
	<div class="row-fluid">
    	<ul>
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