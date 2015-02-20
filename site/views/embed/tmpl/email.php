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
<div class="embed-email<?php echo $this->params->get("pageclass_sfx"); ?>">
	
	<div class="row-fluid">
    	<div class="span11">
    	    <h2><?php echo JText::_("COM_CROWDFUNDING_SEND_TO_FRIEND"); ?></h2>
            <p><?php echo JText::_("COM_CROWDFUNDING_SEND_TO_FRIEND_HELP"); ?></p>
            <form method="post" action="<?php echo JRoute::_("index.php?option=com_crowdfunding");?>" class="bs-docs-example mt_0" id="form-send-to-friend" autocomplete="off">

                <?php echo $this->form->getControlGroup('subject'); ?>
                <?php echo $this->form->getControlGroup('sender_name'); ?>
                <?php echo $this->form->getControlGroup('sender'); ?>
                <?php echo $this->form->getControlGroup('receiver'); ?>
                <?php echo $this->form->getControlGroup('message'); ?>
                <?php echo $this->form->getControlGroup('captcha'); ?>

                <?php echo $this->form->getInput('id'); ?>
                <?php echo JHtml::_('form.token'); ?>

                <input type="hidden" name="task" value="friendmail.send" />
                <button type="submit" class="btn btn-primary">
                    <?php echo JText::_("COM_CROWDFUNDING_SEND");?>
                </button>
            		
            </form>
        	
    	</div>
    	
	</div>
	
</div>