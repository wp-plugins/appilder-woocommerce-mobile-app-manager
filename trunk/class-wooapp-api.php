<?php
/**
 * woocommerce_mobapp API
 *
 * Handles wooappAPI endpoint requests
 *
 * @author      WooThemes
 * @category    API
 * @package     woocommerce_mobapp/API
 * @since       2.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WOOAPP_API {

    /** This is the major version for the REST API and takes
     * first-order position in endpoint URLs
     */
    const VERSION = 1;

    /** @var WOOAPP_API_Server the REST API server */
    public $server;

    /**
     * Setup class
     *
     * @access public
     * @since 2.0
     * @return WOOAPP_API
     */
    public function __construct() {

        // add query vars
        add_filter( 'query_vars', array( $this, 'add_query_vars'), 0 );

        // register API endpoints
        add_action( 'init', array( $this, 'add_endpoint'), 0 );

        // handle REST/legacy API request
        add_action( 'parse_request', array( $this, 'handle_api_requests'), 0 );
    }
    public function override_session(){
        include_once( 'api/woocommerce-extend/class-wooapp-session.php' );
        WC()->session = new WOOAPP_Session();
        return WC()->session;
    }

    /**
     * add_query_vars function.
     *
     * @access public
     * @since 2.0
     * @param $vars
     * @return array
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'wooapp-api';
        $vars[] = 'wooapp-api-route';
        return $vars;
    }

    /**
     * add_endpoint function.
     *
     * @access public
     * @since 2.0
     * @return void
     */
    public function add_endpoint() {

        // REST API
        add_rewrite_rule( '^wooapp-api\/v' . self::VERSION . '/?$', 'index.php?wooapp-api-route=/', 'top' );
        add_rewrite_rule( '^wooapp-api\/v' . self::VERSION .'(.*)?', 'index.php?wooapp-api-route=$matches[1]', 'top' );

        // legacy API for payment gateway IPNs
        add_rewrite_endpoint( 'wooapp-api', EP_ALL );
    }


    /**
     * API request - Trigger any API requests
     *
     * @access public
     * @since 2.0
     * @return void
     */
    public function handle_api_requests() {
        global $wp;

        if ( ! empty( $_GET['wooapp-api'] ) )
            $wp->query_vars['wooapp-api'] = $_GET['wooapp-api'];

        if ( ! empty( $_GET['wooapp-api-route'] ) )
            $wp->query_vars['wooapp-api-route'] = $_GET['wooapp-api-route'];

        // REST API request
        if ( ! empty( $wp->query_vars['wooapp-api-route'] ) ) {

            define( 'WOOAPP_API_REQUEST', true );

            // load required files
            $this->includes();

            $this->server = new WOOAPP_API_Server( $wp->query_vars['wooapp-api-route'] );

            // load API resource classes
            $this->register_resources( $this->server );

            // Fire off the request
            $this->server->serve_request();
            exit;
        }

        // legacy API requests
        if ( ! empty( $wp->query_vars['wooapp-api'] ) ) {

            // Buffer, we won't want any output here
            ob_start();

            // Get API trigger
            $api = strtolower( esc_attr( $wp->query_vars['wooapp-api'] ) );

            // Load class if exists
            if ( class_exists( $api ) )
                $api_class = new $api();

            // Trigger actions
            do_action( 'woocommerce_mobapp_api_' . $api );

            // Done, clear buffer and exit
            ob_end_clean();
            die('1');
        }
    }


    /**
     * Include required files for REST API request
     *
     * @since 2.1
     */
    private function includes() {
        include_once( 'api/api-functions.php' );
        include_once( 'api/class-wooapp-api-error.php' );
        include_once( 'api/class-wooapp-api-server.php' );
        include_once( 'api/interface-wooapp-api-handler.php' );
        include_once( 'api/class-wooapp-api-json-handler.php' );
        include_once( 'api/class-wooapp-api-xml-handler.php' );

        // authentication
        include_once( 'api/class-wooapp-api-authentication.php' );
        $this->authentication = new WOOAPP_API_Authentication();

        include_once( 'api/class-wooapp-api-resource.php' );
        include_once( 'api/class-wooapp-api-orders.php' );
        include_once( 'api/class-wooapp-api-products.php' );
      //  include_once( 'api/class-wooapp-api-coupons.php' );
        include_once( 'api/class-wooapp-api-customers.php' );
      //  include_once( 'api/class-wooapp-api-reports.php' );
        include_once( 'api/class-wooapp-api-cart.php' );
        include_once( 'api/class-wooapp-api-AppBase.php' );
        include_once( 'api/class-wooapp-api-InAppPages.php' );
        include_once( 'api/class-wooapp-api-pushNotification.php' );
        include_once( 'api/class-wooapp-api-widgets.php' );
        include_once( 'api/class-wooapp-api-payment.php' );
        include_once( 'api/class-wooapp-api-exception.php' );

        // allow plugins to load other response handlers or resource classes
        do_action( 'woocommerce_mobapp_api_loaded' );
    }
    /**
     * Register available API resources
     *
     * @since 2.1
     * @param object $server the REST server
     */
    public function register_resources( $server ) {
        $api_classes = apply_filters( 'woocommerce_mobapp_api_classes',
            array(
                'WOOAPP_API_Customers',
                'WOOAPP_API_Cart',
                'WOOAPP_API_AppBase',
                'WOOAPP_API_Orders',
                'WOOAPP_API_Products',
           //     'WOOAPP_API_Coupons',
           //     'WOOAPP_API_Reports',
                'WOOAPP_API_InAppPages',
                'WOOAPP_API_pushNotification',
                'WOOAPP_API_Widgets',
                'WOOAPP_API_Payment',
            )
        );

        foreach ( $api_classes as $api_class ) {
            $this->$api_class = new $api_class( $server );
        }
    }

}
