jQuery(document).ready(function() {
	
	// Style file input
	jQuery('.fileupload').fileuploadstyle();

    // Initialize symbol length indicator
    var shortDesc = jQuery('#jform_short_desc');
    shortDesc.attr("maxlength", 255);
    shortDesc.maxlength({
		alwaysShow: true,
		placement: 'bottom-right'
	});
	
	// Load locations from the server
	jQuery('#jform_location_preview').typeahead({
		
		ajax : {
			url: "index.php?option=com_crowdfunding&format=raw&task=project.loadLocation",
			method: "get",
			triggerLength: 3,
			preProcess: function (response) {
				
	            if (response.success === false) {
	                return false;
	            }
	            
	            return response.data;
	        }
		},
		onSelect: function(item) {
			
			jQuery("#jform_location").attr("value", item.value);
			
		}
		
	});
	
	// Validation plugin
	jQuery('#js-cf-project-form').parsley({
		uiEnabled: false,
		messages: {
			required: Joomla.JText._('COM_CROWDFUNDING_THIS_VALUE_IS_REQUIRED')
		}
	});
	
});