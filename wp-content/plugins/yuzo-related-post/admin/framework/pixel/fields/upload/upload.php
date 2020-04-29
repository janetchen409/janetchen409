<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Class field upload
 *
 * @since 1.0 2019-03-03 18:16:53 Release
 *
 */
if( ! class_exists( 'PF_Field_upload' ) ) {
class PF_Field_upload extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    /**
     * Render field
     *
     * @since   1.6     2019-12-02  Class Update
     * @return  string
     */
    public function render() {

        $args = wp_parse_args( $this->field, array(
            'library'      => array(),
            'button_title' => esc_html__( 'Upload', 'pf' ),
            'remove_title' => esc_html__( 'Remove', 'pf' ),
        ) );

        echo $this->field_before();

        $library = ( is_array( $args['library'] ) ) ? $args['library'] : array_filter( (array) $args['library'] );
        $library = ( ! empty( $library ) ) ? implode(',', $library ) : '';
        $hidden  = ( empty( $this->value ) ) ? ' hidden' : '';

        echo '<div class="pf--wrap">';
        echo '<input type="text" name="'. $this->field_name() .'" value="'. $this->value .'"'. $this->field_attributes() .'/>';

        echo '<a href="#" class="button button-primary pf--button" data-library="'. esc_attr( $library ) .'">'. $args['button_title'] .'</a>';
        echo '<a href="#" class="button button-secondary pf-warning-primary pf--remove'. $hidden .'">'. $args['remove_title'] .'</a>';
        echo '</div>';


        echo $this->field_after();

    }
}
}
