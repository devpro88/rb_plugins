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
            'post_status'   => 'pending'
        );

        $post_id = wp_insert_post( $post_info );

        global $post;
        RB_Announcements_Post_Type::save_post( $post_id, $post );        
    }

}
?>
<div class="rb-announcements">
    <form action="" method="POST" id="announcements-form">
        <h2><?php esc_html_e( 'Submit new announcement' , 'rb-announcements' ); ?></h2>

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
        <input type="text" name="rb_announcements_title" id="rb_announcements_title" value="<?php if( isset( $title ) ) echo $title; ?>" required />
        <br />
        <label for="rb_announcements_product"><?php esc_html_e( 'Product', 'rb-announcements' ); ?> *</label>
        <input type="text" name="rb_announcements_product" id="rb_announcements_product" value="<?php if( isset( $product ) ) echo $product; ?>" required />

        <br />
        <?php 
        if( isset( $content )){
            wp_editor( $content, 'rb_announcements_content', array( 'wpautop' => true, 'media_buttons' => false ) );
        }else{
            wp_editor( '', 'rb_announcements_content', array( 'wpautop' => true, 'media_buttons' => false ) );
        }
        ?>
        </br />
        
        <fieldset id="additional-fields">
            <label for="rb_announcements_sale"><?php esc_html_e( 'Has sale?', 'rb-announcements' ); ?></label>
            <select name="rb_announcements_sale" id="rb_announcements_sale">
                <option value="Yes" <?php if( isset( $sale ) ) selected( $sale, "Yes" ); ?>><?php esc_html_e( 'Yes', 'rb-announcements' ); ?></option>
                <option value="No" <?php if( isset( $sale ) ) selected( $sale, "No" ); ?>><?php esc_html_e( 'No', 'rb-announcements' ); ?></option>
            </select>
            <label for="rb_announcements_video_url"><?php esc_html_e( 'Video URL', 'rb-announcements' ); ?></label>
            <input type="url" name="rb_announcements_video_url" id="rb_announcements_video_url" value="<?php if( isset( $video ) ) echo $video; ?>" />
        </fieldset>
        <br />
        <input type="hidden" name="rb_announcements_action" value="save">
        <input type="hidden" name="action" value="editpost">
        <input type="hidden" name="rb_announcements_nonce" value="<?php echo wp_create_nonce( 'rb_announcements_nonce' ); ?>">
        <input type="hidden" name="submitted" id="submitted" value="true" />
        <input type="submit" name="submit_form" value="<?php esc_attr_e( 'Submit', 'rb-announcements' ); ?>" />
    </form>
</div>
<div class="announcements-list">
<?php 

global $current_user;
global $wpdb; 
$q = $wpdb->prepare(
    "SELECT ID, post_author, post_date, post_title, post_status, meta_key, meta_value
    FROM $wpdb->posts AS p
    INNER JOIN $wpdb->announcementmeta AS tm
    ON p.ID = tm.announcement_id
    WHERE p.post_author = %d
    AND tm.meta_key = 'rb_announcements_sale'
    AND p.post_status IN ( 'publish', 'pending' )
    ORDER BY p.post_date DESC",
    $current_user->ID
);
$results = $wpdb->get_results( $q );

if( $wpdb->num_rows ):
?>
            <table>
                <caption><?php esc_html_e( 'Your Announcements', 'rb-announcements' ); ?></caption>
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'rb-announcements' ); ?></th>
                        <th><?php esc_html_e( 'Title', 'rb-announcements' ); ?></th>
                        <th><?php esc_html_e( 'Sale', 'rb-announcements' ); ?></th>
                        <th><?php esc_html_e( 'Edit?', 'rb-announcements' ); ?></th>
                        <th><?php esc_html_e( 'Delete?', 'rb-announcements' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'rb-announcements' ); ?></th>
                    </tr>
                </thead>  
                <tbody>
                <?php foreach( $results as $result ): ?>  
                    <tr>
                        <td><?php echo esc_html( date( 'M/d/Y', strtotime( $result->post_date ) ) ); ?></td>
                        <td><?php echo esc_html( $result->post_title ); ?></td>
                        <td><?php echo $result->meta_value == 'Yes' ? esc_html__( 'Yes', 'rb-announcements' ) : esc_html__( 'No', 'rb-announcements' ); ?></td>
                        <?php $edit_post = add_query_arg( 'post', $result->ID, home_url( '/edit-announcement' ) ); ?>
                        <td><a href="<?php echo esc_url( $edit_post );  ?>"><?php esc_html_e( 'Edit', 'rb-announcements' ); ?></a></td>
                        <td><a onclick="return confirm( 'Are you sure you want to delete post: <?php echo $result->post_title ?>?' )" href="<?php echo get_delete_post_link( $result->ID, "", true ); ?>"><?php esc_html_e( 'Delete', 'rb-announcements' ); ?></a></td>
                        <td><?php echo $result->post_status == 'publish' ? esc_html__( 'Published', 'rb-announcements' ) : esc_html__( 'Pending', 'rb-announcements' ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
<?php endif; ?>
</div>
<script>
if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}
</script>