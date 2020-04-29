<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
if( ! class_exists( 'PF_Comment_Metabox' ) ) {
/**
 * Comment Metabox class
 *
 * @package PF
 * @subpackage PF/Classes
 *
 * @since	1.6	    2019-12-02  Added the ability to add classes for the metabox in comment
 */
class PF_classCommentMetabox extends PF_classAbstract{

    // constans
    public $unique     = '';
    public $abstract   = 'comment_metabox';
    public $pre_fields = array();
    public $sections   = array();
    public $args       = array(
        'title'        => '',
        'data_type'    => 'serialize',
        'priority'     => 'default',
        'show_restore' => false,
        'theme'        => 'dark',
        'class'        => '',
        'defaults'     => array(),
    );

    // run comment metabox construct
    public function __construct( $key, $params = array() ) {

        $this->unique     = $key;
        $this->args       = apply_filters( "pf_{$this->unique}_args", wp_parse_args( $params['args'], $this->args ), $this );
        $this->sections   = apply_filters( "pf_{$this->unique}_sections", $params['sections'], $this );
        $this->pre_fields = $this->pre_fields( $this->sections );

        add_action( 'add_meta_boxes_comment', array( &$this, 'add_comment_meta_box' ) );
        add_action( 'edit_comment', array( &$this, 'save_comment_meta_box' ) );

        if( ! empty( $this->args['class'] ) ) {
        add_filter( 'postbox_classes_comment_'. $this->unique, array( &$this, 'add_comment_metabox_classes' ) );
        }

        // wp enqeueu for typography and output css
        parent::__construct();

    }

    // instance
    public static function instance( $key, $params = array() ) {
        return new self( $key, $params );
    }

    public function pre_fields( $sections ) {

        if( empty( $sections ) ) return ;

		$result  = array();

		if( empty( $sections ) ) return ;

		foreach( $sections as $key => $section ) {

			if( ! empty( $section['fields'] ) ) {

				foreach( $section['fields'] as $key => $field ) {

					$result[] = $field;

				}

			}elseif( ! empty( $section['sub'] ) ){

				foreach( $section['sub'] as $k => $v ){

					if( ! empty( $v['fields'] ) ){

						foreach ( $v['fields'] as $_k => $_v ) {

							$result[] = $_v;

						}

					}

				}

			}
		}

		return $result;
    }

    public function add_comment_metabox_classes( $classes ) {

        if( ! empty( $this->args['class'] ) ) {
            $classes[] = $this->args['class'];
        }

        return $classes;

    }

    // add comment metabox
    public function add_comment_meta_box( $post_type ) {

        add_meta_box( $this->unique, $this->args['title'], array( &$this, 'add_comment_meta_box_content' ), 'comment', 'normal', $this->args['priority'], $this->args );

    }

    // get default value
    public function get_default( $field ) {

        $default = ( isset( $field['id'] ) && isset( $this->args['defaults'][$field['id']] ) ) ? $this->args['defaults'][$field['id']] : null;
        $default = ( isset( $field['default'] ) ) ? $field['default'] : $default;

        return $default;

    }

    // get meta value
    public function get_meta_value( $comment_id, $field ) {

        $value = null;

        if( ! empty( $comment_id ) && ! empty( $field['id'] ) ) {

        if( $this->args['data_type'] !== 'serialize' ) {
            $meta  = get_comment_meta( $comment_id, $field['id'] );
            $value = ( isset( $meta[0] ) ) ? $meta[0] : null;
        } else {
            $meta  = get_comment_meta( $comment_id, $this->unique, true );
            $value = ( isset( $meta[$field['id']] ) ) ? $meta[$field['id']] : null;
        }

        }

        $default = $this->get_default( $field );
        $value   = ( isset( $value ) ) ? $value : $default;

        return $value;

    }

    // add comment metabox content
    public function add_comment_meta_box_content( $comment, $callback ) {

        $has_nav  = ( count( $this->sections ) > 1 ) ? true : false;
        $show_all = ( ! $has_nav ) ? ' pf-show-all' : '';
        $errors   = ( is_object ( $comment ) ) ? get_comment_meta( $comment->comment_ID, '_pf_errors', true ) : array();
        $errors   = ( ! empty( $errors ) ) ? $errors : array();
        $theme    = ( $this->args['theme'] ) ? ' pf-theme-'. $this->args['theme'] : '';

        if( is_object ( $comment ) && ! empty( $errors ) ) {
            delete_comment_meta( $comment->comment_ID, '_pf_errors' );
        }

        wp_nonce_field( 'pf_comment_metabox_nonce', 'pf_comment_metabox_nonce'. $this->unique );

        echo '<div class="pf pf-comment-metabox'. $theme .'">';

        echo '<div class="pf-wrapper'. $show_all .'">';

            if( $has_nav ) {

            echo '<div class="pf-nav pf-nav-metabox" data-unique="'. $this->unique .'">';

                echo '<ul>';
                $tab_key = 1;
                foreach( $this->sections as $section ) {

                    $tab_error = ( ! empty( $errors['sections'][$tab_key] ) ) ? '<i class="pf-label-error pf-error">!</i>' : '';
                    $tab_icon = ( ! empty( $section['icon'] ) ) ? '<i class="pf-icon '. $section['icon'] .'"></i>' : '';

                    echo '<li><a href="#" data-section="'. $this->unique .'_'. $tab_key .'">'. $tab_icon . $section['title'] . $tab_error .'</a></li>';

                    $tab_key++;
                }
                echo '</ul>';

            echo '</div>';

            }

            echo '<div class="pf-content">';

            echo '<div class="pf-sections">';

            $section_key = 1;

            foreach( $this->sections as $section ) {

                $onload = ( ! $has_nav ) ? ' pf-onload' : '';

                echo '<div id="pf-section-'. $this->unique .'_'. $section_key .'" class="pf-section'. $onload .'">';

                $section_icon  = ( ! empty( $section['icon'] ) ) ? '<i class="pf-icon '. $section['icon'] .'"></i>' : '';
                $section_title = ( ! empty( $section['title'] ) ) ? $section['title'] : '';

                echo ( $section_title || $section_icon ) ? '<div class="pf-section-title"><h3>'. $section_icon . $section_title .'</h3></div>' : '';

                if( ! empty( $section['fields'] ) ) {

                foreach ( $section['fields'] as $field ) {

                    if( ! empty( $field['id'] ) && ! empty( $errors['fields'][$field['id']] ) ) {
                        $field['_error'] = $errors['fields'][$field['id']];
                    }

                    PF::field( $field, $this->get_meta_value( $comment->comment_ID, $field ), $this->unique, 'comment_metabox' );

                }

                } else {

                    echo '<div class="pf-no-option pf-text-muted">'. esc_html__( 'No option provided by developer.', 'pf' ) .'</div>';

                }

                echo '</div>';

                $section_key++;
            }

            echo '</div>';

            echo '<div class="clear"></div>';

            if( ! empty( $this->args['show_restore'] ) ) {

                echo '<div class="pf-restore-wrapper">';
                echo '<label>';
                echo '<input type="checkbox" name="'. $this->unique .'[_restore]" />';
                echo '<span class="button pf-button-restore">'. esc_html__( 'Restore', 'pf' ) .'</span>';
                echo '<span class="button pf-button-cancel">'. sprintf( '<small>( %s )</small> %s', esc_html__( 'update post for restore ', 'pf' ), esc_html__( 'Cancel', 'pf' ) ) .'</span>';
                echo '</label>';
                echo '</div>';

            }

            echo '</div>';

            echo ( $has_nav ) ? '<div class="pf-nav-background"></div>' : '';

            echo '<div class="clear"></div>';

        echo '</div>';

        echo '</div>';

    }

    // save comment metabox
    public function save_comment_meta_box( $comment_id ) {

        if( ! wp_verify_nonce( pf_get_var( 'pf_comment_metabox_nonce'. $this->unique ), 'pf_comment_metabox_nonce' ) ) {
            return $comment_id;
        }

        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $comment_id;
        }

        $errors   = array();
        $request  = pf_get_var( $this->unique );
        $defaults = [];

        if( ! empty( $request ) ) {

        // ignore _nonce
        if( isset( $request['_nonce'] ) ) {
            unset( $request['_nonce'] );
        }

        // sanitize and validate
        $section_key = 1;
        foreach( $this->sections as $section ) {

            if( ! empty( $section['fields'] ) ) {

            foreach( $section['fields'] as $field ) {

                if( ! empty( $field['id'] ) ) {

                    // sanitize
                    if( ! empty( $field['sanitize'] ) ) {

                        $sanitize              = $field['sanitize'];
                        $value_sanitize        = isset( $request[$field['id']] ) ? $request[$field['id']] : '';
                        $request[$field['id']] = call_user_func( $sanitize, $value_sanitize );

                    }

                    // validate
                    if( ! empty( $field['validate'] ) ) {

                            $validate       = $field['validate'];
                            $value_validate = isset( $request[$field['id']] ) ? $request[$field['id']] : '';
                            $has_validated  = call_user_func( $validate, $value_validate );

                        if( ! empty( $has_validated ) ) {

                            $errors['sections'][$section_key] = true;
                            $errors['fields'][$field['id']] = $has_validated;
                            $request[$field['id']] = $this->get_meta_value( $comment_id, $field );

                        }

                    }

                    // auto sanitize
                    if( ! isset( $request[$field['id']] ) || is_null( $request[$field['id']] ) ) {
                        $request[$field['id']] = '';
                    }

                }

            }

            }

            $section_key++;
        }

        }

        $request = apply_filters( "pf_{$this->unique}_save", $request, $comment_id, $this );

        do_action( "pf_{$this->unique}_save_before", $request, $comment_id, $this );

        if( empty( $request ) || ! empty( $request['_restore'] ) ) {

            if( $this->args['data_type'] !== 'serialize' ) {
                foreach ( $request as $key => $value ) {
                    delete_comment_meta( $comment_id, $key );
                }
            } else {
                $defaults['comment_id'] = $comment_id;
                delete_comment_meta( $comment_id, $this->unique );
                update_comment_meta( $comment_id, $this->unique, $defaults );
            }

        } else {

            if( $this->args['data_type'] !== 'serialize' ) {
                foreach ( $request as $key => $value ) {
                    update_comment_meta( $comment_id, $key, $value );
                }
            } else {
                update_comment_meta( $comment_id, $this->unique, $request );
            }

            if( ! empty( $errors ) ) {
                update_comment_meta( $comment_id, '_pf_errors', $errors );
            }

        }

        do_action( "pf_{$this->unique}_saved", $request, $comment_id, $this );

        do_action( "pf_{$this->unique}_save_after", $request, $comment_id, $this );

    }
} }