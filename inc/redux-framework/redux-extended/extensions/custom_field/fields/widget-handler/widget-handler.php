<?php
/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     ReduxFramework
 * @subpackage  Field_slides
 * @author      Luciano "WebCaos" Ubertini
 * @author      Daniel J Griffiths (Ghost1227)
 * @author      Dovy Paukstys
 * @version     3.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
// Don't duplicate me!
if (!class_exists('widget_handler')) {
    class widget_handler {
        static $product_selector = "product_selector_field";
        static  $category_selector = "category_selector_field";
        static  $inApp_selector = "inAppPages_selector_field";
        static  $url ="url_field" ;
        static $widgets = array('slider_widgets' => "Slider Widgets",
            'grid_widgets' => "Grid widgets",
            'product_scroller_widgets'=>"Product scroller widgets",
            'banner_widgets'=>"Banner widgets",
            'search_widgets' => "Search widgets",
            'html_widgets' => "HTML widgets",
            'menu_widgets' => "Menu widgets",
        );
        function __construct( $field = array(), $value ='', $parent ) {
            $this->parent = $parent;
            $this->field = $field;
            $this->value = $value;
            require_once(wooapp_path('inc/redux-framework/ReduxCore/inc/fields/select/field_select.php'));
        }
        public function enqueue() {
            wp_enqueue_script(
                'redux-field-widget-handler-js',
                plugins_url('widget-handler.js',__FILE__),
                array('jquery', 'jquery-ui-core', 'jquery-ui-accordion', 'wp-color-picker'),
                time(),
                true
            );
            wp_enqueue_style(
                'redux-field-select2-css',
                ReduxFramework::$_url . 'assets/js/vendor/select2/select2.css',
                time(),
                true
            );
        }
        public static function single_display($id,$callback){
            if((!isset($GLOBALS[$id]) || !$GLOBALS[$id])){
                $GLOBALS[$id] = true;
                call_user_func($callback);
            }
        }

        public  function widget_field($field_name,$hidden=true,$field=array(),$value=array()){
            $index = "widget_".$field_name;
            if(isset($field['multi']) && $field['multi']) $index.="_multi";
            $style = $hidden?"display:none;":"";
            if((!isset($GLOBALS[$index])  ||  !$GLOBALS[$index]) || !$hidden){
                if($hidden) $GLOBALS[$index] = true;
                if($hidden) echo '<div id="'.$index.'" style="'.$style.'">';
                if(!isset($field['class']))
                    $field['class'] = '';
                else
                    $field['class'] = $field['class'].' widget-field';
                call_user_func(array($this,$field_name),$field,$value);
                if($hidden) echo '</div>';
            }
        }

        private function widget_filed_args($field_defaults,$field_args){
            $field_defaults = array_merge(array('id'=>'__toReplace',
                'name_suffix' =>'',
                'name'=>'__toReplace',
                'class' =>'widget-field',
                'title' => '',
                'subtitle' => '',
            ),$field_defaults);
            return wp_parse_args($field_args,$field_defaults);
        }

        public function product_selector_field($field,$value){
                $default = array(
                    'type' => 'select',
                    'data' => 'posts',
                    'args' => array('post_type' => array('product'),'posts_per_page' => -1),
                );
                $field =  $this->widget_filed_args($default,$field);
                $widget = new ReduxFramework_select($field,$value,$this->parent);
                $widget->render();
        }

        public function inAppPages_selector_field($field,$value){
            $option_name = 'inApp_pages' ;
            $pages = get_option( $option_name );
            if ( $pages !== false ) {
	            $pages = json_decode( $pages, true );
	            if ( is_array( $pages ) ) {
		            $pages = array_merge( array( "home" => "Home Page" ), $pages );
	            } else {
		            $pages = array( "home" => "Home Page" );
	            }
            }else {
	            $pages = array( "home" => "Home Page" );
            }
                    $class = isset($field['class'])?$field['class']:'';
                    $multi = (isset($field['multi']) && $field['multi'])?'multiple="multiple"':'';
                    echo "<select class='$class' $multi data-placeholder='Select a page' name='__toReplace' id='__toReplace'>";
                    echo "<option></option>";
                    foreach($pages as $id=>$page){
	                        if(isset($value) && $id==$value) $selected = "selected='selected'"; else $selected = "";
                            echo "<option value='$id' $selected>$page</option>";
                     }
                    echo "</select>";
        }
        public function category_selector_field($field,$value){
            $default = array(
                'type' => 'select',
                'data' => 'categories',
                'args' => array('taxonomy' => array('product_cat')),
            );
            $field =  $this->widget_filed_args($default,$field);
            $widget = new ReduxFramework_select($field,$value,$this->parent);
            $widget->render();
        }
        public function url_field($field,$value=""){
                $class = isset($this->field['class'])?$this->field['class']:'';
                echo '<input class="widget-action-value-url full-text '.$class.'" type="text" id="__toReplace" name="__toReplace" placeholder="Enter URL" />';
        }

        public static  function filter_widget_values($widget_values){
            if(isset($widget_values['items']['_blank'])) unset($widget_values['items']['_blank']);
            return $widget_values;
        }
        public static function widget_title($widget){
            return isset(self::$widgets[$widget])?self::$widgets[$widget]:'';
        }
        public static function  system_product_scrollers(){
            $return = array();
            $return =  Array(
               "recent" => Array ( "title" => "Recent Products Scroller"),
                "featured" =>    Array ( "title" => "Featured products Scroller"),
                "sale" =>    Array ( "title" => "Sale Products Scroller"),
                "bestselling" =>    Array ( "title" => "Best Selling Products Scroller"),
                "toprated" =>    Array ( "title" => "Top Rated Products Scroller"),
            );
            return $return;
        }
        public static function get_widgets($widget = null){
            if($widget!=null && !isset(self::$widgets[$widget])) return false;
            $return = array();
            global $mobappSettings;
            if($widget == null){
                    foreach(self::$widgets as $widget=>$title){
                        if(isset($mobappSettings[$widget]))
                           $return = array_merge($return,array($widget=>self::filter_widget_values($mobappSettings[$widget])));
                        if($widget=="product_scroller_widgets"){
                            if(!isset($return[$widget]["slides"]) || empty($return[$widget]["slides"]))
                                $return[$widget]["slides"] = array();
                            $return[$widget]["slides"] = array_merge($return[$widget]["slides"],self::system_product_scrollers());
                        }
                    }
            }else{
                $return = isset($mobappSettings[$widget])?$mobappSettings[$widget]:array();
            }
            return $return;
        }
    }
}
