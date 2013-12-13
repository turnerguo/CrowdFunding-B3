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
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" >
    
    <div class="width-50 fltlft">
        <fieldset class="adminform">
            
            <ul class="adminformlist">
            	<li><?php echo $this->form->getLabel('subject'); ?>
                <?php echo $this->form->getInput('subject'); ?></li>
                
            	<li><?php echo $this->form->getLabel('sender_name'); ?>
                <?php echo $this->form->getInput('sender_name'); ?></li>
                
            	<li><?php echo $this->form->getLabel('sender_email'); ?>
                <?php echo $this->form->getInput('sender_email'); ?></li>
                
            	<li><?php echo $this->form->getLabel('title'); ?>
                <?php echo $this->form->getInput('title'); ?></li>
                
                <li><?php echo $this->form->getLabel('id'); ?>
                <?php echo $this->form->getInput('id'); ?></li>
            </ul>
            
            <div class="clr"></div>
            <?php echo $this->form->getLabel('body'); ?>
            <div class="clr"></div>
            <?php echo $this->form->getInput('body'); ?>
            <div class="clr"></div>
            
        </fieldset>
    </div>

    <div class="width-50 fltlft">
        <h3><?php echo JText::_("COM_CROWDFUNDING_INDICATORS_LIST");?></h3>
        <p class="small"><?php echo JText::_("COM_CROWDFUNDING_INDICATORS_INFO");?></p>
        <table>
            <tr>
                <td><strong>{SITE_NAME}</strong></td>
                <td><?php echo JText::_("COM_CROWDFUNDING_EMAIL_SITE_NAME");?></td>
            </tr>
            <tr>
                <td><strong>{SITE_URL}</strong></td>
                <td><?php echo JText::_("COM_CROWDFUNDING_EMAIL_SITE_URL");?></td>
            </tr>    
            <tr>
                <td><strong>{ITEM_TITLE}</strong></td>
                <td><?php echo JText::_("COM_CROWDFUNDING_EMAIL_ITEM_TITLE");?></td>
            </tr>
            <tr>
                <td><strong>{ITEM_URL}</strong></td>
                <td><?php echo JText::_("COM_CROWDFUNDING_EMAIL_ITEM_URL");?></td>
            </tr>
            <tr>
                <td><strong>{SENDER_NAME}</strong></td>
                <td><?php echo JText::_("COM_CROWDFUNDING_EMAIL_SENDER_NAME");?></td>
            </tr>
            <tr>
                <td><strong>{SENDER_EMAIL}</strong></td>
                <td><?php echo JText::_("COM_CROWDFUNDING_EMAIL_SENDER_EMAIL");?></td>
            </tr>
            <tr>
                <td><strong>{RECIPIENT_NAME}</strong></td>
                <td><?php echo JText::_("COM_CROWDFUNDING_EMAIL_RECIPIENT_NAME");?></td>
            </tr>
            <tr>
                <td><strong>{RECIPIENT_EMAIL}</strong></td>
                <td><?php echo JText::_("COM_CROWDFUNDING_EMAIL_RECIPIENT_EMAIL");?></td>
            </tr>
            <tr>
                <td><strong>{AMOUNT}</strong></td>
                <td><?php echo JText::_("COM_CROWDFUNDING_EMAIL_AMOUNT");?></td>
            </tr>
            <tr>
                <td><strong>{TRANSACTION_ID}</strong></td>
                <td><?php echo JText::_("COM_CROWDFUNDING_EMAIL_TRANSACTION_ID");?></td>
            </tr>
        </table>
        
        <p class="small"><?php echo JText::_("COM_CROWDFUNDING_EMAIL_EXTRA_LINE");?></p>
    </div>
    
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
