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

$active = array("rewards" => false, "payment" => false, "share" => false);

switch($this->layoutData->layout) {
    case "default":
        $active["rewards"] = true;
        break;
    case "payment":
        $active["payment"] = true;
        break;
    case "share":
        $active["share"] = true;
        break;
}

?>
<div class="navbar">
    <div class="navbar-inner">
    	<a class="brand" href="javascript:void(0);"><?php echo JText::_("COM_CROWDFUNDING_INVESTMENT_PROCESS");?></a>

    	<ul class="nav">
            <li <?php echo ($active["rewards"]) ? 'class="active"' : '';?>>
            	<a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->layoutData->item->slug, $this->layoutData->item->catslug));?>">
            	(1) <?php echo JText::_("COM_CROWDFUNDING_STEP_PLEDGE_REWARDS");?>
            	</a>
            </li>
            
            <li <?php echo ($active["payment"]) ? 'class="active"' : '';?>>
            	<?php if(!empty($this->layoutData->paymentProcess->step1)){?> 
                <a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->layoutData->item->slug, $this->layoutData->item->catslug, "payment"));?>">
                (2) <?php echo JText::_("COM_CROWDFUNDING_STEP_PAY");?>
                </a>
                <?php }else {?>
                <a href="javascript: void(0);" class="disabled">(2) <?php echo JText::_("COM_CROWDFUNDING_STEP_PAY");?></a>
                <?php }?>
            </li>
            
            <li <?php echo ($active["share"]) ? 'class="active"' : '';?>>
            	<?php if(!empty($this->layoutData->paymentProcess->step2)){?> 
                <a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->layoutData->item->slug, $this->layoutData->item->catslug, "share"));?>">
                (3) <?php echo JText::_("COM_CROWDFUNDING_STEP_SHARE");?>
                </a>
                <?php }else {?>
                <a href="javascript: void(0);" class="disabled">(3) <?php echo JText::_("COM_CROWDFUNDING_STEP_SHARE");?></a>
                <?php }?>
            </li>
            
        </ul>
     </div>
</div>
