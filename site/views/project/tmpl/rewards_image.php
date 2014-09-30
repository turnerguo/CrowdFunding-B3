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
?>
<div class="js-reward-image-wrapper" >
    <div class="thumbnail">
        <?php 
        $rewardId   = JArrayHelper::getValue($this->formItem, "id");
        $imageThumb = JArrayHelper::getValue($this->formItem, "image_thumb");
        
        if (!empty($imageThumb)) {
            $rewardImage = $this->rewardsImagesUri."/".$imageThumb;
            $displayRemoveButton = "";
        } else {
            $rewardImage = "media/com_crowdfunding/images/no_image.png";
            $displayRemoveButton = 'style="display: none;';
        }

        echo JHtml::_('crowdfunding.rewardImage', $rewardImage, $rewardId, $this->params->get("rewards_image_thumb_width", 200), $this->params->get("rewards_image_thumb_height", 200));
        ?>
    </div>
    
    <?php if (!$this->debugMode) {?>
    <input type="file" name="images[<?php echo (int)$this->formItem["id"]?>]" value="" />
    
	<a href="javascript: void(0);" class="btn btn-danger js-btn-remove-reward-image" <?php echo $displayRemoveButton; ?> data-reward-id="<?php echo $rewardId;?>">
    	<i class="icon-trash icon-white"></i> 
    	<?php echo JText::_("COM_CROWDFUNDING_REMOVE")?>
	</a>
	<?php } ?>
	
</div>