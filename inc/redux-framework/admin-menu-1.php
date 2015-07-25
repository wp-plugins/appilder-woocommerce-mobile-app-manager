<?php

if ( ! class_exists( 'Redux' ) ) {
    return;
}


// This is your option name where all the Redux data is stored.
$opt_name = 'mobappNavigationSettings';


$args = array(
    'disable_tracking' =>true,
    'forced_dev_mode_off'=>true,
    // TYPICAL -> Change these values as you need/desire
    'opt_name'          => $opt_name,            // This is where your data is stored in the database and also becomes your global variable name.
    'display_name'      => "Navigation Menu",     // Name that appears at the top of your panel
    // 'display_version'   => "1.6.5",  // Version that appears at the top of your panel
    'menu_type'         => 'submenu',                  //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
    'allow_sub_menu'    => true,                    // Show the sections below the admin menu item or not
    'menu_title'        => __('Navigation Menu', 'mobapp-settings-page'),
    'page_title'        => __('Appilder WooCommerce Mobile App Manager - Navigation Menu', 'mobapp-settings-page'),

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
    'page_parent'       => 'woocommerce-mobile-app-manager',            // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
    'page_permissions'  => 'manage_options',        // Permissions needed to access the options panel.
    'menu_icon'         => 'dashicons-smartphone',                      // Specify a custom URL to an icon
    'last_tab'          => '',                      // Force your panel to always open to a specific tab (by id)
    'page_icon'         => 'dashicons-smartphone',           // Icon displayed in the admin panel next to your menu_title
    'page_slug'         => 'woocommerce-mobile-app-manager-nav-menu',              // Page slug used to denote the panel
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

Redux::setSection( $opt_name,array(
                'icon'      => 'el-icon-align-left',
                'title'     => __('Navigation Menu', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'        => 'nav_menu',
                        'type'      => 'nav_menu_builder',
                        'title'     => __('Navigation menu', 'redux-framework-demo'),
                        'doc'  => __('Add items to your navigation menu from the menu items.', 'redux-framework-demo'),
                    )
                )
 ));

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