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

// no direct access
defined('_JEXEC') or die;?>
<div class="cfunding<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>
    
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=transactions'); ?>" method="post" name="adminForm" id="adminForm">
    
        <table class="table table-striped table-bordered cf-transactions">
            <thead>
            	<tr>
            		<th>
            		    <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_PROJECT', 'b.title', $this->listDirn, $this->listOrder); ?>
        		    </th>
            		<th>
            			<?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_AMOUNT', 'a.txn_amount', $this->listDirn, $this->listOrder); ?>
            		</th>
            		<th>
            			<?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_INVESTOR', 'e.name', $this->listDirn, $this->listOrder); ?>
            		</th>
            		<th>
            			<?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_BENEFICIARY', 'f.name', $this->listDirn, $this->listOrder); ?>
            		</th>
            		<th>
            			<?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_DATE', 'a.txn_date', $this->listDirn, $this->listOrder); ?>
            		</th>
            		<th>
            			<?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDING_REWARD', 'd.title', $this->listDirn, $this->listOrder); ?>
            		</th>
            		<th>
            			<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
        		    </th>
            	</tr>
            </thead>
            <tbody>
            	<?php foreach($this->items as $item) {?>
            	<tr>
            		<td>
            			<a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($item->slug, $item->catslug));?>">
            			<?php echo JHtmlString::truncate(strip_tags($item->project), 64); ?>
            		    </a>
        		    </td>
            		<td class="pull-center"><?php echo $this->currency->getAmountString($item->txn_amount); ?></td>
            		<td class="pull-center"><?php echo $item->investor; ?></td>
            		<td class="pull-center"><?php echo $item->receiver; ?></td>
            		<td class="pull-center"><?php echo JHtml::_('date', $item->txn_date, JText::_('DATE_FORMAT_LC3')); ?></td>
            		<td class="pull-center">
            		    <?php if(!$item->reward_id) { ?>
                		<img src="media/com_crowdfunding/images/noreward_16.png" alt="<?php echo JText::_('COM_CROWDFUNDING_REWARD_NOT_SELECTED'); ?>" width="16" height="16"/>
                		<?php } else {?>
                		<img src="media/com_crowdfunding/images/reward_16.png" alt="<?php echo JText::_('COM_CROWDFUNDING_REWARD_SELECTED'); ?>" title="<?php echo JText::sprintf('COM_CROWDFUNDING_REWARD_TOOLTIP', $item->reward); ?>" width="16" height="16" class="hasTip" />
                		<?php }?>
            		</td>
            		<td class="pull-center">
            			<?php echo $item->id; ?>
            		</td>
            	</tr>
            	<?php }?>
            </tbody>
            <tfoot>
            </tfoot>
        </table>
        
    	<?php echo $this->pagination->getListFooter(); ?>
        
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="filter_order" value="<?php echo $this->listOrder; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->listDirn; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
<div class="clearfix">&nbsp;</div>
<?php echo $this->version->backlink;?>