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
<div class="row-fluid">
    <div class="span6 form-horizontal">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" >
        
            <fieldset>
                
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('txn_amount'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('txn_amount'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('txn_currency'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('txn_currency'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('service_provider'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('service_provider'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('txn_status'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('txn_status'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('txn_id'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('txn_id'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('investor_id'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('investor_id'); ?></div>
                </div>
                
            </fieldset>
        
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
