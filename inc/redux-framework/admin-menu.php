<?php

/**
ReduxFramework Sample Config File
For full documentation, please visit: https://docs.reduxframework.com
 * */

if (!class_exists('MobAPP_Admin_Menu_Config')) {

    class MobAPP_Admin_Menu_Config {

        public $args        = array();
        public $sections    = array();
        public $theme;
        public $ReduxFramework;

        public function __construct() {

            if (!class_exists('ReduxFramework')) {
                return;
            }

               // This is needed. Bah WordPress bugs.  ;)
               // if (  true == Redux_Helpers::isTheme(__FILE__) ) {
               //     $this->initSettings();
               // } else {
                add_action('wp_loaded', array($this, 'initSettings'), 10);
            // }

        }

        public function initSettings() {
            // Set the default arguments
            $this->setArguments();

            // Set a few help tabs so you can see how it's done
//              $this->setHelpTabs();

            // Create the sections and fields
            $this->setSections();

            if (!isset($this->args['opt_name'])) { // No errors please
                return;
            }

            // If Redux is running as a plugin, this will remove the demo notice and links
            //add_action( 'redux/loaded', array( $this, 'remove_demo' ) );

            // Function to test the compiler hook and demo CSS output.
            // Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.
            //add_filter('redux/options/'.$this->args['opt_name'].'/compiler', array( $this, 'compiler_action' ), 10, 2);

            // Change the arguments after they've been declared, but before the panel is created
            //add_filter('redux/options/'.$this->args['opt_name'].'/args', array( $this, 'change_arguments' ) );

            // Change the default value of a field after it's been set, but before it's been useds
            //add_filter('redux/options/'.$this->args['opt_name'].'/defaults', array( $this,'change_defaults' ) );

            // Dynamically add a section. Can be also used to modify sections/fields
            //add_filter('redux/options/' . $this->args['opt_name'] . '/sections', array($this, 'dynamic_section'));

            $this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
        }

        /**

        This is a test function that will let you see when the compiler hook occurs.
        It only runs if a field	set with compiler=>true is changed.

         * */
        function compiler_action($options, $css) {
            //echo '<h1>The compiler hook has run!</h1>';
            //print_r($options); //Option values
            //print_r($css); // Compiler selector CSS values  compiler => array( CSS SELECTORS )

            /*
              // Demo of how to use the dynamic CSS and write your own static CSS file
              $filename = dirname(__FILE__) . '/style' . '.css';
              global $wp_filesystem;
              if( empty( $wp_filesystem ) ) {
                require_once( ABSPATH .'/wp-admin/includes/file.php' );
              WP_Filesystem();
              }

              if( $wp_filesystem ) {
                $wp_filesystem->put_contents(
                    $filename,
                    $css,
                    FS_CHMOD_FILE // predefined mode settings for WP files
                );
              }
             */
        }

        /**

        Custom function for filtering the sections array. Good for child themes to override or add to the sections.
        Simply include this function in the child themes functions.php file.

        NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
        so you must use get_template_directory_uri() if you want to use any of the built in icons

         * */
        function dynamic_section($sections) {
            //$sections = array();
            $sections[] = array(
                'title' => __('Section via hook', 'mobapp-settings-page'),
                'desc' => __('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'mobapp-settings-page'),
                'icon' => 'el-icon-paper-clip',
                // Leave this as a blank section, no options just some intro text set above.
                'fields' => array()
            );

            return $sections;
        }

        /**

        Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.

         * */
        function change_arguments($args) {
            //$args['dev_mode'] = true;

            return $args;
        }

        /**

        Filter hook for filtering the default value of any given field. Very useful in development mode.

         * */
        function change_defaults($defaults) {
            $defaults['str_replace'] = 'Testing filter hook!';

            return $defaults;
        }

        // Remove the demo link and the notice of integrated demo from the redux-framework plugin
        function remove_demo() {

            // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
            if (class_exists('ReduxFrameworkPlugin')) {
                remove_filter('plugin_row_meta', array(ReduxFrameworkPlugin::instance(), 'plugin_metalinks'), null, 2);

                // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
                remove_action('admin_notices', array(ReduxFrameworkPlugin::instance(), 'admin_notices'));
            }
        }

        public function setSections() {
            // ACTUAL DECLARATION OF SECTIONS
            $this->sections[] = array(
                'title'     => __('API Settings', 'mobapp-settings-page'),
                'desc'      => __('', 'mobapp-settings-page'),
                'icon'      => 'el-icon-home',
                // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
                'fields'    => array(
                    array(
                        'id'       => 'mobapp-api-key',
                        'type'     => 'text',
                        'title'    => __('API KEY', 'mobapp-settings-page'),
                        'subtitle' => __('Enter the API KEY obtained during creating application ', 'mobapp-settings-page'),
                        'default'  => '',
                        'validate' => 'not_empty'
                    ),
                    array(
                        'id'       => 'mobapp-api-secret',
                        'type'     => 'text',
                        'title'    => __('API SECRET', 'mobapp-settings-page'),
                        'subtitle' =>  __('Enter the API SECRET obtained during creating application', 'mobapp-settings-page'),
                        'default'  => '',
                        'validate' => 'not_empty'
                    ),
                    array(
                        'id'       => 'debug_mode',
                        'type'     => 'switch',
                        'title'    => __('Debug Mode', 'redux-framework-demo'),
                        'subtitle' => __('Enable/Disable Debug mode', 'redux-framework-demo'),
                        'on' => 'Enable',
                        'off'   => 'Disable',
                        'default'  => false,
                )
                ),
            );

            $this->sections[] = array(
                'icon'      => 'el-icon-align-left',
                'title'     => __('Navigation Menu', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'        => 'nav_menu',
                        'type'      => 'nav_menu_builder',
                        'title'     => __('Navigation menu', 'redux-framework-demo'),
                        'doc'  => __('Add items to your navigation menu from the menu items.', 'redux-framework-demo'),
                    )
                )
            );

            $attrs_raw = wc_get_attribute_taxonomy_names();
            foreach($attrs_raw as $attr){
                $args[$attr] = wc_attribute_label($attr);
            }

            $this->sections[] = array(
                'icon'      => 'el-icon-align-left',
                'title'     => __('Products Filter', 'mobapp-settings-page'),
                'fields'    => array(
/*                    array(
                        'id'       => 'product_filter_enable',
                        'type'     => 'switch',
                        'title'    => __('Filter', 'redux-framework-demo'),
                        'subtitle' => __('Enable/Disable filter option on application', 'redux-framework-demo'),
                        'on' => 'Enable',
                        'off'   => 'Disable',
                        'default'  => false,
                    ),
                    array(
                        'id'       => 'price_filter_enable',
                        'type'     => 'switch',
                        'title'    => __('Price Filter', 'redux-framework-demo'),
                        'subtitle' => __('Enable/Disable price slider inside filter', 'redux-framework-demo'),
                        'on' => 'Enable',
                        'off'   => 'Disable',
                        'default'  => true,
                    ), */
                    array(
                        'id'        => 'product_filter_attr',
                        'type'     => 'select',
                        'multi'     => true,
                        'title'    => __('Filter Attributes', 'redux-framework-demo'),
                        'desc'     => __('Select attributes to filter products', 'redux-framework-demo'),
                        'options'   => $args,
                         //'data'      => 'taxonomies',
                         //'args'      => array('object_type'=>array('product')),
                    ),
                )
            );

            $this->sections[] = array(
                'icon'      => 'el-icon-th-list',
                'title'     => __('Widgets', 'mobapp-settings-page'),
            );
            $this->sections[] = array(
                'subsection'   => true,
                'icon'      => ' el-icon-slideshare',
                'title'     => __('Slider Widgets', 'mobapp-settings-page'),
                'heading' => __('Slider Widgets <a href="javascript:void(0);" data-id="slider_widgets" class="button add-slider-add button-primary">Add Slider</a>', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'slider_widgets',
                        'type'        => 'slideshow',
                        'title'       => __('Sliders', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited sliders with drag and drop sortings.', 'redux-framework-demo'),
                    )

                ),
            );
            $this->sections[] = array(
                'icon'      => 'el-icon-th-large',
                'subsection'   => true,
                'title'     => __('Grid Widgets', 'mobapp-settings-page'),
                'heading' => __('Grid Widgets <a href="javascript:void(0);" data-id="grid_widgets" class="button add-slider-add button-primary">New Grid</a>', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'grid_widgets',
                        'type'        => 'slideshow',
                        'title'       => __('Grids', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited grids with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  => "Grid",
                            "add_slider" => "Add Grid",
                            "add_slide" => "Add item",
                            "remove_slider" => "Delete Grid",
                            "remove_slide" => "Delete item",
                            "new_slide"     => "New item"
                        )
                    )
                ),
            );

            $this->sections[] = array(
                'icon'      => 'el-icon-th-large',
                'subsection'   => true,
                'title'     => __('Menu Widgets', 'mobapp-settings-page'),
                'heading' => __('Menu Widgets <a href="javascript:void(0);" data-id="menu_widgets" class="button add-slider-add button-primary">New Menu</a>', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'menu_widgets',
                        'type'        => 'slideshow',
                        'title'       => __('Menus', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited Menus with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  => "Menu",
                            "add_slider" => "Add Menu",
                            "add_slide" => "Add item",
                            "remove_slider" => "Delete Menu",
                            "remove_slide" => "Delete item",
                            "new_slide"     => "New item"
                        )
                    )
                ),
            );

            $this->sections[] = array(
                'icon'      => 'el-icon-text-width',
                'subsection'   => true,
                'title'     => __('Products Scroller', 'mobapp-settings-page'),
                'heading' => __('Product Scroller', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'product_scroller_widgets',
                        'type'        => 'slides_product_scroller',
                        'title'       => __('Products Scroller', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited Products Scroller with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  => "Scroller",
                            "add_slider" => "Add Scroller",
                            "remove_slider" => "Delete Scroller",
                            "new_slide"     => "New item"
                        )
                    )
                ),
            );


            $this->sections[] = array(
                'icon'      => 'el-icon-text-width',
                'subsection'   => true,
                'title'     => __('HTML Widgets', 'mobapp-settings-page'),
                'heading' => __('HTML Widgets', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'html_widgets',
                        'type'        => 'slides_html',
                        'title'       => __('HTML Widgets', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited HTML Widgets with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  => "HTML Widget",
                            "add_slider" => "Add HTML Widget",
                            "remove_slider" => "Delete HTML Widget",
                            "new_slide"     => "New Widget"
                        )
                    )
                ),
            );

            $this->sections[] = array(
                'icon'      => 'el-icon-lines',
                'subsection'   => true,
                'title'     => __('Banner Widgets', 'mobapp-settings-page'),
                'heading' => __('Banner Widgets', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'banner_widgets',
                        'type'        => 'slides_single',
                        'title'       => __('Banner Widgets', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited Banner Widgets with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  =>"Banner",
                            "add_slider" => "Add Banner",
                            "add_slide" => "Add item",
                            "remove_slider" => "Delete Banner",
                            "remove_slide" => "Delete item",
                            "new_slide"     => "New item"
                        )
                    )
                ),
            );

            $this->sections[] = array(
                'icon'      => 'el-icon-search-alt',
                'subsection'   => true,
                'title'     => __('Search Widgets', 'mobapp-settings-page'),
                'heading' => __('Search Widgets', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'          => 'search_widgets',
                        'type'        => 'slides_search',
                        'title'       => __('Search Widgets', 'redux-framework-demo'),
                        'subtitle'    => __('Unlimited Search Widgets with drag and drop sortings.', 'redux-framework-demo'),
                        'titles'    =>  array(
                            "name"  => "Search",
                            "add_slider" => "Add Search Widget",
                            "add_slide" => "Add item",
                            "remove_slider" => "Delete Search Widget",
                            "remove_slide" => "Delete item",
                            "new_slide"     => "New item"
                        )
                    )
                ),
            );
            $this->sections[] = array(
                'icon'      => 'el-icon-website',
                'title'     => __('In-App Pages', 'mobapp-settings-page'),
                'heading' => __('In-App Pages', 'mobapp-settings-page'),
                'fields'    => array(
                ),
            );
            $this->sections[] = array(
                'icon'      => 'el-icon-website',
                'title'     => __('Home Page', 'mobapp-settings-page'),
                'subsection'   => true,
                'heading' => __('Home Page <a id="add-InAPPPageLink1" href="#TB_inline?width=520&height=60&inlineId=add-inPage-box" class="thickbox button button-primary">New In-App page</a>', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'      => 'page_layout_home',
                        'type'    => 'page_builder',
                        'title'   => 'Homepage Layout Manager',
                        'subtitle'     => __('This is your app home page layout. Drag and drop widgets you created to the page layout', 'mobapp-settings-page'),
                        'desc'    => 'Organize how you want the layout to appear on the homepage',
                        'options' => array(
                            'enabled'  => array(
                            ),
                            'disabled' => array(

                            )
                        ),
                    ),
                ),
            );
            $option_name = 'inApp_pages' ;
            $pages = get_option( $option_name );
            if ( $pages !== false ) {
                $pages = json_decode($pages,true);
                if(is_array($pages)){
                    foreach($pages as $id=>$page){
                        $this->sections[] = array(
                            'icon'      => 'el-icon-website',
                            'title'     => __('<span class="page_layout_link_'.$page.'">'.$page.'</span>', 'mobapp-settings-page'),
                            'subsection'   => true,
                            'class' => 'page_layout_class_'.$id,
                            'heading' => __($page.' <a  data-id="'.$id.'" data-field_id="page_layout_'.$id.'"  class="button button-danger remove-InAPPPage">Delete page</a>  <a id="add-InAPPPageLink'.$id.'" href="#TB_inline?width=520&height=60&inlineId=add-inPage-box" class="thickbox button button-primary">New In-App page</a>', 'mobapp-settings-page'),
                            'fields'    => array(
                                array(
                                    'id'      => 'page_layout_'.$id,
                                    'type'    => 'page_builder',
                                    'title'   => $page.' Layout Manager',
                                    'subtitle'=>__('This is your custom page ( '.$page.' ) layout. Drag and drop widgets you created to the page layout'),
                                    'desc'    => 'Organize how you want the layout to appear on the '.$page,
                                    'options' => array(
                                        'enabled'  => array(
                                        ),
                                        'disabled' => array(
                                        )
                                    ),
                                ),
                            ),
                        );
                    }
                }
            }
			// Dummy section to add new In-App page
	            $this->sections[] = array(
	                'icon'      => 'el-icon-plus',
	                'class' => 'add-new-inAppPage',
	                'title'     => __('<span class="add-new-inAppPage-label">Add Page</span>', 'mobapp-settings-page'),
	                'subsection'   => true,
	            );
			$this->sections[] = array(
                'icon'      => 'el-icon-envelope-alt',
                'class' => '',
                'title'     => __('Push Notification', 'mobapp-settings-page'),
                'fields'    => array(
                    array(
                        'id'      => 'gcm_auth_key',
                        'type'    => 'text',
                        'title'   => 'GCM Auth Key',
                        'desc'    => 'Enter your GCM Auth Key',
                    )
                ),
            );
            $this->sections[] = array(
                'icon'      => 'el-icon-plus-sign',
                'class' => '',
                'subsection'   => true,
                'fields'    => array(
			/*
                    array(
                        'id'       => 'opt-push-media',
                        'type'     => 'media',
                        'url'      => true,
                        'height'    => "512",
                        'width'    => "512",
                        "class"     => "wooAppPushNotify_Media",
                        'title'    => __('Icon', 'redux-framework-demo'),
                        'desc'     => __('Basic media uploader with disabled URL input field.', 'redux-framework-demo'),
                        'subtitle' => __('Upload any icon which to use as notification icon , if no icon is uploaded default App icon will be used', 'redux-framework-demo'),
                    ),
			*/
                        array(
                        'id'      => 'push_send_title',
                        'type'    => 'text',
                        "class"     => "wooAppPushNotify_Title",
                        'title'   => 'Title',
                        'subtitle'    => 'Enter push notification title',
                    ),
                    array(
                        'id'      => 'push_send_message',
                        'type'    => 'textarea',
                        "class"     => "wooAppPushNotify_Message",
                        'title'   => 'Message',
                        'subtitle'    => 'Enter push notification message (Short message)',
                    ),
                    array(
                        'id'      => 'action_send_action',
                        'type'    => 'select_action_field',
                        "class"     => "wooAppPushNotify_Action",
                        'title'   => 'Action',
                        'subtitle'    => 'Select push notification on click action',
                    ),
                    array(
                        'id'      => 'push_send_button',
                        "class"     => "wooAppPushNotify_Send",
                        'type'    => 'push_notification_send',
                        'title'   => '',
                        'subtitle'  => 'You have '.WOOAPP_API_Core_pushNotification::getCount().' users for receiving push notification',
                        'desc'    => '',
                    ),
                ),
                'title'     => __('Send Notification', 'mobapp-settings-page'),
            );
            $this->sections[] = array(
                'icon'      => 'el-icon-time',
                'class' => '',
                'subsection'   => true,
                'title'     => __('History', 'mobapp-settings-page'),
                'fields'    =>   array(    array(
                    'id'      => 'push_history_button',
                    "class"     => "wooAppPushNotify_History",
                    'type'    => 'push_notification_history',
                    'title'   => 'Push notification history',
                    'desc'    => '',
                )),
            );
        }
        /**
        All the possible arguments for Redux.
        For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments

         * */
        public function setArguments() {
            $this->args = array(
                // TYPICAL -> Change these values as you need/desire
                'opt_name'          => 'mobappSettings',            // This is where your data is stored in the database and also becomes your global variable name.
                'display_name'      => "WooCommerce Mobile App Manager",     // Name that appears at the top of your panel
                'display_version'   => "1.4.4",  // Version that appears at the top of your panel
                'menu_type'         => 'menu',                  //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
                'allow_sub_menu'    => true,                    // Show the sections below the admin menu item or not
                'menu_title'        => __('Mobile App Manager', 'mobapp-settings-page'),
                'page_title'        => __('WooCommerce Mobile App Manager', 'mobapp-settings-page'),

                // You will need to generate a Google API key to use this feature.
                // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
                'google_api_key' => '', // Must be defined to add google fonts to the typography module
                'async_typography'  => true,                    // Use a asynchronous font on the front end or font string
                'admin_bar'         => false,                    // Show the panel pages on the admin bar
                'global_variable'   => '',                      // Set a different name for your global variable other than the opt_name
              //  'dev_mode'          => true,                    // Show the time the page took to load, etc
                'dev_mode'          => false,                    // Show the time the page took to load, etc
                'customizer'        => false,                    // Enable basic customizer support

                // OPTIONAL -> Give you extra features
                'page_priority'     => null,                    // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
                'page_parent'       => 'plugins.php',            // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
                'page_permissions'  => 'manage_options',        // Permissions needed to access the options panel.
                'menu_icon'         => 'dashicons-smartphone',                      // Specify a custom URL to an icon
                'last_tab'          => '',                      // Force your panel to always open to a specific tab (by id)
                'page_icon'         => 'dashicons-smartphone',           // Icon displayed in the admin panel next to your menu_title
                'page_slug'         => 'woocommerce-mobile-app-manager',              // Page slug used to denote the panel
                'save_defaults'     => true,                    // On load save the defaults to DB before user clicks save or not
                'default_show'      => false,                   // If true, shows the default value next to each field that is not the default value.
                'default_mark'      => '',                      // What to print by the field's title if the value shown is default. Suggested: *
                'show_import_export' => false,                   // Shows the Import/Export panel when not used as a field.

                // CAREFUL -> These options are for advanced use only
                'transient_time'    => 60 * MINUTE_IN_SECONDS,
                'output'            => true,                    // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
                'output_tag'        => true,                    // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
                'footer_credit'     => ' ',                   // Disable the footer credit of Redux. Please leave if you can help it.

                // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
                'database'              => '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
                'system_info'           => false, // REMOVE

                // HINTS
                'hints' => array(
                    'icon'          => 'icon-question-sign',
                    'icon_position' => 'right',
                    'icon_color'    => 'lightgray',
                    'icon_size'     => 'normal',
                    'tip_style'     => array(
                        'color'         => 'light',
                        'shadow'        => true,
                        'rounded'       => false,
                        'style'         => '',
                    ),
                    'tip_position'  => array(
                        'my' => 'top left',
                        'at' => 'bottom right',
                    ),
                    'tip_effect'    => array(
                        'show'          => array(
                            'effect'        => 'slide',
                            'duration'      => '500',
                            'event'         => 'mouseover',
                        ),
                        'hide'      => array(
                            'effect'    => 'slide',
                            'duration'  => '500',
                            'event'     => 'click mouseleave',
                        ),
                    ),
                )
            );


            $this->args['share_icons'][] = array(
                'url'   => 'https://www.facebook.com/appilder',
                'title' => 'Like us on Facebook',
                'icon'  => 'el-icon-facebook'
            );

            // Panel Intro text -> before the form
            if (!isset($this->args['global_variable']) || $this->args['global_variable'] !== false) {
                if (!empty($this->args['global_variable'])) {
                    $v = $this->args['global_variable'];
                } else {
                    $v = str_replace('-', '_', $this->args['opt_name']);
                }
                $this->args['intro_text'] = sprintf(__('<p>For creating  WooCommmerce mobile application  visit : <a target="_blank" href="https://appilder.com/woocommerce/">https://appilder.com/woocommerce/</a> |  <a target="_blank" href="https://appilder.com/woocommerce/docs">Plugin Documentation</a> </p>', 'mobapp-settings-page'), $v);
            } else {
                // $this->args['intro_text'] = __('<p>This text is displayed above the options panel. It isn\'t required, but more info is always better! The intro_text field accepts all HTML.</p>', 'mobapp-settings-page');
            }

            // Add content after the form.
            //  $this->args['footer_text'] = __('<p>This text is displayed below the options panel. It isn\'t required, but more info is always better! The footer_text field accepts all HTML.</p>', 'mobapp-settings-page');
        }

    }
    global $reduxConfig;
    $reduxConfig = new MobAPP_Admin_Menu_Config();
}



