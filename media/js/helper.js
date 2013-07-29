/**
 * JavaScript Helper
 */
var CrowdFundingHelper = {
		
	displayMessageSuccess: function(title, text) {
		
	    jQuery.pnotify({
	        title: title,
	        text: text,
	        icon: "icon-ok",
	        type: "success",
	        sticker: false,
	        icon: false
        });
	},
	displayMessageFailure: function(title, text) {
		
		jQuery.pnotify({
	        title: title,
	        text: text,
	        icon: 'icon-warning-sign',
	        type: "error",
	        sticker: false,
	        icon: false
        });
	}
}