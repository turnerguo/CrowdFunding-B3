jQuery(document).ready(function() {
	"use strict";

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
			send: function() {
				jQuery("#js-extra-images-loader").show();
			},
			fail: function() {
				jQuery("#js-extra-images-loader").hide();
			},
	        done: function (event, response) {
	            
	        	if(!response.result.success) {

                    ITPrismUIHelper.displayMessageFailure(response.result.title, response.result.text);
	        		
	        	} else {
	        		
	        		// Get data
	        		var data 		= response.result.data;
	        		
	        		// Clone the templates of the row
	        		var extraImage  = jQuery("#js-extra-img-row").clone().removeClass("hide");
	        		jQuery(extraImage).removeAttr("id");
	        		
	        		// Set the thumbnail to the image element
	        		var imgSrc = jQuery(extraImage).find(".js-extra-img");
	        		jQuery(imgSrc).attr("src", data.thumb);
	        		
	        		var btnRemove = jQuery(extraImage).find(".js-extra-image-remove");
	        		jQuery(btnRemove).attr("data-image-id", data.id);
	        		
	        		jQuery("#js-extra-images-rows").prepend(extraImage);
	        	}
	        	
	        	// Hide ajax loader.
	        	jQuery("#js-extra-images-loader").hide();
	        }
		});
		
		// Remove image
		jQuery('#js-extra-images-rows').on("click", ".js-extra-image-remove", function(event) {
			event.preventDefault();
			
			var imageId = jQuery(this).data("image-id");
			
			var _self   = this;
			
			jQuery.ajax({
				url: "index.php?option=com_crowdfunding&task=story.removeExtraImage",
				type: "POST",
				data: { format: "raw", id: imageId },
				dataType: "text json"
			}).done( function( response ) {
				
				if(!response.success) {
                    ITPrismUIHelper.displayMessageFailure(response.title, response.text);
				} else {
					jQuery(_self).parent().parent().remove();
				}
    	    	
			});
			
		});
	}
		
});