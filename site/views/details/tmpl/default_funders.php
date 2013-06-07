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
<?php 
if(!empty($this->items)) { 
    
    foreach($this->items as $item ) {
    
    $user = JFactory::getUser($item->id);
    $socialProfile  = JHtml::_("crowdfunding.socialProfile", $this->socialPlatform, $user);
    $socialAvatar   = JHtml::_("crowdfunding.socialAvatar", $this->avatars, $user, "media/com_crowdfunding/images/no-profile.png");
?>
    <div class="row-fluid">
        
        <div class="span12 cf-funder-row"> 
        
            <div class="media">
                <a class="pull-left" href="<?php echo (!$socialProfile) ? "javascript: void(0);" : $socialProfile;?>">
                    <img class="media-object" src="<?php echo $socialAvatar;?>">
                </a>
                <div class="media-body">
                    <?php if(!empty($socialProfile)){ ?>
                    <h4 class="media-heading">
                        <a href="<?php echo $socialProfile;?>">
                        <?php echo $item->name; ?>
                        </a>
                    </h4>
                    <?php } else {?>
                    <h4 class="media-heading"><?php echo $item->name; ?></h4>
                    <?php }?>
                </div>
            </div>
    		
    	</div>
    	
    </div>
    <?php } ?>
    
<?php } ?>