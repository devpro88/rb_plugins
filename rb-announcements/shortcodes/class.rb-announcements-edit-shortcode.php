<?php 

if( ! class_exists('RB_Announcements_Edit_Shortcode')){
    class RB_Announcements_Edit_Shortcode{
        public function __construct(){
            add_shortcode( 'rb_announcements_edit', array( $this, 'add_shortcode' ) );
        }

        public function add_shortcode(){
            
            ob_start();
            require( RB_ANNOUNCEMENTS_PATH . 'views/rb-announcements_edit_shortcode.php' );
            wp_enqueue_script( 'custom_js' );
            wp_enqueue_script( 'validate_js' );
            return ob_get_clean();
        }
    }
}
