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
defined('_JEXEC') or die;?>

<ul class="thumbnails">
  <?php if(isset($this->item)) {
      
    $raised         = $this->currency->getAmountString($this->item->funded); 
	
	// Prepare the value that I am going to display
	$fundedPercents = JHtml::_("crowdfunding.funded", $this->item->funded_percents);
	
	$user = JFactory::getUser($this->item->user_id);
	$socialProfile  = JHtml::_("crowdfunding.socialProfile", $this->socialPlatform, $user);
 ?>
  <li class="span12">
    <div class="thumbnail">
      <img src="<?php echo $this->imageFolder."/".$this->item->image;?>" alt="<?php echo $this->item->title;?>" width="200" height="200">
      <div class="caption">
        <h3><a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug)); ?>"><?php echo $this->item->title;?></a></h3>
        <span class="cf-founder">by 
            <?php if(!empty($socialProfile)){ ?>
            <a href="<?php echo $socialProfile;?>"><?php echo $this->item->user_name; ?></a>
            <?php } else {?>
            <?php echo $this->item->user_name; ?>
            <?php }?>
        </span>
        <p><?php echo $this->item->short_desc;?></p>
        <?php echo JHtml::_("crowdfunding.progressbar", $fundedPercents, $this->item->days_left, $this->item->funding_type);?>
        
        <div class="row-fluid">
        	<div class="span4">
        	<div><strong><?php echo $this->item->funded_percents;?>%</strong></div>
        	<?php echo strtoupper( JText::_("COM_CROWDFUNDING_FUNDED") );?>
        	</div>
        	<div class="span4">
        	<div><strong><?php echo $raised;?></strong></div>
        	<?php echo strtoupper( JText::_("COM_CROWDFUNDING_RAISED") );?>
        	</div>
        	<div class="span4">
        	<div><strong><?php echo $this->item->days_left;?></strong></div>
        	<?php echo strtoupper( JText::_("COM_CROWDFUNDING_DAYS_LEFT") );?>
        	</div>
        </div>
      </div>
    </div>
  </li>
  <?php } ?>
</ul>