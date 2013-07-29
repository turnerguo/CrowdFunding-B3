<?php
/**
 * @package      CrowdFunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die; ?>
<div class="cfrewards<?php echo $moduleclassSfx; ?>">

	<div class="reward_title center"><?php echo JText::_("MOD_CROWDFUNDINGREWARDS_PLEDGE_REWARDS");?></div>
	<?php foreach($rewards as $reward) {?>
    	<div class="reward">
    		<a href="<?php echo JText::_(CrowdFundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatSlug(), "default", $reward->id));?>">
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