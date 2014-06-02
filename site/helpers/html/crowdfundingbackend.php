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

/**
 * CrowdFunding Html Helper
 *
 * @package        ITPrism Components
 * @subpackage     CrowdFunding
 * @since          1.6
 */
abstract class JHtmlCrowdFundingBackend
{

    public static function approved($i, $value, $prefix, $checkbox = 'cb')
    {
        JHtml::_('bootstrap.tooltip');

        if (!$value) { // Disapproved
            $task  = $prefix . "approve";
            $title = "COM_CROWDFUNDING_APPROVE_ITEM";
            $class = "ban-circle";
        } else {
            $task  = $prefix . "disapprove";
            $title = "COM_CROWDFUNDING_DISAPPROVE_ITEM";
            $class = "ok";
        }

        $html[] = '<a class="btn btn-micro hasTooltip" href="javascript:void(0);" onclick="return listItemTask(\'' . $checkbox . $i . '\',\'' . $task . '\')" title="' . addslashes(htmlspecialchars(JText::_($title), ENT_COMPAT, 'UTF-8')) . '">';
        $html[] = '<i class="icon-' . $class . '"></i>';
        $html[] = '</a>';

        return implode("\n", $html);
    }

    /**
     * Returns a published state on a grid
     *
     * @param   integer      $value    The state value.
     * @param   integer      $i        The row index
     * @param   string|array $prefix   An optional task prefix or an array of options
     * @param   boolean      $enabled  An optional setting for access control on the action.
     * @param   string       $checkbox An optional prefix for checkboxes.
     *
     * @return  string  The Html code
     *
     * @see     JHtmlJGrid::state
     * @since   11.1
     */
    public static function published($i, $value, $prefix = '', $enabled = true, $checkbox = 'cb')
    {
        if (is_array($prefix)) {
            $options  = $prefix;
            $enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        $states = array(
            1  => array('unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', true, 'publish', 'publish'),
            0  => array('publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', true, 'unpublish', 'unpublish'),
            2  => array('unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', true, 'archive', 'archive'),
            -2 => array('publish', 'JTRASHED', 'JLIB_HTML_PUBLISH_ITEM', 'JTRASHED', true, 'trash', 'trash')
        );

        return JHtmlJGrid::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
    }

    public static function reward($rewardId, $reward, $projectId, $sent = 0)
    {
        $sent = (!$sent) ? 0 : 1;

        $html = array();

        if (!$rewardId) {

            $rewardLink = "javascript: void(0);";

            $icon  = "../media/com_crowdfunding/images/noreward_16.png";
            $title = 'title="' . JText::_('COM_CROWDFUNDING_REWARD_NOT_SELECTED') . '"';

        } else {

            $rewardLink = JRoute::_("index.php?option=com_crowdfunding&view=rewards&pid=" . (int)$projectId) . "&amp;filter_search=" . rawurlencode("id:" . $rewardId);

            if (!$sent) {
                $icon  = "../media/com_crowdfunding/images/reward_16.png";
                $title = 'title="';
                $title .= htmlspecialchars($reward, ENT_QUOTES, "UTF-8") . "<br />";
                $title .= JText::_("COM_CROWDFUNDING_REWARD_NOT_SENT");
                $title .= '"';
            } else {
                $icon  = "../media/com_crowdfunding/images/reward_sent_16.png";
                $title = 'title="';
                $title .= htmlspecialchars($reward, ENT_QUOTES, "UTF-8") . "<br />";
                $title .= JText::_("COM_CROWDFUNDING_REWARD_SENT");
                $title .= '"';
            }

        }

        $html[] = '<a href="' . $rewardLink . '" class="hasTooltip" ' . $title . '>';
        $html[] = '<img src="' . $icon . '" width="16" height="16" />';
        $html[] = '</a>';

        return implode(" ", $html);
    }


    /**
     * @param   int $i
     * @param   int $value The state value
     * @param   bool $canChange
     *
     * @return string
     */
    public static function featured($i, $value = 0, $canChange = true)
    {
        JHtml::_('bootstrap.tooltip');

        // Array of image, task, title, action
        $states = array(
            0 => array('unfeatured', 'projects.featured', 'COM_CROWDFUNDING_UNFEATURED', 'COM_CROWDFUNDING_TOGGLE_TO_FEATURE'),
            1 => array('featured', 'projects.unfeatured', 'COM_CROWDFUNDING_FEATURED', 'COM_CROWDFUNDING_TOGGLE_TO_UNFEATURE'),
        );

        $state = JArrayHelper::getValue($states, (int)$value, $states[1]);
        $icon  = $state[0];
        if ($canChange) {
            $html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="' . JText::_($state[3]) . '"><i class="icon-' . $icon . '"></i></a>';
        } else {
            $html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="' . JText::_($state[2]) . '"><i class="icon-' . $icon . '"></i></a>';
        }

        return $html;
    }

    public static function reason($value)
    {
        if (!$value) {
            return "";
        }

        JHtml::_('bootstrap.tooltip');

        $title = JText::sprintf("COM_CROWDFUNDING_STATUS_REASON", htmlspecialchars($value, ENT_COMPAT, 'UTF-8'));

        $html[] = '<a class="btn btn-micro hasTooltip" href="javascript:void(0);" title="' . addslashes($title) . '">';
        $html[] = '<i class="icon-question"></i>';
        $html[] = '</a>';

        return implode("\n", $html);
    }
}
