<?php
/**
 * @package      ITPrism Components
 * @subpackage   CrowdFunding
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('JPATH_PLATFORM') or die;

class JFormFieldGoal extends JFormField {
    
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  11.1
	 */
	protected $type = 'Goal';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput(){
	    
		// Initialize some field attributes.
		$size      = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$readonly  = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled  = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$class     = (!empty($this->element['class'])) ? ' class="'. (string) $this->element['class'] .'"' : "";
		
		// Initialize JavaScript field attributes.
		$onchange    = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$params      = JComponentHelper::getParams("com_crowdfunding");
		$currencyId  = $params->get("project_currency");
		
		jimport("crowdfunding.currency");
		$currency    = CrowdFundingCurrency::getInstance($currencyId);
		
		if(!empty($currency->symbol)) { // Prepended
		    $html = '<div class="input-prepend input-append"><span class="add-on">'.$currency->symbol.'</span>';
		} else { // Append
		    $html = '<div class="input-append">';
		}
		
		$html .= '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . '/>';
			
		// Appended
		$html .= '<span class="add-on">'.$currency->abbr.'</span></div>';
		
		return $html;
	}
}
