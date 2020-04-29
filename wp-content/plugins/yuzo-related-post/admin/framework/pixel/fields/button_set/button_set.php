<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Class Field button_set
 *
 * @since 	1.0 	2019-03-06		Release
 * @since 	1.5.1	2019-07-16		Tooltip is added on the buttons
 * @since	1.5.4	2019-10-26		Now you can get field_data as the 'selects'
 *
 */
if( ! class_exists( 'PF_Field_button_set' ) ) {
class PF_Field_button_set extends PF_classFields {

	public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
	}

	/**
	 * Field Redevelopment
	 *
	 * @since	1.6		2019-12-02		Validation was added to validate the value
	 * @return 	string
	 */
	public function render() {

		$args = wp_parse_args( $this->field, array(
			'multiple' => false,
			'options'  => array(),
			'query_args' => array(),
		) );

		if( is_string( $args['options'] ) ) {
			$options = $this->field_data( $args['options'], false, $args['query_args'] );
		} else {
			$options = $args['options'];
		}

		$value = ( is_array( $this->value ) ) ? $this->value : array_filter( (array) $this->value );

		echo $this->field_before();

		if( ! empty( $options ) ) {

			echo '<div class="pf-siblings pf--button-group" data-multiple="'. $args['multiple'] .'">';

			foreach( $options as $key => $option ) {

				$type    = ( $args['multiple'] ) ? 'checkbox' : 'radio';
				$extra   = ( $args['multiple'] ) ? '[]' : '';
				$active  = ( in_array( $key, $value ) || ( empty( $value ) && empty( $key ) )  ) ? ' pf--active' : '';
				$checked = ( in_array( $key, $value ) || ( empty( $value ) && empty( $key ) ) ) ? ' checked' : '';
				$attribute_sub = ( isset( $args['attributes_sub'] ) ? $args['attributes_sub'] : null );
				/* if( $this->field['id'] == 'design_screen' ) */
				$tooltip = ! empty( $attribute_sub['tooltip'] ) ? $attribute_sub['tooltip'][$key] : null;
				/* if( $args['id'] == 'design_screen' ){
					echo '<pre>$tooltip<br />'; var_dump($tooltip); echo '</pre>';exit;

				} */
				echo '<div class="pf--sibling pf--button'. $active .'" '. $this->field_tooltip($tooltip) .'>';
				echo '<input type="'. $type .'" name="'. $this->field_name( $extra ) .'" value="'. $key .'"'. $this->field_attributes() . $checked .'/>';
				echo $option;
				echo '</div>';

			}

			echo '</div>';

		}

		echo '<div class="clear"></div>';

		echo $this->field_after();

	}

}
}
