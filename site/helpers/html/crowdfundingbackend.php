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

/**
 * CrowdFunding Html Helper
 *
 * @package		ITPrism Components
 * @subpackage	CrowdFunding
 * @since		1.6
 */
abstract class JHtmlCrowdFundingBackend {
    
    public static function approved($i, $value, $prefix, $checkbox = 'cb') {
         
        if(!$value) { // Disapproved
            $task   = $prefix."approve";
            $title  = "COM_CROWDFUNDING_APPROVE_ITEM";
            $text   = "COM_CROWDFUNDING_DISAPPROVED";
            $class  = "disapprove";
        } else {
            $task   = $prefix."disapprove";
            $title  = "COM_CROWDFUNDING_DISAPPROVE_ITEM";
            $text   = "COM_CROWDFUNDING_APPROVED";
            $class  = "approve";
        }
    
        $html[] = '<a class="jgrid"';
        $html[] = ' href="javascript:void(0);" onclick="return listItemTask(\'' . $checkbox . $i . '\',\'' . $task . '\')"';
        $html[] = ' title="' . addslashes(htmlspecialchars(JText::_($title), ENT_COMPAT, 'UTF-8')) . '">';
        $html[] = '<span class="state ' . $class . '">';
        $html[] = '<span class="text">' . JText::_($text) . '</span>';
        $html[] = '</span>';
        $html[] = '</a>';
    
        return implode($html);
    }
    
    /**
     * @param   int $value	The state value
     * @param   int $i
     */
    public static function featured($value = 0, $i, $canChange = true) {
        
        // Array of image, task, title, action
        $states	= array(
            0	=> array('disabled.png',  'projects.featured',	  'COM_CROWDFUNDING_UNFEATURED',  'COM_CROWDFUNDING_TOGGLE_TO_FEATURE'),
            1	=> array('featured.png',  'projects.unfeatured',  'COM_CROWDFUNDING_FEATURED',    'COM_CROWDFUNDING_TOGGLE_TO_UNFEATURE'),
        );
        
        $state	= JArrayHelper::getValue($states, (int) $value, $states[1]);
        $html	= JHtml::_('image', 'admin/'.$state[0], JText::_($state[2]), NULL, true);
        if ($canChange) {
            $html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'. $html.'</a>';
        }
        
        return $html;
        
    }
    
    /**
     * Returns a published state on a grid
     *
     * @param   integer       $value         The state value.
     * @param   integer       $i             The row index
     * @param   string|array  $prefix        An optional task prefix or an array of options
     * @param   boolean       $enabled       An optional setting for access control on the action.
     * @param   string        $checkbox      An optional prefix for checkboxes.
     *
     * @return  string  The Html code
     *
     * @see     JHtmlJGrid::state
     * @since   11.1
     */
    public static function published($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb') {
        
        if (is_array($prefix)) {
            $options  = $prefix;
            $enabled  = array_key_exists('enabled', $options)  ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix   = array_key_exists('prefix', $options)   ? $options['prefix'] : '';
        }
        
        $states = array(
            1 => array('unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', true, 'publish', 'publish'),
            0 => array('publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', true, 'unpublish', 'unpublish'),
            2 => array('unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', true, 'archive', 'archive'),
            -2 => array('publish', 'JTRASHED', 'JLIB_HTML_PUBLISH_ITEM', 'JTRASHED', true, 'trash', 'trash')
        );
    
        return JHtmlJGrid::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
    }
    
    
    public static function reward($rewardId, $reward, $projectId, $sent = 0) {
    
        $state = (!$sent) ? 1 : 0;
    
        $html = array();
    
        if(!$rewardId) {
            
            $rewardLink = "javascript: void(0);";
            
            $icon  = "../media/com_crowdfunding/images/noreward_16.png";
            $title = 'title="' . JText::_('COM_CROWDFUNDING_REWARD')."::".JText::_('COM_CROWDFUNDING_REWARD_NOT_SELECTED') . '"';
            
        } else {
    
            $rewardLink = JRoute::_("index.php?option=com_crowdfunding&view=rewards&pid=".(int)$projectId)."&amp;filter_search=".rawurlencode("id:".$rewardId);
            
            if(!$sent) {
                $icon  = "../media/com_crowdfunding/images/reward_16.png";
                $title = 'title="' . JText::_('COM_CROWDFUNDING_REWARD')."::".htmlspecialchars($reward, ENT_QUOTES, "UTF-8") . '"';
            } else {
                $icon  = "../media/com_crowdfunding/images/reward_sent_16.png";
                $title = 'title="' . JText::_('COM_CROWDFUNDING_REWARD')."::". htmlspecialchars($reward, ENT_QUOTES, "UTF-8") . '"';
            }
    
        }
    
        $html[] = '<a href="'.$rewardLink.'" class="hasTip" '.$title.'>';
        $html[] = '<img src="'.$icon.'" width="16" height="16" />';
        $html[] = '</a>';
    
        return implode(" ", $html);
    }
    
}
