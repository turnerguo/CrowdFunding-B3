jQuery(document).ready(function() {
	
	jQuery(".reward-amount").bind("click", function(event) {
		
		var elements  	  = jQuery(this).find(".reward-amount-radio");
		var radio  	  	  = elements[0];
		
		if(jQuery(radio).is(':checked') === false) {
			jQuery(".reward-amount-radio").attr('checked', false);
			jQuery(radio).attr('checked', true);
	    }
		
		var rewardId      = jQuery(radio).data("id");
		
		var amount  	  = parseFloat( jQuery(radio).val() );
		var currentAmount = parseFloat( jQuery("#current-amount").val() );
		
		if(currentAmount < amount) {
			jQuery("#current-amount").val(amount);
		}
		
		jQuery("#reward-id").val(rewardId);
		
	});
	

	/*jQuery("#form-pledge").bind("submit", function(event) {
		
		var elements  	  = jQuery("#rewards").find(".reward-amount-radio");
		var radio 		  =  null;
		
		jQuery.each(elements, function(index, value) {
			if(jQuery(value).is(':checked')) {
				var radio =  elements[index];
			}
		});
		
		console.log(radio);
		
		var currentAmount = parseFloat( jQuery("#current-amount").val() );
		var amount  	  = parseFloat( jQuery(radio).val() ); 
		
		if(currentAmount < amount) {
			return false;
		}
	});*/
	
});
	
