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

// No direct access
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

    <div class="width-40 fltlft">
        <fieldset class="adminform">
            <legend><?php echo $this->legend; ?></legend>
            
            <ul class="adminformlist">
            
                <li><?php echo $this->form->getLabel('data'); ?>
                <?php echo $this->form->getInput('data'); ?></li>
                
                
                <?php if(strcmp($this->importType, "states") != 0) {?>
                <li><?php echo $this->form->getLabel('reset_id'); ?>
                <?php echo $this->form->getInput('reset_id'); ?></li>
                <?php }?>
                
            </ul>
            
		</fieldset>
	</div>
    
    <input type="hidden" name="task" value="" id="task"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
