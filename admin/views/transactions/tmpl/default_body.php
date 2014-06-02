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
<?php foreach ($this->items as $i => $item) {
    $ordering = ($this->listOrder == 'a.ordering');
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td>
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=transaction&layout=edit&id=" . $item->id); ?>"><?php echo $item->txn_id; ?></a>
            <?php if (!empty($item->parent_txn_id)) { ?>
                <div class="small">
                    <?php echo $this->escape($item->parent_txn_id); ?>
                </div>
            <?php } ?>
        </td>
        <td class="center hidden-phone"><?php echo JHtml::_("crowdfunding.name", $item->sender); ?></td>
        <td class="center hidden-phone"><?php echo $this->escape($item->beneficiary); ?></td>
        <td class="center hidden-phone">
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=transactions&filter_search=pid:" . $item->project_id); ?>">
                <?php echo JHtmlString::truncate(strip_tags($item->project), 53); ?>
            </a>
        </td>
        <td class="center"><?php
            $currency = $this->currencies->getCurrencyByAbbr($item->txn_currency);
            if (!empty($currency)) {
                echo $currency->getAmountString($item->txn_amount);
            } else {
                echo $item->txn_amount;
            }
            ?></td>
        <td class="center hidden-phone"><?php echo $item->txn_date; ?></td>
        <td class="center hidden-phone"><?php echo $item->service_provider; ?></td>
        <td class="center hidden-phone">
            <?php echo $item->txn_status; ?>
            <?php echo JHtml::_('crowdfundingbackend.reason', $item->status_reason); ?>
        </td>
        <td class="center hidden-phone">
            <?php echo JHtml::_('crowdfundingbackend.reward', $item->reward_id, $item->reward, $item->project_id, $item->reward_state); ?>
        </td>
        <td class="center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>
	  