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
defined('_JEXEC') or die;
$id = (!empty($this->item->id)) ? "&id=".(int)$this->item->id : "";

$active = array("basic" => false, "funding" => false, "story" => false, "rewards" => false);
switch($this->layout) {
    case "default":
        $active["basic"] = true;
        break;
    case "funding":
        $active["funding"] = true;
        break;
    case "story":
        $active["story"] = true;
        break;
    case "rewards":
        $active["rewards"] = true;
        break;
    
}

?>
<div class="row-fluid">
	<div class="span12">
    	<div class="navbar">
            <div class="navbar-inner">
            	<a class="brand" href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project".$id);?>"><?php echo JText::_("COM_CROWDFUNDING_WIZARD");?></a>

            	<ul class="nav">
                    <li <?php echo ($active["basic"]) ? 'class="active"' : '';?>>
                    	<a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project".$id);?>"><?php echo JText::_("COM_CROWDFUNDING_STEP_BASIC");?></a>
                    </li>
                    
                    <li <?php echo ($active["funding"]) ? 'class="active"' : '';?>>
                    	<?php if(!empty($this->item->id)){?> 
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=funding".$id);?>"><?php echo JText::_("COM_CROWDFUNDING_STEP_FUNDING");?></a>
                        <?php }else {?>
                        <a href="javascript: void(0);" class="disabled"><?php echo JText::_("COM_CROWDFUNDING_STEP_FUNDING");?></a>
                        <?php }?>
                    </li>
                    
                    <li <?php echo ($active["story"]) ? 'class="active"' : '';?>>
                    	<?php if(!empty($this->item->id)){?> 
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=story".$id);?>"><?php echo JText::_("COM_CROWDFUNDING_STEP_STORY");?></a>
                        <?php }else {?>
                        <a href="javascript: void(0);" class="disabled"><?php echo JText::_("COM_CROWDFUNDING_STEP_STORY");?></a>
                        <?php }?>
                    </li>
                    
                    <li <?php echo ($active["rewards"]) ? 'class="active"' : '';?>>
                    	<?php if(!empty($this->item->id)){?> 
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=rewards".$id);?>"><?php echo JText::_("COM_CROWDFUNDING_STEP_REWARDS");?></a>
                        <?php }else {?>
                        <a href="javascript: void(0);" class="disabled"><?php echo JText::_("COM_CROWDFUNDING_STEP_REWARDS");?></a>
                        <?php }?>
                    </li>
                </ul>
             </div>
		</div>
    </div>
</div>