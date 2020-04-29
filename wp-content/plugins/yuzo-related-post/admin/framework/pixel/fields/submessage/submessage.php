<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class Field submessage
 *
 * @since 1.0 2019-03-07 05:24:08 Release
 *
 */
if( ! class_exists( 'PF_Field_submessage' ) ) {
  class PF_Field_submessage extends PF_classFields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $style = ( ! empty( $this->field['style'] ) ) ? $this->field['style'] : 'normal';

      echo '<div class="pf-submessage pf-submessage-'. $style .'">'. $this->field['content'] .'</div>';

    }

  }
}
