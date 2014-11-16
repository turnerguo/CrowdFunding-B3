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
<div class="embed-email<?php echo $this->params->get("pageclass_sfx"); ?>">
	
	<div class="row-fluid">
    	<div class="span11">
    	    <h2><?php echo JText::_("COM_CROWDFUNDING_SEND_TO_FRIEND"); ?></h2>
            <p><?php echo JText::_("COM_CROWDFUNDING_SEND_TO_FRIEND_HELP"); ?></p>
            <form method="post" action="<?php echo JRoute::_(CrowdFundingHelperRoute::getEmbedRoute($this->item->slug, $this->item->catslug)."&task=friendmail.send");?>" class="bs-docs-example cfbf" id="form-send-to-friend" autocomplete="off">
            		
            		<?php echo $this->form->getLabel('subject'); ?>
                    <?php echo $this->form->getInput('subject'); ?>
                    
            		<?php echo $this->form->getLabel('sender_name'); ?>
                    <?php echo $this->form->getInput('sender_name'); ?>
                    
                    <?php echo $this->form->getLabel('sender'); ?>
                    <?php echo $this->form->getInput('sender'); ?>
                    
                    <?php echo $this->form->getLabel('receiver'); ?>
                    <?php echo $this->form->getInput('receiver'); ?>
                    
                    <?php echo $this->form->getLabel('message'); ?>
                    <?php echo $this->form->getInput('message'); ?>
                    
                    <?php echo $this->form->getLabel('captcha'); ?>
                    <?php echo $this->form->getInput('captcha'); ?>
            		
            		<?php echo $this->form->getInput('id'); ?>
            		<?php echo JHtml::_('form.token'); ?> 
            		<button type="submit" class="button button-large"><?php echo JText::_("COM_CROWDFUNDING_SEND");?></button>
            		
            </form>
        	
    	</div>
    	
	</div>
	
</div>
<div class="clearfix">&nbsp;</div>