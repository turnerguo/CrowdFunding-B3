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

<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('title'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('catid'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('catid'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('type_id'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('type_id'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
    <div class="controls">
        <div class="fileupload fileupload-new" data-provides="fileupload">
        <span class="btn btn-file">
            <span class="fileupload-new"><?php echo JText::_("COM_CROWDFUNDING_SELECT_FILE"); ?></span>
            <span class="fileupload-exists"><?php echo JText::_("COM_CROWDFUNDING_CHANGE"); ?></span>
            <?php echo $this->form->getInput('image'); ?>
        </span>
            <span class="fileupload-preview"></span>
            <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
        </div>
    </div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('published'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('approved'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('approved'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('id'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('short_desc'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('short_desc'); ?></div>
</div>