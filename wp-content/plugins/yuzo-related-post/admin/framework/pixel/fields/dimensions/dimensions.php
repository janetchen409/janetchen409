<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Class field dimensions
 *
 * @since 1.0
 * @since 1.2.4 2019-03-23 14:26:46 -Possibility of changing the type of input
 *                                  -Possibility add atributes
 *
 */
if( ! class_exists( 'PF_Field_dimensions' ) ) {
class PF_Field_dimensions extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    /**
     * Render field
     *
     * @since   1.4.6   2019-06-12  Change of validation in the unit
     * @since   1.6     2019-12-02  - 'Show_units' was added. TRUE shows the units in a select, FALSE does not show it
     *                              - Field wrap added
     * @return  string
     */
    public function render() {

        $args = wp_parse_args( $this->field, array(
            'width_icon'         => '<i class="fa fa-arrows-h"></i>',
            'height_icon'        => '<i class="fa fa-arrows-v"></i>',
            'width_placeholder'  => 'width',
            'height_placeholder' => 'height',
            'width'              => true,
            'height'             => true,
            'unit'               => true,
            'units'              => array( 'px', '%', 'em' ),
            'show_units'         => false,
        ) );

        $default_values = array(
            'width'  => '',
            'height' => '',
            'unit'   => 'px',
        );

        // Assign the type of input
        $type = ( ! empty( $this->field['attributes']['type'] ) ) ? $this->field['attributes']['type'] : 'number';

        $value   = wp_parse_args( $this->value, $default_values );
        $value   = wp_parse_args( $this->value, $default_values );
        $unit    = ( count( $args['units'] ) === 1 && ! empty( $args['unit'] ) ) ? $args['units'][0] : '';
        $is_unit = ( ! empty( $unit ) ) ? ' pf--is-unit' : '';

        echo $this->field_before();

        echo '<div class="pf--inputs">';

        if( ! empty( $args['width'] ) ) {
            $placeholder = ( ! empty( $args['width_placeholder'] ) ) ? ' placeholder="'. $args['width_placeholder'] .'"' : '';
            echo '<div class="pf--input">';
            echo ( ! empty( $args['width_icon'] ) ) ? '<span class="pf--label pf--icon">'. $args['width_icon'] .'</span>' : '';
            echo '<input type="' . $type . '" name="'. $this->field_name('[width]') .'" value="'. $value['width'] .'"'. $placeholder .' class="pf-input-number'. $is_unit .'" '. $this->field_attributes() .' />';
            echo ( ! empty( $unit ) ) ? '<span class="pf--label pf--unit">'. $args['units'][0] .'</span>' : '';
            echo '</div>';

        }

        if( ! empty( $args['height'] ) ) {
            $placeholder = ( ! empty( $args['height_placeholder'] ) ) ? ' placeholder="'. $args['height_placeholder'] .'"' : '';
            echo '<div class="pf--input">';
            echo ( ! empty( $args['height_icon'] ) ) ? '<span class="pf--label pf--icon">'. $args['height_icon'] .'</span>' : '';
            echo '<input type="' . $type . '" name="'. $this->field_name('[height]') .'" value="'. $value['height'] .'"'. $placeholder .' class="pf-input-number'. $is_unit .'" '. $this->field_attributes() .' />';
            echo ( ! empty( $unit ) ) ? '<span class="pf--label pf--unit">'. $args['units'][0] .'</span>' : '';
            echo '</div>';
        }

        if( ! empty( $args['unit'] ) && ! empty( $args['show_units'] ) && count( $args['units'] ) > 1 ) {
            echo '<div class="pf--input">';
            echo '<select name="'. $this->field_name('[unit]') .'">';
            foreach( $args['units'] as $unit ) {
                $selected = ( $value['unit'] === $unit ) ? ' selected' : '';
                echo '<option value="'. $unit .'"'. $selected .'>'. $unit .'</option>';
            }
            echo '</select>';
            echo '</div>';
        }

        echo '</div>';

        echo $this->field_after();

    }

    public function output() {

        $output    = '';
        $element   = ( is_array( $this->field['output'] ) ) ? join( ',', $this->field['output'] ) : $this->field['output'];
        $prefix    = ( ! empty( $this->field['output_prefix'] ) ) ? $this->field['output_prefix'] .'-' : '';
        $important = ( ! empty( $this->field['output_important'] ) ) ? '!important' : '';
        $unit      = ( ! empty( $this->value['unit'] ) ) ? $this->value['unit'] : 'px';
        $width     = ( isset( $this->value['width'] ) && $this->value['width'] !== '' ) ? $prefix .'width:'. $this->value['width'] . $unit . $important .';' : '';
        $height    = ( isset( $this->value['height'] ) && $this->value['width'] !== '' ) ? $prefix .'height:'. $this->value['height'] . $unit . $important .';' : '';

        if( $width !== '' || $height !== '' ) {
            $output = $element .'{'. $width . $height .'}';
        }

        $this->parent->output_css .= $output;

        return $output;

    }

}
}
