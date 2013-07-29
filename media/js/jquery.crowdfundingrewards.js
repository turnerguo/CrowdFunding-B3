;(function ( $, window, document, undefined ) {

	"use strict";
	
    // Create the defaults once
    var pluginName = "CrowdfundingRewards";
    var defaults = {};

    // The actual plugin constructor
    function CrowdfundingRewards(element, options) {
        this.element = element;
        
        this.options = $.extend( {}, defaults, options );

        this._defaults = defaults;
        this._name     = pluginName;

        this.init();
        
//        this.displayNumber();
    }

    CrowdfundingRewards.prototype = {

        init: function() {
        	
        	var self = this;
            
        	$(this.element).on("click", function(event) {
        		event.preventDefault();
        		
        		var txnId = $(this).data("txn-id");
        		console.log(txnId);
        		
        		var fields = {
    				txn_id: txnId
        		};
        		
    			$.ajax({
    				type: "POST",
    				url: "index.php?option=com_crowdfunding&format=raw&task=rewards.changeState",
    				data: fields,
    				dataType: "text json"
    			}).done(function(response){
    				
    				console.log(response);
    				
    			});
        		
        		
        	});
        	
        },

        /*displayNumber: function(element, options) {
        	
        	var self = this;
        	
        	$.ajax({
        		type: "GET",
        		url: "index.php?option=com_gamification&format=raw&task=notifications.getNumber",
        		dataType: "text json"
        	}).done(function(response){
        		
        		var results = parseInt(response.data.results);
        		
        		if(results > 0) {
        			$(self._numberContainer).text(results).show();
        			var title = $(document).attr("title");
        			
        			$(document).attr("title", "("+ results + ") "+ title) ;
        		}
        	});
        	
        }*/
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new CrowdfundingRewards( this, options ));
            }
        });
    };

})( jQuery, window, document );

