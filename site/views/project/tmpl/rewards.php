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
        
        <div class="btn-group cf-rewards-submit-btn">
            <button class="btn" <?php echo $this->disabledButton;?>>
                <i class="icon-ok"></i>
                <?php echo JText::_("COM_CROWDFUNDING_SAVE_REWARDS");?>
            </button>
            <?php if(empty($this->item->published)) {?>
            <button class="btn dropdown-toggle" data-toggle="dropdown" <?php echo $this->disabledButton;?>>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=projects.savestate&id=".$this->item->id."&state=1&".JSession::getFormToken()."=1&return=1"); ?>" id="js-btn-rewards-publish">
                        <i class="icon-ok-circle"></i>
                        <?php echo JText::_("COM_CROWDFUNDING_PUBLISH_NOW");?>
                    </a>
                </li>
            </ul>
            <?php }?>
        </div>
        
    </form>
</div>
<?php echo $this->loadTemplate("tmpl");?>

<?php if(empty($this->item->published)) {?>
<div class="modal hide fade" id="js-modal-publish-project">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3><?php echo JText::_("COM_CROWDFUNDING_PUBLISHING_PROJECT");?></h3>
    </div>
    <div class="modal-body">
        <p class="cf-fm"><?php echo JText::_("COM_CROWDFUNDING_QUESTION_PUBLISH_PROJECT");?></p>
        <p><?php echo JText::_("COM_CROWDFUNDING_NOTE_PUBLISHING_PROJECT");?></p>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn btn-primary" id="js-modal-btn-pp-yes"><?php echo JText::_("JYES");?></a>
        <a href="#" class="btn" id="js-modal-btn-pp-no"><?php echo JText::_("JNO");?></a>
    </div>
</div>
<?php }?>
<?php echo $this->version->backlink;?>