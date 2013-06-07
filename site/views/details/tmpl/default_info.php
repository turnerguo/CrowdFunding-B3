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
$fundedAmount = $this->currency->getAmountString($this->item->goal);
?>
<div class="cfinfo">
    <div class="cfinfo-raised">
    	<?php echo $this->currency->getAmountString($this->item->funded); ?>
    </div>
    <div class="cfinfo-raised-of">
        <?php echo JText::sprintf("COM_CROWDFUNDING_RAISED_OF", $fundedAmount);?>
	</div>
	<div class="progress progress-success">
   		<div class="bar" style="width: <?php echo JHtml::_("crowdfunding.funded", $this->item->funded_percents);?>%"></div>
    </div>
	<div class="cfinfo-days-raised">
    	<div class="cfinfo-days-wrapper">
    		<div class="cfinfo-days">
        		<img src="media/com_crowdfunding/images/clock.png" width="25" height="25" />
        		<?php echo $this->item->days_left;?>
    		</div>
    		<div class="tac fzmfwbu"><?php echo JText::_("COM_CROWDFUNDING_DAYS_LEFT");?></div>
		</div>
		<div class="cfinfo-percent-wrapper">
			<div class="cfinfo-percent">
    			<img src="media/com_crowdfunding/images/piggy-bank.png" width="27" height="20" />
        		<?php echo $this->item->funded_percents;?>%
    		</div>
    		<div class="tac fzmfwbu pt5"><?php echo JText::_("COM_CROWDFUNDING_FUNDED");?></div>
		</div>
	</div>
	<div class="clearfix"></div>
    <div class="cfinfo-funding-type">
        <?php echo JText::sprintf("COM_CROWDFUNDING_FUNDING_TYPE", $this->item->funding_type); ?>
    </div>
    
	<?php if(!$this->item->days_left) {?>
	<div class="well">
		<div class="cf-fund-result-state pull-center"><?php echo JHtml::_("crowdfunding.resultState", $this->item->funded_percents, $this->item->funding_type);?></div>
		<div class="cf-frss pull-center"><?php echo JHtml::_("crowdfunding.resultStateText", $this->item->funded_percents, $this->item->funding_type);?></div>
	</div>
	<?php } else {?>
	<div class="cfinfo-funding-action">
		<a class="btn btn-large btn-block" href="<?php echo JText::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug));?>"><?php echo JText::_("COM_CROWDFUNDING_INVEST_NOW"); ?></a>
	</div>
	<?php }?>
    
    <div class="cfinfo-funding-type-info">
    	<?php
    	
    	$endDate = JHtml::_('date', $this->item->funding_end, JText::_('DATE_FORMAT_LC3'));
    	
    	if($this->item->funding_type == "FIXED") {
    	    echo JText::sprintf("COM_CROWDFUNDING_FUNDING_TYPE_INFO_FIXED", $fundedAmount, $endDate);
    	} else {
    	    echo JText::sprintf("COM_CROWDFUNDING_FUNDING_TYPE_INFO_FLEXIBLE", $endDate);
    	}
    	?>
    </div>
</div>