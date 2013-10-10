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
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
    <div class="width-40 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_("COM_CROWDFUNDING_PROJECT_DATA_LEGEND"); ?></legend>
            
            <ul class="adminformlist">
                <li><?php echo $this->form->getLabel('title'); ?>
                <?php echo $this->form->getInput('title'); ?></li>
                
                <li><?php echo $this->form->getLabel('alias'); ?>
                <?php echo $this->form->getInput('alias'); ?></li>
                
                <li><?php echo $this->form->getLabel('goal'); ?>
                <?php echo $this->form->getInput('goal'); ?></li>
                
                <li><?php echo $this->form->getLabel('funded'); ?>
                <?php echo $this->form->getInput('funded'); ?></li>
                
                <li><?php echo $this->form->getLabel('funding_type'); ?>
                <?php echo $this->form->getInput('funding_type'); ?></li>
                
                <li><?php echo $this->form->getLabel('pitch_video'); ?>
                <?php echo $this->form->getInput('pitch_video'); ?></li>
                
                <li><?php echo $this->form->getLabel('catid'); ?>
                <?php echo $this->form->getInput('catid'); ?></li>
                
                <li><?php echo $this->form->getLabel('published'); ?>
                <?php echo $this->form->getInput('published'); ?></li>
                   
                <li><?php echo $this->form->getLabel('approved'); ?>
                <?php echo $this->form->getInput('approved'); ?></li>
    
                <li><?php echo $this->form->getLabel('id'); ?>
                <?php echo $this->form->getInput('id'); ?></li>
            </ul>
            
            <div class="clr"></div>
            <?php echo $this->form->getLabel('short_desc'); ?>
            <div class="clr"></div>
            <?php echo $this->form->getInput('short_desc'); ?>
            <div class="clr"></div>
            
            <div class="clr"></div>
            <?php echo $this->form->getLabel('description'); ?>
            <div class="clr"></div>
            <?php echo $this->form->getInput('description'); ?>
            <div class="clr"></div>
            
        </fieldset>
    </div>

    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
