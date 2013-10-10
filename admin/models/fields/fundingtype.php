<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('radio');

class JFormFieldFundingType extends JFormFieldRadio {
    
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'FundingType';

	/**
	 * Method to get the field input markup for a spacer.
	 * The spacer does not have accept input.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput() {
	    
	    // Get component parameters
	    $componentParams    = JComponentHelper::getParams("com_crowdfunding");
	    $allowedFundingType = $componentParams->get("project_funding_type");
	    
		// Initialize variables.
		$html = array();

		// Initialize some field attributes.
		$class      = ( !$this->element['class'] ) ? ' class="radio"' : ' class="radio ' . (string) $this->element['class'] . '"';
        
		// Start the radio field output.
		$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

		// Build the radio field output.
	    switch($allowedFundingType) {
	        
	        case "FIXED":
	            $this->prepareFixed($html);
	            break;
	            
	        case "FLEXIBLE":
	            $this->prepareFlexible($html);
	            break;
	            
	        default:
	            
	            $this->prepareFixed($html);
	            $this->prepareFlexible($html);
	            
	            break;
	    }

		// End the radio field output.
		$html[] = '</fieldset>';

		return implode($html);
	}
	
	
	private function prepareFixed(&$html) {
	    
	    // Initialize some option attributes.
	    $checked   = ($this->value == "FIXED") ? ' checked="checked"' : '';
	     
	    $html[] = '<input type="radio" id="' . $this->id .'_fixed" name="' . $this->name . '"' . ' value="FIXED"' . $checked . '/>';
	    $html[] = '<label for="' . $this->id .'_fixed">'. JText::_("COM_CROWDFUNDING_FIELD_FUNDING_TYPE_FIXED") . '</label>';
	    $html[] = JText::_(JString::trim("COM_CROWDFUNDING_FIELD_FUNDING_TYPE_HELP_FIXED"));
	    
	}
	
	private function prepareFlexible(&$html) {
	     
	    // Initialize some option attributes.
	    $checked   = ($this->value == "FLEXIBLE") ? ' checked="checked"' : '';
	
	    $html[] = '<input type="radio" id="' . $this->id .'_fixed" name="' . $this->name . '"' . ' value="FLEXIBLE"' . $checked . '/>';
	    $html[] = '<label for="' . $this->id .'_fixed">'. JText::_("COM_CROWDFUNDING_FIELD_FUNDING_TYPE_FLEXIBLE") . '</label>';
	    $html[] = JText::_(JString::trim("COM_CROWDFUNDING_FIELD_FUNDING_TYPE_HELP_FLEXIBLE"));
	     
	}
	
}
