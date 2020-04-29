<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Class field color
 *
 * @since 1.0 2019-03-04 05:10:05 Release
 *
 */
if( ! class_exists( 'PF_Field_color' ) ) {
    class PF_Field_color extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

        $default_attr = ( ! empty( $this->field['default'] ) ) ? ' data-default-color="'. $this->field['default'] .'"' : '';

        echo $this->field_before();
        echo '<input type="text" name="'. $this->field_name() .'" value="'. $this->value .'" class="pf-color"'. $default_attr . $this->field_attributes() .'/>';
        echo $this->field_after();

    }

    public function output() {

        $output    = '';
        $elements  = ( is_array( $this->field['output'] ) ) ? $this->field['output'] : array_filter( (array) $this->field['output'] );
        $important = ( ! empty( $this->field['output_important'] ) ) ? '!important' : '';
        $mode      = ( ! empty( $this->field['output_mode'] ) ) ? $this->field['output_mode'] : 'color';

        if( ! empty( $elements ) && isset( $this->value ) && $this->value !== '' ) {
            foreach( $elements as $key_property => $element ) {
                if( is_numeric( $key_property ) ) {
                    $output = implode( ',', $elements ) .'{'. $mode .':'. $this->value . $important .';}';
                    break;
                } else {
                    $output .= $element .'{'. $key_property .':'. $this->value . $important .'}';
                }
            }
        }

        $this->parent->output_css .= $output;

        return $output;

    }

}
}
