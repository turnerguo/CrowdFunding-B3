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
<div class="row-fluid<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>
    
    <?php foreach($this->items as $items) {?>
    <div class="row-fluid">
    	<ul class="thumbnails">
    	<?php for($i = 1, $max = 3; $i < 4; $i++) { ?>
    	<?php if(isset($items[$i])) {
    	    $raised   = JHtml::_("crowdfunding.amount", $items[$i]->funded, $this->currency);
    		
    		// Prepare the value that I am going to display
    		$fundedPercents = JHtml::_("crowdfunding.funded", $items[$i]->funded_percents);
    		$socialProfile  = JHtml::_("crowdfunding.socialProfile", $items[$i]->user_id, $this->socialPlatform);
    	 ?>
          <li class="span4">
            <div class="thumbnail">
              <img src="<?php echo $this->imageFolder."/".$items[$i]->image;?>" alt="<?php echo $items[$i]->title;?>" width="200" height="200">
              <div class="caption">
                <h3><a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=details&id=".(int)$items[$i]->id); ?>"><?php echo $items[$i]->title;?></a></h3>
                <span class="cf-founder">by 
                    <?php if(!empty($socialProfile)){ ?>
                    <a href="<?php echo $socialProfile;?>"><?php echo $items[$i]->user_name; ?></a>
                    <?php } else {?>
                    <?php echo $items[$i]->user_name; ?>
                    <?php }?>
                </span>
                <p><?php echo $items[$i]->short_desc;?></p>
                    <?php echo JHtml::_("CrowdFunding.progressbar", $fundedPercents, $items[$i]->days_left, $items[$i]->funding_type);?>
                <div class="row-fluid">
                	<div class="span4">
                	<div><strong><?php echo $items[$i]->funded_percents;?>%</strong></div>
                	<?php echo strtoupper( JText::_("COM_CROWDFUNDING_FUNDED") );?>
                	</div>
                	<div class="span4">
                	<div><strong><?php echo $raised;?></strong></div>
                	<?php echo strtoupper( JText::_("COM_CROWDFUNDING_RAISED") );?>
                	</div>
                	<div class="span4">
                	<div><strong><?php echo $items[$i]->days_left;?></strong></div>
                	<?php echo strtoupper( JText::_("COM_CROWDFUNDING_DAYS_LEFT") );?>
                	</div>
                </div>
              </div>
            </div>
          </li>
          <?php } ?>
          <?php }?>
        </ul>
    </div>
    <?php }?>
    <div class="clearfix"></div>
    <div class="pagination">
        <?php if ($this->params->def('show_pagination_results', 1)) : ?>
            <p class="counter">
                <?php echo $this->pagination->getPagesCounter(); ?>
            </p>
        <?php endif; ?>
    
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
    <div class="clearfix">&nbsp;</div>
</div>
<?php echo $this->version->backlink;?>