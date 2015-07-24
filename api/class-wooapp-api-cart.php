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
/**
 * Class WOOAPP_API_Cart
 * @todo Check notices and return if has error
 */
class WOOAPP_API_Cart extends WOOAPP_API_Resource {

    /** @var string $base the route base */
    protected $base = '/cart';
    protected $cart_object;
    protected $shipping_calculated = false;
    public function __construct(WOOAPP_API_Server $server){
        parent::__construct($server);
        require_once("woocommerce-extend/class-wooapp-cart.php");
    }
    public function cart(){
        if(!isset($this->cart_object)){
             $this->cart_object = new WOOAPP_Cart();
             WC()->cart = $this->cart_object;
             if(!$this->shipping_calculated) {
                 $data = WC()->session->get('wc_shipping_calculate_details');
                 if (!empty($data)) {
                     $_POST = array_merge($_POST, $data);
                     $this->calculate_shipping($data['calc_shipping_country'], $data['calc_shipping_state'], false);
                     $this->set_shipping_method();
                 }
             }
            // WC()->customer->set_shipping_to_base();
        }
        global $woocommerce;
        $woocommerce->cart = $this->cart_object;
        return $this->cart_object;
    }
    /**
     * Register the routes for this class
     *
     * @param array $routes
     * @return array
     */
    public function register_routes( $routes ) {

        $routes[ $this->base ] = array(
            array( array( $this, 'get_cart_items' ), WOOAPP_API_Server::READABLE ),
        );

        $routes[ $this->base . '/count'] = array(
            array( array( $this, 'get_cart_count' ), WOOAPP_API_Server::READABLE ),
        );

        $routes[ $this->base . '/add'] = array(
            array( array( $this, 'add_to_cart' ),  WOOAPP_API_Server::EDITABLE |WOOAPP_API_Server::ACCEPT_DATA ),
        );

        $routes[ $this->base . '/meta'] = array(
            array( array( $this, 'cart_meta' ),  WOOAPP_API_Server::METHOD_GET),
        );

        $routes[ $this->base . '/set_quantity' ] = array(
            array( array( $this, 'update_quantity' ), WOOAPP_API_Server::EDITABLE),
        );

        $routes[ $this->base . '/update_quantity' ] = array(
            array( array( $this, 'update_bulk_quantity' ), WOOAPP_API_Server::EDITABLE),
        );

        $routes[ $this->base . '/coupon/add' ] = array(
            array( array( $this, 'add_coupon' ), WOOAPP_API_Server::METHOD_POST),
        );

        $routes[ $this->base . '/coupon/remove' ] = array(
            array( array( $this, 'remove_coupon' ), WOOAPP_API_Server::METHOD_POST),
        );

        $routes[ $this->base . '/calculate_shipping' ] = array(
            array( array( $this, 'calculate_shipping' ), WOOAPP_API_Server::METHOD_POST),
        );

        return $routes;
    }

    public function get_cart_meta(){
        $this->cart()->calculate_totals();
        $coupon_discount_amounts = array();
        foreach($this->cart()->coupon_discount_amounts as $coupon=>$price){
            $coupon_discount_amounts[]=array("coupon"=>$coupon,"discount"=>$price);
        }
        return array(
            "coupons_applied"=>$this->cart()->get_applied_coupons(),
            "coupon_discounted"=>$coupon_discount_amounts,
            "count"=>$this->cart()->get_cart_contents_count(),
            "shipping_fee" => $this->cart()->shipping_total,
            "tax"=>$this->cart()->get_cart_tax(),
            "fees"=>$this->cart()->get_fees(),
            "currency" =>get_woocommerce_currency(),
            "currency_symbol"=>get_woocommerce_currency_symbol(),
            "total"=>$this->cart()->cart_contents_total,
            "order_total"=>$this->cart()->total,
            "price_format"=>get_woocommerce_price_format(),
            'timezone'			 => wc_timezone_string(),
            'tax_included'   	 => ( 'yes' === get_option( 'woocommerce_prices_include_tax' ) ),
            'weight_unit'    	 => get_option( 'woocommerce_weight_unit' ),
            'dimension_unit' 	 => get_option( 'woocommerce_dimension_unit' ),
        );
    }
    public function cart_meta(){
        $cart = $this->cart()->get_cart_api(); //  get_user_meta(get_current_user_id(), '_woocommerce_persistent_cart',true);
        $return['cart'] = array();
        if(!empty($cart)){
            $return = $this->get_cart_meta();
            $return['status'] =1;
        }else{
            $return = WOOAPP_API_Error::setError($return,"empty_cart","Cart is empty");
        }
        return $return;
    }

    /**
     * @param bool|array $shipping_methods
     */
    public function set_shipping_method($shipping_methods=false){
        if(empty($shipping_methods) && isset($_POST['shipping_methods']))
            $shipping_methods = $_POST['shipping_methods'];
        elseif(empty($shipping_methods) && isset($_GET['shipping_methods']))
            $shipping_methods = $_GET['shipping_methods'];

        if(empty($shipping_methods))
            $shipping_methods =  WC()->session->get( 'wc_chosen_shipping_methods', $shipping_methods );

        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
        if ( isset($shipping_methods) && is_array( $shipping_methods) && !empty($shipping_methods)) {
            foreach ($shipping_methods as $i => $value) {
                $chosen_shipping_methods[$i] = wc_clean($value);
            }
            WC()->session->set( 'wc_chosen_shipping_methods', $shipping_methods );
            WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
        }
    }

    /**
     * Get all orders
     *
     * @since 2.1
     * @internal param string $fields
     * @internal param array $filter
     * @internal param string $status
     * @internal param int $page
     * @internal param WC_Cart $cart
     * @param array $shipping_methods
     * @return array
     */
    public function get_cart_items($shipping_methods=array()){
        $this->set_shipping_method($shipping_methods);
        $this->cart()->calculate_totals();
        $cart = $this->cart()->get_cart_api(); //  get_user_meta(get_current_user_id(), '_woocommerce_persistent_cart',true);
        $return['cart'] = array();
        if(!empty($cart)){
            $return['status'] =1;
            $return['cart'] = $cart;
            $this->cart()->calculate_totals();
            $return = array_merge($return,$this->get_cart_meta());
        }else{
           $return = WOOAPP_API_Error::setError($return,"empty_cart","Cart is empty");
        }
        if(is_wooapp_api_error($return))
            return $return;
        $return = array_merge($return,$this->get_shipping_methods());
        return $return;
    }
    public function get_shipping_methods(){
        $return = array();
        if($this->cart()->needs_shipping() && $this->cart()->show_shipping()){
            $return['show_shipping'] = 1;
            $this->cart()->calculate_shipping();
            $packages = WC()->shipping()->get_packages();
            foreach ( $packages as $i => $package ) {
                $chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
                $return['shipping'][] = array('methods'=>$this->getMethodsInArray($package['rates']),
                    'chosen'=>$chosen_method,'index'=>$i
                );
            }
        }else{
            $return['show_shipping'] = 0;
            $return['shipping'] = array();
        }
        if(empty($return['shipping']) || is_null($return['shipping']) || !is_array($return['shipping'])) {
            $return['show_shipping'] = 0;
            $return['shipping'] = array();
        }

        return $return;
    }
    private function getMethodsInArray($methods){
        $return = array();
        foreach($methods as $method){
            $return[]=array(
                'id'=>$method->id,
                'label'=>$method->label,
                'cost'=>$method->cost,
                'taxes'=>$method->taxes,
                'method_id'=>$method->method_id,
            );
        }
        return $return;
    }
    public function is_readable($cart){

    }

    public function get_wp_notices_error(){
        $notices = WC()->session->get( 'wc_notices', array() );
        if(!empty($notices['error'])){
            $return = array();
            foreach($notices['error'] as $key=>$error){
                $return = WOOAPP_API_Error::setError($return,'cart_add_error'.$key, html_entity_decode($error));
            }
            wc_clear_notices();
        }else{
            return false;
        }
    }

    /**
     * @param $product_id
     * @param $quantity
     * @param null $variation_id
     * @param string $variation
     * @internal param $id
     * @return mixed
     */
    public function add_to_cart($product_id,$quantity,$variation_id=null,$variation=''){
        $data=array();
            $added =    $this->cart()->add_to_cart( $product_id, $quantity, $variation_id, $variation, $data );
        $this->cart()->check_cart_items();
        $this->cart()->persistent_cart_update();
        if(!$added){
            $return = $this->get_wp_notices_error();
            if(!is_wooapp_api_error($return))
                $return = WOOAPP_API_Error::setError($return,"error_add","Cannot add item to cart");
        }else{
            $return = $this->get_cart_items();
        }
        return $return;
    }

    /**
     * Get the total number of cart
     * @return array
     */
    public function get_cart_count( ) {
        return array( 'count' => (int)$this->cart()->get_cart_contents_count()  );
    }

    /**
     * Update  cart item quantity
     *
     * @since 2.1
     * @param $key
     * @param $quantity
     * @param bool $refresh_totals
     * @param bool $return_item
     * @internal param int $id the order ID
     * @internal param array $data
     * @return array
     */
    public function update_quantity($key, $quantity,$refresh_totals = true,$return_item = true ) {
        $this->cart()->set_quantity( $key, $quantity, $refresh_totals);
        $this->cart()->check_cart_items();
        $this->cart()->persistent_cart_update();
        if($return_item)
        {
            $return = $this->get_cart_items();
            return $return;
        }else
            return true;
    }


    /**
     * Update  cart item quantity
     *
     * @since 2.1
     * @param $bulk
     * @internal param $quantity
     * @return array
     */
    public function update_bulk_quantity($bulk) {
        foreach($bulk as $key=>$qty){
            $this->update_quantity($key,$qty,true,false);
        }
        $return = $this->get_cart_items();
        return $return;
    }

    public function add_coupon($coupon_code){
        $added = $this->cart()->add_discount($coupon_code);
        if(!$added){
            $return = $this->get_wp_notices_error();
            if(!is_wooapp_api_error($return))
                $return = WOOAPP_API_Error::setError($return,"invalid_coupon","Invalid coupon");
        }else{
            $this->cart()->persistent_cart_update();
            $return = $this->get_cart_items();
        }
       return $return;
    }
    public function remove_coupon($coupon_code){
        $added = $this->cart()->remove_coupon($coupon_code);
        if(!$added){
            $return = $this->get_wp_notices_error();
            if(!is_wooapp_api_error($return))
                $return = WOOAPP_API_Error::setError($return,"invalid_coupon","Cannot remove coupon");
        }else{
            $this->cart()->persistent_cart_update();
            $return = $this->get_cart_items();
        }
       return $return;
    }
    public function calculate_shipping($calc_shipping_country,$calc_shipping_state,$has_to_return = true){
        $return = array();
        $data = array('calc_shipping_country'=>wc_clean($calc_shipping_country),'calc_shipping_state'=>wc_clean($calc_shipping_state));
        try {
             if(apply_filters( 'woocommerce_shipping_calculator_enable_postcode', true ) && !isset($_POST['calc_shipping_postcode'])){
                 $return = WOOAPP_API_Error::setError($return,"missing_parameter","Missing parameter calc_shipping_postcode");
             }elseif(apply_filters( 'woocommerce_shipping_calculator_enable_postcode', true ) && isset($_POST['calc_shipping_postcode']))
                 $data['calc_shipping_postcode'] = wc_clean($_POST['calc_shipping_postcode']);
             else
                 $data['calc_shipping_postcode'] = '';

             if(apply_filters( 'woocommerce_shipping_calculator_enable_city', false)  && !isset($_POST['calc_shipping_city'])){
                 $return = WOOAPP_API_Error::setError($return,"missing_parameter","Missing parameter calc_shipping_city");
             }elseif(apply_filters( 'woocommerce_shipping_calculator_enable_city', false)  && isset($_POST['calc_shipping_city']))
                 $data['calc_shipping_city'] = wc_clean($_POST['calc_shipping_city']);
             else
                 $data['calc_shipping_city'] = '';

             if(!is_wooapp_api_error($return)){
                     WC()->shipping->reset_shipping();
                     $country  = $data['calc_shipping_country'];
                     $state    = $data['calc_shipping_state'];
                     $postcode = $data['calc_shipping_postcode'];
                     $city     = $data['calc_shipping_city'];

                     if ( !empty($postcode) && ! WC_Validation::is_postcode( $postcode, $country ) ) {
                         throw new Exception( __( 'Please enter a valid postcode/ZIP.', 'woocommerce' ) );
                     } elseif ( !empty($postcode) ) {
                         $postcode = wc_format_postcode( $postcode, $country );
                     }

                     if ( $country ) {
                         WC()->customer->set_location( $country, $state, $postcode, $city );
                         WC()->customer->set_shipping_location( $country, $state, $postcode, $city );
                     } else {
                         WC()->customer->set_to_base();
                         WC()->customer->set_shipping_to_base();
                     }

                     WC()->customer->calculated_shipping( true );
                     $this->shipping_calculated = true;
                     do_action( 'woocommerce_calculated_shipping' );
             }
        }catch (Exception $e){
            $return = WOOAPP_API_Error::setError("unable_to_process","Unable to process");
        }
        if($this->get_wp_notices_error() !== false){
            $return = $this->get_wp_notices_error();
        }

        if(!is_wooapp_api_error($return)  && $has_to_return){
            WC()->session->set('wc_shipping_calculate_details',$data);
            $return= $this->get_cart_items();
            $return['status'] =1;
        }

        if($has_to_return)
            return $return;
        else
            return true;
    }
    public function __destruct(){
        wc_clear_notices();
    }
}
