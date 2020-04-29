<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 * CORE and Start of the Pixel Framework
 *
 * @package PF
 * @subpackage PF/Classes
 *
 * @since   1.0     2019-02-25 13:55:09     Release
 *
 * @since   1.2.2   2019-03-20 14:05:56     - Removed spaces in the file header.
 *                                          - Added '$current_instance_args' to get the object
 *                                            current instantiated.
 *                                          - New comments were updated also in the functions.
 *                                          - Extra validations in case there are no fields.
 * @since   1.2.7   2019-03-27 17:22:10     - Add var $errors_fields to group individual errors
 * @since   1.3.5   2019-04-16 12:28:44     The key 'pages' was added inside the variable $args_defaults
 * @since   1.4.4   2019-05-21 02:04:53     - The $inited variable is added for elements already started
 *                                          - The 'Widget' key is added as a new element
 * @since   1.4.5   2019-05-23 00:18:35     Added the function 'if there is a class' to prevent it from being charged more than once
 * @since   1.5     2019-07-14 13:17:00     Customizer functionality added
 * @since   1.5.3   2019-09-30              - Added options like taxonomy, shortcodes, profile options
 *                                          - The possibility of validating if a plugin exists is added to the kernel
 *                                          - Validation was fixed to get the custom field path
 * @since   1.6     2019-12-02              'Comment_metaboxes' was added for metabox within comments
 *
 */
if( ! class_exists('PF') ){
final class PF{

    public static
    $god            = array(
        // ─── framework title ────────
        'framework_title' => 'Pixel Framework <small>Lenin Zapata</small>',
        'framework_class' => '',
    ),
    /** Current directory @var string */
    $dir            = '',
    /** URL public of the framework @var string */
    $url            = '',
    /** Version of the framework @var string */
    $version        = '',
    /** Developer Mode activate @var bool */
    $dev_mode       = false,
    /** Sentinel if you already neighborhood the fields @var bool */
    $setup_fields   = false,
    /** Accumulate Name and Id of the plugin with its elements @var array */
    $elements       = array(),
    /** Accumulate all fields @var array  */
    $fields         = array(),
    /** Save items already started */
    $inited         = array(),
    /** Elements of the framework @var array */
    $args_defaults  = array(
        'options'           => array(),   // settings
        'metaboxes'         => array(),
        'widgets'           => array(),
        'customize_options' => array(),
        'shortcoders'       => array(),
        'taxonomy_options'  => array(),
        'pages'             => array(),
        'widgets'           => array(),
        'profile_options'   => array(),
        'comment_metaboxes' => array(),
    ),
    /** Add all elements and arguments of the current plugin @var array */
    $args = [],
    /**
     * Save the last key of the variable $ args used, that is:
     *  * If you add a setting 'addSetting' then this variable will have 'options'
     *  * If you add a metabox 'addMetabox' then this variable will have 'metaboxes'
     *  * ...etc...
     */
    $current_args = '',
    /**
     * Save in an array every Instanced Element
     */
    $args_instanced = null,
    /**
     * Save all the initial errors of the fields
     * @since 1.2.7 2019-03-27 17:23:35 Add var
     */
    $errors_fields = array(),
    /**
     * All shortcode instance
     * @since   1.5.3   2019-09-29
     * @var     array
     */
    $shortcode_instances     = array(),
    /**
     * All shortcode instance with keys
     * @since   1.5.3   2019-09-29
     * @var     array
     */
    $shortcode_instances_key = array();

    /**
     * Initializer of the class, call of the autoload.
     *
     * @since   1.0 2019-02-19 00:28:31 Release
     *
     * @return  void
     */
    public static function init(){

        // init action
        do_action( 'pf_init' );

        // Declare constants
        self::constants();

        // Load the framework autoload
        // (Inside a plugin or theme wordpress use this makes the system slow)
        /////self::autoload();

        // Core files
        self::load_files_core();

        // Load Hooks
        self::load_hooks();

    }

    /**
     * Load all the constant of the framework to be
         * used in different parts.
        *
        * @since	1.0	    2019-02-19 01:50:18     Release
        * @since    1.3.3   2019-04-07 09:45:38     Added the variable 'WP_MODE_DEV' to validate a global dev mode
        * @since    1.3.5   2019-04-16 12:31:02     -For the PATH and URL variables, the main file paths were taken
        *                                            this solves the problem of the routes, the https was not sent
        *                                           -The array of file data is vertically fixed
        * @since    1.4     2019-04-20 15:19:08     Mode developer update validation
        * @return	void
        */
    public static function constants() {
        // ─── We find the path and url publishes of the framework ────────

        self::$dir = PIXEL_PATH; //$dirname;
        self::$url = PIXEL_URL; //$directory_uri . $foldername;

        // ─── Get data from the header of the main framework file ────────
        $header         = array(
                                'name'        => 'Plugin Name',
                                'description' => 'Description',
                                'version'     => 'Version',
                                'author'      => 'Author',
                                'uri'         => 'Author URI',
                                'dev'         => 'Developer mode'
                                );
        $pf_header_data = get_file_data( self::$dir . '/pixel-framework.php', $header );

        /* ─── Version of the plugin from the main file header ────────
        |--------------------------------------------------------------------------
        | ! Developer Mode
        |--------------------------------------------------------------------------
        |
        | * If you are developing a change at the level of this framework you can
            | activate the developer mode so that the files type: css, js is not
            | cache in the browser randomly changed version. There are 2 ways:
        |
        | * From the header of the pixel-framework.php file adding any character
            |   to the 'Developer Mode' tag
            |   Activate developer mode by setting true in constant
            |   WordPress WP_DEBUG.
        |
        */
        self::$dev_mode = ( self::if_mode_developer() ) ? true : false;
        self::$version  = ! self::$dev_mode ? $pf_header_data['version'] : rand(111,999);
    }

    private static function if_mode_developer(){
        return WP_DEBUG || (defined('WP_MODE_DEV') && WP_MODE_DEV);
    }

    /**
     * Check if a plugin is active
     *
     * @param string $file
     * @return boolean
     */
    public static function is_active_plugin( $file = '' ) {
        return in_array( $file, (array) get_option( 'active_plugins', array() ) );
    }

    /**
     * Sanitize dirname
     *
     * @since	1.0	2019-02-19 01:40:46 Release
     *
     * @param string $dirname Route address
     * @return	string
     */
    public static function sanitize_dirname( $dirname ){
        return preg_replace( '/[^A-Za-z]/', '', $dirname );
    }

    /**
     * URL plugin folder static
     *
     * @param string $file
     *
     * @since 1.0 2019-02-28 10:45:35 Release
     * @return string
     */
    public static function include_plugin_url( $file ) {
        return self::$url .'/'. ltrim( $file, '/' );
    }

    /**
     * Autoload
     *
     * You do not need to use 'require_once' or 'include' functions
     * to call files, with the autoload I would call all the
     * system classes.
     *
     * @since	1.0	    2019-02-19 02:23:27     Release
     * @return  void
     */
    public static function autoload(){
        require_once self::$dir . '/functions/autoload.php';
    }

    /**
     * Load the necessary files so that the core
     * start working correctly.
     *
     * @since	1.0	    2019-02-19 19:01:28     Release
     * @since	1.1.2	2019-03-14 13:22:41     It was duplicated to include the abstract class 'class-abstract
     * @since   1.3.5   2019-04-16 12:34:43     The class for pages was added
     * @since   1.4     2019-04-20 15:19:46     Add file class-metabox
     * @since   1.4.4   2019-05-21 02:12:47     The class-widget file is added
     * @since   1.5     2019-07-14 13:17:57     Customized class is added
     * @since   1.5.3   2019-09-30              Classes for taxonomies, shortcode and user options were added
     * @since   1.6     2019-12-02              The file containing the 'Comment Metabox' class was added
     *
     * @return void
     */
    public static function load_files_core(){
        // ─── Load functions ────────
        $files[] = ['functions/sanitize',
                    'functions/helper',
                    'functions/actions',
                    'functions/validate',];
        // Fetch
        foreach($files as $file)
            foreach($file as $value)
                self::load_file( self::$dir . '/' . $value .'.php' );
        // ─── Load class ────────
        $files[] = ['classes/class-abstract',
                    'classes/class-fields',
                    'classes/class-widget',
                    'classes/class-customize-options',
                    'classes/class-shortcode',
                    'classes/class-taxonomy-options',
                    'classes/class-profile-options',
                    ];

        if( pf_is_user_login_allow() ){
            $files[] = ['classes/class-options',
                        'classes/class-pages',
                        'classes/class-metabox',
                        'classes/class-comment-metabox'];
        }
        // Fetch
        foreach($files as $file){
            foreach($file as $value){
                self::load_file( self::$dir . '/' . $value .'.php' );
            }
        }
    }

    /**
     * Upload a file sent by a route
     *
     * @since	1.0	2019-02-19 19:07:42 Release
     *
     * @param string $file Name or path of the file
     * @return void
     */
    public static function load_file( $file ){
        require_once $file;
    }

    /**
     * Load Textdomain
     *
     * @since	1.0	2019-02-19 20:09:33 Release
     * @return	void
     */
    public static function textdomain(){
        load_textdomain( 'pf', self::$dir .'/languages/'. get_locale() .'.mo' );
    }

    /**
     * Admin enqueue scripts
     *
     * @since	1.0	    2019-02-19  Release
     * @since   1.5.3   2019-09-30  - 'Jquery-ui-sortable' is added
     *                              - Translation variables are added using jquery
     *
     * @return	void
     */
    public static function admin_enqueue_scripts(){

        $min = ( self::$dev_mode ) ? '' : '.min';

        // ─── WP Media (upload, gallery, etc...) ────────
        wp_enqueue_media();

        // ─── WP Color Picker ────────
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        // ─── Autocomplete jQuery ────────
        // Enqueue jQuery UI and autocomplete
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'jquery-ui-spinner' );
        wp_enqueue_script( 'jquery-ui-sortable' );

        // ─── CDN Styles ────────
        wp_enqueue_style( 'pf-fa4', 'https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css', array(), self::$version, 'all' );

        // ─── Framework styles ────────
        wp_enqueue_style( 'pf', PF::include_plugin_url( 'assets/css/pf'. $min .'.css' ), array(), self::$version, 'all' );

        // ─── Javascript plugin ────────
        wp_enqueue_script( 'pf-plugins', self::$url . '/assets/js/pf-plugins' . $min . '.js' , array('jquery-ui-spinner'), self::$version, 'all' );

        // ─── Javascript Framework ────────
        wp_enqueue_script( 'pf', self::$url . '/assets/js/pf' . $min . '.js' , array(), self::$version, 'all' );

        // ─── Localize Script ────────
        wp_localize_script( 'pf', 'pf_vars', array(
            'color_palette'  => apply_filters( 'pf_color_palette', array() ),
            'i18n'           => array(
                'confirm' => esc_html__( 'Are you sure?', 'pf' ),
                'reset_notification'  => esc_html__( 'Restoring options.', 'pf' ),
                'import_notification' => esc_html__( 'Importing options.', 'pf' ),

                // chosen localize
                'typing_text'     => esc_html__( 'Please enter %s or more characters', 'pf' ),
                'searching_text'  => esc_html__( 'Searching...', 'pf' ),
                'no_results_text' => esc_html__( 'No results match', 'pf' ),
            ),
        ) );

        // ─── If the field has enqueue in its style class and script it executes it ────────
        $enqueued = array();
        if( ! empty( self::$fields ) ) {
            foreach( self::$fields as $field ) {
                if( ! empty( $field['type'] ) ) {
                    //$classname = 'PF\\Fields\\PF_Field_'. $field['type'];
                    $classname = 'PF_Field_' . $field['type'];
                    self::maybe_include_field( $field['type'] );
                    if( class_exists( $classname ) && method_exists( $classname, 'enqueue' ) ) {
                        $instance = new $classname( $field );
                        if( method_exists( $classname, 'enqueue' ) ) {
                            $instance->enqueue();
                        }
                        unset( $instance );
                    }
                }
            }
        }

        // ─── Add custom script o style ────────
        do_action( 'pf_enqueue' );

    }

    /**
     * Load hooks framework
     *
     * @since	1.0	    2019-02-20  Release
     * @since   1.6     2019-12-02  Style was added for correction of the new WP 5.3 design
     * @return	void
     */
    public static function load_hooks(){
        // ─── Sweep all the elements of the framework in different wordpress hook ────────
        add_action( 'init', array( __CLASS__, 'setup' ) );
        add_action( 'after_setup_theme', array( __CLASS__, 'setup' ) );
        add_action( 'switch_theme', array( __CLASS__, 'setup' ) );

        // ─── Add the written admin and styles ────────
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ), 10 );

        add_action( 'admin_head', array( __CLASS__, 'add_admin_head_css' ), 99 );
        add_action( 'customize_controls_print_styles', array( __CLASS__, 'add_admin_head_css' ), 99 );
    }

    /**
     * Execute framework fields
     *
     * @since   1.0     2019-02-20 17:52:09     Release
     * @since   1.3.5   2019-04-16 12:35:39     Now also ask if the plugin has pages
     * @since   1.4     2019-04-20 15:20:14     -Valid if there are no elements
     *                                          -Added the instance of pages and metabox
     * @since   1.4.4   2019-05-21 02:13:28     Add widget element
     * @since   1.5     2019-07-14 13:18:23     Element customize added
     * @since   1.5.3   2019-09-30              Validation was added to add elements such as: shortcodes, taxonomies, profile options
     * @since   1.6     2019-12-02              - The 'comment_metaboxes' section was added on the objects
     *                                          - There were objects that were not being assigned in their instances
     * @return  void
     */
    public static function setup() {

        // ─── This validation causes these validations to be executed only once ────────
        if( empty( self::$setup_fields ) ) {

            // welcome page
            //self::include_plugin_file( 'views/welcome.php' );

            // ─── Fetch Elements with Pixel Framework ────────
            if ( ! empty( self::$elements ) ){
                foreach (self::$elements as $idElement => $values) {
                    //echo json_encode( self::$elements );exit;
                    if( empty($values) ) continue;

                    // ─── setup options ────────
                    $params = array();
                    $params['element_id'] = $idElement;
                    // For Settings
                    if ( ! empty( $values['options'] ) ) {
                        foreach( $values['options'] as $key => $settings ) {
                            if( ! empty( $settings ) ) {
                                $params['args']                = $settings;
                                $params['sections']            = ! empty($settings['sections']) ? $settings['sections'] : null;
                                self::  $args_instanced[$idElement]['options'] = PF_classOptions::instance( $key, $params );
                            }
                        }
                    }
                    // For Pages
                    $params['element_id'] = $idElement;
                    if( ! empty( $values['pages'] ) ){
                        foreach( $values['pages'] as $key => $pages ) {
                            if( ! empty( $pages ) ) {
                                $params['args']                = $pages;
                                self::  $args_instanced[$idElement]['pages'] = PF_classPages::instance( $key, $params );
                            }
                        }
                    }
                    // For Metabox
                    $params = array();
                    $params['element_id'] = $idElement;
                    if( ! empty( $values['metaboxes'] ) ){
                        foreach( $values['metaboxes'] as $key => $metaboxs ) {
                            if( ! empty( $metaboxs ) ) {
                                $params['args']                = $metaboxs;
                                $params['sections']            = ! empty($metaboxs['sections']) ? $metaboxs['sections'] : null;
                                self::  $args_instanced[$idElement]['metabox'] = PF_classMetabox::instance( $key, $params );
                            }
                        }
                    }
                    // For Widget
                    if ( ! empty( $values['widgets'] ) && class_exists( 'WP_Widget_Factory' ) ) {
                        $wp_widget_factory = new WP_Widget_Factory();
                        foreach( $values['widgets'] as $key => $widgets ) {
                            if( ! isset( self::$inited[$key] ) ) {
                                self::$inited[$key] = true;
                                $wp_widget_factory->register( PF_classWidget::instance( $key, $widgets ) );
                            }
                        }
                    }
                    // For Customizer
                    $params = array();
                    $params['element_id'] = $idElement;
                    if ( ! empty( $values['customize_options'] ) ) {
                        foreach( $values['customize_options'] as $key => $customizes ) {
                            if( ! empty( $customizes ) && ! isset( self::$inited[$key] ) ) {
                                $params['args']       = $customizes;
                                $params['sections']   = ! empty($customizes['sections']) ? $customizes['sections'] : null;
                                self::$inited[$key] = true;
                                self::$args_instanced[$idElement]['customize_options'] = PF_Customize_Options::instance( $key, $params );
                            }
                        }
                    }
                    // For Shortcode
                    $params = array();
                    $params['element_id'] = $idElement;
                    if( ! empty( $values['shortcoders'] ) ){
                        foreach( $values['shortcoders'] as $key => $shortcode ) {
                            if( ! empty( $shortcode ) ) {
                                $params['args']     = $shortcode;
                                $params['sections'] = ! empty($shortcode['sections']) ? $shortcode['sections'] : null;
                                self::$inited[$key] = true;

                                self::  $args_instanced[$idElement]['shortcoders'] = PF_classShortcoder::instance( $key, $params );
                            }
                        }
                        // Once editor setup for gutenberg and media buttons
                        if( ! empty( PF::$shortcode_instances ) ) {
                            PF_classShortcoder::once_editor_setup();
                        }
                    }

                    // setup taxonomy options
                    $params = array();
                    $params['element_id'] = $idElement;
                    if ( ! empty( $values['taxonomy_options'] ) ) {
                        foreach( $values['taxonomy_options'] as $key => $taxonomy_options ) {
                            if( ! empty( $taxonomy_options ) && ! isset( self::$inited[$key] ) ) {

                                $params['args']     = $taxonomy_options;
                                $params['sections'] = $taxonomy_options['sections'];
                                self::$inited[$key] = true;

                                self::  $args_instanced[$idElement]['taxonomy_options'] = PF_classTaxonomyOptions::instance( $key, $params );

                            }
                        }
                    }

                    // setup profile options
                    $params = array();
                    $params['element_id'] = $idElement;
                    if ( ! empty( $values['profile_options'] ) ) {
                        foreach( $values['profile_options'] as $key => $profile ) {
                            if( ! empty( $profile['sections'] ) && ! isset( self::$inited[$key] ) ) {

                                $params['args']     = $profile;
                                $params['sections'] = $profile['sections'];
                                self::$inited[$key] = true;

                                self::  $args_instanced[$idElement]['profile_options'] = PF_classProfileOptions::instance( $key, $params );

                            }
                        }
                    }

                    // For Comment Metabox
                    $params = array();
                    $params['element_id'] = $idElement;
                    if( ! empty( $values['comment_metaboxes'] ) ){
                        foreach( $values['comment_metaboxes'] as $key => $comment_metabox ) {
                            if( ! empty( $comment_metabox ) ) {
                                $params['args']     = $comment_metabox;
                                $params['sections'] = ! empty($comment_metabox['sections']) ? $comment_metabox['sections'] : null;
                                self::$inited[$key] = true;
                                self::$args_instanced[$idElement]['comment_metaboxes'] = PF_classCommentMetabox::instance( $key, $params );
                            }
                        }
                    }
                }
            }
            // setup shortcoders
            // Once editor setup for gutenberg and media buttons

        }

        do_action( 'pf_loaded' );

        self::$setup_fields = true;
    }

    /*
    |--------------------------------------------------------------------------
    | Methods to build
    |--------------------------------------------------------------------------
    |
    | The developer has these public / statis methods to be able to build
    | options
    */
    /**
     * Add plugin to WP
     *
     * @param string $id
     * @return void
     */
    public static function addPlugin( $id ) {
        self::$elements[$id] = self::$args;
        self::$args          = self::$args_defaults;
    }

    /**
     * Clean the elements for a new plugin
     *
     * @since   1.4     2019-04-20 15:22:54     Release
     *
     * @todo    // FIXME: This is in a BETA state, but it is not proven that it is
     *          a good practice.
     */
    public static function new_plugin() {
        self::$elements[] = null;
        self::$args       = null;
    }

    /**
     * Add a setting options
     *
     * @param string $id
     * @param array $args
     * @return void
     */
    public static function addSetting( $id, $args = array() ) {
        self::$current_args = 'options';
        self::$args[self::$current_args][$id] = $args;
    }

    /**
     * Add options to the metabox
     *
     * @since   1.6     2019-12-02      Release
     *
     * @param   string  $id
     * @param   array   $args
     * @return  void
     */
    public static function addMetabox( $id, $args = array() ) {
        self::$current_args = 'metaboxes';
        self::$args[self::$current_args][$id] = $args;
    }

    /**
     * Add options to the comment metabox
     *
     * @since   1.6     2019-12-02      Release
     *
     * @param   string  $id
     * @param   array   $args
     * @return  void
     */
    public static function addCommentMetabox( $id, $args = [] ) {
        self::$current_args = 'comment_metaboxes';
        self::$args[self::$current_args][$id] = $args;
    }

    /**
     * Add page in menu or submenu
     *
     * @since 1.3.5     2019-04-15 21:07:41     Release
     *
     * @param string $id
     * @param array $args
     * @return void
     */
    public static function addPage( $id, $args = [] ){
        self::$current_args = 'pages';
        self::$args[self::$current_args][$id] = $args;
    }

    /**
     * Add widget
     *
     * @since   1.4.4   2019-05-21 00:53:56     Release
     *
     * @param   string  $id
     * @param   array   $args
     * @return  void
     */
    public static function addWidget( $id, $args = [] ){
        self::$current_args = 'widgets';
        self::$args[self::$current_args][$id] = $args;
    }

    /**
     * Add Customizer Options
     *
     * @since   1.5     2019-07-14 04:42:47     Release
     *
     * @param   string  $id
     * @param   array   $args
     * @return  void
     */
    public static function addCustomizer( $id, $args = [] ){
        self::$current_args = 'customize_options';
        self::$args[self::$current_args][$id] = $args;
    }

    /**
     * Add Shortcodes Options
     *
     * @since   1.5.3   2019-09-29  Release
     *
     * @param   string  $id
     * @param   array   $args
     * @return  void
     */
    public static function addShortcode( $id, $args = [] ){
        self::$current_args = 'shortcoders';
        self::$args[self::$current_args][$id] = $args;
    }

    /**
     * Add Taxonomy Options
     *
     * @since   1.5.3   2019-09-29  Release
     *
     * @param   string  $id
     * @param   array   $args
     * @return  void
     */
    public static function addTaxonomyOptions( $id, $args = [] ){
        self::$current_args = 'taxonomy_options';
        self::$args[self::$current_args][$id] = $args;
    }

    /**
     * Add Profile Options
     *
     * @since   1.5.3   2019-09-29  Release
     *
     * @param   string  $id
     * @param   array   $args
     * @return  void
     */
    public static function addProfileOptions( $id, $args = [] ){
        self::$current_args = 'profile_options';
        self::$args[self::$current_args][$id] = $args;
    }

    /**
     * Add a section or subsection
     *
     * @param string $id
     * @param array $sections
     * @return void
     */
    public static function addSection( $id, $sections ) {
        if( !empty($sections['parent']) ){
            $parent = $sections['parent'];
            if( empty( $sections['section'] ) ){
                unset( $sections['parent'] );
                self::$args[self::$current_args][$parent]['sections'][$id]= $sections;
            }else{
                $section = $sections['section'];
                $parent  = $sections['parent'];
                unset( $sections['parent'] );
                unset( $sections['section'] );
                self::$args[self::$current_args][$parent]['sections'][$section]['sub'][$id] = $sections;
            }
        }

        // ─── Add fields to be able to have all the options in a variable ────────
        self::get_all_fields( $sections );
    }
    // ───  ──────── ─── ──────── ─── ──────── ─── ──────── ─── ──────── ─── ──────── ─── ──────── ───


    /**
     * Obtain all the fields recursively
     *
     * This serves to accumulate all the fields and their attributes
     * that are loaded by hook in the first instance as enqueue
     *
     * @since    1.0     2019-03-03 15:15:34     Release
     * @since    1.4.7   2019-06-17 01:10:08     The acordions field was added as a recursive
     *
     * @param    array   $sections               Fetch all section options
     * @return   void
     */
    public static function get_all_fields( $sections ) {

        if( ! empty( $sections['fields'] ) ) {

            foreach( $sections['fields'] as $field ) {

                if( ! empty( $field['accordions'] ) ) {
                    foreach ($field['accordions'] as $key => $value) {
                        if( ! empty( $value['fields'] ) ) {
                            self::get_all_fields( $value );
                        }
                    }
                }

                if( ! empty( $field['fields'] ) ) {
                    self::get_all_fields( $field );
                }

                if( ! empty( $field['type'] ) ) {
                    self::$fields[] = $field;
                }
            }
        }
    }

    /**
     * Includes the class of a field
     *
     * @param string $type Type of field that is required
     *
     * @since 1.0 2019-02-25 02:23:16 Release
     * @return void
     */
    public static function maybe_include_field( $type = '' ) {
        //if( ! class_exists( 'PF_Field_'. $type) && class_exists( 'PF\\Classes\\PF_classFields' )  ) {
        if( ! class_exists( 'PF_Field_'. $type) && class_exists( 'PF_classFields' )  ) {
            self::include_plugin_file( 'fields/'. $type .'/'. $type .'.php' );
        }
    }

    /**
     * Includes the class of a field custom
     *
     * @param string $type Type of field that is required
     *
     * @since 1.2.1 2019-03-19 06:47:01 Release
     * @return void
     */
    public static function maybe_include_field_custom( $type = '', $path ) {
        $file_name_type = str_replace( "_", "-", $type );
        if( ! class_exists( 'PF_Field_'. $type) && class_exists( 'PF_classFields' )  ) {
            require_once( $path . $file_name_type .'/'. $file_name_type .'.php' );
        }
    }

    /**
     * Get the file of a specific route
         *
         * Perform several validations to see if the file is:
        *  * Part of a header template
        *  * Part of a template within the file
        *  * If it exists within the absolute path
        *  * If it exists within the relative path
        *
        * @param string $file Path of the file
        * @param boolean $load
        *
        * @since 1.0 2019-02-25 02:51:21 Release
        * @return void
        */
    public static function include_plugin_file( $file, $load = true ) {

        $path     = '';
        $file     = ltrim( $file, '/' );

        // pf-options is the folder name where the framework would be placed to execute
        // in this case it would be used for when it refers to THEMES.
        $override = apply_filters( 'pf-options', 'pf-options' );

        if ( file_exists( self::$dir .'/'. $file ) ) {
            $path = self::$dir .'/'. $file;
        }elseif ( file_exists( self::$dir .'/'. $override .'/'. $file ) ) {
            $path = self::$dir .'/'. $override .'/'. $file;
        }elseif( file_exists( get_parent_theme_file_path( $override .'/'. $file ) ) ) {
            $path = get_parent_theme_file_path( $override .'/'. $file );
        } elseif ( file_exists( get_theme_file_path( $override .'/'. $file ) ) ) {
            $path = get_theme_file_path( $override .'/'. $file );
        }

        if( ! empty( $path ) && ! empty( $file ) && $load ) {

            global $wp_query;

            if( is_object( $wp_query ) && function_exists( 'load_template' ) ) {

                load_template( $path, true );


            } else {

                require_once( $path );

            }

        } else {

            return self::$dir .'/'. $file;

        }

    }

    /**
     * Make a field wrap
     *
     * @since   1.3.5   2019-04-16  Add documentation of the function
     * @since   1.5.3   2019-09-30  Validation was fixed to get the custom field path
     *
     * @return  string|html
     */
    public static function field( $field = array(), $value = '', $unique = '', $where = '', $parent = '' ) {

        // ─── If there are no values ​​then I will return ────────
        if( ! count($field) > 0 ) return;

        // Check for unallow fields
        if( ! empty( $field['_notice'] ) ) {

            $field_type = $field['type'];

            $field            = array();
            $field['content'] = sprintf( esc_html__( 'Ooops! This field type (%s) can not be used here, yet.', 'pf' ), '<strong>'. $field_type .'</strong>' );
            $field['type']    = 'notice';
            $field['style']   = 'danger';

        }

        $depend             = '';
        $hidden             = '';
        $unique             = ( ! empty( $unique ) ) ? $unique : '';
        $class              = ( ! empty( $field['class'] ) ) ? ' ' . $field['class'] : '';
        $is_pseudo          = ( ! empty( $field['pseudo'] ) ) ? ' pf-pseudo-field' : '';
        $field_type         = ( ! empty( $field['type'] ) ) ? $field['type'] : '';
        $field_err          = ( ! empty( $field['_error'] ) ) ? ' pf-field-error' : '';
        $field['_error']    = ( ! empty( $field['id'] ) && ! empty( self::$errors_fields[$field['id']] ) ) ? self::$errors_fields[$field['id']] : '';
        $field['id']        = empty( $field['id'] ) ? pf_generate_ramdon_code() : $field['id'];

        if ( ! empty( $field['dependency'] ) ) {

            $dependency      = $field['dependency'];
            $hidden          = ' hidden';
            $data_controller = '';
            $data_condition  = '';
            $data_value      = '';
            $data_global     = '';

            if( is_array( $dependency[0] ) ) {

                $data_controller = implode( '|', array_column( $dependency, 0 ) );
                $data_condition  = implode( '|', array_column( $dependency, 1 ) );
                $data_value      = implode( '|', array_column( $dependency, 2 ) );
                $data_global     = implode( '|', array_column( $dependency, 3 ) );

            } else {

                $data_controller = ( ! empty( $dependency[0] ) ) ? $dependency[0] : '';
                $data_condition  = ( ! empty( $dependency[1] ) ) ? $dependency[1] : '';
                $data_value      = ( ! empty( $dependency[2] ) ) ? $dependency[2] : '';
                $data_global     = ( ! empty( $dependency[3] ) ) ? $dependency[3] : '';

            }

            $depend .= ' data-controller="'. $data_controller .'"';
            $depend .= ' data-condition="'. $data_condition .'"';
            $depend .= ' data-value="'. $data_value .'"';
            $depend .= ( ! empty( $data_global ) ) ? ' data-depend-global="true"' : '';
        }

        if( ! empty( $field_type ) ) {

            echo '<div data-id="'. $field['id'] .'" class="pf-field class-'. $field['id'] .' pf-field-'. $field_type . $is_pseudo . $class . $hidden . $field_err . '"'. $depend .'>';

            if( ! empty( $field['title'] ) ) {
                $subtitle = ( ! empty( $field['subtitle'] ) ) ? '<p class="pf-text-subtitle">'. $field['subtitle'] .'</p>' : '';
                echo '<div class="pf-title"><h4>' . $field['title'] . '</h4>'. $subtitle .'</div>';
            }

            echo ( ! empty( $field['title'] ) ) ? '<div class="pf-fieldset">' : '';

            $value = ( ! isset( $value ) && isset( $field['default'] ) ) ? $field['default'] : $value;
            $value = ( isset( $field['value'] ) ) ? $field['value'] : $value;

            // ─── Valid if it is a custom fields ────────
            if( ! empty( $field['is_custom'] ) && $field['is_custom'] == TRUE ){
                $element_id = ! empty( $field['element_id'] ) ? $field['element_id'] : null;
                if( isset(self::$args_instanced[$element_id]) ){
                    $path_custom = ! empty ( self::$args_instanced[$element_id][$where]->args['custom_fields_path'] ) ?
                                            self::$args_instanced[$element_id][$where]->args['custom_fields_path'] : null;
                    if( ! empty( $path_custom ) ){
                        self::maybe_include_field_custom( $field_type, $path_custom );
                    }
                }
            }else{
                self::maybe_include_field( $field_type );
            }

            $classname = 'PF_Field_' . $field_type;
            if( class_exists( $classname ) ) {
                $instance = new $classname( $field, $value, $unique, $where, $parent );
                $instance->render();
            } else {
                echo '<p>'. esc_html__( 'This field class is not available!', 'pf' ) .'</p>';
            }

        } else {
            echo '<p>'. esc_html__( 'This type is not found!', 'pf' ) .'</p>';
        }

        echo ( ! empty( $field['title'] ) ) ? '</div>' : '';
        echo '<div class="clear"></div>';
        echo '</div>';

    }

    //
    // WP 5.2 fallback
    //
    // This function has been created as temporary.
    // It will be remove after stable version of wp 5.3.
    //
    public static function add_admin_head_css() {

        global $wp_version;

        $current_branch = implode( '.', array_slice( preg_split( '/[.-]/', $wp_version ), 0, 2 ) );

        if( version_compare( $current_branch, '5.3', '<' ) ) {

            echo '<style type="text/css">
            .pf-field-slider .pf--unit,
            .pf-field-border .pf--label,
            .pf-field-spacing .pf--label,
            .pf-field-dimensions .pf--label,
            .pf-field-spinner .ui-button-text-only{
                border-color: #ddd;
            }
            .pf-warning-primary{
                box-shadow: 0 1px 0 #bd2130 !important;
            }
            .pf-warning-primary:focus{
                box-shadow: none !important;
            }
            </style>';

        }

    }

}
// Run!
PF::init();
}