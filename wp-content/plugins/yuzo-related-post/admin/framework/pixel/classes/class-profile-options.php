<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
if( ! class_exists( 'PF_classProfile_Options' ) ) {
/**
 *
 * Profile Options Class
 *
 * @package PF
 * @subpackage PF/Classes
 *
 * @since   1.5.3   2019-09-30  Release
 *
 */
class PF_classProfileOptions extends PF_classAbstract{

    // ─── Constans class ────────
    public $unique   = '';
    public $abstract = 'profile';
    public $sections = array();
    public $args     = array(
        'data_type'    => 'serialize',
        'defaults'     => array(),
        'class'        => '',
    );

    /**
     * Run profile construct
     *
     * @since   1.5.3           2019-09-30      Release
     *
     * @param   string          $key            Id
     * @param   array|mixed     $params         Configuration of the same
     */
    public function __construct( $key, $params ) {

        $this->unique   = $key;
        $this->args     = apply_filters( "pf_{$this->unique}_args", wp_parse_args( $params['args'], $this->args ), $this );
        $this->sections = apply_filters( "pf_{$this->unique}_sections", $params['sections'], $this );

        add_action( 'admin_init', array( &$this, 'add_profile_options' ) );

    }

    /**
     * Instance
     *
     * @since   1.5.3           2019-09-30      Release
     *
     * @param   string          $key            Id
     * @param   array|mixed     $params         Configuration of the same
     */
    public static function instance( $key, $params ) {
        return new self( $key, $params );
    }

    /**
     * Add profile add/edit fields
     *
     * @since   1.5.3       2019-10-01      Release
     * @return void
     */
    public function add_profile_options() {

        add_action( 'show_user_profile', array( &$this, 'render_profile_form_fields' ) );
        add_action( 'edit_user_profile', array( &$this, 'render_profile_form_fields' ) );

        add_action( 'personal_options_update', array( &$this, 'save_profile' ) );
        add_action( 'edit_user_profile_update', array( &$this, 'save_profile' ) );

    }

    /**
     * Get default value
     *
     * @param   1.5.3   $field  Field that is required by default
     * @return  mixed
     */
    public function get_default( $field ) {

        $default = ( isset( $field['id'] ) && isset( $this->args['defaults'][$field['id']] ) ) ? $this->args['defaults'][$field['id']] : null;
        $default = ( isset( $field['default'] ) ) ? $field['default'] : $default;

        return $default;

    }

    /**
     * Get default value
     *
     * @since   1.5.3       2019-10-01  Release
     *
     * @param   $user_id    $user_id    User ID
     * @param   $field      $field      Field that is required to save
     * @return  void
     */
    public function get_meta_value( $user_id, $field ) {

        $value = null;

        if( ! empty( $user_id ) && ! empty( $field['id'] ) ) {

            if( $this->args['data_type'] !== 'serialize' ) {
                $meta  = get_user_meta( $user_id, $field['id'] );
                $value = ( isset( $meta[0] ) ) ? $meta[0] : null;
            } else {
                $meta  = get_user_meta( $user_id, $this->unique, true );
                $value = ( isset( $meta[$field['id']] ) ) ? $meta[$field['id']] : null;
            }

        }

        $default = $this->get_default( $field );
        $value   = ( isset( $value ) ) ? $value : $default;

        return $value;

    }

    /**
     * Render profile add/edit form fields
     *
     * @since   1.5.3   2019-10-01      Release
     *
     * @param   object  $profileuser    Objeto de datos del perfil
     * @return  string|html
     */
    public function render_profile_form_fields( $profileuser ) {

        $is_profile = ( is_object( $profileuser ) && isset( $profileuser->ID ) ) ? true : false;
        $profile_id = ( $is_profile ) ? $profileuser->ID : 0;
        $errors     = ( ! empty( $profile_id ) ) ? get_user_meta( $profile_id, '_pf_errors', true ) : array();
        $errors     = ( ! empty( $errors ) ) ? $errors : array();
        $class      = ( $this->args['class'] ) ? ' '. $this->args['class'] : '';

        // clear errors
        if( ! empty( $errors ) ) {
            delete_user_meta( $profile_id, '_pf_errors' );
        }

        echo '<div class="pf pf-profile pf-onload'. $class .'">';

        wp_nonce_field( 'pf_profile_nonce', 'pf_profile_nonce'. $this->unique );

        foreach( $this->sections as $section ) {

            $section_icon  = ( ! empty( $section['icon'] ) ) ? '<i class="pf-icon '. $section['icon'] .'"></i>' : '';
            $section_title = ( ! empty( $section['title'] ) ) ? $section['title'] : '';

            echo ( $section_title || $section_icon ) ? '<h2>'. $section_icon . $section_title .'</h2>' : '';

            if( ! empty( $section['fields'] ) ) {
                foreach( $section['fields'] as $field ) {

                    if( ! empty( $field['id'] ) && ! empty( $errors[$field['id']] ) ) {
                        $field['_error'] = $errors[$field['id']];
                    }

                    PF::field( $field, $this->get_meta_value( $profile_id, $field ), $this->unique, 'profile' );

                }
            }

        }

        echo '</div>';

    }

    /**
     * Save profile form fields
     *
     * @since   1.5.3       2019-10-01      Release
     *
     * @param   int         $user_id        User ID
     * @return  void
     */
    public function save_profile( $user_id ) {

        if ( wp_verify_nonce( pf_get_var( 'pf_profile_nonce'. $this->unique ), 'pf_profile_nonce' ) ) {

            $errors = array();

            foreach ( $this->sections as $section ) {

                $request = pf_get_var( $this->unique, array() );

                // ignore _nonce
                if( isset( $request['_nonce'] ) ) {
                    unset( $request['_nonce'] );
                }

                // sanitize and validate
                if( ! empty( $section['fields'] ) ) {

                    foreach( $section['fields'] as $field ) {

                        if( ! empty( $field['id'] ) ) {

                            // sanitize
                            if( ! empty( $field['sanitize'] ) ) {

                                $sanitize              = $field['sanitize'];
                                $value_sanitize        = pf_get_vars( $this->unique, $field['id'] );
                                $request[$field['id']] = call_user_func( $sanitize, $value_sanitize );

                            }

                            // validate
                            if( ! empty( $field['validate'] ) ) {

                                $validate = $field['validate'];
                                $value_validate = pf_get_vars( $this->unique, $field['id'] );
                                $has_validated = call_user_func( $validate, $value_validate );

                                if( ! empty( $has_validated ) ) {

                                    $errors[$field['id']]  = $has_validated;
                                    $request[$field['id']] = $this->get_meta_value( $user_id, $field );

                                }

                            }

                            // auto sanitize
                            if( ! isset( $request[$field['id']] ) || is_null( $request[$field['id']] ) ) {
                                $request[$field['id']] = '';
                            }

                        }

                    }

                }

                $request = apply_filters( "pf_{$this->unique}_save", $request, $user_id, $this );

                do_action( "pf_{$this->unique}_save_before", $request, $user_id, $this );

                if( empty( $request ) ) {

                    if( $this->args['data_type'] !== 'serialize' ) {
                        foreach ( $request as $key => $value ) {
                            delete_user_meta( $user_id, $key );
                        }
                    } else {
                        delete_user_meta( $user_id, $this->unique );
                    }

                } else {

                if( $this->args['data_type'] !== 'serialize' ) {
                    foreach ( $request as $key => $value ) {
                        update_user_meta( $user_id, $key, $value );
                    }
                } else {
                    update_user_meta( $user_id, $this->unique, $request );
                }

                if( ! empty( $errors ) ) {
                    update_user_meta( $user_id, '_pf_errors', $errors );
                }

                }

                do_action( "pf_{$this->unique}_saved", $request, $user_id, $this );

                do_action( "pf_{$this->unique}_save_after", $request, $user_id, $this );

            }

        }

    }

} }