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
defined('_JEXEC') or die;
?>
<div class="row-fluid reward-form" id="reward_tmpl" style="display: none;">
    <div class="span2 reward-form-help"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_REWARD"); ?></div>
    <div class="span10">
    	<label for="reward_amount_d" id="reward_amount_label_d"><?php echo JText::_("COM_CROWDFUNDING_AMOUNT"); ?><span class="star">&nbsp;*</span></label>
        <div class="input-prepend input-append">
        	<?php if(!empty($this->currency->symbol)){?>
            <span class="add-on"><?php echo $this->currency->symbol;?></span>
            <?php }?>
            <input name="rewards[][amount]" id="reward_amount_d" type="text" value="" />
            <span class="add-on"><?php echo $this->currency->abbr;?></span>
        </div>
        
        <label for="reward_title_d" id="reward_title_title_d"><?php echo JText::_("COM_CROWDFUNDING_TITLE"); ?><span class="star">&nbsp;*</span></label>
        <input name="rewards[][title]" id="reward_title_d" type="text" class="input-xlarge" value="" />
        
        <label for="reward_description_d" id="reward_description_title_d"><?php echo JText::_("COM_CROWDFUNDING_DESCRIPTION"); ?><span class="star">&nbsp;*</span></label>
        <textarea name="rewards[][description]" id="reward_description_d" rows="6" class="input-xlarge"></textarea>
        
        <label for="reward_number_d" id="reward_number_title_d"><?php echo JText::_("COM_CROWDFUNDING_AVAIABLE");?></label>
        <input name="rewards[][number]" id="reward_number_d" type="text" class="input-xlarge" value="" />
        
        <label for="reward_delivery_d" id="reward_delivery_title_d"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_ESTIMATED_DELIVERY");?></label>
        <?php echo JHtml::_('calendar', "", "rewards[][delivery]", "reward_delivery_d");?>
        
        <input name="rewards[][id]" type="hidden" value="" id="reward_id_d" />
        <?php if(!$this->debugMode) {?>
        <a href="#" class="btn btn-danger btn_remove_reward" id="reward_remove_d" data-reward-id="0" data-index-id="0" >
            <i class="icon-trash"></i> 
            <?php echo JText::_("COM_CROWDFUNDING_REMOVE")?>
        </a>
        <?php }?>
    </div>
</div>
