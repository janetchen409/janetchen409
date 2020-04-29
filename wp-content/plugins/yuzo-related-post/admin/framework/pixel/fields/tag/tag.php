<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class field Tag
 *
 * @since   2019-04-20 18:37:03     Release
 */
if( ! class_exists( 'PF_Field_tag' ) ) {
class PF_Field_tag extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

		$type = ( ! empty( $this->field['attributes']['type'] ) ) ? $this->field['attributes']['type'] : 'text';

		echo $this->field_before();

		echo '<textarea class="tag-editor" type="'. $type .'" name="'. $this->field_name() .'" '.  $this->field_attributes() . ' '.  $this->field_attributes_js() . ' >'. $this->value .'</textarea>';

		echo $this->field_after();

	}

}
}