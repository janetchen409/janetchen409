<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Class Field Palette
 *
 * @since 1.0 2019-03-04 17:06:43 Release
 *
 */
if( ! class_exists( 'PF_Field_palette' ) ) {
class PF_Field_palette extends PF_classFields {

	public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
	}

	public function render() {

		$palette = ( ! empty( $this->field['options'] ) ) ? $this->field['options'] : array();

		echo $this->field_before();

		if( ! empty( $palette ) ) {

			echo '<div class="pf-siblings pf--palettes">';

			foreach ( $palette as $key => $colors ) {

				$active  = ( $key === $this->value ) ? ' pf--active' : '';
				$checked = ( $key === $this->value ) ? ' checked' : '';

				echo '<div class="pf--sibling pf--palette'. $active .'">';

				if( ! empty( $colors ) ) {

					foreach( $colors as $color ) {

					echo '<span style="background-color: '. $color .';"></span>';

					}

				}

				echo '<input type="radio" name="'. $this->field_name() .'" value="'. $key .'"'. $this->field_attributes() . $checked .'/>';
				echo '</div>';

			}

			echo '</div>';

		}

		echo '<div class="clear"></div>';

		echo $this->field_after();

	}

}
}
