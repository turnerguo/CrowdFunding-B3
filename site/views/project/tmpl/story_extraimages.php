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
<div id="crowdf-extra-images">
    
    <h4><?php echo JText::_("COM_CROWDFUNDING_EXTRA_IMAGES"); ?></h4>
    
    <span class="btn fileinput-button">
        <i class="icon-upload"></i>
        <span><?php echo JText::_("COM_CROWDFUNDING_UPLOAD_IMAGES");?></span>
        <!-- The file input field used as target for the file upload widget -->
        <input id="js-extra-fileupload" type="file" name="files[]" data-url="<?php echo JText::_("index.php?option=com_crowdfunding&task=project.addExtraImage&format=raw");?>" multiple />
    </span>
    
</div>

<div id="js-extra-images-rows">
    <?php if(!empty($this->images)) {
        foreach( $this->images as $extraImage) {
    ?>
    <div class="row-fluid extra-image-row">
        <div class="span10">
            <img src="<?php echo $this->extraImagesUri."/".$extraImage->thumb;?>" />
        </div>
        <div class="span2">
            <a href="javascript: void(0);" class="btn btn-danger js-extra-image-remove" data-image-id="<?php echo (int)$extraImage->id; ?>"><?php echo JText::_("COM_CROWDFUNDING_REMOVE")?></a>
        </div>
    </div>

    <?php }
    }?>
</div>

<div class="row-fluid extra-image-row hide" id="js-extra-img-row">
    <div class="span10">
        <img src="" class="js-extra-img" />
    </div>
    <div class="span2">
        <a href="javascript: void(0);" class="btn btn-danger js-extra-image-remove"><?php echo JText::_("COM_CROWDFUNDING_REMOVE")?></a>
    </div>
</div>
