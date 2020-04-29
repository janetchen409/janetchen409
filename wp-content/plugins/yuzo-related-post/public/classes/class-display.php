<?php
namespace YUZO\Publi;
use YUZO\Core\YUZO_Core as yuzo;

if( ! class_exists( 'Yuzo_Display' ) ){
/**
 * Display functionality of the plugin.
 *
 * Functions that let you know if a related
 * post is displayed or not in some position.
 *
 * @since 		6.0         2019-04-25     Release
 *
 * @package    	Yuzo
 * @subpackage 	Yuzo/Public
 */
class Yuzo_Display {

	/**
	 * Variable that contains the options from list related
	 *
	 * @since 	6.0     2019-04-25 20:30:24     Release
	 * @access 	public
	 * @var 	Yuzo    $options    Get options from the database
	 */
	public $options = [],
	/**
	 * Variable that contains the settings general
	 *
	 * @since 	6.0     2019-04-25 20:30:37     Release
	 * @access 	public
	 * @var 	Yuzo    $options    Get setting
	 */
	$settings = [],
	/**
	 * List of active Yuzo options (this serves multiple purposes)
	 * @since	6.0		2019-06-11 20:32:31		Release
	 * @access 	public
	 * @var 	array	$list_design			List of options
	 */
	$list_yuzo_active = [],
	/**
	 * ID of the current Yuzo that is being validated or processed
	 *
	 * @since	6.0.9.7		2019-07-31 17:40:53		Release
	 * @access	public
	 * @var		int|null
	 */
	$current_yuzo_id = null,
	/**
	 * Save what kind of Yuzo are going to be displayed or are running
	 *
	 * @since	6.0.9.8		2019-08-22		Release
	 * @access	public
	 * @var		array
	 */
	$types_yuzo = [];

	/**
	 * Init class
	 *
	 * @since   6.0     2019-04-25 20:31:32     Release
	 */
	public function __construct(){

		// This only works for front-end.
		if( is_admin() ) return;

		// Get the variables of the current Setting (general)
		$this->settings = yuzo::instance()->settings;

	}

	/**
	 * Main function that is valid if it shows or not the related post
	 *
	 * @since   6.0     	2019-04-26	Release
	 * @since	6.0.9.83	2019-10-04	Robust validations in metabox and robust content
	 * @return  boolean
	 */
	public function show_related_post(){

		yuzo::instance()->logs->group( "Public ⇨ Display ⇨ Show related posts" );

		$list_settings_related = yuzo::instance()
									->public
									->related_options
									->get_list_settings_related_post();

		$content_related_options = null;
		$inline_related_options  = null;

		if( is_array($list_settings_related) && ! empty( $list_settings_related ) ){

			yuzo::instance()->logs->info( "var #list_settings_related is a array" );

			foreach ($list_settings_related as $key => $value) {

				$list_var_temp = unserialize($value['setting']);
				$list_var_temp['post_id'] = $value['post_id'];

				// ─── Valid if the yuzo is active by metabox ────────
				if( ! empty( (int)$list_var_temp['post_id'] )
					&& yuzo::instance()->public->related_algorithm->disabled_current_yuzo( $list_var_temp['post_id'] ) ){ continue; };

				$list_var_temp = yuzo_fix_var_design( $list_var_temp, true );

				// Add to the global list of active options
				$this->list_yuzo_active[] = $list_var_temp;

				// Validate if related is type 'content'
				if( ! empty ( $list_var_temp['fieldset_design']['panels-design']['where_show'] ) && $list_var_temp['fieldset_design']['panels-design']['where_show'] == 'content' ){

					// Only in 'below' goes to the end of the content
					if( $list_var_temp['fieldset_design']['panels-design']['content_location'] == 'below-post-content' ){
						$content_related_options[] = $list_var_temp;
					}else{
						// the rest goes between the paragraphs
						$inline_related_options [] = $list_var_temp;
					}

				}else{

					continue;

				}

			}

			// Fetch for content
			if( is_array($content_related_options) ){

				usort($content_related_options, function ($item1, $item2) {
					$location_priority1 = isset( $item1['content_appende_and_order']['location_priority'] ) ? $item1['content_appende_and_order']['location_priority'] : 10;
					$location_priority2 = isset( $item2['content_appende_and_order']['location_priority'] ) ? $item2['content_appende_and_order']['location_priority'] : 10;
					if ( (int)$location_priority1 == (int)$location_priority2 ) return 0;
					return (int)$location_priority1 < (int)$location_priority2 ? -1 : 1;
				});

				if( is_array( $content_related_options ) ){

					foreach ($content_related_options as $key => $value){

						$opt_temp = $this->options = yuzo_fix_var_design((object)$value);

						if( $this->display_related_post() ){

							// It is indicated that at least you have a yuzo content
							$this->types_yuzo[] = 'content';

							add_action( 'the_content', function ( $content = '' ) use ( $opt_temp ) {

								$template = yuzo::instance()
												->public
												->related_algorithm
												->get_result_yuzo_post( $opt_temp );

								// Append html related to content
								return $content.$template;

							}, $opt_temp->fieldset_design['panels-design']['content_appende_and_order'][ 'location_priority' ], 1 );

						}
					}

				}
			}

			// Fetch for inline
			if( is_array($inline_related_options) ){

				if( is_array( $inline_related_options ) ){

					$k = 19;
					foreach ($inline_related_options as $key => $value){

						// Update options for each fetch
						$opt_temp = $this->options = (object)$value;

						// Validate if display related
						if( $this->display_related_post() ){

							// It is indicated that at least you have a yuzo inline
							$this->types_yuzo[] = 'inline';

							// add hook dynamic
							add_action('the_content', function ($content = '') use ( $opt_temp ) {

								// ─── cross the temporal value to make it public ────────
								$this->options = $opt_temp;

								// Calculate the position of this yuzo based on the content paragraphs
								$position = $this->calculate_position( $content );
								return $this->prefix_insert_after_paragraph( yuzo::instance()->public->related_algorithm->get_result_yuzo_post( $opt_temp ) , $position, $content );
							}, $k++, 1);

						}
					}
				}
			}

		}

		yuzo::instance()->logs->groupEnd();
	}

	/**
	 * Main function that is valid if it shows or not the related post
	 *
	 * @since   6.0     2019-04-26 00:18:33     Release
	 * @static
	 * @param   object  $other_list_options     List object options
	 * @return  boolean
	 */
	public function display_related_post( $other_list_options = null ){

		//If there is another option then it becomes the current one. (example: shortcode)
		$this->options = $other_list_options ?: $this->options;
		yuzo::instance()->logs->group( "Validate display yuzo id " . ( $this->options->post_id ?: 0 ) );

		// Valid that it does not show in specific post
		if( $this->display_not_post_id() ){
			yuzo::instance()->logs->error( "▼ Valid that it does not show in specific post ");
			yuzo::instance()->logs->groupEnd();
			return;
		}
		yuzo::instance()->logs->debug( "√ Valid that it does not show in specific post");

		// Valid if displayed by post id specific
		if( $this->display_only_specific_post() ){
			yuzo::instance()->logs->error( "▼ Valid if displayed by post id specific ");
			yuzo::instance()->logs->groupEnd();
			return;
		}
		yuzo::instance()->logs->debug( "√ Valid if displayed by post id specific");

		// Valid if displayed by Custom Type Post
		if( ! $this->display_in_cpt() ){
			yuzo::instance()->logs->error( "▼ Valid if displayed by Custom Type Post ");
			yuzo::instance()->logs->groupEnd();
			return;
		}
		yuzo::instance()->logs->debug( "√ Valid if displayed by Custom Type Post");

		// Valid if shown by inherited taxonomies (example: category)
		if( ! $this->display_in_taxonomy_hierarchical() ){
			yuzo::instance()->logs->error( "Valid if shown by inherited taxonomies (example: category)" );
			yuzo::instance()->logs->groupEnd();
			return;
		}
		yuzo::instance()->logs->debug( "√ Valid if shown by inherited taxonomies (example: category)");

		// It shows in different parts of the page, according to the Theme
		if( ! is_singular() && ! $this->display_template_page() ){
			yuzo::instance()->logs->error( "▼ It shows in different parts of the page, according to the Theme");
			yuzo::instance()->logs->groupEnd();
			return;
		};
		yuzo::instance()->logs->debug( "√  It shows in different parts of the page, according to the Theme");

		yuzo::instance()->logs->success( "Display OK");
		yuzo::instance()->logs->groupEnd();

		return true;

	}

	/**
	 * Valid if displayed by Custom Type Post
	 *
	 * @since   6.0     2019-04-26 00:21:25     Release
	 * @access  private
	 * @return  boolean
	 */
	private function display_in_cpt(){
		global $post;
		// Current CPT (v.s) Select CPT
		return in_array( get_post_type($post->ID) , (array) $this->options->show_only_in_type_post );
	}

	/**
	 * It does not show the related post in the IDs that the user enters
	 *
	 * @since	6.0		2019-04-26	Release
	 * @since	6.1.52	2020-02-09	Valid if that option element exists
	 * @return	bool
	 */
	private function display_not_post_id(){
		global $post;
		$not_in =  ! empty( $this->options->not_appear_inside ) ? $this->options->not_appear_inside : '';
		return in_array( $post->ID , explode(",",(string)$not_in ) );
	}

	/**
	 * Valid if shown by inherited taxonomies (example: category)
	 *
	 * In general, most people use general categories within the posts,
	 * these are the ones that are being validated if they are shown or not.
	 *
	 * @since   6.0     	2019-04-26	Release
	 * @since	6.0.9.84	2019-11-25	Correction in validation of selected taxonomy
	 * @access  private
	 * @return  boolean
	 */
	private function display_in_taxonomy_hierarchical(){

		global $post;

		/**
		 * Valid if there is a taxonomy this custom post type
		 * if you have it, enter to validate if it shows in which part but
		 * in case it does not have then it returns TRUE because it can be shown by another validation
		 * remember that the CPT relationship that does not have taxonomies will now be for the title
		 *
		 */
		if( ! yuzo::instance()->public->related_algorithm->is_cpt_has_taxonomy( get_post_type( $post->ID ) ) ) return true;
		yuzo::instance()->logs->debug( "Ok " . get_post_type( $post->ID ) . " contains a taxonomy " );

		/**
		 * I get all the hierarchical taxonomies to be able to
		 * know which one is allowed to show the related post
		 */
		$taxonomies         = get_object_taxonomies( get_post_type( $post->ID ) );
		$taxo_hier          = [];
		$terms              = array();
		$taxonomies_exclude = array( 'post_format' );

		if( is_array( $taxonomies ) ){

			foreach ($taxonomies as $key => $value) {

				if( in_array( $value , $taxonomies_exclude ) ) { continue; }

				if( is_taxonomy_hierarchical($value) ){

					$taxo_hier[] = $value;

				}

			}
		}

		if( ! empty($taxo_hier) ){

			foreach ($taxo_hier as $key => $value) {

				$temp = wp_get_post_terms( $post->ID, $value, array( 'fields' => 'all' ) );
				if( ! empty( $temp ) ){
					foreach ($temp as $v) {
						$terms[] = $v->slug;
					}
				}

			}

		}else{
			return true;
			// If none is selected
			//if( ! is_array( $this->options->show_only_in_taxonomy_hierarchical ) ) return;
		}

		if( count($terms) > 0 ){

			$terms = yuzo_array_flatten( $terms );

		}

		$taxonomy_options             = pf_get_taxonomy_and_terms_available( $this->options->show_only_in_taxonomy_hierarchical );
		$taxonomy_with_all_terms      = ! empty( $taxonomy_options[0] ) ? $taxonomy_options[0] : [];
		$taxonomy_all_terms_avalibled = ! empty( $taxonomy_options[1] ) ? $taxonomy_options[1] : [];

		// ─── Valid if the current taxonomy is among the taxonomies that must be shown in all terms ────────
		if( count( array_intersect( $taxonomy_with_all_terms, $taxo_hier ) ) > 0 ){
			return true;
		// ─── Valid if one of the selected terms belong to one of the current post ────────
		}elseif( count( array_intersect( $terms, yuzo_array_flatten($taxonomy_all_terms_avalibled) ) ) > 0 ){
			return true;
		}

		return false;
	}

	/**
	 * It shows in different parts of the page, according to the Theme
	 *
	 * If parts of the Theme as: Home, category, tag, etc ... are normal
	 * blog type then Yuzo will show there either as Hook within the_content or
	 * also as shortcode, it all depends on the availability of the Theme.
	 *
	 * @since 6.0
	 * @access private
	 * @return boolean
	 */
	private function display_template_page(){

		// Variable array that has in which part of the theme show related post
		$page_view = (array) $this->options->show_only_in_places_on_the_page;

		// Validations for all the typical parts of a Theme.
		if( (is_home() || is_front_page() ) && in_array( 'homepage' , $page_view ) ){ return true; }
		if( is_category() && in_array( 'category' , $page_view ) ){ return true; }
		if( is_tag() && in_array( 'tag' , $page_view ) ){ return true; }
		if( is_author() && in_array( 'author' , $page_view ) ){ return true; }
		if( is_search() && in_array( 'search' , $page_view ) ){ return true; }
		if( is_archive() && in_array( 'archive' , $page_view ) ){ return true; }
		if( is_feed() && in_array( 'feed' , $page_view ) ){ return true; }

		return false;
	}

	/**
	 * Insert a text (related) in a paragraph position.
	 *
	 * @param   string  $insertion 	 	Text to be inserted
	 * @param   int     $paragraph_id  	Position of the paragraph in which it will be inserted
	 * @param   string 	$content        Content indexed from the hook (the_content)
	 *
	 * @since   6.0     2019-04-26 00:30:46     Release
	 * @access  public
	 * Hooked into 'the_content'
	 *
	 * @return string
	 */
	public function prefix_insert_after_paragraph( $insertion, $paragraph_id, $content ) {

		$closing_p = '</p>';
		$paragraphs = explode( $closing_p, $content );
		foreach ($paragraphs as $index => $paragraph) {
			if ( $paragraph_id == $index ) {
				$paragraphs[$index] = $insertion . $paragraphs[$index];
			}else{
				if ( trim( $paragraph ) ) {
					$paragraphs[$index] .= $closing_p;
				}
			}
		}

		return implode( '', $paragraphs );
	}

	public function calculate_position( $content ){

		$location = $this->options->fieldset_design['panels-design']['content_location'];
		$position = $this->options
						->fieldset_design['panels-design']['content_appende_paragraph_order']['location_paragraph'];

		if( $location == 'top-post-content' ){
			return 0;
		}elseif( $location == 'middle-post-content' ){
			$number_paragraph = explode( '</p>', $content );
			yuzo::instance()->logs->log( "Public ⇨ Display ⇨ yuzo id {$this->options->post_id} have number_paragraph for 		= middle-post-content " . count($number_paragraph));
			if( ! empty( $number_paragraph ) ){
				return ceil(count($number_paragraph)/2) - 1;
			}
		}elseif( in_array( $location , ['right-post-content','left-post-content']) ){
			return 0;
		}elseif( $location == 'top-paragraph-number' ){
			return $position;
		}elseif( $location == 'bottom-paragraph-number' ){
			$number_paragraph = explode( '</p>', $content );

			// ─── If you have a yuzo content then the correction factor is 2 otherwise it is 1 ────────
			$factor = in_array( 'content', $this->types_yuzo ) ? 2: 1;

			yuzo::instance()->logs->log( "Public ⇨ Display ⇨ yuzo id {$this->options->post_id} have number_paragraph for bottom-paragraph-number = " . count($number_paragraph));
			return (count($number_paragraph) - $factor - $position );
		}

		return 0;
	}

	/**
	 * Show the related only in specific posts
	 * Enter the IDs of the post you want to be displayed there only.
	 *
	 * @since   6.0     2019-04-26 00:31:54     Release
	 * @access  public
	 * @return  boolean
	 */
	public function display_only_specific_post(){

		global $post;

		$return 	= 	false;
		$array_ids  = 	$this->options->display_only_specific_postid ?
						explode( "," , $this->options->display_only_specific_postid ) :
						null;

		if( ! empty($array_ids) ){
			$return = ! in_array( $post->ID, $array_ids );
		}

		return $return;
	}

} }