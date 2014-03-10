<?php
/**
* @package      CrowdFunding
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

jimport("joomla.filesystem.file");
jimport("joomla.filesystem.path");
jimport("joomla.filesystem.folder");

/**
 * This class provieds functionality that manage log files.
 */
class CrowdFundingLogFiles implements Iterator, Countable, ArrayAccess {
    
    protected $items  = array();
    
    /**
     * A list with files, which should be in the litst with items.
     * 
     * @var array
     */
    protected $files  = array();
    
    protected $position = 0;
    
    /**
     * Initialize the object.
     * 
     * @param JDatabase Database object.
     * @param array     Projects IDs
     */
    public function __construct($files = array()) {
        $this->files = $files;
    }

    public function load() {
        
        // Read files in folder /logs
        $config    = JFactory::getConfig();
        $logFolder = $config->get("log_path");
    
        $files     = JFolder::files($logFolder);
        if(!is_array($files)) {
            $files = array();
        }
    
        foreach($files as $key => $file) {
            if(strcmp("index.html", $file) != 0) {
                $this->items[] = JPath::clean($logFolder .DIRECTORY_SEPARATOR. $files[$key]);
            }
        }
    
        if(!empty($this->files)) {
            
            foreach($this->files as $fileName) {
                
                // Check for a file in site folder.
                $errorLogFile = JPath::clean(JPATH_ROOT .DIRECTORY_SEPARATOR. $fileName);
                if(JFile::exists($errorLogFile)) {
                    $this->items[] = $errorLogFile;
                }
                
                // Check for a file in admin folder.
                $errorLogFile = JPath::clean(JPATH_BASE .DIRECTORY_SEPARATOR. $fileName);
                if(JFile::exists($errorLogFile)) {
                    $this->items[] = $errorLogFile;
                }
            }
        }
        
        sort($this->items);
        
    }
    
    public function rewind() {
        $this->position = 0;
    }
    
    public function current() {
        return (!isset($this->items[$this->position])) ? null : $this->items[$this->position];
    }
    
    public function key() {
        return $this->position;
    }
    
    public function next() {
        ++$this->position;
    }
    
    public function valid() {
        return isset($this->items[$this->position]);
    }
    
    public function count() {
        return (int)count($this->items);
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->items[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->items[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }
    
}
