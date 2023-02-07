<?php

global $wpdb;
$query = $wpdb->prepare( 
    "SELECT * FROM $wpdb->announcementmeta
    WHERE announcement_id = %d",
    $post->ID
);
$results = $wpdb->get_results( $query, ARRAY_A );
?>
<table class="form-table rb-announcements-metabox"> 
    <!-- Nonce -->
    <input type="hidden" name="rb_announcements_nonce" value="<?php echo wp_create_nonce( 'rb_announcements_nonce' ); ?>">

    <input 
    type="hidden" 
    name="rb_announcements_action" 
    value="<?php echo ( empty ( $results[0]['meta_value'] ) || empty ( $results[1]['meta_value'] ) ? 'save' : 'update' ); ?>">

    <tr>
        <th>
            <label for="rb_announcements_sale"><?php esc_html_e( 'Has sale?', 'rb-announcements' ); ?></label>
        </th>
        <td>
            <select name="rb_announcements_sale" id="rb_announcements_sale">
                <option value="Yes" <?php if( isset( $results[0]['meta_value'] ) ) selected( $results[0]['meta_value'], 'Yes' ); ?>><?php esc_html_e( 'Yes', 'rb-announcements' )?></option>';
                <option value="No" <?php if( isset( $results[0]['meta_value'] ) ) selected( $results[0]['meta_value'], 'No' ); ?>><?php esc_html_e( 'No', 'rb-announcements' )?></option>';
            </select>            
        </td>
    </tr>
    <tr>
        <th>
            <label for="rb_announcements_video_url"><?php esc_html_e( 'Video URL', 'rb-announcements' ); ?></label>
        </th>
        <td>
            <input 
                type="url" 
                name="rb_announcements_video_url" 
                id="rb_announcements_video_url" 
                class="regular-text video-url"
                value="<?php echo ( isset( $results[1]['meta_value'] ) ) ? esc_url( $results[1]['meta_value'] ) : ""; ?>"
            >
        </td>
    </tr> 
</table>