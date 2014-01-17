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
 * Script file of the component
 */
class pkg_crowdFundingInstallerScript {
    
    /**
     * method to install the component
     *
     * @return void
     */
    public function install($parent) {
    }
    
    /**
     * method to uninstall the component
     *
     * @return void
     */
    public function uninstall($parent) {
    }
    
    /**
     * method to update the component
     *
     * @return void
     */
    public function update($parent) {
    }
    
    /**
     * method to run before an install/update/uninstall method
     *
     * @return void
     */
    public function preflight($type, $parent) {
    }
    
    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    public function postflight($type, $parent) {
        
        if(!defined("COM_CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR")) {
            define("COM_CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR ."com_crowdfunding");
        }
        
        // Register Component helpers
        JLoader::register("CrowdFundingInstallHelper", COM_CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR."helpers".DIRECTORY_SEPARATOR."install.php");
    
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        
        $params             = JComponentHelper::getParams("com_crowdfunding");
        $this->imagesFolder = JFolder::makeSafe($params->get("images_directory", "images/crowdfunding"));
        $this->imagesPath   = JPath::clean( JPATH_SITE.DIRECTORY_SEPARATOR.$this->imagesFolder );
        
        // Create images folder
        if(!is_dir($this->imagesPath)){
            CrowdFundingInstallHelper::createFolder($this->imagesPath);
        }
        
        // Start table with the information
        CrowdFundingInstallHelper::startTable();
    
        // Requirements
        CrowdFundingInstallHelper::addRowHeading(JText::_("COM_CROWDFUNDING_MINIMUM_REQUIREMENTS"));
        
        // Display result about verification for existing folder 
        $title  = JText::_("COM_CROWDFUNDING_IMAGE_FOLDER");
        $info   = $this->imagesFolder;
        if(!is_dir($this->imagesPath)) {
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success"  , "text" => JText::_("JYES"));
        }
        CrowdFundingInstallHelper::addRow($title, $result, $info);
        
        // Display result about verification for writeable folder 
        $title  = JText::_("COM_CROWDFUNDING_WRITABLE_FOLDER");
        $info   = $this->imagesFolder;
        if(!is_writable($this->imagesPath)) {
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success"  , "text" => JText::_("JYES"));
        }
        CrowdFundingInstallHelper::addRow($title, $result, $info);
        
        // Display result about verification for GD library
        $title  = JText::_("COM_CROWDFUNDING_GD_LIBRARY");
        $info   = "";
        if(!extension_loaded('gd') AND function_exists('gd_info')) {
            $result = array("type" => "important", "text" => JText::_("COM_CROWDFUNDING_WARNING"));
        } else {
            $result = array("type" => "success"  , "text" => JText::_("JON"));
        }
        CrowdFundingInstallHelper::addRow($title, $result, $info);
        
        // Display result about verification for cURL library
        $title  = JText::_("COM_CROWDFUNDING_CURL_LIBRARY");
        $info   = "";
        if( !extension_loaded('curl') ) {
            $info   = JText::_("COM_CROWDFUNDING_CURL_INFO");
            $result = array("type" => "important", "text" => JText::_("JOFF"));
        } else {
            $result = array("type" => "success"  , "text" => JText::_("JON"));
        }
        CrowdFundingInstallHelper::addRow($title, $result, $info);
        
        // Display result about verification Magic Quotes
        $title  = JText::_("COM_CROWDFUNDING_MAGIC_QUOTES");
        $info   = "";
        if( get_magic_quotes_gpc() ) {
            $info   = JText::_("COM_CROWDFUNDING_MAGIC_QUOTES_INFO");
            $result = array("type" => "important", "text" => JText::_("JON"));
        } else {
            $result = array("type" => "success"  , "text" => JText::_("JOFF"));
        }
        CrowdFundingInstallHelper::addRow($title, $result, $info);
        
        // Display result about verification FileInfo
        $title  = JText::_("COM_CROWDFUNDING_FILEINFO");
        $info   = "";
        if( !function_exists('finfo_open') ) {
            $info   = JText::_("COM_CROWDFUNDING_FILEINFO_INFO");
            $result = array("type" => "important", "text" => JText::_("JOFF"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdFundingInstallHelper::addRow($title, $result, $info);
        
        // Display result about verification FileInfo
        $title  = JText::_("COM_CROWDFUNDING_PHP_VERSION");
        $info   = "";
        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            $result = array("type" => "important", "text" => JText::_("COM_CROWDFUNDING_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdFundingInstallHelper::addRow($title, $result, $info);
        
        // Display result about verification of installed ITPrism Library
        jimport("itprism.version");
        $title  = JText::_("COM_CROWDFUNDING_ITPRISM_LIBRARY");
        $info   = "";
        if( !class_exists("ITPrismVersion") ) {
            $info   = JText::_("COM_CROWDFUNDING_ITPRISM_LIBRARY_DOWNLOAD");
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdFundingInstallHelper::addRow($title, $result, $info);
        
        // Installed extensions
        
        CrowdFundingInstallHelper::addRowHeading(JText::_("COM_CROWDFUNDING_INSTALLED_EXTENSIONS"));
        
        // CrowdFunding Library
        $result = array("type" => "success"  , "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdFundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CROWDFUNDING_LIBRARY"), $result, JText::_("COM_CROWDFUNDING_LIBRARY"));
        
        // Plugins
        
        // Content - CrowdFunding - Navigation
        $result = array("type" => "success"  , "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdFundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CONTENT_CROWDFUNDING_NAVIGATION"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));
        
        // Content - CrowdFunding - Share
        $result = array("type" => "success"  , "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdFundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CONTENT_CROWDFUNDING_SHARE"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));
        
        // Content - CrowdFunding - Admin Mail
        $result = array("type" => "success"  , "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdFundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CONTENT_CROWDFUNDING_ADMIN_MAIL"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));
        
        // Content - CrowdFunding - User Mail
        $result = array("type" => "success"  , "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdFundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CONTENT_CROWDFUNDING_USER_MAIL"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));
        
        // System - CrowdFunding - Modules
        $result = array("type" => "success"  , "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdFundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_SYSTEM_CROWDFUNDINGMODULES"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));
        
        // CrowdFunding Payment - PayPal
        $result = array("type" => "success"  , "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdFundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CROWDFUNDINGPAYMENT_PAYPAL"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));
        
        // Modules
        
        // CrowdFunding Info
        $result = array("type" => "success"  , "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdFundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CROWDFUNDING_MODULE_INFO"), $result, JText::_("COM_CROWDFUNDING_MODULE"));
        
        // CrowdFunding Details
        $result = array("type" => "success"  , "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdFundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CROWDFUNDING_MODULE_DETAILS"), $result, JText::_("COM_CROWDFUNDING_MODULE"));
        
        // CrowdFunding Rewards
        $result = array("type" => "success"  , "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdFundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CROWDFUNDING_MODULE_REWARDS"), $result, JText::_("COM_CROWDFUNDING_MODULE"));
        
        // End table
        CrowdFundingInstallHelper::endTable();
            
        echo JText::sprintf("COM_CROWDFUNDING_MESSAGE_REVIEW_SAVE_SETTINGS", JRoute::_("index.php?option=com_crowdfunding"));
        
        jimport("itprism.version");
        if(!class_exists("ITPrismVersion")) {
            echo JText::_("COM_CROWDFUNDING_MESSAGE_INSTALL_ITPRISM_LIBRARY");
        }
        
    }
}
