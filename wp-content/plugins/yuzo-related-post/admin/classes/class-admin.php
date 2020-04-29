<?php
namespace YUZO\Admin;
use YUZO\Core\YUZO_Core as yuzo;

if( ! class_exists( 'Yuzo_Admin' ) ){
/**
 * |--------------------------------------------------------------------------
 * | The admin-specific functionality of the plugin.
 * |--------------------------------------------------------------------------
 * Defines the plugin name, version, hooks for how to
 * enqueue the admin-specific stylesheet, javaScript and admin processes
 *
 * @since 		6.0         2019-04-13  Release
 * @since       6.0.9.84    2019-11-25  Class code reordering
 * @package 	YUZO
 * @subpackage 	YUZO/Admin
 */
class Yuzo_Admin{
    private
    /**
     * The ID
     * @since   6.0   2019-04-13    Release
     * @access  private
     * @var     string
     */
    $name,
    /**
     * The version
     *
     * @since    6.0       2019-04-13   Release
     * @access   private
     * @var      string    $version     The current version of this plugin.
     */
    $version;

    public static
    /**
     * Indicates that if the login user can have permission
     * to load panel setting.
     *
     * @since    6.0    2019-04-13  Release
     * @access   public
     * @var      bool
     */
    $is_user_panel_setting = false;


    /**
     * Initialize the class and set its properties.
     *
     * @since    6.0       2019-04-13   Release
     * @since    6.1.22    2020-01-02   The update to the Pro version was removed, this was a mistake
     *
     * @param    string    $name        The name of this plugin.
     * @param    string    $version     he version of this plugin.
     * @return   void
     */
    public function __construct( $name, $version ) {

        $this->name    = $name;
        $this->version = $version;

        // ─── Load Core Files ────────
        if( self::isUserAllow() ){
            $this->load_files_admin();
        }

    }

    /**
     * Validate a user according to the role he has
     * default is now the 'administrator'
     *
     * @since       6.0         2019-04-13  Release
     * @since       6.0.9.4     2019-07-27 01:13:04     Multisite version settings
     * @access      public
     * @static
     *
     * @return boolean
     */
    public static function isUserAllow(){

        if( is_network_admin() ){
            if( ! (defined('LOGGED_IN_COOKIE') && isset($_COOKIE['LOGGED_IN_COOKIE'])) ) return;
            if( ! (defined('SECURE_AUTH_COOKIE') && isset($_COOKIE['SECURE_AUTH_COOKIE'])) ) return;
        }

        if( ! function_exists('wp_get_current_user' ) ) {
            require_once ABSPATH . "wp-includes/pluggable.php" ;
        }
        $allowed_roles = apply_filters( YUZO_ID . '_role_user_allow',array('administrator') );
        $user          = wp_get_current_user();
        $is_user_admin = array_intersect($allowed_roles, $user->roles );
        $is_value      = ( is_admin() || is_customize_preview() || $is_user_admin );
        if( $is_value ){ self::$is_user_panel_setting = true; }
        return $is_value;
    }

    /**
     * Register the stylesheets and script for the admin area.
     *
     * @since   6.0         2019-04-13  Release
     * @since   6.0.9.84    2019-10-06  If the DEBUG option is activated within Yuzo then it does not minify the files
     * @since   6.1.52      2020-02-09  News var for localize script
     *
     * @return  void
     */
    public function enqueue_script_styles() {

        $min = ! YUZO_MODE_DEV && ( !isset(yuzo::instance()->settings->general_mode_debug) || ! yuzo::instance()->settings->general_mode_debug) ? '.min' : '';
        /**
         * An instance of this class should be passed to the run() function
         * defined in Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style( $this->name, YUZO_URL . 'admin/assets/css/yuzo' . $min . '.css', array(), $this->version, 'all' );
        wp_enqueue_script( $this->name . '-plugins', YUZO_URL . 'admin/assets/js/yuzo-plugins' . $min . '.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->name, YUZO_URL . 'admin/assets/js/yuzo.min.js', array( 'jquery', $this->name . '-plugins' ), $this->version, true );

        // Var JS
        $args_localizer = array(
            'url' => YUZO_URL, 'type' => 'LITE', 'version' => YUZO_VERSION, 'aip' => yuzo_get_real_ip(), 'id' => YUZO_ID,
            'nonce_preview' => wp_create_nonce('yuzo_preview_nonce'), 'updates' => (int) yuzo_get_option('yuzo-config','update_count'),
            'desc' => get_bloginfo( 'description', 'display' ), 'host' => parse_url(home_url(), PHP_URL_HOST),
            'title' => get_bloginfo('name'), 'nonce'  => (yuzo_get_option('yuzo-config','nonce_api')),'di' => yuzo_get_option('yuzo-config','date_install'),
        );
        wp_localize_script( $this->name  , 'yuzo_vars', $args_localizer );
    }

    /**
     * Load the necessary files so that the public
     * start working correctly.
     *
     * @since   6.0     2019-04-13 16:42:07     Release
     *
     * @return  void
     */
    public function load_files_admin(){
        // ─── Load functions ────────
        $files[] = [
                    'admin/functions/helpers',
                    'admin/functions/actions',
                ];

        // Fetch ↓
        foreach($files as $x => $file)
            foreach($file as $key => $value)
                $this->load_file( YUZO_PATH . '/' . $value .'.php' );
    }

    /**
     * Upload a file sent by a route
     *
     * @since   6.0	    2019-03-22 02:45:13 Release
     *
     * @param   string                      $file Name or path of the file
     * @return  void
     */
    public function load_file( $file ){
        require_once $file;
    }

    /**
     * Performs functions when activating the plugin
     *
     * @since   6.0                 2019-04-13  Release
     * @hook    activated_plugin
     * @access  public
     * @see     YUZO -> define_admin_hooks
     */
    public static function admin_activated_plugin(){
        \YUZO\Core\Yuzo_Activator::activate();
    }

    public static function admin_save_and_display_error_to_install(){
        \YUZO\Core\Yuzo_Activator::save_and_display_error();
    }

    /**
     * Perform processes after an update
     *
     * @since   6.0     2019-04-13  Release
     * @since   6.1.36  2020-01-08  The second parameter was added because it was not working for the update
     * @return void
     */
    public static function after_upgrade_plugin( $upgrader_object, $options ){
        // ─── The path to our plugin's main file ────────
        $our_plugin = YUZO_BASENAME;
        // ─── If an update has taken place and the updated type is plugins and the plugins element exists ────────
        if( is_array($options) && $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
            // ─── Iterate through the plugins being updated and check if ours is there ────────
            foreach( $options['plugins'] as $plugin ) {
                if( $plugin == $our_plugin ) {
                    // ─── Verify changes in the table of the database if you have it ────────
                    self::check_update_table_db();
                    // ─── Update new configuration information ────────
                    self::check_update_config();
                }
            }
        }
    }

    /**
     * Verify new structure changes in the plugin table
     *
     * @since   6.0     2019-04-13  Release
     * @since   6.1.40  2020-01-12  The function was changed to public
     * @access  private
     */
    public function check_update_table_db(){
        \YUZO\Core\Yuzo_Activator::create_tables();
    }

    /**
     * Processes when updating the plugin
     * @since   6.1.40  2020-01-12  Doc.
     *
     * @return void
     */
    public function check_update_config(){
        \YUZO\Core\Yuzo_Activator::register_config();
        \YUZO\Core\Yuzo_Activator::update_config();
    }

    /* public function yuzo_filter_cron( $schedules ){
        $schedules['every_day_yuzo_1'] = array(
            'interval'  => 60, // * 60 * 24,
            'display'   => __( '', '' )
        );
        return $schedules;
    } */

    /**
     * Load pixel framework
     * Create an interface setting|menus|others...
     * This is only allowed for users who are inside the Admin or users that are log in.
     *
     * @since   6.0   2019-04-13 16:29:57    Release
     * @author  Lenin Zapata (Pixel) <leninzapatap@gmail.com>
     */
    private function load_framework(){
        // ─── It serves to create CPT ────────
        require_once YUZO_PATH . 'admin/framework/posttypes/Columns.php';
        require_once YUZO_PATH . 'admin/framework/posttypes/Taxonomy.php';
        require_once YUZO_PATH . 'admin/framework/posttypes/PostType.php';
        // ─── Load the general framework ────────
        require_once YUZO_PATH . 'admin/framework/pixel/pixel-framework.php';
    }
} }