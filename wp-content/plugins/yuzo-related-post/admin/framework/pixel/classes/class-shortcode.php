<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
if( ! class_exists( 'PF_classShortcoder' ) ) {
/**
 * Shortcode class
 *
 * @package PF
 * @subpackage PF/Classes
 *
 * @since   1.5.3   2019-09-29      Release
 *
 */
class PF_classShortcoder extends PF_classAbstract{

    // ─── Constans class ────────
    public $unique       = '';
    public $abstract     = 'shortcoder';
    public $blocks       = array();
    public $sections     = array();
    public $pre_tabs     = array();
    public $pre_sections = array();
    public $args         = array(
        'button_title'     => 'Add Shortcode',
        'select_title'     => 'Select a shortcode',
        'insert_title'     => 'Insert Shortcode',
        'show_in_editor'   => true,
        'defaults'         => array(),
        'class'            => '',
        'gutenberg'        => array(
            'title'          => 'PF Shortcodes',
            'description'    => 'PF Shortcode Block',
            'icon'           => 'screenoptions',
            'category'       => 'widgets',
            'keywords'       => array( 'shortcode', 'pf', 'insert' ),
            'placeholder'    => 'Write shortcode here...',
        ),
    );

    /**
     * Shortcode Construct
     *
     * @since   1.5.3       2019-09-29      Release
     *
     * @param   string      $key            ID unique instance
     * @param   array       $params         Params reemplace default
     * @return  void
     */
    public function __construct( $key, $params = array() ) {

        // ─── Assign the unique ID (id plugin), argument of the Options and Section ────────
        $this->element_id   = $params['element_id'];
        $this->unique       = $key;
        $this->args         = apply_filters( "pf_{$this->unique}_args", wp_parse_args( $params['args'], $this->args ), $this );

        // ─── Prepare the sections and fields before showing them in the administration panel ────────
        $this->sections     = apply_filters( "pf_{$this->unique}_sections", $params['sections'], $this );
        $this->pre_tabs     = $this->pre_tabs( $this->sections );
        $this->pre_sections = $this->pre_sections( $this->sections );

        // ─── Load Options hook ────────
        add_action( 'admin_footer', array( &$this, 'add_shortcode_modal' ) );
        add_action( 'customize_controls_print_footer_scripts', array( &$this, 'add_shortcode_modal' ) );
        add_action( 'wp_ajax_pf-get-shortcode-'. $this->unique, array( &$this, 'get_shortcode' ) );

        if( ! empty( $this->args['show_in_editor'] ) ) {

            $data = wp_parse_args( array( 'hash' => md5( $key ), 'modal_id' => $this->unique ), $this->args );

            PF::$shortcode_instances[]    = $data;
            PF::$shortcode_instances_key[$key] = $data;

            // elementor editor support
            if( PF::is_active_plugin( 'elementor/elementor.php' ) ) {
                add_action( 'elementor/editor/before_enqueue_scripts', array( 'PF', 'add_admin_enqueue_scripts' ), 20 );
                add_action( 'elementor/editor/footer', array( &$this, 'add_shortcode_modal' ) );
                add_action( 'elementor/editor/footer', 'pf_set_icons' );
            }

        }

    }

    /**
     * Create an Instance of Options
     * You can create multiple instances with different $keys.
     *
     * @since   1.5.3       2019-02-21  Release
     *
     * @param   string      $key        ID unique setting/options
     * @param   array       $params
     * @return  object
     */
    public static function instance( $key, $params = array() ) {
        return new self( $key, $params );
    }

    /**
     * Collect and Order all sections
     *
     * @since   1.5.3   2019-02-21  Release
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
     * Collect the sections
     *
     * @since   1.5.3   2019-02-22      Release
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
            if( empty( $tab['sub'] ) ) {
                $result[] = $tab;
            }
        }

        return $result;
    }

    /**
     * Get default value
     *
     * @since   1.5.3   2019-10-01  Release
     *
     * @param   array   $field      Field that the default value is required
     * @return  void
     */
    public function get_default( $field ) {

        $default = ( isset( $field['id'] ) && isset( $this->args['defaults'][$field['id']] ) ) ? $this->args['defaults'][$field['id']] : null;
        $default = ( isset( $field['default'] ) ) ? $field['default'] : $default;

        return $default;

    }

    /**
     * Add the group of fields that have been selected to be displayed
     * If there is only one group then it will drive the fields without group selector.
     *
     * @since   1.5.3   2019-10-01      Release
     * @return  string|html
     */
    public function add_shortcode_modal() {

        $class        = ( $this->args['class'] ) ? ' '. $this->args['class'] : '';
        $has_select   = ( count( $this->pre_tabs ) > 1 ) ? true : false;
        $single_usage = ( ! $has_select ) ? ' pf-shortcode-single' : '';
        $hide_header  = ( ! $has_select ) ? ' hidden' : '';

        ?>
        <div id="pf-modal-<?php echo $this->unique; ?>" class="wp-core-ui pf-modal pf-shortcode<?php echo $single_usage . $class; ?>" data-modal-id="<?php echo $this->unique; ?>" data-nonce="<?php echo wp_create_nonce( 'pf_shortcode_nonce' ); ?>">
            <div class="pf-modal-table">
            <div class="pf-modal-table-cell">
                <div class="pf-modal-overlay"></div>
                <div class="pf-modal-inner">
                <div class="pf-modal-title">
                    <?php echo $this->args['button_title']; ?>
                    <div class="pf-modal-close"></div>
                </div>
                <?php

                    echo '<div class="pf-modal-header'. $hide_header .'">';
                    echo '<select>';
                    echo ( $has_select ) ? '<option value="">'. $this->args['select_title'] .'</option>' : '';

                    $tab_key = 1;
                    foreach ( $this->pre_tabs as $tab ) {

                    if( ! empty( $tab['sub'] ) ) {

                        echo '<optgroup label="'. $tab['title'] .'">';

                        foreach ( $tab['sub'] as $sub ) {

                        $view      = ( ! empty( $sub['view'] ) ) ? ' data-view="'. $sub['view'] .'"' : '';
                        $shortcode = ( ! empty( $sub['shortcode'] ) ) ? ' data-shortcode="'. $sub['shortcode'] .'"' : '';
                        $group     = ( ! empty( $sub['group_shortcode'] ) ) ? ' data-group="'. $sub['group_shortcode'] .'"' : '';

                        echo '<option value="'. $tab_key .'"'. $view . $shortcode . $group .'>'. $sub['title'] .'</option>';

                        $tab_key++;
                        }

                        echo '</optgroup>' ;

                    } else {

                        $view      = ( ! empty( $tab['view'] ) ) ? ' data-view="'. $tab['view'] .'"' : '';
                        $shortcode = ( ! empty( $tab['shortcode'] ) ) ? ' data-shortcode="'. $tab['shortcode'] .'"' : '';
                        $group     = ( ! empty( $tab['group_shortcode'] ) ) ? ' data-group="'. $tab['group_shortcode'] .'"' : '';

                        echo '<option value="'. $tab_key .'"'. $view . $shortcode . $group .'>'. $tab['title'] .'</option>';

                        $tab_key++;
                    }

                    }

                    echo '</select>';
                    echo '</div>';

                ?>
                <div class="pf-modal-content">
                    <div class="pf-modal-loading"><div class="pf-loading"></div></div>
                    <div class="pf-modal-load"></div>
                </div>
                <div class="pf-modal-insert-wrapper hidden"><a href="#" class="button button-primary pf-modal-insert"><?php echo $this->args['insert_title']; ?></a></div>
                </div>
            </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get the shortcode html
     *
     * @since   1.5.3   2019-10-01      Release
     * @return void
     */
    public function get_shortcode() {

        ob_start();

        $shortcode_key = pf_get_var( 'shortcode_key' );

        if( ! empty( $shortcode_key ) && wp_verify_nonce( pf_get_var( 'nonce' ), 'pf_shortcode_nonce' ) ) {

            $unallows  = array( 'group', 'repeater', 'sorter' );
            $section   = $this->pre_sections[$shortcode_key-1];
            $shortcode = ( ! empty( $section['shortcode'] ) ) ? $section['shortcode'] : '';
            $view      = ( ! empty( $section['view'] ) ) ? $section['view'] : 'normal';

            if( ! empty( $section ) ) {

                //
                // View: normal
                if( ! empty( $section['fields'] ) && $view !== 'repeater' ) {

                    echo '<div class="pf-fields">';

                    foreach ( $section['fields'] as $field ) {

                    if( in_array( $field['type'], $unallows ) ) { $field['_notice'] = true; }

                        // Extra tag improves for spesific fields (border, spacing, dimensions etc...)
                        $field['tag_prefix'] = ( ! empty( $field['tag_prefix'] ) ) ? $field['tag_prefix'] .'_' : '';

                        $field_default = $this->get_default( $field );

                        PF::field( $field, $field_default, $shortcode, 'shortcode' );

                    }

                    echo '</div>';

                }

                //
                // View: group and repeater fields
                $repeatable_fields = ( $view === 'repeater' && ! empty( $section['fields'] ) ) ? $section['fields'] : array();
                $repeatable_fields = ( $view === 'group' && ! empty( $section['group_fields'] ) ) ? $section['group_fields'] : $repeatable_fields;

                if( ! empty( $repeatable_fields ) ) {

                    $button_title    = ( ! empty( $section['button_title'] ) ) ? ' '. $section['button_title'] : esc_html__( 'Add one more', 'pf' );
                    $inner_shortcode = ( ! empty( $section['group_shortcode'] ) ) ? $section['group_shortcode'] : $shortcode;

                    echo '<div class="pf--repeatable">';

                    echo '<div class="pf--repeat-shortcode">';

                        echo '<div class="pf-repeat-remove fa fa-times"></div>';

                        echo '<div class="pf-fields">';

                        foreach ( $repeatable_fields as $field ) {

                        if( in_array( $field['type'], $unallows ) ) { $field['_notice'] = true; }

                            // Extra tag improves for spesific fields (border, spacing, dimensions etc...)
                            $field['tag_prefix'] = ( ! empty( $field['tag_prefix'] ) ) ? $field['tag_prefix'] .'_' : '';

                            $field_default = $this->get_default( $field );

                            PF::field( $field, $field_default, $inner_shortcode.'[0]', 'shortcode' );

                        }

                        echo '</div>';

                    echo '</div>';

                    echo '</div>';

                    echo '<div class="pf--repeat-button-block"><a class="button pf--repeat-button" href="#"><i class="fa fa-plus-circle"></i> '. $button_title .'</a></div>';

                }

            }

        } else {
            echo '<div class="pf-field pf-text-error">'. esc_html__( 'Error: Nonce verification has failed. Please try again.', 'pf' ) .'</div>';
        }

        wp_send_json_success( array( 'content' => ob_get_clean() ) );

    }

    /**
     * Once editor setup for gutenberg and media buttons
     *
     * @since   1.5.3   2019-10-01      Release
     * @return  void
     */
    public static function once_editor_setup() {

        if ( function_exists( 'register_block_type' ) ) {
            add_action( 'init', array( 'PF_ClassShortcoder', 'add_guteberg_block' ) );
        }

        if ( pf_wp_editor_api() ) {
            add_action( 'media_buttons', array( 'PF_ClassShortcoder', 'add_media_buttons' ) );
        }

    }

    /**
     * Add gutenberg blocks
     *
     * @since   1.5.3   2019-10-01      Release
     * @return  void
     */
    public static function add_guteberg_block() {

        wp_register_script( 'pf-gutenberg-block', PF::include_plugin_url( 'assets/js/pf-gutenberg-block.js' ), array( 'wp-blocks', 'wp-editor', 'wp-element', 'wp-components' ) );

        wp_localize_script( 'pf-gutenberg-block', 'pf_gutenberg_blocks', PF::$shortcode_instances );

        foreach( PF::$shortcode_instances as $hash => $value ) {

            register_block_type( 'pf-gutenberg-block/block-'. $hash, array(
                'editor_script' => 'pf-gutenberg-block',
            ) );

        }

    }

    /**
     * Add media buttons
     *
     * @since   1.5.3   2019-10-01      Release
     * @param   mixed   $editor_id      Id unicp from the editor
     * @return  string|html
     */
    public static function add_media_buttons( $editor_id ) {

        foreach( PF::$shortcode_instances as $hash => $value ) {
            echo '<a href="#" class="button button-primary pf-shortcode-button" data-editor-id="'. $editor_id .'" data-modal-id="'. $value['modal_id'] .'">'. $value['button_title'] .'</a>';
        }

    }

} }
