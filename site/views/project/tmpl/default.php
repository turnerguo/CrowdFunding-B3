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

if (strcmp("five_steps", $this->wizardType) == 0) {
    $layout      = new JLayoutFile('project_wizard', $this->layoutsBasePath);
} else {
    $layout      = new JLayoutFile('project_wizard_six_steps', $this->layoutsBasePath);
}
echo $layout->render($this->layoutData);
?>

<div class="row-fluid">
    <div class="span6">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="projectForm" id="js-cf-project-form" novalidate="novalidate" autocomplete="off" enctype="multipart/form-data" >
            
            <?php echo $this->form->getLabel('title'); ?>
            <?php echo $this->form->getInput('title'); ?>
            
            <?php echo $this->form->getLabel('short_desc'); ?>
            <?php echo $this->form->getInput('short_desc'); ?>
            
            <?php echo $this->form->getLabel('catid'); ?>
            <?php echo $this->form->getInput('catid'); ?>

            <?php echo $this->form->getLabel('location_preview'); ?>
            <?php echo $this->form->getInput('location_preview'); ?>
            
            <?php if(!empty($this->numberOfTypes)) {?>
                <?php echo $this->form->getLabel('type_id'); ?>
                <?php echo $this->form->getInput('type_id'); ?>
            <?php  } else { ?>
                <input type="hidden" name="jform[type_id]" value="0" />
            <?php }?>
            
            <?php 
			if($this->params->get("project_terms", 0) AND $this->isNew) {
			    $termsUrl = $this->params->get("project_terms_url", "");
			?>
			<label class="checkbox">
            	<input type="checkbox" name="jform[terms]" value="1" required="required"> <?php echo (!$termsUrl) ? JText::_("COM_CROWDFUNDING_TERMS_AGREEMENT") : JText::sprintf("COM_CROWDFUNDING_TERMS_AGREEMENT_URL", $termsUrl);?>
            </label>
            <?php }?>
            
            <?php echo $this->form->getInput('id'); ?>
            <?php echo $this->form->getInput('location_id'); ?>
            
            <input type="hidden" name="task" value="project.save" />
            <?php echo JHtml::_('form.token'); ?>
            
            <div class="clearfix"></div>
            <button type="submit" class="btn mtb_15_0" <?php echo $this->disabledButton;?>>
            	<i class="icon-ok icon-white"></i>
                <?php echo JText::_("COM_CROWDFUNDING_SAVE_AND_CONTINUE")?>
            </button>
        </form>
    </div>

    <div class="span6">
        <?php if(!$this->debugMode) {?>
        <div class="mb_15">
            <span class="btn fileinput-button">
                <i class="icon-upload"></i>
                <span><?php echo JText::_("COM_CROWDFUNDING_UPLOAD_IMAGE");?></span>
                <!-- The file input field used as target for the file upload widget -->
                <input id="js-thumb-fileupload" type="file" name="project_image" data-url="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=project.uploadImage&format=raw");?>"/>
            </span>

            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=project.removeImage&id=".$this->item->id."&".JSession::getFormToken()."=1");?>" id="js-btn-remove-image" class="btn btn-danger" style="display: <?php echo $this->displayRemoveButton; ?>">
                <i class="icon-trash icon-white"></i>
                <?php echo JText::_("COM_CROWDFUNDING_REMOVE_IMAGE");?>
            </a>

            <img src="media/com_crowdfunding/images/ajax-loader.gif" width="16" height="16" id="js-thumb-fileupload-loader" style="display: none;" />

            <div class="clearfix"></div>
            <div id="js-image-tools" class="mt10" style="display: none;">
                <a href="javascript: void(0);" class="btn btn-primary" id="js-crop-btn">
                    <i class="icon-ok-circle icon-white"></i>
                    <?php echo JText::_("COM_CROWDFUNDING_CROP_IMAGE");?>
                </a>

                <a href="javascript: void(0);" class="btn" id="js-crop-btn-cancel">
                    <i class="icon-ban-circle icon-white"></i>
                    <?php echo JText::_("COM_CROWDFUNDING_CANCEL");?>
                </a>
            </div>

        </div>
        <form action="<?php echo JRoute::_("index.php?option=com_crowdfunding");?>" method="post" id="js-image-tools-form">
            <input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />
        </form>
        <?php }?>

        <div id="js-fixed-dragger-cropper">
            <img src="<?php echo $this->imagePath; ?>" class="img-polaroid" id="js-thumb-img" />
        </div>

    </div>

</div>