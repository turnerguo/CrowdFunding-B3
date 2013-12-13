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

$options = array(
        'onActive' => 'function(title, description){
        description.setStyle("display", "block");
        title.addClass("open").removeClass("closed");
    }',
        'onBackground' => 'function(title, description){
        description.setStyle("display", "none");
        title.addClass("closed").removeClass("open");
    }',
        'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
        'useCookie' => true, // this must not be a string. Don't use quotes.
);

?>


<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

    <?php echo JHtml::_('tabs.start', 'tab_group_id', $options); 
		
		echo JHtml::_('tabs.panel', JText::_('COM_CROWDFUNDING_BASIC'), 'panel_1_id');
		echo $this->loadTemplate('basic');
		
		echo JHtml::_('tabs.panel', JText::_('COM_CROWDFUNDING_FUNDING'), 'panel_2_id');
		echo $this->loadTemplate('funding');
		
		echo JHtml::_('tabs.panel', JText::_('COM_CROWDFUNDING_STORY'), 'panel_3_id');
		echo $this->loadTemplate('story');
		
		echo JHtml::_('tabs.end');
	?>

	<div class="clr"></div>

    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
