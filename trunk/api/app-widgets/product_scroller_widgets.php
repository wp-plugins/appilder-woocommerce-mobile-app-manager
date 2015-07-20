<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once("interface-widget.php");

class Product_Scroller extends  WOOAPP_Widget_Handler{
    public function __construct(){
        global $mobappSettings;
        $this->type = "product_scroller_widgets";
        $this->id = 5;
        $this->values = array();
        if(isset($mobappSettings[$this->type]['slides'])){
            $this->values = $mobappSettings[$this->type]['slides'];
            if(isset($this->values['_blank']))
                unset($this->values['_blank']);
        }
    }
    public function getValueById($id){
        if(isset($this->values[$id])){
            return  $this->api_out($this->values[$id]);
        }else{
            if($id=="recent"){
                $products = $this->get_recent_products();
                if(!empty($products)){
                    return $this->api_out(array("title" => "Recent products","products"=>$products));
                }else{
                    return false;
                }
            }elseif($id=="featured"){
                $products = $this->get_featured_products();
                if(!empty($products)){
                    return $this->api_out(array("title" => "Featured Products","products"=>$products));
                }else{
                    return false;
                }
            }elseif($id=="sale"){
                $products = $this->get_sale_products();
                if(!empty($products)){
                    return $this->api_out(array("title" => "Sale Products","products"=>$products));
                }else{
                    return false;
                }
            }elseif($id=="bestselling"){
                $products = $this->best_selling_products();
                if(!empty($products)){
                    return $this->api_out(array("title" => "Best Selling Products","products"=>$products));
                }else{
                    return false;
                }
            }elseif($id=="toprated"){
                $products = $this->top_rated_products();
                if(!empty($products)){
                    return $this->api_out(array("title" => "Top Rated Products","products"=>$products));
                }else{
                    return false;
                }
            }else{
                return false;
            }
         }
    }
    public function get_recent_products(){
        $atts =  array(
            'per_page' 	=> '12',
            'columns' 	=> '4',
            'orderby' 	=> 'date',
            'order' 	=> 'desc'
        );
        extract($atts);
        $meta_query = WC()->query->get_meta_query();
        $args = array(
            'post_type'				=> 'product',
            'post_status'			=> 'publish',
            'ignore_sticky_posts'	=> 1,
            'posts_per_page' 		=> $per_page,
            'orderby' 				=> $orderby,
            'order' 				=> $order,
            'meta_query' 			=> $meta_query
        );
        $products = $this->getIds($args,$atts);
        return $products;
    }
    public function get_featured_products(){
        $atts = array(
            'per_page' 	=> '12',
            'columns' 	=> '4',
            'orderby' 	=> 'date',
            'order' 	=> 'desc'
        );
        extract($atts);
        $args = array(
            'post_type'				=> 'product',
            'post_status' 			=> 'publish',
            'ignore_sticky_posts'	=> 1,
            'posts_per_page' 		=> $per_page,
            'orderby' 				=> $orderby,
            'order' 				=> $order,
            'meta_query'			=> array(
                array(
                    'key' 		=> '_visibility',
                    'value' 	=> array('catalog', 'visible'),
                    'compare'	=> 'IN'
                ),
                array(
                    'key' 		=> '_featured',
                    'value' 	=> 'yes'
                )
            )
        );

        $products = $this->getIds($args,$atts);
        return $products;
    }
    public function get_sale_products(){
        $atts =  array(
            'per_page'      => '12',
            'columns'       => '4',
            'orderby'       => 'title',
            'order'         => 'asc'
        );
        extract($atts);
        // Get products on sale
        $product_ids_on_sale = wc_get_product_ids_on_sale();

        $meta_query   = array();
        $meta_query[] = WC()->query->visibility_meta_query();
        $meta_query[] = WC()->query->stock_status_meta_query();
        $meta_query   = array_filter( $meta_query );

        $args = array(
            'posts_per_page'	=> $per_page,
            'orderby' 			=> $orderby,
            'order' 			=> $order,
            'no_found_rows' 	=> 1,
            'post_status' 		=> 'publish',
            'post_type' 		=> 'product',
            'meta_query' 		=> $meta_query,
            'post__in'			=> array_merge( array( 0 ), $product_ids_on_sale )
        );
        $products = $this->getIds($args,$atts);
        return $products;
    }
    public function best_selling_products(){
        $atts = array(
            'per_page'      => '12',
            'columns'       => '4'
        );
        extract($atts);
        $args = array(
            'post_type' 			=> 'product',
            'post_status' 			=> 'publish',
            'ignore_sticky_posts'   => 1,
            'posts_per_page'		=> $per_page,
            'meta_key' 		 		=> 'total_sales',
            'orderby' 		 		=> 'meta_value_num',
            'meta_query' 			=> array(
                array(
                    'key' 		=> '_visibility',
                    'value' 	=> array( 'catalog', 'visible' ),
                    'compare' 	=> 'IN'
                )
            )
        );

        $products = $this->getIds($args,$atts);
        return $products;
    }

    public function top_rated_products(){
        $atts =  array(
            'per_page'      => '12',
            'columns'       => '4',
            'orderby'       => 'title',
            'order'         => 'asc'
        );
        extract($atts);
        $args = array(
            'post_type' 			=> 'product',
            'post_status' 			=> 'publish',
            'ignore_sticky_posts'   => 1,
            'orderby' 				=> $orderby,
            'order'					=> $order,
            'posts_per_page' 		=> $per_page,
            'meta_query' 			=> array(
                array(
                    'key' 			=> '_visibility',
                    'value' 		=> array('catalog', 'visible'),
                    'compare' 		=> 'IN'
                )
            )
        );

        $products = $this->getIds($args,$atts);
        return $products;
    }

    private function getIds($args,$atts){
        $products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
        $return = array();
        if(isset($products->posts) && !empty($products->posts)){
            foreach((array) $products->posts as $post) {
                $return[] = $post->ID;
            }
        }
        return $return;
    }
}