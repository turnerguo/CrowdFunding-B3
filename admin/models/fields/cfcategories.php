<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package      CrowdFunding
 * @subpackage   Components
 * @since       1.6
 */
class JFormFieldCfCategories extends JFormFieldList {
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'cfcategories';
    
    /**
     * Method to get the field options.
     *
     * @return  array   The field option objects.
     * @since   1.6
     */
    protected function getOptions(){
        
        // Initialize variables.
        $options = array();
        
        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);
        
        $query
            ->select('a.id AS value, a.title AS text')
            ->from('#__categories AS a')
            ->order("a.title ASC");
        
        // Set state
        $state = JArrayHelper::getValue($this->element, "state");
        if(!is_null($state)) {
            $query->where("published = ".(int)$state);
        }
        
        // Get the options.
        $db->setQuery($query);
        $options = $db->loadObjectList();
        
        array_unshift($options, JHtml::_('select.option', '0', '- '.JText::_('COM_CROWDFUNDING_SELECT_CATEGORY').' -', 'value', 'text'));
        
        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);
        
        return $options;
    }
}
