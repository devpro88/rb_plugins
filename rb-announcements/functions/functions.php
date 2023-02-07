<?php

function rbt_register_user(){

    if( isset( $_POST['submitted'] )){
        if( isset( $_POST['rb_announcements_register_nonce'] ) ){
            if( ! wp_verify_nonce( $_POST['rb_announcements_register_nonce'], 'rb_announcements_register_nonce' ) ){
                return;
            }
        }

        global $reg_errors;
        $reg_errors = new WP_Error();

        $username = sanitize_user( $_POST['username'] );
        $firstname = sanitize_text_field( $_POST['firstname'] );
        $lastname = sanitize_text_field( $_POST['lastname'] );
        $useremail = sanitize_email( $_POST['useremail'] );
        $password = $_POST['password'];

        if( empty( $username ) || empty( $firstname ) || empty( $lastname ) || empty( $useremail ) || empty( $password ) ){
            $reg_errors->add( 'empty-field', esc_html__( 'Required form field is missing', 'rb-announcements' ) );
        }

        if( strlen( $username ) < 6 ){
            $reg_errors->add( 'username_length', esc_html__( 'Username too short. At least 6 characters is required', 'rb-announcements' ) );
        }

        if( username_exists( $username ) ){
            $reg_errors->add( 'user_name', esc_html__( 'Invalid credentials', 'rb-announcements' ) );
        }

        if( ! validate_username( $username ) ){
            $reg_errors->add( 'username_invalid', esc_html__( 'The username you entered is not valid!', 'rb-announcements' ) );
        }

        if( ! is_email( $useremail ) ){
            $reg_errors->add( 'email_invalid', esc_html__( 'Email is not valid', 'rb-announcements' ) );
        }

        if( email_exists( $useremail ) ){
            $reg_errors->add( 'email_exists', esc_html__( 'Email already exists', 'rb-announcements' ) );
        }

        if( strlen( $password ) < 5 ){
            $reg_errors->add( 'password_length', esc_html__( 'Password length must be greater than 5', 'rb-announcements' ) );
        }

        if( is_wp_error( $reg_errors ) ){
            foreach( $reg_errors->get_error_messages() as $error ){
                ?>
                    <div style="color:#FF0000; text-align:left"><?php echo $error; ?></div>
                <?php
            }
        }

        if( count( $reg_errors->get_error_messages() ) < 1 ){
            $user_data = array(
                'user_login'    => $username,
                'first_name'    => $firstname,
                'last_name' => $lastname,
                'user_email'    => $useremail,
                'user_pass' => $password,
                'role'  => 'author'
            );
            $user = wp_insert_user( $user_data );

            wp_login_form();            
        }
    }
    if( ! isset( $user )){
    ?>
        <h3><?php esc_html_e( 'Create your account', 'rb-announcements' ); ?></h3>
        <form action="" method="post" name="user_registeration">
            <label for="username"><?php esc_html_e( 'Username', 'rb-announcements' ); ?> *</label>  
            <input type="text" name="username" required /><br />
            <label for="firstname"><?php esc_html_e( 'First Name', 'rb-announcements' ); ?> *</label>  
            <input type="text" name="firstname" required /><br />
            <label for="lastname"><?php esc_html_e( 'Last Name', 'rb-announcements' ); ?> *</label>  
            <input type="text" name="lastname" required /><br />
            <label for="useremail"><?php esc_html_e( 'Email address', 'rb-announcements' ); ?> *</label>
            <input type="text" name="useremail" required /> <br />
            <label for="password"><?php esc_html_e( 'Password', 'rb-announcements' ); ?> *</label>
            <input type="password" name="password" required /> <br />
            <input type="submit" name="user_registeration" value="<?php echo esc_attr__( 'Sign Up', 'rb-announcements' ); ?>" />

            <input type="hidden" name="rb_announcements_register_nonce" value="<?php echo wp_create_nonce( 'rb_announcements_register_nonce' ); ?>">
            <input type="hidden" name="submitted" id="submitted" value="true" />
        </form>
        <h3><?php esc_html_e( 'Or login', 'rb-announcements' ); ?></h3>
        <?php wp_login_form(); ?>
    <?php
    }
}