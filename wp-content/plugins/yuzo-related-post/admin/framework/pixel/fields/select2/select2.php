<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class field Select2
 *
 * @since   1.4.6    2019-06-12 21:16:19    Release
 *
 */
if( ! class_exists( 'PF_Field_select2' ) ) {
class PF_Field_select2 extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    /**
     * Render field
     *
     * @since   1.4.6   2019-06-12 21:16:03     Release
     * @return  string
     */
    public function render() {

        $args = wp_parse_args( $this->field, array(
            'multiple'    => false,
            'placeholder' => '',
        ) );

        $this->value = ( is_array( $this->value ) ) ? $this->value : array_filter( (array) $this->value );

        echo $this->field_before();

        if( isset( $this->field['options'] ) ) {

            $options          = ( is_array( $this->field['options'] ) ) ? $this->field['options'] : $this->field_data( $this->field['options'] );
            $multiple_name    = ( $args['multiple'] ) ? '[]' : '';
            $multiple_attr    = ( $args['multiple'] ) ? ' multiple="multiple"' : '';
            $width            = $multiple_attr ? ' style="width:100%;"' : ' style="width:200px;"' ;

            if( is_array( $options ) && ! empty( $options ) ) {

                echo '<select name="'. $this->field_name( $multiple_name ) .'"'. $width . $multiple_attr  . $this->field_attributes() .'>';

                foreach ( $options as $option_key => $option ) {

                    if( is_array( $option ) && ! empty( $option ) ) {

                        echo '<optgroup label="'. $option_key .'">';

                        foreach( $option as $sub_key => $sub_value ) {
                            $selected = ( in_array( $sub_key, $this->value ) ) ? ' selected' : '';
                            echo '<option value="'. $sub_key .'" '. $selected .'>'. $sub_value .'</option>';
                        }

                        echo '</optgroup>';

                    } else {
                        $selected = ( in_array( $option_key, $this->value ) ) ? ' selected' : '';
                        echo '<option value="'. $option_key .'" '. $selected .'>'. $option .'</option>';
                    }

                }

                echo '</select>';

            } else {

                echo ( ! empty( $this->field['empty_message'] ) ) ? $this->field['empty_message'] : esc_html__( 'No data provided for this option type.', 'pf' );

            }

        }

        echo $this->field_after();

    }

}
}
