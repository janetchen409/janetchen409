<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class field Accordion
 *
 * @since   1.0     2019-03-03  Release
 * @since   1.5.2   2019-08-31  Added collapsible option for accordions
 *
 */
if( ! class_exists( 'PF_Field_accordion' ) ) {
class PF_Field_accordion extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

        $unallows = array( 'accordion' );

        echo $this->field_before();

        $collapsible = ( ! empty( $this->field['collapsible'] ) ) ? ' pf-accordion-collapsible' : '';

        echo '<div class="pf-accordion-items'. $collapsible .'">';

        $i = 1;

        foreach ( $this->field['accordions'] as $key => $accordion ) {

            $icon           = ( ! empty( $accordion['icon'] ) ) ? 'pf--icon '. $accordion['icon'] : 'pf-accordion-icon fa fa-angle-right';
            $open_title     = ( ! empty( $accordion['active'] ) ) ? ' pf-accordion-open-title' : '';
            $open_content   = ( ! empty( $accordion['active'] ) ) ? ' pf-accordion-open' : '';
            $open_accordion = ( ! empty( $accordion['active'] ) ) ? ' pf-accordion-item-open' : '';
            $icon           = ( ! empty( $accordion['active'] ) ) ? 'pf-accordion-icon fa fa-angle-down' : $icon;

            echo '<div class="pf-accordion-item '. $open_accordion .' pf-item-'. $i .'" data-id='. $i .'>';


                echo '<h4 class="pf-accordion-title'.$open_title.'">';
                echo '<i class="'. $icon .'"></i>';
                echo $accordion['title'];
                echo '</h4>';

                echo '<div class="pf-accordion-content'.$open_content.'">';

                foreach ( $accordion['fields'] as $field ) {

                    if( in_array( $field['type'], $unallows ) ) { $field['_notice'] = true; }

                    $field_id      = ( isset( $field['id'] ) ) ? $field['id'] : '';
                    $field_default = ( isset( $field['default'] ) ) ? $field['default'] : '';
                    $field_value   = ( isset( $this->value[$field_id] ) ) ? $this->value[$field_id] : $field_default;
                    $unique_id     = ( ! empty( $this->unique ) ) ? $this->unique .'['. $this->field['id'] .']' : $this->field['id'];

                    PF::field( $field, $field_value, $unique_id, 'field/accordion' );

                }

                echo '</div>';

            echo '</div>';

            $i++;

        }

        echo '</div>';

        echo $this->field_after();

    }

}
}
