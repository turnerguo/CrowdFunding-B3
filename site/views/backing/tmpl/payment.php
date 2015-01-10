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
<div class="cfbacking<?php echo $this->params->get("pageclass_sfx"); ?>">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>
	
	<div class="row-fluid">
		<div class="span12">
    		<?php 
        	  if(strcmp("three_steps", $this->wizardType) == 0) {
        		  $layout      = new JLayoutFile('payment_wizard', $this->layoutsBasePath);
    		  } else {
        		  $layout      = new JLayoutFile('payment_wizard_four_steps', $this->layoutsBasePath);
    		  }
        	  echo $layout->render($this->layoutData);
    		?>	
    	</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			<h2><?php echo JText::_("COM_CROWDFUNDING_INVESTMENT_SUMMARY");?></h2>
			<div class="bs-docs-example">
				<p><?php 
				$amount = $this->amount->setValue($this->paymentAmount)->format();
				echo JText::sprintf("COM_CROWDFUNDING_INVESTMENT_AMOUNT", $amount); ?></p>
				<p><?php echo JText::_("COM_CROWDFUNDING_FUNDING_TYPE_".JString::strtoupper($this->item->funding_type));?></p>
				<p class="alert alert-info">
					<i class="icon-info-sign"></i>
				<?php
				$endDate = JHtml::_('date', $this->item->funding_end, JText::_('DATE_FORMAT_LC3'));
            	if($this->item->funding_type == "FIXED") {
                    $goal    = $this->currency->getAmountString($this->item->goal);
            	    echo JText::sprintf("COM_CROWDFUNDING_FUNDING_TYPE_INFO_FIXED", $goal, $endDate);
            	} else {
            	    echo JText::sprintf("COM_CROWDFUNDING_FUNDING_TYPE_INFO_FLEXIBLE", $endDate);
            	}
				?></p>
			</div>
			
			<?php if($this->rewardsEnabled) {?>
			<h2><?php echo JText::_("COM_CROWDFUNDING_SELECTED_REWARD");?></h2>
			<div class="bs-docs-example">
			<?php if(!$this->paymentSession->rewardId) {?>
				<p><?php echo JText::_("COM_CROWDFUNDING_NO_SELECTED_REWARD");?></p>
			<?php } else { ?>
				<h4><?php echo $this->escape($this->reward->getTitle());?></h4>
				<p><?php echo $this->escape($this->reward->getDescription());?></p>
			<?php } ?>
			</div>
			<?php } ?>

			<?php if(!empty($this->item->event->onBeforePaymentAuthorize)) { ?>
				<h2><?php echo JText::_("COM_CROWDFUNDING_PAYMENTS_NOT_ALLOWED");?></h2>
				<div class="well">
					<?php echo $this->item->event->onBeforePaymentAuthorize;?>
				</div>
			<?php } ?>

			<?php if(!empty($this->item->event->onProjectPayment)) { ?>
			<h2><?php echo JText::_("COM_CROWDFUNDING_PAYMENT_METHODS");?></h2>
			<div class="bs-docs-example">
				<?php echo $this->item->event->onProjectPayment;?>
			</div>
			<?php } ?>

    	</div>
	</div>
</div>
<div class="clearfix">&nbsp;</div>