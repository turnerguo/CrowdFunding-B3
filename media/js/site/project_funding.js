jQuery(document).ready(function() {
	
	// Disable input of date and hide calendar icon
	if(jQuery('#funding_duration_type0').is(':checked')) {
		jQuery("#jform_funding_days").removeAttr("disabled");
		jQuery("#jform_funding_end").attr('disabled','disabled');
		jQuery("#jform_funding_end_img").hide();
	}
	
	// Disable input of date and hide calendar icon
	if(jQuery('#funding_duration_type1').is(':checked')) {
		jQuery("#jform_funding_end").removeAttr("disabled");
		jQuery("#jform_funding_end_img").show();
		jQuery("#jform_funding_days").attr('disabled','disabled');
	}
	
	// Event for days
	jQuery("#funding_duration_type0").on("click", function(event) {
		jQuery("#jform_funding_days").removeAttr("disabled");
		jQuery("#jform_funding_end").attr('disabled','disabled');
		jQuery("#jform_funding_end_img").hide();
	});
	
	// Event for date
	jQuery("#funding_duration_type1").on("click", function(event) {
		jQuery("#jform_funding_end").removeAttr("disabled");
		jQuery("#jform_funding_days").attr('disabled','disabled');
		jQuery("#jform_funding_end_img").show();
	});
	
});