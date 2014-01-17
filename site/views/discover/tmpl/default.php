<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
$itemSpan = (!empty($this->numberInRow)) ? round(12/$this->numberInRow) : 4;
?>
<div class="cfdiscover<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>
    
    <?php if(empty($this->items)) {?>
    <p class="alert alert-warning"><?php echo JText::_("COM_CROWDFUNDING_NO_ITEMS_MATCHING_QUERY"); ?></p>
    <?php }?>
    
    <?php if(!empty($this->displayFilters)) {
        echo $this->loadTemplate("filters");    
    }?>
    
    <?php if(!empty($this->items)) {?>
    <ul class="thumbnails">
    <?php foreach($this->items as $item) { ?>
    	  <?php
    	      // Prepare style class for based on project state.
    	      if(!$item->days_left) {
                $projectStateCSS = "cf-project-completed";
              } else {
                $projectStateCSS = "cf-project-active";
              }
               
    	      $raised   = $this->currency->getAmountString($item->funded, $this->params->get("locale_intl", 0)); 
    	      
    	      // Prepare the value that I am going to display
    	      $fundedPercents = JHtml::_("crowdfunding.funded", $item->funded_percents);
    	      
    	      // Prepare social profile.
    	      if(!empty($this->displayCreator)) {
        		  $socialProfile  = (!$this->socialProfiles) ? null : $this->socialProfiles->getLink($item->user_id);
        		  $profileName    = JHtml::_("crowdfunding.socialProfileLink", $socialProfile, $item->user_name);
    		  }
    	 ?>
          <li class="span<?php echo $itemSpan;?>">
            <div class="thumbnail <?php echo $projectStateCSS; ?>">
              <a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($item->slug, $item->catslug)); ?>">
                <?php if(!$item->image){?>
                <img src="<?php echo "media/com_crowdfunding/images/no_image.png";?>" alt="<?php echo $item->title;?>" width="<?php echo $this->imageWidth;?>" height="<?php echo $this->imageHeight;?>">
                <?php } else {?>
              	<img src="<?php echo $this->imageFolder."/".$item->image;?>" alt="<?php echo $item->title;?>" width="<?php echo $this->imageWidth;?>" height="<?php echo $this->imageHeight;?>">
              	<?php }?>
          	  </a>
              <div class="caption">
                <h3><a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($item->slug, $item->catslug)); ?>"><?php echo $item->title;?></a></h3>
                <?php if(!empty($this->displayCreator)) {?>
                <span class="cf-founder">
                    <?php echo JText::sprintf("COM_CROWDFUNDING_BY_S", $profileName); ?>
                </span>
                <?php } ?>
                
                <?php if($this->params->get("discover_display_description", true)) {?>
                <p><?php echo $item->short_desc;?></p>
                <?php }?>
                
                <?php echo JHtml::_("crowdfunding.progressbar", $fundedPercents, $item->days_left, $item->funding_type);?>
                <div class="row-fluid">
                	<div class="span4">
                	<div><strong><?php echo $item->funded_percents;?>%</strong></div>
                	<?php echo JString::strtoupper(JText::_("COM_CROWDFUNDING_FUNDED"));?>
                	</div>
                	<div class="span4">
                	<div><strong><?php echo $raised;?></strong></div>
                	<?php echo JString::strtoupper(JText::_("COM_CROWDFUNDING_RAISED"));?>
                	</div>
                	<div class="span4">
                	<div><strong><?php echo $item->days_left;?></strong></div>
                	<?php echo JString::strtoupper(JText::_("COM_CROWDFUNDING_DAYS_LEFT"));?>
                	</div>
                </div>
              </div>
            </div>
          </li>
    <?php }?>
    </ul>
    <?php }?>
    <div class="clearfix"></div>
    
    <div class="pagination">
        <?php if ($this->params->def('show_pagination_results', 1)) { ?>
            <p class="counter">
                <?php echo $this->pagination->getPagesCounter(); ?>
            </p>
        <?php } ?>
    
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
	
    <div class="clearfix">&nbsp;</div>
</div>
<div class="clearfix">&nbsp;</div>
<?php echo $this->version->backlink;?>