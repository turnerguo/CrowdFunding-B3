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

if (strcmp("five_steps", $this->wizardType) == 0) {
    $layout      = new JLayoutFile('project_wizard', $this->layoutsBasePath);
} else {
    $layout      = new JLayoutFile('project_wizard_six_steps', $this->layoutsBasePath);
}
echo $layout->render($this->layoutData);

?>
<div class="row-fluid">
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="projectForm" id="crowdf-rewards-form" class="form-validate" enctype="multipart/form-data" >
        
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
        
        <?php if(!$this->rewardsEnabled) {?>
            <p class="sticky"><?php echo JText::_("COM_CROWDFUNDING_NOTE_REWARDS_CREATING_NOT_ALLOWED");?></p>
        <?php }?>
        
        <div class="cf-rewards-submit-btn">
            <button class="btn" <?php echo $this->disabledButton;?> name="btn_submit" value="save" type="submit">
                <i class="icon-ok icon-white"></i>
                <?php echo JText::_("COM_CROWDFUNDING_SAVE_REWARDS");?>
            </button>

            <button class="btn" <?php echo $this->disabledButton;?> name="btn_submit" value="save_continue" type="submit">
                <i class="icon-ok icon-white"></i>
                <?php echo JText::_("COM_CROWDFUNDING_SAVE_AND_CONTINUE");?>
            </button>
        </div>

    </form>
</div>
<?php echo $this->loadTemplate("tmpl");?>