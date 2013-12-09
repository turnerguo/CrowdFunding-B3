<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>

<div class="form-horizontal">
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" >
        
        <div class="row-fluid">
        
            <div class="span8">
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
                </div>
            </div>
            
            <div class="span4">
                <?php echo $this->loadTemplate('params'); ?>
            </div>
        </div>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
    
</div>