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
    protected $customer_data = array();
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
		$routes[ $this->base.'/checkout_fields' ] = array(
			array( array( $this, 'checkout_fields' ),WOOAPP_API_Server::METHOD_GET ),
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
    public function checkout_fields($field){
        $checkout = WC()->checkout;
        $return = array("status"=>1,"field"=>$field,"items"=>array());
        if(isset($checkout->checkout_fields[$field])) {
            $this->customer_data = getapi()->WOOAPP_API_Customers->get_customer(get_current_user_id( ));
            if($field == "billing"){
                $options = get_option( 'wccs_settings' );
                if ( count( $options['buttons'] ) > 0 && function_exists('wpml_string_wccm')) :
                    foreach ( $options['buttons'] as $btn ) :
                        if ( ! empty( $btn['label'] ) &&  ($btn['type'] == 'text') ) {
                            $checkout->checkout_fields[$field][$btn['cow']] = array(
                                'id'                => $btn['cow'],
                                'type'          => 'text',
                                'class'         => array('wccs-field-class wccs-form-row-wide'),
                                'label'         =>  wpml_string_wccm(''.$btn['label'].''),
                                'required'  => $btn['checkbox'],
                                'placeholder'       => wpml_string_wccm(''.$btn['placeholder'].''),
                                'default'           => $checkout->get_value( ''.$btn['cow'].'' ),
                            );
                        }
                        if ( ! empty( $btn['label'] ) &&  ($btn['type'] == 'select') ) {
                            $checkout->checkout_fields[$field][$btn['cow']] = array(
                                'id'                => $btn['cow'],
                                'type'          => 'select',
                                'class'         => array('wccs-field-class wccs-form-row-wide'),
                                'label'         =>  wpml_string_wccm(''.$btn['label'].''),
                                'options'     => array(
                                 ),
                                'required'  => $btn['checkbox'],
                                'placeholder'       => wpml_string_wccm(''.$btn['placeholder'].''),
                                'default'           => $checkout->get_value( ''.$btn['cow'].'' ),
                            );

                            if(isset($btn['option_a']) && !empty($btn['option_a']))
                                $checkout->checkout_fields[$field][$btn['cow']]['options'][''.wpml_string_wccm(''.$btn['option_a'].'').''] = ''.wpml_string_wccm(''.$btn['option_a'].'').'';
                            if(isset($btn['option_b']) && !empty($btn['option_b']))
                                $checkout->checkout_fields[$field][$btn['cow']]['options'][''.wpml_string_wccm(''.$btn['option_b'].'').''] = ''.wpml_string_wccm(''.$btn['option_b'].'').'';
                            if(isset($btn['option_c']) && !empty($btn['option_c']))
                                $checkout->checkout_fields[$field][$btn['cow']]['options'][''.wpml_string_wccm(''.$btn['option_c'].'').''] = ''.wpml_string_wccm(''.$btn['option_c'].'').'';
                            if(isset($btn['option_d']) && !empty($btn['option_d']))
                                $checkout->checkout_fields[$field][$btn['cow']]['options'][''.wpml_string_wccm(''.$btn['option_d'].'').''] = ''.wpml_string_wccm(''.$btn['option_d'].'').'';
                        }

                        if ( ! empty( $btn['label'] ) &&  ($btn['type'] == 'date') ) {
                            $checkout->checkout_fields[$field][$btn['cow']] = array(
                                'id'                => $btn['cow'],
                                'type'          => 'text',
                                'class'         => array('wccs-field-class MyDate-'.$btn['cow'].' wccs-form-row-wide'),
                                'label'         =>  wpml_string_wccm(''.$btn['label'].''),
                                'required'  => $btn['checkbox'],
                                'placeholder'       => wpml_string_wccm(''.$btn['placeholder'].''),
                                'default'           => $checkout->get_value( ''.$btn['cow'].'' ),
                            );
                        }

                        if ( ! empty( $btn['label'] ) &&  ($btn['type'] == 'checkbox') ) {
                            $checkout->checkout_fields[$field][$btn['cow']] = array(
                                'id'                => $btn['cow'],
                                'type'          => 'checkbox',
                                'class'         => array('wccs-field-class wccs-form-row-wide'),
                                'label'         =>  wpml_string_wccm(''.$btn['label'].''),
                                'required'  => $btn['checkbox'],
                                'placeholder'       => wpml_string_wccm(''.$btn['placeholder'].''),
                                'default'           => $checkout->get_value( ''.$btn['cow'].'' ),
                            );
                         }
                    endforeach;
                endif;
            }
            if($field == "shipping" || $field == "billing")
                $this->customer_data = $this->customer_data['customer'][$field.'_address'];
            $this->customer_data['field'] =$field;
            $return["items"] = array_map(array($this,'parse_field'),$checkout->checkout_fields[$field],array_keys($checkout->checkout_fields[$field]));
            $return = apply_filters("appilder_woocommerce_checkout_fields",$return);
        }else
            $return['status'] = 0;
        return $return;
    }
    private function parse_field($args,$key){
        $defaults = array(
            'type'              => 'text',
            'id'                => $key,
            'label'             => '',
            'description'       => '',
            'placeholder'       => '',
            'maxlength'         => false,
            'required'          => false,
            // 'class'             => array(), // 'label_class'       => array(),           // 'input_class'       => array(), // 'return'            => false,         // 'custom_attributes' => array(),
            'options'           => array(),
            'validate'          => array(),
            'default'           => '',
            'default_value'           => '',
        );
        $args = wp_parse_args( $args, $defaults );
        if($args['type'] == "country") {
            $args['options'] = ($key == 'shipping_country') ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();
        }
        $args['options'] = array_map(array($this,'remove_key'),$args['options'],array_keys($args['options']));
        if($this->customer_data['field'] == "shipping" || $this->customer_data['field'] == "billing"){
            $key = preg_replace("/{$this->customer_data['field']}_/",'',$args['id']);
            if(isset($this->customer_data[$key]) && !empty($this->customer_data[$key]))
                $args['default_value'] =  !is_null($this->customer_data[$key])?$this->customer_data[$key]:'';
            else
                $args['default_value'] = !is_null($args['default'])?$args['default']:'';
        }else{
            $args['default_value'] = $args['default'];
        }
        $args['default'] = is_null($args['default'])?'':$args['default'];
        return $args;
    }
    private function remove_key($value,$key){
        return array(
            "id"=>$key,
            "name"=>$value
        );
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
        getapi()->WOOAPP_API_Cart->cart()->calculate_totals();
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
            if(isset($_POST['ship_to_different_address']) && ($_POST['ship_to_different_address']===false || $_POST['ship_to_different_address']==="false" || $_POST['ship_to_different_address']==0)){
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
