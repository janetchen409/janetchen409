<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class Field sub heading
 *
 * @since 1.0 2019-03-07 05:13:46 Release
 * @since 1.0 2019-03-21 02:23:33 Added 'field_before' and 'field_after' in this field
 *
 */
if( ! class_exists( 'PF_Field_subheading' ) ) {
	class PF_Field_subheading extends PF_classFields {

	public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
	}

	public function render() {

		echo $this->field_before();

		echo ( ! empty( $this->field['content'] ) ) ? $this->field['content'] : '';

		echo $this->field_after();

	}

}
}
