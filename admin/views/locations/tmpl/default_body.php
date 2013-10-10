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
<?php foreach ($this->items as $i => $item) {?>
	<tr class="row<?php echo $i % 2; ?>">
		<td class="center">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
		<td>
			<a href="index.php?option=com_crowdfunding&amp;view=location&amp;layout=edit&amp;id=<?php echo $item->id;?>" ><?php echo $item->name; ?></a>
		</td>
		<td class="center"><?php echo $item->country_code; ?></td>
		<td class="center"><?php echo $item->timezone; ?></td>
		<td class="center"><?php echo $item->latitude; ?></td>
		<td class="center"><?php echo $item->longitude; ?></td>
		<td class="center"><?php echo $item->state_code; ?></td>
		<td class="center"><?php echo JHtml::_('jgrid.published', $item->published, $i, "locations."); ?></td>
        <td align="center"><?php echo $item->id;?></td>
	</tr>
<?php } ?>
	  