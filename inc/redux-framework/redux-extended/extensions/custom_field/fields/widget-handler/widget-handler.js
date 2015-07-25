/* global redux_change, wp
* @todo Change way of giving id to new widget
*
* */

jQuery(document).ready(function () {
    // update_action_value(jQuery(""))
    jQuery(".select2-sortable").select2Sortable();
    jQuery('.remove-slider-remove').live('click', function () {
        var removeId =jQuery(this).attr("id").match(/\[items\]\[([0-9]+)\]\[slides\]/)[1];
        jQuery(this).parents("tr").hide("slow", function() { jQuery(this).remove();});
        redux_change(jQuery(this));
        var data_id= jQuery(this).attr("data-id");
        var id = "#blank_slide_"+data_id;
        var slider_ids_element = jQuery("#sliders_ids_"+data_id);
        var slider_count_element = jQuery("#sliders_count_"+data_id);
        var itemCount = jQuery(slider_count_element).val();
        slider_count_element.val(itemCount*1-1);
        var sliders_counts =  jQuery(slider_ids_element).val().split(",");
        jQuery.each(sliders_counts,function(index,val){
            if(val == removeId)
                sliders_counts.splice(index,1);
        });
        var slideCount1 = ++sliders_counts[sliders_counts.length - 1];
        slider_ids_element.val(sliders_counts.join());
    });

    jQuery('.slider-title').keyup(change_slider_title);
    function change_slider_title(event){
        var newTitle = event.target.value;
        jQuery(this).parents("tr").find(".slider-title-display").text(newTitle);
    }

    window.update_search_cat_selector = function (element){
        var hiddenInput = jQuery(element); //.parents("ul").children(".widget-action-value-field");
        var action_val =  hiddenInput.attr("data-value").split(',');
        var name = hiddenInput.attr("data-name");
        var id = hiddenInput.attr("data-id");
        var field  = jQuery("#widget_category_selector_field_multi > select").select2("destroy").clone(true);
        field.find("option").filter(function() {
            return (action_val!="" && jQuery.inArray(jQuery(this).val(),action_val)>-1);
        }).prop('selected', true);
        var select2 = true;
        var attr_id = field.attr("id");
        var attr_name = field.attr("name");
        if (typeof attr_id !== 'undefined' && attr_id !== false) {
            field.attr("id",id);
        }
        if (typeof attr_name !== 'undefined' && attr_name !== false) {
            field.attr("name",name );
        }
        jQuery(hiddenInput).empty().append(field);
        if(select2)
            jQuery(hiddenInput).find('select').select2();
        jQuery(hiddenInput).show();
    }

    jQuery(".widget-search-action").each(function(index,element){
        window.update_search_cat_selector(element);
    });
    function update_action_value(element){
        var element = jQuery(element);
        var hiddenInput = element.parents("ul").children(".widget-action-value-field");
        var action_type = element.val();
        var action_val =  hiddenInput.attr("data-value");
        var name = hiddenInput.attr("data-name");
        var id = hiddenInput.attr("data-id");
        var hidden = false;
        var select2 = false;
        if(action_type == 'open_product'){
            var field  = jQuery("#widget_product_selector_field > select").select2("destroy").clone(true);
          //  console.log(field);
            field.find("option").filter(function() {
                return jQuery(this).val() == action_val;
            }).prop('selected', true);
            select2 = true;
        }else if(action_type == 'open_category'){
            var field  = jQuery("#widget_category_selector_field > select").select2("destroy").clone(true);
            field.find("option").filter(function() {
                return jQuery(this).val() == action_val;
            }).prop('selected', true);
            select2 = true;
        }else if(action_type == 'open_page'){
            var field  = jQuery("#widget_inAppPages_selector_field > select").select2("destroy").clone(true);
            field.find("option").filter(function() {
                return jQuery(this).val() == action_val;
            }).prop('selected', true);
            select2 = true;
        }else if(action_type == 'go_to_url'){
            var field  = jQuery("#widget_url_field > input").select2("destroy").clone(true);
            field.val(action_val);
        }else{
            hidden  = true;
        }
        if(!hidden){
            var attr_id = field.attr("id");
            var attr_name = field.attr("name");
            if (typeof attr_id !== 'undefined' && attr_id !== false) {
                field.attr("id",id);
            }
            if (typeof attr_name !== 'undefined' && attr_name !== false) {
                field.attr("name",name );
            }
            jQuery(hiddenInput).empty().append(field);
            if(select2)
                jQuery(hiddenInput).find('select').select2();
            jQuery(hiddenInput).show();
           // hiddenInput.attr("data-value",hiddenInput.find('select').val());
        }else{
            jQuery(hiddenInput).hide();
        }
    }

    jQuery(".widget-action-selector").each(function(index,element){
        update_action_value(element);
    });

    jQuery('.widget-action-selector').change(change_action_value_field);
    function change_action_value_field(event){
        update_action_value(event.target);
    }

    jQuery('.add-slider-add').click(function () {
        redux_change(jQuery(this));
        var data_id= jQuery(this).attr("data-id");
        var id = "#blank_slide_"+data_id;
        var slider_ids_element = jQuery("#sliders_ids_"+data_id);
        var slider_count_element = jQuery("#sliders_count_"+data_id);

        var sliders_id = jQuery("#sliders_id_"+data_id).val();

        var sliders_add_slider_title = jQuery("#sliders_addslider_"+data_id).val();
        var sliders_add_slide_title = jQuery("#sliders_addslide_"+data_id).val();
        var sliders_remove_slider_title = jQuery("#sliders_removeslider_"+data_id).val();
        var sliders_remove_slide_title = jQuery("#sliders_removeslide_"+data_id).val();
        var sliders_new_slide_title = jQuery("#sliders_newslide_"+data_id).val();

        var  sliders_name =jQuery("#sliders_name_"+data_id).val();
        var newSlider  = jQuery(id).clone(true);

        newSlider.css("display","block");
        var sliders_counts =  jQuery(slider_ids_element).val().split(",");
        var slideCount1 = ++sliders_counts[sliders_counts.length - 1];
        jQuery(slider_ids_element).val(jQuery(slider_ids_element).val()+","+slideCount1);
        var itemCount = jQuery(slider_count_element).val();
        jQuery(slider_count_element).val(itemCount*1+1);
        newSlider.attr("id","new-slider-added-"+slideCount1);
        jQuery(newSlider).find("*").each(function(){
           var attr_id = jQuery(this).attr("id");
           var attr_name = jQuery(this).attr("name");
           var attr_rel = jQuery(this).attr("rel");
           var attr_rel_id = jQuery(this).attr("rel-id");
           var attr_data_id = jQuery(this).attr("data-id");
           var attr_data_name = jQuery(this).attr("data-name");
           var attr_rel_name = jQuery(this).attr("rel-name");
           if (typeof attr_id !== 'undefined' && attr_id !== false) {
               jQuery(this).attr("id",     attr_id.replace(/_blank/, slideCount1) )
           }
           if (typeof attr_name !== 'undefined' && attr_name !== false) {
               jQuery(this).attr("name", attr_name.replace(/_blank/, slideCount1) )
           }
           if (typeof attr_rel !== 'undefined' && attr_rel !== false) {
               jQuery(this).attr("rel",    attr_rel.replace(/_blank/, slideCount1) )
           }
           if (typeof attr_rel_id !== 'undefined' && attr_rel_id !== false) {
               jQuery(this).attr("rel-id", attr_rel_id.replace(/_blank/, slideCount1) )
           }
           if (typeof attr_data_id !== 'undefined' && attr_data_id !== false) {
               jQuery(this).attr("data-id",attr_data_id.replace(/_blank/, slideCount1) )
           }
           if (typeof attr_data_name !== 'undefined' && attr_data_name !== false) {
               jQuery(this).attr("data-id",attr_data_name.replace(/_blank/, slideCount1) )
           }
           if (typeof attr_rel_name !== 'undefined' && attr_rel_name !== false) {
               jQuery(this).attr("rel-name",attr_rel_name.replace(/_blank/, slideCount1) );
           }

        });

        var action_value_field = jQuery(newSlider).find(".widget-action-value-field");
        var attr_data_id = jQuery(action_value_field).attr('data-id');
        var attr_data_name = jQuery(action_value_field).attr('data-name');
        var attr_data_value = jQuery(action_value_field).attr('data-value');
        if (typeof attr_data_id !== 'undefined' && attr_data_id !== false) {
            jQuery(action_value_field).attr('data-id',attr_data_id.replace(/_blank/, slideCount1));
        }
        if (typeof attr_data_name !== 'undefined' && attr_data_name !== false) {
            jQuery(action_value_field).attr('data-name',attr_data_name.replace(/_blank/, slideCount1));
        }
        if (typeof attr_data_value !== 'undefined' && attr_data_value !== false) {
            jQuery(action_value_field).attr('data-value',"");
        }

        var newTr = jQuery('<tr valign="top"></tr>');
        newTr.html('<th scope="row"><div class="redux_field_th"><span class="slider-title-display">'+sliders_name+' '+slideCount1+'</span><span class="description">'+sliders_id+'_'+slideCount1+'</span></div></th><td><fieldset id="mobappSettings-opt-slides_blank" class="redux-field-container redux-field redux-container-slideshow " data-id="opt-slides_1"></fieldset></td>');
        var frame= newTr.find('#mobappSettings-opt-slides_blank');
        frame.html(newSlider).attr("id", frame.attr("id").replace(/_blank/, slideCount1));

        frame.find("._addslider").replaceWith(sliders_add_slider_title);
        frame.find("._addslide").replaceWith(sliders_add_slide_title);
        frame.find("._removeslider").replaceWith(sliders_remove_slider_title);
        frame.find("._removeslide").replaceWith(sliders_remove_slide_title);
        frame.find("._newslide").replaceWith(sliders_new_slide_title);

        newTr.find(".slider-title").val(sliders_name+' '+slideCount1+''); //.live('keyup',change_slider_title);
        var currentTab  = jQuery(this).parents(".redux-group-tab");
        jQuery(currentTab).find(".form-table > tbody").append(newTr);
    });
    jQuery(".navigation-menu-go").parent().parent("a").unbind('click');
    jQuery(".navigation-menu-go").parent().parent("a").click(
        function (event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            window.location.href = 'admin.php?page=woocommerce-mobile-app-manager-nav-menu';
            return false;
        });
});