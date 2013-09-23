jQuery(document).ready(function() {
	
	// Style file input
	jQuery('.fileupload').fileuploadstyle();
	
	// Initialize extra images.
	var extraImagesElement = jQuery("#crowdf-extra-images");
	if(extraImagesElement.length > 0) {
		
		var projectId = jQuery("#jform_id").val();
			
		// Add image
		jQuery('#js-extra-fileupload').fileupload({
			dataType: 'text json',
			formData: {id: projectId},
			singleFileUploads: true,
	        done: function (event, response) {
	            
	        	if(!response.result.success) {
	        		
	        		CrowdFundingHelper.displayMessageFailure(response.result.title, response.result.text);
	        		
	        	} else {
	        		
	        		// Get data
	        		var data 		= response.result.data;
	        		
	        		// Clone the templates of the row
	        		var extraImage  = jQuery("#js-extra-img-row").clone().removeClass("hide");
	        		jQuery(extraImage).removeAttr("id");
	        		
	        		// Set the thumnail to the image element
	        		var imgSrc = jQuery(extraImage).find(".js-extra-img");
	        		jQuery(imgSrc).attr("src", data.thumb);
	        		
	        		var btnRemove = jQuery(extraImage).find(".js-extra-image-remove");
	        		jQuery(btnRemove).attr("data-image-id", data.id);
	        		
	        		jQuery("#js-extra-images-rows").prepend(extraImage);
	        	}
	        }
		});
		
		// Remove image
		jQuery('#js-extra-images-rows').on("click", ".js-extra-image-remove", function(event) {
			event.preventDefault();
			
			var imageId = jQuery(this).data("image-id");
			
			var _self   = this;
			
			jQuery.ajax({
				url: "index.php?option=com_crowdfunding&task=project.removeExtraImage",
				type: "POST",
				data: { format: "raw", id: imageId },
				dataType: "text json"
			}).done( function( response ) {
				
				if(!response.success) {
					CrowdFundingHelper.displayMessageFailure(response.title, response.text);
				} else {
					jQuery(_self).parent().parent().remove()
					
				}
    	    	
			});
			
		});
	}
		
});