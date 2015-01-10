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
<div class="row-fluid reward-form" id="reward_tmpl" style="display: none;">
    <div class="span2 reward-form-help"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_REWARD"); ?></div>
    <div class="span10">
    	<label for="reward_amount_d" id="reward_amount_label_d"><?php echo JText::_("COM_CROWDFUNDING_AMOUNT"); ?><span class="star">&nbsp;*</span></label>
        <div class="input-prepend input-append">
        	<?php if($this->currency->getSymbol()){?>
            <span class="add-on"><?php echo $this->currency->getSymbol();?></span>
            <?php }?>
            <input name="rewards[][amount]" id="reward_amount_d" type="text" value="" />
            <span class="add-on"><?php echo $this->currency->getAbbr();?></span>
        </div>
        
        <label for="reward_title_d" id="reward_title_title_d"><?php echo JText::_("COM_CROWDFUNDING_TITLE"); ?><span class="star">&nbsp;*</span></label>
        <input name="rewards[][title]" id="reward_title_d" type="text" class="input-xlarge" value="" />
        
        <label for="reward_description_d" id="reward_description_title_d"><?php echo JText::_("COM_CROWDFUNDING_DESCRIPTION"); ?><span class="star">&nbsp;*</span></label>
        <textarea name="rewards[][description]" id="reward_description_d" rows="6" class="input-xlarge"></textarea>
        
        <label for="reward_number_d" id="reward_number_title_d"><?php echo JText::_("COM_CROWDFUNDING_AVAIABLE");?></label>
        <input name="rewards[][number]" id="reward_number_d" type="text" class="input-xlarge" value="" />
        
        <label for="reward_delivery_d" id="reward_delivery_title_d"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_ESTIMATED_DELIVERY");?></label>
        <?php echo JHtml::_('calendar', "", "rewards[][delivery]", "reward_delivery_d", $this->dateFormatCalendar);?>
        
        <input name="rewards[][id]" type="hidden" value="" id="reward_id_d" />
        <?php if(!$this->debugMode) {?>
        <a href="#" class="btn btn-danger btn_remove_reward" id="reward_remove_d" data-reward-id="0" data-index-id="0" >
            <i class="icon-trash icon-white"></i> 
            <?php echo JText::_("COM_CROWDFUNDING_REMOVE_REWARD")?>
        </a>
        <?php }?>
    </div>
</div>
