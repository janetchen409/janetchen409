<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Class Field spinner
 *
 * @since 1.0 2019-03-05 18:52:56 Release
 *
 */
if( ! class_exists( 'PF_Field_spinner' ) ) {
class PF_Field_spinner extends PF_classFields {

	public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
	}

	/**
     * Render field
	 *
     * @since   1.6     2019-12-02  Wrap removed and input field simplified
     * @return  string
     */
	public function render() {

		$args = wp_parse_args( $this->field, array(
			'max'  => 100,
			'min'  => 0,
			'step' => 1,
			'unit' => '',
		) );

		echo $this->field_before();
		echo '<div class="pf--spin"><input type="number" name="'. $this->field_name() .'" value="'. $this->value .'"'. $this->field_attributes( array( 'class' => 'pf-input-number' ) ) .' data-max="'. $args['max'] .'" data-min="'. $args['min'] .'" data-step="'. $args['step'] .'" data-unit="'. $args['unit'] .'"/></div>';
		echo $this->field_after();

	}

	public function enqueue() {

		if( ! wp_script_is( 'jquery-ui-spinner' ) ) {
			wp_enqueue_script( 'jquery-ui-spinner' );
		}

	}

	public function output() {

		$output    = '';
		$elements  = ( is_array( $this->field['output'] ) ) ? $this->field['output'] : array_filter( (array) $this->field['output'] );
		$important = ( ! empty( $this->field['output_important'] ) ) ? '!important' : '';
		$mode      = ( ! empty( $this->field['output_mode'] ) ) ? $this->field['output_mode'] : 'width';
		$unit      = ( ! empty( $this->field['unit'] ) ) ? $this->field['unit'] : 'px';

		if( ! empty( $elements ) && isset( $this->value ) && $this->value !== '' ) {
			foreach( $elements as $key_property => $element ) {
				if( is_numeric( $key_property ) ) {
					if( $mode ) {
						$output = implode( ',', $elements ) .'{'. $mode .':'. $this->value . $unit . $important .';}';
					}
					break;
				} else {
					$output .= $element .'{'. $key_property .':'. $this->value . $unit . $important .'}';
				}
			}
		}

		$this->parent->output_css .= $output;

		return $output;

	}

}
}
