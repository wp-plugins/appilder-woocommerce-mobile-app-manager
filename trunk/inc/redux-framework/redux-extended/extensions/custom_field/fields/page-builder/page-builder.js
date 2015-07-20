/*global redux, redux_opts*/
/*
 * Field page_builder jquery function
 * Based on
 * [SMOF - Slightly Modded Options Framework](http://aquagraphite.com/2011/09/slightly-modded-options-framework/)
 * Version 1.4.2
 */

(function ($) {
    "use strict";
    redux.field_objects = redux.field_objects || {};
    redux.field_objects.page_builder = redux.field_objects.page_builder || {};
    var scroll = '';
    $(document).ready(
        function () {
            jQuery(".remove-InAPPPage").click(function (e) {
                e.preventDefault();
                var link_id = jQuery(this).parent().parent().attr("id");
                var tab = jQuery(this).parent().parent();
                if (confirm("Do you want to delete this page ?")) {
                    jQuery(this).html("Deleting...");
                    var id = jQuery(this).attr("data-id");
                    var data = {
                        'action': 'remove_inApp_page_action',
                        'id': id
                    };
                    $.post(ajaxurl, data, function (response) {
                        if (response.status) {
                            jQuery(tab).remove();
                            jQuery("#" + link_id + "_li").remove();
                            // alert('Page removed');
                            jQuery("#11_section_group_li_a").click();
                        } else {
                            alert('Error : ' + response.error);
                        }
                    }, "json");
                }
            });
            jQuery(".add-new-inAppPage-label").parent().parent("a").unbind('click');
            jQuery(".add-new-inAppPage-label").parent().parent("a").click(
                function (event) {
                    event.stopImmediatePropagation();
                    event.preventDefault();
                    jQuery("#add-InAPPPageLink1").click();
                    return false;
                });
            jQuery("#page_title-text").keydown(function (e) {
                if (e.keyCode == 13) {
                    jQuery("#page_title-add-button").click();
                }
            });
            jQuery("#page_title-add-button").click(function (e) {
                e.preventDefault();
                jQuery("#page_title-add-button").html("Adding.....");
                jQuery("#page_title-add-button").prop('disabled', true);
                jQuery("#page_title-text").prop('disabled', true);
                var title = jQuery("#page_title-text").val();
                var data = {
                    'action': 'add_inApp_page_action',
                    'title': title
                };
                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                $.post(ajaxurl, data, function (response) {
                    if (response.status) {
                        tb_remove();
                        var new_tab_id = parseInt(jQuery(".add-new-inAppPage-label").parent().parent("a").attr("id").match(/\d+/)[0]);
                        $.cookie(
                            'redux_current_tab', new_tab_id, {
                                expires: 7,
                                path: '/'
                            }
                        );
                        if (window.onbeforeunload !== null) {
                            alert('Page added . Reload Page or Save Settings to view newly added page');
                        } else {
                            if (confirm('Page Added,Reload page to view newly added page ?')) {
                                location.reload(true);
                            }
                        }
                    } else {
                        alert('Error : ' + response.error);
                    }
                    jQuery("#page_title-add-button").html("Add page");
                    jQuery("#page_title-add-button").prop('disabled', false);
                    jQuery("#page_title-text").prop('disabled', false);
                }, "json");
            });
            //redux.field_objects.page_builder.init();
        }
    );

    redux.field_objects.page_builder.init = function (selector) {

        if (!selector) {
            selector = $(document).find('.redux-container-page_builder');
        }
        $(".page_builder_acc").accordion({
            header: "> h3",
            collapsible: true,
            active: false,
            heightStyle: "content",
            icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }
        });
        $(selector).each(
            function () {
                var el = $(this);
                var parent = el;
                if (!el.hasClass('redux-field-container')) {
                    parent = el.parents('.redux-field-container:first');
                }
                if (parent.hasClass('redux-field-init')) {
                    parent.removeClass('redux-field-init');
                } else {
                    return;
                }
                /**    page_builder (Layout Manager) */
                el.find('.redux-page_builder').each(
                    function () {
                        var id = $(this).attr('id');
                        // console.log(id);
                        el.find('#' + id).find('ul:not(.sortSource)').sortable(
                            {
                                items: 'li',
                                placeholder: "placeholder",
                                connectWith: '.sortlist_' + id,
                                opacity: 0.8,
                                scroll: false,
                                out: function (event, ui) {
                                    if (!ui.helper) return;
                                    if (ui.offset.top > 0) {
                                        scroll = 'down';
                                    } else {
                                        scroll = 'up';
                                    }
                                    redux.field_objects.page_builder.scrolling($(this).parents('.redux-field-container:first'));

                                },
                                over: function (event, ui) {
                                    scroll = '';
                                },

                                deactivate: function (event, ui) {
                                    scroll = '';
                                },

                                stop: function (event, ui) {
                                    var page_builder = redux.page_builder[$(this).attr('data-id')];
                                    var id = $(this).find('h3').text();

                                    if (page_builder.limits && id && page_builder.limits[id]) {
                                        if ($(this).children('li').length >= page_builder.limits[id]) {
                                            $(this).addClass('filled');
                                            if ($(this).children('li').length > page_builder.limits[id]) {
                                                $(ui.sender).sortable('cancel');
                                            }
                                        } else {
                                            $(this).removeClass('filled');
                                        }
                                    }
                                },
                                receive: function (event, ui) {
                                    document.copyHelper = null;
                                    document.blockSort = false;

                                },
                                update: function (event, ui) {
                                    var page_builder = redux.page_builder[$(this).attr('data-id')];
                                    var id = $(this).find('h3').text();

                                    if (page_builder.limits && id && page_builder.limits[id]) {
                                        if ($(this).children('li').length >= page_builder.limits[id]) {
                                            $(this).addClass('filled');
                                            if ($(this).children('li').length > page_builder.limits[id]) {
                                                $(ui.sender).sortable('cancel');
                                            }
                                        } else {
                                            $(this).removeClass('filled');
                                        }
                                    }

                                    $(this).find('.position').each(
                                        function () {
                                            var listID = $(this).parent().attr('id');
                                            var parentID = $(this).parent().parent().attr('data-group-id');
                                            var icount = $(this).parent().parent().find("li[class^='" + listID + "_']").length;
                                            $(this).parent().removeClass(listID);
                                            if (listID.match(/([a-zA-Z_]+)_([0-9]+)$/i)) {
                                                listID = listID + '_' + icount;
                                            }
                                            $(this).parent().attr('id', listID);
                                            $(this).parent().addClass(listID);
                                            redux_change($(this));
                                            var optionID = $(this).parent().parent().parent().attr('id');
                                            $(this).prop(
                                                "name",
                                                redux.args.opt_name + '[' + optionID + '][' + parentID + '][' + listID + ']'
                                            );
                                        }
                                    );
                                }
                            }
                        );
                        el.find('#' + id).find('ul.sortSource').sortable({
                            connectWith: '.sortlist_' + id,
                            items: 'li',
                            zindex: 9200,
                            forcePlaceholderSize: false,
                            helper: function (e, li) {
                                document.copyHelper = li.clone().insertAfter(li);
                                var liclone = li.clone();
                                jQuery(liclone).css("position", "relative");
                                jQuery(liclone).css("z-index", "999");
                                li.parent().parent().append(liclone);
                                return liclone;
                            },
                            start: function (event, ui) {
                                document.blockSort = true;
                            },
                            stop: function (event, ui) {
                                document.copyHelper && document.copyHelper.remove();
                                if (document.blockSort) {
                                    event.preventDefault();
                                }
                            },
                            receive: function (event, ui) {
                                document.blockSort = false;
                                ui.sender.sortable("cancel");
                                if (typeof ui.item !== "undefined") {
                                    $(ui.item).remove();
                                } else
                                    $(event.toElement).remove();
                            }

                        });
                        el.find(".redux-page_builder").disableSelection();
                    }
                );
            }
        );
    };

    redux.field_objects.page_builder.scrolling = function (selector) {

        var scrollable = selector.find(".redux-page_builder");

        if (scroll == 'up') {
            scrollable.scrollTop(scrollable.scrollTop() - 20);
            setTimeout(redux.field_objects.page_builder.scrolling, 50);
        } else if (scroll == 'down') {
            scrollable.scrollTop(scrollable.scrollTop() + 20);
            setTimeout(redux.field_objects.page_builder.scrolling, 50);
        }
    };

})(jQuery);