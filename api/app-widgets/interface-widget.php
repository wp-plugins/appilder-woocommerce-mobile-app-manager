<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once("interface-widget.php");
abstract class WOOAPP_Widget_Handler {
    var $values;
    var $type;
    var $id;
    public function getValues(){
        return $this->values;
    }
    public function getValueById($id){
        return isset($this->values[$id])?$this->api_out($this->values[$id]):false;
    }
    public function count(){
        return is_array($this->values)?count($this->values):false;
    }
    public function api_out($value){
        $return = array();
        $return['type'] = $this->id;
        $return['title'] = $value['title'];
        if(isset($value['slides']) && is_array($value['slides'])){
            foreach($value['slides'] as $item){
                $return['items'][]=$this->itemValue($item);
            }
        }elseif($this->id==5 && isset($value['products'])){
            $return['items']=$this->fetchProductDetails($value);
        }elseif($this->id==6 && isset($value['content'])){
            $return['items'][]=$this->html_Field($value);
        }else{
            if($this->id==1)
                $value['click_action'] = 'search_cat';
            $return['items'][]=$this->itemValue($value);
        }
        return $return;
    }
    public function  html_Field($item){
        $return=array(
            "action" => array(
                "tagetType" => "html",
            ),
            "value" => array(
                "title" => isset($item['title'])?$item['title']:"",
                "content"=>do_shortcode($item['content'])
            )
        );
        return $return;
    }
    public function itemValue($item){

        $return=array(
            "action" => array(
                "tagetType" => isset($item['click_action'])?$item['click_action']:"",
            ),
            "value" => array(
                "title" => isset($item['title'])?$item['title']:"",
                "url" => isset($item['image'])?$item['image']:""
            )
        );
        if(isset($item['click_action_value']) && is_array($item['click_action_value'])) {
            $return['action']['extraParam'] = implode(",", $item['click_action_value']);
        }elseif($return['action']["tagetType"]=='open_category'){
            $return['action']['extraParams'] = $this->fetchCatDetails($item['click_action_value']);
        }elseif(isset($item['click_action_value']))
            $return['action']['extraParam']=$item['click_action_value'];
        else{
            $return['action']['extraParam']= "";
            $return['action']['extraParams'] =array();
        }
        return $return;
    }
    public function fetchCatDetails($cat){
            $catTerms = get_term($cat,"product_cat");
            $return=array((String)$catTerms->term_id,$catTerms->name);
        return $return;
    }
    public function fetchProductDetails($value){
        $products = $value['products'];
        $return = array();
        $i=-1;
        foreach($products as $product_id){
           $product =  get_product($product_id);
            if($product!==false){
                $return[++$i]['action']['tagetType'] = 'open_product';
                $return[$i]['action']['extraParam'] = $product_id;
                $return[$i]['value']=  getapi()->WOOAPP_API_Products->get_product_data($product,true);
            }
        }
        return $return;
    }
}