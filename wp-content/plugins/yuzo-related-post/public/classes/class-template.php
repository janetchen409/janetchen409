<?php
namespace YUZO\Publi;
use YUZO\Core\YUZO_Core as yuzo;

if( ! class_exists( 'Yuzo_Template' ) ){
/**
 * Template and design functions
 *
 * @since		6.0		2019-07-26		Release
 *
 * @package    	Yuzo
 * @subpackage 	Yuzo/Public
 */
class Yuzo_Template{

	public
	/**
	 * Variable that contains the options of a single list of related
	 *
	 * @since 	6.0
	 * @access 	public
	 * @var 	array    $options    Get options from the database
	 */
	$options = [],
	/**
	 * Object variable of the class to obtain the image
	 * @since 	6.0		2019-06-11 15:42:37		Release
	 * @var 	object|mixed
	 */
	$imagen = null,
	/**
	 * Get settings general
	 * @since	6.0		2019-06-11 15:47:02		Release
	 * @var 	object|mixed
	 */
	$settings,
	/**
	 * Maximum number of post to show
	 *
	 * @since	6.0		2019-07-10 23:11:49		Release
	 * @see 	Yuzo_Related::$number_of_slack
	 */
	$max_num_post_to_show = 0,
	/**
	 * Current index of the post executed in the loop
	 *
	 * @since	6.0.9.4		2019-07-26 18:28:31		Release
	 */
	$post_item_current_index = 0,
	/**
	 * Estilos para los posts que deben mostrarse en lugar de los que no tienen imagen
	 *
	 * @since	6.0.9.4		2019-07-26 19:25:52		Release
	 * @see		self::template()
	 */
	$css_news_post_to_show = '',
	/**
	 * It contains an object of the maximum number of posts per resolutions
	 * return example: $r->mobile, $r->tablet, $r->desktop
	 *
	 * @since	6.0.9.4		2019-07-26 20:46:35		Release
	 */
	$post_number_per_resolution = null,
	/**
	 * It has an arrangement of the current number of posts shown by resolutions
	 *
	 * @see		6.0.9.4		2019-07-26 20:48:56		Release
	 */
	$current_index_post_per_resolution = ['mobile'=>0,'tablet'=>0,'desktop'=>0],
	/**
	 * Current query
	 *
	 * @since	6.0.9.7		2019-08-01 02:16:55		Release
	 * @access	public
	 * @var		object|null
	 */
	$query = null,
	/**
	 * Contains the information of the current Yuzo from the metabox
	 *
	 * @since	1.0		2019-09-22		Release
	 * @access	private
	 */
	$metabox_data = null;

	/**
	 * Init class
	 *
	 * @since 6.0
	 */
	public function __construct(){

		// This only works for front-end.
		//if( is_admin() ) return;

	}

	/**
	 * Print the final design of the post list
	 *
	 * @since 	6.0		2019-05-03	Release
	 * @since	6.0.9.7	2019-08-01	The entire query filter is removed and Yuzo query stays clean
	 * @since	6.1		2019-12-13	- New logs are added to verify errors
	 * 								- The option that shows text above and below the envelope is added
	 *
	 * @param 	array 	$ids
	 * @param 	mixed 	$options
	 * @return 	void
	 */
	public function design( $ids = [], $options = null ){

		global $post;

		$this->options  = yuzo_fix_var_design( $options );
		$this->settings = ! $this->settings ? yuzo::instance()->settings : $this->settings;
		$_html          = '';

		yuzo::instance()->logs->group( "Public ⇨ Template ⇨ call func design from yuzo id {$this->options->post_id}" );
		yuzo::instance()->logs->log( "All id to show ", $ids );

		if( is_array($ids) && count($ids)>0 ){
			$ids = yuzo_formater_ids( $ids, null );
			yuzo::instance()->logs->log( " Format the ids", $ids );
			$ids = array_unique( $ids );
			yuzo::instance()->logs->log( " Remove duplicates and leave unique id", $ids );
			$ids = $this->calculate_number_to_show_cut_array( $ids );
			yuzo::instance()->logs->log( " Calculate and adjust the id to show", $ids );
			$ids = array_map('intval', (array)$ids );
			yuzo::instance()->logs->log( " Put them as integers", $ids );

			$args =  array(
				"post__in"       => $ids,
				'orderby'        => 'post__in',
				'post_type'      => (array) $this->options->cpt_to_related,
				'post_status'    => $this->status_post(),
				'posts_per_page' => -1,
				'tax_query'      => [],
				'query_label'    => 'yuzo_final_sql',
				'ignore_sticky_posts' => 1
			);

			// ─── Filter the Yuzo query to clear the $ where variable ────────
			// Another plugin may be filtering queries, with this Yuzo is cleanly
			// ready to execute your query perfectly
			add_filter('posts_where', function ($where, $query) use( $ids ) {
				global $wpdb;
				$label = $query->query['query_label'] ?? '';
				if($label === 'yuzo_final_sql') { // Just filter the yuzo query
					//-- More stuff will go here.
					$where = " AND {$wpdb->prefix}posts.ID in ( ". implode(",",$ids) . ")";
				}

				return $where;
			}, 10, 2);

			$this->query = new \WP_Query( $args );

			/* add_filter( 'posts_where', function( $where = ''){
				$where = "";
				return $where;
			}); */

			//echo '<br /><br />'.$GLOBALS['wp_query']->request;
			yuzo::instance()->logs->debug("sql template (before loop)", $this->query->request );

			// Add text/html above
			$_html .= isset( $this->options->fieldset_design['panels-design']['design_html_above'] ) ? $this->options->fieldset_design['panels-design']['design_html_above'] : '';

			if ( $this->query->have_posts() ) :

				// Class name of this yuzo
				$_html_array_template = $this->get_class_and_css_template( $this->options );
				$_html_class_template = $_html_array_template[0];
				//$_html_css_template   = $_html_array_template[1];

				// Wrap
				$_html .= '<section YuzoPluginFree class="wp-yuzo yzp-wrapper '. $_html_class_template .'" data-version="'. YUZO_VERSION .'" ';
				$_html .= ' data-id="'. $this->options->post_id .'"  data-type="'. $this->get_type_layout_list_post() .'" data-level="'. yuzo_get_index_level( yuzo_get_views( $post->ID ) ) .'" >';

				// Title related
				if( $this->options->fieldset_design['panels-design']['where_show'] != 'widget' && $this->options->fieldset_design['panels-design']['template_type'] != 'inline' ){
					$_html .= $this->options->fieldset_design['panels-design']['title'];
				}

				// Loop related
				$template = $this->options->fieldset_design['panels-design']['template'] ?: 'default' ; // $this->options->design_layout == 'grid' ? $this->options->design_templates_grid : $this->options->design_templates_list;
				yuzo::instance()->logs->log( "Public ⇨ Template ⇨ Template name ", $template );
				$_html .= $this->loop( $template );

				$_html .= '</section>';

			endif;
			//wp_reset_query();
			wp_reset_postdata();

			// Add text/html below
			$_html .= isset( $this->options->fieldset_design['panels-design']['design_html_below'] ) ? $this->options->fieldset_design['panels-design']['design_html_below'] : '';
		}

		// apply reset
		$this->reset_per_yuzo();

		yuzo::instance()->logs->groupEnd();

		return $_html;
	}

	/**
	 * Get the type of list / related that is showing, the types are:
	 * *1. c = content
	 * *2. w = widget
	 * *3. s = shortcode
	 *
	 * @since 	6.0		2019-05-19 18:55:24		Release
	 * @since	6.0.2	2019-07-12 06:27:17		Removed 'inline' to put the shortcode
	 * @return 	array
	 */
	private function get_type_layout_list_post(){

		$array = [
			'content'   => 'c',
			'widget'    => 'w',
			'shortcode' => 's',
		];

		return $array[ $this->options->fieldset_design['panels-design']['where_show'] ];
	}

	/**
	 * Calculate the number of post (ids) that will show after adding
	 * the post included.
	 *
	 * @since	6.0		2019-05-19 19:19:09		Release
	 * @since
	 * @return 	array
	 */
	private function calculate_number_to_show_cut_array( $ids = [] ){
		$total = (int) yuzo::instance()->public->related_algorithm->get_number_post();
		$this->max_num_post_to_show = $total; // - (int) yuzo::instance()->public->related_algorithm->number_of_slack;
		return array_splice( $ids, 0, $total );
	}

	/**
	 * Status post to show
	 * This is also found in the class 'related'
	 *
	 * @since 	6.0		2019-05-23 02:14:21		Release
	 * @return 	string
	 *
	 * @see yuzo::public::related::status_post
	 */
	private function status_post(){
		return "publish";
	}

	/**
	 * Design of the yuzo loop post
	 *
	 * @since	6.0		2019-06-08 14:00:44		Release
	 * @since	6.0.9.7	2019-08-01 23:27:47		The query was changed as an object
	 *
	 * @param 	array	$template				Template Name
	 * @param	object	$opts					Temporary object of options of the current Yuzo
	 * @param	object	$setting				General configuration
	 * @param	object	$query					Generic query, used for the preview
	 * @return 	string
	 */
	public function loop( $template = '', $opts = null, $settings = null, $query = null ){
		//global $wp_query;
		//echo $wp_query->found_posts . " " . $wp_query->request;
		// Just send data in the preview
		$this->options  = $opts ? $opts : $this->options;
		$this->settings = $settings ? $settings : $this->settings;
		$this->query    = $query ? $query : $this->query;
		// ───────────────────────────

		// If there is a post, then run the class to get the images of each one
		$args = [
			'default' => $this->settings->general_image_default['url'],
			'size'    => $this->options->fieldset_design['panels-design']['design_thumbnail_size']  ?: 'medium',
		];
		$this->imagen = ! $this->imagen ? new \wpImage( $args ) : $this->imagen;

		// Defauls Values
		$defaults = [
			'target'      => '',
			'nofollow'    => '',
			'remove_href' => ' href',
			'template'    => $template,
		];
		$args = [
			'target'      => ! empty( $this->settings->general_seo_target_link ) ? ' target="_blank"' : '',
			'nofollow'    => ! empty( $this->settings->general_seo_rel_nofollow ) ? ' rel="nofollow"' : '',
			'remove_href' => ! empty( $this->settings->general_seo_remove_href ) ? ' data-href' : ' href',
		];

		// Define variables
		$args = pf_wp_parse_args( $args, $defaults );

		$_html  = '';
		$_html .= "<ul class='yzp-container' >";
		$i 		= 0;
		while ( $this->query->have_posts() ) : $this->query->the_post();
			if( ! isset($_REQUEST['is_preview']) && $this->max_num_post_to_show <= $i ){ break; }
			$_html .= $this->template( $args );
			$i++;
		endwhile;

		$_html .= "</ul>";

		// I check if there is an add style to add
		if( ! empty( $this->settings->general_exclude_post_without_image )  ){
			$this->css_news_post_to_show = '';
			$this->css_news_post_to_show .= '<style>';
			$this->css_news_post_to_show .= ".yzp-id-{$this->options->post_id}.yzp-wrapper .yzp-container .yzp-wrap-item.esp-show-in-mobile{display:none;}";
			$this->css_news_post_to_show .= ".yzp-id-{$this->options->post_id}.yzp-wrapper .yzp-container .yzp-wrap-item.esp-show-in-tablet{display:none;}";
			$this->css_news_post_to_show .= ".yzp-id-{$this->options->post_id}.yzp-wrapper .yzp-container .yzp-wrap-item.esp-show-in-desktop{display:none;}";

			$this->css_news_post_to_show .= "@media screen and (min-width: 319px) and (max-width: 767px) {.yzp-id-{$this->options->post_id}.yzp-wrapper .yzp-container .yzp-wrap-item.esp-show-in-mobile{display:list-item;}}";
			$this->css_news_post_to_show .= "@media screen and (min-width: 768px) and (max-width: 1024px) {.yzp-id-{$this->options->post_id}.yzp-wrapper .yzp-container .yzp-wrap-item.esp-show-in-tablet{display:list-item;}}";
			$this->css_news_post_to_show .= "@media screen and (min-width: 1025px) {.yzp-id-{$this->options->post_id}.yzp-wrapper .yzp-container .yzp-wrap-item.esp-show-in-desktop{display:list-item;}}";
			$this->css_news_post_to_show .= '</style>';
		}
		return $_html . $this->css_news_post_to_show;
	}

	/**
	 * Return the class of the envelope and the CSS necessary for the design of related post
	 *'
		* @since	6.0			2019-06-10	Release
		* @since	6.0.2		2019-07-12	Special list template is validated without imenes so as not to add a class
		* @since	6.0.9.81	2019-08-28	- The css for margin, padding and background of the yuzo wrap was added
		* 									- Post spacing added
		*
		* @since	6.0.9.82	2019-09-03	- It was placed! Important to custom attributes
		*									- Class was added for a template with image no longer have it
		*
		* @param 	string 	$opts					Options
		* @return 	array
		*/
	public function get_class_and_css_template( $opts ){

		// Calculation of the class of columns by device
		$class_mobile_columns  = ' yzp-mobile-columns-' . (int)$opts->fieldset_design['panels-design']{'design_screen_mobile'}['design_screen_mobile_columns'];
		$class_tablet_columns  = ' yzp-tablet-columns-' . (int)$opts->fieldset_design['panels-design']['design_screen_tablet']['design_screen_tablet_columns'];;
		$class_desktop_columns = ' yzp-desktop-columns-' . (int)$opts->fieldset_design['panels-design']['design_screen_desktop']['design_screen_desktop_columns'];;
		$opts->fieldset_design['panels-design']['design_image_size'] =   $opts->fieldset_design['panels-design']['design_image_size'] ?: '';

		$class_aspect_ratio    = ' yzp-aspect-ratio-' . ( $opts->fieldset_design['panels-design']['design_image_size'] ?: '1-1' );

		$class_template = ' yzp-template-'; //' yzp-' . ( $opts->design_layout == 'grid' ? $opts->design_templates_grid : $opts->design_templates_list  );
		$class_template .= $type_layout = $opts->fieldset_design['panels-design']['template_type'] ?: 'grid';
		$class_template .= '-' . ($opts->fieldset_design['panels-design']['template'] ?: 'x');
		// FIXME you must validate in some way that that item is a grid | list | inline | etc .. that is with a field hidden by js to identify
		$class_template .= ' yzp-layout-' . $type_layout; // ( $opts->design_layout == 'grid' ? 'layout-grid' : 'layout-list' );

		if( $opts->fieldset_design['panels-design']['where_show'] == 'content' && in_array( $opts->fieldset_design['panels-design']['content_location'] , ['left-post-content', 'right-post-content']) ){
			$class_template .= ' yzp-align-content-' . ($opts->fieldset_design['panels-design']['content_location'] == 'left-post-content' ? 'left' : 'right' );
		}
		/*
		|--------------------------------------------------------------------------
		| Custom
		|--------------------------------------------------------------------------
		*/
		// Template colours
		if( ! empty( $opts->fieldset_design['panels-design']['template'] ) && $opts->fieldset_design['panels-design']['template'] == 'colours' ){
			if( ! empty( $opts->fieldset_design['panels-design']['template_colours'] ) ){
				$class_template .= ' yzp-template-colours-' . $opts->fieldset_design['panels-design']['template_colours'];
			}
			if( empty( $opts->fieldset_design['panels-design']['template_show_imagen'] ) ){
				$class_template .= ' yzp-template-colours-no-image';
			}
		}
		// ───────────────────────────
		$class_template .= ( $opts->fieldset_design['panels-design']['where_show'] == 'widget' ? ' yzp-widget ' : '' );
		$class_id_yuzo = ' yzp-id-' . $opts->post_id;

		// Class that identifies whether or not it has a goal
		$class_meta = '';
		if( empty( $opts->fieldset_design['panels-design']['design_metas']['enabled'] ) ){
			$class_meta = ' yzp-without-meta';
		}

		// If you do not have excerpt
		if( (int)$opts->fieldset_design['panels-design']['design_show_excerpt'] == 0 ){
			$class_template .= ' yzp-without-excerpt';
		}


		// Compose the final array
		$array_return[0] = $class_id_yuzo . $class_template . $class_meta  . $class_aspect_ratio . $class_mobile_columns . $class_tablet_columns . $class_desktop_columns;
		$array_return[1] = ' /* css style */ ';

		return $array_return;
	}

	public function header_css( $opts ){

		// This would only work for administrator iframes
		$css_current_theme = '';

		// This is the basis for all templates
		$css_base = $css_current_theme . ""; // Here was the base

		// Send the style sheet only for the preview
		if( isset($_REQUEST['is_preview']) && $_REQUEST['is_preview'] ){
			//$min = ! YUZO_MODE_DEV ? '.min' : '';
			//$css_base .= file_get_contents( YUZO_URL . '/public/assets/css/yuzo'.$min.'.css' );
			$css_base .= '.yzp-wrapper .yzp-container .yzp-wrap-item .yzp-item .yzp-metas .yzp-meta::before, .yzp-inline .yzp-container .yzp-wrap-item .yzp-item .yzp-metas .yzp-meta::before, .yzp-widget .yzp-container .yzp-wrap-item .yzp-item .yzp-metas .yzp-meta::before{background-image: url('.YUZO_URL.'/public/assets/images/metas.svg);}';
		}

		// ─── Place all custom designs for each active yuzo  ────────
		// if preview admin
		if( is_admin() ){
			$yuzos_active[] = $opts;
		}else{
			$yuzos_active = yuzo::instance()->public->related_display->list_yuzo_active;
		}

		// ─── Get the general setting ────────
		$this->settings = ! $this->settings ? yuzo::instance()->settings : $this->settings;

		$css_custom   = '';
		$css_template = '';
		if( ! empty( $yuzos_active ) ){
			foreach ($yuzos_active as $key => $value) {
				$opts = $this->options = (object)$value;
				$opts = yuzo_fix_var_design( $opts );
				$yuzo_current_id = ".yzp-id-{$opts->post_id}";
				// Calculate columns mobile, tablet, desktop
				$number_posts = $this->css_number_post_diplay( $opts );
				$css_custom .= $yuzo_current_id.".yzp-wrapper .yzp-container .yzp-wrap-item{display:none;}";
				if( empty( $this->settings->general_exclude_post_without_image )  ){
					$css_custom .= "@media screen and (min-width: 319px) and (max-width: 767px) {" . $number_posts['mobile'] . "}";
					$css_custom .= "@media screen and (min-width: 768px) and (max-width: 1024px) {" . $number_posts['tablet'] . "}";
					$css_custom .= "@media screen and (min-width: 1025px) {" . $number_posts['desktop'] . "}";
				}

				// Image size ratio
				$opts->fieldset_design['panels-design']['design_image_size'] = $opts->fieldset_design['panels-design']['design_image_size'] ?: '';
				$ratio = $opts->fieldset_design['panels-design']['design_image_size'];
				$css_custom .= $yuzo_current_id.'.yzp-wrapper.yzp-aspect-ratio-' . $ratio  . '{ --yzp-aspect-ratio:'. str_replace( [":","-"], "/", $ratio  )  .' } ' ;
				// This custom is for each yuzo number post
				$css_custom .= $yuzo_current_id.'.yzp-wrapper.yzp-aspect-ratio-' . str_replace( ":", "-",$ratio ) . '{ --yzp-aspect-ratio:'. str_replace( [":","-"], "/",$ratio )  .' } ' ;

				/*
				|--------------------------------------------------------------------------
				| Templates
				|--------------------------------------------------------------------------
				*/
				$css = '';
				$css_id_yuzo = '.yzp-id-' . $opts->post_id;
				$template_type =  ! empty( $this->options->fieldset_design['panels-design']['template_type'] ) ? $this->options->fieldset_design['panels-design']['template_type'] : null;
				// Theme Yuzo inline
				if( ! empty( $template = $opts->fieldset_design['panels-design']['template']) ) {
					$color1 = ! empty( $opts->fieldset_design['panels-design']['template_color_1'] ) ? $opts->fieldset_design['panels-design']['template_color_1'] : '';
					$color2 = ! empty( $opts->fieldset_design['panels-design']['template_color_2'] ) ? $opts->fieldset_design['panels-design']['template_color_2'] : '';
					if( $template == 'yuzo-i' ){
						if( $color1 ) {
							$sample = $color1;
							$color1 = pf_hex2rgba($color1,0.8);
							$color1_b = pf_hex2rgba($sample,0.9);
							$color1_c = pf_hex2rgba('#EBEEFB',0.5);
							$css .= $css_id_yuzo.'.yzp-wrapper.yzp-layout-inline.yzp-template-inline-yuzo-i .yzp-container .yzp-wrap-item:hover{background-color: '. $color1 .'!important;}';
							$css .= $css_id_yuzo.'.yzp-wrapper.yzp-layout-inline.yzp-template-inline-yuzo-i .yzp-container .yzp-wrap-item:hover .yzp-item-button{background-color: '. $color1_b .'!important;}';
							$css .= $css_id_yuzo.'.yzp-wrapper.yzp-layout-inline.yzp-template-inline-yuzo-i .yzp-container .yzp-wrap-item .yzp-item .yzp-item-title .yzp-title-inline{color: '. $color1 .'!important;}';
							$css .= $css_id_yuzo.'.yzp-wrapper.yzp-layout-inline.yzp-template-inline-yuzo-i .yzp-container .yzp-wrap-item:hover .yzp-item .yzp-item-title .yzp-title-inline{color: '. $color1_c .'!important;}';
						}
					}elseif( $template == 'yuzo-i2' ){
						if( $color2 ){
							$sample = $color2;
							$color2 = pf_hex2rgba($color2,0.7);
							$color2_b = pf_hex2rgba($sample,0.6);
							$css .= $css_id_yuzo.'.yzp-wrapper.yzp-layout-inline.yzp-template-inline-yuzo-i2 .yzp-container .yzp-wrap-item .yzp-item-button a{background-color: '. $color2 .'!important;}';
							$css .= $css_id_yuzo.'.yzp-wrapper.yzp-layout-inline.yzp-template-inline-yuzo-i2 .yzp-container .yzp-wrap-item:hover .yzp-item-button a{background-color: '. $color2_b .'!important;}';
						}
					}
					elseif( $template == 'default-i' ){
						if( $color1 ){
							$color1 = pf_hex2rgba($color1,0.7);
							$css .= $css_id_yuzo.'.yzp-wrapper.yzp-layout-inline .yzp-container .yzp-wrap-item .yzp-item{background-color: '. $color1 .'!important;padding-left: 10px;padding-top: 11px}';
						}
						if( $color2 ){
							$color2 = pf_hex2rgba($color2,0.8);
							$css .= $css_id_yuzo.'.yzp-wrapper.yzp-layout-inline .yzp-container .yzp-wrap-item .yzp-item{ border-left: 3px solid '.$color2.'!important;}';
						}
					}
				}

				/*
				|--------------------------------------------------------------------------
				| Text custom
				|--------------------------------------------------------------------------
				*/
				$text_custom = $opts->fieldset_design['panels-design'] ?: null;
				if( isset($text_custom['design_text_font_size']['width']) && ! empty( $font_size = $text_custom['design_text_font_size']['width'] ) ){
					$unit = 'px';//$text_custom['design_text_font_size']['unit'];
					$css .= $css_id_yuzo.'.yzp-wrapper .yzp-container .yzp-wrap-item .yzp-item .yzp-item-title a{font-size: '. $font_size . $unit .'!important;}';
				}
				if( isset($text_custom['design_text_line_height']['width']) && ! empty( $line_height = $text_custom['design_text_line_height']['width'] ) ){
					$unit = 'px';//$text_custom['design_text_line_height']['unit'];
					$css .= $css_id_yuzo.'.yzp-wrapper .yzp-container .yzp-wrap-item .yzp-item .yzp-item-title a{ line-height: '. $line_height . $unit .'!important; }';
				}
				if( isset($text_custom['design_text_font_weight']) && ! empty( $font_weight = $text_custom['design_text_font_weight'] ) ){
					$css .= $css_id_yuzo.'.yzp-wrapper .yzp-container .yzp-wrap-item .yzp-item .yzp-item-title a{ font-weight: '. $font_weight .'!important; }';
				}
				if( ( isset($text_custom['design_text_color_hover']['color']) || isset($text_custom['design_text_color_hover']['hover']) ) && ( ! empty( $color = $text_custom['design_text_color_hover']['color'] ) || ! empty( $color = $text_custom['design_text_color_hover']['hover']) ) ){
					$hover =  ! empty( $text_custom['design_text_color_hover']['hover'] )  ? $text_custom['design_text_color_hover']['hover'] : null;
					if( ! empty( $color = $text_custom['design_text_color_hover']['color'] ) ){
						$css .= $css_id_yuzo.'.yzp-wrapper .yzp-container .yzp-wrap-item .yzp-item .yzp-item-title a{ color: '. $color .'!important; }';
					}
					if( $hover ){
						$css .= $css_id_yuzo.'.yzp-wrapper .yzp-container .yzp-wrap-item .yzp-item:hover .yzp-item-title a{ color: '. $hover .'!important; }';
					}
				}
				/*
				|--------------------------------------------------------------------------
				| Wrap box
				|--------------------------------------------------------------------------
				*/
				$box_margin = isset($opts->fieldset_design['panels-design']['design_box_margin']) ? $opts->fieldset_design['panels-design']['design_box_margin'] : null;
				if( ! empty( $box_margin['top'] ) ){
					$css .= $css_id_yuzo.'.yzp-wrapper{margin-top:'. $box_margin['top'] . $box_margin['unit'] .';}';
				}
				if( ! empty( $box_margin['bottom'] ) ){
					$css .= $css_id_yuzo.'.yzp-wrapper{margin-bottom:'. $box_margin['bottom'] . $box_margin['unit'] .';}';
				}
				if( ! empty( $box_margin['left'] ) ){
					$css .= $css_id_yuzo.'.yzp-wrapper{margin-left:'. $box_margin['left'] . $box_margin['unit'] .';}';
				}
				if( ! empty( $box_margin['right'] ) ){
					$css .= $css_id_yuzo.'.yzp-wrapper{margin-right:'. $box_margin['right'] . $box_margin['unit'] .';}';
				}
				$box_padding = isset($opts->fieldset_design['panels-design']['design_box_padding']) ? $opts->fieldset_design['panels-design']['design_box_padding'] : null;
				$class_padding_template_inline = $template_type == 'inline' ? ' .yzp-container .yzp-wrap-item .yzp-item' : '';
				if( ! empty( $box_padding['top'] ) ){
					$css .= $css_id_yuzo.'.yzp-wrapper '.$class_padding_template_inline.'{padding-top:'. $box_padding['top'] . $box_padding['unit'] .'!important;}';
				}
				if( ! empty( $box_padding['bottom'] ) ){
					$css .= $css_id_yuzo.'.yzp-wrapper '.$class_padding_template_inline.'{padding-bottom:'. $box_padding['bottom'] . $box_padding['unit'] .'!important;}';
				}
				if( ! empty( $box_padding['left'] ) ){
					$css .= $css_id_yuzo.'.yzp-wrapper '.$class_padding_template_inline.'{padding-left:'. $box_padding['left'] . $box_padding['unit'] .'!important;}';
				}
				if( ! empty( $box_padding['right'] ) ){
					$css .= $css_id_yuzo.'.yzp-wrapper '.$class_padding_template_inline.'{padding-right:'. $box_padding['right'] . $box_padding['unit'] .'!important;}';
				}
				$box_background = isset($opts->fieldset_design['panels-design']['design_box_background']) ? $opts->fieldset_design['panels-design']['design_box_background'] : null;
				if( ! empty($box_background) ){
					$css .= $css_id_yuzo.'.yzp-wrapper{background:'. $box_background .';}';
				}
				/*
				|--------------------------------------------------------------------------
				| Wrap post
				|--------------------------------------------------------------------------
				*/
				$post_wrap = $opts->fieldset_design['panels-design'];
				if( isset($post_wrap['design_post_spacing']) && ! empty( $post_spacing = $post_wrap['design_post_spacing']) ){
					$spacing_unit = 'px';
					if(  ! empty( $post_spacing['width'] )  ){
						$css .= $css_id_yuzo.'.yzp-wrapper .yzp-container { grid-gap:'. $post_spacing['width'] . $spacing_unit .'; } ';
					}
				}

				// Get css template
				$css_template .= $css; //$this->css_template( $opts );

				// Get css customizer
				//$css_custom .= pf_css_output_styling( $args );

			}
		}

		// Final css
		$css_final = "$css_base $css_template $css_custom";

		if( ! is_admin() )
			echo "<style>$css_final</style>";
		else
			return $css_final;
	}

	/**
	 * Link and text colors
	 *
	 * @since	6.0.2	2019-07-12 06:29:49		Release
	 * @return	string
	 */
	public function css_text_color(){
		$css_color_text = '';
		$yuzo_current_id = ".yzp-id-{$this->options->post_id}";
		if( !empty ($this->options->design_styling_color['color_title']['color']) ){
			$css_color_text .= $yuzo_current_id . ".yzp-wrapper .yzp-container .yzp-wrap-item .yzp-item a{ color: " . $this->options->design_styling_color['color_title']['color'] . "!important; }";
		}

		if( !empty ($this->options->design_styling_color['color_title']['hover']) ){
			$css_color_text .= $yuzo_current_id . ".yzp-wrapper .yzp-container .yzp-wrap-item .yzp-item:hover a{ color: " . $this->options->design_styling_color['color_title']['hover'] . "!important; }";
		}

		if( !empty ($this->options->design_styling_color['color_text']) ){
			$css_color_text .= $yuzo_current_id . ".yzp-wrapper .yzp-container .yzp-wrap-item .yzp-item .yzp-excerpt{ color: " . $this->options->design_styling_color['color_text'] . "!important; }";
		}

		return $css_color_text;
	}

	/**
	 * Valid through the current yuzo option that post
	 * must show.
	 *
	 * @since	6.1.32	2020-01-05	Doc update
	 * @since	6.1.45	2020-01-24	If inline then only show one post
	 * @param	object	$opts		It has all the configuration of the current yuzo
	 */
	public function css_number_post_diplay( $opts = null ){
		$columns  = $this->post_number_per_resolution = (object)yuzo::instance()->public->related_algorithm->get_number_post( $opts, true );
		$_columns_css_mobile  = '';
		$_columns_css_tablet  = '';
		$_columns_css_desktop = '';
		$yuzo_current_id      = ".yzp-id-{$opts->post_id}";

		// If template is INLINE then 1 should be displayed
		if( $this->options->fieldset_design['panels-design']['template_type'] == 'inline' ){
			/* $_columns_css_mobile .= $yuzo_current_id.'.yzp-wrapper .yzp-container .yzp-wrap-item:nth-child(1){display:list-item;}';
			$_columns_css_tablet .= $yuzo_current_id.'.yzp-wrapper .yzp-container .yzp-wrap-item:nth-child(1){display:list-item;}';
			$_columns_css_desktop .= $yuzo_current_id.'.yzp-wrapper .yzp-container .yzp-wrap-item:nth-child(1){display:list-item;}'; */
			$columns->mobile = 1;
			$columns->tablet = 1;
			$columns->desktop = 1;
		}

		//else{
		for ($i=1; $i <= $columns->mobile ; $i++) {
			$_columns_css_mobile .= $yuzo_current_id.'.yzp-wrapper .yzp-container .yzp-wrap-item:nth-child('.$i.'){display:list-item;}';
			$_columns_css_mobile .= $yuzo_current_id.'.yzp-wrapper.yzp-layout-grid .yzp-container .yzp-wrap-item:nth-child('.$i.'){list-style:none;}';
		}
		for ($i=1; $i <= $columns->tablet ; $i++) {
			$_columns_css_tablet .= $yuzo_current_id.'.yzp-wrapper .yzp-container .yzp-wrap-item:nth-child('.$i.'){display:list-item;}';
			$_columns_css_tablet .= $yuzo_current_id.'.yzp-wrapper.yzp-layout-grid .yzp-container .yzp-wrap-item:nth-child('.$i.'){list-style:none;}';
		}
		for ($i=1; $i <= $columns->desktop ; $i++) {
			$_columns_css_desktop .= $yuzo_current_id.'.yzp-wrapper .yzp-container .yzp-wrap-item:nth-child('.$i.'){display:list-item;}';
			$_columns_css_desktop .= $yuzo_current_id.'.yzp-wrapper.yzp-layout-grid .yzp-container .yzp-wrap-item:nth-child('.$i.'){list-style:none;}';
		}
		//}

		return [
			'mobile'  => $_columns_css_mobile,
			'tablet'  => $_columns_css_tablet,
			'desktop' => $_columns_css_desktop,
		];
	}

	public function css_template( $opts ){

		$css = '';
		if( ! empty( $opts ) ){
			null;
		}
		return $css;
	}

	/**
	 * Get the meta category
	 *
	 * If a post has a child category, it will take the last child
	 * @see gist.github.com/ivandoric/11215420
	 *
	 * @since	6.0		2019-06-19 20:33:09		Release
	 * @return 	string
	 */
	public function get_meta_category(){
		//Get all terms associated with post in taxonomy 'category'
		$terms = get_the_terms(get_the_ID(),'category');
		//Get an array of their IDs
		$term_ids = wp_list_pluck($terms,'term_id');
		//Get array of parents - 0 is not a parent
		$parents = array_filter(wp_list_pluck($terms,'parent'));
		//Get array of IDs of terms which are not parents.
		$term_ids_not_parents = array_diff($term_ids,  $parents);
		//Get corresponding term objects
		$terms_not_parents = array_intersect_key($terms,  $term_ids_not_parents);

		if( ! empty( $terms_not_parents[0] ) ){
			return $terms_not_parents[0]->name;
		}else{
			return '';
		}
	}

	/**
	 * Get the date goal with the wordpress format
	 *
	 * @since	6.0		2019-06-19 20:33:43		Release
	 * @return 	string
	 */
	public function get_meta_date(){
		return get_the_date();
	}

	/**
	 * Get the last modification date of the post
	 *
	 * @since	6.0		2019-06-19 21:27:30		Release
	 * @return	string
	 */
	public function get_meta_the_modified(){
		return get_the_modified_date();
	}

	/**
	 * Get the meta view of the post visits
	 *
	 * @since	6.0		2019-06-19 20:34:27		Release
	 * @return 	void
	 */
	public function get_meta_view(){
		return yuzo_get_views( get_the_ID(), $this->settings, false );
	}

	/**
	 * Get the meta author of the post
	 *
	 * @since	6.0		2019-06-19 20:34:54		Release
	 * @return 	string
	 */
	public function get_meta_author(){
		return get_the_author();
	}

	/**
	 * Get the goal of number of comments
	 *
	 * @since	6.0		2019-06-19 20:35:36		Release
	 * @return	string
	 */
	public function get_meta_comment(){
		return get_comments_number();
	}

	public function get_excerpt(){
		$show         = $this->options->fieldset_design['panels-design']['design_show_excerpt'] ?: ( isset($this->options->design_show_excerpt) && $this->options->design_show_excerpt ? $this->options->design_show_excerpt : false );
		$text_length  = $this->options->fieldset_design['panels-design']['design_text_length'] ?: ($this->options->design_text_length ?: 80 );
		$text_content = $this->options->fieldset_design['panels-design']['design_text_content'] ?: ($this->options->design_text_content ?: 'from_content' );

		if( $show ){

			$length = (int) $text_length ;

			if( $text_content == 'from_content'  ){
				$text_formater = yuzo_formater_text( get_the_content(), $length );
			}elseif( $text_content == 'from_excert' ){
				$text_formater = yuzo_formater_text( get_the_excerpt(), $length );
			}

			// ─── If it is excerpt and it is empty then we take the content for the text ────────
			if( $text_content == 'from_excert' && empty( $text_formater ) ){
				$text_formater = yuzo_formater_text( get_the_content(), $length );
			}

			return $text_formater;
		}

		return '';
	}

	/**
	 * Show the selected goals and the order
	 *
	 * @since	6.0		2019-06-19 20:35:51		Release
	 * @return	string
	 */
	public function template_meta(){

		$out = '';
		if( empty( $this->options->fieldset_design['panels-design']['design_metas']['enabled'] ) ) return;
		$out.= '<div class="yzp-item-metas">';
		foreach( $this->options->fieldset_design['panels-design']['design_metas']['enabled'] as $key ){
			if( $key == 'Date' ){
				$out.= '<span class="yzp-meta yzp-meta-date">'. $this->get_meta_date() .'</span>';
			}elseif( $key == 'Category' ){
				$out.= '<span class="yzp-meta yzp-meta-cat">'. $this->get_meta_category() .'</span>';
			}elseif( $key == 'View' ){
				$out.= '<span class="yzp-meta yzp-meta-view">'. $this->get_meta_view() .'</span>';
			}elseif( $key == 'Author' ){
				$out.= '<span class="yzp-meta yzp-meta-author">'. $this->get_meta_author() .'</span>';
			}elseif( $key == 'Comment' ){
				$out.= '<span class="yzp-meta yzp-meta-comment">'. $this->get_meta_comment() .'</span>';
			}elseif( preg_replace('/\s+/', '', $key) == 'Datelastupdate' ){
				$out.= '<span class="yzp-meta yzp-meta-date">'. $this->get_meta_the_modified() .'</span>';
			}
		}

		$out.= '</div>';
		return $out;
	}

	/**
	 * Reset default values ​​for each Yuzo
	 *
	 * @since	6.0.9.4
	 * @return  void
	 */
	private function reset_per_yuzo(){
		// Reset count current yuzo
		$this->current_index_post_per_resolution = ['mobile'=>0,'tablet'=>0,'desktop'=>0];
	}

	/**
	 * Get the post image
	 * The image is displayed in this order:
	 * 		- Yuzo posts
	 * 		- Feature posts
	 * 		- Imagen insert in posts
	 * 		- Html tag img
	 * 		- Default image
	 *
	 * @since	6.0.9.82	2019-10-03		Release
	 * @return 	string
	 */
	private function get_image( $id = 0 ){

		if( ! $id ) return $this->imagen->get_image( get_the_ID() );

		$imagen = null;
		$metabox_yuzo = get_post_meta( $id , "yuzo-in-post" );

		if( ! empty( $metabox_yuzo ) ){
			$imagen = ! empty( $metabox_yuzo[0]['photo_post'] )
			&& $metabox_yuzo[0]['photo_post']
			? $metabox_yuzo[0]['photo_post'] : false;

			if( ! empty( $imagen ) && count($imagen) > 0 && $imagen['url'] ){
				$imagen['src']  = $imagen['url'];
				$imagen['alt']  = $imagen['alt'];
				$imagen = (object)$imagen;
			}else{
				$this->imagen->get_image( get_the_ID() );
			}
		}

		return isset($imagen->url) && $imagen->url ? $imagen : $this->imagen->get_image( get_the_ID() );
	}

	/**
	 * Validate additional classes that each item could have
	 *
	 * @since	6.0.9.4		2019-07-26	Release
	 * @since	6.1.32		2020-01-05	The variable 'post_number_per_resolution' is updated for each Yuzo
	 * @return 	string
	 */
	private function item_post_custom_class( $args = [] ){

		$class_custom = '';
		/*
		|--------------------------------------------------------------------------
		| To exclude posts that have no image
		|--------------------------------------------------------------------------
		*/
		$this->current_index_post_per_resolution['mobile']++;
		$this->current_index_post_per_resolution['tablet']++;
		$this->current_index_post_per_resolution['desktop']++;

		// ─── If the template is an INLINE type then it will always return 1 ────────
		if( $this->options->fieldset_design['panels-design']['template_type'] == 'inline' ){
			$class_custom .= ' esp-show-in-mobile esp-show-in-tablet  esp-show-in-desktop';
		}else{
			// Valid if the option of not showing post without photo is activated then do not enter to put these classes
			if( !( $args['image']->src == $this->settings->general_image_default['thumbnail'] && ! empty( $this->settings->general_exclude_post_without_image ) ) ){
				$this->post_number_per_resolution = (object)yuzo::instance()->public->related_algorithm->get_number_post( $this->options, true );
				if( isset($this->post_number_per_resolution->mobile) && $this->post_number_per_resolution->mobile >= $this->current_index_post_per_resolution['mobile'] ){
					$class_custom .= ' esp-show-in-mobile';
				}
				if( isset($this->post_number_per_resolution->tablet) && $this->post_number_per_resolution->tablet >= $this->current_index_post_per_resolution['tablet'] ){
					$class_custom .= ' esp-show-in-tablet';
				}
				if( isset($this->post_number_per_resolution->desktop) && $this->post_number_per_resolution->desktop >= $this->current_index_post_per_resolution['desktop'] ){
					$class_custom .= ' esp-show-in-desktop';
				}
			}else{
				$this->current_index_post_per_resolution['mobile']--;
				$this->current_index_post_per_resolution['tablet']--;
				$this->current_index_post_per_resolution['desktop']--;
			}
		}

		/*
		|--------------------------------------------------------------------------
		| If you don't have an image, the posts will add a class
		|--------------------------------------------------------------------------
		*/
		if( ! empty( $args['image'] ) ){
			if( $args['image']->src == $this->settings->general_image_default['thumbnail'] ){
				$class_custom .= ' yzp-without-image';
			}
		}

		/*
		|--------------------------------------------------------------------------
		| If you do not want to show the post without image then this class is added
		|--------------------------------------------------------------------------
		*/
		if( ! empty( $this->settings->general_exclude_post_without_image ) && $args['image']->src == $this->settings->general_image_default['thumbnail'] ){
			$class_custom .= ' yzp-not-show-without-image';
		}

		return $class_custom;
	}

	/**
	 * Validate what type of template is the current Yuzo
	 *
	 * @since	6.1.45	2020-01-23	Release
	 * @param 	string 	$template	Template Type
	 * @return 	boolean
	 */
	public function is_template( $template = 'inline' ){
		return $template == $this->options->fieldset_design['panels-design']['template_type'];
	}

	/**
	 * Template structure
	 * This may have different html structure for each different design
	 *
	 * @since	6.0			2019-06-11	Release
	 * @since	6.0.9.4		2019-07-27	- Post counter added
	 * 									- Function added for custom classes
	 * @since	6.0.9.83	2019-10-04	- The new image function is added
	 * 									- The variables are moved for a better interpretation
	 * @since	6.1			2019-12-13	Counter is added in the wrap of each item, to verify how many is shown
	 *
	 * @param 	array 		$args		Template parameter
	 * @return 	string|html
	 */
	public function template( $args = [] ){

		\extract( $args );
		$html = '';

		// Variables for all templates
		$title     = get_the_title();
		$image     = null;
		$image     = $this->get_image( get_the_ID() ); //$this->imagen->get_image( get_the_ID() );  // FUTURE: add filter to be able to order the attachment image
		$image_src = $image->src;
		$image_alt = empty( $image->alt ) ? $title : $image->alt;
		$link      = empty( $_REQUEST['is_preview'] ) ? get_the_permalink() : 'javascript:void(0);' ;
		$ID        = get_the_ID();
		$excerpt   = $this->get_excerpt();

		// ─── Post sequential counter ────────
		$this->post_item_current_index++;

		// ─── Obtained custom class ────────
		$custom_class = $this->item_post_custom_class([
			'image' => $image,
		]);

		/*
		|--------------------------------------------------------------------------
		| TEMPLATES STRUCTURE
		|--------------------------------------------------------------------------
		*/
		$template_type = isset( $this->options->fieldset_design['panels-design']['template_type'] ) && $this->options->fieldset_design['panels-design']['template_type'] ?
						$this->options->fieldset_design['panels-design']['template_type'] : 'grid';
		$design_show_excerpt = isset( $this->options->fieldset_design['panels-design']['design_show_excerpt'] ) && $this->options->fieldset_design['panels-design']['design_show_excerpt'] ?
								$this->options->fieldset_design['panels-design']['design_show_excerpt'] : 0;
		$ytitle = isset( $this->options->fieldset_design['panels-design']['title'] ) && $this->options->fieldset_design['panels-design']['title'] ?
				$this->options->fieldset_design['panels-design']['title'] : '';

		if( ( empty( $template ) || $template == 'grid-default' || $template ) && $template_type != 'inline' ) {

			$html = "<li class='yzp-wrap-item $custom_class' post-id='$ID' data-n='$this->post_item_current_index'>";
				$html .= "<div class='yzp-item' >";
					if( $template != 'default-l' ){
						$html .= "<div class='yzp-item-img' >
								<img alt='$image_alt' src='$image_src' />
							</div>";
					}
					$html .= $this->template_meta();
					$html .= "	<div class='yzp-item-title'>
									<a class='' {$remove_href}='$link' $target $nofollow >$title</a>
								</div>";
					if( ! ( in_array($template, ['yuzo-l']) || (int)$design_show_excerpt == 0 ) ){
						$html .= "<div class='yzp-item-excerpt'>{$excerpt}</div>";
					}
				$html .= "</div>";
			$html .= "</li>";
		}elseif( $template_type == 'inline' ){
			$yzp_title = '<span class="yzp-title-inline">' . strip_tags( $ytitle ) . '</span>';
			$html = "<li class='yzp-wrap-item $custom_class' post-id='$ID'>";
				$html .= "<div class='yzp-item' >";
					$html .= "<div class='yzp-item-title'> ";
						if( $template == 'yuzo-i' ){
							$html .= "<a class='' {$remove_href}='$link' $target $nofollow >{$yzp_title} $title</a>";
						}elseif( $template == 'yuzo-i2' ){
							$html .= "<div class='yzp-item-title-sub-wrap'><a class='' {$remove_href}='$link' $target $nofollow >$title</a></div>";
						}else{
							$html .= "{$yzp_title} <a class='' {$remove_href}='$link' $target $nofollow >$title</a>";
						}
					$html .= "</div>";
					if( in_array( $template, ['yuzo-i','yuzo-i2'] ) ){
						$html .= "	<div class='yzp-item-button'>".( $template == 'yuzo-i' ? '▷' : "<a  href='$link' $target $nofollow>Read</a>" )."</div>";
					}
					if( $template == 'yuzo-i2' ){
						$html .= "<div class='yzp-item-img' >
								<img alt='$image_alt' src='$image_src' />
							</div>";
					}
				$html .= "</div>";
			$html .= "</li>";
		}

		return $html;
	}

} }