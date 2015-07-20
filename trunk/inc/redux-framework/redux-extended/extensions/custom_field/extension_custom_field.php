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
 * @author      Dovy Paukstys (dovy)
 * @version     3.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Don't duplicate me!
if( !class_exists( 'ReduxFramework_extension_custom_field' ) ) {

    /**
     * Main ReduxFramework custom_field extension class
     *
     * @since       3.1.6
     */

    class ReduxFramework_extension_custom_field extends ReduxFramework {
        // Protected vars
        protected $parent;
        public $extension_url;
        public $extension_dir;
        private $custom_fields;
        public static $theInstance;

        /**
         * Class Constructor. Defines the args for the extions class
         *
         * @since       1.0.0
         * @access      public
         * @param array $parent
         * @internal param array $sections Panel sections.
         * @internal param array $args Class constructor arguments.
         * @internal param array $extra_tabs Extra panel tabs.
         * @return \ReduxFramework_extension_custom_field
         */
        public function __construct( $parent ) {

            $this->parent = $parent;
            if ( empty( $this->extension_dir ) ) {
                $this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
            }
            require_once("fields/widget-handler/widget-handler.php");
            $this->custom_fields = array(
                "cat_sort"=>"cat_sort_field",
                "nav_menu_builder"=>"nav-menu-builder",
                "page_builder"=>"page-builder",
                "slides_product_scroller"=>"slideshow-field",
                "slides_html"=>"slideshow-field",
                "slides_search"=>"slideshow-field",
                "slides_single"=>"slideshow-field",
                "slideshow"=>"slideshow-field",
                "push_notification_send"=>"push-notification-send",
                "push_notification_history"=>"push-notification-history",
                "select_action_field" => "select-action-field"
                 //                "sortable" => "sortable-field",
            );
            self::$theInstance = $this;
            foreach($this->custom_fields as $field=>$folder) {
                    add_filter('redux/' . $this->parent->args['opt_name'] . '/field/class/' . $field, array(&$this, 'overload_field_path')); // Adds the local field
            }
        }

        public function getInstance() {
            return self::$theInstance;
        }
        public function get_name(){

        }
        // Forces the use of the embedded field path vs what the core typically would use
        public function overload_field_path($field) {
            $field_name = basename($field,".php");
            $field_name = ltrim($field_name,"field_");
            if(isset($this->custom_fields[$field_name])) {
                return $this->extension_dir . '/fields/' . $this->custom_fields[$field_name] . '/' . $field_name . '.php';
            }else {
               return false;
            }
        }

    } // class
} // if
