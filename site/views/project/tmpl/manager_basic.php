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
<h4 class="cf-m-0 pl5 cf-bg-color-yellow"><?php echo JText::_("COM_CROWDFUNDING_BASIC_INFORMATION"); ?></h4>
<table class="table table-striped">
    <tbody>
    <tr>
        <td><?php echo JText::_("COM_CROWDFUNDING_HITS"); ?></td>
        <td><?php echo $this->item->hits;?></td>
    </tr>
    <tr>
        <td><?php echo JText::_("COM_CROWDFUNDING_UPDATES"); ?></td>
        <td><?php echo $this->statistics["updates"];?></td>
    </tr>
    <tr>
        <td><?php echo JText::_("COM_CROWDFUNDING_COMMENTS"); ?></td>
        <td><?php echo $this->statistics["comments"];?></td>
    </tr>
    <tr>
        <td><?php echo JText::_("COM_CROWDFUNDING_FUNDERS"); ?></td>
        <td><?php echo $this->statistics["funders"];?></td>
    </tr>
    <tr>
        <td><?php echo JText::_("COM_CROWDFUNDING_RAISED"); ?></td>
        <td><?php echo $this->raised;?></td>
    </tr>
    </tbody>
</table>
