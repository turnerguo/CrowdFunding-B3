jQuery(document).ready(function() {
	
	jQuery(".upremove_btn").bind("click", function(event) {
		
		event.preventDefault();
		
		var question  = jQuery("#cf-hidden-question").val();
		
		$answer 	  = confirm(question);
		if( false == $answer ) {
			return;
		}
		
		var id 		  = jQuery(this).data("id");
		var elementId = "update"+id;
		
		var data 	  = {"id": id};
		
		jQuery.ajax({
			url: "index.php?option=com_crowdfunding&format=raw&task=update.remove",
			type: "POST",
			data: data,
			dataType: "text json",
			success: function(response) {
				
				if(response.success) {
					jQuery("#"+elementId).fadeOut("slow", function() {
						jQuery(this).remove();
					});
					
					CrowdFundingHelper.displayMessageSuccess(response.title, response.text);
				} else {
					CrowdFundingHelper.displayMessageFailure(response.title, response.text);
				}
				
			}
				
		});
		
	});
	
	
	jQuery(".upedit_btn").bind("click", function(event) {
		
		event.preventDefault();
		
		var id 		  = jQuery(this).data("id");
		
		jQuery.ajax({
			url: "index.php?option=com_crowdfunding&format=raw&task=update.getdata&id="+id,
			type: "GET",
			dataType: "text json",
			success: function(response) {
				
				if(!response.success) {
					CrowdFundingHelper.displayMessageFailure(response.title, response.text);
				}
				
				jQuery("#jform_title").val(response.data.title);
				jQuery("#jform_description").val(response.data.description);
				jQuery("#jform_id").val(response.data.id);
				
			}
				
		});
		
	});
	
	
	jQuery("#cf-updates-reset").bind("click", function(event) {
		
		event.preventDefault();
		
		var id 		  = jQuery(this).data("id");
		
		jQuery("#jform_title").val("");
		jQuery("#jform_description").val("");
		jQuery("#jform_id").val("");
		
	});
	
});
	
