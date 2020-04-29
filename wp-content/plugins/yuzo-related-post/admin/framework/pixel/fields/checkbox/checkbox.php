<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class field Checkbox
 *
 * @since 1.0 2019-03-02 17:24:32 Release
 *
 */
if( ! class_exists( 'PF_Field_checkbox' ) ) {
class PF_Field_checkbox extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    /**
     * Render field
     *
     * @since   1.4.6   2019-06-12  Change of validation in the check list
     * @since   1.6     2019-12-02  '_pseudo' was added in the checkbox field
     * @return  string
     */
    public function render() {

        $args = wp_parse_args( $this->field, array(
            'inline'     => false,
            'query_args' => array(),
        ) );

        $inline_class = ( $args['inline'] ) ? ' class="pf--inline-list"' : '';

        echo $this->field_before();

        if( isset( $this->field['options'] ) ) {

            $value   = ( is_array( $this->value ) ) ? $this->value : array_filter( (array) $this->value );
            $options = $this->field['options'];
            $options = ( is_array( $options ) ) ? $options : array_filter( $this->field_data( $options, false, $args['query_args'] ) );

            if( is_array( $options ) && ! empty( $options ) ) {

                echo '<ul'. $inline_class .'>';
                foreach ( $options as $option_key => $option_value ) {

                    if( is_array( $option_value ) && ! empty( $option_value ) ) {

                        echo '<li>';
                        echo '<ul>';
                        echo '<li><strong>'. $option_key .'</strong></li>';
                        foreach( $option_value as $sub_key => $sub_value ) {
                            $checked = ( in_array( $sub_key, $value ) ) ? ' checked' : '';
                            echo '<li><label><input type="checkbox" name="'. $this->field_name( '[]' ) .'" value="'. $sub_key .'"'. $this->field_attributes() . $checked .'/> '. $sub_value .'</label></li>';
                        }
                        echo '</ul>';
                        echo '</li>';

                    } else {

                        $checked = ( in_array( $option_key, $value ) ) ? ' checked' : '';
                        echo '<li><label><input type="checkbox" name="'. $this->field_name( '[]' ) .'" value="'. $option_key .'"'. $this->field_attributes() . $checked .'/> '. $option_value .'</label></li>';

                    }

                }
                echo '</ul>';

            } else {

                echo ( ! empty( $this->field['empty_message'] ) ) ? $this->field['empty_message'] : esc_html__( 'No data provided for this option type.', 'pf' );

            }

        } else {
            echo '<label class="pf-checkbox">';
            echo '<input type="hidden" name="'. $this->field_name() .'" value="'. $this->value .'" class="pf--input"'. $this->field_attributes() .'/>';
            echo '<input type="checkbox" name="_pseudo" class="pf--checkbox"'. checked( $this->value, 1, false ) .'/>';
            echo ( ! empty( $this->field['label'] ) ) ? ' '. $this->field['label'] : '';
            echo '</label>';
        }

        echo $this->field_after();

    }

}
}
