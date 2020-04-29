<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
if( ! class_exists( 'PF_classOptions' ) ) {
/**
 * Options class
 *
 * @package PF
 * @subpackage PF/Classes
 *
 * @since   1.0     2019-02-25  Release
 * @since   1.2.2   2019-03-20  - Removed spaces in the file header.
 *                              - Improvements in variable declarations.
 *                              - Updating comments on features that did not have.
 * @since   1.5.3   2019-09-30  $wrapper_class is added for wrap classes
 */
class PF_classOptions{

    // ─── Constans class ────────
    public
    $element_id    = null,
    $unique        = '',
    $notice        = '',
    $abstract      = 'options',
    $sections      = array(),
    $options       = array(),
    $options_temp  = array(),
    $errors        = array(),
    $pre_tabs      = array(),
    $pre_fields    = array(),
    $pre_sections  = array();

    // ─── Default args ────────
    public $args   = array(
        'framework_class' => '',
        // menu settings ────────
        'menu_title'      => '',
        'menu_slug'       => '',
        'menu_type'       => 'menu',
        'menu_capability' => 'manage_options',
        'menu_icon'       => null,
        'menu_position'   => null,
        'menu_hidden'     => false,
        'menu_parent'     => '',
        'menu_title_sub'  => '', // Change the title of the first submenu

        // menu extras ────────
        'show_bar_menu'      => true,
        'show_sub_menu'      => true,
        'show_network_menu'  => true,
        'show_in_customizer' => false,

        'show_search'         => true,
        'show_reset_all'      => true,
        'show_reset_section'  => true,
        'show_save_section'   => true,
        'show_footer'         => true,
        'show_all_options'    => true,
        'show_buttons_top'    => true,
        'show_buttons_footer' => true,
        'sticky_header'       => true,
        'save_defaults'       => true,
        'save_when_error'     => true,
        'ajax_save'           => true,

        // admin bar menu settings ────────
        'admin_bar_menu_icon'     => '',
        'admin_bar_menu_priority' => 80,

        // settings ────────
        'setting_title'         => 'Pixel Framework',
        'setting_wrap_class'    => '',
        'setting_vertical_mode' => false,

        // footer ────────
        'footer_text'   => '',
        'footer_after'  => '',
        'footer_credit' => '',

        // database model ────────
        'database'       => '',   // options, transient, theme_mod, network
        'transient_time' => 0,

        // contextual help ────────
        'contextual_help'         => array(),
        'contextual_help_sidebar' => '',

        // typography options ────────
        'enqueue_webfont' => true,
        'async_webfont'   => false,

        // others ────────
        'output_css' => true,

        // theme & class ────────
        'theme'         => 'light',
        'class'         => '',
        'image_default' => PIXEL_URL . 'assets/images/default.png',

        // Custom Fields Path ────────
        'custom_fields_path'  => '',

        // ─── External default values ────────
        'defaults' => array(),
    );

    /**
     * Create an Instance of Options
     * You can create multiple instances with different $keys.
     *
     * @since   1.0         2019-02-21  Release
     *
     * @param   string      $key        ID unique setting/options
     * @param   array       $params
     * @return  object
     */
    public static function instance( $key, $params = array() ) {
        return new self( $key, $params );
    }

    /**
     * Run construct
     *
     * @since   1.0     2019-02-21  Release
     * @since   1.5.3   2019-09-30  'Element_id' is added to know the total element ID of those options
     *
     * @param string    $key        ID unique setting/options
     * @param array     $params     Option Settings
     */
    public function __construct( $key, $params = array() ) {

        // ─── Assign the unique ID (id plugin), argument of the Options and Section ────────
        $this->element_id   = $params['element_id'];
        $this->unique       = $key;
        $this->args         = apply_filters( "pf_{$this->unique}_args", pf_wp_parse_args( $params['args'], $this->args ), $this );
        $this->sections     = apply_filters( "pf_{$this->unique}_sections", $params['sections'], $this );

        // ─── Prepare the sections and fields before showing them in the administration panel ────────
        $this->pre_tabs     = $this->pre_tabs( $this->sections );
        $this->pre_fields   = $this->pre_fields( $this->sections );
        $this->pre_sections = $this->pre_sections( $this->sections );

        // ─── Verify save data and validate ────────
        $this->get_options();
        $this->set_options();
        $this->save_defaults();

        // ─── Load Options hook ────────
        $this->load_hooks();
    }

    /**
     * Collect and Order all sections
     *
     * @since   1.0     2019-02-21  Release
     *
     * @param   array   $sections
     * @return  void
     */
    public function pre_tabs( $sections ) {

        $result    = array();
        $parents   = array();
        $count     = 100;
        $increment = 10;

        if( ! empty( $sections ) ){
            foreach ($sections as $key => $value) {
                $value['priority']  = ( isset( $value['priority'] ) ) ? $value['priority'] : $count;
                $count              = $count + $increment;
                if( ! empty( $value['sub'] ) ) {
                    foreach ($value['sub'] as $k => $v) {
                        $v['priority']  = ( isset( $v['priority'] ) ) ? $v['priority'] : $count;
                        $parents[]      = $v;
                        $count++;
                    }
                    $value['sub'] = wp_list_sort( $parents, array( 'priority' => 'ASC' ), 'ASC', true );
                    $parents = array();
                }
                $result[] = $value;
            }
        }

        return wp_list_sort( $result, array( 'priority' => 'ASC' ), 'ASC', true );
    }

    /**
     * Collect all fields
     *
     * @since 1.0 2019-02-21 20:12:47 Release
     * @since 1.2.2 2019-03-20 14:14:34 Improvement to save / reset / reset section
     *
     * @param array $sections
     * @return void
     */
    public function pre_fields( $sections = array() ) {

        if( empty( $sections ) ) return ;

        $result  = array();

        if( empty( $sections ) ) return ;

        foreach( $sections as $key => $section ) {

            if( ! empty( $section['fields'] ) ) {

                foreach( $section['fields'] as $key => $field ) {

                    $result[] = $field;

                }

            }elseif( ! empty( $section['sub'] ) ){

                foreach( $section['sub'] as $k => $v ){

                    if( ! empty( $v['fields'] ) ){

                        foreach ( $v['fields'] as $_k => $_v ) {

                            $result[] = $_v;

                        }

                    }

                }

            }
        }

        return $result;
    }

    /**
     * Collect the sections
     *
     * @since   1.0     2019-02-22  Release
     *
     * @param   array   $sections
     * @return  void
     */
    public function pre_sections( $sections ) {

        $result = array();

        foreach( $this->pre_tabs as $tab ) {
            if( ! empty( $tab['sub'] ) ) {
                foreach( $tab['sub'] as $sub ) {
                    $result[] = $sub;
                }
            }
            if( empty( $tab['sub'] ) ){
                $result[] = $tab;
            }
        }
        return $result;
    }

    /**
     * Save the options in the database.
     *
     * You have several ways to save it within the database:
     *  * transient
     *  * theme_mod
     *  * network
     *  * options (tabla wp_options)
     *
     * @since 1.0 2019-02-22 01:30:07 Release
     *
     * @param mixed $request Data that will save
     * @return void
     */
    public function save_options( $request ) {

        // ─── Valid if there is no error to save data according to the option 'save_when_error' ────────
        if( ! $this->args['save_when_error'] && ! empty( $this->errors ) ){
            $this->options = $this->options_temp;
            return;
        }

        if( $this->args['database'] === 'transient' ) {
            set_transient( $this->unique, $request, $this->args['transient_time'] );
        } else if( $this->args['database'] === 'theme_mod' ) {
            set_theme_mod( $this->unique, $request );
        } else if( $this->args['database'] === 'network' ) {
            update_site_option( $this->unique, $request );
        } else {
            update_option( $this->unique, $request );
        }

        do_action( "pf_{$this->unique}_saved", $request, $this );
    }

    /**
     * Get data(options) from the database.
     *
     * You have several ways to get it within the database:
     *  * transient
     *  * theme_mod
     *  * network
     *  * options (tabla wp_options)
     *
     * @since 1.0 2019-02-22 01:32:53 Release
     * @return void
     */
    public function get_options() {

        if( $this->args['database'] === 'transient' ) {
            $this->options = get_transient( $this->unique );
        } else if( $this->args['database'] === 'theme_mod' ) {
            $this->options = get_theme_mod( $this->unique );
        } else if( $this->args['database'] === 'network' ) {
            $this->options = get_site_option( $this->unique );
        } else {
            $this->options = $this->options_temp = get_option( $this->unique );
        }

        if( empty( $this->options ) ) {
            $this->options = array();
        }

        return $this->options;
    }

    /**
     * Save the data in the form of ajax
     *
     * @since   1.0     2019-03-07  Release
     * @since   1.5.2   2019-08-31  Added the '_' character for the name suffix
     * @return void
     */
    public function ajax_save() {

        if( ! empty( $_POST['data'] ) ) {

            $_POST = json_decode( stripslashes( $_POST['data'] ), true );

            if( wp_verify_nonce( pf_get_var( 'pf_options_nonce_'. $this->unique ), 'pf_options_nonce' ) ) {

                $this->set_options();

                wp_send_json_success( array( 'success' => true, 'notice' => $this->notice, 'errors' => $this->errors ) );

            }

        }

        wp_send_json_error( array( 'success' => false, 'error' => esc_html__( 'Error while saving.', 'pf' ) ) );

    }

    /**
     * Save the default values ​​of the fields
     *
     * @since 1.0 2019-02-22 01:50:02 Release
     * @return void
     */
    public function save_defaults() {

        if( ! $this->args['save_defaults'] ){ return; }

        if( empty( $this->pre_fields ) ){ return; }

        $tmp_options = $this->options;

        foreach( $this->pre_fields as $field ) {

            if( ! empty( $field['id'] ) ) {

                $field_default = ( isset( $field['default'] ) ) ? $field['default'] : '';
                $field_value   = ( isset( $this->options[$field['id']] ) ) ? $this->options[$field['id']] : $field_default;

                $this->options[$field['id']] = $field_value;
            }
        }

        if( $this->args['save_defaults'] && empty( $tmp_options ) ) {
            $this->save_options( $this->options );
        }
    }

    /**
     * Load hooks Options
     *
     * @since	1.0	2019-02-22 02:13:42 Release
     * @return	void
     */
    public function load_hooks(){
        add_action( 'admin_menu', array( &$this, 'add_admin_menu' ), 11 );
        add_action( 'wp_ajax_pf_'. $this->unique .'_ajax_save', array( &$this, 'ajax_save' ) );
        if( ! empty( $this->args['show_network_menu'] ) ) {
            add_action( 'network_admin_menu', array( &$this, 'add_admin_menu' ) );
        }
    }

    /**
     * Add main menu for sections
     *
     * @since	1.0	2019-02-22 02:21:23 Release
     * @return	void
     */
    public function add_admin_menu(){
        /*
        |--------------------------------------------------------------------------
        | @menu_parent argument examples.
        |--------------------------------------------------------------------------
        |
        | For Dashboard: 'index.php'
        | For Posts: 'edit.php'
        | For Media: 'upload.php'
        | For Pages: 'edit.php?post_type=page'
        | For Comments: 'edit-comments.php'
        | For Custom Post Types: 'edit.php?post_type=your_post_type'
        | For Appearance: 'themes.php'
        | For Plugins: 'plugins.php'
        | For Users: 'users.php'
        | For Tools: 'tools.php'
        | For Settings: 'options-general.php'
        |
        */

        extract( $this->args );
        if( $menu_type === 'menu' ) {
            $menu_page = call_user_func('add_menu_page',
                                        $menu_title,
                                        $menu_title,
                                        $menu_capability,
                                        $menu_slug,
                                        array( &$this, 'add_options_html' ),
                                        $menu_icon,
                                        $menu_position );
            if ( ! empty( $menu_title_sub ) ){
                call_user_func('add_submenu_page', $menu_slug, $menu_title, $menu_title_sub, $menu_capability, $menu_slug );
            }
        }else{
            if( $menu_type === 'submenu' ) {
                $menu_page = call_user_func('add_submenu_page',
                                            $menu_parent,
                                            $menu_title,
                                            $menu_title_sub,
                                            $menu_capability,
                                            //admin_url("admin.php?page=$menu_slug"),//
                                            $menu_slug,
                                            array( &$this, 'add_options_html' ) );
            }
        }

        add_action( 'load-'. $menu_page, array( &$this, 'add_page_on_load' ) );
    }

    /**
     * Add button setting
     *
     * Add setting buttons and their settings
     *
     * @since	1.2.3   2019-11-03  Release
     * @since   1.5.7   2019-11-03  Now the 'save' button is added to show it or not,
     *                              this will help the hooks before and after button be shown
     * @since   1.6     2019-12-02  The button-secundary class in the reset class was removed
     *
     * @param   string  $ajax_class Class that identifies if ajax is the saved data
     * @return	void
     */
    public function add_button_setting( $ajax_class, $name_options = '' ){
        echo '<div class="pf-buttons pixel_options_'.$this->unique.'_buttons_before">';
        do_action("pixel_options_{$this->unique}_buttons_before");
        echo ( $this->args['show_save_section'] ) ? '<input type="submit" name="'. $this->unique .'[_nonce][save]" class="button button-primary pf-save'. $ajax_class .'" value="'. esc_html__( 'Save', 'pf' ) .'" data-save="'. esc_html__( 'Saving...', 'pf' ) .'">' : '';
        echo ( $this->args['show_reset_section'] ) ? '<input type="submit" name="pf_transient[reset_section]" class="button button-secondary pf-reset-section pf-confirm" value="'. esc_html__( 'Reset Section', 'pf' ) .'" data-confirm="'. esc_html__( 'Are you sure to reset this section options?', 'pf' ) .'">' : '';
        echo ( $this->args['show_reset_all'] ) ? '<input type="submit" name="pf_transient[reset]" class="button pf-warning-primary pf-reset-all pf-confirm" value="'. esc_html__( 'Reset All', 'pf' ) .'" data-confirm="'. esc_html__( 'Are you sure to reset all options?', 'pf' ) .'">' : '';
        do_action("pixel_options_{$this->unique}_buttons_after");
        echo '</div>';
    }

    /**
     * HTML of the menu (output options)
     *
     * @since	1.0	    2019-02-24      Release
     * @since   1.2.7   2019-03-27      Added core variable 'PF::$errors_fields to be able to save individual error.
     * @since   1.5.2   2019-08-31      The '_' character was added for the name suffix
     * @since   1.5.3   2019-09-30      - $wrapper_class is added for wrap classes
     *                                  - element_id is added to obtain information of the element in execution
     * @since   1.5.4   2019-10-26      Now you can add class in each setting tab (options)
     *
     * @return  string
     */
    public function add_options_html(){

        $has_nav       = ( count( $this->pre_tabs ) > 1 ) ? true : false;
        $show_all      = ( ! $has_nav ) ? ' pf-show-all' : '';
        $ajax_class    = ( $this->args['ajax_save'] ) ? ' pf-save-ajax' : '';
        $sticky_class  = ( $this->args['sticky_header'] ) ? ' pf-sticky-header' : '';
        $vertical_mode = ( $this->args['setting_vertical_mode'] ) ? ' pf-vertical' : '';
        $wrapper_class = ( $this->args['framework_class'] ) ? ' '. $this->args['framework_class'] : '';
        $theme         = ( $this->args['theme'] ) ? ' pf-theme-'. $this->args['theme'] : '';
        $class         = ( $this->args['class'] ) ? ' '. $this->args['class'] : '';
        PF::$errors_fields = $this->errors;

        echo '<div class="pf pf-options pf-' . $this->unique . $theme . $wrapper_class . $class . $vertical_mode . '"
                data-slug="'. $this->args['menu_slug'] .'" data-unique="'. $this->unique .'">';

            $notice_class = ( ! empty( $this->notice ) ) ? ' pf-form-show' : '';
            $notice_text  = ( ! empty( $this->notice ) ) ? $this->notice : '';

            echo '<div class="pf-form-result pf-form-success'. $notice_class .'">'. $notice_text .'</div>';

            $error_class = ( ! empty( $this->errors ) ) ? ' pf-form-show' : '';

            echo '<div class="pf-form-result pf-form-error'. $error_class .'">';
            if( ! empty( $this->errors ) ) {
                foreach ( $this->errors as $error ) {
                    echo '<i class="pf-label-error">!</i> ' . $error . '<br />';
                }
            }
            echo '</div>';

            echo '<div class="pf-container">';

            echo '<form method="post" action="" enctype="multipart/form-data" id="pf-form" autocomplete="off">';

                echo '<input type="hidden" class="pf-section-id" name="pf_transient[section]" value="1">';
                wp_nonce_field( 'pf_options_nonce', 'pf_options_nonce_'.$this->unique );
                do_action( "pf_{$this->unique}_before_header" );
                echo '<div class="pf-header'. esc_attr( $sticky_class ) .'">';
                    echo '<div class="pf-header-inner">';
                            echo '<div class="pf-header-left">';
                                echo '<h1>'. $this->args['setting_title'] .'</h1>';
                            echo '</div>';

                            echo '<div class="pf-header-right">';

                                echo ( $has_nav && $this->args['show_all_options'] ) ? '<div class="pf-expand-all" title="'. esc_html__( 'show all options', 'pf' ) .'"><i class="fa fa-outdent"></i></div>' : '';

                                echo ( $this->args['show_search'] ) ? '<div class="pf-search"><input type="text" name="pf-search" placeholder="'. esc_html__( 'Search option(s)', 'pf' ) .'" autocomplete="off" /></div>' : '';

                                if( $this->args['show_buttons_top'] ){
                                    $this->add_button_setting( $ajax_class );
                                }

                            echo '</div>';

                    echo '</div>';

                    echo '<div class="clear"></div>';
                echo '</div>';

                echo '<div class="pf-wrapper'. $show_all .'">';

                    if( $has_nav ) {
                        echo '<div class="pf-nav pf-nav-options">';

                            echo '<ul class="nav-tab-wrapper">';

                            $tab_key = 1;
                            $tab_with_sub = array();

                            foreach( $this->pre_tabs as $tab ) {

                                $tab_error = $this->error_check( $tab );
                                $tab_icon  = ( ! empty( $tab['icon'] ) ) ? '<i class="'. $tab['icon'] .'"></i>' : '';
                                $tab_class = ( ! empty( $tab['class'] ) ) ? $tab['class'] : '';

                                if( ! empty( $tab['sub'] ) ) {

                                    echo '<li class="pf-tab-depth-0 pf-tab-with-subtabs '. $tab_class .'">';

                                        echo '<a href="#tab='. $tab_key .'" data-tab-id="'.$tab_key.'" class="pf-arrow nav-tab">'. $tab_icon . $tab['title'] . $tab_error .'</a>';

                                        echo '<ul>';

                                        foreach ( $tab['sub'] as $sub ) {

                                            $sub_error      = $this->error_check( $sub );
                                            $sub_icon       = ( ! empty( $sub['icon'] ) ) ? '<i class="'. $sub['icon'] .'"></i>' : '';
                                            $tab_with_sub[] = $tab_key;

                                            echo '<li class="pf-tab-depth-1"><a id="pf-tab-link-'. $tab_key .'" data-tab-id="'.$tab_key.'" href="#tab='. $tab_key .'">'. $sub_icon . $sub['title'] . $sub_error .'</a></li>';

                                            $tab_key++;
                                        }

                                        echo '</ul>';

                                    echo '</li>';

                                } else {

                                    echo '<li class="pf-tab-depth-0 '. $tab_class .'"><a class="nav-tab" data-tab-id="'.$tab_key.'" id="pf-tab-link-'. $tab_key .'" href="#tab='. $tab_key .'">'. $tab_icon . $tab['title'] . $tab_error .'</a></li>';

                                    $tab_key++;
                                }

                            }

                            echo '</ul>';

                        echo '</div>';

                    }

                    // Validate if there is no navigation then this variable puts an array of an item of 1
                    $tab_with_sub = empty( $tab_with_sub ) ? array(1) : $tab_with_sub;

                    echo '<div class="pf-content">';

                        echo '<div class="pf-sections">';

                        $section_key  = 1;
                        $section_show = isset($_COOKIE['pf-last-options-tab-' . $this->unique]) ?
                                                $_COOKIE['pf-last-options-tab-' . $this->unique] : 1;
                        foreach( $this->pre_sections as $section ) {

                            $onload = ( ! $has_nav ) ? ' pf-onload' : '';
                            $section_icon = ( ! empty( $section['icon'] ) ) ? '<i class="pf-icon '. $section['icon'] .'"></i>' : '';
                            $section_child_class = in_array( $section_key, $tab_with_sub ) ? ' section-child' : '' ;

                            echo '<div id="pf-section-'. $section_key .'" class="pf-section'. $onload . $section_child_class .'" >';
                            echo ( $has_nav ) ? '<div class="pf-section-title"><h3>'. $section_icon . $section['title'] .'</h3></div>' : '';
                            echo ( ! empty( $section['description'] ) ) ? '<div class="pf-field pf-section-description">'. $section['description'] .'</div>' : '';

                            if( ! empty( $section['fields'] ) ) {

                                foreach( $section['fields'] as $field ) {

                                    $is_field_error = $this->error_check( $field );
                                    if( ! empty( $is_field_error ) ) {
                                        $field['_error'] = $is_field_error;
                                    }

                                    $value = ( ! empty( $field['id'] ) && isset( $this->options[$field['id']] ) ) ? $this->options[$field['id']] : '';
                                    $field['element_id'] = $this->element_id;
                                    PF::field( $field, $value, $this->unique, 'options', '' );

                                }

                            } else {

                                echo '<div class="pf-no-option pf-text-muted">'. esc_html__( 'No option provided by developer.', 'pf' ) .'</div>';

                            }

                            echo '</div>';

                            $section_key++;
                        }

                        echo '</div>';

                        echo '<div class="clear"></div>';

                    echo '</div>';

                    echo '<div class="pf-nav-background"></div>';

                echo '</div>';

                if( ! empty( $this->args['show_footer'] ) ) {

                    echo '<div class="pf-footer">';

                    if( $this->args['show_buttons_footer'] ){
                        $this->add_button_setting( $ajax_class );
                    }

                    echo ( ! empty( $this->args['footer_text'] ) ) ? '<div class="pf-copyright">'. $this->args['footer_text'] .'</div>' : '';

                    echo '<div class="clear"></div>';
                    echo '</div>';

                }

                echo '</form>';

            echo '</div>';

            echo '<div class="clear"></div>';

            echo ( ! empty( $this->args['footer_after'] ) ) ? $this->args['footer_after'] : '';

        echo '</div>';

    }

    /**
     * Valida errores grupaes o individuales
     *
     * @since   1.2.7 2019-03-27 17:41:44 Update functions
     *
     * @param   mixed $args Values ​​of $Fields
     * @param   mixed $err Response response
     */
    public function error_check( &$args, $err = '' ) {
        if( ! $this->args['ajax_save'] ) {
            $args     = (array) $args;
            if( ! empty( $args['fields'] ) ) {
                foreach( $args['fields'] as $field ) {
                    if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
                        $err = $this->error_check( $field, $err );
                    }elseif( ! empty( $field['id'] ) ) {
                        if( array_key_exists( $field['id'], $this->errors ) ) {
                            $err = '<span class="pf-label-error">!</span>';
                        }
                    }
                }
            }
            if( ! empty( $args['sub'] ) ) {
                foreach( $args['sub'] as $sub ) {
                    $err = $this->error_check( $sub, $err );
                }
            }

            if( ! empty( $args['id'] ) && array_key_exists( $args['id'], $this->errors ) ) {
                $err = $this->errors[$args['id']];
            }

        }
        return $err;
    }

    /**
     * Set options
     *
     * Freight and save the fields
     *
     * @since   1.2.2   2019-03-20  Improved
     * @since   1.2.7   2019-03-27  Added the function 'pf_sanitize_fields_recursive' to be able
     *                              validate and sanitize the values ​​of each field.
     * @since   1.3.3   2019-04-07  Valid within the $ _POST if the field with X name exists, it is giving Notice
     * @since   1.4.6   2019-06-12  Security improvements
     * @since   1.5.2   2019-08-31  The character '_' was added to combine it with the name and give better interpretation
     *
     * @return void
     */
    public function set_options() {

        if( wp_verify_nonce( pf_get_var( 'pf_options_nonce_'. $this->unique ), 'pf_options_nonce' ) ) {

            $request    = pf_get_var( $this->unique, array() );
            if( ! empty( $request ) ){
                $transient  = pf_get_var( 'pf_transient' );
                $section_id = ( ! empty( $transient['section'] ) ) ? $transient['section'] : '';
            }else{
                // ─── If we are not within the current setting submit then leave the process ────────
                return;
            }

            // ─── import data ────────
            if( ! empty( $transient['pf_import_data'] ) ) {

                $import_data = json_decode( stripslashes( trim( $transient['pf_import_data'] ) ), true );
                $request     = ( is_array( $import_data ) ) ? $import_data : array();

                $this->notice = esc_html__( 'Success. Imported backup options.', 'pf' );

            } else if( ! empty( $transient['reset'] ) ) {

                foreach( $this->pre_fields as $field ) {

                    if( ! empty( $field['id'] ) ) {

                        if( isset( $field['default'] ) ) {

                            $request[$field['id']] = $field['default'];

                        } else {

                            $request[$field['id']] = '';

                        }

                    }

                }

                $this->notice = esc_html__( 'Default options restored.', 'pf' );

            } else if( ! empty( $transient['reset_section'] ) && ! empty( $section_id ) ) {

                if( ! empty( $this->pre_sections[$section_id-1]['fields'] ) ) {

                    foreach( $this->pre_sections[$section_id-1]['fields'] as $field ) {

                        if( ! empty( $field['id'] ) ) {

                            if( isset( $field['default'] ) ) {

                                $request[$field['id']] = $field['default'];

                            } else {

                                $request[$field['id']] = '';

                            }

                        }

                    }

                }

                $this->notice = esc_html__( 'Default options restored for only this section.', 'pf' );

            } else if( ! empty( $this->pre_fields ) ){

                // ─── sanitize and validate ────────
                foreach( $this->pre_fields as $field ) {

                    //                                Validate 1.3.3 ↓
                    if( ! empty( $field['id'] ) && isset($request[$field['id']]) && ( ! empty( $request[$field['id']] ) || $request[$field['id']] == ''  ) ) {

                        // ─── sanitize & validate ────────
                        $request[$field['id']] = pf_sanitize_fields_recursive( $field, $request[$field['id']], $this );

                    }

                }

            }else{

                $this->notice = esc_html__( 'No fields to save.', 'pf' );

            }

            // ─── ignore nonce requests ────────
            if( isset( $request['_nonce'] ) ) { unset( $request['_nonce'] ); }

            $request = wp_unslash( $request );

            $request = apply_filters( "pf_{$this->unique}_save", $request, $this );

            do_action( "pf_{$this->unique}_save_before", $request, $this );

            $this->options = $request;

            $this->save_options( $request );

            do_action( "pf_{$this->unique}_save_after", $request, $this );

            if( !( ! $this->args['save_when_error'] && ! empty( $this->errors )) ) {
                if( empty( $this->notice ) ){
                    $this->notice = esc_html__( 'Settings saved.', 'pf' );
                }
            }

        }
        return true;
    }

    /**
     * Load processes, hook, filter right on the options page
     *
     * @since 1.0 2019-03-07 06:29:32 Release
     * @return void
     */
    public function add_page_on_load(){
        add_filter( 'admin_footer_text', array( &$this, 'add_admin_footer_text' ) );
    }

    /**
     * Text from the bottom left of the panel
     *
     * @since 1.3.5  2019-04-16 00:06:32    Add the documentation of this function
     * @return void
     */
    public function add_admin_footer_text() {
        $default = 'Pixel by Lenin Zapata';
        echo ( ! empty( $this->args['footer_credit'] ) ) ? $this->args['footer_credit'] : $default;
    }

} }