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

$fieldSets = $this->form->getFieldsets('params');

foreach ($fieldSets as $name => $fieldSet)  {
?>
<div class="tab-pane" id="params-<?php echo $name;?>">
	<?php
	if (isset($fieldSet->description) && trim($fieldSet->description)) {
		echo '<p class="alert alert-info">'.$this->escape(JText::_($fieldSet->description)).'</p>';
	}
	?>
	<?php foreach ($this->form->getFieldset($name) as $field) { ?>
	<div class="control-group">
		<div class="control-label"><?php echo $field->label; ?></div>
		<div class="controls"><?php echo $field->input; ?></div>
	</div>
	<?php } ?>
</div>
<?php } ?>