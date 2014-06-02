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
<div class="row-fluid">
    <div class="span6 form-horizontal">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm"
              id="adminForm" class="form-validate" enctype="multipart/form-data">

            <fieldset>

                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('title'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('amount'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('amount'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('number'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('number'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('distributed'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('distributed'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('delivery'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('delivery'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('published'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('id'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('description'); ?></div>
                </div>

            </fieldset>

            <?php echo $this->form->getInput('project_id'); ?>
            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>

    <?php if (!empty($this->item->id) AND !empty($this->item->image)) { ?>
        <div class="span6">
            <div class="thumbnail">
                <img src="<?php echo $this->rewardsImagesUri . "/" . $this->item->image; ?>"/>
            </div>
        </div>
    <?php } ?>
</div>
