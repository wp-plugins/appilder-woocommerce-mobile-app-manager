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
if (!class_exists('ReduxFramework_slideshow')) {
    /**
     * Main ReduxFramework_slideshow class
     *
     * @since       1.0.0
     */
    class ReduxFramework_slideshow extends widget_handler{

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
         * @return \ReduxFramework_slideshow
         */
        function __construct( $field = array(), $value ='', $parent ) {
          parent::__construct($field,$value,$parent);
            $this->field_id = $this->field['id'];
            $default_titles = array(
                'name'=>"Slider",
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
            echo '<div id="blank_slide_'.$this->field_id.'" style="display:none">';
            $slider = new ReduxFramework_slideshow(
                array('id'=>$this->field['id'].'__blank','name'=>$this->field['name'].'[items][_blank]',
                'titles'=>array(
                    'name'=>"Slider",
                    "add_slider" => "<span class='_addslider'></span>",
                    "add_slide" => "<span class='_addslide'></span>",
                    "remove_slider" => "<span class='_removeslider'></span>",
                    "remove_slide" => "<span class='_removeslide'></span>",
                    "new_slide"=> "<span class='_newslide'></span>",)
                ),
                array(),
                $this->parent);
            $slider->render(true);
            echo "</div>";
            $this->widget_field(self::$product_selector);
            $this->widget_field(self::$category_selector);
            $this->widget_field(self::$url);
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
         if($hidden == false)
         {
             $this->blank_slider();
             if(!isset($this->value['count']) || empty($this->value['count'])) $this->value['count']=1;
             if(!isset($this->value['sliders']) || empty($this->value['sliders'])) $this->value['sliders']="1";
             echo '<input type="hidden" id="sliders_count_'.$this->field_id.'" name="'.$this->field['name'].'[count]" value="'.$this->value['count'].'" />';
             echo '<input type="hidden" id="sliders_ids_'.$this->field_id.'" name="'.$this->field['name'].'[sliders]" value="'.$this->value['sliders'].'" />';
             echo '<input type="hidden" id="sliders_id_'.$this->field_id.'" name="" value="'.$this->field_id.'" />';
             echo '<input type="hidden" id="sliders_name_'.$this->field_id.'" name="" value="'.$this->titles['name'].'" />';

             echo '<input type="hidden" id="sliders_addslider_'.$this->field_id.'" name="" value="'.$this->titles['add_slider'].'" />';
             echo '<input type="hidden" id="sliders_removeslider_'.$this->field_id.'" name="" value="'.$this->titles['remove_slider'].'" />';
             echo '<input type="hidden" id="sliders_addslide_'.$this->field_id.'" name="" value="'.$this->titles['add_slide'].'" />';
             echo '<input type="hidden" id="sliders_newslide_'.$this->field_id.'" name="" value="'.$this->titles['new_slide'].'" />';
             echo '<input type="hidden" id="sliders_removeslide_'.$this->field_id.'" name="" value="'.$this->titles['remove_slide'].'" />';
             echo '</fieldset></td></tr>';
         }
         $x = 0;
         if(!isset($this->value['items']) || empty($this->value['items'])){
             $this->value['items'] = array('1'=>array());
         }else if(isset($this->value['items']['_blank']))
            unset($this->value['items']['_blank']);
         $filed_name = $this->field['name'];
         $id = $this->field['id'];
         if(isset($this->value['items'] )){
             ksort($this->value['items']);
         }

         foreach($this->value['items'] as $key=>$value){
            if(!$hidden)
            {
                if(empty($value['title'])) $value['title'] =  $this->titles['name'].' '.$key;
                echo '<tr valign="top"><th scope="row"><div class="redux_field_th"><span class="slider-title-display">'.esc_attr($value['title']).'</span><span class="description">'.$id.'_'.$key.'</span></div></th><td><fieldset id="mobappSettings-opt-slides_'.$key.'" class="redux-field-container redux-field redux-container-slideshow " data-id="opt-slides_1">';
                $this->field['name']=$filed_name.'[items]['.$key.']';
                $this->field['id']=$id.'_'.$key;
            }else{
                $value['title'] = 'Slider _blank';
            }
             echo '<div>';
             echo '<label style="width:25%" for="' . $this->field['name'] . '[title]">Title</label>';
             echo '<input style="width:70%" type="text" id="' . $this->field['id'] . '-title" name="' . $this->field['name'] . '[title]" value="' . esc_attr($value['title']) . '" placeholder="Title" class="slider-title"  required /></div>';
             echo '<div class="redux-slideshow-accordion">';
             if(!$hidden)
                 $this->field['name']=$filed_name.'[items]['.$key.'][slides]';
             else
                  $this->field['name']=$filed_name.'[slides]';
             if (isset($value['slides']) && is_array($value['slides']) && !$hidden) {
                $slides = $value['slides'];
                foreach ($slides as $slide) {
                    if ( empty( $slide ) ) {
                        continue;
                    }

                    $defaults = array(
                        'title' => '',
                        'click_action' => '',
                        'sort' => '',
                        'click_action_value' => '',
                        'image' => '',
                        'thumb' => '',
                        'attachment_id' => '',
                        'height' => '',
                        'width' => '',
                        'select' => array(),
                    );
                    $slide = wp_parse_args( $slide, $defaults );

                    if ( empty( $slide['thumb'] ) && !empty( $slide['attachment_id'] ) ) {
                        $img = wp_get_attachment_image_src($slide['attachment_id'], 'full');
                        $slide['image'] = $img[0];
                        $slide['width'] = $img[1];
                        $slide['height'] = $img[2];
                    }

                    echo '<div class="redux-slideshow-accordion-group"><fieldset class="redux-field" data-id="'.$this->field['id'].'"><h3><span class="redux-slideshow-header">' . $slide['title'] . '</span></h3><div>';

                    $hide = '';
                    if ( empty( $slide['image'] ) ) {
                        $hide = ' hide';
                    }


                    echo '<div class="screenshot' . $hide . '">';
                    echo '<a class="of-uploaded-image" href="' . $slide['image'] . '">';
                    echo '<img class="redux-slideshow-image" id="image_image_id_' . $x . '" src="' . $slide['thumb'] . '" alt="" target="_blank" rel="external" />';
                    echo '</a>';
                    echo '</div>';

                    echo '<div class="redux_slideshow_add_remove">';

                    echo '<span class="button media_upload_button" id="add_' . $x . '">' . __('Upload', 'redux-framework') . '</span>';

                    $hide = '';
                    if ( empty( $slide['image'] ) || $slide['image'] == '' ) {
                        $hide = ' hide';
                    }

                    echo '<span class="button remove-image' . $hide . '" id="reset_' . $x . '" rel="' . $slide['attachment_id'] . '">' . __('Remove', 'redux-framework') . '</span>';

                    echo '</div>' . "\n";

                    echo '<ul id="' . $this->field['id'] . '-ul" class="redux-slideshow-list">';
                    $placeholder = (isset($this->field['placeholder']['title'])) ? esc_attr($this->field['placeholder']['title']) : __( 'Title', 'redux-framework' );
                    echo '<li><input type="text" id="' . $this->field['id'] . '-title_' . $x . '" name="' . $this->field['name'] . '[' . $x . '][title]" value="' . esc_attr($slide['title']) . '" placeholder="'.$placeholder.'" class="full-text slide-title" /></li>';

                    $placeholder = (isset($this->field['placeholder']['click_action'])) ? esc_attr($this->field['placeholder']['click_action']) : __( 'click_action', 'redux-framework' );
                    echo '<li><select class="widget-action-selector" name="' . $this->field['name'] . '[' . $x . '][click_action]" id="' . $this->field['id'] . '-click_action_' . $x . '" placeholder="'.$placeholder.'">';
                    echo '<option value=""> Select Action </option>';
                    echo '<option value="open_product" '.(($slide['click_action']=='open_product')?'selected="selected"':'').'> Open Product </option>';
                    echo '<option value="open_category" '.(($slide['click_action']=='open_category')?'selected="selected"':'').'> Open Category </option>';
                    echo '<option value="open_page" '.(($slide['click_action']=='open_page')?'selected="selected"':'').'> Open Page </option>';
                    echo '<option value="go_to_url" '.(($slide['click_action']=='go_to_url')?'selected="selected"':'').'> Go to URL </option>';
                    echo '</select></li>';

                    echo '<li style="display:none" class="widget-action-value-field" data-name="' . $this->field['name'] . '[' . $x . '][click_action_value]" data-value="'.$slide['click_action_value'].'" data-id="' . $this->field['name'] . '-' . $x . '-click_action_value"></li>';

                    echo '<li><input type="hidden" class="slide-sort" name="' . $this->field['name'] . '[' . $x . '][sort]" id="' . $this->field['id'] . '-sort_' . $x . '" value="' . $slide['sort'] . '" />';
                    echo '<li><input type="hidden" class="upload-id" name="' . $this->field['name'] . '[' . $x . '][attachment_id]" id="' . $this->field['id'] . '-image_id_' . $x . '" value="' . $slide['attachment_id'] . '" />';
                    echo '<input type="hidden" class="upload-thumbnail" name="' . $this->field['name'] . '[' . $x . '][thumb]" id="' . $this->field['id'] . '-thumb_url_' . $x . '" value="' . $slide['thumb'] . '" readonly="readonly" />';
                    echo '<input type="hidden" class="upload" name="' . $this->field['name'] . '[' . $x . '][image]" id="' . $this->field['id'] . '-image_url_' . $x . '" value="' . $slide['image'] . '" readonly="readonly" />';
                    echo '<input type="hidden" class="upload-height" name="' . $this->field['name'] . '[' . $x . '][height]" id="' . $this->field['id'] . '-image_height_' . $x . '" value="' . $slide['height'] . '" />';
                    echo '<input type="hidden" class="upload-width" name="' . $this->field['name'] . '[' . $x . '][width]" id="' . $this->field['id'] . '-image_width_' . $x . '" value="' . $slide['width'] . '" /></li>';
                    echo '<li><a href="javascript:void(0);" class="button deletion redux-slideshow-remove">' . __($this->titles['remove_slide'], 'redux-framework') . '</a></li>';
                    echo '</ul></div></fieldset></div>';
                        $x++;
                }
            }

            if ($x == 0) {
                echo '<div class="redux-slideshow-accordion-group"><fieldset class="redux-field" data-id="'.$this->field['id'].'"><h3><span class="redux-slideshow-header">'. __($this->titles['new_slide'], 'redux-framework').'</span></h3><div>';

                $hide = ' hide';

                echo '<div class="screenshot' . $hide . '">';
                echo '<a class="of-uploaded-image" href="">';
                echo '<img class="redux-slideshow-image" id="image_image_id_' . $x . '" src="" alt="" target="_blank" rel="external" />';
                echo '</a>';
                echo '</div>';

                //Upload controls DIV
                echo '<div class="upload_button_div">';

                //If the user has WP3.5+ show upload/remove button
                echo '<span class="button media_upload_button" id="add_' . $x . '">' . __('Upload image', 'redux-framework') . '</span>';

                echo '<span class="button remove-image' . $hide . '" id="reset_' . $x . '" rel="' . $this->parent->args['opt_name'] . '[' . $this->field['id'] . '][attachment_id]">' . __('Remove', 'redux-framework') . '</span>';

                echo '</div>' . "\n";

                echo '<ul id="' . $this->field['id'] . '-ul" class="redux-slideshow-list">';
                $placeholder = (isset($this->field['placeholder']['title'])) ? esc_attr($this->field['placeholder']['title']) : __( 'Title', 'redux-framework' );

                echo '<li><input type="text" id="' . $this->field['id'] . '-title_' . $x . '" name="' . $this->field['name'] . '[' . $x . '][title]" value="" placeholder="'.$placeholder.'" class="full-text slide-title" /></li>';

                $placeholder = (isset($this->field['placeholder']['click_action'])) ? esc_attr($this->field['placeholder']['click_action']) : __( 'click_action', 'redux-framework' );
                echo '<li><select class="widget-action-selector" name="' . $this->field['name'] . '[' . $x . '][click_action]" id="' . $this->field['id'] . '-click_action_' . $x . '" placeholder="'.$placeholder.'">';
                echo '<option value=""> Select Action </option>';
                echo '<option value="open_product" > Open product </option>';
                echo '<option value="open_category" > Open category </option>';
                echo '<option value="open_page"> Open page </option>';
                echo '<option  value="go_to_url"> Go to URL </option>';
                echo '</select></li>';
                echo '<li style="display:none" class="widget-action-value-field" data-name="' . $this->field['name'] . '[' . $x . '][click_action_value]" data-value="" data-id="' . $this->field['name'] . '-' . $x . '-click_action_value"></li>';
                echo '<li><input type="hidden" class="slide-sort" name="' . $this->field['name'] . '[' . $x . '][sort]" id="' . $this->field['id'] . '-sort_' . $x . '" value="' . $x . '" />';
                echo '<li><input type="hidden" class="upload-id" name="' . $this->field['name'] . '[' . $x . '][attachment_id]" id="' . $this->field['id'] . '-image_id_' . $x . '" value="" />';
                echo '<input type="hidden" class="upload" name="' . $this->field['name'] . '[' . $x . '][image]" id="' . $this->field['id'] . '-image_url_' . $x . '" value="" readonly="readonly" />';
                echo '<input type="hidden" class="upload-height" name="' . $this->field['name'] . '[' . $x . '][height]" id="' . $this->field['id'] . '-image_height_' . $x . '" value="" />';
                echo '<input type="hidden" class="upload-width" name="' . $this->field['name'] . '[' . $x . '][width]" id="' . $this->field['id'] . '-image_width_' . $x . '" value="" /></li>';
                echo '<input type="hidden" class="upload-thumbnail" name="' . $this->field['name'] . '[' . $x . '][thumb]" id="' . $this->field['id'] . '-thumb_url_' . $x . '" value="" /></li>';
                echo '<li><a href="javascript:void(0);" class="button deletion redux-slideshow-remove">'. __($this->titles['remove_slide'], 'redux-framework').'</a></li>';
                echo '</ul></div></fieldset></div>';
            }
             $id_for_action = ($hidden)?trim($this->field_id,'__blank'):$this->field_id;
             echo '</div><a href="javascript:void(0);" class="button redux-slideshow-add button-primary" rel-id="' . $this->field['id'] . '-ul" rel-name="' . $this->field['name'] . '[title][]">' . __($this->titles['add_slide'], 'redux-framework') . '</a><br/>
             <a href="javascript:void(0);" id="remove-slider-'.$this->field['name'].'" data-id="' . $id_for_action . '" class="button remove-slider-remove deletion" style="color:#ef521d !important" rel-id="' . $this->field['id'] . '-ul" >' . __($this->titles['remove_slider'], 'redux-framework') . '</a>
             <a href="javascript:void(0);" class="button add-slider-add button-primary" data-id="' . $id_for_action . '">'.__($this->titles['add_slider'], 'redux-framework').'</a><br/>';

             if(!$hidden)
             {
                 echo '</fieldset></td></tr>';
             }
         }

             if(!$hidden)
             {
                 echo "<tr style='display: none'><td colspan='2'><fieldset>";
             }
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
                ReduxFramework::$_url . 'assets/js/media/media.min.js',
                array( 'jquery', 'redux-js' ),
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
           parent::enqueue();
        }
    }
}
