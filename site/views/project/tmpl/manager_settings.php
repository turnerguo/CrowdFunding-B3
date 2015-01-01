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
<?php if (!$this->item->published) { ?>
    <a class="btn btn-large" href="<?php echo JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug)); ?>">
        <i class="icon-eye-open icon-white"></i>
        <?php echo JText::_("COM_CROWDFUNDING_PREVIEW");?>
    </a>
    <a class="btn btn-primary btn-large" id="js-btn-project-publish" href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=projects.savestate&id=".(int)$this->item->id."&state=1&".JSession::getFormToken()."=1&return=".base64_encode($this->returnUrl)); ?>">
        <i class="icon-ok-circle icon-white"></i>
        <?php echo JText::_("COM_CROWDFUNDING_LAUNCH");?>
    </a>
    <p class="alert alert-info mt10">
        <i class="icon-info-sign"></i>
        <?php echo JText::_("COM_CROWDFUNDING_NOTE_LAUNCH_PROJECT"); ?>
    </p>
<?php } else { ?>

    <a class="btn btn-primary btn-large" id="js-btn-project-unpublish" href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=projects.savestate&id=".(int)$this->item->id."&state=0&".JSession::getFormToken()."=1&return=".base64_encode($this->returnUrl)); ?>">
        <i class="icon-ok-circle icon-white"></i>
        <?php echo JText::_("COM_CROWDFUNDING_STOP");?>
    </a>

<?php } ?>
