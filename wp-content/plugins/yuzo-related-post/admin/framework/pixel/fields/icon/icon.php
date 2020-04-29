<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Class Field icon
 *
 * @since 1.0 2019-03-05 23:03:49 Release
 *
 */
if( ! class_exists( 'PF_Field_icon' ) ) {
	class PF_Field_icon extends PF_classFields {

	public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
	}

	public function render() {

		$args = wp_parse_args( $this->field, array(
			'button_title' => esc_html__( 'Add Icon', 'pf' ),
			'remove_title' => esc_html__( 'Remove Icon', 'pf' ),
		) );

		echo $this->field_before();

		$nonce  = wp_create_nonce( 'pf_icon_nonce' );
		$hidden = ( empty( $this->value ) ) ? ' hidden' : '';

		echo '<div class="pf-icon-select">';
		echo '<span class="pf-icon-preview'. $hidden .'"><i class="'. $this->value .'"></i></span>';
		echo '<a href="#" class="button button-primary pf-icon-add" data-nonce="'. $nonce .'">'. $args['button_title'] .'</a>';
		echo '<a href="#" class="button pf-warning-primary pf-icon-remove'. $hidden .'">'. $args['remove_title'] .'</a>';
		echo '<input type="text" name="'. $this->field_name() .'" value="'. $this->value .'" class="pf-icon-value"'. $this->field_attributes() .' />';
		echo '</div>';

		echo $this->field_after();

	}

	}
}
