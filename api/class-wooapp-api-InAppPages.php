<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class WOOAPP_API_InAppPages  extends WOOAPP_API_Resource {
    /** @var string $base the route base */
    protected $base = '/inAppPages';
    public function register_routes( $routes ) {

        $routes[ $this->base ] = array(
            array( array( $this, 'get_pages' ), WOOAPP_API_Server::READABLE ),
        );
        # GET /coupons/code/<code>, note that coupon codes can contain spaces, dashes and underscores
        $routes[ $this->base . '/page' ] = array(
            array( array( $this, 'get_page_by_name' ), WOOAPP_API_Server::READABLE ),
        );

        return $routes;
    }
    function get_page_by_name($id){
        global $mobappSettings;
        $pages = $this->get_pages();
        $return = array();
        if(isset($pages[$id])){
            $return = $pages[$id];
            $return['items'] = getapi()->WOOAPP_API_Widgets->getWidgetsOfPage($id);
            $return['hash'] = md5(json_encode($return['items']));
        }else{
            $return  = WOOAPP_API_Error::setError($return,"invalid_page","Invalid Page ID");
        }
        return $return;
    }
   public static function  get_pages(){
        $option_name = 'inApp_pages';
        $return_pages["home"]=array("id"=>"home","name"=>"Home");
        $pages = get_option( $option_name );
        if ($pages !== false) {
            $pages = json_decode($pages,true);
            if(is_array($pages)){
                foreach($pages as $id=>$name){
                    $return_pages[$id]=array("id"=>$id,"name"=>$name);
                }
            }
        }
        return $return_pages;
    }
    public function is_readable($cart){

    }
} 