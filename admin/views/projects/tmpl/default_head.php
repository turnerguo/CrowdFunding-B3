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
<tr>
    <th width="1%">
        <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
    </th>
	<th class="title" >
	     <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_TITLE', 'a.title', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="5%">
		<?php echo JHtml::_('grid.sort', 'JFEATURED', 'a.featured', $this->listDirn, $this->listOrder, NULL, 'desc'); ?>
	</th>
	<th width="10%">
	    <?php echo JHtml::_('grid.sort',  'JCATEGORY', 'b.title', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="5%">
	     <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_CREATED', 'a.created', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="5%">
	     <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_GOAL', 'a.goal', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="5%">
	     <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_FUNDED', 'a.funded', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="5%">
	     <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_FUNDED_PERCENTS', 'funded_percents', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="10%">
	     <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_START_DATE', 'a.funding_start', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="10%">
	     <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_END_DATE', 'a.funding_end', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="10%">
        <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'a.ordering', $this->listDirn, $this->listOrder); ?>
        <?php if ($this->saveOrder) {?>
        <?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'projects.saveorder'); ?>
        <?php }?>
    </th>
    <th width="5%"><?php echo JHtml::_('grid.sort',  'JSTATUS', 'a.published', $this->listDirn, $this->listOrder); ?></th>
    <th width="5%"><?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_APPROVED', 'a.approved', $this->listDirn, $this->listOrder); ?></th>
    <th width="5%"><?php echo JText::_("COM_CROWDFUNDING_TYPE");?></th>
    <th width="3%" class="nowrap"><?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?></th>
</tr>
	  