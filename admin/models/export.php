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
defined( '_JEXEC' ) or die;

jimport( 'joomla.application.component.model' );

class CrowdFundingModelExport extends JModel {
    
    public function getCurrencies() {
        
        $db     = $this->getDbo();
        /** @var $db JDatabaseMySQLi **/
        
        // Create a new query object.
        $query  = $db->getQuery(true);

        // Select the required fields from the table.
        $query
            ->select('a.id, a.title, a.abbr, a.symbol, a.position')
            ->from($db->quoteName('#__crowdf_currencies').' AS a');
        
        
        $db->setQuery($query);
        $results = $db->loadAssocList();
        
        $output = $this->prepareXML($results, "currencies", "currency");
        
        return $output;
    }
    
    public function getLocations() {
        
        $db     = $this->getDbo();
        /** @var $db JDatabaseMySQLi **/
        
        // Create a new query object.
        $query  = $db->getQuery(true);

        // Select the required fields from the table.
        $query
            ->select(
            'a.id, a.name, a.latitude, a.longitude, a.country_code, '.
            'a.timezone, a.state_code, a.published'
            )
            ->from($db->quoteName('#__crowdf_locations').' AS a');
        
        
        $db->setQuery($query);
        $results = $db->loadAssocList();
        
        $output = $this->prepareXML($results, "locations", "location");
        
        return $output;
    }
    
    public function getCountries() {
    
        $db     = $this->getDbo();
        /** @var $db JDatabaseMySQLi **/
    
        // Create a new query object.
        $query  = $db->getQuery(true);
    
        // Select the required fields from the table.
        $query
        ->select('a.id, a.name, a.code')
        ->from($db->quoteName('#__crowdf_countries').' AS a');
    
        $db->setQuery($query);
        $results = $db->loadAssocList();
    
        $output = $this->prepareXML($results, "countries", "country");
    
        return $output;
    }
    
    protected function prepareXML($results, $root, $child) {
        
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><'.$root.'/>');
        
        if(!empty($root) AND !empty($child) ) {
            
            foreach( $results as $data ) {
                
                $item = $xml->addChild($child);
                
                foreach( $data as $key => $value ) {
                    $item->addChild($key, $value);
                }
            }
        }
        
        return $xml->asXML();

    } 
}