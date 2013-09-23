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
abstract class JHtmlCrowdFunding {
    
    /**
     * 
     * Display an icon for approved or not approved project
     * @param integer $value
     */
    public static function approved($value) {
        
        $html = '<i class="{ICON}"></i>';
        
        switch($value) {
            
            case 1: // Published
                $html = str_replace("{ICON}", "icon-ok-sign", $html);
                break;
                
            default: // Unpublished
                $html = str_replace("{ICON}", "icon-remove-sign", $html);
                break;
        }
        
        return $html;
        
    }
    
    /**
     * 
     * Display an input field for amount
     * @param float $value
     * @param object $currency
     * @param array $options
     */
    public static function inputAmount($value, $currency, $options) {
        
        $class  = "";
        $symbol = $currency->getSymbol();
        $currencyCode = $currency->getAbbr();
        
        if(!empty($symbol)){
            $class = "input-prepend ";
        }
        
        $class .= "input-append";
        
        $html = '<div class="'.$class.'">';
        
        if(!empty($symbol)){
            $html .= '<span class="add-on">'. $symbol .'</span>';
        }
            
        $name = JArrayHelper::getValue($options, "name");
        
        $id   = "";
        if(JArrayHelper::getValue($options, "id")) {
            $id = 'id="'.JArrayHelper::getValue($options, "id").'"';
        }
        
        if(!$value OR !is_numeric($value)) {
            $value = 0;
        }
        
        if(JArrayHelper::getValue($options, "class")) {
            $class = 'class="'.JArrayHelper::getValue($options, "class").'"';
        }
        
        $html .= '<input type="text" name="'.$name.'" value="'.$value.'" '.$id.' '.$class.' />';
        
        if(!empty($currencyCode)) {
            $html .= '<span class="add-on">'.$currencyCode.'</span>';
        }
            
        $html .= '</div>';
        
        return $html;
        
    }
    
    /**
     * Add symbol or abbreviation to a currency
     * 
     * @param float $value
     * @param array $currency
     * 
     * @deprecated 1.1 Use CrowdFundingCurrency::getAmountString()
     */
    public static function amount($value, $currency) {
        
        if(!empty($currency["symbol"])) { // Prepended
		    $amount = $currency["symbol"].$value;
		} else { // Append
		    $amount = $value.$currency["abbr"];
		}
		
		return $amount;
    } 
    
    /**
     * Display a progress bar
     * 
     * @param int 	  $percent	 	A percent of fund raising
     * @param int     $daysLeft
     * @param string  $fundingType
     */
    public static function progressBar($percent, $daysLeft, $fundingType) {
        
        $html   = array();
        $class  = 'progress-success';
        
        if($daysLeft > 0 ) {
            $html[1] = '<div class="bar" style="width: '.$percent.'%"></div>';
        } else {
            
            // Check for the type of funding
            if($fundingType == "FLEXIBLE") { 
            
                if($percent > 0 ) {
                    $html[1] = '<div class="bar" style="width: 100%">'.JString::strtoupper( JText::_("COM_CROWDFUNDING_SUCCESSFUL") ).'</div>';                
                } else {
                    $class   = 'progress-danger';
                    $html[1] = '<div class="bar" style="width: 100%">'.JString::strtoupper( JText::_("COM_CROWDFUNDING_COMPLETED") ).'</div>';
                }
                
            } else { // Fixed
                
                if($percent >= 100 ) {
                    $html[1] = '<div class="bar" style="width: 100%">'.JString::strtoupper( JText::_("COM_CROWDFUNDING_SUCCESSFUL") ).'</div>';                
                } else {
                    $class   = 'progress-danger';
                    $html[1] = '<div class="bar" style="width: 100%">'.JString::strtoupper( JText::_("COM_CROWDFUNDING_COMPLETED") ).'</div>';
                }
                
            }
            
        }
        
        $html[0] = '<div class="progress '.$class.'">';
        $html[2] = '</div>';
        
        ksort($html);
        
        return implode("\n", $html);
    } 
    
    /**
     * Display a state of result
     * 
     * @param int 	$percent	 	A percent of fund raising
     * @param string  $fundingType
     */
    public static function resultState($percent, $fundingType) {
        
        $html   = array();
        
        // Check for the type of funding
        if($fundingType == "FLEXIBLE") { 
            
            if($percent > 0 ) {
                $otuput = JText::_("COM_CROWDFUNDING_SUCCESSFUL");                
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_COMPLETED");
            }
            
        } else { // Fixed
            
            if($percent >= 100 ) {
                $otuput = JText::_("COM_CROWDFUNDING_SUCCESSFUL");                
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_COMPLETED");
            }
            
        }
        
        return $otuput;
    } 
    
    /**
     * 
     * Display a text that describes the state of result
     * 
     * @param int 	  $percent	 A percent of fund raising
     * @param string  $fundingType
     */
    public static function resultStateText($percent, $fundingType) {
        
        $html   = array();
        
        // Check for the type of funding
        if($fundingType == "FLEXIBLE") { 
            
            if($percent > 0 ) {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_FINISHED_SUCCESSFULLY");                
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_HAS_EXPIRED");
            }
            
        } else { // Fixed
            
            if($percent >= 100 ) {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_FINISHED_SUCCESSFULLY");                
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_HAS_EXPIRED");
            }
            
        }
        
        return $otuput;
    } 
    
    /**
     * 
     * Display an icon for state of project
     * @param integer $value
     * @param string  $url		An url to the task
     */
    public static function state($value, $url, $tip = false) {
        
        $title = "";
        if(!empty($tip)) {
            JHtml::_("bootstrap.tooltip");
            
            $tipMessage = ($value != 1) ? JText::_("COM_CROWDFUNDING_PUBLISH_THIS_ITEM") : JText::_("COM_CROWDFUNDING_UNPUBLISH_THIS_ITEM");
            
            $class = ' class="btn btn-small hasTooltip"';
            $title = ' title="'.htmlspecialchars($tipMessage, ENT_QUOTES, "UTF-8").'"';
            
        } else {
            $class = ' class="btn btn-small"';
        }
        
        $html = '<a href="'.$url.'"'.$class.$title.' ><i class="{ICON}"></i></a>';
        
        switch($value) {
            
            case 1: // Published
                $html = str_replace("{ICON}", "icon-ok-circle", $html);
                break;
                
            default: // Unpublished
                $html = str_replace("{ICON}", "icon-remove-circle", $html);
                break;
        }
        
        return $html;
    }
    
    /**
     * 
     * If value is higher than 100, sets it to 100. 
     * This method validates percent of funding.
     * @param integer $value
     */
    public static function funded($value) {
		if($value > 100) {
		    $value = 100;
		};
		return $value;
    }
    
    /**
     * Calculate funded percents
     * @param float $goal
     * @param float $funded
     */
    public static function percents($goal, $funded) {
		
        $percents = 0;
        if($goal > 0) {
            $percents = round( ($funded/$goal) * 100, 2 );
        }
        
		return $percents;
    }
    
    /**
     * 
     * This method generates a code that display a video
     * @param string $value
     */
    public static function video($value) {
        
        jimport("itprism.video.embed");
        $videoEmbed = new ITPrismVideoEmbed($value);
        $html = $videoEmbed->getHtmlCode();
        
        return $html;
    }
    
    
    /**
     * Method to sort a column in a grid
     *
     * @param   string  $title          The link title
     * @param   string  $order          The order field for the column
     * @param   string  $direction      The current direction
     * @param   string  $selected       The selected ordering
     * @param   string  $task           An optional task override
     * @param   string  $new_direction  An optional direction for the new column
     * @param   string  $tip            An optional text shown as tooltip title instead of $title
     *
     * @return  string
     *
     * @since   11.1
     */
    public static function sort($title, $order, $direction = 'asc', $selected = 0, $task = null, $new_direction = 'asc', $tip = '') {

		$direction = strtolower($direction);
		$icon = array('arrow-up', 'arrow-down');
		$index = (int) ($direction == 'desc');

		if ($order != $selected) {
			$direction = $new_direction;
		} else {
			$direction = ($direction == 'desc') ? 'asc' : 'desc';
		}

		$html = '<a href="#" onclick="Joomla.tableOrdering(\'' . $order . '\',\'' . $direction . '\',\'' . $task . '\');return false;" class="hasTooltip" title="' . JHtml::tooltipText(($tip ? $tip : $title), 'JGLOBAL_CLICK_TO_SORT_THIS_COLUMN') . '">';

		if (isset($title['0']) && $title['0'] == '<') {
			$html .= $title;
		} else {
			$html .= JText::_($title);
		}

		if ($order == $selected) {
			$html .= ' <i class="icon-' . $icon[$index] . '"></i>';
		}

		$html .= '</a>';

		return $html;
	}
    
    public static function reward($rewardId, $reward, $txnId, $sent = 0, $canEdit = false) {
        
        $state = (!$sent) ? 1 : 0;
        
        $html = array();
        
        if(!$rewardId) {
            $icon  = "media/com_crowdfunding/images/noreward_16.png";
            $title = 'title="' . JText::_('COM_CROWDFUNDING_REWARD_NOT_SELECTED') . '"';
        } else {
            
            if(!$sent) {
                
                $icon  = "media/com_crowdfunding/images/reward_16.png";
                
                // Prepare tooltip text
                if($canEdit) {
                    $tooltipText = JText::sprintf('COM_CROWDFUNDING_SENT_REWARD_TOOLTIP', htmlspecialchars($reward, ENT_QUOTES, "UTF-8"), "<br />");
                } else {
                    $tooltipText = htmlspecialchars($reward, ENT_QUOTES, "UTF-8")."<br />".JText::_('COM_CROWDFUNDING_REWARD_NOT_SENT');
                }
                $title = 'title="' . $tooltipText . '"';
                
            } else {
                
                $icon  = "media/com_crowdfunding/images/reward_sent_16.png";
                
                // Prepare tooltip text
                if($canEdit) {
                    $tooltipText = JText::sprintf('COM_CROWDFUNDING_NOT_SENT_REWARD_TOOLTIP', htmlspecialchars($reward, ENT_QUOTES, "UTF-8"), "<br />");
                } else {
                    $tooltipText = htmlspecialchars($reward, ENT_QUOTES, "UTF-8")."<br />".JText::_('COM_CROWDFUNDING_REWARD_HAS_BEEN_SENT');
                }
                
                $title = 'title="' . $tooltipText . '"';
                
            }
            
        }
        
        // Prepare link
        if(!$rewardId OR !$canEdit) {
            $link = "javascript: void(0);";
        } else {
            $link = JRoute::_("index.php?option=com_crowdfunding&task=rewards.changeState&txn_id=".(int)$txnId."&state=".(int)$state."&".JSession::getFormToken()."=1");
        }
        
        $html[] = '<a href="'.$link.'" class="hasTooltip" '.$title.'>';
        $html[] = '<img src="'.$icon.'" width="16" height="16" />';
        $html[] = '</a>';
    
        return implode(" ", $html);
    }
    
    
    public static function projectTitle($title, $categoryState, $slug, $catSlug) {
    
        $html = array();

        if(!$categoryState) {
            $html[] = htmlspecialchars($title, ENT_QUOTES, "utf-8");
            $html[] = '<button type="button" class="hasTooltip" title="'.htmlspecialchars(JText::_("COM_CROWDFUNDING_SELECT_OTHER_CATEGORY_TOOLTIP"), ENT_QUOTES, "utf-8").'">';
            $html[] = '<i class="icon-info-sign"></i>';
            $html[] = '</button>';
        } else {
            
            $html[] = '<a href="'. JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($slug, $catSlug)) .'">';
            $html[] = htmlspecialchars($title, ENT_QUOTES, "utf-8");
            $html[] = '</a>';
        }     
       
        return implode("\n", $html);
    }
    
    public static function date($date, $format = "d F Y") {
        
        if(CrowdFundingHelper::isValidDate($date)) {
            $date = JHtml::_("date", $date, $format);
        } else {
            $date = "---";
        }
        
        return $date;
    }
    
    public static function duration($startDate, $endDate, $days, $format = "d F Y") {
    
        $otuput = "";
        
        if(!empty($days)) {
            $otuput .= JText::sprintf("COM_CROWDFUNDING_DURATION_DAYS", (int)$days);
            
            // Display end date
            if(CrowdFundingHelper::isValidDate($endDate)) {
                $otuput .= '<div class="info-mini">';
                $otuput .= JText::sprintf("COM_CROWDFUNDING_DURATION_END_DATE", JHTML::_('date', $endDate, $format));
                $otuput .= '</div>';
            }
            
        } else if(CrowdFundingHelper::isValidDate($endDate)) {
            $otuput .= JText::sprintf("COM_CROWDFUNDING_DURATION_END_DATE", JHTML::_('date', $endDate, $format));
        } else {
            $otuput .= "---";
        }
    
        return $otuput;
    }
    
    public static function postedby($name, $date, $link = null) {
    
        if(!empty($link)) {
            $profile = '<a href="'.$link.'">'.htmlspecialchars($name, ENT_QUOTES, "utf-8").'</a>';
        } else {
            $profile = $name;
        }
        
        $date = JHTML::_('date', $date, JText::_('DATE_FORMAT_LC3'));
        $html = JText::sprintf("COM_CROWDFUNDING_POSTED_BY", $profile, $date);
         
        return $html;
    }
    
    public static function name($name) {
    
        if(!empty($name)) {
            $output = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
        } else {
            $output = JText::_("COM_CROWDFUNDING_ANONYMOUS");
        }
    
        return $output;
    }
    
    /**
     * Display a percent string.
     *
     * <code>
     * $percentString = CrowdFundingHelper::percent(100);
     * echo $percentString;
     * </code>
     *
     * @param string $value
     * @return string
     */
    public static function percent($value) {
    
        if(!$value) {
            $value = "0.0";
        }
    
        return $value . "%";
    }
    
    public static function socialProfileLink($link, $name, $options = array()) {
    
        if(!empty($link)) {
            
            $targed = "";
            if(!empty($options["target"])) {
                $targed = 'target="'.JArrayHelper::getValue($options, "target").'"';
            }
            
            $output = '<a href="'.$link.'" '.$targed.'>'.htmlspecialchars($name, ENT_QUOTES, "UTF-8").'</a>';
            
        } else {
            $output = htmlspecialchars($name, ENT_QUOTES, "utf-8");
        }
    
        return $output;
    }
}
