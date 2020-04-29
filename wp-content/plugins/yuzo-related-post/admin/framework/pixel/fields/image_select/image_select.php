<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Class Field Image Select
 *
 * @since 1.0
 *
 */
if( ! class_exists( 'PF_Field_image_select' ) ) {
	class PF_Field_image_select extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    /**
     * Render field
     *
     * @since   1.4.6   2019-06-12 21:07:28     Now there is the possibility of adding text for the images
     * @return  string
     */
    public function render() {

      $args = wp_parse_args( $this->field, array(
        'multiple' => false,
        'options'  => array(),
        'texts'    => array(),
      ) );

      $value = ( is_array( $this->value ) ) ? $this->value : array_filter( (array) $this->value );

      echo $this->field_before();

      if( ! empty( $args['options'] ) ) {

        echo '<div class="pf-siblings pf--image-group" data-multiple="'. $args['multiple'] .'">';

        $num = 1;

        foreach( $args['options'] as $key => $option ) {

          $type    = ( $args['multiple'] ) ? 'checkbox' : 'radio';
          $extra   = ( $args['multiple'] ) ? '[]' : '';
          $active  = ( in_array( $key, $value ) ) ? ' pf--active' : '';
          $checked = ( in_array( $key, $value ) ) ? ' checked' : '';
          $text    = ( ! empty( $args['texts'][$key] ) ) ? ' <label>'. $args['texts'][$key] .'</label> ' : '';

          echo '<div class="pf--sibling pf--image'. $active .'">';
          echo '<img src="'. $option .'" alt="img-'. $num++ .'" ' . $this->field_attributes() .' />';
          echo '<input type="'. $type .'" name="'. $this->field_name( $extra ) .'" value="'. $key .'"'. $this->field_attributes() . $checked .'/>';
          echo $text;
          echo '</div>';

        }

        echo '</div>';

      }

      echo '<div class="clear"></div>';

      echo $this->field_after();

    }

    public function output() {

      $output    = '';
      $bg_image  = array();
      $important = ( ! empty( $this->field['output_important'] ) ) ? '!important' : '';
      $elements  = ( is_array( $this->field['output'] ) ) ? join( ',', $this->field['output'] ) : $this->field['output'];

      if( ! empty( $elements ) && isset( $this->value ) && $this->value !== '' ) {
        $output = $elements .'{background-image:url('. $this->value .')'. $important .';}';
      }

      $this->parent->output_css .= $output;

      return $output;

    }

  }
}
