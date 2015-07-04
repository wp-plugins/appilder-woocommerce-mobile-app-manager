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
if (!class_exists('ReduxFramework_push_notification_history')) {
    /**
     * Main ReduxFramework_slideshow class
     *
     * @since       1.0.0
     */
    class ReduxFramework_push_notification_history {
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
         * @return \ReduxFramework_push_notification_history
         */
         function __construct( $field = array(), $value ='', $parent ) {
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
                WOOAPP_API_Core_pushNotification::action_getHistory();
         }
         public function enqueue() {
             wp_enqueue_script(
                 'push-notif-his-js',
                 plugins_url('push_notification_history.js',__FILE__),
                 array('jquery'),
                 time(),
                 true
             );
         }

    }
}
