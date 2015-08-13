<?php
/**
 * @author      Mohammed Anees
 * @since       8/15/2014
 */
if (!defined('ABSPATH')) exit;

function appilder_woocommerce_upload_mimes( $existing_mimes ) {
    $existing_mimes['pem'] = 'application/x-pem-file';
    return $existing_mimes;
}
add_filter( 'mime_types', 'appilder_woocommerce_upload_mimes' );

require_once('redux-extended/loader.php');
if ( !class_exists( 'ReduxFramework' ) ) {
    require_once( 'ReduxCore/framework.php' );
}

require_once('admin-menu.php');
require_once('admin-menu-1.php');

add_action( 'redux/options/mobappNavigationSettings/global_variable', 'wooapp_copy_navSettings' );


if ( ! function_exists( 'wooapp_copy_navSettings' ) ) { // Created to copy nav from old to new :D
    function wooapp_copy_navSettings($options)
    {
        if(!isset($options['nav_menu']) || empty($options['nav_menu'])){
            global $mobappSettings;
            if(isset($mobappSettings['nav_menu'])){
                $options['nav_menu'] = $mobappSettings['nav_menu'];
                /** @var ReduxFramework $rudex_ins */
                $rudex_ins = ReduxFrameworkInstances::get_instance("mobappNavigationSettings");
                if(is_a($rudex_ins,"ReduxFramework")){
                    $rudex_ins->set("nav_menu",$mobappSettings['nav_menu']);
                }
            }
        }
        return $options;
    }
}
