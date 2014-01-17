<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.modeladmin');

class CrowdFundingModelLog extends JModelAdmin {

    protected $item = array();
     
    protected $logFiles = array(
        "/administrator/error_log",            
        "/error_log",
        "/php_errorlog",
    );
    
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Log', $prefix = 'CrowdFundingTable', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get model state variables
     *
     * @since   12.2
     */
    public function populateState() {
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        // Load the filter state.
        $value = $app->input->get("id");
        $this->setState($this->getName().".id", $value);

    }
    
    public function getForm($data = array(), $loadData = true) {
        
    }
    
    /**
     * This method loads the data from a log file.
     * 
     * @param string Filename
     * 
     * @return string
     */
    public function loadLogFile($file) {
        
        $files  = CrowdFundingHelper::getLogFiles();
        
        $output = "";
        
        foreach($files AS $sourceFile) {
            
            $sourceFile = JPath::clean($sourceFile);
            $value      = str_replace(JPATH_ROOT, "", $sourceFile);
            
            if(strcmp($value, $file) == 0) {
                $output = JFile::read($sourceFile);
                
            }
        }
        
        return $output;
    }
    
    /**
     * This method deletes the data from a log file.
     * 
     * @param string Filename
     * 
     * @return boolean True on success, false on failure.
     */
    public function deleteFile($file) {
        
        $files  = CrowdFundingHelper::getLogFiles();
        
        foreach($files AS $sourceFile) {
            
            $sourceFile = JPath::clean($sourceFile);
            
            if(strcmp($sourceFile, $file) == 0) {
                if(JFile::delete($sourceFile)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Delete all records in logs table.
     * 
     * @return void
     */
    public function removeAll() {
    
        $db     = $this->getDbo();
        /** @var $db JDatabaseMySQLi **/
    
        $db->truncateTable("#__crowdf_logs");
    
    }
    
    /**
     * Clean and prepare secure file.
     * 
     * @param string $file
     * 
     * @return string|null
     */
    public function prepareFile($file) {
        
        $clenFile    = null;
        $fileName    = basename($file);
        $logsFolder  = DIRECTORY_SEPARATOR."logs";
        
        // Prepare file error_log
        if(strcmp("error_log", $fileName) == 0) {
            
            if(1 == strpos($file, "administrator")) {
                $clenFile = DIRECTORY_SEPARATOR."administrator".DIRECTORY_SEPARATOR."error_log";
            } else {
                $clenFile = DIRECTORY_SEPARATOR."error_log";
            }
            
        } else {
        
            // Prepare file in logs filder.
            if(0 == strpos($file, $logsFolder)) {
                $clenFile = $logsFolder.DIRECTORY_SEPARATOR.$fileName;
            }
            
        }
            
        $clenFile = JPATH_ROOT.$clenFile;
        
        // Validate the file.
        if(!JFile::exists($clenFile)) {
            $clenFile = null;
        }
        
        return $clenFile;
    }
}