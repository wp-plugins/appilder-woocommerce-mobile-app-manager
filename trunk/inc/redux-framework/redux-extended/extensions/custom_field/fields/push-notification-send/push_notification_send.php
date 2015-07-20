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
if (!class_exists('ReduxFramework_push_notification_send')) {
    /**
     * Main ReduxFramework_slideshow class
     *
     * @since       1.0.0
     */
    class ReduxFramework_push_notification_send {
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
         * @return \ReduxFramework_push_notification_send
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
             echo "<div style='text-align: right'>";
             echo '<li style="display:none" class="widget-action-value-field" data-name="noti[1][click_action_value][]" data-value="" data-id="noti-click_action_value"></li>';
             echo "<button id='sendPushNotification' class='button button-primary' style='    width: 33%;    font-size: large;    height: 50px;    font-weight: bold;'>Send Notification</button>";
             echo "</div>"."<br />";
             echo '<div class="send-success-notification notice-green" style="left: 183px;display:none"></div>';
         }
         public function enqueue() {
             wp_enqueue_script(
                 'push-notif-send-js',
                 plugins_url('push_notification_send.js',__FILE__),
                 array('jquery'),
                 time(),
                 true
             );
         }

    }
}
