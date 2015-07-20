<?php
if (!defined('ABSPATH')) exit;
/**
 * Calls the class on the post edit screen.
 */
function call_wooappMetaBoxes() {
    new wooappMetaBoxes();
}

if ( is_admin() ) {
    add_action( 'load-post.php', 'call_wooappMetaBoxes' );
    add_action( 'load-post-new.php', 'call_wooappMetaBoxes' );
}

/**
 * The Class.
 */
class wooappMetaBoxes {

    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post', array( $this, 'save' ) );
    }

    /**
     * Adds the meta box container.
     */
    public function add_meta_box( $post_type ) {
        $post_types = array('post', 'product');     //limit meta box to certain post types
        if ( in_array( $post_type, $post_types )) {
            add_meta_box(
                'wooapp_product_desc'
                ,__( 'Description On Mobile App', 'wppapp' )
                ,array( $this, 'render_meta_box_content' )
                ,$post_type
                ,'advanced'
                ,'core'
            );
        }
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save( $post_id ) {

        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */

        // Check if our nonce is set.
        if ( ! isset( $_POST['wooapp_inner_custom_box_nonce'] ) )
            return $post_id;

        $nonce = $_POST['wooapp_inner_custom_box_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'wooapp_inner_custom_box' ) )
            return $post_id;

        // If this is an autosave, our form has not been submitted,
        //     so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;

        } else {

            if ( ! current_user_can( 'edit_post', $post_id ) )
                return $post_id;
        }

        /* OK, its safe for us to save the data now. */
        $allowed_html = array('strong'=>array(),'em'=>array(),'b'=>array(),'i'=>array(),'a'=>array(),'li'=>array(),'ol'=>array(),'ul'=>array(),'br'=>array(),'div'=>array());
        // Sanitize the user input.
        $mydata = wp_kses( $_POST['wooapp_product_desc'],$allowed_html );

        // Update the meta field.
        update_post_meta( $post_id, 'wooapp_product_desc', $mydata );
    }


    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'wooapp_inner_custom_box', 'wooapp_inner_custom_box_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        $value = get_post_meta( $post->ID, 'wooapp_product_desc', true );

        $settings = array(
            'textarea_name' => 'wooapp_product_desc',
            'media_buttons' => false,
            'quicktags'     => array( 'buttons' => 'em,strong,link' ),
            'tinymce'       => array(
                'toolbar1' => 'bold,italic,strikethrough,bullist,numlist,link,unlink,separator,undo,redo,separator',
                'theme_advanced_buttons2' => '',
            ),
            'editor_css'    => '<style>#wp-wooapp_product_desc_field-editor-container .wp-editor-area{height:175px; width:100%;}</style>'
        );
        wp_editor(  $value , 'wooapp_product_desc_field',$settings);

    }
}
