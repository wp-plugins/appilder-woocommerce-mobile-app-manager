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
if (!class_exists('ReduxFramework_select_action_field')) {
    /**
     * Main ReduxFramework_slideshow class
     *
     * @since       1.0.0
     */
    class ReduxFramework_select_action_field extends widget_handler{
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
         * @return \ReduxFramework_select_action_field
         */
         function __construct( $field = array(), $value ='', $parent ) {
             $this->parent = $parent;
             $this->field = $field;
             $this->value = $value;
         }

        /**
         * Field Render Function.
         *
         * Takes the vars and outputs the HTML for the field in the settings
         *
         * @since       1.0.0
         * @access      public
         * @internal param bool $hidden
         * @return      void
         */
         public function render() {
             $this->widget_field(widget_handler::$inApp_selector,true,array("multi"=>false,"sortable"=>false,"width"=>"80",'title'=>"Products","class"=>"select2"));
             $class = isset($this->field['class'])?$this->field['class']:'';
             echo '<ul class="'.$class.'"><li>';
             echo '<select class="widget-action-selector" name="' . $this->field['name'] .'[click_action]" id="' . $this->field['id'] . '-click_action_" placeholder="">';
             echo '<option value=""> Select Action </option>';
             echo '<option value="open_product" '.(($this->value['click_action']=='open_product')?'selected="selected"':'').'> Open Product </option>';
             echo '<option value="open_category" '.(($this->value['click_action']=='open_category')?'selected="selected"':'').'> Open Category </option>';
             echo '<option value="open_page" '.(($this->value['click_action']=='open_page')?'selected="selected"':'').'> Open Page </option>';
             echo '<option value="go_to_url" '.(($this->value['click_action']=='go_to_url')?'selected="selected"':'').'> Go to URL </option>';
             echo '</select>';
             echo '</li>';
             $value = isset($this->value['click_action_value'])?$this->value['click_action_value']:'';
             echo '<br><li style="display:none" class="widget-action-value-field" data-name="' . $this->field['name'] . '[click_action_value]" data-value="'.$value.'" data-id="' . $this->field['name'] . '-click_action_value"></li></ul>';
         }
         public function enqueue() {
           /*  wp_enqueue_script(
                 'push-notif-send-js',
                 plugins_url('push_notification_send.js',__FILE__),
                 array('jquery'),
                 time(),
                 true
             );
           */
         }

    }
}
