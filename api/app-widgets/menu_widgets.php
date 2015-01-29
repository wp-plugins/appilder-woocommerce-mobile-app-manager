<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once("interface-widget.php");

class Menu_Widgets extends  WOOAPP_Widget_Handler{
    public function __construct(){
        global $mobappSettings;
        $this->type = "menu_widgets";
        $this->id = 7;
        $this->values = array();
        if(isset($mobappSettings[$this->type]['items'])){
            $this->values = $mobappSettings[$this->type]['items'];
            if(isset($this->values['_blank']))
                unset($this->values['_blank']);
        }
    }
}