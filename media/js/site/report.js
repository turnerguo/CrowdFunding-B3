jQuery(document).ready(function () {
    "use strict";

    // Initialize symbol length indicator
    var shortDesc = jQuery('#cfreport_description');
    shortDesc.attr("maxlength", 255);
    shortDesc.maxlength({
        alwaysShow: true,
        placement: 'bottom-right'
    });

    var $projectTitleElement = jQuery("#cfreport_project");

    if ($projectTitleElement) {

        // Load projects from the server
        $projectTitleElement.typeahead({

            ajax: {
                url: "index.php?option=com_crowdfunding&format=raw&task=project.loadProject",
                method: "get",
                triggerLength: 3,
                preProcess: function (response) {

                    if (response.success === false) {
                        return false;
                    }

                    return response.data;
                }
            },
            onSelect: function (item) {
                jQuery("#cfreport_id").val(item.value);
            }

        });
    }

});