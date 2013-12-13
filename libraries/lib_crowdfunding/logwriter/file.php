<?php
/**
 * @package      CrowdFunding
 * @subpackage   Library
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport("joomla.filesystem.path");
jimport("joomla.filesystem.folder");
jimport("joomla.filesystem.file");
JLoader::register("CrowdFundingLogWriter", JPATH_LIBRARIES . DIRECTORY_SEPARATOR . "userideas" . DIRECTORY_SEPARATOR . "interface" . DIRECTORY_SEPARATOR . "logwriter.php");

class CrowdFundingLogWriterFile implements CrowdFundingLogWriter {
    
    protected $file;
     
    protected $title;
    protected $type;
    protected $data;
    protected $record_date;
    
    public function __construct($file) {
        
        $this->file = $this->validate($file);

        if(!$this->file) {
            throw new Exception("LIB_CROWDFUNDING_INVALID_FILE", 500);
        }
        
    }
    
    protected function validate($file) {
        
        JPath::clean($file);
        
        $folder = dirname($file);
        
        if(!JFolder::exists($folder)) {
            throw new Exception("LIB_CROWDFUNDING_FOLDER_DOES_NOT_EXIST", 500);
        }
        
        // Create file
        if(!JFile::exists($file)) {
            $buffer = "#<?php die('Forbidden.'); ?>\n";
            JFile::write($file, $buffer);
        }
        
        return $file;
        
    }
    
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
    
    public function setType($type) {
        $this->type = $type;
        return $this;
    }
    
    public function setData($data) {
        $this->data = $data;
        return $this;
    }
    
    public function setDate($date) {
        $this->record_date = $date;
        return $this;
        
    }
    
    public function store() {
        
        // Log it into the log file.
        $logData   = "=========================================\n";
        $logData  .= "Date Time: ".$this->record_date."\n";
        $logData  .= $this->title." (".$this->type.") \n";
        if(!empty($this->data)) {
            $logData .= var_export($this->data, true)."\n";
        }
        
        file_put_contents($this->file, $logData, FILE_APPEND);
        
    }
    
}