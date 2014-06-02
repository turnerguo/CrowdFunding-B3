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
?>
<?php foreach ($this->items as $i => $item) { ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="center">
            <?php echo JHtml::_('jgrid.published', $item->published, $i, "rewards."); ?>
        </td>
        <td>
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=reward&layout=edit&id=" . $item->id); ?>"><?php echo $item->title; ?></a>
        </td>
        <td class="center"><?php echo $this->currency->getAmountString($item->amount); ?></td>
        <td class="center hidden-phone"><?php echo $item->number; ?></td>
        <td class="center hidden-phone"><?php echo $item->distributed; ?></td>
        <td class="center hidden-phone"><?php echo $item->available; ?></td>
        <td class="center hidden-phone"><?php echo (CrowdFundingHelper::isValidDate($item->delivery)) ? JHtml::_('date', $item->delivery, JText::_('DATE_FORMAT_LC3')) : "--"; ?></td>
        <td class="center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>
	  