<?php
/**
 * @package      CrowdFunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
 
// no direct access
defined('_JEXEC') or die; 
?>
<div class="cfmdetails<?php echo $moduleclassSfx; ?>">
    <ul class="thumbnails">
      <?php if($project->getId()) {?>
      <li>
        <div class="thumbnail">
          <img src="<?php echo $imageFolder."/".$project->getImage();?>" alt="<?php echo htmlspecialchars($project->getTitle(), ENT_QUOTES, "UTF-8");?>" width="200" height="200">
          <div class="caption">
            <h3><a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($project->getSlug(), $project->getCatSlug())); ?>"><?php echo htmlspecialchars($project->getTitle(), ENT_QUOTES, "UTF-8");?></a></h3>
            <span class="cf-founder">by 
                <?php if(!empty($socialProfileLink)){ ?>
                <a href="<?php echo $socialProfileLink;?>"><?php echo $user->name; ?></a>
                <?php } else {?>
                <?php echo $user->name; ?>
                <?php }?>
            </span>
            <p><?php echo htmlspecialchars($project->getShortDesc(), ENT_QUOTES, "UTF-8");?></p>
            <?php echo JHtml::_("crowdfunding.progressbar", $fundedPercents, $project->getDaysLeft(), $project->getFundingType());?>
            
            <div class="row-fluid">
            	<div class="span4">
            	<div><strong><?php echo $project->getFundedPercents();?>%</strong></div>
            	<?php echo strtoupper( JText::_("MOD_CROWDFUNDINGDETAILS_FUNDED") );?>
            	</div>
            	<div class="span4">
            	<div><strong><?php echo $raised;?></strong></div>
            	<?php echo strtoupper( JText::_("MOD_CROWDFUNDINGDETAILS_RAISED") );?>
            	</div>
            	<div class="span4">
            	<div><strong><?php echo $project->getDaysLeft();?></strong></div>
            	<?php echo strtoupper( JText::_("MOD_CROWDFUNDINGDETAILS_DAYS_LEFT") );?>
            	</div>
            </div>
          </div>
        </div>
      </li>
      <?php } ?>
    </ul>
</div>