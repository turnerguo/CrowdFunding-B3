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
<div class="width-100 fltlft">
    <fieldset class="adminform">
        <ul class="adminformlist">
            <li><?php echo $this->form->getLabel('goal'); ?>
            <?php echo $this->form->getInput('goal'); ?></li>
            
            <li><?php echo $this->form->getLabel('funded'); ?>
            <?php echo $this->form->getInput('funded'); ?></li>
            
            <li><?php echo $this->form->getLabel('funding_type'); ?>
            <?php echo $this->form->getInput('funding_type'); ?></li>
            
        </ul>
        
        <div class="clr"></div>
        
        <div>
        	<label title="<?php echo JText::_("COM_CROWDFUNDING_FIELD_FUNDING_DURATION_DESC");?>" class="hasTip required" for="jform_funding_duration_type" id="jform_funding_duration_type-lbl">
        	<?php echo JText::_("COM_CROWDFUNDING_FIELD_FUNDING_DURATION");?>
        	</label>
        </div>
        
        <div class="clr"></div>
        <table>
            <?php if(empty($this->fundingDuration) OR (strcmp("days", $this->fundingDuration) == 0)) {?>
            <tr>
                <td>
                    <input type="radio" value="days" name="jform[funding_duration_type]" id="js-funding-duration-days" <?php echo $this->checkedDays;?>>
                    <?php echo $this->form->getLabel('funding_days'); ?>
                    <div class="clearfix"></div>
                    <?php echo $this->form->getInput('funding_days'); ?>
                </td>
            </tr>
            <?php }?>
            
            <?php if(empty($this->fundingDuration) OR (strcmp("date", $this->fundingDuration) == 0)) {?>
            <tr>
                <td>
                    <div class="clearfix"></div>
        			<input type="radio" value="date" name="jform[funding_duration_type]" id="js-funding-duration-date" <?php echo $this->checkedDate;?>>            
                    <?php echo $this->form->getLabel('funding_end'); ?>
                    <div class="clearfix"></div>
                    <?php echo $this->form->getInput('funding_end'); ?>
                </td>
            </tr>
            <?php }?>
        </table>
        
    </fieldset>
</div>
<div class="clr"></div>
