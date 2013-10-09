<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport( 'joomla.application.component.controller' );

/**
 * CrowdFunding export controller
 *
 * @package      CrowdFunding
 * @subpackage   Components
 */
class CrowdFundingControllerExport extends JControllerLegacy {
    
    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Export', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function download() {
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $type  = $app->input->get->getCmd("type");
        $model = $this->getModel();
        
        try {
            
            switch($type) {
                case "locations":
                    $output      = $model->getLocations();
                    $fileName    = "locations.xml";
                    break;
                
                case "currencies": 
                    $output      = $model->getCurrencies();
                    $fileName    = "currencies.xml";
                    break;
                    
                case "countries":
                    $output      = $model->getCountries();
                    $fileName    = "countries.xml";
                    break;
                    
                default: // Error
                    $output      = "";
                    $fileName    = "error.xml";
                    break;
            }
            
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
        
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.archive');
        
        $tmpFolder   = JPath::clean( $app->getCfg("tmp_path") );
        
        $archiveName = JFile::stripExt(JFile::getName($fileName))."_".substr(JApplication::getHash(time()), 0, 4);
        $archiveFile = $archiveName.".zip";
        $destination = $tmpFolder.DIRECTORY_SEPARATOR.$archiveFile;
        
        // compression type
        $zipAdapter   = JArchive::getAdapter('zip'); 
        $filesToZip[] = array(
        	'name' => $fileName, 
        	'data' => $output
        ); 
        
        $zipAdapter->create($destination, $filesToZip, array());
        
        $filesize = filesize($destination);
        
        JResponse::setHeader('Content-Type', 'application/octet-stream', true);
        JResponse::setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        JResponse::setHeader('Content-Transfer-Encoding', 'binary', true);
        JResponse::setHeader('Pragma', 'no-cache', true);
        JResponse::setHeader('Expires', '0', true);
        JResponse::setHeader('Content-Disposition', 'attachment; filename='.$archiveFile, true);
        JResponse::setHeader('Content-Length', $filesize, true);
        
        $doc = JFactory::getDocument();
        $doc->setMimeEncoding('application/octet-stream');

        echo JFile::read($destination);
        
    }
    
}