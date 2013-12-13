jQuery(document).ready(function() {
	
	jQuery("#cf_add_new_reward").bind("click", function(event) {
		event.preventDefault();
		
		var item 		= jQuery("#reward_tmpl").clone();
		var itemsNumber = parseInt(jQuery("#items_number").attr("value"));
		itemsNumber 	= itemsNumber + 1;
		
		// Clone element
		jQuery(item).attr("id", "reward_box_d");
		jQuery(item).appendTo("#rewards_wrapper");
		
		// Element wrapper 
		jQuery("#reward_box_d", "#rewards_wrapper").attr("id", "reward_box_"+itemsNumber);
		
		// Amount
		jQuery("#reward_amount_label_d", "#rewards_wrapper").attr("for", "reward_amount_"+itemsNumber);
		jQuery("#reward_amount_label_d", "#rewards_wrapper").removeAttr("id");
		jQuery("#reward_amount_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][amount]");
		jQuery("#reward_amount_d", "#rewards_wrapper").attr("id", "reward_amount_"+itemsNumber);
		
		// Title
		jQuery("#reward_title_title_d", "#rewards_wrapper").attr("for", "reward_title_"+itemsNumber);
		jQuery("#reward_title_title_d", "#rewards_wrapper").removeAttr("id");
		jQuery("#reward_title_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][title]");
		jQuery("#reward_title_d", "#rewards_wrapper").attr("id", "reward_title_"+itemsNumber);
		
		// Description
		jQuery("#reward_description_title_d", "#rewards_wrapper").attr("for", "reward_description_"+itemsNumber);
		jQuery("#reward_description_title_d", "#rewards_wrapper").removeAttr("id");
		jQuery("#reward_description_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][description]");
		jQuery("#reward_description_d", "#rewards_wrapper").attr("id", "reward_description_"+itemsNumber);
		
		// Availble
		jQuery("#reward_number_title_d", "#rewards_wrapper").attr("for", "reward_number_"+itemsNumber);
		jQuery("#reward_number_title_d", "#rewards_wrapper").removeAttr("id");
		jQuery("#reward_number_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][number]");
		jQuery("#reward_number_d", "#rewards_wrapper").attr("id", "reward_number_"+itemsNumber);
		
		// Delivery
		jQuery("#reward_delivery_title_d", "#rewards_wrapper").attr("for", "reward_delivery_"+itemsNumber);
		jQuery("#reward_delivery_title_d", "#rewards_wrapper").removeAttr("id");
		jQuery("#reward_delivery_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][delivery]");
		jQuery("#reward_delivery_d", "#rewards_wrapper").attr("id", "reward_delivery_"+itemsNumber);

		// Reward ID
		jQuery("#reward_id_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][id]");
		jQuery("#reward_id_d", "#rewards_wrapper").removeAttr("id");
		
		// Number of elements
		jQuery("#items_number").attr("value", itemsNumber);
		
		// The button "remove"
		var elementRemove = jQuery("#reward_remove_d", "#rewards_wrapper");
		jQuery(elementRemove).attr("id", "reward_remove_"+itemsNumber);
		jQuery(elementRemove).data("index-id", itemsNumber);
		
		// Display form
		jQuery(item).show();
		
		// Calendar 
		jQuery("#reward_delivery_d_img", "#rewards_wrapper").attr("id", "reward_delivery_"+itemsNumber+"_img");
		Calendar.setup({
			// Id of the input field
			inputField: "reward_delivery_"+itemsNumber,
			// Format of the input field
			ifFormat: projectWizard.dateFormat,
			// Trigger for the calendar (button ID)
			button: "reward_delivery_"+itemsNumber+"_img",
			// Alignment (defaults to "Bl")
			align: "Tl",
			singleClick: true,
			firstDay: 0
		});
		
	});
	
	jQuery("#rewards_wrapper").on("click", ".btn_remove_reward", function(event) {
		event.preventDefault();
		
		var index    = jQuery(this).data("index-id");
		var rewardId = jQuery(this).data("reward-id");
		
		if(rewardId) { // Delete element in Database and remove it from UI
			
			var data = "rid[]="+rewardId;
			
			jQuery.ajax({
				url: "index.php?option=com_crowdfunding&format=raw&task=rewards.remove",
				type: "POST",
				data: data,
				dataType: "text json",
				success: function(response) {
					
					if(response.success) {
						jQuery("#reward_box_"+index).remove();
						CrowdFundingHelper.displayMessageSuccess(response.title, response.text);
					} else {
						CrowdFundingHelper.displayMessageFailure(response.title, response.text);
					}
					
				}
					
			});
			
		} else { // Remove the element 
			jQuery("#reward_box_"+index).remove();
		}
	});
});