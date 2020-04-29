<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Field: switcher
 *
 * @since 1.0 2019-03-05 22:52:20 Release
 *
 */
if( ! class_exists( 'PF_Field_switcher' ) ) {
	class PF_Field_switcher extends PF_classFields {

	public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
	}

	public function render() {

		$active     = ( ! empty( $this->value ) ) ? ' pf--active' : '';
		$text_on    = ( ! empty( $this->field['text_on'] ) ) ? $this->field['text_on'] : 'On';
		$text_off   = ( ! empty( $this->field['text_off'] ) ) ? $this->field['text_off'] : 'Off';
		$text_width = ( ! empty( $this->field['text_width'] ) ) ? ' style="width: '. $this->field['text_width'] .'px;"': '';

		echo $this->field_before();

		echo '<div class="pf--switcher'. $active .'"'. $text_width .'>';
		echo '<span class="pf--on">'. $text_on .'</span>';
		echo '<span class="pf--off">'. $text_off .'</span>';
		echo '<span class="pf--ball"></span>';
		echo '<input type="text" name="'. $this->field_name() .'" value="'. $this->value .'"'. $this->field_attributes() .' />';
		echo '</div>';

		echo ( ! empty( $this->field['label'] ) ) ? '<span class="pf--label">'. $this->field['label'] . '</span>' : '';

		echo '<div class="clear"></div>';

		echo $this->field_after();

	}

}
}
