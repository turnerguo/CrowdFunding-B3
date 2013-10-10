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
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=projects'); ?>" method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar">
        <div class="filter-search fltlft">
            <label class="filter-search-lbl hasTip" for="filter_search" title="<?php echo JText::_('COM_CROWDFUNDING_SEARCH_IN_PROJECTS_TOOLTIP'); ?>"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="text" name="filter_search" id="filter_search" class="hasTip" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CROWDFUNDING_SEARCH_IN_PROJECTS_TOOLTIP'); ?>" />
            <button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>
        <div class="filter-select fltrt">
            <select name="filter_state" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array("archived" => false)), 'value', 'text', $this->state->get('filter.state'), true);?>
            </select>
            
            <select name="filter_approved" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('COM_CROWDFUNDING_SELECT_APPROVED_STATUS');?></option>
                <?php echo JHtml::_('select.options', $this->approvedOptions, 'value', 'text', $this->state->get('filter.approved'), true);?>
            </select>
            
            <select name="filter_featured" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('COM_CROWDFUNDING_SELECT_FEATURED_STATUS');?></option>
                <?php echo JHtml::_('select.options', $this->featuredOptions, 'value', 'text', $this->state->get('filter.featured'), true);?>
            </select>
            
            <select name="filter_category_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_crowdfunding'), 'value', 'text', $this->state->get('filter.category_id'));?>
			</select>
        </div>
    </fieldset>
    
    <table class="adminlist">
       <thead><?php echo $this->loadTemplate('head');?></thead>
	   <tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
	   <tbody><?php echo $this->loadTemplate('body');?></tbody>
	</table>

    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_order" value="<?php echo $this->listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->listDirn; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>