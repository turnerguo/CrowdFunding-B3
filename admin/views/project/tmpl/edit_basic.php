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
<div class="width-50 fltlft">
    <fieldset class="adminform">
        <ul class="adminformlist">
            <li><?php echo $this->form->getLabel('title'); ?>
            <?php echo $this->form->getInput('title'); ?></li>
            
            <li><?php echo $this->form->getLabel('alias'); ?>
            <?php echo $this->form->getInput('alias'); ?></li>
            
            <li><?php echo $this->form->getLabel('catid'); ?>
            <?php echo $this->form->getInput('catid'); ?></li>
            
            <li><?php echo $this->form->getLabel('type_id'); ?>
            <?php echo $this->form->getInput('type_id'); ?></li>
            
            <li><?php echo $this->form->getLabel('published'); ?>
            <?php echo $this->form->getInput('published'); ?></li>
               
            <li><?php echo $this->form->getLabel('approved'); ?>
            <?php echo $this->form->getInput('approved'); ?></li>

            <li><?php echo $this->form->getLabel('image'); ?>
            <?php echo $this->form->getInput('image'); ?></li>
            
            <li><?php echo $this->form->getLabel('id'); ?>
            <?php echo $this->form->getInput('id'); ?></li>
        </ul>
        
        <div class="clr"></div>
        <?php echo $this->form->getLabel('short_desc'); ?>
        <div class="clr"></div>
        <?php echo $this->form->getInput('short_desc'); ?>
        <div class="clr"></div>
        
    </fieldset>
</div>

<div class="width-50 fltlft">
    <?php if(!empty($this->item->image)) {?>
    <img src="<?php echo $this->imagesUrl."/".$this->item->image; ?>" />
    
    <div class="clearfix"></div>
    <br />
    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=project.removeImage&image_type=main&id=".(int)$this->item->id."&".JSession::getFormToken()."=1");?>" class="btn btn-danger">
        <i class="icon-trash icon-white"></i>
        <?php echo JText::_("COM_CROWDFUNDING_REMOVE_IMAGE");?>
    </a>
    <?php } else { ?>
    <img src="../media/com_crowdfunding/images/no_image.png" />
    <?php }?>
</div>
<div class="clr"></div>
