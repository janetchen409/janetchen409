<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class field tab
 *
 * @since 1.0 2019-03-03 17:10:32 Release
 *
 */
if( ! class_exists( 'PF_Field_tab' ) ) {
class PF_Field_tab extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

        $unallows = array( 'tab' );

        echo $this->field_before();

        echo '<div class="pf-tab-nav">';
        foreach ( $this->field['tabs'] as $key => $tab ) {

            $tab_icon   = ( ! empty( $tab['icon'] ) ) ? '<i class="pf--icon '. $tab['icon'] .'"></i>' : '';
            $tab_active = ( empty( $key ) ) ? ' class="pf-tab-active"' : '';

            echo '<a href="#"'. $tab_active .'>'. $tab_icon . $tab['title'] .'</a>';

        }
        echo '</div>';

        echo '<div class="pf-tab-sections">';
        foreach ( $this->field['tabs'] as $key => $tab ) {

            $tab_hidden = ( ! empty( $key ) ) ? ' hidden' : '';

            echo '<div class="pf-tab-section'. $tab_hidden .'">';

            foreach ( $tab['fields'] as $field ) {

                if( in_array( $field['type'], $unallows ) ) { $field['_notice'] = true; }

                $field_id      = ( isset( $field['id'] ) ) ? $field['id'] : '';
                $field_default = ( isset( $field['default'] ) ) ? $field['default'] : '';
                $field_value   = ( isset( $this->value[$field_id] ) ) ? $this->value[$field_id] : $field_default;
                $unique_id     = ( ! empty( $this->unique ) ) ? $this->unique .'['. $this->field['id'] .']' : $this->field['id'];

                PF::field( $field, $field_value, $unique_id, 'field/tab' );

            }

            echo '</div>';

        }
        echo '</div>';

        echo $this->field_after();

    }

}
}
