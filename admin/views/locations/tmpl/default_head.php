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
	<th class="title">
        <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_NAME', 'a.name', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%">
    	<?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_COUNTRY_CODE', 'a.country_code', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%">
        <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_TIMEZONE', 'a.timezone', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%">
        <?php echo JText::_("COM_CROWDFUNDING_LATITUDE"); ?>
    </th>
    <th width="10%">
        <?php echo JText::_("COM_CROWDFUNDING_LONGITUDE"); ?>
    </th>
    <th width="10%">
        <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_STATE_CODE', 'a.state_code', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%"><?php echo JHtml::_('grid.sort',  'JSTATUS', 'a.published', $this->listDirn, $this->listOrder); ?></th>
    <th width="3%"><?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?></th>
</tr>
	  