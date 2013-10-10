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
<?php foreach ($this->items as $i => $item) {
	    $ordering  = ($this->listOrder == 'a.ordering');
	?>
	<tr class="row<?php echo $i % 2; ?>">
        <td ><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
		<td>
    		<a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=comment&layout=edit&id=".$item->id);?>" >
    		<?php echo JHTML::_('string.truncate', $item->comment, 128);?>
    		</a>
		</td>
		<td class="center"><a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=edit&id=".$item->project_id);?>"><?php echo $item->project; ?></a></td>
        <td class="center"><?php echo JHTML::_('date', $item->record_date, JText::_('DATE_FORMAT_LC3')); ?></td>
        <td class="center"><?php echo JHtml::_('jgrid.published', $item->published, $i, "comments."); ?></td>
        <td class="center"><?php echo $item->id;?></td>
	</tr>
<?php }?>
	  