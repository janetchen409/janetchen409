<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Class field fieldset
 *
 * @since 1.0 2019-03-03 17:43:21 Release
 *
 */
if( ! class_exists( 'PF_Field_fieldset' ) ) {
class PF_Field_fieldset extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

        echo $this->field_before();

        $inline     = ! empty( $this->field['inline'] ) ? 'inline-fields' : '';
        $twocolumns = ! empty( $this->field['2columns'] ) ? 'pf-2columns' : '';

        echo '<div class="pf-fieldset-content '. $inline .' '. $twocolumns .'">';

        foreach ( $this->field['fields'] as $field ) {

            $field_id      = ( isset( $field['id'] ) ) ? $field['id'] : '';
            $field_default = ( isset( $field['default'] ) ) ? $field['default'] : '';
            $field_value   = ( isset( $this->value[$field_id] ) ) ? $this->value[$field_id] : $field_default;
            $unique_id     = ( ! empty( $this->unique ) ) ? $this->unique .'['. $this->field['id'] .']' : $this->field['id'];

            PF::field( $field, $field_value, $unique_id, 'field/fieldset' );

        }

        echo '</div>';

        echo $this->field_after();

    }

}
}
