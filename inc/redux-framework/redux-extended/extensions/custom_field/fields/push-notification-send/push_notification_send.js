jQuery( document ).ready(
    function() {
        function clear_push_page(parent){
           // jQuery(parent).find("#reset_opt-push-media").click();
            jQuery(parent).find(".wooAppPushNotify_Title").val("");
            jQuery(parent).find(".wooAppPushNotify_Message").val("");
            jQuery(parent).find(".wooAppPushNotify_Action").find(".widget-action-selector").prop("selectedIndex",0);
            jQuery(parent).find(".wooAppPushNotify_Action > .widget-action-value-field").val("");
            jQuery(parent).find(".wooAppPushNotify_Action > .widget-action-value-field").prop("selectedIndex",0);
            jQuery(parent).find(".widget-action-value-field").html('');
       }

        jQuery("#sendPushNotification").click(function(e){
           jQuery('.send-success-notification').hide();
           e.preventDefault();
           var but = jQuery(this);
f
            but.html("Sending...");
            but.attr("disabled","disabled");

           var parent = jQuery(this).parents("table");
          //  var media_id =  jQuery(parent).find("input[name='mobappSettings[opt-push-media][id]']").val();
           var title =  jQuery(parent).find("input.wooAppPushNotify_Title").val();
           var message =  jQuery(parent).find("textarea.wooAppPushNotify_Message").val();
           var action =  jQuery(parent).find(".wooAppPushNotify_Action").find(".widget-action-selector").val();
           if(action=='go_to_url')
               var action_value =  jQuery(parent).find(".wooAppPushNotify_Action > .widget-action-value-field").find("input,select").val();
           else if(action!='')
               var action_value =  jQuery(parent).find(".wooAppPushNotify_Action > .widget-action-value-field").find("input,select").select2('val');
           else
               var action_value = '';

           var data = {
               'action': 'send_push_notification_to_app',
            //   'media_id': media_id,
               'title': title,
               'message': message,
               'click_action': action,
               'acton_value': action_value
           };

           jQuery.post(ajaxurl, data, function(response) {
               but.html("Send Notification");
               but.removeAttr("disabled");

               if(response !=null && response.status !=null && response.status) {
                   clear_push_page(parent);
                   jQuery('.send-success-notification').html('<strong>Notification send to '+response.success+' users</strong>').show();
               }else{
                   alert("Unable to send notification please verify you have entered all information correct");
               }
           },"json");
       });
        clear_push_page(jQuery("#sendPushNotification").parents("table"));
    }
);