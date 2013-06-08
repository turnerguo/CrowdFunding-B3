jQuery(document).ready(function() {
	
	// Style file input
	jQuery('.fileupload').fileupload();
	
	jQuery('#jform_short_desc').attr("maxlength", 255);
	
	// Initialize symbol length indicator
	jQuery('#jform_short_desc').maxlength({
		alwaysShow: true,
		placement: 'bottom-right'
	});
	
	jQuery.locationNames 	= new Array();
	jQuery.responseData 	= new Array();
	
	jQuery('#jform_location_preview').typeahead({
		source: function(query) {
			
			if(query.length >= 3) {
				
				var data = {"query": query};
				
				jQuery.ajax({
					url: "index.php?option=com_crowdfunding&format=raw&task=project.loadLocation",
					type: "GET",
					data: data,
					async: false,
					dataType: "text json",
					success: function(response) {
						
						if(response.data.length > 0) {
							
							jQuery.responseData  = response.data;
							
							jQuery(response.data).each(function(index) {
								jQuery.locationNames[index] = response.data[index].name;
							});
						}
						
					}
						
				});
				
				return jQuery.locationNames;
			}
			
		},
		updater: function(item) {
			
			var id = 0;
			
			if(jQuery.responseData.length > 0) {
				
				jQuery(jQuery.responseData).each(function(index) {
					
					if(item == jQuery.responseData[index].name) {
						id = jQuery.responseData[index].id;
					}
					
				});
				
			}
			
			jQuery("#jform_location").attr("value", id);
			
			return item;
		}
	});
	
});