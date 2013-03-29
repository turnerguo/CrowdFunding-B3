<?php
/**
 * @package      ITPrism Components
 * @subpackage   CrowdFunding
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

// Prepare availability number
$availability = JArrayHelper::getValue($this->formItem,  "number", 0);
if(!$availability) {
    $availability = "";
}

// Prepare delivery date
$deliveryDate = JArrayHelper::getValue($this->formItem,  "delivery", null);
if($deliveryDate) {
    $date = new JDate($deliveryDate);
    $date = $date->toUnix();
    if($date < 0) {
        $deliveryDate = null;
    } 
}
?>
<div class="row-fluid reward-form" id="reward_box_<?php echo $this->formIndex;?>">
    <div class="span2 reward-form-help"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_REWARD"); ?></div>
    <div class="span10">
    	<label class="hasTip" for="reward_amount_<?php echo $this->formIndex;?>" title="<?php echo JText::_("COM_CROWDFUNDING_REWARDS_AMOUNT_DESC");?>"><?php echo JText::_("COM_CROWDFUNDING_AMOUNT"); ?><span class="star">&nbsp;*</span></label>
        <div class="input-prepend input-append">
        	<?php if(!empty($this->currency["symbol"])){?>
            <span class="add-on"><?php echo $this->currency["symbol"];?></span>
            <?php }?>
            <input name="rewards[<?php echo $this->formIndex;?>][amount]" id="reward_amount_<?php echo $this->formIndex;?>" type="text" value="<?php echo JArrayHelper::getValue($this->formItem,  "amount")?>" class="span12" />
            <span class="add-on"><?php echo $this->currency["abbr"];?></span>
        </div>
        
        <label class="hasTip" for="reward_title_<?php echo $this->formIndex;?>" title="<?php echo JText::_("COM_CROWDFUNDING_REWARDS_TITLE_DESC");?>"><?php echo JText::_("COM_CROWDFUNDING_TITLE"); ?><span class="star">&nbsp;*</span></label>
        <input name="rewards[<?php echo $this->formIndex;?>][title]" id="reward_title_<?php echo $this->formIndex;?>" type="text" class="input-xlarge" value="<?php echo JArrayHelper::getValue($this->formItem,  "title")?>" />
        
        <label class="hasTip" for="reward_description_<?php echo $this->formIndex;?>" title="<?php echo JText::_("COM_CROWDFUNDING_REWARDS_DESCRIPTION_DESC");?>"><?php echo JText::_("COM_CROWDFUNDING_DESCRIPTION"); ?><span class="star">&nbsp;*</span></label>
        <textarea name="rewards[<?php echo $this->formIndex;?>][description]" id="reward_description_<?php echo $this->formIndex;?>" rows="6" class="input-xlarge"><?php echo JArrayHelper::getValue($this->formItem,  "description")?></textarea>
        
        <label class="hasTip" for="reward_number_<?php echo $this->formIndex;?>" title="<?php echo JText::_("COM_CROWDFUNDING_REWARDS_AVAIABLE_DESC");?>"><?php echo JText::_("COM_CROWDFUNDING_AVAIABLE"); ?></label>
        <input name="rewards[<?php echo $this->formIndex;?>][number]" id="reward_number_<?php echo $this->formIndex;?>" type="text" class="input-xlarge" value="<?php echo $availability; ?>" />
        
        <label class="hasTip" for="reward_delivery_<?php echo $this->formIndex;?>" title="<?php echo JText::_("COM_CROWDFUNDING_REWARDS_ESTIMATED_DELIVERY_DESC");?>"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_ESTIMATED_DELIVERY");?></label>
        <?php echo JHtml::_('calendar', $deliveryDate, "rewards[".$this->formIndex."][delivery]", "reward_delivery_".$this->formIndex);?>
        
        <input name="rewards[<?php echo $this->formIndex;?>][id]" type="hidden" value="<?php echo JArrayHelper::getValue($this->formItem,  "id", 0)?>" />
        
        <?php if(!$this->debugMode) {?>
        <a href="#" class="btn btn_remove_reward" data-reward-id="<?php echo JArrayHelper::getValue($this->formItem,  "id")?>" data-index-id="<?php echo $this->formIndex;?>" >
        	<i class="icon-trash"></i> 
        	<?php echo JText::_("COM_CROWDFUNDING_REMOVE")?>
    	</a>
    	<?php }?>
    </div>
</div>
