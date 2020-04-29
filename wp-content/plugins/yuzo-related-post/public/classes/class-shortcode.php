<?php
/**
 * Shortcodes for Yuzo
 *
 * @since       6.0     2019-04-28 19:21:25     Release
 * @package     Yuzo
 * @subpackage  Yuzo/Public
 */
// ───────────────────────────
namespace YUZO\Publi;
use YUZO\Core\YUZO_Core as yuzo;
// ───────────────────────────
if( ! class_exists( 'Yuzo_Shortcode' ) ){
    /**
     * Class that handles the shortcode of plugin
     * All the related post created has code of shortcode
     * to be able to place them in the place that is required.
     */
	class Yuzo_Shortcode{

		/**
		 * Show a shortcode by an ID, in case you don't have it then you will look for the first asset
		 *
		 * @since	6.1.35	2020-01-08	Doc
		 * @since	6.1.45	2020-01-24	Load again load core to avoid any possible error
		 *
		 * @param 	array 	$atts		Shortcode Attributes
		 * @param 	string 	$content	Content
		 * @param 	string 	$tag		Tags shortcode
		 * @return 	void
		 */
		public function show_by_id_list( $atts = [], $content = null, $tag = '' ){

			yuzo::instance()->logs->group( "Public ⇨ Shortcode: Use shortcode yuzo" );

			// Load lib vendor
			yuzo::instance()->load_files_core();

			// normalize attribute keys, lowercase
			$atts = array_change_key_case((array)$atts, CASE_LOWER);

		    // override default attributes with user attributes
            $ok_atts = shortcode_atts([
				'id' => 0,
			], $atts, $tag);

			if( empty($ok_atts['id']) || (int)$ok_atts['id'] == 0 ){
				// Check if there is an active Yuzo shortcode, the first one that I will then show that
				// Get all Yuzo active
				$yuzo_actives = yuzo::instance()->public->related_options->get_list_id_active();
				if( ! empty( $yuzo_actives ) && is_array($yuzo_actives) ){
					foreach ($yuzo_actives as $key => $value) {
						$list_options = yuzo::instance()
							->public
							->related_options
							->get_list_settings_related_post( (int)$value );
						if( isset($list_options[0]) ){
							// Get list current options for related post
							$options =  (object)unserialize($list_options[0]['setting']);
							if( isset($options->fieldset_design['panels-design']['where_show']) && $options->fieldset_design['panels-design']['where_show'] == 'shortcode' ){
								$ok_atts['id'] = $value;
								break;
							}
						}
					}
				}
				if( empty($ok_atts['id']) || (int)$ok_atts['id'] == 0 ){
					yuzo::instance()->logs->groupEnd();
					return;
				}
			}

			$list_options = yuzo::instance()
				->public
				->related_options
				->get_list_settings_related_post( (int)$ok_atts['id'] );

			if( isset($list_options[0]) ){
				// Get list current options for related post
				$options =  (object)unserialize($list_options[0]['setting']);

				// Valid if it shows or not the related post
				if( yuzo::instance()->public->related_display->display_related_post( $options ) ){
					return yuzo::instance()->public->related_algorithm->get_result_yuzo_post( $options );
				}

			}

			yuzo::instance()->logs->groupEnd();

		}

		/**
		 * Shortcode de vistas de Yuzo
		 *
		 * @since	6.0.2		2019-07-12 06:25:44		Release
		 * @return 	string|int
		 */
		public function yuzo_shortcodes(){
			add_shortcode( 'yuzo', array( $this, 'show_by_id_list' ) );
			add_shortcode( 'yuzo_views', function(  $atts, $content = null ){
				yuzo::instance()->logs->notice("Public ⇨ Shortcode: Use shortcode yuzo_views");
				global $post;
				extract(shortcode_atts(array(
					'id'    => $post->ID
				), $atts));
				$s = yuzo::instance()->settings;
				return yuzo_get_views($id,$s,false);
			} );
        }


	}
}