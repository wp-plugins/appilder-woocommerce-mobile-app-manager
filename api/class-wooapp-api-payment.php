<?php
/**
 * woocommerce_mobapp API Orders Class
 *
 * Handles requests to the /orders endpoint
 *
 * @author      WooThemes
 * @category    API
 * @package     woocommerce_mobapp/API
 * @since       2.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WOOAPP_API_Payment extends WOOAPP_API_Resource {

	/** @var string $base the route base */
	protected $base = '/payment';

	/**
	 * Register the routes for this class
	 *
	 * GET /orders
	 * GET /orders/count
	 * GET|PUT /orders/<id>
	 * GET /orders/<id>/notes
	 *
	 * @since 2.1
	 * @param array $routes
	 * @return array
	 */
	public function register_routes( $routes ) {

		# GET /orders
		$routes[ $this->base ] = array(
			array( array( $this, 'get_available_payment_gateways' ), WOOAPP_API_Server::READABLE ),
		);

		# GET /orders
		$routes[ $this->base.'/checkout' ] = array(
			array( array( $this, 'check_out' ),WOOAPP_API_Server::METHOD_POST ),
		);

		# GET /orders
		$routes[ $this->base.'/allowed_country' ] = array(
			array( array( $this, 'get_allowed_country_states' ),WOOAPP_API_Server::METHOD_GET ),
		);

		# GET /orders
		$routes[ $this->base.'/shipping_country' ] = array(
			array( array( $this, 'get_shipping_country_states' ),WOOAPP_API_Server::METHOD_GET ),
		);


		return $routes;
	}
    public function  get_allowed_country_states(){
        $countries = WC()->countries->get_allowed_countries();
        $states_c =  WC()->countries->get_allowed_country_states();
        $return['countries'] = array();
        $i=-1;
        foreach($countries as $key=>$country) {
            $return['countries'][++$i]=array("id"=>$key,"name"=>html_entity_decode($country),"states"=>array());
            if(isset($states_c[$key]) && is_array($states_c[$key])){
                foreach($states_c[$key] as $key=>$state) {
                    $return['countries'][$i]["states"][] = array("id"=>$key,"name"=>html_entity_decode($state));
                }
            }
        }
        return $return;
    }
    public function get_shipping_country_states() {
        $countries = WC()->countries->get_shipping_countries();
        $states_c =  WC()->countries->get_shipping_country_states();
        $return['countries'] = array();
        $i=-1;
        foreach($countries as $key=>$country) {
            $return['countries'][++$i]=array("id"=>$key,"name"=>html_entity_decode($country),"states"=>array());
            if(isset($states_c[$key]) && is_array($states_c[$key])){
                foreach($states_c[$key] as $key=>$state) {
                    $return['countries'][$i]["states"][] = array("id"=>$key,"name"=>html_entity_decode($state));
                }
            }
        }
        return $return;
    }
	public  function get_available_payment_gateways( ) {
        $available_payment_gateways = WC()->payment_gateways()->get_available_payment_gateways();
	    $return = array();
	    foreach($available_payment_gateways as $key => $gateway){
            $return['gateways'][]=array(
                "id" => $gateway->id,
                "title" => $gateway->title,
                "description" =>$gateway->description,
                "icon" =>$gateway->icon,
                "chosen" =>$gateway->chosen,
                "order_button_text" =>$gateway->order_button_text,
                "enabled" =>$gateway->enabled,
                "countries" =>$gateway->countries,
                "availability" =>$gateway->availability,
                "supports" =>$gateway->supports
            );
        }
        $customer = getapi()->WOOAPP_API_Customers->get_customer(get_current_user_id( ));
        if(is_wooapp_api_error($customer))
            return $customer;
        $return = array_merge($return,$customer);
        getapi()->WOOAPP_API_Cart->cart()->calculate_totals();
        if ( wc_get_page_id( 'terms' ) > 0 && apply_filters( 'woocommerce_checkout_show_terms', true ) ) {
            $terms_is_checked = apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) );
            $return['terms'] = array(
                "show"  => 1,
                "link" =>  esc_url( get_permalink( wc_get_page_id( 'terms' ) ) ),
                "checked"   =>($terms_is_checked)?1:0
            );
        }else{
            $return['terms'] = array(
                "show"  => 0,
                "link" =>  "",
                "checked"=>0
            );
        }
        $return['cart_meta'] = getapi()->WOOAPP_API_Cart->get_cart_meta();
        $return['_wpnonce'] = wp_create_nonce('woocommerce-process_checkout');
        return $return;
	}
    public function check_out(){
        try{
            define("DOING_AJAX",true);
            require_once("woocommerce-extend/class-wooapp-checkout.php");
            if(isset($_POST['ship_to_different_address']) && ($_POST['ship_to_different_address']===false || $_POST['ship_to_different_address']==="false" || $_POST['ship_to_different_address']===0)){
                unset($_POST['ship_to_different_address']);
                $_POST['shipping_first_name'] = '';
                $_POST['shipping_last_name'] = '';
                $_POST['shipping_company'] = '';
                $_POST['shipping_address_1'] = '';
                $_POST['shipping_address_2'] = '';
                $_POST['shipping_city'] = '';
                $_POST['shipping_postcode'] = '';
                $_POST['shipping_country'] = '';
                $_POST['shipping_state'] = '';
            }
            WC()->cart = getapi()->WOOAPP_API_Cart->cart();
            $woocommerce_checkout = new WOOAPP_Checkout();
            $woocommerce_checkout->enable_signup=false;
            $woocommerce_checkout->enable_guest_checkout=false;
            ob_start();
            $return =  $woocommerce_checkout->process_checkout();
            ob_get_clean();
            if(isset($return['result']) && $return['result']!="failure" && isset($return['order_id'])){
               $order = getapi()->WOOAPP_API_Orders->get_order($return['order_id']);
                $return['order'] = $order['order'];
                getapi()->WOOAPP_API_Cart->cart()->empty_cart(true);
            }
            if(!isset($return['result']) || $return['result']!="success"){
                if(isset($return['messages'])){
                    preg_match_all("/<li>(.*?)<\/li>/i",$return['messages'],$errors);
                    if(is_array($errors[1]) && !empty($errors[1])){
                        foreach($errors[1] as $error)
                            $return = WOOAPP_API_Error::setError($return,"checkout_error",strip_tags($error),"woocommerce_mobapp");
                    }else
                        $return = WOOAPP_API_Error::setError($return,"checkout_error","Cannot proccess checkout","woocommerce_mobapp");
                }else
                    $return = WOOAPP_API_Error::setError($return,"checkout_error","Cannot proccess checkout","woocommerce_mobapp");
            }
        } catch(Exception $e){
            $return = WOOAPP_API_Error::setError($return,"checkout_error","Cannot proccess checkout","woocommerce_mobapp");
        }
        return $return;
    }
}
