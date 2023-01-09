<?php 

if( ! class_exists( 'RB_Slider_Settings' )){
    class RB_Slider_Settings{

        public static $options;

        public function __construct(){
            self::$options = get_option( 'rb_slider_options' );
            add_action( 'admin_init', array( $this, 'admin_init') );
        }

        public function admin_init(){
            
            register_setting( 'rb_slider_group', 'rb_slider_options', array( $this, 'rb_slider_validate' ) );

            add_settings_section(
                'rb_slider_main_section',
                esc_html__( 'How does it work?', 'rb-slider' ),
                null,
                'rb_slider_page1'
            );

            add_settings_section(
                'rb_slider_second_section',
                esc_html__( 'Other Plugin Options', 'rb-slider' ),
                null,
                'rb_slider_page2'
            );

            add_settings_field(
                'rb_slider_shortcode',
                esc_html__( 'Shortcode', 'rb-slider' ),
                array( $this, 'rb_slider_shortcode_callback' ),
                'rb_slider_page1',
                'rb_slider_main_section'
            );

            add_settings_field(
                'rb_slider_title',
                esc_html__( 'Slider Title', 'rb-slider' ),
                array( $this, 'rb_slider_title_callback' ),
                'rb_slider_page2',
                'rb_slider_second_section',
                array(
                    'label_for' => 'rb_slider_title'
                )
            );

            add_settings_field(
                'rb_slider_bullets',
                esc_html__( 'Display Bullets', 'rb-slider' ),
                array( $this, 'rb_slider_bullets_callback' ),
                'rb_slider_page2',
                'rb_slider_second_section',
                array(
                    'label_for' => 'rb_slider_bullets'
                )
            );

            add_settings_field(
                'rb_slider_style',
                esc_html__( 'Slider Style', 'rb-slider' ),
                array( $this, 'rb_slider_style_callback' ),
                'rb_slider_page2',
                'rb_slider_second_section',
                array(
                    'items' => array(
                        'style-1',
                        'style-2'
                    ),
                    'label_for' => 'rb_slider_style'
                )
                
            );
        }

        public function rb_slider_shortcode_callback(){
            ?>
            <span><?php esc_html_e( 'Use the shortcode [rb_slider] to display the slider in any page/post/widget', 'rb-slider' ); ?></span>
            <?php
        }

        public function rb_slider_title_callback( $args ){
            ?>
                <input 
                type="text" 
                name="rb_slider_options[rb_slider_title]" 
                id="rb_slider_title"
                value="<?php echo isset( self::$options['rb_slider_title'] ) ? esc_attr( self::$options['rb_slider_title'] ) : ''; ?>"
                >
            <?php
        }
        
        public function rb_slider_bullets_callback( $args ){
            ?>
                <input 
                    type="checkbox"
                    name="rb_slider_options[rb_slider_bullets]"
                    id="rb_slider_bullets"
                    value="1"
                    <?php 
                        if( isset( self::$options['rb_slider_bullets'] ) ){
                            checked( "1", self::$options['rb_slider_bullets'], true );
                        }    
                    ?>
                />
                <label for="rb_slider_bullets"><?php esc_html_e( 'Whether to display bullets or not', 'rb-slider' ); ?></label>
                
            <?php
        }

        public function rb_slider_style_callback( $args ){
            ?>
            <select 
                id="rb_slider_style" 
                name="rb_slider_options[rb_slider_style]">
                <?php 
                foreach( $args['items'] as $item ):
                ?>
                    <option value="<?php echo esc_attr( $item ); ?>" 
                        <?php 
                        isset( self::$options['rb_slider_style'] ) ? selected( $item, self::$options['rb_slider_style'], true ) : ''; 
                        ?>
                    >
                        <?php echo esc_html( ucfirst( $item ) ); ?>
                    </option>                
                <?php endforeach; ?>
            </select>
            <?php
        }

        public function rb_slider_validate( $input ){
            $new_input = array();
            foreach( $input as $key => $value ){
                switch ($key){
                    case 'rb_slider_title':
                        if( empty( $value )){
                            add_settings_error( 'rb_slider_options', 'rb_slider_message', esc_html__( 'The title field can not be left empty', 'rb-slider' ), 'error' );
                            $value = esc_html__( 'Please, type some text', 'rb-slider' );
                        }
                        $new_input[$key] = sanitize_text_field( $value );
                    break;
                    default:
                        $new_input[$key] = sanitize_text_field( $value );
                    break;
                }
            }
            return $new_input;
        }

    }
}

