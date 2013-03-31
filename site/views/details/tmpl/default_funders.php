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
<?php if(!empty($this->items)) {?>
<?php foreach($this->items as $item ) {
    $socialProfile  = JHtml::_("crowdfunding.socialProfile", $item->id, $this->socialPlatform);
?>
    <div class="row-fluid">
    
    	<div class="span12 cf-funder-row"> 
    		<?php if(!empty($socialProfile)){ ?>
            <a href="<?php echo $socialProfile;?>"><?php echo $item->name; ?></a>
            <?php } else {?>
            <?php echo $item->name; ?>
            <?php }?>
    	</div>
    	
    </div>
    <?php }?>
    
<?php }?>