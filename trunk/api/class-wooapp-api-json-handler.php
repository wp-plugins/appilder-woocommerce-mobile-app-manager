<?php
/**
 * woocommerce_mobapp API
 *
 * Handles parsing JSON request bodies and generating JSON responses
 *
 * @author      WooThemes
 * @category    API
 * @package     woocommerce_mobapp/API
 * @since       2.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WOOAPP_API_JSON_Handler implements WOOAPP_API_Handler {

	/**
	 * Get the content type for the response
	 *
	 * @since 2.1
	 * @return string
	 */
	public function get_content_type() {

		return 'application/json; charset=' . get_option( 'blog_charset' );
	}

	/**
	 * Parse the raw request body entity
	 *
	 * @since 2.1
	 * @param string $body the raw request body
	 * @return array|mixed
	 */
	public function parse_body( $body ) {

		return json_decode( $body, true );
	}

	/**
	 * Generate a JSON response given an array of data
	 *
	 * @since 2.1
	 * @param array $data the response data
	 * @return string
	 */
	public function generate_response( $data ) {
        $data = apply_filters('apply_pagination',$data);
		if ( isset( $_GET['_jsonp'] ) ) {

			// JSONP enabled by default
			if ( ! apply_filters( 'woocommerce_mobapp_api_jsonp_enabled', true ) ) {

				getapi()->server->send_status( 400 );

				$data = array( array( 'code' => 'woocommerce_mobapp_api_jsonp_disabled', 'message' => __( 'JSONP support is disabled on this site', 'woocommerce_mobapp' ) ) );
			}

			// Check for invalid characters (only alphanumeric allowed)
			if ( preg_match( '/\W/', $_GET['_jsonp'] ) ) {

				getapi()->server->send_status( 400 );

				$data = array( array( 'code' => 'woocommerce_mobapp_api_jsonp_callback_invalid', __( 'The JSONP callback function is invalid', 'woocommerce_mobapp' ) ) );
			}

			return $_GET['_jsonp'] . '(' . json_encode( $data ) . ')';
		}

		return json_encode( $data );
	}

}
