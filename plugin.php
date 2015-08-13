<?php
/**
 * Plugin Name: WooCommerce Mobile App Manager
 * Plugin URI: https://appilder.com/woocommerce
 * Description: WooCommerce Mobile App Manager plugin is manager for managing android app created from <a href="https://appilder.com/woocommerce" target="_blank">appilder.com/woocommerce</a>
 * Version: 1.6.8.6
 * Author: Appilder
 * Author URI: http://appilder.com
 * Requires at least: 3.8
 * Tested up to: 4.2.2
 *
 */

/**
 * Check if woocommerce is active
 **/

if (!defined('ABSPATH')) exit;
define("APPILDER_WOOCOMMERCE_PLUGIN",true);
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || is_multisite()) {
    // Init API
    global $api;
    // header("Access-Control-Allow-Origin: *"); //@todo: Comment on production
    add_action('woocommerce_loaded','load_wooapp_plugin');
    include_once('class-wooapp-api.php');

    /*
        include_once('inc/update-checker.php');
        $plugin_updates = new wooapp_wp_auto_update("1.4.1","https://appilder.com/woocommerce/plugin/update","woocommerce-mobile-app-manager/plugin.php");
    */

	$api = new WOOAPP_API();
    function getapi()
    {
        global $api;
        return $api;
    }
    /**
     * Return the WC API URL for a given request
     *
     * @param mixed $request
     * @param mixed $ssl (default: null)
     * @return string
     */
    function api_request_url($request, $ssl = null)
    {
        if (is_null($ssl)) {
            $scheme = parse_url(get_option('home'), PHP_URL_SCHEME);
        } elseif ($ssl) {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }
        if (get_option('permalink_structure')) {
            return esc_url_raw(trailingslashit(home_url('/wooapp-api/' . $request, $scheme)));
        } else {
            return esc_url_raw(add_query_arg('wooapp-api', $request, trailingslashit(home_url('', $scheme))));
        }
    }
    function get_woocommerce_mobapp_api_url($path)
    {
        $url = get_home_url(null, 'wooapp-api/v' . WOOAPP_API::VERSION . '/', is_ssl() ? 'https' : 'http');
        if (!empty($path) && is_string($path)) {
            $url .= ltrim($path, '/');
        }
        return $url;
    }
    function  wooapp_path($append=""){
        $path = plugin_dir_path( __FILE__ );
        if(!empty($append))
            return $path.$append;
        else
            return $path;
    }

    if(is_admin()){
     require_once('inc/post_meta.php');
    }

    function load_wooapp_plugin()
    {
        if (is_admin() || !empty($_GET['wooapp-api-route']) || !empty($_GET['wooapp-api'])) {
            require_once('inc/redux-framework/redux.php');
            include_once('inc/ajax-functions.php');
            include_once('inc/push-notification/class.pushNotification.php');
        }
    }

    function wooapp_activated() {
        //        flush_rewrite_rules();
	    /* enable/disable tracking on Redux Framework option panel */
	    $framework_options = get_option('redux-framework-tracking'); // get the array
	    $framework_options['allow_tracking'] = 'no'; // set the value to yes or no
	    update_option('redux-framework-tracking', $framework_options); // update the array
        do_action("wooapp_activate");
    }

    function wooapp_uninstall(){
     //   flush_rewrite_rules();
        do_action("wooapp_uninstall");
    }
    function wooapp_deactivate(){
    }
    register_activation_hook( __FILE__ , 'wooapp_activated');
    register_deactivation_hook( __FILE__ , 'wooapp_deactivate');
    register_uninstall_hook( __FILE__ , 'wooapp_uninstall');

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wooapp_api_ts_add_plugin_action_links');
	function wooapp_api_ts_add_plugin_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=woocommerce-mobile-app-manager">Settings</a>',
				'docs' => '<a target="_blank" href="https://appilder.com/woocommerce/docs/">Docs</a>',
				'create_app' => '<a target="_blank" href="https://appilder.com/woocommerce/">Create App</a>' ),
				$links
		);
	}
}
