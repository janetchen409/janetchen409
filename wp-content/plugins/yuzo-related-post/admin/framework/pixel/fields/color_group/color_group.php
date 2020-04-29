<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Field Color Group
 *
 * @since 1.0 2019-03-04 16:39:29 Release
 *
 */
if( ! class_exists( 'PF_Field_color_group' ) ) {
class PF_Field_color_group extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

        $options = ( ! empty( $this->field['options'] ) ) ? $this->field['options'] : array();

        echo $this->field_before();

        if( ! empty( $options ) ) {
            foreach( $options as $key => $option ) {

                $color_value  = ( ! empty( $this->value[$key] ) ) ? $this->value[$key] : '';
                $default_attr = ( ! empty( $this->field['default'][$key] ) ) ? ' data-default-color="'. $this->field['default'][$key] .'"' : '';

                echo '<div class="pf--left pf-field-color">';
                echo '<div class="pf--title">'. $option .'</div>';
                echo '<input type="text" name="'. $this->field_name('['. $key .']') .'" value="'. $color_value .'" class="pf-color"'. $default_attr . $this->field_attributes() .'/>';
                echo '</div>';

            }
        }

        echo '<div class="clear"></div>';

        echo $this->field_after();

    }

}
}
