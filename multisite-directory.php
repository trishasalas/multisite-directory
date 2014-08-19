<?php
/**
 * Plugin Name: Multisite Directory
 *
 * @package   Multisite Directory
 * @author    Trisha Salas
 * @license   GPL-2.0+
 * @link      http://trishasalas.com
 * @copyright 2014 Trisha Salas
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! class_exists( 'Multisite_Directory' ) ) {

    /**
     * Main Multisite Directory Class
     *
     * @since 1.0.0
     */
    class Multisite_Directory {

        /**
         * Plugin version, used for cache-busting of style and script file references.
         * @since   1.0.0
         * @var     string
         */
        const VERSION = '1.0.0';

        /**
         * @since    1.0.0
         * @var      string
         */
        protected $plugin_slug = 'mdp-multisite-directory';

        /**
         * Instance of this class.
         * @since    1.0.0
         * @var      object
         */
        static $instance = null;


        /**
         * Return the plugin slug.
         * @since    1.0.0
         * @return    Plugin slug variable.
         */
        public function get_plugin_slug() {
            return $this->plugin_slug;
        }

        /**
         * Construct function
         *
         * @since 1.0.0
         */
        public function __construct() {

            $this->basename         = plugin_basename( __FILE__ );
            $this->directory_path   = plugin_dir_path( __FILE__ );
            $this->directory_url    = plugins_url( dirname( $this->basename ) );

            add_action( 'init', array( $this, 'includes' ) );
            add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            register_activation_hook( __FILE__, array( $this, 'activate' ) );
            register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

            register_activation_hook( __FILE__, array( $this, 'create_directory_page' ) );

            add_filter( 'the_content', array( $this, 'mdp_multisite_info' ) );

        }

        /**
         * Return an instance of this class.
         * @since     1.0.0
         * @return    object    A single instance of this class.
         */
        public static function get_instance() {
            // If the single instance hasn't been set, set it now.
            if ( null == self::$instance ) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         * Load the plugin text domain for translation.
         * @since    1.0.0
         */
        public function load_plugin_textdomain() {
            $domain = $this->plugin_slug;
            $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
            load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
            load_plugin_textdomain( $domain, false, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
        }

        /**
         * Include our plugin dependencies
         *
         * @since 1.0.0
         */
        public function includes() {
            if ( $this->meets_requirements() ) {
                require_once( plugin_dir_path( __FILE__ ) . '/includes/class-multisite-directory-admin.php' );
                require_once( plugin_dir_path( __FILE__ ) . '/includes/multisite-directory-settings.php' );
            }
        }

        /**
         * Activation hook for the plugin.
         *
         * @since 1.0.0
         */
        public function activate() {
            if ( $this->meets_requirements() ) {
            }
        }

        /**
         * Deactivation hook for the plugin.
         *
         * @since 1.0.0
         */
        public function deactivate() {
        }


        public function meets_requirements() {
            global $wp_version;
            if ( version_compare( $wp_version, "3.9", "<" ) ) {
                wp_die( "'" . $this->name . "' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='" . admin_url() . "'>WordPress admin</a>." );
            } else {
                return true;
            }
            if ( !is_multisite() ) {
                wp_die( "'" . $this->name . "' requires a WordPress Multisite Environment and has been deactivated!<br /><br />Back to <a href='" . admin_url() . "'>WordPress admin</a>." );
            } else {
                return true;
            }
        }

        /**
         * Register and enqueue public-facing style sheet.
         *
         * @since    1.0.0
         */
        public function enqueue_styles() {
            wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( '/css/styles.css', __FILE__ ), array(), self::VERSION );
        }

        /**
         * Register and enqueues public-facing JavaScript files.
         *
         * @since    1.0.0
         */
        public function enqueue_scripts() {
            wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( '/js/iris-custom.js', __FILE__ ), array( 'jquery' ), self::VERSION );
        }

        /**
         *
         * @since    1.0.0
         */
        public function create_directory_page() {

            $main_site = is_main_site();
            switch_to_blog( $main_site );

            if( !get_page_by_title( 'Directory' ) ) {

                $mdp_dirpage = array(
                    'post_title' => 'post_title',
                    'post_type' => 'page',
                    'post_status' => 'publish'
                );
                wp_update_post( $mdp_dirpage );
                restore_current_blog();
                }
        }


        public function mdp_multisite_info() {
            global $wpdb;
            global $post;

            $sites = wp_get_sites( array( 'network_id' => $wpdb->siteid ) );

            foreach ( $sites as $site ) {
                switch_to_blog( $site[ 'blog_id' ] );

                if ( isset( $site[ 'domain' ], $site[ 'path' ] ) ) {
                    $url = $site[ 'domain' ] . $site[ 'path' ];
                } else {
                    $url = site_url();
                }

                $sites_info[ $site[ 'blog_id' ] ] = array(
                    'url' => $url,
                    'name' => get_bloginfo( 'name' ),
                    'desc' => get_bloginfo( 'description' ),
                    'rss' => get_bloginfo( 'rss2_url' ),
                    'comments_rss' => get_bloginfo( 'comments_rss2_url' ),
                );
                restore_current_blog();
            }

            if ( $post->post_name == 'directory' ) {

                $content = '';

                $content .= '<div id="mdp-site-directory" class="mdp-site"><ul>';

                foreach ( (array)$sites_info as $site_id => $site_info ) {

                    $content .= '<li class="mdp-site-name">';
                    $content .= '<a href="' . esc_url( $site_info[ 'url' ] ) . '">';
                    $content .= esc_html( $site_info[ 'name' ] );
                    $content .= '</a></li>';
                }
                $content .= '</ul></div>';
                return $content;
            }

        }
    }
    $_GLOBALS['Multisite_Directory'] = new Multisite_Directory;
}