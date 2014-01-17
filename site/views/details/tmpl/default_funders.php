<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;?>
<?php 
if(!empty($this->items)) { 
    
    foreach($this->items as $item ) {
        $socialProfile  = (!$this->socialProfiles) ? null : $this->socialProfiles->getLink($item->id);
        $socialAvatar   = (!$this->socialProfilesAvatars) ? $this->defaultAvatar : $this->socialProfilesAvatars->getAvatar($item->id, $this->avatarsSize);
?>
    <div class="row-fluid">
        
        <div class="span12 cf-funder-row"> 
        
            <div class="media">
                <a class="pull-left" href="<?php echo (!$socialProfile) ? "javascript: void(0);" : $socialProfile;?>">
                    <img class="media-object" src="<?php echo $socialAvatar;?>" width="<?php echo $this->avatarsSize;?>" height="<?php echo $this->avatarsSize;?>">
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