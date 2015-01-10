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
?>

<?php if (!empty($this->extraImages)) { ?>
    <div id="js-extra-images-rows">

        <?php foreach ($this->extraImages as $extraImage) { ?>

            <div class="row-fluid extra-image-row">
                <div class="span10">
                    <img src="<?php echo $this->extraImagesUri . "/" . $extraImage->thumb; ?>"/>
                </div>
                <div class="span2">
                    <a href="javascript: void(0);" class="btn btn-danger js-extra-image-remove"
                       data-image-id="<?php echo (int)$extraImage->id; ?>"
                       data-user-id="<?php echo (int)$this->item->user_id; ?>"><?php echo JText::_("COM_CROWDFUNDING_REMOVE") ?></a>
                </div>
            </div>

        <?php } ?>
    </div>
<?php } ?>