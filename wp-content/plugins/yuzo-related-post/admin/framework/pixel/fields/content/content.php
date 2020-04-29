<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class Field content
 *
 * @since 	1.0
 * @since	1.4.9	2019-07-12 07:40:47		Now it has the attributes before and after
 *
 */
if( ! class_exists( 'PF_Field_content' ) ) {
class PF_Field_content extends PF_classFields {

	public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
	}

	public function render() {
		echo $this->field_before();
		echo $this->field['content'];
		echo $this->field_after();
	}

}
}