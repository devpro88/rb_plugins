<?php

if( ! class_exists( 'RB_Announcements_Post_Type' )){
    class RB_Announcements_Post_Type{
        public function __construct(){
            add_action( 'init', array( $this, 'create_post_type' ) );
            add_action( 'init', array( $this, 'create_taxonomy' ) );
            add_action( 'init', array( $this, 'register_metadata_table' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

            add_action( 'wp_insert_post', array( $this, 'save_post' ), 10, 2 );
            add_action( 'delete_post', array( $this, 'delete_post' ) );

            add_action( 'pre_get_posts', array( $this, 'add_cpt_author' ) );

        }

        public function create_post_type(){
            register_post_type(
                'rb-announcements',
                array(
                    'label' => esc_html__( 'Announcement', 'rb-announcements' ),
                    'description'   => esc_html__( 'Announcements', 'rb-announcements' ),
                    'labels' => array(
                        'name'  => esc_html__( 'Announcements', 'rb-announcements' ),
                        'singular_name' => esc_html__( 'Announcement', 'rb-announcements' ),
                    ),
                    'public'    => true,
                    'supports'  => array( 'title', 'editor', 'author', 'thumbnail' ),
                    'rewrite'   => array( 'slug' => 'announcements' ),
                    'hierarchical'  => false,
                    'show_ui'   => true,
                    'show_in_menu'  => true,
                    'menu_position' => 5,
                    'show_in_admin_bar' => true,
                    'show_in_nav_menus' => true,
                    'can_export'    => true,
                    'has_archive'   => true,
                    'exclude_from_search'   => false,
                    'publicly_queryable'    => true,
                    'show_in_rest'  => true,
                    'menu_icon' => 'dashicons-admin-site',
                )
            );
        }

        public function create_taxonomy(){
            register_taxonomy(
                'products',
                'rb-announcements',
                array(
                    'labels' => array(
                        'name'  => __( 'Products', 'rb-announcements' ),
                        'singular_name' => __( 'Product', 'rb-announcements' ),
                    ),
                    'hierarchical' => false,
                    'show_in_rest' => true,
                    'public'    => true,
                    'show_admin_column' => true
                )
            );
        }

        public function add_cpt_author( $query ) {
            if ( !is_admin() && $query->is_author() && $query->is_main_query() ) {
                $query->set( 'post_type', array( 'rb-announcements', 'post' ) );
            }
        }

        public function register_metadata_table(){
            global $wpdb;
            $wpdb->announcementmeta = $wpdb->prefix . 'announcementmeta';
        }

        public function add_meta_boxes(){
            add_meta_box(
                'rb_announcements_meta_box',
                esc_html__( 'Announcements Options', 'rb-announcements' ),
                array( $this, 'add_inner_meta_boxes' ),
                'rb-announcements',
                'normal',
                'high'
            );
        }

        public function add_inner_meta_boxes( $post ){
            require_once( RB_ANNOUNCEMENTS_PATH . 'views/rb-announcements_metabox.php' );
        }

        public static function save_post( $post_id, $post ){
            if( isset( $_POST['rb_announcements_nonce'] ) ){
                if( ! wp_verify_nonce( $_POST['rb_announcements_nonce'], 'rb_announcements_nonce' ) ){
                    return;
                }
            }

            if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
                return;
            }

            if( isset( $_POST['post_type'] ) && $_POST['post_type'] === 'rb-announcements' ){
                if( ! current_user_can( 'edit_page', $post_id ) ){
                    return;
                }elseif( ! current_user_can( 'edit_post', $post_id ) ){
                    return;
                }
            }

            if (isset($_POST['action']) && $_POST['action'] == 'editpost') {

                $sale = sanitize_text_field( $_POST['rb_announcements_sale'] );
                $video = esc_url_raw( $_POST['rb_announcements_video_url'] );

                global $wpdb;
                if( $_POST['rb_announcements_action'] == 'save' ){
                    if( get_post_type( $post ) == 'rb-announcements' && 
                        $post->post_status != 'trash' &&
                        $post->post_status != 'auto-draft' &&
                        $post->post_status != 'draft' &&
                        $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT announcement_id
                                FROM $wpdb->announcementmeta
                                WHERE announcement_id = %d",
                                $post_id
                            )) == null
                    ){
                        $wpdb->insert(
                            $wpdb->announcementmeta,
                            array(
                                'announcement_id'    => $post_id,
                                'meta_key'  => 'rb_announcements_sale',
                                'meta_value'    => $sale
                            ),
                            array(
                                '%d', '%s', '%s'
                            )
                        );
                        $wpdb->insert(
                            $wpdb->announcementmeta,
                            array(
                                'announcement_id'    => $post_id,
                                'meta_key'  => 'rb_announcements_video_url',
                                'meta_value'    => $video
                            ),
                            array(
                                '%d', '%s', '%s'
                            )
                        );
                    }
                }else{
                    if( get_post_type( $post ) == 'rb-announcements' ){
                        $wpdb->update(
                            $wpdb->announcementmeta,
                            array(
                                'meta_value'    => $sale
                            ),
                            array(
                                'announcement_id'    => $post_id,
                                'meta_key'  => 'rb_announcements_sale',   
                            ),
                            array( '%s' ),
                            array( '%d', '%s' )
                        );
                        $wpdb->update(
                            $wpdb->announcementmeta,
                            array(
                                'meta_value'    => $video
                            ),
                            array(
                                'announcement_id'    => $post_id,
                                'meta_key'  => 'rb_announcements_video_url',   
                            ),
                            array( '%s' ),
                            array( '%d', '%s' )
                        );
                    }
                }

            }
        }

        public function delete_post( $post_id ){
            if( ! current_user_can( 'delete_posts' ) ){
                return;
            }
            if( get_post_type( $post ) == 'rb-announcements' ){
                global $wpdb;
                $wpdb->delete(
                    $wpdb->announcementmeta,
                    array( 'announcement_id' => $post_id ),
                    array( '%d' )
                );
            }
        }
    }
}
