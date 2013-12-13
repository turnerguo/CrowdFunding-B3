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
            <li><?php echo $this->form->getLabel('pitch_video'); ?>
            <?php echo $this->form->getInput('pitch_video'); ?></li>
            
            <li><?php echo $this->form->getLabel('pitch_image'); ?>
            <?php echo $this->form->getInput('pitch_image'); ?></li>
            
        </ul>
        
        <div class="clr"></div>
        <?php echo $this->form->getLabel('description'); ?>
        <div class="clr"></div>
        <?php echo $this->form->getInput('description'); ?>
        <div class="clr"></div>
        
    </fieldset>
</div>

<div class="width-50 fltlft">
    <?php if(!empty($this->item->pitch_image)) {?>
    <img src="<?php echo $this->imagesUrl."/".$this->item->pitch_image; ?>" />
    
    <div class="clearfix"></div>
    <br />
    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=project.removeImage&image_type=pitch&id=".(int)$this->item->id."&".JSession::getFormToken()."=1");?>" class="btn btn-danger">
        <i class="icon-trash icon-white"></i>
        <?php echo JText::_("COM_CROWDFUNDING_REMOVE_IMAGE");?>
    </a>
    
    <?php } else { ?>
    <img src="../media/com_crowdfunding/images/no_image_large.png" />
    <?php }?>
</div>

<div class="clr"></div>
<?php echo $this->loadTemplate("extraimages");?>

<div class="clr"></div>
