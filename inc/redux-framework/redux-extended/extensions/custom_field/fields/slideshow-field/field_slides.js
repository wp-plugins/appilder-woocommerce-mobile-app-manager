/*global redux_change, wp, redux*/

(function( $ ) {
    "use strict";

    redux.field_objects = redux.field_objects || {};
    redux.field_objects.slideshow = redux.field_objects.slideshow || {};
    redux.field_objects.slides_search = redux.field_objects.slideshow || {};
    redux.field_objects.slides_single = redux.field_objects.slideshow || {};
    redux.field_objects.slides_product_scroller = redux.field_objects.slideshow || {};
    redux.field_objects.slides_html = redux.field_objects.slideshow || {};

    $( document ).ready(
        function() {
          //  redux.field_objects.slides.init();
        }
    );

    redux.field_objects.slideshow.init = function( selector ) {
        if ( !selector ) {
            selector = $( document ).find( '.redux-container-slideshow,.redux-container-slides_search,.redux-container-slides_single,.redux-container-slides_product_scroller,.redux-container-slides_html' );
        }
        // console.log(selector);
        $( selector ).each(
            function() {
                var el = $( this );
                var parent = el;
                if ( !el.hasClass( 'redux-field-container' ) ) {
                    parent = el.parents( '.redux-field-container:first' );
                }
                if (!parent.hasClass( 'redux-field-inited' ) ) {
                    parent.addClass( 'redux-field-init' );
                }
                redux.field_objects.media.init(el);
                if (parent.hasClass( 'redux-field-inited' ) ) {
                    return;
                } else {
                    parent.addClass( 'redux-field-inited' );
                }
                var check_classes = ['redux-container-slideshow','redux-container-slides_search','redux-container-slides_single','redux-container-slides_product_scroller'];
                var current_class = check_classes[0];
                $(check_classes).each(function(index,element){
                    if(el.hasClass(element)){
                        current_class = '.'+element
                    }
                });
                el.find('.redux-slideshow-remove').live('click', function () {
                    console.log("Deleteing...");
                    redux_change(jQuery(this));
                    jQuery(this).parent().siblings().find('input[type="text"]').val('');
                    jQuery(this).parent().siblings().find('textarea').val('');
                    jQuery(this).parent().siblings().find('input[type="hidden"]').val('');

                    var slideCount = jQuery(this).parents(current_class+':first').find('.redux-slideshow-accordion-group').length;
                    if (slideCount > 1) {
                        jQuery(this).parents('.redux-slideshow-accordion-group:first').slideUp('medium', function () {
                            jQuery(this).remove();
                        });
                    } else {
                        jQuery(this).parents('.redux-slideshow-accordion-group:first').find('.remove-image').click();
                        jQuery(this).parents(current_class+'first').find('.redux-slideshow-accordion-group:last').find('.redux-slideshow-header').text("New item");
                    }
                });

                el.find('.redux-slideshow-add').click(function () {
                    var lastOne = jQuery(this).prev().find('.redux-slideshow-accordion-group:last');
                    jQuery(lastOne).find('.select2').select2("destroy");
                    var newSlide = jQuery(lastOne).clone(true);
                    var slideCount = jQuery(newSlide).find('input[type="text"]').attr("name").match(/[0-9]+(?!.*[0-9])/);
                    var slideCount1 = slideCount*1 + 1;
                    jQuery(newSlide).find('input[type="text"],input[type="hidden"],textarea,select').each(function(){
                        var attr_id = jQuery(this).attr("id");
                        var attr_name = jQuery(this).attr("name");
                        if (typeof attr_id !== 'undefined' && attr_id !== false) {
                            jQuery(this).attr("id",attr_id.replace(/[0-9]+(?!.*[0-9])/, slideCount1));
                        }
                        if (typeof attr_name !== 'undefined' && attr_name !== false) {
                            jQuery(this).attr("name",attr_name.replace(/[0-9]+(?!.*[0-9])/, slideCount1));
                        }
                        jQuery(this).val('');
                        if (jQuery(this).hasClass('slide-sort')){
                            jQuery(this).val(slideCount1);
                        }
                    });
                    var action_value_field = jQuery(newSlide).find(".widget-action-value-field");
                    if (typeof action_value_field !== 'undefined' && action_value_field !== false && action_value_field.length !=0) {
                        jQuery(action_value_field).attr('data-id',action_value_field.attr('data-id').replace(/[0-9]+(?!.*[0-9])/, slideCount1));
                        jQuery(action_value_field).attr('data-name',action_value_field.attr('data-name').replace(/[0-9]+(?!.*[0-9])/, slideCount1));
                        jQuery(action_value_field).attr('data-value',"");
                        jQuery(action_value_field).hide().html("");
                    }
                    jQuery(newSlide).find('.screenshot').removeAttr('style');
                    jQuery(newSlide).find('.screenshot').addClass('hide');
                    jQuery(newSlide).find('.screenshot a').attr('href', '');
                    jQuery(newSlide).find('.remove-image').addClass('hide');
                    jQuery(newSlide).find('.redux-slideshow-image').attr('src', '').removeAttr('id');
                    jQuery(newSlide).find('h3').text('').append('<span class="redux-slideshow-header">New item</span><span class="ui-accordion-header-icon ui-icon ui-icon-plus"></span>');
                    jQuery(this).prev().append(newSlide);
                    jQuery(lastOne).find('.select2').select2();
                    // jQuery.reduxSelect.init();
                    if(jQuery(newSlide).find(".widget-search-action").length > 0)
                        window.update_search_cat_selector(jQuery(newSlide).find(".widget-search-action"));
                    else {
                        jQuery(newSlide).find("select.select2").select2();
                        jQuery(newSlide).find("select.select2").select2("val", "");
                    }
                });

                el.find('.slide-title').keyup(function(event) {
                    var newTitle = event.target.value;
                    jQuery(this).parents().eq(3).find('.redux-slideshow-header').text(newTitle);
                });

                el.find(".redux-slideshow-accordion")
                            .accordion({
                                header: "> div > fieldset > h3",
                                collapsible: true,
                                active: false,
                                heightStyle: "content",
                                icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }
                            })
                            .sortable({
                                axis: "y",
                                handle: "h3",
                                //            connectWith: ".redux-slideshow-accordion",
                                start: function(e, ui) {
                                    ui.placeholder.height(ui.item.height());
                                    ui.placeholder.width(ui.item.width());
                                },
                                placeholder: "ui-state-highlight",
                                stop: function (event, ui) {
                                    // IE doesn't register the blur when sorting
                                    // so trigger focusout handlers to remove .ui-state-focus
                                    ui.item.children("h3").triggerHandler("focusout");
                                    var inputs = jQuery('input.slide-sort');
                                    inputs.each(function(idx) {
                                        jQuery(this).val(idx);
                                    });
                                }
                            });

            }
        );
    };
})( jQuery );