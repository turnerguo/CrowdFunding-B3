jQuery(document).ready(function() {
	
	jQuery(".comremove_btn").bind("click", function(event) {
		
		event.preventDefault();
		
		var question  = jQuery("#cf-hidden-question").val();
		
		$answer 	  = confirm(question);
		if( false == $answer ) {
			return;
		}
		
		var id 		  = jQuery(this).data("id");
		var elementId = "comment"+id;
		
		var data 	  = {"id": id};
		
		jQuery.ajax({
			url: "index.php?option=com_crowdfunding&format=raw&task=comment.remove",
			type: "POST",
			data: data,
			dataType: "text json",
			success: function(response) {
				
				if(response.success) {
					jQuery("#"+elementId).fadeOut("slow", function() {
						jQuery(this).remove();
					});

                    ITPrismUIHelper.displayMessageSuccess(response.title, response.text);
				} else {
                    ITPrismUIHelper.displayMessageFailure(response.title, response.text);
				}
				
				// Reset form data if the element has been loaded for editing.
				var currentElementId = jQuery("#jform_id").val();
				if(id == currentElementId) {
					jQuery("#jform_comment").val("");
					jQuery("#jform_id").val("");
				}
			}
				
		});
		
	});
	
	
	jQuery(".comedit_btn").bind("click", function(event) {
		
		event.preventDefault();
		
		var id = jQuery(this).data("id");
		
		jQuery.ajax({
			url: "index.php?option=com_crowdfunding&format=raw&task=comment.getdata&id="+id,
			type: "GET",
			dataType: "text json",
			success: function(response) {
				
				if(!response.success) {
                    ITPrismUIHelper.displayMessageFailure(response.title, response.text);
				}
				
				jQuery("#jform_comment").val(response.data.comment);
				jQuery("#jform_id").val(response.data.id);
				
			}
				
		});
		
	});
	
	
	jQuery("#cf-comments-reset").bind("click", function(event) {
		
		event.preventDefault();
		
		jQuery("#jform_comment").val("");
		jQuery("#jform_id").val("");
		
	});
	
});
