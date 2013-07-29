<?php
/**
 * @package		 CrowdFunding
 * @subpackage	 Plugins
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

jimport('joomla.plugin.plugin');

/**
 * CrowdFunding Info Plugin
 *
 * @package		CrowdFunding
 * @subpackage	Plugins
 */
class plgContentCrowdFundingInfo extends JPlugin {
    
    public function onContentAfterDisplay($context, &$item, &$params, $page = 0) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/

        if($app->isAdmin()) {
            return;
        }

        $doc     = JFactory::getDocument();
        /**  @var $doc JDocumentHtml **/
        
        // Check document type
        $docType = $doc->getType();
        if(strcmp("html", $docType) != 0){
            return;
        }
       
        if(strcmp("com_crowdfunding.details", $context) != 0){
            return;
        }
        
        // Load language
        $this->loadLanguage();
        
        if($this->params->get("load_css", 1)) {
            $doc->addStyleSheet(JURI::root() . "plugins/content/crowdfundinginfo/style.css");
        }
        
        $user = JFactory::getUser($item->user_id);
        
        $locationString = "";
        if($this->params->get("display_location", 0) OR $this->params->get("display_map", 0) OR $this->params->get("display_map", 0)) {
            $location = $this->getLocation($item->location);
            if(!empty($location)) {
                $locationString = "<p>".$location->name.", ".$location->country_code."</p>";
            }
        }
        
        $socialPlatform  = $params->get("integration_social_platform");
        $avatarPlatform  = $params->get("integration_avatars");
        
        $socialProfile  = JHtml::_("crowdfunding.socialProfile", $socialPlatform, $user);
        $socialAvatar   = JHtml::_("crowdfunding.socialAvatar", $avatarPlatform, $user, "media/com_crowdfunding/images/no-profile.png");
        
        if(!$socialProfile) {
            $socialProfileLink = '<a class="pull-left" href="javascript: void(0);">';
            $socialProfileLinkName = $user->name;
        } else {
            $socialProfileLink = '<a class="pull-left" href="'.$socialProfile.'">';
            $socialProfileLinkName = '<a href="'.$socialProfile.'">'.$user->name.'</a>';
        }
        
        $html  = '<div class="clearfix"></div>';
        
        // Prepare period
        $dates = "";
        if($this->params->get("display_dates", 0)) {
            $dates = '
            <div class="span3 crowdf-info-content">
                <h5>'.JText::_("PLG_CONTENT_CROWDFUNDINGINFO_FUNDING_PERIOD").'</h5>
                <p>'.JText::sprintf("PLG_CONTENT_CROWDFUNDINGINFO_DISPLAY_START_DATE", JHtml::_("date", $item->funding_start, JText::_("DATE_FORMAT_LC3")) ).'</p>
                <p>'.JText::sprintf("PLG_CONTENT_CROWDFUNDINGINFO_DISPLAY_END_DATE", JHtml::_("date", $item->funding_end, JText::_("DATE_FORMAT_LC3")) ).'</p>
            </div>';
        }
        
        // Prepare map
        $mapCode="";
        if($this->params->get("display_map", 0) AND !empty($location)) {
            $mapCode = $this->getMapCode($doc, $location);
        }
        
        // Prepare output
        $html  .= '
            <div class="row-fluid crowdf-info">
                <div class="span3 crowdf-info-content">
                    <h5>'.JText::_("PLG_CONTENT_CROWDFUNDINGINFO_PROJECT_BY").'</h5>
                    <div class="media">
                        '.$socialProfileLink.'
                            <img class="media-object" src="'.$socialAvatar.'">
                        </a>
                        <div class="media-body">
                            <h6 class="media-heading">'.$socialProfileLinkName.'</h6>
                            '.$locationString.'
                        </div>
                    </div>
                </div>

                '.$mapCode.'
                '.$dates.'
            </div>
        ';
        
        return $html;
        
    }
    
    private function getMapCode($doc, $location) {
        
        // Set Google map API key and load the script
        $apiKey = "";
        if($this->params->get("google_maps_key")) {
            $apiKey = "&amp;key=".$apiKey;
        }
        $doc->addScript("//maps.googleapis.com/maps/api/js?sensor=false".$apiKey);
        
        // Put the JS code that initializes the map.
        $js = '
        function initialize() {
                
            var cfLatlng = new google.maps.LatLng('.$location->latitude.', '.$location->longitude.');
                
            var map_canvas = document.getElementById("crowdf_map_canvas");
            var map_options = {
              center: cfLatlng,
              disableDefaultUI: true,
              zoom: 8,
              mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            var map = new google.maps.Map(map_canvas, map_options)
                    
            var marker = new google.maps.Marker({
                position: cfLatlng,
                map: map
            });
                    
              
          }
        google.maps.event.addDomListener(window, "load", initialize);
        ';
        
        $doc->addScriptDeclaration($js);
        
        // Put the map element style
        $style = 
        '#crowdf_map_canvas {
            width:  '.$this->params->get("google_maps_width", 300).'px;
            height: '.$this->params->get("google_maps_height", 300).'px;
        }';
        $doc->addStyleDeclaration($style);
        
        // Prepare the HTML code
        $code = '
            <div class="span6 crowdf-info-content">
                <div id="crowdf_map_canvas"></div>
            </div>';
        
        return $code;
    }
    
    private function getLocation($id) {
        
        $db = JFactory::getDbo();
        /** @var $db JDatabaseMySQLi **/
        
        $query = $db->getQuery(true);
        $query
            ->select("a.name, a.latitude, a.longitude, a.country_code")
            ->from($db->quoteName("#__crowdf_locations") . " AS a")
            ->where("a.id = " .(int)$id)
            ->where("a.published = 1");
        
        $db->setQuery($query);
        $result = $db->loadObject();
        
        return $result;
    }
}