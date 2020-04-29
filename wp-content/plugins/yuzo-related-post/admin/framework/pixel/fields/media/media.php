<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: media
 *
 * @since 1.0 2019-03-03 19:37:46 Release
 *
 */
if( ! class_exists( 'PF_Field_media' ) ) {
class PF_Field_media extends PF_classFields{

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

        $args = wp_parse_args( $this->field, array(
            'url'          => true,
            'preview'      => true,
            'library'      => array(),
            'button_title' => esc_html__( 'Upload', 'pf' ),
            'remove_title' => esc_html__( 'Remove', 'pf' ),
            'preview_size' => 'thumbnail',
        ) );

        $default_values = array(
            'url'         => '',
            'id'          => '',
            'width'       => '',
            'height'      => '',
            'thumbnail'   => '',
            'alt'         => '',
            'title'       => '',
            'description' => ''
        );

        $this->value  = wp_parse_args( $this->value, $default_values );

        $library     = ( is_array( $args['library'] ) ) ? $args['library'] : array_filter( (array) $args['library'] );
        $library     = ( ! empty( $library ) ) ? implode(',', $library ) : '';
        $preview_src = ( $args['preview_size'] !== 'thumbnail' ) ? $this->value['url'] : $this->value['thumbnail'];
        $hidden_url  = ( empty( $args['url'] ) ) ? ' hidden' : '';
        $hidden_auto = ( empty( $this->value['url'] ) ) ? ' hidden' : '';
        $placeholder = ( empty( $this->field['placeholder'] ) ) ? ' placeholder="'.  esc_html__( 'No media selected', 'pf' ) .'"' : '';

        echo $this->field_before();

        if( ! empty( $args['preview'] ) ) {
            echo '<div class="pf--preview'. $hidden_auto .'">';
            echo '<div class="pf-image-preview"><a href="#" class="pf--remove fa fa-times"></a><img src="'. $preview_src .'" class="pf--src" alt=""/></div>';
            echo '</div>';
        }

        echo '<div class="pf--placeholder">';
        echo '<input type="text" name="'. $this->field_name('[url]') .'" value="'. $this->value['url'] .'" class="pf--url'. $hidden_url .'" readonly="readonly"'. $this->field_attributes() . $placeholder .' />';
        echo '<a href="#" class="button button-primary pf--button" data-library="'. esc_attr( $library ) .'" data-preview-size="'. esc_attr( $args['preview_size'] ) .'">'. $args['button_title'] .'</a>';
        echo ( empty( $args['preview'] ) ) ? '<a href="#" class="button button-secondary pf-warning-primary pf--remove'. $hidden_auto .'">'. $args['remove_title'] .'</a>' : '';
        echo '</div>';

        echo '<input type="hidden" name="'. $this->field_name('[id]') .'" value="'. $this->value['id'] .'" class="pf--id"/>';
        echo '<input type="hidden" name="'. $this->field_name('[width]') .'" value="'. $this->value['width'] .'" class="pf--width"/>';
        echo '<input type="hidden" name="'. $this->field_name('[height]') .'" value="'. $this->value['height'] .'" class="pf--height"/>';
        echo '<input type="hidden" name="'. $this->field_name('[thumbnail]') .'" value="'. $this->value['thumbnail'] .'" class="pf--thumbnail"/>';
        echo '<input type="hidden" name="'. $this->field_name('[alt]') .'" value="'. $this->value['alt'] .'" class="pf--alt"/>';
        echo '<input type="hidden" name="'. $this->field_name('[title]') .'" value="'. $this->value['title'] .'" class="pf--title"/>';
        echo '<input type="hidden" name="'. $this->field_name('[description]') .'" value="'. $this->value['description'] .'" class="pf--description"/>';

        echo $this->field_after();

    }

}
}
