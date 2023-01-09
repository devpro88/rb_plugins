<?php

/**
 * Plugin Name: RB Slider
 * Plugin URI: https://www.wordpress.org/rb-slider
 * Description: Use the shortcode [rb_slider] to display the slider in any page/post/widget
 * Version: 1.0
 * Requires at least: 5.6
 * Author: Ruslan Bondar
 * Author URI: https://www.codigowp.net
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rb-slider
 * Domain Path: /languages
 */

 /*
RB Slider is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
RB Slider is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with RB Slider. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if( ! defined( 'ABSPATH') ){
    exit;
}

if( ! class_exists( 'RB_Slider' ) ){
    class RB_Slider{
        function __construct(){
            $this->define_constants();

            $this->load_textdomain();

            require_once( RB_SLIDER_PATH . 'functions/functions.php' );

            add_action( 'admin_menu', array( $this, 'add_menu' ) );

            require_once( RB_SLIDER_PATH . 'post-types/class.rb-slider-cpt.php' );
            $RB_Slider_Post_Type = new RB_Slider_Post_Type();

            require_once( RB_SLIDER_PATH . 'class.rb-slider-settings.php' );
            $RB_Slider_Settings = new RB_Slider_Settings();

            require_once( RB_SLIDER_PATH . 'shortcodes/class.rb-slider-shortcode.php' );
            $RB_Slider_Shortcode = new RB_Slider_Shortcode();

            add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 999 );
            add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts') );
        }

        public function define_constants(){
            define( 'RB_SLIDER_PATH', plugin_dir_path( __FILE__ ) );
            define( 'RB_SLIDER_URL', plugin_dir_url( __FILE__ ) );
            define( 'RB_SLIDER_VERSION', '1.0.0' );
        }

        public static function activate(){
            update_option( 'rewrite_rules', '' );
        }

        public static function deactivate(){
            flush_rewrite_rules();
            unregister_post_type( 'rb-slider' );
        }

        public static function uninstall(){

            delete_option( 'rb_slider_options' );

            $posts = get_posts(
                array(
                    'post_type' => 'rb-slider',
                    'number_posts'  => -1,
                    'post_status'   => 'any'
                )
            );

            foreach( $posts as $post ){
                wp_delete_post( $post->ID, true );
            }
        }

        public function load_textdomain(){
            load_plugin_textdomain(
                'rb-slider',
                false,
                dirname( plugin_basename( __FILE__ ) ) . '/languages/'
            );
        }

        public function add_menu(){
            add_menu_page(
                esc_html__( 'RB Slider Options', 'rb-slider' ),
                'RB Slider',
                'manage_options',
                'rb_slider_admin',
                array( $this, 'rb_slider_settings_page' ),
                'dashicons-images-alt2'
            );

            add_submenu_page(
                'rb_slider_admin',
                esc_html__( 'Manage Slides', 'rb-slider' ),
                esc_html__( 'Manage Slides', 'rb-slider' ),
                'manage_options',
                'edit.php?post_type=rb-slider',
                null,
                null
            );

            add_submenu_page(
                'rb_slider_admin',
                esc_html__( 'Add New Slide', 'rb-slider' ),
                esc_html__( 'Add New Slide', 'rb-slider' ),
                'manage_options',
                'post-new.php?post_type=rb-slider',
                null,
                null
            );

        }

        public function rb_slider_settings_page(){
            if( ! current_user_can( 'manage_options' ) ){
                return;
            }

            if( isset( $_GET['settings-updated'] ) ){
                add_settings_error( 'rb_slider_options', 'rb_slider_message', esc_html__( 'Settings Saved', 'rb-slider' ), 'success' );
            }
            
            settings_errors( 'rb_slider_options' );

            require( RB_SLIDER_PATH . 'views/settings-page.php' );
        }

        public function register_scripts(){
            wp_register_script( 'rb-slider-main-jq', RB_SLIDER_URL . 'vendor/flexslider/jquery.flexslider-min.js', array( 'jquery' ), RB_SLIDER_VERSION, true );
            wp_register_style( 'rb-slider-main-css', RB_SLIDER_URL . 'vendor/flexslider/flexslider.css', array(), RB_SLIDER_VERSION, 'all' );
            wp_register_style( 'rb-slider-style-css', RB_SLIDER_URL . 'assets/css/frontend.css', array(), RB_SLIDER_VERSION, 'all' );
        }

        public function register_admin_scripts(){
            global $typenow;
            if( $typenow == 'rb-slider'){
                wp_enqueue_style( 'rb-slider-admin', RB_SLIDER_URL . 'assets/css/admin.css' );
            }
        }

    }
}

if( class_exists( 'RB_Slider' ) ){
    register_activation_hook( __FILE__, array( 'RB_Slider', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'RB_Slider', 'deactivate' ) );
    register_uninstall_hook( __FILE__, array( 'RB_Slider', 'uninstall' ) );

    $rb_slider = new RB_Slider();
} 
