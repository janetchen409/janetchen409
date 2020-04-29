<?php
namespace YUZO\Publi;
use YUZO\Publi as yuzo_publi;
use YUZO\Core\YUZO_Core as yuzo;

if( ! class_exists( 'Yuzo_Public' ) ){
/**
 * --------------------------------------------------------------------------
 * The public-facing functionality of the plugin.
 * --------------------------------------------------------------------------
 *
 * Defines the name, version and hooks for how to
 * enqueue the public-facing stylesheet and javaScript.
 *
 * This class also dedicates only to bar (top or bottom)
 *
 * @since 		6.0         2019-04-13  Release
 * @since       6.0.9.84    2019-10-06  Class tabulation for better compression
 *
 * @package 	YUZO
 * @subpackage 	YUZO/Public
 */
class Yuzo_Public{
    private
    /**
     * The ID
     * @since   6.0     2019-04-13 16:37:03     Release
     * @access  private
     * @var     string
     */
    $name,
    /**
     * The version
     *
     * @since    6.0        2019-04-13 16:37:03
     * @access   private
     * @var      string     $version    The current version of this plugin.
     */
    $version,
    /**
     * Get settings general
     * @var object|mixed
     */
    $settings;

    /**
     * Variable that contains the Related Algorithm class
     *
     * @since 	6.0
     * @access 	public
     * @var 	Yuzo    $related
     */
    public $related_algoritm,

    /**
     * Variable that contains the Display class
     *
     * @since 	6.0
     * @access 	public
     * @var 	Yuzo    $related
     */
    $related_display,

    /**
     * Variable that contains the Template class
     *
     * @since 	6.0
     * @access 	public
     * @var 	Yuzo    $related
     */
    $related_template,

    /**
     * Variable that contains a array of all options
     *
     * @since 	6.0
     * @access 	public
     * @var 	Yuzo    $related_options
     */
    $related_options = [],
    /**
     * Shortcodes
     * @since   6.0     2019-07-02 17:10:26     Release
     * @access  public
     * @var     object
     */
    $shortcode;

    /**
     * Initialize the class and set its properties.
     *
     * @since   6.0         2019-04-13  Release
     * @since   6.0.5       2019-07-12  Remove feed validation
     * @since   6.1         2019-12-13  Add the customizable CSS print hook
     *
     * @param    string     $name 		The name of this plugin.
     * @param    string     $version 	The version of this plugin.
     * @return   void
     */
    public function __construct( $name, $version ) {

        $this->name     = $name;
        $this->version  = $version;
        $this->settings = yuzo::instance()->settings;
        yuzo::instance()->logs->log("Public class instance");

        // ─── Core Files ────────
        $this->load_files_core();

        // Get all the options from the list of the related post
        $this->related_options   = new yuzo_publi\Yuzo_Options;
        yuzo::instance()->logs->log("Public ⇨ Options class instance");

        // Load class display
        $this->related_display   = new yuzo_publi\Yuzo_Display;
        yuzo::instance()->logs->log("Public ⇨ Display class instance");

        // Load class Related Algorithm
        $this->related_algorithm = new yuzo_publi\Yuzo_Related;
        yuzo::instance()->logs->log("Public ⇨ Algorithm class instance");

        // Load class display
        $this->related_template  = new yuzo_publi\Yuzo_Template;
        yuzo::instance()->logs->log("Public ⇨ Template class instance");

        // ─── Set var for class shortcode ────────
        $this->shortcode = new yuzo_publi\Yuzo_Shortcode;
        yuzo::instance()->logs->log("Public ⇨ Shortcode class instance");

        // ─── Enqueue ────────
        yuzo::instance()
            ->loader
            ->add_action( 'wp_enqueue_scripts', $this, 'enqueue_script_styles' );
        yuzo::instance()
            ->loader
            ->add_action( 'wp_head', $this->related_template, 'header_css', 99 );
        yuzo::instance()
            ->loader
            ->add_action( 'init', $this->shortcode, 'yuzo_shortcodes' );
        yuzo::instance()
            ->loader
            ->add_action( 'the_content', $this, 'set_views_in_content' );
        yuzo::instance()
            ->loader
            ->add_action( 'wp_head', $this, 'show_views_in_content', 99 );

        // ─── Print custom css in head ────────
        yuzo::instance()->loader->add_action( 'wp_head' , $this , 'custom_css', 991 );

        // ─── Show the related post in the content ────────
        yuzo::instance()->loader->add_action( 'wp_head' , $this->related_display , 'show_related_post', 98 );
                                            //└──────┘ → This hook is key and first that of the content
        yuzo::instance()->logs->log("Public ⇨ Hook loaded");

        /* add_filter( 'https_local_ssl_verify', '__return_false' );
        add_filter( 'https_ssl_verify', '__return_false' );
        add_filter( 'block_local_requests', '__return_false' ); */
    }

    /**
     * Register the JavaScript and Styles for the public-facing side of the site
     *
     * @since   6.0     2019-04-25 23:53:37     Release
     * @since   6.0.5   2019-07-12 22:19:44     The variable was added 'allows_to_count_visits' where it validates whether or not this visit is by post type
     * @since   6.0.8   2019-07-16 09:37:00     Check if this post is disabled count
     * @since   6.0.9   2019-07-22 22:05:52     Sending of the direct variable of article level
     */
    public function enqueue_script_styles() {

        global $post;

        $min = ! YUZO_MODE_DEV && ( !isset(yuzo::instance()->settings->general_mode_debug) || ! yuzo::instance()->settings->general_mode_debug) ? '.min' : '';
        $scripts = [
            ['handle' => $this->name . '-pixel-geo', 'src'=> YUZO_URL . 'public/assets/js/pixel-geo' . $min . '.js', 'dep'=>array( 'jquery' ), 'ver'=> $this->version , 'in_foot'=>true],
            ['handle' => $this->name . '-js', 'src'=> YUZO_URL . 'public/assets/js/yuzo' . $min . '.js', 'dep'=>array( 'jquery' ), 'ver'=> $this->version , 'in_foot'=>true],
        ];

        for ($i=0; $i < sizeof($scripts); $i++) {
            wp_enqueue_script( $scripts[$i]['handle'], $scripts[$i]['src'], $scripts[$i]['dep'], $scripts[$i]['ver'], $scripts[$i]['in_foot'] );
        }
        yuzo::instance()->logs->log("Public ⇨ Hook ⇨ Script loaded");

        // Var JS
        $post_type_allow = empty( $this->settings->general_cpt_to_counter ) ? ['post','page'] : $this->settings->general_cpt_to_counter;
        $args_localizer = array(
            'ajaxurl'                => admin_url( 'admin-ajax.php' ),
            'post_id'                => is_singular() && ! empty( $post ) ? $post->ID : 0,
            'url'                    => urlencode( yuzo_current_location() ),
            'where_is'               => implode( "|", (array) yuzo_get_query_flags() ),
            'nonce'                  => wp_create_nonce( 'yuzo-click' ),
            'nonce2'                 => wp_create_nonce( 'yuzo-view' ),
            'off_views'              => ! empty( $this->settings->general_disabled_counter_view ) ? 1 : 0,
            'off_views_logged'       => ! empty( $this->settings->general_disabled_view_loggin ) ? 1 : 0,
            'is_logged'              => is_user_logged_in() ? 1 : 0,
            'allows_to_count_visits' => \in_array( $post->post_type, (array)$post_type_allow ) ? 1 : 0,
            'disabled_counter'       => yuzo_disabled_counter( ( ! empty( $post ) ? $post->ID : 0 ), $this->settings ),
            'level_article'          => 'medium',
        );
        wp_localize_script( $this->name . '-js' , 'yuzo_vars', $args_localizer );
        yuzo::instance()->logs->log("Public ⇨ Hook ⇨ localize_script loaded");

        // Style
        wp_enqueue_style( $this->name . '-css', YUZO_URL . 'public/assets/css/yuzo' . $min . '.css', array(), $this->version, 'all' );
        yuzo::instance()->logs->log("Public ⇨ Hook ⇨ style loaded");
    }

    /**
     * Load the necessary files so that the public
         * start working correctly.
        *
        * @since   6.0     2019-04-25 20:37:33     Release
        *
        * @return void
        */
    public function load_files_core(){
        // ─── Load lib ────────
        $files[] = ['include/lib/SqlQueryBuilder',];
        // ─── Load functions ────────
        $files[] = ['public/functions/helper',];
        // ─── Load public class ────────
        $files[] = [
            //'public/classes/class-options',
            /*'public/classes/class-display',
            'public/classes/class-related',
            'public/classes/class-template',
            'public/classes/class-shortcode',*/
        ];

        // Fetch
        foreach($files as $x => $file)
            foreach($file as $key => $value)
                if( ! empty( $value ) )
                    $this->load_file( YUZO_PATH . '/' . $value .'.php' );

        yuzo::instance()->logs->log("Public ⇨ Load files libs and functions");
    }

    /**
     * Upload a file sent by a route
     *
     * @since 6.0	2019-03-22 02:45:13 Release
     *
     * @param string $file Name or path of the file
     * @return void
     */
    public function load_file( $file ){
        require_once $file;
    }

    /**
     * Insert a non-traceable div by google to
     * that views can be displayed without affecting seo
     *
     * @since   6.1.3   2020-01-02  Reelease
     *
     * @return	string
     */
    public function set_views_in_content( $content = '' ){

        $opt =  ! empty( $this->settings->general_show_views ) ? $this->settings->general_show_views : 'views_top';
        $html_insert = '<div class="yzp-no-index"></div>';
        if( $opt == 'views_top' ){
            return $html_insert . $content;
        }elseif( $opt == 'views_bottom' ){
            return $content . $html_insert;
        }

        return $content;
    }

    /**
     * Show the visit counter in the form of CSS
     *
     * @since   6.1.3   2020-01-02  Release
     * @since   6.1.32  2020-01-05  The views icon is added
     *
     * @param   string  $content Hook content
     * @return  void
     */
    public function show_views_in_content( $content = '' ){
        global $post;
        $opt =  ! empty( $this->settings->general_show_views ) ? $this->settings->general_show_views : 'views_top';
        if( in_array( $opt, ['views_top','views_bottom'] ) ){

            /* Validate if the current post type is allowed to count views, if so, then show the counters */
            $post_type_allow = empty( $this->settings->general_cpt_to_counter ) ? ['post'] : $this->settings->general_cpt_to_counter;
            if ( ! in_array( get_post_type($post) , $post_type_allow ) ) return false;

            global $post;
            $views = yuzo_get_views((int)$post->ID, $this->settings);
            if( $views > 0){
                $text =  ! isset( $this->settings->general_show_views_text ) ? 'views {views}' : $this->settings->general_show_views_text;
                $text = str_replace('{views}',$views,$text);
                $text = str_replace('{icon}','👁',$text);
                echo "<style>.yzp-no-index:after { content:'{$text}';color: #635d5d;font-size: .95em;}</style>";
            }
        }
    }

    /**
     * Add custom CSS
     *
     * @since	6.1    2019-12-13  Release
     * @return	string
     */
    public function custom_css(){
        echo ! empty( $this->settings->custom_css ) ? '<style>' . $this->settings->custom_css . '</style>' : '';
    }

} }