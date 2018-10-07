<?php
/**
 * Plugin Name: Rating Post
 * Description: Plugin Rating Post for the WordPress
 * Version: 1.0.0
 * Author: La Thiếu Hiệp
 * Author URI: https://www.facebook.com/lathieuhiep
 * License: MIT License
 * Textdomain: dlk-rating-post
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'dlk_rating_post' ) ) :
    
    class dlk_rating_post {

        /*
        * This method loads other methods of the class.
        */
        function __construct() {
            
            /* Load define */
            $this->dlk_rating_post_define();
            
            /* Load check and activated */
            add_action( 'plugins_loaded', [ $this, 'dlk_rating_post_loaded' ] );
            
            /*Load script*/
//            $this->dlk_rating_post_script();
            
        }

        /* Load define */
        function dlk_rating_post_define() {
            
            define( 'DLK_VERSION', '1.0.0' );
            define( 'dlk_rating_post_path', plugins_url( '/', __FILE__ ) );
            define( 'dlk_rating_post_server_path', dirname( __FILE__ ) );
            
        }

        function dlk_rating_post_loaded() {

            /* Load languages */
            $this->dlk_rating_post_i18n();

            /* Load includes */
            $this->dlk_rating_post_includes();

        }

        /* Load languages */
        function dlk_rating_post_i18n() {

            load_plugin_textdomain( 'dlk-rating-post', false, dlk_rating_post_path . 'languages' );

        }

        /* Load includes */
        function dlk_rating_post_includes() {

            require_once ( dlk_rating_post_server_path . '/includes/dashboard-menus.php' );

        }

        /* Load script */
        function dlk_rating_post_script() {
            add_action( 'wp_enqueue_scripts', [ $this, 'dlk_rating_post_frontend_scripts' ] );
        }

        /* Frontend scripts */
        function dlk_rating_post_frontend_scripts() {

            wp_enqueue_style( 'dlk-addons-elementor', dlk_rating_post_path. 'assets/css/dlk-rating-post.css', array(), DLK_VERSION );

        }

    }
    
    new dlk_rating_post();

endif;