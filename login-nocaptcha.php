<?php
/*
Plugin Name: Login No Captcha reCAPTCHA
Plugin URI: https://wordpress.org/plugins/login-recaptcha/
Description: Adds a Google reCAPTCHA No Captcha checkbox to the login form, thwarting automated hacking attempts
Author: Robert Peake
Version: 1.0.1
Author URI: http://www.robertpeake.com/
Text Domain: login_nocaptcha
Domain Path: /languages/
*/

if ( !function_exists( 'add_action' ) ) {
    die();
}

class LoginNocaptcha {

    public static function init() {
        add_action( 'plugins_loaded', array('LoginNocaptcha', 'load_textdomain') );
        add_action( 'plugins_loaded', array('LoginNocaptcha', 'register_scripts_css' ));
        add_action( 'admin_menu', array('LoginNocaptcha', 'register_menu_page' ));
        add_action( 'admin_init', array('LoginNocaptcha', 'register_settings' ));
        add_action( 'admin_notices', array('LoginNocaptcha', 'admin_notices' ));

        if (LoginNocaptcha::valid_key_secret(get_option('login_nocaptcha_key')) && 
            LoginNocaptcha::valid_key_secret(get_option('login_nocaptcha_secret')) ) {
            add_action('login_enqueue_scripts', array('LoginNocaptcha', 'login_enqueue_scripts_css'));
            add_action('login_form',array('LoginNocaptcha', 'login_form'));
            add_action('authenticate', array('LoginNocaptcha', 'authenticate'), 30, 3);
        }
    }

    public static function load_textdomain() {
        load_plugin_textdomain( 'login_nocaptcha', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public static function register_menu_page(){
        add_options_page( __('Login NoCatpcha Options','login_nocaptcha'), __('Login NoCaptcha','login_nocaptcha'), 'manage_options', plugin_dir_path(  __FILE__ ).'admin.php');
    }

    public static function register_settings() {

        /* user-configurable values */
        add_option('login_nocaptcha_key', '');
        add_option('login_nocaptcha_secret', '');
        
        /* user-configurable value checking public static functions */
        register_setting( 'login_nocaptcha', 'login_nocaptcha_key', 'LoginNocaptcha::filter_string' );
        register_setting( 'login_nocaptcha', 'login_nocaptcha_secret', 'LoginNocaptcha::filter_string' );

        /* system values to determine if captcha is working and display useful error messages */
        add_option('login_nocaptcha_working', false);
        add_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha has not been properly configured. <a href="%s">Click here</a> to configure.','login_nocaptcha'), 'options-general.php?page=login-recaptcha/admin.php'));
        add_option('login_nocaptcha_message_type', 'update-nag');
        if (LoginNocaptcha::valid_key_secret(get_option('login_nocaptcha_key')) && 
           LoginNocaptcha::valid_key_secret(get_option('login_nocaptcha_secret')) ) {
            update_option('login_nocaptcha_working', true);
        } else {
            update_option('login_nocaptcha_working', false);
            update_option('login_nocaptcha_message_type', 'update-nag');
            update_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha has not been properly configured. <a href="%s">Click here</a> to configure.','login_nocaptcha'), 'options-general.php?page=login-recaptcha/admin.php'));
        }
    }

    public static function filter_string( $string ) {
        return trim(filter_var($string, FILTER_SANITIZE_STRING)); //must consist of valid string characters
    }

    public static function valid_key_secret( $string ) {
        if (strlen($string) === 40) {
            return true;
        } else {
            return false;
        }
    }

    public static function register_scripts_css() {
        wp_register_script('login_nocaptcha_google_api', 'https://www.google.com/recaptcha/api.js?hl='.get_locale() );
        wp_register_style('login_nocaptcha_css', plugin_dir_url( __FILE__ ) . 'css/style.css');
    }

    public static function login_enqueue_scripts_css() {
        wp_enqueue_script('login_nocaptcha_google_api');
        wp_enqueue_style('login_nocaptcha_css');
    }

    public static function get_google_errors_as_string( $g_response ) {
        $string = '';
        $codes = array( 'missing-input-secret' => __('The secret parameter is missing.','login_nocaptcha'),
                        'invalid-input-secret' => __('The secret parameter is invalid or malformed.','login_nocaptcha'),
                        'missing-input-response' => __('The response parameter is missing.','login_nocaptcha'),
                        'invalid-input-response' => __('The response parameter is invalid or malformed.','login_nocaptcha') 
                        );
        foreach ($g_response->{'error-codes'} as $code) {
            $string .= $codes[$code].' ';
        }
        return trim($string);
    }

    public static function login_form() {
        echo sprintf('<div class="g-recaptcha" data-sitekey="%s"></div>', get_option('login_nocaptcha_key'));
    }

    public static function authenticate($user, $username, $password) {
        if (isset($_POST['g-recaptcha-response'])) {
            $response = LoginNocaptcha::filter_string($_POST['g-recaptcha-response']);
            $remoteip = $_SERVER["REMOTE_ADDR"];
            $secret = get_option('login_nocaptcha_secret');
            $payload = array('secret' => $secret, 'response' => $response, 'remoteip' => $remoteip);
            $result = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', $payload );
            if (is_a($result,'WP_Error')) { // disable SSL verification for older cURL clients
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $result = curl_exec($ch);
                $g_response = json_decode( $result );
            } else {
                $g_response = json_decode($result['body']);
            }
            if (is_object($g_response)) {
                if ( $g_response->success ) {
                    update_option('login_nocaptcha_working', true);
                    return $user; // success, let them in
                } else {
                    if (in_array('missing-input-response', $g_response->{'error-codes'})) {
                        update_option('login_nocaptcha_working', true);
                        return new WP_Error('denied', __('Please check the ReCaptcha box.','login_nocaptcha'));
                    } else if (in_array('missing-input-secret', $g_response->{'error-codes'}) ||
                           in_array('invalid-input-secret', $g_response->{'error-codes'}) ) {
                        update_option('login_nocaptcha_working', false);
                        update_option('login_nocaptcha_google_error', 'error');
                        update_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha is not working. <a href="%s">Please check your settings</a>. The message from Google was: %s', 'login_nocaptcha'), 
                                                               'options-general.php?page=login-recaptcha/admin.php',
                                                                get_google_errors_as_string($g_response)));
                        return $user; //invalid secret entered; prevent lockouts
                    } else {
                        update_option('login_nocaptcha_working', true);
                        return new WP_Error('denied', __('Incorrect ReCaptcha, please try again.','login_nocaptcha'));
                    }
                }
            } else {
                update_option('login_nocaptcha_working', false);
                update_option('login_nocaptcha_google_error', 'error');
                update_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha is not working. <a href="%s">Please check your settings</a>.', 'login_nocaptcha'), 'options-general.php?page=login-recaptcha/admin.php').' '.__('The response from Google was not valid.','login_nocaptcha'));
                return $user; //not a sane response, prevent lockouts
            }
        } else {
            update_option('login_nocaptcha_working', false);
            update_option('login_nocaptcha_google_error', 'error');
            update_option('login_nocaptcha_error', sprintf(__('Login NoCaptcha is not working. <a href="%s">Please check your settings</a>.', 'login_nocaptcha'), 'options-general.php?page=login-recaptcha/admin.php').' '.__('There was no response from Google.','login_nocaptcha') );
            return $user; //no response from Google
        }
    }

    public static function admin_notices() {
        if(false == get_option('login_nocaptcha_working')) {
            echo '<div class="update-nag">'."\n";
            echo '    <p>'."\n";
            echo get_option('login_nocaptcha_error');
            echo '    </p>'."\n";
            echo '</div>'."\n";
        }
    }
}
LoginNocaptcha::init();
