<?php
/**
 * @package		 ITPrism Plugins
 * @subpackage	 CrowdFunding
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
 * CrowdFunding Navigation Plugin
 *
 * @package		ITPrism Plugins
 * @subpackage	CrowdFunding
 */
class plgContentCrowdFundingInfo extends JPlugin {
    
    
    public function onContentAfterDisplayMedia($context, &$article, &$params, $page = 0) {
        
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
        
        $html  = '<div class="clearfix"></div><div class="row crowdf-info">';
        
        if($this->params->get("display_short_desc")) {
            $html .= '<div class="span8">'.$article->short_desc.'</div>';
        }
        
        /*if($this->params->get("display_start_date")) {
            $html .= '<li><a href="">'.JText::_("PLG_CONTENT_CROWDFUNDINGNAV_UPDATES")."</a></li>";
        }
        
        if($this->params->get("display_end_date")) {
            $html .= '<li><a href="">'.JText::_("PLG_CONTENT_CROWDFUNDINGNAV_COMMENTS")."</a></li>";
        }
        
        if($this->params->get("display_remaind_me")) {
            $html .= '<li><a href="">'.JText::_("PLG_CONTENT_CROWDFUNDINGNAV_FUNDERS")."</a></li>";
        }
        
        if($this->params->get("display_follow")) {
            $html .= '<li><a href="">'.JText::_("PLG_CONTENT_CROWDFUNDINGNAV_FUNDERS")."</a></li>";
        }*/
        
        $html .= '</div>';
        
        return $html;
        
    }
    
}