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
if (!class_exists('ReduxFramework_slides_product_scroller')) {
    /**
     * Main ReduxFramework_slides_product_scroller class
     *
     * @since       1.0.0
     */
    class ReduxFramework_slides_product_scroller extends widget_handler{

        var $titles = array();
        var $field_id = "";

        /**
         * Field Constructor.
         *
         * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
         *
         * @since       1.0.0
         * @access      public
         * @param array $field
         * @param string $value
         * @param $parent
         * @return \ReduxFramework_slides_product_scroller
         */
        function __construct( $field = array(), $value ='', $parent ) {
          parent::__construct($field,$value,$parent);
            $this->field_id = $this->field['id'];
            $default_titles = array(
                "add_slider" => "Add Slider",
                "add_slide" => "Add Slide",
                "remove_slider" => "Delete Slider",
                "remove_slide" => "Delete Slide",
                "new_slide"     => "New item",
            );
            if(!isset($this->field['titles']) || !is_array($this->field['titles']))
                $this->field['titles'] = array();
            $this->titles = wp_parse_args($this->field['titles'],$default_titles);
            $this->enqueue();
        }

        public function blank_slider(){
        }

        /**
         * Field Render Function.
         *
         * Takes the vars and outputs the HTML for the field in the settings
         *
         * @since       1.0.0
         * @access      public
         * @param bool $hidden
         * @return      void
         */
         public function render($hidden = false) {
             $this->blank_slider();
         $x = 0;
         if(!isset($this->value['items']) || empty($this->value['items'])){
             $this->value['items'] = array('1'=>array());
         }else if(isset($this->value['items']['_blank']))
            unset($this->value['items']['_blank']);
         $filed_name = $this->field['name'];
             echo '<div class="redux-slideshow-accordion">';
             $this->field['name']=$filed_name.'[slides]';
             if (isset($this->value['slides']) && is_array($this->value['slides']) && !$hidden) {
                $slides = $this->value['slides'];
                foreach ($slides as $slide) {
                    if ( empty( $slide ) ) {
                        continue;
                    }
                    $defaults = array(
                        'title' => '',
                        'click_action' => '',
                        'sort' => '',
                        'products'=>'',
                        'click_action_value' => '',
                        'image' => '',
                        'thumb' => '',
                        'attachment_id' => '',
                        'height' => '',
                        'width' => '',
                        'select' => array(),
                    );
                    $slide = wp_parse_args( $slide, $defaults );

                    echo '<div class="redux-slideshow-accordion-group"><fieldset class="redux-field" data-id="'.$this->field['id'].'"><h3><span class="redux-slideshow-header">' . $slide['title'] . '</span></h3><div>';
                    echo '<ul id="' . $this->field['id'] . '-ul" class="redux-slideshow-list">';
                    $placeholder = (isset($this->field['placeholder']['title'])) ? esc_attr($this->field['placeholder']['title']) : __( 'Title', 'redux-framework' );
                    echo '<li><input type="text" id="' . $this->field['id'] . '-title_' . $x . '" name="' . $this->field['name'] . '[' . $x . '][title]" value="' . esc_attr($slide['title']) . '" placeholder="'.$placeholder.'" class="full-text slide-title" /></li>';
                    echo '<li>';
                        $this->widget_field(widget_handler::$product_selector,false,array("multi"=>true,"sortable"=>true,"width"=>"80","name"=>$this->field['name'] . '[' . $x . '][products]',"id"=>$this->field['id'] . '-' . $x . '-click_action_value','title'=>"Products","class"=>"select2"),$slide['products']);
                    echo '</li>';
                    echo '<li><input type="hidden" class="slide-sort" name="' . $this->field['name'] . '[' . $x . '][sort]" id="' . $this->field['id'] . '-sort_' . $x . '" value="' . $slide['sort'] . '" />';
                    echo '<li><a href="javascript:void(0);" class="button deletion redux-slideshow-remove">' . __($this->titles['remove_slider'], 'redux-framework') . '</a></li>';
                    echo '</ul></div></fieldset></div>';
                    $x++;
                }
            }

            if ($x == 0) {
                echo '<div class="redux-slideshow-accordion-group"><fieldset class="redux-field" data-id="'.$this->field['id'].'"><h3><span class="redux-slideshow-header">'. __($this->titles['new_slide'], 'redux-framework').'</span></h3><div>';
                echo '<ul id="' . $this->field['id'] . '-ul" class="redux-slideshow-list">';
                $placeholder = (isset($this->field['placeholder']['title'])) ? esc_attr($this->field['placeholder']['title']) : __( 'Title', 'redux-framework' );
                echo '<li><input type="text" id="' . $this->field['id'] . '-title_' . $x . '" name="' . $this->field['name'] . '[' . $x . '][title]" value="" placeholder="'.$placeholder.'" class="full-text slide-title" /></li>';
                echo '<li>';
                $this->widget_field(widget_handler::$product_selector,false,array("multi"=>true,"sortable"=>true,"width"=>"80","name"=>$this->field['name'] . '[' . $x . '][products]',"id"=>$this->field['id'] . '-' . $x . '-click_action_value','title'=>"Products","class"=>"select2"));
                echo '</li>';
                echo '<li><input type="hidden" class="slide-sort" name="' . $this->field['name'] . '[' . $x . '][sort]" id="' . $this->field['id'] . '-sort_' . $x . '" value="' . $x . '" />';
                echo '<li><a href="javascript:void(0);" class="button deletion redux-slideshow-remove">'. __($this->titles['remove_slider'], 'redux-framework').'</a></li>';
                echo '</ul></div></fieldset></div>';
            }
             echo '</div><a href="javascript:void(0);" class="button redux-slideshow-add button-primary" rel-id="' . $this->field['id'] . '-ul" rel-name="' . $this->field['name'] . '[title][]">' . __($this->titles['add_slider'], 'redux-framework') . '</a><br/>';
       }

        /**
         * Enqueue Function.
         *
         * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */

        public function enqueue() {
            wp_enqueue_script(
                'redux-field-media-js',
                ReduxFramework::$_url . 'inc/fields/media/field_media.js',
                array( 'jquery' ),
                time(),
                true
            );

            wp_enqueue_style(
                'redux-field-media-css',
                ReduxFramework::$_url . 'inc/fields/media/field_media.css',
                time(),
                true
            );

            wp_enqueue_script(
                'redux-field-slideshow-js',
                plugins_url('field_slides.js',__FILE__),
                array('jquery', 'jquery-ui-core', 'jquery-ui-accordion', 'wp-color-picker'),
                time(),
                true
            );
            wp_enqueue_style(
                'redux-field-slideshow-css',
                plugins_url('field_slides.css',__FILE__),
                time(),
                true
            );
            /*
            wp_enqueue_script(
                'select2-js',
                ReduxFramework::$_url . 'assets/js/vendor/select2/select2.js',
                array(  ),
                time(),
                true
            );
            wp_enqueue_script(
                'select2-sortable-js',
                ReduxFramework::$_url . 'assets/js/vendor/select2.sortable.js',
                array( ),
                time(),
                true
            );
            */

            Redux_CDN::enqueue_script(
                'select2-js',
                '//cdn.jsdelivr.net/select2/3.5.2/select2.min.js',
                array( 'jquery', 'redux-select2-sortable-js' ),
                '3.5.2',
                true
            );

            wp_enqueue_script(
                'field-select-js',
                ReduxFramework::$_url . 'inc/fields/select/field_select.js',
                array( ),
                time(),
                true
            );
           parent::enqueue();
        }
    }
}
