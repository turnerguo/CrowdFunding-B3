<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;?>
<div class="row-fluid">
    <ul class="thumbnails">
      <?php if(isset($this->item)) {
        $raised         = $this->currency->getAmountString($this->item->funded);
        $fundedPercents = JHtml::_("crowdfunding.funded", $this->item->funded_percents);
    	
        // Get social platform and a link to the profile
        $socialProfile      = CrowdFundingHelper::getSocialProfile($this->item->user_id, $this->socialPlatform);
        $socialProfileLink  = (!$socialProfile) ? null : $socialProfile->getLink();
    	
     ?>
      <li class="span12">
        <div class="thumbnail">
          <img src="<?php echo $this->item->link_image;?>" alt="<?php echo $this->item->title;?>" width="200" height="200">
          <div class="caption">
            <h3><a href="<?php echo JRoute::_( CrowdFundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug) ); ?>" target="_blank"><?php echo $this->item->title;?></a></h3>
            <span class="cf-founder">
                by <?php echo JHtml::_("crowdfunding.socialProfileLink", $socialProfileLink, $this->item->user_name, array("target" => "_blank")); ?> 
            </span>
            <p><?php echo $this->item->short_desc;?></p>
            <div class="progress progress-success">
           		<div class="bar" style="width: <?php echo $fundedPercents;?>%"></div>
            </div>
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
</div>
    