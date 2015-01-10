<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php foreach ($this->items as $i => $item) {
    $ordering = ($this->listOrder == 'a.ordering');

    $disableClassName = '';
    $disabledLabel    = '';
    if (!$this->saveOrder) {
        $disabledLabel    = JText::_('JORDERINGDISABLED');
        $disableClassName = 'inactive tip-top';
    }

    $numberOfRewards = (isset($this->rewards[$item->id])) ? $this->rewards[$item->id]->number : 0;
    ?>
    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid ?>">
        <td class="order nowrap center hidden-phone">
    		<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
    			<i class="icon-menu"></i>
    		</span>
            <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
        </td>
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="center">
            <div  class="btn-group">
            <?php echo JHtml::_('crowdfundingbackend.published', $i, $item->published, "projects."); ?>
            <?php echo JHtml::_('crowdfundingbackend.featured', $i, $item->featured); ?>
            <?php echo JHtml::_('crowdfundingbackend.approved', $i, $item->approved, "projects."); ?>
            </div>
        </td>
        <td class="has-context">
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=edit&id=" . $item->id); ?>">
                <?php echo $this->escape($item->title); ?>
            </a>
            <div class="small">
                <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=rewards&pid=" . $item->id); ?>">
                    <?php echo JText::sprintf("COM_CROWDFUNDING_REWARDS_N", $numberOfRewards); ?>
                </a>
            </div>
        </td>
        <td class="hidden-phone"><?php echo $this->currency->getAmountString($item->goal); ?></td>
        <td class="hidden-phone"><?php echo $this->currency->getAmountString($item->funded); ?></td>
        <td class="hidden-phone"><?php echo JHtml::_("crowdfunding.percent", $item->funded_percents); ?></td>
        <td class="center hidden-phone">
            <?php echo JHtml::_("crowdfunding.date", $item->funding_start, JText::_('DATE_FORMAT_LC3')); ?>
        </td>
        <td class="center hidden-phone">
            <?php echo JHtml::_("crowdfunding.duration", $item->funding_end, $item->funding_days, JText::_('DATE_FORMAT_LC3')); ?>
        </td>
        <td class="center hidden-phone"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')); ?></td>
        <td class="center hidden-phone"><?php echo $item->category; ?></td>
        <td class="center hidden-phone"><?php echo $this->escape($item->type); ?></td>
        <td class="center">
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=users&filter_search=id:" . $item->user_id); ?>">
                <?php echo $this->escape($item->username); ?>
            </a>
        </td>
        <td class="center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>
	  