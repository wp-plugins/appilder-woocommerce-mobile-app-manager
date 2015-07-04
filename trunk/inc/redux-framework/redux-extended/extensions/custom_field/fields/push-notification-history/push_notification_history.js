jQuery( document ).ready(
    function() {
        var updateHisClick = function(e) {
            e.preventDefault();
            jQuery(this).text("Loading...");
            var offset = jQuery(this).attr("data-offset");
            loadPushNotiHis(offset);
        };
        jQuery(".loadPushNotiHis").click(updateHisClick);
        jQuery(".deleteNotiItem").click(function(e) {
            e.preventDefault();
            if(confirm("Do you want to delete this from history ?")) {
                his_id = jQuery(this).attr("data-id");
                jQuery(this).text("Deleting..");
                var data = {
                    action  :   "action_notification_history_delete",
                    id      :   his_id
                };
                jQuery.post(ajaxurl, data, function (response) {
                    console.log(response);
                    jQuery("#noti_his_"+his_id).slideUp("slow").remove();
                 }, "json");
            }
        });

        function loadPushNotiHis(offset_value){
            var data = {
                action  :   "action_getHistory",
                offset      :   offset_value
            };
            jQuery.post(ajaxurl, data, function (response) {
                console.log(response);
                jQuery(".PushNotiHis").html(response).find(".loadPushNotiHis").live("click",updateHisClick);
            });
        }
    }
);