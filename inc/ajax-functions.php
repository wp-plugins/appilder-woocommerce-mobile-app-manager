<?php
/**
 * @Author Mohammed Anees
 */
add_action( 'wp_ajax_add_inApp_page_action', 'add_inApp_page_action_callback' );
add_action( 'wp_ajax_remove_inApp_page_action', 'remove_inApp_page_action_callback' );
function add_inApp_page_action_callback() {
    $title = $_POST['title'];
    $response = array('status'=>false,"error"=>"","new_id"=>"");
    if(!empty($title)){
        $id=sanitize_title($title);
        $option_name = 'inApp_pages' ;
        $pages = get_option( $option_name );
        if ( $pages !== false ) {
            $pages = json_decode($pages,true);
            if(!is_array($pages))
                $pages=array();
            if(!isset($pages[$id])){
                $pages[$id]=$title;
                $new_value = json_encode($pages);
                update_option( $option_name, $new_value );
                $response['status'] = true;
                $response['new_id'] = $id;
            }else{
                $response['error'] = "Name already exists";
            }
        } else {
            $deprecated = null;
            $autoload = 'no';
            $pages=array();
            $pages[$id]=$title;
            $new_value = json_encode($pages);
            add_option( $option_name, $new_value, $deprecated, $autoload );
            $response['status'] = true;
            $response['new_id'] = $id;
        }
    }else{
        $response['error'] = "Title is empty";
    }
    echo json_encode($response);
    die(); // this is required to return a proper result
}
function remove_inApp_page_action_callback() {
    $id = $_POST['id'];
    $response = array('status'=>false,"error"=>"","new_id"=>"");
    if(!empty($id)){
        $option_name = 'inApp_pages' ;
        $pages = get_option( $option_name );
        if ( $pages !== false ) {
            $pages = json_decode($pages,true);
            if(isset($pages[$id])){
                unset($pages[$id]);
                $new_value = json_encode($pages);
                update_option( $option_name, $new_value );
                $response['status'] = true;
                $response['deleted_id'] = $id;
            }else{
                $response['error'] = "Page already deleted";
            }
        } else {
            $response['error'] = "Page already deleted";
        }
    }else{
        $response['error'] = "Unable to proccess your request";
    }
    echo json_encode($response);
    die(); // this is required to return a proper result
}