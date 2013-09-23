<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
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
?>
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" >
    <div class="width-40 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_("COM_CROWDFUNDING_TRANSACTION_MANAGER_LEGEND"); ?></legend>
            
            <ul class="adminformlist">
                <li><?php echo $this->form->getLabel('txn_amount'); ?>
                <?php echo $this->form->getInput('txn_amount'); ?></li>
                
                <li><?php echo $this->form->getLabel('txn_currency'); ?>
                <?php echo $this->form->getInput('txn_currency'); ?></li>
                
                <li><?php echo $this->form->getLabel('service_provider'); ?>
                <?php echo $this->form->getInput('service_provider'); ?></li>
                
                <li><?php echo $this->form->getLabel('txn_status'); ?>
                <?php echo $this->form->getInput('txn_status'); ?></li>
                
                <li><?php echo $this->form->getLabel('txn_id'); ?>
                <?php echo $this->form->getInput('txn_id'); ?></li>
                
                <li><?php echo $this->form->getLabel('id'); ?>
                <?php echo $this->form->getInput('id'); ?></li>
                
                <li><?php echo $this->form->getLabel('investor_id'); ?>
                <?php echo $this->form->getInput('investor_id'); ?></li>
                
            </ul>
            
        </fieldset>
    </div>

    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
