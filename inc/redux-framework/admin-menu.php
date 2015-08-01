<?php

if ( ! class_exists( 'Redux' ) ) {
    return;
}


// This is your option name where all the Redux data is stored.
$opt_name = 'mobappSettings';


$args = array(
    'disable_tracking' =>true,
    'forced_dev_mode_off'=>true,
    // TYPICAL -> Change these values as you need/desire
    'opt_name'          => $opt_name,            // This is where your data is stored in the database and also becomes your global variable name.
    'display_name'      => "WooCommerce Mobile App Manager",     // Name that appears at the top of your panel
    'display_version'   => "1.6.8.3",  // Version that appears at the top of your panel
    'menu_type'         => 'menu',                  //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
    'allow_sub_menu'    => true,                    // Show the sections below the admin menu item or not
    'menu_title'        => __('Mobile App Manager', 'mobapp-settings-page'),
    'page_title'        => __('WooCommerce Mobile App Manager', 'mobapp-settings-page'),
    // You will need to generate a Google API key to use this feature.
    // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
    'google_api_key' => 'AIzaSyAR4MRPWJvIC64kSbq0aTYM8VwbKGi-RYs', // Must be defined to add google fonts to the typography module,
    'google_update_weekly'=>false,
    'async_typography'  => true,                    // Use a asynchronous font on the front end or font string
    'admin_bar'            => false,
    // Show the panel pages on the admin bar
    'admin_bar_icon'       => 'dashicons-portfolio',
    // Choose an icon for the admin bar menu
    'admin_bar_priority'   => 50,
    // Choose an priority for the admin bar menu
    'global_variable'      => '',
    // Set a different name for your global variable other than the opt_name
    'dev_mode'             => false,
    // Show the time the page took to load, etc
    'update_notice'        => false,
    // If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
    'customizer'           => false,

    // OPTIONAL -> Give you extra features
    'page_priority'     => null,                    // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
    'page_parent'       => 'plugins.php',            // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
    'page_permissions'  => 'manage_options',        // Permissions needed to access the options panel.
    'menu_icon'         => 'dashicons-smartphone',                      // Specify a custom URL to an icon
    'last_tab'          => '',                      // Force your panel to always open to a specific tab (by id)
    'page_icon'         => 'dashicons-smartphone',           // Icon displayed in the admin panel next to your menu_title
    'page_slug'         => 'woocommerce-mobile-app-manager',              // Page slug used to denote the panel
    'save_defaults'     => true,                    // On load save the defaults to DB before user clicks save or not
    'default_show'      => false,                   // If true, shows the default value next to each field that is not the default value.
    'default_mark'      => '',                      // What to print by the field's title if the value shown is default. Suggested: *
    'show_import_export' => true,                   // Shows the Import/Export panel when not used as a field.

    // CAREFUL -> These options are for advanced use only
    'transient_time'    => 60 * MINUTE_IN_SECONDS,
    'output'            => true,                    // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
    'output_tag'        => true,                    // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
    'footer_credit'     => ' ',                   // Disable the footer credit of Redux. Please leave if you can help it.

    // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
    'database'              => '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
    'system_info'           => false, // REMOVE
    'use_cdn'              => true,
    // HINTS

    'hints'                => array(
        'icon'          => 'el el-question-sign',
        'icon_position' => 'right',
        'icon_color'    => 'lightgray',
        'icon_size'     => 'normal',
        'tip_style'     => array(
            'color'   => 'light',
            'shadow'  => true,
            'rounded' => false,
            'style'   => '',
        ),
        'tip_position'  => array(
            'my' => 'top left',
            'at' => 'bottom right',
        ),
        'tip_effect'    => array(
            'show' => array(
                'effect'   => 'slide',
                'duration' => '500',
                'event'    => 'mouseover',
            ),
            'hide' => array(
                'effect'   => 'slide',
                'duration' => '500',
                'event'    => 'click mouseleave',
            ),
        ),
    )
);



$args['share_icons'][] = array(
    'url'   => 'https://www.facebook.com/appilder',
    'title' => 'Like us on Facebook',
    'icon'  => 'el-icon-facebook'
);

$args['intro_text'] = __('<p>For creating  WooCommmerce mobile application  visit : <a target="_blank" href="https://appilder.com/woocommerce/">https://appilder.com/woocommerce/</a> |  <a target="_blank" href="https://appilder.com/woocommerce/docs">Plugin Documentation</a> </p>', 'mobapp-settings-page');

Redux::setArgs($opt_name,$args);

Redux::setSection($opt_name,array(
                'title'     => __('API Settings', 'mobapp-settings-page'),
                'desc'      => __('', 'mobapp-settings-page'),
                'icon'      => 'el-icon-home',
                // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
                'fields'    => array(
                    array(
                        'id'       => 'mobapp-api-key',
                        'type'     => 'text',
                        'title'    => __('API KEY', 'mobapp-settings-page'),
                        'subtitle' => __('Enter the API KEY obtained during creating application ', 'mobapp-settings-page'),
                        'default'  => '',
                        'validate' => 'not_empty'
                    ),
                    array(
                        'id'       => 'mobapp-api-secret',
                        'type'     => 'text',
                        'title'    => __('API SECRET', 'mobapp-settings-page'),
                        'subtitle' =>  __('Enter the API SECRET obtained during creating application', 'mobapp-settings-page'),
                        'default'  => '',
                        'validate' => 'not_empty'
                    ),
                   /* array(
                        'id'       => 'debug_mode',
                        'type'     => 'switch',
                        'title'    => __('Debug Mode', 'redux-framework-demo'),
                        'subtitle' => __('Enable/Disable Debug mode', 'redux-framework-demo'),
                        'on' => 'Enable',
                        'off'   => 'Disable',
                        'default'  => false,
                   ) */
                )
));



$attrs_raw = wc_get_attribute_taxonomy_names();
foreach($attrs_raw as $attr){
    $filter_args[$attr] = wc_attribute_label($attr);
}

 Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-align-right',
                'title'     => __('Products Filter', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'        => 'product_filter_attr',
                        'type'     => 'select',
                        'multi'     => true,
                        'title'    => __('Filter Attributes', 'redux-framework-demo'),
                        'desc'     => __('Select attributes to filter products', 'redux-framework-demo'),
                        'options'   => $filter_args,
                         //'data'      => 'taxonomies',
                         //'args'      => array('object_type'=>array('product')),
                    ),
                )
));
 Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-th-list',
                'title'     => __('Widgets', 'mobapp-settings-page'),
            ));
 Redux::setSection( $opt_name,array(
                'subsection'   => true,
                'icon'      => ' el-icon-slideshare',
                'title'     => __('Slider Widgets', 'mobapp-settings-page'),
                'heading' => __('Slider Widgets <a href="javascript:void(0);" data-id="slider_widgets" class="button add-slider-add button-primary">Add Slider</a>', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'slider_widgets',
                        'type'        => 'slideshow',
                        'title'       => __('Sliders', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited sliders with drag and drop sortings.', 'redux-framework-demo'),
                    )

                ),
            ));
Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-th-large',
                'subsection'   => true,
                'title'     => __('Grid Widgets', 'mobapp-settings-page'),
                'heading' => __('Grid Widgets <a href="javascript:void(0);" data-id="grid_widgets" class="button add-slider-add button-primary">New Grid</a>', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'grid_widgets',
                        'type'        => 'slideshow',
                        'title'       => __('Grids', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited grids with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  => "Grid",
                            "add_slider" => "Add Grid",
                            "add_slide" => "Add item",
                            "remove_slider" => "Delete Grid",
                            "remove_slide" => "Delete item",
                            "new_slide"     => "New item"
                        )
                    )
                ),
            ));

 Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-th-large',
                'subsection'   => true,
                'title'     => __('Menu Widgets', 'mobapp-settings-page'),
                'heading' => __('Menu Widgets <a href="javascript:void(0);" data-id="menu_widgets" class="button add-slider-add button-primary">New Menu</a>', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'menu_widgets',
                        'type'        => 'slideshow',
                        'title'       => __('Menus', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited Menus with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  => "Menu",
                            "add_slider" => "Add Menu",
                            "add_slide" => "Add item",
                            "remove_slider" => "Delete Menu",
                            "remove_slide" => "Delete item",
                            "new_slide"     => "New item"
                        )
                    )
                ),
            ));

 Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-text-width',
                'subsection'   => true,
                'title'     => __('Products Scroller', 'mobapp-settings-page'),
                'heading' => __('Product Scroller', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'product_scroller_widgets',
                        'type'        => 'slides_product_scroller',
                        'title'       => __('Products Scroller', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited Products Scroller with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  => "Scroller",
                            "add_slider" => "Add Scroller",
                            "remove_slider" => "Delete Scroller",
                            "new_slide"     => "New item"
                        )
                    )
                ),
            ));


 Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-text-width',
                'subsection'   => true,
                'title'     => __('HTML Widgets', 'mobapp-settings-page'),
                'heading' => __('HTML Widgets', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'html_widgets',
                        'type'        => 'slides_html',
                        'title'       => __('HTML Widgets', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited HTML Widgets with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  => "HTML Widget",
                            "add_slider" => "Add HTML Widget",
                            "remove_slider" => "Delete HTML Widget",
                            "new_slide"     => "New Widget"
                        )
                    )
                ),
            ));

 Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-lines',
                'subsection'   => true,
                'title'     => __('Banner Widgets', 'mobapp-settings-page'),
                'heading' => __('Banner Widgets', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'banner_widgets',
                        'type'        => 'slides_single',
                        'title'       => __('Banner Widgets', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited Banner Widgets with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  =>"Banner",
                            "add_slider" => "Add Banner",
                            "add_slide" => "Add item",
                            "remove_slider" => "Delete Banner",
                            "remove_slide" => "Delete item",
                            "new_slide"     => "New item"
                        )
                    )
                ),
            ));

 Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-search-alt',
                'subsection'   => true,
                'title'     => __('Search Widgets', 'mobapp-settings-page'),
                'heading' => __('Search Widgets', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'search_widgets',
                        'type'        => 'slides_search',
                        'title'       => __('Search Widgets', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited Search Widgets with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  => "Search",
                            "add_slider" => "Add Search Widget",
                            "add_slide" => "Add item",
                            "remove_slider" => "Delete Search Widget",
                            "remove_slide" => "Delete item",
                            "new_slide"     => "New item"
                        )
                    )
                ),
            ));
 Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-website',
                'title'     => __('In-App Pages', 'mobapp-settings-page'),
                'heading' => __('In-App Pages', 'mobapp-settings-page'),
                'fields'    => array(
                ),
            ));
     Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-website',
                'title'     => __('Home Page', 'mobapp-settings-page'),
                'subsection'   => true,
                'heading' => __('Home Page <a id="add-InAPPPageLink1" href="#TB_inline?width=520&amp;height=110&amp;inlineId=add-inPage-box" class="thickbox button button-primary">New In-App page</a>', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'      => 'page_layout_home',
                        'type'    => 'page_builder',
                        'title'   => 'Homepage Layout Manager',
                        'subtitle'     => __('This is your app home page layout. Drag and drop widgets you created to the page layout', 'mobapp-settings-page'),
                        'desc'    => 'Organize how you want the layout to appear on the homepage',
                        'full_width'=>true,
                        'options' => array(
                            'enabled'  => array(
                            ),
                            'disabled' => array(

                            )
                        ),
                    ),
                ),
     ));
            $option_name = 'inApp_pages' ;
            $pages = get_option( $option_name );
            if ( $pages !== false ) {
                $pages = json_decode($pages,true);
                if(is_array($pages)){
                    foreach($pages as $id=>$page){
             Redux::setSection( $opt_name,array(
                            'icon'      => 'el-icon-website',
                            'title'     => __('<span class="page_layout_link_'.$page.'">'.$page.'</span>', 'mobapp-settings-page'),
                            'subsection'   => true,
                            'class' => 'page_layout_class_'.$id,
                            'heading' => __($page.' <a  data-id="'.$id.'" data-field_id="page_layout_'.$id.'"  class="button button-danger remove-InAPPPage">Delete page</a>  <a id="add-InAPPPageLink'.$id.'" href="#TB_inline?width=520&height=70&inlineId=add-inPage-box" class="thickbox button button-primary">New In-App page</a>', 'mobapp-settings-page'),
                            'fields'    => array(
                                array(
                                    'id'      => 'page_layout_'.$id,
                                    'type'    => 'page_builder',
                                    'title'   => $page.' Layout Manager',
                                    'subtitle'=>__('This is your custom page ( '.$page.' ) layout. Drag and drop widgets you created to the page layout'),
                                    'desc'    => 'Organize how you want the layout to appear on the '.$page,
                                    'options' => array(
                                        'enabled'  => array(
                                        ),
                                        'disabled' => array(
                                        )
                                    ),
                                ),
                            ),
                        ));
                    }
                }
            }

			// Dummy section to add new In-App page
	     Redux::setSection( $opt_name,array(
	                'icon'      => 'el-icon-plus',
	                'class' => 'add-new-inAppPage',
	                'title'     => __('<span class="add-new-inAppPage-label">Add Page</span>', 'mobapp-settings-page'),
	                'subsection'   => true,
	            ));


Redux::setSection($opt_name,array(
                'icon'      => 'el-icon-envelope-alt',
                'class' => '',
                'title'     => __('Push Notification', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'      => 'gcm_auth_key',
                        'type'    => 'text',
                        'title'   => 'GCM Auth Key',
                        'desc'    => 'Enter your GCM Auth Key',
                    )
                ),
            ));

 Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-plus-sign',
                'class' => '',
                'subsection'   => true,
                'fields'    => array(
                    array(
                        'id'      => 'push_send_title',
                        'type'    => 'text',
                        "class"     => "wooAppPushNotify_Title",
                        'title'   => 'Title',
                        'subtitle'    => 'Enter push notification title',
                    ),
                    array(
                        'id'      => 'push_send_message',
                        'type'    => 'textarea',
                        "class"     => "wooAppPushNotify_Message",
                        'title'   => 'Message',
                        'subtitle'    => 'Enter push notification message (Short message)',
                    ),
                    array(
                        'id'      => 'action_send_action',
                        'type'    => 'select_action_field',
                        "class"     => "wooAppPushNotify_Action",
                        'title'   => 'Action',
                        'subtitle'    => 'Select push notification on click action',
                    ),
                    array(
                        'id'      => 'push_send_button',
                        "class"     => "wooAppPushNotify_Send",
                        'type'    => 'push_notification_send',
                        'title'   => '',
                   //     'subtitle'  => 'You have '.WOOAPP_API_Core_pushNotification::getCount().' users for receiving push notification',
                        'desc'    => '',
                    ),
                ),
                'title'     => __('Send Notification', 'mobapp-settings-page'),
            ));
         Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-time',
                'class' => '',
                'subsection'   => true,
                'title'     => __('History', 'mobapp-settings-page'),
                'fields'    =>   array(    array(
                    'full_width'    => true,
                    'id'      => 'push_history_button',
                    "class"     => "wooAppPushNotify_History",
                    'type'    => 'push_notification_history',
                    'title'   => 'Push notification history',
                    'desc'    => '',
                )),
            ));


Redux::setSection( $opt_name,array(
    'icon'      => 'el-icon-align-left',
    'title'     => __('<span class="navigation-menu-go">Navigation Menu</span>', 'mobapp-settings-page'),
    'heading' => __('Navigation Menu', 'mobapp-settings-page'),
    'fields'    => array(),
));

// If Redux is running as a plugin, this will remove the demo notice and links

add_action( 'redux/loaded', 'wooapp_redux_remove_demo' );


/**
 * Removes the demo link and the notice of integrated demo from the redux-framework plugin
 */
if ( ! function_exists( 'wooapp_redux_remove_demo' ) ) {
    function wooapp_redux_remove_demo() {
        // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
        if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
            remove_filter( 'plugin_row_meta', array(
                ReduxFrameworkPlugin::instance(),
                'plugin_metalinks'
            ), null, 2 );
            // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
            remove_action( 'admin_notices', array( ReduxFrameworkPlugin::instance(), 'admin_notices' ) );
        }
    }
}

if ( ! function_exists( 'remove_redux_menu' ) ) {
    /** remove redux menu under the tools **/
    add_action('admin_menu', 'remove_redux_menu', 12);
    function remove_redux_menu()
    {
        remove_submenu_page('tools.php', 'redux-about');
    }
}

