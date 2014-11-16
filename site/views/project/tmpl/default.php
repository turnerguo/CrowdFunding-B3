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
            
            <?php echo $this->form->getLabel('image'); ?>
            <div class="fileupload fileupload-new" data-provides="fileupload">
                <span class="btn btn-file">
                    <i class="icon-upload"></i>
                    <span class="fileupload-new"><?php echo JText::_("COM_CROWDFUNDING_SELECT_FILE");?></span>
                    <span class="fileupload-exists">
                        <?php echo JText::_("COM_CROWDFUNDING_CHANGE");?>
                    </span>
                <?php echo $this->form->getInput('image'); ?>
                </span>
                <span class="fileupload-preview"></span>
                <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
            </div>
            
            <?php 
			if($this->params->get("project_terms", 0) AND $this->isNew) {
			    $termsUrl = $this->params->get("project_terms_url", "");
			?>
			<label class="checkbox">
            	<input type="checkbox" name="jform[terms]" value="1" required="required"> <?php echo (!$termsUrl) ? JText::_("COM_CROWDFUNDING_TERMS_AGREEMENT") : JText::sprintf("COM_CROWDFUNDING_TERMS_AGREEMENT_URL", $termsUrl);?>
            </label>
            <?php }?>
            
            <?php echo $this->form->getInput('id'); ?>
            <?php echo $this->form->getInput('location'); ?>
            
            <input type="hidden" name="task" value="project.save" />
            <?php echo JHtml::_('form.token'); ?>
            
            <div class="clearfix"></div>
            <button type="submit" class="btn margin-tb-15px" <?php echo $this->disabledButton;?>>
            	<i class="icon-ok icon-white"></i>
                <?php echo JText::_("COM_CROWDFUNDING_SAVE_AND_CONTINUE")?>
            </button>
        </form>
    </div>
    <?php if($this->imageSmall) {?>
    <div class="span6">
    	<img src="<?php echo $this->imageFolder."/".$this->imageSmall; ?>" class="img-polaroid" />
    	<?php if(!$this->debugMode) {?>
    	<div class="clearfix">&nbsp;</div>
    	<a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=project.removeImage&id=".$this->item->id."&".JSession::getFormToken()."=1");?>" class="btn btn-mini btn-danger" >
    		<i class="icon-trash icon-white"></i> 
    		<?php echo JText::_("COM_CROWDFUNDING_REMOVE_IMAGE");?>
		</a>
    	<?php }?>
    </div>
    <?php }?>
</div>