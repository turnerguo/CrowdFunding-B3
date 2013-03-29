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
defined('_JEXEC') or die;?>

<div class="cfrewards">

	<div class="reward_title center"><?php echo JText::_("COM_CROWDFUNDING_PLEDGE_REWARDS");?></div>
	<?php foreach($this->rewards as $reward) {?>
    	<div class="reward">
    		<a href="<?php echo JText::_("index.php?option=com_crowdfunding&view=backing&id=".(int)$this->item->id."&rid=".(int)$reward->id);?>">
    			<span class="ramount">
    			<?php 
    			$amount = JHtml::_("CrowdFunding.amount", $reward->amount, $this->currency);
    			echo JText::sprintf("COM_CROWDFUNDING_INVEST_MORE", $amount ); ?>
    			</span>
    			<span class="rtitle"><?php echo $this->escape($reward->title); ?></span>
    			<span class="rdesc"><?php echo $this->escape($reward->description); ?></span>
    		</a>
    	</div>
	<?php }?>
	
</div>