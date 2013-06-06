<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined('_JEXEC') or die;?>
<?php if($this->isOwner) {?>
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="updatesForm" id="crowdf-updates-form" class="form-validate" autocomplete="off">
            
        <?php echo $this->form->getLabel('title'); ?>
        <?php echo $this->form->getInput('title'); ?>
        
        <?php echo $this->form->getLabel('description'); ?>
        <?php echo $this->form->getInput('description'); ?>
        
        <?php echo $this->form->getInput('id'); ?>
        <?php echo $this->form->getInput('project_id'); ?>
        
        <input type="hidden" name="task" value="update.save" />
        <?php echo JHtml::_('form.token'); ?>
        
        <div class="clearfix"></div>
        <button type="submit" class="button"><?php echo JText::_("JSAVE")?></button>
        <button type="submit" class="button" id="cf-updates-reset"><?php echo JText::_("COM_CROWDFUNDING_RESET")?></button>
</form>
<div class="hr margin-tb-15px"></div>
<?php }?>
<?php if(!empty($this->items)) {?>
<?php foreach($this->items as $item ) {?>
    <div class="cf-update-item" id="update<?php echo $item->id;?>">
    
    	<div class="cf-info-bar"> 
    		<div class="pull-left">
    			<?php echo $item->author; ?> | <?php echo JHTML::_('date', $item->record_date, JText::_('DATE_FORMAT_LC3')); ?>
    		</div>
        	<?php if($this->userId == $item->user_id ) {?>
        	<div class="pull-right">
        		<a href="javascript: void(0);" class="btn btn-mini upedit_btn" data-id="<?php echo $item->id;?>"><?php echo JText::_("COM_CROWDFUNDING_EDIT");?></a>
        		<a href="javascript: void(0);" class="btn btn-mini btn-danger upremove_btn" data-id="<?php echo $item->id;?>"><?php echo JText::_("COM_CROWDFUNDING_DELETE");?></a>
        	</div>
        	<?php }?>
    	</div>
    	<h3><?php echo $item->title?></h3>
    	<p><?php echo nl2br($item->description);?></p>
    </div>
    <?php }?>
    
<input type="hidden" value="<?php echo JText::_("COM_CROWDFUNDING_QUESTION_REMOVE_RECORD");?>" id="cf-hidden-question" />
<?php }?>