<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class field Text
 *
 * @since 1.0 2019-02-25 13:44:36 Release
 */
if( ! class_exists( 'PF_Field_text' ) ) {
class PF_Field_text extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

		$type = ( ! empty( $this->field['attributes']['type'] ) ) ? $this->field['attributes']['type'] : 'text';

		echo $this->field_before();

		echo '<input type="'. $type .'" name="'. $this->field_name() .'" value="'. $this->value .'"'. $this->field_attributes() .' />';

		echo $this->field_after();

	}

}
}