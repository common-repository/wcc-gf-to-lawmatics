<?php
/*
 * Plugin Name: WCC GF to Lawmatics
 * Description: Send Your Gravity Form's Entries to Your Lawmatics Account With Ease.
 * Author: WeConnectCode
 * Author URI: https://weconnectcode.com/
 *
 * Text Domain: wcc-gf-lawmatics
 * Domain Path: languages
 * Version: 1.0.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
define("WCC_GF_LAWMATICS_PLUGIN_PATH", plugin_dir_path( __FILE__ )."/");
if ( file_exists( plugin_dir_path( __FILE__ ) . '/autoload.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . '/autoload.php';
}

if ( ! class_exists( 'WccGfLawmatics' ) ) {


    /**
     * Class WccGfLawmatics.
     *
     * @since 1.0.0
     */
    class WccGfLawmatics {

        /**
         * Plugin basename.
         *
         * @var string plugin basename
         *
         * @since 1.0.0
         * @access public
         */
        public $plugin;

        public static $modules = array(
            "contacts" => "Contacts" ,
        );
        public static $module = "contacts";

        public static $pages = array(
            "wcc_gf_lawmatics" ,
        );
        /**
         * Plugin version.
         *
         * @var string plugin version
         *
         * @since 1.0.0
         * @access public
         * @static
         */
        public static $wcc_gf_lawmatics_version = '1.0.0';


        /**
         * Plugin name.
         *
         * @var string plugin name
         *
         * @since 1.0.0
         * @access public
         * @static
         */
        public static $plugin_name = 'GF – Lawmatics';

        /**
         * Plugin site.
         *
         * @var string plugin site
         *
         * @since 1.0.0
         * @access public
         * @static
         */
        public static $plugin_site = 'https:://test.com';
        public static $plugin_url = 'https://www.weconnectcode.com/plugin/lawmatics-for-gravity-forms';

        /**
         * Plugin domain.
         *
         * @var string plugin domain
         *
         * @since 1.0.0
         * @access public
         * @static
         */
        public static $domain = 'wcc-gf-to-lawmatics';

        /**
         * Plugin prefix.
         *
         * @var string plugin prefix
         *
         * @since 1.0.0
         * @access public
         * @static
         */
        public static $prefix = 'wcc_gf_lawmatics_';


        /**
         * Shortcode name.
         *
         * @var string shortcode name
         *
         * @since 1.0.0
         * @access public
         * @static
         */
        public static $shortcode_widget = 'wcc_gf_lawmatics_shortcode';

        /**
         * Page for iframe embed.
         *
         * @var string page name
         *
         * @since 1.0.0
         * @access public
         * @static
         */
        public static $page_title = 'Lawmatics CRM Integration';

        /**
         * Page slug for iframe embed.
         *
         * @var string page slug
         *
         * @since 1.0.0
         * @access public
         * @static
         */
        public static $page_name = 'wcc_gf_lawmatics_settings';

   
        public static $authurl = 'https://app.lawmatics.com/oauth/authorize';
        public static $authTokenUrl = 'https://api.lawmatics.com/oauth/token';
        public static $apiUrl = 'https://api.lawmatics.com/v1/';
        /**
         * WccGfLawmatics constructor.
         *
         * @since 1.0.0
         * @access public
         */
        public function __construct() {
            $this->plugin = plugin_basename( __FILE__ );
        }

        /**
         * Register Platform functions.
         *
         * @since 1.0.0
         * @access public
         */
        public function register() {
            load_plugin_textdomain( self::$domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }


        public function uninstall() {
            $uninstall = get_option( 'wcc_gf_lawmatics_uninstall' );
            if ( $uninstall ) {
                delete_option( 'wcc_gf_lawmatics_notification_subject' );
                delete_option( 'wcc_gf_lawmatics_notification_send_to' );
                delete_option( 'wcc_gf_lawmatics_ignore_spam_entry' );
                delete_option( 'wcc_gf_lawmatics_uninstall' );

                global $wpdb;

                $table_name = esc_sql($wpdb->prefix . 'wcc_gf_lawmatics_accounts');
                
                $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS $table_name"));

                $table_name = esc_sql($wpdb->prefix . 'wcc_gf_lawmatics_feeds');
                
                $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS $table_name"));

                $table_name = esc_sql($wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_field');
                
                $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS $table_name"));

                $table_name = esc_sql($wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_history');
                
                $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS $table_name"));
            }
        }
        /**
         * Activate plugin.
         *
         * @since 1.0.0
         * @access public
         */
        public function activate() {
            global $wpdb;
            global $wcc_gf_lawmatics_db_version;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_accounts';  
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id          mediumint(9) NOT NULL AUTO_INCREMENT, 
                name     text NOT NULL, 
                client_id     text NOT NULL, 
                client_secret     text NOT NULL,
                authorization_code     text NOT NULL,
                date_added        datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, 
                date_updated        datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, 
                PRIMARY KEY  (id)
            ) $charset_collate;";
            dbDelta( $sql );



            $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds';  
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id          mediumint(9) NOT NULL AUTO_INCREMENT, 
                account_id     int(11) NOT NULL,
                form_id     varchar(200) NOT NULL,
                name     text NOT NULL, 
                action     text NOT NULL, 
                module     text NOT NULL, 
                status     int(11) NOT NULL,
                date_added        datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, 
                date_updated        datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, 
                PRIMARY KEY  (id)
            ) $charset_collate;";
            dbDelta( $sql );




            $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_field';  
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id          mediumint(9) NOT NULL AUTO_INCREMENT, 
                feed_id     int(11) NOT NULL,
                crm_field     text NOT NULL, 
                fields_type     text NOT NULL, 
                value     text NOT NULL, 
                PRIMARY KEY  (id)
            ) $charset_collate;";
            dbDelta( $sql );



            $table_name = $wpdb->prefix . 'wcc_gf_lawmatics_feeds_to_history';  
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id          mediumint(9) NOT NULL AUTO_INCREMENT, 
                feed_id     int(11) NOT NULL,
                entry_id     int(11) NOT NULL,
                connector_entry_id     text NOT NULL,
                data_sent     text NOT NULL, 
                response     text NOT NULL, 
                trigger_type     text NOT NULL, 
                object     text NOT NULL,
                status     int(11) NOT NULL,
                note     text NOT NULL,
                date_added        datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, 
                date_updated        datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, 
                PRIMARY KEY  (id)
            ) $charset_collate;";
            dbDelta( $sql );
            
            add_option( 'wcc_gf_lawmatics_db_version', $wcc_gf_lawmatics_db_version );
            flush_rewrite_rules();

            register_uninstall_hook( __FILE__, array($this,'uninstall') );
        }

        /**
         * Deactivate plugin.
         *
         * @since 1.0.0
         * @access public
         */

        /**
         * Debug function.
         *
         * @param string|array|bool|mixed $data data for debugging
         * @param bool $die die or not
         * @param bool $print 'print' or 'var_dump'
         *
         * @since 1.0.0
         * @access public
         * @ignore
         */
        public static function debug( $data, $die = true, $print = true ) {
            if ( $print ) {
                echo '<pre>';
                print_r( esc_html($data) );
                echo '</pre>';
            } else {
                var_dump( $data );
            }

            if ( $die ) {
                wp_die();
            }
        }


    }

    $platform = new WccGfLawmatics();
    $platform->register();


    $actions = new WccGfLawmatics_Actions();
    /**
     * Activate plugin.
     */
    register_activation_hook( __FILE__, array( $platform, 'activate' ) );
}
