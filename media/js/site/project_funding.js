jQuery(document).ready(function() {
	
	// Disable input of date and hide calendar icon
	if(jQuery('#js-funding-duration-days').is(':checked')) {
		disableDate();
	}
	
	// Disable input of date and hide calendar icon
	if(jQuery('#js-funding-duration-date').is(':checked')) {
		disableDays();
	}
	
	// Event for days
	jQuery("#js-funding-duration-days").on("click", function(event) {
		disableDate();
	});
	
	// Event for date
	jQuery("#js-funding-duration-date").on("click", function(event) {
		disableDays();
	});
	
	
	// Event for label dayse
	jQuery("#jform_funding_days-lbl").on("click", function(event) {
		jQuery('#js-funding-duration-days').prop("checked", true);
		disableDate();
	});
	
	// Event for date
	jQuery("#jform_funding_end-lbl").on("click", function(event) {
		jQuery('#js-funding-duration-date').prop("checked", true);
		disableDays();
	});
	
	function disableDate() {
		jQuery("#jform_funding_days").removeAttr("disabled");
		jQuery("#jform_funding_end").attr('disabled','disabled');
		jQuery("#jform_funding_end_img").hide();
	}
	
	function disableDays() {
		jQuery("#jform_funding_end").removeAttr("disabled");
		jQuery("#jform_funding_days").attr('disabled','disabled');
		jQuery("#jform_funding_end_img").show();
	}
	
});