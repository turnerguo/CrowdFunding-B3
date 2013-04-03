<?php
/**
 * @package      ITPrism Components
 * @subpackage   CrowdFunding
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die;

// Load router
if(!class_exists("CrowdFundingHelperRoute")) {
    $helperDir = JPATH_SITE . DIRECTORY_SEPARATOR. "components" . DIRECTORY_SEPARATOR ."com_crowdfunding". DIRECTORY_SEPARATOR . "helpers";
    JLoader::register("CrowdFundingHelperRoute", $helperDir . DIRECTORY_SEPARATOR . "route.php");
}

/**
 * Method to build Route
 * @param array $query
 */
function CrowdFundingBuildRoute(&$query){
    
    $segments = array();
    
    // get a menu item based on Itemid or currently active
    $app  = JFactory::getApplication();
    $menu = $app->getMenu();
    
    // we need a menu item.  Either the one specified in the query, or the current active one if none specified
    if(empty($query['Itemid'])){
        $menuItem = $menu->getActive();
    }else{
        $menuItem = $menu->getItem($query['Itemid']);
    }
    
    $mOption	= (empty($menuItem->query['option'])) ? null : $menuItem->query['option'];
    $mView	    = (empty($menuItem->query['view']))   ? null : $menuItem->query['view'];
	$mCatid	    = (empty($menuItem->query['catid']))  ? null : $menuItem->query['catid'];
	$mId	    = (empty($menuItem->query['id']))     ? null : $menuItem->query['id'];
	
	// If is set view and Itemid missing, we have to put the view to the segments
	if (isset($query['view'])) {
		$view = $query['view'];
		
		if (empty($query['Itemid']) OR ($mOption !== "com_crowdfunding")) {
			$segments[] = $query['view'];
		}

		// We need to keep the view for forms since they never have their own menu item
		if ($view != 'form') {
			unset($query['view']);
		}
	};
    
    // are we dealing with a category that is attached to a menu item?
	if (isset($view) AND ($mView == $view) AND (isset($query['id'])) AND ($mId == intval($query['id']))) {
		unset($query['view']);
		unset($query['catid']);
		unset($query['id']);
		return $segments;
	}
	
    // Views
	if(isset($view)) {
	    
    	switch($view) {
    	    
    	    case "category":
    	        if ($mId != intval($query['id']) || $mView != $view) {
    	            $catId = $query['id'];
    	            
    	            CrowdFundingHelperRoute::prepareCategoriesSegments($catId, $segments, $mId);
    			    unset($query['id']);
		            unset($query['catid']);
    	        }
    	        
    	        break;
    	        
    	        
    	    case "backing":
    	        
    	        $catId      = $query['catid'];
    	        CrowdFundingHelperRoute::prepareCategoriesSegments($catId, $segments, $mId);
    	        
    	        $id = $query['id'];
				$segments[] = $id;
				
				unset($query['id']);
	            unset($query['catid']);
    	        
    	        $segments[] = "backing";
    	        
    	        if(isset($query["rid"])) {
    	            
    	            if( !empty($query["rid"]) ) {
                        $segments[] = $query["rid"];
    	            }
    	            
    	            unset($query["rid"]);
    	        }
    	        
    	        break;
    	        
	       case "details":
	            
    	        $catId      = $query['catid'];
    	        CrowdFundingHelperRoute::prepareCategoriesSegments($catId, $segments, $mId);
    	        
    	        $id = $query['id'];
				$segments[] = $id;
				
				unset($query['id']);
	            unset($query['catid']);
    	        break;
    	        
	        case "embed":
	            
    	        $catId = $query['catid'];
    	        CrowdFundingHelperRoute::prepareCategoriesSegments($catId, $segments, $mId);
    	        unset($query['catid']);
    	        
    	        $id = $query['id'];
				$segments[] = $id;
				unset($query['id']);
	            
	            $segments[] = "embed";
	            
    	        break;
    	        
	        case "project": // Form for adding prajects
	            
	            if($menuItem->query["view"] == $view) {
	                unset($query['view']);
	            }
	            
	            break;
    	}
        
	}
    
	// Layout
    if (isset($query['layout'])) {
		if (!empty($query['Itemid']) && isset($menuItem->query['layout'])) {
			if ($query['layout'] == $menuItem->query['layout']) {
				unset($query['layout']);
			}
		} else {
			if ($query['layout'] == 'default') {
				unset($query['layout']);
			}
		}
	};
	
	// Screen
	if(isset($query["screen"])) {
	    $segments[] = $query["screen"];
	    unset($query['screen']);
	}
    
    return $segments;
}

/**
 * Method to parse Route
 * @param array $segments
 */
function CrowdFundingParseRoute($segments){
    
    $vars = array();
    
    //Get the active menu item.
    $app        = JFactory::getApplication();
    $menu       = $app->getMenu();
    $item       = $menu->getActive();
    
    $db         = JFactory::getDBO();
    
    // Count route segments
    $count      = count($segments);
    
    // Standard routing for articles.  If we don't pick up an Itemid then we get the view from the segments
	// the first segment is the view and the last segment is the id of the details, category or payment.
    if(!isset($item)) {
        $vars['view']   = $segments[0];
        $vars['catid']  = $segments[$count - 1];
        return $vars;
    } 
    
	if($count == 3) { 
	    
	    $segment2  = $segments[$count - 1];
	    
	    switch($segment2) {
	        
	        case "backing": // Backing without reward
	            
	            $itemId         = (int)$segments[$count - 2];
	            
        	    $vars['view']   = 'backing';
        		$vars['id']     = (int)$itemId;
        		
	            break;
	            
            case "embed": // Backing without reward
	            
	            $itemId         = (int)$segments[$count - 2];
	            $catId          = (int)$segments[$count - 3];
	            
        	    $vars['view']   = 'embed';
        		$vars['id']     = (int)$itemId;
        		$vars['catid']  = (int)$catId;
        		
	            break;
	            
	        default: // Screens of details - "updates", "comments", "funders"
	            
	            $itemId         = (int)$segments[$count - 2];
	            $catId          = (int)$segments[$count - 3];
	            
        	    $vars['view']   = 'details';
        		$vars['id']     = (int)$itemId;
        		$vars['catid']  = (int)$catId;
        		
        		// Get screen
        		$screen    = $segments[$count - 1];
        	    $allowedScreens = array("updates", "comments", "funders");
        	    if(in_array($screen, $allowedScreens)) {
        	        $vars['screen'] = $screen;
        	    }
        	    
	            break;
	            
	    }
	    
		return $vars;
	}
	
	// Backing with rewards
	if($count == 4) { 
	    
	    $itemId         = (int)$segments[$count - 3];
	    $rewardId       = (int)$segments[$count - 1];

	    $vars['view']   = 'backing';
		$vars['id']     = (int)$itemId;
		$vars['rid']    = (int)$rewardId;
		
		return $vars;
	}
	
    // if there was more than one segment, then we can determine where the URL points to
	// because the first segment will have the target category id prepended to it.  If the
	// last segment has a number prepended, it is details, otherwise, it is a category.
	$catId     = (int)$segments[0];
	$itemId    = (int)$segments[$count - 1];

	if ($itemId > 0) {
		$vars['view']   = 'details';
		$vars['catid']  = $catId;
		$vars['id']     = $itemId;
	} else {
		$vars['view']   = 'category';
		$vars['id']     = $catId;
	}

    return $vars;
}