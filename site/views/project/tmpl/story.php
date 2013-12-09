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
<?php echo $this->loadTemplate("nav");?>
<div class="row-fluid">
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="projectForm" id="crowdf-story-form" class="form-validate" enctype="multipart/form-data">
        
        <div class="span12">
        
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('pitch_video'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('pitch_video'); ?></div>
				<span class="help-block"><?php echo JText::_("COM_CROWDFUNDING_FIELD_FUNDING_VIDEO_HELP_BLOCK");?></span>
            </div>
                
            <?php echo $this->form->getLabel('pitch_image'); ?>
            <div class="fileupload fileupload-new" data-provides="fileupload">
                <span class="btn btn-file">
                    <i class="icon-upload"></i>
                    <span class="fileupload-new"><?php echo JText::_("COM_CROWDFUNDING_SELECT_FILE");?></span>
                    <span class="fileupload-exists">
                        <?php echo JText::_("COM_CROWDFUNDING_CHANGE");?>
                    </span>
                <?php echo $this->form->getInput('pitch_image'); ?>
                </span>
                <span class="fileupload-preview"></span>
                <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
            </div>
            <span class="help-block">(PNG, JPG, or GIF - <?php echo $this->pWidth; ?> x <?php echo $this->pHeight; ?> pixels) </span>
            
            <?php if(!empty($this->pitchImage)) {?>
            <img src="<?php echo $this->imageFolder."/".$this->pitchImage;?>" class="img-polaroid" />
            <?php if(!$this->debugMode) {?>
            <div class="clearfix">&nbsp;</div>
        	<a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=story.removeImage&id=".$this->item->id."&".JSession::getFormToken()."=1");?>" class="btn btn-mini btn-danger">
        	   <i class="icon-trash icon-white"></i> 
        	   <?php echo JText::_("COM_CROWDFUNDING_REMOVE_IMAGE");?>
    	    </a>
        	<?php }?>
            <?php }?>
            
            <?php echo $this->form->getLabel('description'); ?>
            <?php echo $this->form->getInput('description'); ?>
        	<div class="clearfix"></div>
            
            <?php echo $this->form->getInput('id'); ?>
            <input type="hidden" name="task" value="story.save" />
            <?php echo JHtml::_('form.token'); ?>
            
            <div class="clearfix"></div>
            <?php if($this->params->get("extra_images", 0)){
                echo $this->loadTemplate("extraimages");
            }?>
            <div class="clearfix"></div>
            
            <button type="submit" class="btn margin-tb-15px" <?php echo $this->disabledButton;?>>
            	<i class="icon-ok icon-white"></i>
                <?php echo JText::_("COM_CROWDFUNDING_SAVE_AND_CONTINUE")?>
            </button>
        </div>
        
    </form>
</div>
<?php echo $this->version->backlink;?>