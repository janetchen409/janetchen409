<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
if( ! class_exists( 'PF_classMetabox' ) ) {
/**
 * Metabox class
 *
 * @package PF
 * @subpackage PF/Classes
 *
 * @since   1.4     2019-04-16 19:41:43   	Release
 * @since 	1.4.2 	2019-04-30 02:48:30 	- Function was added for the credits of the page within a cpt
 * 											- Added function for options only when loading the current page
 * @since	1.5.3	2019-09-30				Added the ability to add classes for the metabox
 */
class PF_classMetabox{

	// ─── Constans class ────────
	public
	$unique       = '',
	$abstract     = 'metabox',
	$pre_fields   = array(),
	$sections     = array(),
	$post_type    = array(),
	$pre_sections = array(),

	// ─── Default args ────────
	$args       = array(
		'title'              => '',
		'post_type'          => 'post',
		'data_type'          => 'serialize',
		'context'            => 'advanced',
		'show_nav'			 => true,
		'priority'           => 'default',
		'exclude_post_types' => array(),
		'page_templates'     => '',
		'post_formats'       => '',
		'show_restore'       => false,
		'enqueue_webfont'    => true,
		'async_webfont'      => false,
		'output_css'         => true,
		'theme'              => 'dark',
		'defaults'           => array(),
		'class'              => '',
		'image_default'      => PIXEL_URL . 'assets/images/default.png',
		// footer ────────
		'footer_credit' 	 => '',
	);

	/**
	 * Run construct
	 *
	 * @since   1.4     2019-04-16 20:11:16     Release
	 * @since	1.4.2	2019-04-30 02:47:35		Added 'add_page_on_load' function for new features
	 *
	 * @param   string  $key ID                 unique setting/options
	 * @param   array   $params
	 */
	public function __construct( $key, $params = array() ) {
		$this->unique         = $key;
		$this->args           = apply_filters( "pf_metabox_{$this->unique}_args", wp_parse_args( $params['args'], $this->args ), $this );
		$this->sections       = apply_filters( "pf_metabox_{$this->unique}_sections", $params['sections'], $this );
		$this->post_type      = ( is_array( $this->args['post_type'] ) ) ? $this->args['post_type'] : array_filter( (array) $this->args['post_type'] );
		$this->post_formats   = ( is_array( $this->args['post_formats'] ) ) ? $this->args['post_formats'] : array_filter( (array) $this->args['post_formats'] );
		$this->page_templates = ( is_array( $this->args['page_templates'] ) ) ? $this->args['page_templates'] : array_filter( (array) $this->args['page_templates'] );

		$this->pre_tabs     = $this->pre_tabs( $this->sections );
		$this->pre_fields   = $this->pre_fields( $this->sections );
		$this->pre_sections = $this->pre_sections( $this->sections );

		add_action( "add_meta_boxes", array( &$this, 'add_meta_box' ) );
		add_action( 'add_meta_boxes', array( &$this, 'add_page_on_load' ) );
		add_action( 'save_post', array( &$this, 'save_meta_box' ), 10, 2 );
		add_action( 'edit_attachment', array( &$this, 'save_meta_box' ) );



		if( ! empty( $this->page_templates ) || ! empty( $this->post_formats ) ) {
			foreach( $this->post_type as $post_type ) {
				add_filter( 'postbox_classes_'. $post_type .'_'. $this->unique, array( &$this, 'add_metabox_classes' ) );
			}
		}

		// wp enqeueu for typography and output css
		// BUG: add the abstract class
		//parent::__construct();

	}

	/**
	 * Create an Instance of Metabox
     * You can create multiple instances with different $keys.
	*
	* @since   1.4     2019-04-16 20:22:11     Release
	*
	* @param   string  $key                    ID unique setting/options
	* @param   array   $params
	* @return  object
	*/
	public static function instance( $key, $params = array() ) {
		return new self( $key, $params );
	}

	/**
	 * Collect and Order all sections
	 *
	 * @since   1.4     2019-04-16 20:23:02   Release
	 *
	 * @param   array   $sections
	 * @return  void
	 */
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

	/**
	 * Collect and Order all sections
	 *
	 * @since 	1.4 	2019-04-18 22:25:35 	Release
	 *
	 * @param 	array 	$sections
	 * @return 	void
	 */
	public function pre_tabs( $sections ) {

		$result    = array();
		$parents   = array();
		$count     = 100;
		$increment = 10;
		$tabs      = [];

		if( ! empty( $sections ) ){
			foreach ($sections as $key => $value) {
				$value['priority']  = ( isset( $value['priority'] ) ) ? $value['priority'] : $count;
				$count              = $count + $increment;
				if( ! empty( $value['sub'] ) ) {
					foreach ($value['sub'] as $k => $v) {
						$v['priority']  = ( isset( $v['priority'] ) ) ? $v['priority'] : $count;
						$parents[]      = $v;
						$count++;
					}
					$value['sub'] = wp_list_sort( $parents, array( 'priority' => 'ASC' ), 'ASC', true );
					$parents = array();
				}
				$result[] = $value;
			}
		}

		return wp_list_sort( $result, array( 'priority' => 'ASC' ), 'ASC', true );
	}

	/**
	 * Collect the sections
	 *
	 * @since 	1.0 	2019-04-18 22:42:26 	Release
	 *
	 * @param 	array 	$sections
	 * @return 	void
	 */
	public function pre_sections( $sections ) {

		$result = array();

		foreach( $this->pre_tabs as $tab ) {
			if( ! empty( $tab['sub'] ) ) {
				foreach( $tab['sub'] as $sub ) {
					$result[] = $sub;
				}
			}
			if( empty( $tab['sub'] ) ){
				$result[] = $tab;
			}
		}
		return $result;
	}

	/**
	 * Valida errores grupaes o individuales
	 *
	 * @since   1.4 	2019-04-18 22:35:45 	Update functions
	 *
	 * @param   mixed 	$args 					Values ​​of $Fields
	 * @param   mixed 	$err 					Response response
	 */
	public function error_check( &$args, $err = '' ) {
		if( ! $this->args['ajax_save'] ) {
			$args     = (array) $args;
			if( ! empty( $args['fields'] ) ) {
				foreach( $args['fields'] as $field ) {
					if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
						$err = $this->error_check( $field, $err );
					}elseif( ! empty( $field['id'] ) ) {
						if( array_key_exists( $field['id'], $this->errors ) ) {
							$err = '<span class="pf-label-error">!</span>';
						}
					}
				}
			}
			if( ! empty( $args['sub'] ) ) {
				foreach( $args['sub'] as $sub ) {
					$err = $this->error_check( $sub, $err );
				}
			}

			if( ! empty( $args['id'] ) && array_key_exists( $args['id'], $this->errors ) ) {
				$err = $this->errors[$args['id']];
			}

		}
		return $err;
	}

	/**
	 * Add classes to metaboxes
	 *
	 * @since   1.4     2019-04-20	Release
	 * @since	1.5.3	2019-09-30	Added the ability to add classes for the metabox
	 *
	 * @param   string   $sections
	 * @return  void
	 */
	public function add_metabox_classes( $sections ) {

		global $post;

		if( ! empty( $this->post_formats ) ) {

			$saved_post_format = ( is_object( $post ) ) ? get_post_format( $post ) : false;
			$saved_post_format = ( ! empty( $saved_post_format ) ) ? $saved_post_format : 'default';

			$classes[] = 'pf-post-formats';

			// Sanitize post format for standard to default
			if( ( $key = array_search( 'standard', $this->post_formats ) ) !== false ) {
				$this->post_formats[$key] = 'default';
			}

			foreach( $this->post_formats as $format ) {
				$classes[] = 'pf-post-format-'. $format;
			}

			if( ! in_array( $saved_post_format, $this->post_formats ) ) {
				$classes[] = 'pf-hide';
			} else {
				$classes[] = 'pf-show';
			}

		}

		if( ! empty( $this->page_templates ) ) {

			$saved_template = ( is_object( $post ) && ! empty( $post->page_template ) ) ? $post->page_template : 'default';

			$classes[] = 'pf-page-templates';

			foreach( $this->page_templates as $template ) {
				$classes[] = 'pf-page-'. preg_replace( '/[^a-zA-Z0-9]+/', '-', strtolower( $template ) );
			}

			if( ! in_array( $saved_template, $this->page_templates ) ) {
				$classes[] = 'pf-hide';
			} else {
				$classes[] = 'pf-show';
			}

		}

		if( ! empty( $this->args['class'] ) ) {
			$classes[] = $this->args['class'];
		}

		return $classes;

	}

	/**
	 * Add metaboxes, also exclude those that are not necessary
	 *
	 * @since   1.4     2019-04-20 15:36:23   Release
	 *
	 * @param   string   $post_type
	 * @return  void
	 */
	public function add_meta_box( $post_type ) {

		if( ! in_array( $this->post_type, $this->args['exclude_post_types'] ) ) {

			add_meta_box( $this->unique, $this->args['title'], array( $this, 'add_meta_box_content' ), $this->post_type, $this->args['context'], $this->args['priority'], $this->args );

		}

	}

	/**
	 * Get default value
	 *
	 * @since   1.4     		2019-04-20 15:37:05   Release
	 *
	 * @param   array|object   $field
	 * @return  mixed
	 */
	public function get_default( $field ) {

		$default = ( isset( $this->args['defaults'][$field['id']] ) ) ? $this->args['defaults'][$field['id']] : '';
		$default = ( isset( $field['default'] ) ) ? $field['default'] : $default;

		return $default;

	}

	/**
	 * Get meta value
	 *
	 * @since   1.4		2019-04-20	Release
	 * @since	1.5.3	2019-09-30	The $ value variable is set with NULL for better validation
	 *
	 * @param   array|object   $field
	 * @return  mixed
	 */
	public function get_meta_value( $field ) {

		global $post;

		$value = null;

		if( is_object( $post ) && ! empty( $field['id'] ) ) {

			if( $this->args['data_type'] !== 'serialize' ) {
				$meta  = get_post_meta( $post->ID, $field['id'] );
				$value = ( isset( $meta[0] ) ) ? $meta[0] : null;
			} else {
				$meta  = get_post_meta( $post->ID, $this->unique, true );
				$value = ( isset( $meta[$field['id']] ) ) ? $meta[$field['id']] : null;
			}

			$default = $this->get_default( $field );
			$value   = ( isset( $value ) ) ? $value : $default;

		}

		return $value;

	}

	/**
	 * Get meta value
	 *
	 * @since   1.4     		2019-04-20 15:38:31		Release
	 * @since	1.4.4			2019-05-21 02:16:09		Class is added to show the active table
	 * @since	1.4.6			2019-06-12 18:39:53		- The metabox theme name class is added
	 * 													- The metabox now has validation of nonce and class with unique name
	 * @since	1.6				2019-12-02				The 'pf-metabox-wrapper' class was replaced by 'pf-restore-wrapper'
	 *
	 * @param   array|object   $field
	 * @return  mixed
	 */
	public function add_meta_box_content( $post, $callback ) {

		global $post;
		$has_nav  = ( count( $this->sections ) > 1 && $this->args['context'] !== 'side' && $this->args['show_nav'] == true ) ? true : false;
		$show_all = ( ! $has_nav ) ? ' pf-show-all' : '';
		$errors   = ( is_object ( $post ) ) ? get_post_meta( $post->ID, '_pf_errors', true ) : array();
		$errors   = ( ! empty( $errors ) ) ? $errors : array();
		$theme    = ( $this->args['theme'] ) ? ' pf-theme-'. $this->args['theme'] : '';
		$class    = ( $this->args['class'] ) ? ' '. $this->args['class'] : '';

		if( is_object ( $post ) && ! empty( $errors ) ) {
			delete_post_meta( $post->ID, '_pf_errors' );
		}

		wp_nonce_field( 'pf_metabox_nonce', 'pf_metabox_nonce'. $this->unique );

		echo '<div class="pf pf-metabox pf-metabox-'. $this->unique . $theme . $class.'">';

			echo '<div class="pf-wrapper'. $show_all .'">';

			$tab_last_selected = ! empty( $_COOKIE['pf-last-metabox-tab-'. $post->ID .'-'. $this->unique] ) ? $_COOKIE['pf-last-metabox-tab-'. $post->ID .'-'. $this->unique] : 1;
			if( $has_nav ) {

				echo '<div class="pf-nav pf-nav-metabox" data-unique="'. $this->unique .'">';

				echo '<ul class="nav-tab-wrapper">';

				$tab_key = 1;

				foreach( $this->pre_tabs as $tab ) {

					$link_active = ( $tab_key == $tab_last_selected ) ? 'pf-section-active' : '';
					$tab_error   = ( ! empty( $errors['sections'][$tab_key] ) ) ? '<i class="pf-label-error pf-error">!</i>' : '';
					$tab_icon    = ( ! empty( $tab['icon'] ) ) ? '<i class="pf-icon '. $tab['icon'] .'"></i>' : '';

					echo '<li><a class="'. $link_active .'" href="#" data-section="'. $this->unique .'_'. $tab_key .'">'. $tab_icon . $tab['title'] . $tab_error .'</a></li>';
					//echo '<li class="pf-tab-depth-0"><a class="nav-tab" data-tab-id="'.$tab_key.'" id="pf-tab-link-'. $tab_key .'" href="#tab='. $tab_key .'">'. $tab_icon . $tab['title'] . $tab_error .'</a></li>';

					$tab_key++;
				}
				echo '</ul>';

				echo '</div>';

			}

			echo '<div class="pf-content">';

				echo '<div class="pf-sections">';

				$section_key = 1;

				foreach( $this->sections as $section ) {

					$onload         = ( ! $has_nav ) ? ' pf-onload' : '';
					$section_active = ( $section_key == $tab_last_selected ) ? 'style="display:block;"' : 'style="display:none;"';

					echo '<div id="pf-section-'. $this->unique .'_'. $section_key .'" class="pf-section'. $onload .'" '. $section_active .'>';

					$section_icon  = ( ! empty( $section['icon'] ) ) ? '<i class="pf-icon '. $section['icon'] .'"></i>' : '';
					$section_title = ( ! empty( $section['title'] ) ) ? $section['title'] : '';

					echo ( $section_title || $section_icon ) ? '<div class="pf-section-title"><h3>'. $section_icon . $section_title .'</h3></div>' : '';

					if( ! empty( $section['fields'] ) ) {

						foreach ( $section['fields'] as $field ) {

						if( ! empty( $field['id'] ) && ! empty( $errors['fields'][$field['id']] ) ) {
							$field['_error'] = $errors['fields'][$field['id']];
						}

						PF::field( $field, $this->get_meta_value( $field ), $this->unique, 'metabox' );

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

					echo '<div class=" pf-restore-wrapper">';
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

	/**
	 * Save metabox
	 *
	 * @since	1.4.6	2019-06-12	Security improvements in metabox saving
	 * @since	1.4.7	2019-07-11	Added improvements for default data
	 * @since	1.4.9	2019-07-12	Now also save the post_id of the current post
	 * @since	1.5.5	2019-10-28	Validation was added so you can execute the BACKUP fields correctly
	 *
	 * @param	int		$post_id				ID of the current post
	 * @return	void
	 */
	public function save_meta_box( $post_id ) {

		if( ! wp_verify_nonce( pf_get_var( 'pf_metabox_nonce'. $this->unique ), 'pf_metabox_nonce' ) ) {
			return $post_id;
		}

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		$errors  = array();
		$request = pf_get_var( $this->unique );
		$defaults= [];

		if( ! empty( $request ) ) {

			// ignore _nonce
			if( isset( $request['_nonce'] ) ) {
				unset( $request['_nonce'] );
			}


			// ─── import data ────────
			$transient = pf_get_var('pf_transient');
            if( empty( $transient['pf_import_data'] ) ) {
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
										$request[$field['id']] = $this->get_meta_value( $field );

									}

								}

								// auto sanitize
								if( ! isset( $request[$field['id']] ) || is_null( $request[$field['id']] ) ) {
									$request[$field['id']] = '';
								}
								// ─── sanitize & validate ────────
								//$request[ $field['id'] ] = pf_sanitize_fields_recursive( $field, $request[$field['id']], $this );

								// Accumulate data by default
								$defaults[$field['id']] = $this->get_default( $field );
							}

						}

					}

					$section_key++;
				}

			}else{

				$import_data = json_decode( stripslashes( trim( $transient['pf_import_data'] ) ), true );
                $request     = ( is_array( $import_data ) ) ? $import_data : array();

			}

			$request = apply_filters( "pf_{$this->unique}_save", $request, $post_id, $this );

			do_action( "pf_{$this->unique}_save_before", $request, $post_id, $this );

			if( empty( $request ) || ! empty( $request['_restore'] ) ) {

				if( $this->args['data_type'] !== 'serialize' ) {
					foreach ( $request as $key => $value ) {
						delete_post_meta( $post_id, $key );
					}
				} else {
					$defaults['post_id'] = $post_id;
					delete_post_meta( $post_id, $this->unique );
					update_post_meta( $post_id, $this->unique, $defaults );
				}

			} else {

				if( $this->args['data_type'] !== 'serialize' ) {
					foreach ( $request as $key => $value ) {
						update_post_meta( $post_id, $key, $value );
					}
				} else {
					$request['post_id'] = $post_id;
					update_post_meta( $post_id, $this->unique, $request );
				}

				if( ! empty( $errors ) ) {
					update_post_meta( $post_id, '_pf_errors', $errors );
				}

			}

			do_action( "pf_{$this->unique}_saved", $request, $post_id, $this );

			do_action( "pf_{$this->unique}_save_after", $request, $post_id, $this );

		}
	}

	/**
	 * Load processes, hook, filter right on the options page
	 *
	 * @since 	1.4.2 	2019-03-07	Release
	 * @since	1.6		2019-12-02	Valid if the post_type is full to add the filter
	 *
	 * @return void
	 */
	public function add_page_on_load(){

		global $post;

		if( empty( $post->post_type ) ) return;

		if( isset($this->args['post_type'])
			&& ( is_array($this->args['post_type'])
			&& in_array( $post->post_type , $this->args['post_type'] ) ) ){
			add_filter( 'admin_footer_text', array( &$this, 'add_admin_footer_text' ) );
		}elseif(  ! empty($this->args['post_type']) && $post->post_type == $this->args['post_type'] ){
			add_filter( 'admin_footer_text', array( &$this, 'add_admin_footer_text' ) );
		}

	}

	/**
	 * Text from the bottom left of the panel
	 *
	 * @since 	1.4.2	2019-04-30 02:32:50    Release
	 * @return 	void
	 */
	public function add_admin_footer_text() {
		echo ( ! empty( $this->args['footer_credit'] ) ) ? $this->args['footer_credit'] : '' ;
	}
} }