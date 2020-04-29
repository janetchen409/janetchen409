<?php
/**
 * @since 		6.0         2019-04-13      Release
 * @since       6.0.9.7     2019-08-01      -Validation added to work only with PHP7
 *                                          -Log functions were added to track Yuzo processes
 * @since       6.0.9.83    2019-10-04      Tabulation in the class for better interpretation.
 *
 * @package 	YUZO
 * @subpackage 	YUZO/Core
 */
// ───────────────────────────
namespace YUZO\Core;
// ───────────────────────────
if( ! class_exists( 'YUZO_Core', false ) ){
/*
|--------------------------------------------------------------------------
| Main Class
|--------------------------------------------------------------------------
|
| This is the initial and main plugin class, here is
    | The bases for the operation of everything are executed.
|
*/
final class YUZO_Core{

    /**
     * Existing instance
     *
     * Instance of the main class of the plugin
     * with this (deny duplicate).
     *
     * @access protected
     * @since   6.0   2019-04-13 16:21:27     Release
     * @var     object|mixed
     */
    protected static $instance;

    public
    /**
     * The ID|Slug plugin
     * @since   6.0         2019-04-13 16:21:27     Release
     * @access  public
     * @var     string
     */
    $name = YUZO_ID,
    /**
     * Version
     * @since   6.0         2019-04-13 16:21:27   Release
     * @access  public
     * @var     string|int
     */
    $version = YUZO_VERSION,
    /**
     * Version database
     * @since   6.0         2019-04-13 16:21:27     Release
     * @access  public
     * @var     string|int
     */
    $version_db = YUZO_VERSION_DB,
    /**
     * URL public plugin
     * @since   6.0     2019-04-13 16:21:27     Release
     * @access  public
     * @var     string
     */
    $url = YUZO_URL,
    /**
     * URL server plugin
     * @since   6.0     2019-04-13 16:21:27     Release
     * @access  public
     * @var     string
     */
    $path = YUZO_PATH,
    /**
     * Maintains and registers all hooks for the plugin
     * @since   6.0     2019-04-13 16:21:27     Release
     * @access  public
     * @var     object
     */
    $loader,
    /**
     * Refers to the admin object of the plugin
     * @since   6.0     2019-04-13 16:21:27     Release
     * @access  public
     * @var     object
     */
    $admin,
    /**
     * Customize the administration (aesthetic part)
     * @since   6.0        2019-04-01 10:08:07      Release
     * @access  public
     * @var     object
     */
    $admin_custom,
    /**
     * Refers to the public object of the plugin
     * @since   6.0     2019-04-13 16:21:27     Release
     * @access  public
     * @var     object
     */
    $public,
    /**
     * Get all general options
         * of the application.
    * @var mixed|object
    */
    $options = null,

    /**
     * Variable that contains the setting main
     *
     * @since 	6.0
     * @access 	public
     * @var 	Yuzo    $options    Get settings from the database
     */
    $settings,
    /**
     * Object that records plugin logs
     *
     * @since   6.0.9.7     2019-07-31 14:12:01     Release
     * @access  public
     * @var     phpConsole  Library to register logs in the javascript console
     */
    $logs = null;

    /**
     * Get class instance (Singleton is Rock!)
     *
     * @since 	6.0     2019-04-13 16:21:27     Release
     * @return 	object
     */
    public static function instance() {
        if ( ! isset( static::$instance ) || !static::$instance ) {
            static::$instance = new static();
            static::$instance->init();
        }
        return static::$instance;
    }

    /**
     * Initializer of the class.
     *
     * @since   6.0     2019-04-13 16:21:27     Release
     * @return  void
     */
    public function init(){

        // ─── Init action ────────
        do_action( 'YUZO_init' );

        // ─── Load Core Files ────────
        $this->load_files_core();

        // ─── Load General Setting ────────
        $this->get_options();

        // ─── Load Setup ────────
        $this->setup();

    }

    /**
     * Load Setup initial plugin
     *
     * @since   6.0     2019-04-13 16:21:27     Release
     * @return void
     */
    public function setup(){
        $this->register_logs();
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the necessary files so that the core
         * start working correctly.
    *
    * @since	6.0     2019-04-13 16:21:27     Release
    * @since   6.0.9.7 2019-08-01 23:00:12     Now the libraries are loaded first since they have no dependency
    * @return  void
    */
    public function load_files_core(){
        // ─── Load framework ────────
        $files[] = ['admin/framework/pixel/pixel-framework'];

        // ─── Load libs (no dependency) ────────
        $files[] = [
            'include/lib/SqlQueryBuilder',
            'include/lib/Session',
            'include/lib/phpConsole',
        ];

        // ─── Load functions ────────
        $files[] = [
            'include/functions/sanitize',
            'include/functions/helper',
            'include/functions/actions',
            'include/functions/validate',
        ];

        // ─── Load options plugin ────────
        $files[] = ['admin/options/init'];

        // Fetch ↓
        foreach( $files as $x => $file )
            foreach($file as $key => $value)
                self::load_file( $this->path . '/' . $value .'.php' );

    }

    /**
     * Upload a file sent by a route
     *
     * @since   6.0	    2019-04-13 16:21:27     Release
     *
     * @param   string  $file                   Name or path of the file
     * @return  void
     */
    public static function load_file( $file ){
        require_once $file;
    }

    /**
     * Load the initial dependencies
         * of the plugin, these are environment variables
    *
    * @since	6.0	    2019-04-13 16:21:27     Release
    * @return	void
    */
    protected function load_dependencies(){
        // ─── Set hooks and filters ────────
        $this->loader = new \YUZO\Core\Yuzo_Loader;

        // ─── Set admin functions ────────
        $this->admin = new \YUZO\Admin\Yuzo_Admin( $this->get_plugin_name(), $this->get_version() );

        // ─── Set admin aesthetics ────────
        $this->admin_custom = new \YUZO\Admin\Yuzo_AdminCustom( $this->get_plugin_name(), $this->get_version() );

        // ─── Set and load class public ────────
        $this->public = new \YUZO\Publi\Yuzo_Public( $this->get_plugin_name(), $this->get_version() );
    }

    /**
     * Get ID plugin
     * The name|slug of the plugin
     *
     * @since   6.0     2019-04-13 16:21:27     Release
     * @return  string
     */
    public function get_plugin_name() {
        return $this->name;
    }

    /**
     * Retrieve the version number of the plugin
     * The version number of the plugin.
     *
     * @since   6.0     2019-04-13  Release
     * @since   6.1     2019-12-14  Better validation attached to the user setting
     * @return  string
     */
    public function get_version() {
        $if_prod = ! YUZO_MODE_DEV && ( !isset(self::instance()->settings->general_mode_debug) || ! (int)self::instance()->settings->general_mode_debug) ? '.min' : '';
        return  $if_prod ? $this->version : rand(99,999);
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the YUZO_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since   6.0     2019-04-13 16:21:27    Release
     * @access  private
     */
    private function set_locale() {
        $plugin_i18n = new \YUZO\Core\Yuzo_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
        $this->logs->log("Load language");
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since   6.0         2019-04-13  Release
     * @since   6.0.9.6     2019-07-28  The 'in_admin_header' hook was added for the header design
     * @since   6.0.9.7     2019-08-01  It was added that the new library is loaded in the hook 'admin_footer'
     * @since   6.0.9.8     2019-08-28  New header filter added
     * @since   6.1.37      2020-01-08  'upgrader_process_complete' added for 2 parameters
     * @since   6.1.40      2020-01-12  The 'pixel_options_yuzo-setting_buttons_before' hook is added to add the Donate button.
     * @since   6.1.52      2020-02-09  Add hook for feedback
     * @access  private
     */
    private function define_admin_hooks() {
        // ─── Load style and script ────────
        $this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_script_styles' );

        // ─── Load functionalities (framework)    ↓ ────────
        $this->loader->add_action( 'pixel_options_yuzo_buttons_after', $this->admin_custom, 'add_button_support' );

        // ───Load other actions ────────
        // Change the order of Yuzo submenus
        $this->loader->add_action( 'custom_menu_order', $this->admin_custom, 'order_submenu' );
        // ─── Load functionalities (framework)    ↓ ────────
        $this->loader->add_action( 'pixel_options_yuzo-setting_buttons_before', $this->admin_custom, 'button_donate_options' );
        $this->loader->add_action( 'activated_plugin', $this->admin, 'admin_activated_plugin' );
        $this->loader->add_action( 'upgrader_process_complete', $this->admin, 'after_upgrade_plugin', 10, 2 );
        $this->loader->add_action( 'admin_bar_menu',  $this->admin_custom, 'create_adminbar_menu_yuzo_views', 150 );
        $this->loader->add_action( 'in_admin_header',  $this->admin_custom, 'yuzo_header_in_cpt', 150 );
        $this->loader->add_action( 'admin_footer',  $this->logs, 'print_console', 99999 );
        $this->loader->add_action( 'admin_footer',  $this, 'mgschangelog', 99999 );
        $this->loader->add_action( 'admin_footer',  $this->admin_custom, 'form_feedback_uninstall', 99999 );
        $this->loader->add_action( 'pf_yuzo-setting_before_header',  $this->admin_custom, 'header_in_setting' );
        $this->loader->add_action( 'manage_posts_extra_tablenav',  $this->admin_custom, 'footer_extra_list_posts' );
		$this->loader->add_filter( 'plugin_action_links_'.YUZO_BASENAME, $this->admin_custom, 'actionLinks' );
        //$this->loader->add_action( 'yuzo_add_every_day_event',  $this->admin_custom, 'yuzo_func_cron' );
        //$this->loader->add_filter( 'cron_schedules',  $this->admin, 'yuzo_filter_cron' );
        //$this->loader->add_action( 'admin_footer',  $this->admin_custom, 'footer_side_yuzo' );

        //$this->loader->add_action( 'activated_plugin', $this->admin, 'admin_save_and_display_error_to_install' );
        // ─── AJAX actions ────────
        add_action( 'wp_ajax_yuzo-save-click', 'yuzo_save_click' );
        add_action( 'wp_ajax_nopriv_yuzo-save-click', 'yuzo_save_click' );

        add_action( 'wp_ajax_yuzo-save-view', 'yuzo_save_view' );
        add_action( 'wp_ajax_nopriv_yuzo-save-view', 'yuzo_save_view' );

        $this->logs->log("Load admin hook");
    }

    /**
     * Subtly change the look when the plugin is updated
     * @since   6.1.31  2020-01-04      Release
     * @since   6.1.38  2020-01-08      The official website link is added in the PRO label
     */
    public function mgschangelog(){
        global $pagenow;
        if( isset($_GET['plugin']) && $_GET['plugin'] == 'yuzo-related-post' ){ ?>
<style>
#section-changelog.section ul{
    position: relative;
    counter-reset: gradient-counter;
    list-style: none;
    border-bottom: 1px solid #d1d5e94a;
}
#section-changelog.section ul > li{
    counter-increment: gradient-counter;
    color: #626274;
}
#section-changelog.section ul > li:before {
    content: counter(gradient-counter);
    position: absolute;
    left: -26px;
    background: #d1d5e9; /* linear-gradient(135deg, #2c83b7 0%,#3fd0e7 100%); */
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    display: inline-block;
    line-height: 1rem;
    color: white;
    text-align: center;
    padding: 3px;
    font-size: .8em;
    margin-right: 0.5rem;
    font-weight: bold;
}
#section-changelog.section ul > li > span{
    background: linear-gradient(45deg, #4f94d4, #3989cc85);
    padding: 1px 6px;
    border-radius: 3px;
    font-weight: bold;
    font-size: .8em;
    font-style: italic;
    color: #fff;
    transition: 100ms all cubic-bezier(0, 0, 0.35, 0.94);
    position: relative;
    display: inline-block;
}
#section-changelog.section ul > li > span > a{
    text-decoration: none;
    color: #fff;
}
#section-changelog.section ul > li > span:hover{
    transform: translateY(-2px);
}
#section-changelog > p:nth-child(1) a:nth-child(1){
    background: #4FD49D;
    background: #7dd27d;
    color: #fff;
    border-color: green;
}
</style>
<script>
jQuery("#section-changelog > p:nth-child(1) a").addClass('button button-primary');
var text_button = jQuery("#section-changelog > p:nth-child(1) a:nth-child(1)").text();
var text_button2 = jQuery("#section-changelog").children('p').eq(0).children('a').eq(1).text();
jQuery("#section-changelog").children('p').eq(0).children('a').eq(0).text( '🙋🏻 ' + text_button + ' 🙋🏻‍♂️' );
jQuery("#section-changelog").children('p').eq(0).children('a').eq(1).text( '✅ ' + text_button2 + ' 📈' );
jQuery("#section-changelog > p:nth-child(1) a").attr("target","_blank");
jQuery("#section-changelog > p:nth-child(1) a").attr("style","width:100%;font-size:12px;line-height:38px;");
jQuery("#section-changelog > p:nth-child(1)").attr("style","text-align:center;")

jQuery('#section-changelog > ul > li').each(function() {
    var text = jQuery(this).text();
    jQuery(this).html(text.replace('[PRO]', '<span class="section-changelog-label-pro"><a href="http://bit.ly/3797KWQ" target="_blank" >PRO</a></span>'));
});

</script>
        <style>
/*#section-changelog{background: red;}*/
        </style>
        <?php }
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since   6.0     2019-04-13 16:21:27     Release
     * @access  private
     */
    private function define_public_hooks() {
        $this->loader->add_action( 'wp_enqueue_scripts', $this->public, 'enqueue_script_styles' );
        $this->loader->add_action( 'wp_footer',  $this->logs, 'print_console', 99999 );
        $this->logs->log("Load public hook");
        $this->logs->groupEnd();
    }

    /**
     * Load options general
     *
     * @since	6.0	            2019-03-23  Release
     * @since   6.1             2019-12-14  Validate if variable setting is already loaded or not
     * @return	object|mixed
     */
    public function get_options(){
        return ! $this->settings ? $this->settings = \yuzo_get_option() : $this->settings;
    }

    /**
     * Start an instance to register logs of the plugin processes.
     *
     * @since   6.0.9.7     2019-07-31 23:39:39     Release
     * @since   6.0.9.8     2019-08-28              New log files added
     * @return  null
     */
    private function register_logs(){
        // ─── Consolelog ────────
        $this->logs = new \phpConsole;
        $this->logs->new('Yuzo v' . YUZO_VERSION, YUZO_MODE_DEV || ( ! empty($this->settings->general_mode_debug) ? true : false ) );
        $this->logs->group('Core');
        $this->logs->log('Load file functions');
        $this->logs->log('Load file libs');
        // ───────────────────────────
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since   6.0   2019-04-13 16:21:27     Release
     * @return  void
     */
    public function run() {
        // Loads hook and filters
        $this->loader->run();
    }
}
}

/**
 * Initial validation that guarantees that Yuzo works with
 * PHP7 onwards.
 */
if( ! version_compare(phpversion(), '7.0', '>=') ){
    //! Fallback for very old php version
    add_action('admin_notices', function () {
    ?>
    <div class="notice notice-error">
        <p><?php _ex('Your PHP version is <a href="https://php.net/supported-versions.php" rel="noreferrer" target="_blank">outdated</a> and not supported by Yuzo. Please disable Yuzo, upgrade to PHP 7.0 or higher, and enable Yuzo again. It is necessary to follow these steps in order.', 'Status message', 'yuzo'); ?></p>
    </div>
    <?php
    } );
}else{
    // Run!
    global  $YUZO;
    $YUZO 	= YUZO_Core::instance();
    $YUZO->logs->success("Run! ⬇️");
    $YUZO->run(); //!! 💯
}