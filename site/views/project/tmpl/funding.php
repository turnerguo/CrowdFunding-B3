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
<?php echo $this->loadTemplate("nav");?>
<div class="row-fluid">
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="projectForm" id="crowdf-funding-form" class="form-validate" autocomplete="off">
        
        <div class="row-fluid">
            <div class="span2"><?php echo $this->form->getLabel('goal'); ?></div>
            <div class="span10">
                <?php echo $this->form->getInput('goal'); ?>
                <?php if(!empty($this->maxAmount)) {?>
                <span class="help-block"><?php echo JText::sprintf("COM_CROWDFUNDING_MINIMUM_MAXIMUM_AMOUNT", $this->currency->getAmountString($this->minAmount, $this->params->get("locale_intl", 0)), $this->currency->getAmountString($this->maxAmount, $this->params->get("locale_intl", 0)));?></span>
                <?php } else {?>
                <span class="help-block"><?php echo JText::sprintf("COM_CROWDFUNDING_MINIMUM_AMOUNT", $this->currency->getAmountString($this->minAmount, $this->params->get("locale_intl", 0)));?></span>
                <?php }?>
            </div>
        </div>
        
        <div class="row-fluid">
            <div class="span2"><?php echo $this->form->getLabel('funding_type'); ?></div>
            <div class="span10"><?php echo $this->form->getInput('funding_type'); ?></div>
        </div>
    
    	<div class="row-fluid">
            <div class="span2">
            	<label title="<?php echo JHtml::tooltipText(JText::_("COM_CROWDFUNDING_FIELD_FUNDING_DURATION_DESC"));?>" class="hasTooltip required" for="jform_funding_duration_type" id="jform_funding_duration_type-lbl">
            	<?php echo JText::_("COM_CROWDFUNDING_FIELD_FUNDING_DURATION");?><span class="star">&nbsp;*</span>
            	</label>
            </div>
            
            <div class="span10">
                <?php if(empty($this->fundingDuration) OR (strcmp("days", $this->fundingDuration) == 0)) {?>
                    <input type="radio" value="days" name="jform[funding_duration_type]" id="js-funding-duration-days" <?php echo $this->checkedDays;?>>
                    <?php echo $this->form->getLabel('funding_days'); ?>
                    <div class="clearfix"></div>
                    <?php echo $this->form->getInput('funding_days'); ?>
                    <?php if(!empty($this->maxDays)) {?>
                    <span class="help-block"><?php echo JText::sprintf("COM_CROWDFUNDING_MINIMUM_MAXIMUM_DAYS", $this->minDays, $this->maxDays);?></span>
                    <?php } else {?>
                    <span class="help-block"><?php echo JText::sprintf("COM_CROWDFUNDING_MINIMUM_DAYS", $this->minDays);?></span>
                    <?php }?>
    			<?php }?>
    			
    			<?php if(empty($this->fundingDuration) OR (strcmp("date", $this->fundingDuration) == 0)) {?>
        			<div class="clearfix"></div>
        			<input type="radio" value="date" name="jform[funding_duration_type]" id="js-funding-duration-date" <?php echo $this->checkedDate;?>>            
                    <?php echo $this->form->getLabel('funding_end'); ?>
                    <div class="clearfix"></div>
                    <?php echo $this->form->getInput('funding_end'); ?>
                <?php }?>
            </div>
        </div>
        
        <?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="task" value="funding.save" />
        <?php echo JHtml::_('form.token'); ?>
        
        <div class="clearfix"></div>
        <button type="submit" class="btn margin-tb-15px" <?php echo $this->disabledButton;?>>
        	<i class="icon-ok icon-white"></i>
            <?php echo JText::_("COM_CROWDFUNDING_SAVE_AND_CONTINUE")?>
        </button>
    </form>
</div>
<?php echo $this->version->backlink;?>