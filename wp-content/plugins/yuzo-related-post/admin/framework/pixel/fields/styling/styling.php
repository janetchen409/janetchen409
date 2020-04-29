<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 *
 * Class field styling
 *
 * @since   1.4.7   2019-06-12 22:40:49     Release
 *
 */
if( ! class_exists( 'PF_Field_styling' ) ) {
class PF_Field_styling extends PF_classFields {

    public  $value  = array(),
            $version = '5.41.0',
            $cdn_url = 'https://cdn.jsdelivr.net/npm/codemirror@';

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
    }

    /**
     * Render field
     *
     * @since   1.4.7   2019-06-12 22:49:34     Release
     * @return  string
     */
    public function render() {

        echo $this->field_before();

        $args = pf_wp_parse_args( $this->field, array(
			'device'             => true,
            'tabs_options'       => [
                'typography' => true,
                'box'        => true,
                'custom'     => true
            ],
            'typography_options' => [
                'color'           => true,
                'font_size'       => true,
                'font_weight'     => true,
                'font_style'      => true,
                //'font_align'      => true,
                //'text_shadow'     => false,
                'text_transform'  => true,
                //'text_decoration' => false,
                'line_height'     => true,
                'letter_spacing'  => true,
                //'overflow'        => false,
                //'word_break'      => false,
            ],
            'box_options' => [
                'margin'        => true,
                'padding'       => true,
                'border_color'  => true,
                'border'        => true,
                'border_radius' => true,
                'background'    => true,
                //'box-shadow'    => true,
                //'opacity'       => true,
            ],
            'custom_options' => [
                'classname'  => true,
                'css' => true,
            ],
        ) );

        echo '<div class="pf--styling" >';

        $devices   = ['all' => '[all]'];
        $class_nav = $args['device'] == true ? '' : ' hidden';

        echo '<ul class="pf--styling__nav '.$class_nav.'">';
            echo '<li class="pf--styling__nav_desktop active" data-id="desktop"><i class="fa fa-desktop"></i></li>';
            echo '<li class="pf--styling__nav_tablet" data-id="tablet"><i class="fa fa-tablet"></i></li>';
            echo '<li class="pf--styling__nav_mobile" data-id="mobile"><i class="fa fa-mobile"></i></li>';
        echo '</ul>';

        echo '<div class="pf-clear"></div>';

        if( $args['device'] == true ){
            $devices = ['desktop'=>'[desktop]','tablet'=>'[tablet]','mobile'=>'[mobile]'];
        }

        foreach ($devices as $_key => $_value) {
            $default_values[$_key] = array(
                'color'          => '',
                'font_size'      => ['size' => '','unit' => 'px'],
                'line_height'    => ['size' => '','unit' => 'px'],
                'font_style'     => '',
                'font_weight'    => '',
                'text_transform' => '',
                'letter_spacing' => ['size' => '','unit' => 'px'],

                'background'    => '',
                'border_color'  => '',
                'border'        => ['top' => '','right' => '','bottom' => '','left' => '','unit' => 'px'],
                'border_radius' => ['top' => '','right' => '','bottom' => '','left' => '','unit' => 'px'],
                'padding'       => ['top' => '','right' => '','bottom' => '','left' => '','unit' => 'px'],
                'margin'        => ['top' => '','right' => '','bottom' => '','left' => '','unit' => 'px'],

                'classname'     => '',
                'css'           => '',
            );
        }
        $value             = wp_parse_args( $this->value, $default_values );
        $fields_typography = $args['typography_options'];
        $fields_box        = $args['box_options'];
        $fields_custom     = $args['custom_options'];

        $i = 1;
        foreach ($devices as $k => $v) {

        $class_hidden = $i == 1 ? '' : ' hidden';

        echo '<div class="pf--styling__wrap pf--styling__wrap_'.$k. $class_hidden . '" >';
        echo '<div class="pf--styling__content">';
        echo '<ul class="pf--styling__tab">';
            echo '<li class="pf--tabs__typography active" data-id="typography">'.__('Typography','pf').'</li>';
            echo '<li class="pf--tabs__box" data-id="box">'.__('Box','pf').'</li>';
            echo '<li class="pf--tabs__custom" data-id="custom">'.__('Custom','pf').'</li>';
        echo '</ul>';

        echo '</div>';

        echo '<div  class="pf--block pf--tabs__typography_content active">';

            if( $fields_typography['color'] ):
            echo '<div class="pf--fields">';
                echo '<div class="pf--title">'. esc_html__( 'Color', 'pf' ) .'</div>';
                echo '<div class="pf--field"><input type="text" name="'. $this->field_name("{$v}[color]") .'" value="'. $value[$k]['color'] .'" class="pf-color" /></div>';
            echo '</div>';
            endif;

            if( $fields_typography['font_size'] ):
            echo '<div class="pf--fields pf--fields_fontsize">';
                echo '<div class="pf--title">'. esc_html__( 'Font size', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    echo '<input type="text" name="'. $this->field_name("{$v}[font_size][size]") .'" value="'. $value[$k]['font_size']['size'] .'" class="pf-number" />';
                    echo '<select name="'. $this->field_name("{$v}[font_size][unit]") .'">';
                    foreach( array( 'px', 'em', 'rem' ) as $style ) {
                        $selected = ( $value[$k]['font_size']['unit'] === $style ) ? ' selected' : '';
                        echo '<option value="'. $style .'"'. $selected .'>'. ucfirst( $style ) .'</option>';
                    }
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            endif;

            if( $fields_typography['line_height'] ):
            echo '<div class="pf--fields pf--fields_fontsize">';
                echo '<div class="pf--title">'. esc_html__( 'Line height', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    echo '<input type="text" name="'. $this->field_name("{$v}[line_height][size]") .'" value="'. $value[$k]['line_height']['size'] .'" class="pf-number" />';
                    echo '<select name="'. $this->field_name("{$v}[line_height][unit]") .'">';
                    foreach( array( 'px', 'em', 'rem' ) as $style ) {
                        $selected = ( $value[$k]['line_height']['unit'] === $style ) ? ' selected' : '';
                        echo '<option value="'. $style .'"'. $selected .'>'. ucfirst( $style ) .'</option>';
                    }
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            endif;

            if( $fields_typography['font_style'] ):
            echo '<div class="pf--fields">';
                echo '<div class="pf--title">'. esc_html__( 'Font style', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    echo '<select name="'. $this->field_name("{$v}[font_style]") .'">';
                    foreach( array( '','normal', 'italic', 'oblique' ) as $style ) {
                        $selected = ( $value[$k]['font_style'] === $style ) ? ' selected' : '';
                        echo '<option value="'. $style .'"'. $selected .'>'. ucfirst( $style ) .'</option>';
                    }
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            endif;

            if( $fields_typography['font_weight'] ):
            echo '<div class="pf--fields">';
                echo '<div class="pf--title">'. esc_html__( 'Font weight', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    echo '<select name="'. $this->field_name("{$v}[font_weight]") .'">';
                    foreach( array('','300', '400', '500', '600', '700', '800', '900' ) as $style ) {
                        $selected = ( $value[$k]['font_weight'] === $style ) ? ' selected' : '';
                        echo '<option value="'. $style .'"'. $selected .'>'. ucfirst( $style ) .'</option>';
                    }
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            endif;

            if( $fields_typography['text_transform'] ):
            echo '<div class="pf--fields">';
                echo '<div class="pf--title">'. esc_html__( 'Text Transform', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    echo '<select name="'. $this->field_name("{$v}[text_transform]") .'">';
                    foreach( array( 'inherit','none','uppercase', 'capitalize', 'lowercase' ) as $style ) {
                        $selected = ( $value[$k]['text_transform'] === $style ) ? ' selected' : '';
                        echo '<option value="'. $style .'"'. $selected .'>'. ucfirst( $style ) .'</option>';
                    }
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            endif;

            if( $fields_typography['letter_spacing'] ):
            echo '<div class="pf--fields pf--fields_fontsize">';
                echo '<div class="pf--title">'. esc_html__( 'Letter Spacing', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    echo '<input type="text" name="'. $this->field_name("{$v}[letter_spacing][size]") .'" value="'. $value[$k]['letter_spacing']['size'] .'" class="pf-number" />';
                    echo '<select name="'. $this->field_name("{$v}[letter_spacing][unit]") .'">';
                    foreach( array( 'px', 'em', 'rem' ) as $style ) {
                        $selected = ( $value[$k]['letter_spacing']['unit'] === $style ) ? ' selected' : '';
                        echo '<option value="'. $style .'"'. $selected .'>'. ucfirst( $style ) .'</option>';
                    }
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            endif;


        echo '</div>'; // pf--block

        echo '<div class="pf--block pf--tabs__box_content hidden">';

            if( $fields_box['background'] ):
            echo '<div class="pf--fields">';
                echo '<div class="pf--title">'. esc_html__( 'Background', 'pf' ) .'</div>';
                echo '<div class="pf--field"><input type="text" name="'. $this->field_name("{$v}[background]") .'" value="'. $value[$k]['background'] .'" class="pf-color" /></div>';
            echo '</div>';
            endif;

            if( $fields_box['border_color'] ):
            echo '<div class="pf--fields">';
                echo '<div class="pf--title">'. esc_html__( 'Border color', 'pf' ) .'</div>';
                echo '<div class="pf--field"><input type="text" name="'. $this->field_name("{$v}[border_color]") .'" value="'. $value[$k]['border_color'] .'" class="pf-color" /></div>';
            echo '</div>';
            endif;

            if( $fields_box['border'] ):
            echo '<div class="pf--fields pf--fields_four">';
                echo '<div class="pf--title">'. esc_html__( 'Border', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    $icons = [ 'top' => 'fa fa-long-arrow-up', 'right' => 'fa fa-long-arrow-right', 'bottom' => 'fa fa-long-arrow-down', 'left' => 'fa fa-long-arrow-left',  ];
                    foreach ( array( 'top', 'right', 'bottom', 'left' ) as $style ) {
                        echo '<div class="pf--input">';
                        echo '<span class="pf--label pf--label-icon"><i class="'.$icons[$style].'"></i></span>';
                        echo '<input type="text" name="'. $this->field_name("{$v}[border][{$style}]") .'" value="'. $value[$k]['border'][$style] .'" placeholder="'. $style .'" class="pf-number" />';
                        echo '</div>';
                    }
                    echo '<select name="'. $this->field_name("{$v}[border][unit]") .'">';
                    foreach( array( 'px', 'em', 'rem' ) as $style ) {
                        $selected = ( $value[$k]['border']['unit']  === $style ) ? ' selected' : '';
                        echo '<option value="'. $style .'"'. $selected .'>'. ucfirst( $style ) .'</option>';
                    }
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            endif;

            if( $fields_box['border_radius'] ):
            echo '<div class="pf--fields pf--fields_four">';
                echo '<div class="pf--title">'. esc_html__( 'Border radius', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    $icons = [ 'top' => 'fa fa-long-arrow-up', 'right' => 'fa fa-long-arrow-right', 'bottom' => 'fa fa-long-arrow-down', 'left' => 'fa fa-long-arrow-left',  ];
                    foreach ( array( 'top', 'right', 'bottom', 'left' ) as $style ) {
                        echo '<div class="pf--input">';
                        echo '<span class="pf--label pf--label-icon"><i class="'.$icons[$style].'"></i></span>';
                        echo '<input type="text" name="'. $this->field_name("{$v}[border_radius][{$style}]") .'" value="'. $value[$k]['border_radius'][$style] .'" placeholder="'. $style .'" class="pf-number" />';
                        echo '</div>';
                    }
                    echo '<select name="'. $this->field_name("{$v}[border_radius][unit]") .'">';
                    foreach( array(  'px' ) as $style ) {
                        $selected = ( $value[$k]['border_radius']['unit'] === $style ) ? ' selected' : '';
                        echo '<option value="'. $style .'"'. $selected .'>'. ucfirst( $style ) .'</option>';
                    }
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            endif;

            if( $fields_box['padding'] ):
            echo '<div class="pf--fields pf--fields_four">';
                echo '<div class="pf--title">'. esc_html__( 'Padding', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    $icons = [ 'top' => 'fa fa-long-arrow-up', 'right' => 'fa fa-long-arrow-right', 'bottom' => 'fa fa-long-arrow-down', 'left' => 'fa fa-long-arrow-left',  ];
                    foreach ( array( 'top', 'right', 'bottom', 'left' ) as $style ) {
                        echo '<div class="pf--input">';
                        echo '<span class="pf--label pf--label-icon"><i class="'.$icons[$style].'"></i></span>';
                        echo '<input type="text" name="'. $this->field_name("{$v}[padding][{$style}]") .'" value="'. $value[$k]['padding'][$style] .'" placeholder="'. $style .'" class="pf-number" />';
                        echo '</div>';
                    }
                    echo '<select name="'. $this->field_name("{$v}[padding][unit]") .'">';
                    foreach( array(  'px' ) as $style ) {
                        $selected = ( $value[$k]['padding']['unit'] === $style ) ? ' selected' : '';
                        echo '<option value="'. $style .'"'. $selected .'>'. ucfirst( $style ) .'</option>';
                    }
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            endif;

            if( $fields_box['margin'] ):
            echo '<div class="pf--fields pf--fields_four">';
                echo '<div class="pf--title">'. esc_html__( 'Margin', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    $icons = [ 'top' => 'fa fa-long-arrow-up', 'right' => 'fa fa-long-arrow-right', 'bottom' => 'fa fa-long-arrow-down', 'left' => 'fa fa-long-arrow-left',  ];
                    foreach ( array( 'top', 'right', 'bottom', 'left' ) as $style ) {
                        echo '<div class="pf--input">';
                        echo '<span class="pf--label pf--label-icon"><i class="'.$icons[$style].'"></i></span>';
                        echo '<input type="text" name="'. $this->field_name("{$v}[margin][$style]") .'" value="'. $value[$k]['margin'][$style] .'" placeholder="'. $style .'" class="pf-number" />';
                        echo '</div>';
                    }
                    echo '<select name="'. $this->field_name("{$v}[margin][unit]") .'">';
                    foreach( array(  'px' ) as $style ) {
                        $selected = ( $value[$k]['margin']['unit'] === $style ) ? ' selected' : '';
                        echo '<option value="'. $style .'"'. $selected .'>'. ucfirst( $style ) .'</option>';
                    }
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            endif;

        echo '</div>';

        echo '<div class="pf--block pf--tabs__custom_content hidden">';

            if( $fields_custom['classname'] ):
            echo '<div class="pf--fields">';
                echo '<div class="pf--title">'. esc_html__( 'Classes Name', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    echo '<input type="text" name="'. $this->field_name("{$v}[classname]") .'" value="'. $value[$k]['classname'] .'" />';
                echo '</div>';
            echo '</div>';
            endif;

            if( $fields_custom['css'] ):
            $settings = array(
                'tabSize'     => 2,
                'lineNumbers' => true,
                'theme'       => 'default',
                'mode'        => 'css',
                'cdnURL'      => 'https://cdn.jsdelivr.net/npm/codemirror@' . '5.41.0',
                'lineNumbers' => true,
            );
            $encoded  = htmlspecialchars( json_encode( $settings ) );

            echo '<div class="pf--fields">';
                echo '<div class="pf--title">'. esc_html__( 'Custom css', 'pf' ) .'</div>';
                echo '<div class="pf--field">';
                    echo '<textarea name="'. $this->field_name("{$v}[css]") .'"  data-editor="'. $encoded .'">'. $value[$k]['css'] .'</textarea>';
                echo '</div>';
            echo '</div>';
            endif;

        echo '</div>';
        echo '</div>';
            $i++;
        }

        echo '</div>';


        echo $this->field_after();
    }

    public function enqueue() {

        if( ! wp_script_is( 'pf-codemirror' ) ) {
            wp_enqueue_script( 'pf-codemirror', $this->cdn_url . $this->version .'/lib/codemirror.min.js', array( 'pf' ), $this->version, true );
            wp_enqueue_script( 'pf-codemirror-loadmode', $this->cdn_url . $this->version .'/addon/mode/loadmode.min.js', array( 'pf-codemirror' ), $this->version, true );
        }

        if( ! wp_style_is( 'pf-codemirror' ) ) {
            wp_enqueue_style( 'pf-codemirror', $this->cdn_url . $this->version .'/lib/codemirror.min.css', array(), $this->version );
        }

    }
} }