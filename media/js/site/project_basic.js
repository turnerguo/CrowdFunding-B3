jQuery(document).ready(function() {
	
	// Style file input
	jQuery('.fileupload').fileuploadstyle();
	
	jQuery('#jform_short_desc').attr("maxlength", 255);
	
	// Initialize symbol length indicator
	jQuery('#jform_short_desc').maxlength({
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
			
			console.log(item);
			
			jQuery("#jform_location").attr("value", item.value);
			
		}
		
	});
	
	// Validation plugin
	jQuery('#crowdf-project-form').parsley({
		messages: {
			required: Joomla.JText._('COM_CROWDFUNDING_THIS_VALUE_IS_REQUIRED')
		}
	});
	
});