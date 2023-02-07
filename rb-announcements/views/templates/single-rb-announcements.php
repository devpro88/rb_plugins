<?php get_header(); ?>

<div class="wrap">
    <div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
    
            <?php

            global $wpdb; 
            $q = $wpdb->prepare(
                "SELECT meta_value
                FROM $wpdb->posts AS p
                INNER JOIN $wpdb->announcementmeta AS tm
                ON p.ID = tm.announcement_id
                WHERE p.ID = %d",
                get_the_ID()
            );
            $results = $wpdb->get_results( $q, ARRAY_A );
            $has_sale = $results[0]['meta_value'] == "Yes" ? "has-sale" : "";
            $video_url = esc_url( $results[1]['meta_value'] );
            $products = get_the_terms( $post->ID, 'products' );

            while( have_posts() ): 
                the_post();

            ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( $has_sale ); ?>>
                        <div class="announcement-item">                   
                            <div class="content">
                                <h1><?php the_title(); ?></h1>
                                <div class="meta">
                                    <span class="product"><strong><?php _e( 'Product', 'rb-announcements' ); ?>:</strong>
                                        <?php foreach( $products as $product ): ?>
                                            <a href="<?php echo esc_url( get_term_link( $product ) ) ?>"><?php echo esc_html( $product->name ); ?></a>
                                        <?php endforeach; ?>
                                    </span>
                                    <span class="author"><strong><?php _e( 'Author', 'rb-announcements' ); ?>: </strong>
                                        <?php the_author_posts_link(); ?>
                                    </span>
                                    <span class="the-date"><strong><?php _e( 'Published on', 'rb-announcements' ); ?>: </strong>
                                        <?php the_date(); ?>
                                    </span>                            
                                </div>
                                <div class="content">
                                    <?php the_content(); ?>                            
                                </div>
                                <div class="video">
                                    <?php 
                                    if( ! empty( $video_url )){
                                        global $wp_embed;
                                        $video_embed = $wp_embed->run_shortcode( '[embed width="560" height="315"]' . $video_url . '[/embed]' );
                                        echo $video_embed;
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </main>
        </div>
    </div>
<?php
get_footer();