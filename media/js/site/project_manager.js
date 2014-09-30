jQuery(document).ready(function() {
	

    jQuery("#js-btn-project-publish").on("click", function(event){
        event.preventDefault();

        if(confirm(Joomla.JText._('COM_CROWDFUNDING_QUESTION_LAUNCH_PROJECT'))) {
            window.location.href = jQuery(this).attr("href");
        }

    });

    jQuery("#js-btn-project-unpublish").on("click", function(event){
        event.preventDefault();

        if(confirm(Joomla.JText._('COM_CROWDFUNDING_QUESTION_STOP_PROJECT'))) {
            window.location.href = jQuery(this).attr("href");
        }

    });

});