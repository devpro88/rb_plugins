<?php 

if( !class_exists( 'RB_Slider_Post_Type') ){
    class RB_Slider_Post_Type{
        function __construct(){
            add_action( 'init', array( $this, 'create_post_type' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
            add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
            add_filter( 'manage_rb-slider_posts_columns', array( $this, 'rb_slider_cpt_columns' ) );
            add_action( 'manage_rb-slider_posts_custom_column', array( $this, 'rb_slider_custom_columns'), 10, 2 );
            add_filter( 'manage_edit-rb-slider_sortable_columns', array( $this, 'rb_slider_sortable_columns' ) );
        }

        public function create_post_type(){
            register_post_type(
                'rb-slider',
                array(
                    'label' => esc_html__( 'Slider', 'rb-slider' ),
                    'description'   => esc_html__( 'Sliders', 'rb-slider' ),
                    'labels' => array(
                        'name'  => esc_html__( 'Sliders', 'rb-slider' ),
                        'singular_name' => esc_html__( 'Slider', 'rb-slider' ),
                    ),
                    'public'    => true,
                    'supports'  => array( 'title', 'editor', 'thumbnail' ),
                    'hierarchical'  => false,
                    'show_ui'   => true,
                    'show_in_menu'  => false,
                    'menu_position' => 50,
                    'show_in_admin_bar' => true,
                    'show_in_nav_menus' => true,
                    'can_export'    => true,
                    'has_archive'   => false,
                    'exclude_from_search'   => false,
                    'publicly_queryable'    => true,
                    'show_in_rest'  => true,
                    'menu_icon' => 'dashicons-images-alt2',
                    //'register_meta_box_cb'  =>  array( $this, 'add_meta_boxes' )
                )
            );
        }

        public function rb_slider_cpt_columns( $columns ){
            $columns['rb_slider_link_text'] = esc_html__( 'Link Text', 'rb-slider' );
            $columns['rb_slider_link_url'] = esc_html__( 'Link URL', 'rb-slider' );
            return $columns;
        }

        public function rb_slider_custom_columns( $column, $post_id ){
            switch( $column ){
                case 'rb_slider_link_text':
                    echo esc_html( get_post_meta( $post_id, 'rb_slider_link_text', true ) );
                break;
                case 'rb_slider_link_url':
                    echo esc_url( get_post_meta( $post_id, 'rb_slider_link_url', true ) );
                break;                
            }
        }

        public function rb_slider_sortable_columns( $columns ){
            $columns['rb_slider_link_text'] = 'rb_slider_link_text';
            return $columns;
        }

        public function add_meta_boxes(){
            add_meta_box(
                'rb_slider_meta_box',
                esc_html__( 'Link Options', 'rb-slider' ),
                array( $this, 'add_inner_meta_boxes' ),
                'rb-slider',
                'normal',
                'high'
            );
        }

        public function add_inner_meta_boxes( $post ){
            require_once( RB_SLIDER_PATH . 'views/rb-slider_metabox.php' );
        }

        public function save_post( $post_id ){
            if( isset( $_POST['rb_slider_nonce'] ) ){
                if( ! wp_verify_nonce( $_POST['rb_slider_nonce'], 'rb_slider_nonce' ) ){
                    return;
                }
            }

            if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
                return;
            }

            if( isset( $_POST['post_type'] ) && $_POST['post_type'] === 'rb-slider' ){
                if( ! current_user_can( 'edit_page', $post_id ) ){
                    return;
                }elseif( ! current_user_can( 'edit_post', $post_id ) ){
                    return;
                }
            }

            if( isset( $_POST['action'] ) && $_POST['action'] == 'editpost' ){
                $old_link_text = get_post_meta( $post_id, 'rb_slider_link_text', true );
                $new_link_text = $_POST['rb_slider_link_text'];
                $old_link_url = get_post_meta( $post_id, 'rb_slider_link_url', true );
                $new_link_url = $_POST['rb_slider_link_url'];

                if( empty( $new_link_text )){
                    update_post_meta( $post_id, 'rb_slider_link_text', esc_html__( 'Add some text', 'rb-slider' ) );
                }else{
                    update_post_meta( $post_id, 'rb_slider_link_text', sanitize_text_field( $new_link_text ), $old_link_text );
                }

                if( empty( $new_link_url )){
                    update_post_meta( $post_id, 'rb_slider_link_url', '#' );
                }else{
                    update_post_meta( $post_id, 'rb_slider_link_url', sanitize_text_field( $new_link_url ), $old_link_url );
                }
                
                
            }
        }

    }
}