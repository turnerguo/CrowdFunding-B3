<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.modelitem');

class CrowdFundingModelLog extends JModelItem {

    protected $item = array();
     
    protected $logFiles = array(
        "/administrator/error_log",            
        "/error_log",            
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
    
    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed    Object on success, false on failure.
     *
     * @since   12.2
     */
    public function getItem($pk = null) {
        
        $pk = (!empty($pk)) ? $pk : (int)$this->getState($this->getName() . '.id');
        
        $storedId = $this->getStoreId($pk);
        
        if(!isset($this->item[$storedId])) {
            
            $table = $this->getTable();
            
            if ($pk > 0) {
                // Attempt to load the row.
                $return = $table->load($pk);
                
                // Check for a table object error.
                if ($return === false && $table->getError()) {
                    $this->setError($table->getError());
                    return false;
                }
            }
            
            // Convert to the JObject before adding other data.
            $properties = $table->getProperties(1);
            $this->item[$storedId] = JArrayHelper::toObject($properties, 'JObject');
            
        }
        
        return $this->item[$storedId];
    }
    
    /**
     * Method to delete one or more records.
     *
     * @param   array  &$pks  An array of record primary keys.
     *
     * @since   12.2
     */
    public function delete(&$pks) {
        
        $pks   = (array) $pks;
        $table = $this->getTable();
    
        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                if (!$table->delete($pk)) {
                    throw new Exception($table->getError());
                }
            }
        }
    
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