<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class field Gallery
 *
 * @since 1.0 2019-03-03 22:43:14 Release
 *
 */
if( ! class_exists( 'PF_Field_gallery' ) ) {
class PF_Field_gallery extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

        $args = wp_parse_args( $this->field, array(
            'add_title'   => esc_html__( 'Add Gallery', 'pf' ),
            'edit_title'  => esc_html__( 'Edit Gallery', 'pf' ),
            'clear_title' => esc_html__( 'Clear', 'pf' ),
        ) );

        $hidden = ( empty( $this->value ) ) ? ' hidden' : '';

        echo $this->field_before();

        echo '<ul>';

        if( ! empty( $this->value ) ) {

            $values = explode( ',', $this->value );

            foreach ( $values as $id ) {
                $attachment = wp_get_attachment_image_src( $id, 'thumbnail' );
                echo '<li><img src="'. $attachment[0] .'" alt="" /></li>';
            }

        }

        echo '</ul>';
        echo '<a href="#" class="button button-primary pf-button">'. $args['add_title'] .'</a>';
        echo '<a href="#" class="button pf-edit-gallery'. $hidden .'">'. $args['edit_title'] .'</a>';
        echo '<a href="#" class="button pf-warning-primary pf-clear-gallery'. $hidden .'">'. $args['clear_title'] .'</a>';
        echo '<input type="text" name="'. $this->field_name() .'" value="'. $this->value .'"'. $this->field_attributes() .'/>';

        echo $this->field_after();

    }

}
}
