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
<div class="row-fluid">

    <h2><?php echo $this->escape($this->item->title); ?></h2>

    <p><?php echo JText::sprintf("COM_CROWDFUNDING_LOG_TYPE", $this->item->type); ?></p>

    <pre><?php echo $this->escape($this->item->data); ?></pre>

    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm"
          id="adminForm" class="form-validate">
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="cid[]" value="<?php echo (int)$this->item->id; ?>"/>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>