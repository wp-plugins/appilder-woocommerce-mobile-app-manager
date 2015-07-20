<?php
/**
 * User: Mohammed Anees
 * Date: 5/10/14
 * Time: 8:31 PM
 */

class WOOAPP_Cart extends WC_Cart {
    function __construct(){
        parent::__construct();
        parent::init();
        define('WOOCOMMERCE_CART',true);
        $cart = get_user_meta(get_current_user_id(), '_woocommerce_persistent_cart',true);
        $current_cart =  $this->get_cart();
        if(!empty($cart['cart'])){
            foreach($cart['cart'] as $key=>$cart){
                if(!isset($current_cart[$key]))
                    $this->add_to_cart($cart['product_id'],$cart['quantity'],$cart['variation_id'],$cart['variation']);
            }
        }
        $this->check_cart_items();
        $this->persistent_cart_update();
    }

    /**
     * Returns the contents of the cart in an array.
     *
     * @return array contents of the cart
     */
    public function get_cart_api() {
       $cart = array_filter( (array) $this->cart_contents );
       $return =array();
       foreach($cart as $key=>$item){
           $item["key"] = $key;
           $item = array_merge($item,get_product_short_details($item["data"]));
           unset($item["data"]);
           $return[] = $item;
       }
       return $return;
    }
} 