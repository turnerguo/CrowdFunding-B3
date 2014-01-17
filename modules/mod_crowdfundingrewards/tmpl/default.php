<?php
/**
 * @package      CrowdFunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
 
// no direct access
defined('_JEXEC') or die; ?>
<?php if(count($rewards) > 0) {?>
<div class="cfrewards<?php echo $moduleclassSfx; ?>">

	<div class="reward_title center"><?php echo JText::_("MOD_CROWDFUNDINGREWARDS_PLEDGE_REWARDS");?></div>
	<?php foreach($rewards as $reward) {?>
    	<div class="reward">
    		<a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatSlug(), "default", $reward->id));?>">
    			<span class="ramount">
    			<?php 
    			$amount = $currency->getAmountString($reward->amount); 
    			echo JText::sprintf("MOD_CROWDFUNDINGREWARDS_INVEST_MORE", $amount ); ?>
    			</span>
    			<span class="rtitle"><?php echo htmlspecialchars($reward->title, ENT_QUOTES, "UTF-8"); ?></span>
    			<span class="rdesc"><?php echo htmlspecialchars($reward->description, ENT_QUOTES, "UTF-8"); ?></span>
    		</a>
    	</div>
	<?php }?>
</div>
<?php }?>