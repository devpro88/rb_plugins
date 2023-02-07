<?php

/**
* Plugin Name: RB Announcements
* Plugin URI: https://www.wordpress.org/rb-announcements
* Description: A plugin for commercial announcements, business correspondence, blog or something similar on a WordPress site.
* Version: 1.0
* Requires at least: 5.6
* Requires PHP: 7.0
* Author: Ruslan Bondar
* Author URI: www.linkedin.com/in/ruslan-bondar-79531b123
* License: GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: rb-announcements
* Domain Path: /languages
*/
/*
RB Announcements is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
RB Announcements is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with RB Announcements. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( !class_exists( 'RB_Announcements' )){

	class RB_Announcements{

		public function __construct(){

            $this->load_textdomain();

			$this->define_constants(); 

            require_once( RB_ANNOUNCEMENTS_PATH . "functions/functions.php" );

            require_once( RB_ANNOUNCEMENTS_PATH . "post-types/class.rb-announcements-cpt.php" );
            $RBAnnouncementsPostType = new RB_Announcements_Post_Type();

            require_once( RB_ANNOUNCEMENTS_PATH . "shortcodes/class.rb-announcements-shortcode.php" );
            $RBAnnouncementsShortcode = new RB_Announcements_Shortcode();

            require_once( RB_ANNOUNCEMENTS_PATH . "shortcodes/class.rb-announcements-edit-shortcode.php" );
            $RBAnnouncementsEditShortcode = new RB_Announcements_Edit_Shortcode();

            add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 999 );

            add_filter( 'single_template', array( $this, 'load_custom_single_template' ) );
            			
		}

		public function define_constants(){
            // Path/URL to root of this plugin, with trailing slash.
			define ( 'RB_ANNOUNCEMENTS_PATH', plugin_dir_path( __FILE__ ) );
            define ( 'RB_ANNOUNCEMENTS_URL', plugin_dir_url( __FILE__ ) );
            define ( 'RB_ANNOUNCEMENTS_VERSION', '1.0.0' );
		}

        public function load_textdomain(){
            load_plugin_textdomain(
                'rb-announcements',
                false,
                dirname( plugin_basename( __FILE__ ) ) . '/languages/'
            );
        }

        /**
         * Activate the plugin
         */
        public static function activate(){
            update_option('rewrite_rules', '' );

            global $wpdb;

            $table_name = $wpdb->prefix . "announcementmeta";

            $rbt_db_version = get_option( 'rb_announcement_db_version' ) ;

            if( empty( $rbt_db_version ) ){
                $query = "
                    CREATE TABLE $table_name (
                        meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                        announcement_id bigint(20) NOT NULL DEFAULT '0',
                        meta_key varchar(255) DEFAULT NULL,
                        meta_value longtext,
                        PRIMARY KEY  (meta_id),
                        KEY announcement_id (announcement_id),
                        KEY meta_key (meta_key))
                        ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $query );

                $rbt_db_version = '1.0';
                add_option( 'rb_announcement_db_version', $rbt_db_version );
            }

            if( $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'submit-announcement'" ) === null ){
                
                $current_user = wp_get_current_user();

                $page = array(
                    'post_title'    => __('Submit Announcements', 'rb-announcements' ),
                    'post_name' => 'submit-announcement',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user->ID,
                    'post_type' => 'page',
                    'post_content'  => '<!-- wp:shortcode -->[rb_announcements]<!-- /wp:shortcode -->'
                );
                wp_insert_post( $page );
            }

            if( $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'edit-announcement'" ) === null ){
                
                $current_user = wp_get_current_user();

                $page = array(
                    'post_title'    => __('Edit Announcement', 'rb-announcements' ),
                    'post_name' => 'edit-announcement',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user->ID,
                    'post_type' => 'page',
                    'post_content'  => '<!-- wp:shortcode -->[rb_announcements_edit]<!-- /wp:shortcode -->'
                );
                wp_insert_post( $page );
            }
        }

        /**
         * Deactivate the plugin
         */
        public static function deactivate(){
            flush_rewrite_rules();
            unregister_post_type( 'rb-announcements' );
        }        

        /**
         * Uninstall the plugin
         */
        public static function uninstall(){
            delete_option( 'rb_announcement_db_version' );

            global $wpdb;

            $wpdb->query(
                "DELETE FROM $wpdb->posts
                WHERE post_type = 'rb-announcements'"
            );

            $wpdb->query(
                "DELETE FROM $wpdb->posts
                WHERE post_type = 'page'
                AND post_name IN( 'submit-announcement', 'edit-announcement' )"
            );

            $wpdb->query( $wpdb->prepare(
                "DROP TABLE IF EXISTS %s",
                $wpdb->prefix . 'announcementmeta'
            ));            

        }  
        
        public function register_scripts(){
            wp_register_script( 'custom_js', RB_ANNOUNCEMENTS_URL . 'assets/jquery.custom.js', array( 'jquery' ), RB_ANNOUNCEMENTS_VERSION, true );
            wp_register_script( 'validate_js', RB_ANNOUNCEMENTS_URL . 'assets/jquery.validate.min.js', array( 'jquery' ), RB_ANNOUNCEMENTS_VERSION, true );
            if( is_singular( 'rb-announcements' )){
                wp_enqueue_style( 'rb-announcements', RB_ANNOUNCEMENTS_URL . 'assets/style.css', array(), RB_ANNOUNCEMENTS_VERSION, 'all' );
            }
        }

        public function load_custom_single_template( $tpl ){
            if( is_singular( 'rb-announcements' ) ){
                $tpl = RB_ANNOUNCEMENTS_PATH . 'views/templates/single-rb-announcements.php';
            }
            return $tpl;
        }

	}
}

// Plugin Instantiation
if (class_exists( 'RB_Announcements' )){

    // Installation and uninstallation hooks
    register_activation_hook( __FILE__, array( 'RB_Announcements', 'activate'));
    register_deactivation_hook( __FILE__, array( 'RB_Announcements', 'deactivate'));
    register_uninstall_hook( __FILE__, array( 'RB_Announcements', 'uninstall' ) );

    // Instatiate the plugin class
    $rb_announcements = new RB_Announcements(); 
}

// function wpd_plugin_page_template( $page_template ){
//     if ( is_page( 'submit-announcement' ) ) {
//         $page_template = dirname( __FILE__ ) . '/testingg.php';
//     }
//     return $page_template;
// }
// add_filter( 'page_template', 'wpd_plugin_page_template' );