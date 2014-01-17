<?php
/**
 * @package      CrowdFunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
 
// no direct access
defined('_JEXEC') or die; 

$width  = $componentParams->get("rewards_image_square_width", 50);
$height = $componentParams->get("rewards_image_square_height", 50);
?>
<?php if(count($rewards) > 0) {?>
<div class="cfrewards<?php echo $moduleclassSfx; ?>">

	<div class="reward_title center"><?php echo JText::_("MOD_CROWDFUNDINGREWARDS_PLEDGE_REWARDS");?></div>
	<?php foreach($rewards as $reward) {?>
    	<div class="reward">
    	
    	<?php if(!empty($reward->image_square)) { ?>
    	   <div class="row-fluid">
        	   <div class="span3">
        	    <?php
                    $thumb = $rewardsImagesUri."/".$reward->image_square;
                    $image = $rewardsImagesUri."/".$reward->image;
        	        echo CrowdFundingRewardsModuleHelper::image($thumb, $image, $width, $height);
        	    ?>
        	    </div>
        	    <div class="span9">
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
    		</div>
    	<?php } else {?>
    	   <a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatSlug(), "default", $reward->id));?>">
    			<span class="ramount">
    			<?php 
    			$amount = $currency->getAmountString($reward->amount); 
    			echo JText::sprintf("MOD_CROWDFUNDINGREWARDS_INVEST_MORE", $amount ); ?>
    			</span>
    			<span class="rtitle"><?php echo htmlspecialchars($reward->title, ENT_QUOTES, "UTF-8"); ?></span>
    			<span class="rdesc"><?php echo htmlspecialchars($reward->description, ENT_QUOTES, "UTF-8"); ?></span>
    		</a>
    	<?php }?>
    	</div>
	<?php }?>
</div>
<?php }?>