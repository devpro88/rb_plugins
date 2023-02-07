<?php

if( ! is_user_logged_in() ){
    rbt_register_user();
    return;
}

if( isset( $_POST['rb_announcements_nonce'] ) ){
    if( ! wp_verify_nonce( $_POST['rb_announcements_nonce'], 'rb_announcements_nonce' ) ){
        return;
    }
}

$errors = array();
$hasError = false;

if( isset( $_POST['submitted'])){
    $title              = $_POST['rb_announcements_title'];
    $content            = $_POST['rb_announcements_content'];
    $product             = $_POST['rb_announcements_product'];
    $sale    = $_POST['rb_announcements_sale'];
    $video              = $_POST['rb_announcements_video_url'];

    if( trim( $title ) === '' ){
        $errors[] = esc_html__( 'Please, enter a title', 'rb-announcements' );
        $hasError = true;
    }

    if( trim( $content ) === '' ){
        $errors[] = esc_html__( 'Please, enter some content', 'rb-announcements' );
        $hasError = true;
    }

    if( trim( $product ) === '' ){
        $errors[] = esc_html__( 'Please, enter some product', 'rb-announcements' );
        $hasError = true;
    }

    if( $hasError === false ){
        $post_info = array(
            'post_type' => 'rb-announcements',
            'post_title'    => sanitize_text_field( $title ),
            'post_content'  => wp_kses_post( $content ),
            'tax_input' => array(
                'products'   => sanitize_text_field( $product )
            ),
            'ID'    => $_GET['post']
        );

        $post_id = wp_update_post( $post_info );

        global $post;
        RB_Announcements_Post_Type::save_post( $post_id, $post );        
    }

}

global $current_user;
global $wpdb; 
$q = $wpdb->prepare(
    "SELECT ID, post_author, post_title, post_content, meta_key, meta_value
    FROM $wpdb->posts AS p
    INNER JOIN $wpdb->announcementmeta AS tm
    ON p.ID = tm.announcement_id
    WHERE p.ID = %d
    AND p.post_author = %d
    ORDER BY p.post_date DESC",
    $_GET['post'],
    $current_user->ID
);
$results = $wpdb->get_results( $q, ARRAY_A );
if( current_user_can( 'edit_post', $_GET['post'] )):
?>
<div class="rb-announcements">
    <form action="" method="POST" id="announcements-form">
        <h2><?php esc_html_e( 'Edit announcement' , 'rb-announcements' ); ?></h2>

        <?php 
            if( $errors != '' ){
                foreach( $errors as $error ){
                    ?>
                        <span class="error">
                            <?php echo $error; ?>
                        </span>
                    <?php
                }
            }
        ?>
        
        <label for="rb_announcements_title"><?php esc_html_e( 'Title', 'rb-announcements' ); ?> *</label>
        <input type="text" name="rb_announcements_title" id="rb_announcements_title" value="<?php echo esc_html( $results[0]['post_title'] ); ?>" required />
        <br />
        <label for="rb_announcements_product"><?php esc_html_e( 'Product', 'rb-announcements' ); ?> *</label>
        <input type="text" name="rb_announcements_product" id="rb_announcements_product" value="<?php echo strip_tags( get_the_term_list( $_GET['post'], 'products', '', ', ' ) ); ?>" required />

        <br />
        <?php 
            wp_editor( $results[0]['post_content'], 'rb_announcements_content', array( 'wpautop' => true, 'media_buttons' => false ) );
        ?>
        </br />
        
        <fieldset id="additional-fields">
            <label for="rb_announcements_sale"><?php esc_html_e( 'Has sale?', 'rb-announcements' ); ?></label>
            <select name="rb_announcements_sale" id="rb_announcements_sale">
                <option value="Yes" <?php selected( $results[0]['meta_value'], "Yes" ); ?>><?php esc_html_e( 'Yes', 'rb-announcements' ); ?></option>
                <option value="No" <?php selected( $results[0]['meta_value'], "No" ); ?>><?php esc_html_e( 'No', 'rb-announcements' ); ?></option>
            </select>
            <label for="rb_announcements_video_url"><?php esc_html_e( 'Video URL', 'rb-announcements' ); ?></label>
            <input type="url" name="rb_announcements_video_url" id="rb_announcements_video_url" value="<?php echo $results[1]['meta_value']; ?>" />
        </fieldset>
        <br />
        <input type="hidden" name="rb_announcements_action" value="update">
        <input type="hidden" name="action" value="editpost">
        <input type="hidden" name="rb_announcements_nonce" value="<?php echo wp_create_nonce( 'rb_announcements_nonce' ); ?>">
        <input type="hidden" name="submitted" id="submitted" value="true" />
        <input type="submit" name="submit_form" value="<?php esc_attr_e( 'Submit', 'rb-announcements' ); ?>" />
    </form>
    <br>
    <a href="<?php echo esc_url( home_url( '/submit-announcement' ) ); ?>"><?php esc_html_e( 'Back to announcements list', 'rb-announcements' ); ?></a>
</div>
<?php endif; ?>
<script>
if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}
</script>