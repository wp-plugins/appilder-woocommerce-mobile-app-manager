/*global jQuery, document, redux_change */

jQuery(document).ready(function() {
    var selector = jQuery(document).find('.redux-container-nav_menu_builder');
    redux.field_objects.media.init('.redux-container-nav_menu_builder');
    var slug = function(str) {
        str = str.replace(/^\s+|\s+$/g, ''); // trim
        str = str.toLowerCase();

        // remove accents, swap ñ for n, etc
        var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
        var to   = "aaaaaeeeeeiiiiooooouuuunc------";
        for (var i=0, l=from.length ; i<l ; i++) {
            str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }
        str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
            .replace(/\s+/g, '-') // collapse whitespace and replace by -
            .replace(/-+/g, '-'); // collapse dashes

        return str;
    };
    jQuery(selector).each(function(item,e){
        jQuery(e).find(".page_builder_acc").accordion({
            header: "> h3",
            collapsible: true,
            active: false ,
            heightStyle: "content",
            icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }
        });
        jQuery(e).find(".page_builder_acc:first").accordion({
            header: "> h3",
            collapsible: true,
            active: 0 ,
            heightStyle: "content",
            icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }
        });
        jQuery(e).find(".select2").select2();

        jQuery(e).find(".select2").on("change",function(e){
            var label =  jQuery(this).find("option:selected").text();
            jQuery(this).parent().find(".label").val(label);
        });

        jQuery(e).find(".addtocat").click(function(e){
            e.preventDefault();
            var input = jQuery(this).parent().find(".addfield");
            var select2=false;
            var label = jQuery(this).parent().find(".label").val();
            var value_label =""
            var value = "";
            var type='';

            if(jQuery(this).parent().parent().hasClass("uploadfield")) {
                var media_url  = jQuery(this).parent().find(".upload-thumbnail").val();
                var media_id = jQuery(this).parent().find(".upload-id").val();
            }else{
                var media_id ='';
                var media_url = '';
            }

            if(jQuery(input).hasClass("select2")){
                select2 =true;
               value =  jQuery(input).select2("val");
               value_label =  jQuery(input).find("option:selected").text();
            }else{
               value =  jQuery(input).val();
            }
            if(value != "" && label!="" && !(select2 && value_label ==""))
            {
                var field_name = jQuery(this).parent().parent().parent().attr("data-name");
                var field_id = jQuery(this).parent().parent().parent().attr("data-id");
                var type = jQuery(this).parent().parent().attr("data-type");
                var id= slug(type+'_'+value);
                var parent = '';
                var exists = jQuery(".nav_menu_items_space").find("li[id^=list_"+id+"]").length;
                id=id+'_'+exists;
                field_name = field_name +'['+id+']';
                field_id = field_id +'-'+id;
                var html = "<li id='list_"+id+"'><div class='page_builder_acc acc_new'><h3>"+label+"</h3><div>";

                if(media_id!='' && media_url!=''){
                    html+='<img src="'+media_url+'" style="height:32px;width:32px" ><br>';
                    html+='<input class="media_id"  type="hidden" name="' + field_name+'[media_id]'  + '" id="' + field_id + '-media_id"  value="'+media_id+'"/>';
                    html+='<input class="media_url" type="hidden" name="' + field_name+'[media_url]'  + '" id="' +field_id + '-media_url"  value="'+media_url+'"/>';
                }

                html+='<input class="labeledit" type="text" name="' + field_name+'[label]'  + '" id="' + field_id + '-label"  value="'+label+'"/>';
                html+='<input type="hidden" name="' + field_name+'[value]'  + '" id="' + field_id + '-value"  value="'+value+'"/>';
                html+='<input type="hidden" name="' + field_name+'[label_value]'  + '" id="' + field_id + '-label_value"  value="'+value_label+'"/>';
                html+='<input  type="hidden" name="' + field_name+'[type]'  + '" id="' + field_id + '-type"  value="'+type+'"/>';
                html+='<input type="hidden" name="' + field_name+'[id]'+ '" id="' + field_id + '-id"  value="'+id+'"/>';
                html+='<input class="parentfield" type="hidden" name="' + field_name+'[parent]'+ '" id="' + field_id + '-parent"  value="'+parent+'"/>';
                var dis_val = (value_label=='')?value:value_label;
                var type_label = jQuery(this).parent().parent().find("h3").text();
                html+= '<div class="bt_info_txt"><span class="disp">'+dis_val+' ('+type_label+')</span> <span class="del"><a href="#" class="deleteNavItem">Delete</a></span></div>';
                html+="</div></div>\n</li>";
                jQuery(".nav_menu_items_space > ol").append(html).find(".acc_new").accordion({
                    header: "> h3",
                    collapsible: true,
                    active: 0 ,
                    heightStyle: "content",
                    icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }
                }).removeClass("acc_new").find(".deleteNavItem").live("click",deleteNavItem);
                jQuery(".nav_menu_items_space > ol").find(".labeledit").keyup(updateTitleNav);
//                console.log(value,label,value_label);
                window.catlistmenuf();
            }else{
                console.log(value,label,select2,value_label);
            }

        });
        jQuery(e).find(".nav_menu_items_space").find(".labeledit").keyup(updateTitleNav);
        function updateTitleNav(){
                jQuery(this).parent().parent().find("h3").html('<span class="ui-accordion-header-icon ui-icon ui-icon-minus"></span>'+jQuery(this).val());

        }
        jQuery(e).find(".deleteNavItem").live("click",deleteNavItem);
        function deleteNavItem(e){
            e.preventDefault();
            var del_item = jQuery(this).parent().parent().parent().parent().parent();
            if(jQuery(del_item).find("ol").length>0){
                jQuery(del_item).find("ol").clone().insertBefore(jQuery(del_item).parent());
                jQuery(del_item).find("ol").remove();
            }
            jQuery(del_item).remove();
            window.save_update();
        }
    });
});