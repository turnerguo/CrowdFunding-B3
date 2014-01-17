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
<div class="cf-filters">
    <form action="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=descover");?>" method="get" > 
    <?php if ($this->filterPaginationLimit) { ?>
		<div class="pull-right">
			<label for="limit" class="element-invisible">
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
			</label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	<?php } ?>
	</form>
</div>
<div class="clearfix">&nbsp;</div>