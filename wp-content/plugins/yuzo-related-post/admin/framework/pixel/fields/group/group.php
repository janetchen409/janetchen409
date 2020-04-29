<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class field Group
 *
 * @since 1.0 2019-03-03 04:35:26 Release
 *
 */
if( ! class_exists( 'PF_Field_group' ) ) {
class PF_Field_group extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

        $args = wp_parse_args( $this->field, array(
            'max'                    => 0,
            'min'                    => 0,
            'fields'                 => array(),
            'button_title'           => esc_html__( 'Add New', 'pf' ),
            'accordion_title_prefix' => '',
            'accordion_title_number' => false,
            'accordion_title_auto'   => true,
        ) );

        $title_prefix = ( ! empty( $args['accordion_title_prefix'] ) ) ? $args['accordion_title_prefix'] : '';
        $title_number = ( ! empty( $args['accordion_title_number'] ) ) ? true : false;
        $title_auto   = ( ! empty( $args['accordion_title_auto'] ) ) ? true : false;

        if( ! empty( $this->parent ) && preg_match( '/'. preg_quote( '['. $this->field['id'] .']' ) .'/', $this->parent ) ) {

            echo '<div class="pf-notice pf-notice-danger">'. esc_html__( 'Error: Nested field id can not be same with another nested field id.', 'pf' ) .'</div>';

        } else {

            echo $this->field_before();

            echo '<div class="pf-cloneable-item pf-cloneable-hidden">';

            echo '<div class="pf-cloneable-helper">';
            echo '<i class="pf-cloneable-sort fa fa-arrows"></i>';
            echo '<i class="pf-cloneable-clone fa fa-clone"></i>';
            echo '<i class="pf-cloneable-remove pf-confirm fa fa-times" data-confirm="'. esc_html__( 'Are you sure to delete this item?', 'pf' ) .'"></i>';
            echo '</div>';

            echo '<h4 class="pf-cloneable-title">';
            echo '<span class="pf-cloneable-text">';
            echo ( $title_number ) ? '<span class="pf-cloneable-title-number"></span>' : '';
            echo ( $title_prefix ) ? '<span class="pf-cloneable-title-prefix">'. $title_prefix .'</span>' : '';
            echo ( $title_auto ) ? '<span class="pf-cloneable-value"><span class="pf-cloneable-placeholder"></span></span>' : '';
            echo '</span>';
            echo '</h4>';

            echo '<div class="pf-cloneable-content">';
            foreach ( $this->field['fields'] as $field ) {

                $field_parent  = $this->parent .'['. $this->field['id'] .']';
                $field_default = ( isset( $field['default'] ) ) ? $field['default'] : '';

                PF::field( $field, $field_default, '_nonce', 'field/group', $field_parent );

            }
            echo '</div>';

            echo '</div>';

            echo '<div class="pf-cloneable-wrapper pf-data-wrapper" data-title-number="'. $title_number .'" data-unique-id="'. $this->unique .'" data-field-id="['. $this->field['id'] .']" data-max="'. $args['max'] .'" data-min="'. $args['min'] .'">';

            if( ! empty( $this->value ) ) {

                $num = 0;

                foreach ( $this->value as $value ) {

                    $first_id    = ( isset( $this->field['fields'][0]['id'] ) ) ? $this->field['fields'][0]['id'] : '';
                    $first_value = ( isset( $value[$first_id] ) ) ? $value[$first_id] : '';

                    echo '<div class="pf-cloneable-item">';

                        echo '<div class="pf-cloneable-helper">';
                        echo '<i class="pf-cloneable-sort fa fa-arrows"></i>';
                        echo '<i class="pf-cloneable-clone fa fa-clone"></i>';
                        echo '<i class="pf-cloneable-remove pf-confirm fa fa-times" data-confirm="'. esc_html__( 'Are you sure to delete this item?', 'pf' ) .'"></i>';
                        echo '</div>';

                        echo '<h4 class="pf-cloneable-title">';
                        echo '<span class="pf-cloneable-text">';
                        echo ( $title_number ) ? '<span class="pf-cloneable-title-number">'. ( $num+1 ) .'.</span>' : '';
                        echo ( $title_prefix ) ? '<span class="pf-cloneable-title-prefix">'. $title_prefix .'</span>' : '';
                        echo ( $title_auto ) ? '<span class="pf-cloneable-value">' . $first_value .'</span>' : '';
                        echo '</span>';
                        echo '</h4>';

                        echo '<div class="pf-cloneable-content">';

                        foreach ( $this->field['fields'] as $field ) {

                            $field_parent  = $this->parent .'['. $this->field['id'] .']';
                            $field_unique = ( ! empty( $this->unique ) ) ? $this->unique .'['. $this->field['id'] .']['. $num .']' : $this->field['id'] .'['. $num .']';
                            $field_value  = ( isset( $field['id'] ) && isset( $value[$field['id']] ) ) ? $value[$field['id']] : '';

                            PF::field( $field, $field_value, $field_unique, 'field/group', $field_parent );

                        }

                        echo '</div>';

                    echo '</div>';

                    $num++;

                }

            }

            echo '</div>';

            echo '<div class="pf-cloneable-alert pf-cloneable-max">'. esc_html__( 'You can not add more than', 'pf' ) .' '. $args['max'] .'</div>';
            echo '<div class="pf-cloneable-alert pf-cloneable-min">'. esc_html__( 'You can not remove less than', 'pf' ) .' '. $args['min'] .'</div>';

            echo '<a href="#" class="button button-primary pf-cloneable-add">'. $args['button_title'] .'</a>';

            echo $this->field_after();

        }

    }

    public function enqueue() {

        if( ! wp_script_is( 'jquery-ui-accordion' ) ) {
            wp_enqueue_script( 'jquery-ui-accordion' );
        }

        if( ! wp_script_is( 'jquery-ui-sortable' ) ) {
            wp_enqueue_script( 'jquery-ui-sortable' );
        }

    }

    public function aa(){}

}
}
