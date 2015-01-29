jQuery( document ).ready(
    function() {
       jQuery("#sendPushNotification").click(function(e){
           e.preventDefault();
           var parent = jQuery(this).parents("table");
           var media_id =  jQuery(parent).find("input[name='mobappSettings[opt-push-media][id]']").val();
           var title =  jQuery(parent).find(".wooAppPushNotify_Title").val();
           var message =  jQuery(parent).find(".wooAppPushNotify_Message").val();
           var action =  jQuery(parent).find(".wooAppPushNotify_Action").val();
           var data = {
               'action': 'send_push_notification_to_app',
               'media_id': media_id,
               'title': title,
               'message': message,
               'click_action': action,
               'acton_value': 1234
           };
           jQuery.post(ajaxurl, data, function(response) {
                alert('Got this from the server: ' + response);
            });
       });
    }
);