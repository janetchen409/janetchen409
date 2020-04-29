<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class field Textarea
 *
 * @since 1.0 2019-03-01 18:05:00 Release
 */
if( ! class_exists( 'PF_Field_textarea' ) ) {
class PF_Field_textarea extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

		echo $this->field_before();
		echo $this->shortcoder();
		echo '<textarea name="'. $this->field_name() .'"'. $this->field_attributes() .'>'. $this->value .'</textarea>';

		echo $this->field_after();

	}

	public function shortcoder() {

		if( ! empty( $this->field['shortcoder'] ) ) {

			$shortcoders = ( is_array( $this->field['shortcoder'] ) ) ? $this->field['shortcoder'] : array_filter( (array) $this->field['shortcoder'] );

			foreach( $shortcoders as $shortcode_id ) {

				if( isset( PF::$shortcode_instances_key[$shortcode_id] ) ) {

					$setup_args   = PF::$shortcode_instances_key[$shortcode_id];
					$button_title = ( ! empty( $setup_args['button_title'] ) ) ? $setup_args['button_title'] : esc_html__( 'Add Shortcode', 'pf' );

					echo '<a href="#" class="button button-primary pf-shortcode-button" data-modal-id="'. $shortcode_id .'">'. $button_title .'</a>';

				}

			}

		}

	}

}}