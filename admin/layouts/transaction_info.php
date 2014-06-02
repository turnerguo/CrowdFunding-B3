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
<h2><?php echo JText::_("COM_CROWDFUNDING_ADDITIONAL_INFORMATION"); ?></h2>
<table class="table table-condensed">
    <tbody>
    <?php foreach ($displayData as $key => $value) { ?>
        <?php if (!is_array($value)) { ?>
            <tr>
                <th><?php echo $this->escape($key); ?></th>
                <td><?php echo $this->escape($value); ?></td>
            </tr>
        <?php } else { ?>
            <tr class="cf-response-type">
                <th colspan="2"><?php echo JText::sprintf("COM_CROWDFUNDING_TRACK_ID", $this->escape($key)); ?></th>
            </tr>
            <?php foreach ($value as $k => $v) { ?>
                <tr>
                    <th><?php echo $this->escape($k); ?></th>
                    <td><?php echo $this->escape($v); ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    </tbody>
</table>