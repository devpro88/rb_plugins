<?php
if( ! function_exists( 'rb_slider_get_placeholder_image' )){
    function rb_slider_get_placeholder_image(){
        return "<img src='" . RB_SLIDER_URL . "assets/images/default.jpg' class='img-fluid wp-post-image' />";
    }
}

if( ! function_exists( 'rb_slider_options' )){
    function rb_slider_options(){
        $show_bullets = isset( RB_Slider_Settings::$options['rb_slider_bullets'] ) && RB_Slider_Settings::$options['rb_slider_bullets'] == 1 ? true : false;

        wp_enqueue_script( 'rb-slider-options-js', RB_SLIDER_URL . 'vendor/flexslider/flexslider.js', array( 'jquery' ), RB_SLIDER_VERSION, true );
        wp_localize_script( 'rb-slider-options-js', 'SLIDER_OPTIONS', array(
            'controlNav' => $show_bullets
        ) );
    }
}
