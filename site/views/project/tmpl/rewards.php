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
defined('_JEXEC') or die;
?>
<?php echo $this->loadTemplate("nav");?>
<div class="row-fluid">
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="projectForm" id="crowdf-rewards-form" class="form-validate">
        
        <div id="rewards_wrapper">
        <?php 
        if(!$this->items) { // Display first form
            $this->formItem  = array();
            $this->formIndex = 1;
            echo $this->loadTemplate("form");
        
        } else {
            
            $this->formIndex = 1;
            
            foreach($this->items as $item) {
                $this->formItem  = $item;
                echo $this->loadTemplate("form");
                $this->formIndex++; 
            }
        }?>
        </div>
        
        <input type="hidden" name="task" value="rewards.save" />
        <input type="hidden" name="id" value="<?php echo $this->projectId;?>" />
        <?php echo JHtml::_('form.token'); ?>
        <div class="clearfix"></div>
        
        <input type="hidden" name="items_number" id="items_number" value="<?php echo (0 == count($this->items)) ? 1 : count($this->items);?>" />
        <?php if(!$this->debugMode) {?>
        <button class="btn btn-large btn-block" type="button" id="cf_add_new_reward"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_ADD_REWARD");?></button>
        <?php }?>
        
        <button type="submit" class="button button-large margin-tb-15px" <?php echo $this->disabledButton;?>>
        	<i class="icon-ok icon-white"></i>
            <?php echo JText::_("JSAVE")?>
        </button>
    </form>
</div>
<?php echo $this->loadTemplate("tmpl");?>
<?php echo $this->version->backlink;?>