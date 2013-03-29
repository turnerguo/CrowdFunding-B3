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
defined('_JEXEC') or die;
?>
<form enctype="multipart/form-data"  action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    <div class="width-40 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_("COM_CROWDFUNDING_LOCATION_DATA"); ?></legend>
            
            <ul class="adminformlist">
                <li><?php echo $this->form->getLabel('name'); ?>
                <?php echo $this->form->getInput('name'); ?></li>
    
                <li><?php echo $this->form->getLabel('latitude'); ?>
                <?php echo $this->form->getInput('latitude'); ?></li>
    
    			<li><?php echo $this->form->getLabel('longitude'); ?>
                <?php echo $this->form->getInput('longitude'); ?></li>
                  
    			<li><?php echo $this->form->getLabel('country_code'); ?>
                <?php echo $this->form->getInput('country_code'); ?></li>  
                
    			<li><?php echo $this->form->getLabel('timezone'); ?>
                <?php echo $this->form->getInput('timezone'); ?></li>  
                
                <li><?php echo $this->form->getLabel('published'); ?>
                <?php echo $this->form->getInput('published'); ?></li>   
                
                <li><?php echo $this->form->getLabel('id'); ?>
                <?php echo $this->form->getInput('id'); ?></li>
            </ul>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>