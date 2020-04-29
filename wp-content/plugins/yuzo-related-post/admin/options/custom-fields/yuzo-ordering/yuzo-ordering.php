<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class field Yuzo Ordering
 *
 * Is surprised to show a set button for the order and a select for the
 * order values
 *
 * @since   6.0     2019-04-20 22:29:18     Release
 *
 */
if( ! class_exists( 'PF_Field_yuzo_ordering' ) ) {
    class PF_Field_yuzo_ordering extends PF_classFields{

        public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
            parent::__construct( $field, $value, $unique, $where, $parent );
        }

        public function render() {

            $args = wp_parse_args( $this->field, array(
                'b_multiple' => false,
                'b_options'  => array(),
            ) );

            $value = ( is_array( $this->value ) ) ? $this->value : array_filter( (array) $this->value );


            echo $this->field_before();

            if( ! empty( $args['b_options'] ) ) {

                echo '<div class="pf-siblings pf--button-group" data-multiple="'. $args['b_multiple'] .'">';

                foreach( $args['b_options'] as $key => $option ) {

                    $type    = ( $args['b_multiple'] ) ? 'checkbox' : 'radio';
                    $extra   = ( $args['b_multiple'] ) ? 'order[]' : 'order';
                    $active  = ( in_array( $key, $value ) ) ? ' pf--active' : '';
                    $checked = ( in_array( $key, $value ) ) ? ' checked' : '';

                    echo '<div class="pf--sibling pf--button'. $active .'">';
                    echo '<input type="'. $type .'" name="'. $this->field_name( $extra ) .'" value="'. $key .'"'. $this->field_attributes() . $checked .'/>';
                    echo $option;
                    echo '</div>';

                }

                echo '</div>';

            }




            $args = wp_parse_args( $this->field, array(
                's_chosen'      => false,
                's_multiple'    => false,
                's_placeholder' => '',
            ) );

            $this->value = ( is_array( $this->value ) ) ? $this->value : array_filter( (array) $this->value );

            if( ! empty( $this->field['s_options'] ) ) {

                $options          = ( is_array( $this->field['s_options'] ) ) ? $this->field['s_options'] : $this->field_data( $this->field['s_options'] );
                $multiple_name    = ( $args['s_multiple'] ) ? 'by[]' : 'by';
                $multiple_attr    = ( $args['s_multiple'] ) ? ' multiple="multiple"' : '';
                $chosen_rtl       = ( is_rtl() ) ? ' chosen-rtl' : '';
                $chosen_attr      = ( $args['s_chosen'] ) ? ' class="pf-chosen'. $chosen_rtl .'"' : '';
                $placeholder_attr = ( $args['s_chosen'] && $args['s_placeholder'] ) ? ' data-placeholder="'. $args['s_placeholder'] .'"' : '';

                if( ! empty( $options ) ) {

                    echo '<select name="'. $this->field_name( $multiple_name ) .'"'. $multiple_attr . $chosen_attr . $placeholder_attr . $this->field_attributes() .'>';

                    if( $args['s_placeholder'] && empty( $args['s_multiple'] ) ) {
                        if( ! empty( $args['s_chosen'] ) ) {
                            echo '<option value=""></option>';
                        } else {
                            echo '<option value="">'. $args['s_placeholder'] .'</option>';
                        }
                    }

                    foreach ( $options as $option_key => $option ) {

                        if( is_array( $option ) && ! empty( $option ) ) {

                            echo '<optgroup label="'. $option_key .'">';

                            foreach( $option as $sub_key => $sub_value ) {
                                $selected = ( in_array( $sub_key, $this->value ) ) ? ' selected' : '';
                                echo '<option value="'. $sub_key .'" '. $selected .'>'. $sub_value .'</option>';
                            }

                            echo '</optgroup>';

                        } else {
                            $selected = ( in_array( $option_key, $this->value ) ) ? ' selected' : '';
                            echo '<option value="'. $option_key .'" '. $selected .'>'. $option .'</option>';
                        }

                    }

                    echo '</select>';

                } else {

                    echo ( ! empty( $this->field['empty_message'] ) ) ? $this->field['empty_message'] : esc_html__( 'No data provided for this option type.', 'pf' );

                }

            }


            echo $this->field_after();
        }

    }
}