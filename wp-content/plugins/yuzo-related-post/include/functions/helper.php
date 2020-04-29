<?php
/**
 * Get the saved settings
 *
 * @since   6.0     2019-04-13 17:36:18     Release
 * @since   6.0.5   2019-07-12 22:16:34     Validation at the moment of requesting a field, if no exists, send null
 *
 * @param   string  $id                     Id of the option to obtain from the database table 'wp_options'
 * @param   string  $field                  If you fill this parameter you will find the value of this name index
 *
 * @return  object|mixed
 */
function yuzo_get_option( $id = 'yuzo-setting', $field = null ){
    if( ! $field ){
        return (object) get_option( $id );
    }else{
        $a = (object) get_option( $id );
        return ! empty( $a->{$field} ) ? $a->{$field} : null;
    }
}

/**
 * Update several fields or only one option
 *
 * @since   6.0.9.83        2019-10-04      Release doc
 *
 * @param   string          $id
 * @param   mixed           $value
 * @param   string          $field
 * @return  object|array
 */
function yuzo_update_option( $id = 'yuzo-setting', $value, $field = null ){
    if( !$field ){
        return update_option($id,$value);
    }else{
        $a = get_option( $id );
        $a[$field] = $value;
        return update_option($id,$a);
    }
}

/**
 * Generate CSS based on selector, properties and values
 *
 * @since   6.0     2019-04-13 17:37:48     Release
 *
 * @param   string  $selector               Name of class/s
 * @param   string  $style                  Name of the property
 * @param   string  $value                  Value of the property
 * @param   string  $prefix                 Main prefix
 * @param   string  $postfix                Postfix by name
 * @param   boolean $echo                   TRUE=print | FALSE=return
 *
 * @return string
 */
function yuzo_generate_css( $selector, $style, $value = null, $prefix='', $postfix='', $echo=true ) {
    $return = '';
    if ( ! empty( $value ) ) {
        $return = sprintf('%s { %s:%s; }',
            $selector,
            $style,
            $prefix.$value.$postfix
        );
    }else{
        $return = sprintf('%s { %s; }',
            $selector,
            $style
        );
    }
    if ( $echo ) {
        echo $return;
    }
    return $return;
}

/**
 * Generates a range of numbers with 3 parameters
 *
 * @since   6.0     2019-04-13 17:38:25     Release
 * @return  array
 */
function yuzo_generate_range( $from = 1, $to = 30, $step = 1 ){
    return array_map( 'strval', range( $from, $to, $step) );
}

/**
 * Get real IP user
 * @since   6.0     2019-04-13 17:39:03     Release
 * @return  string
 */
function yuzo_get_real_ip( $ip_test = '' ){
    if( ! empty( $ip_test ) ) return $ip_test;
    switch(true){
        case (!empty($_SERVER['HTTP_X_REAL_IP'])) : return $_SERVER['HTTP_X_REAL_IP'];
        case (!empty($_SERVER['HTTP_CLIENT_IP'])) : return $_SERVER['HTTP_CLIENT_IP'];
        case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) : return $_SERVER['HTTP_X_FORWARDED_FOR'];
        default : return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Get the views of a post
 *
 * @since   6.0     2019-05-07 00:22:14     Release
 * @since   6.0     2019-06-19 00:06:04     Improve documentation
 *
 * @param   int     $id                     Id of the post that wants to see the views
 * @param   object  $settings               Yuzo general options
 * @param   bool    $without_format         TRUE = send the number without format, FALSE = send it with format
 * @return  void
 */
function yuzo_get_views( $id = 0, $settings = object, $without_format = true ) {

    global $wpdb;

    $table_name = $wpdb->prefix . "yuzoviews";

    $query = "SELECT views FROM {$table_name} WHERE post_id = %d";
    $result = $wpdb->get_var( $wpdb->prepare( $query , $id ) );

    if ( !$result ) {
        return (int)"0";
    }

    if( $without_format == true ){
        return $result;
    }else{
        if( ! empty( $settings ) )
            return yuzo_cut_counter( $result, $settings );
        else
            return $result;
    }
}

/**
 * Obtiene el total de vistas de un post
 *
 * @since   6.1.3   Doc
 *
 * @param   integer $id
 * @return  void
 */
function yuzo_get_post_value( $id = 0 ){

    global $wpdb;

    $table_name = $wpdb->prefix . "yuzoclicks";

    $query  = "SELECT SUM(price_per_click) FROM {$table_name} WHERE post_from = %d";
    $result = $wpdb->get_var( $wpdb->prepare( $query , $id ) );
    return $result;
}

/**
 * Get the click of a post
 *
 * @since   6.0         2019-05-07  Release
 * @since   6.0.9.82    2019-08-28
 *
 * @param   int     $id             Id of the post that wants to see the views
 * @param   bool    $from           TRUE = calculate the clicks from where you received them, FALSE = calculate the clicks to where you received them
 * @return  void
 */
function yuzo_get_clicks( $id = 0, $from = false ) {

    global $wpdb;

    $table_name = $wpdb->prefix . "yuzoclicks";

    if( ! $from ){
        $query = "SELECT count(*) clicks FROM {$table_name} WHERE post_id = %d";
    }else{
        $query = "SELECT count(*) clicks FROM {$table_name} WHERE post_from = %d";
    }
    $result = $wpdb->get_var( $wpdb->prepare( $query , $id ) );

    if ( !$result ) {
        return (int)"0";
    }

    return $result;
}

/**
 * Get the levels ranges of an article imposed by Yuzo
 *
 * @since   6.0.9   2019-07-20 02:20:34     Release
 * @param   string  $scale                  Between low|medium|hight
 * @return  array
 */
function yuzo_get_range_counter_by_posts( $scale = 'low' ){

    $array_range = array();

    if( $scale == 'low' ){
        $array_range['range1']['from'] = 0;$array_range['range1']['to']    = 100;
        $array_range['range2']['from'] = 100;$array_range['range2']['to']  = 500;
        $array_range['range3']['from'] = 500;$array_range['range3']['to']  = 1000;
        $array_range['range4']['from'] = 1000;$array_range['range4']['to'] = 2000;
        $array_range['range5']['from'] = 0;$array_range['range5']['to']    = 2000;
    }elseif( $scale == 'medium' ){
        $array_range['range1']['from'] = 0;$array_range['range1']['to']    = 500;
        $array_range['range2']['from'] = 500;$array_range['range2']['to']  = 1000;
        $array_range['range3']['from'] = 1000;$array_range['range3']['to'] = 5000;
        $array_range['range4']['from'] = 5000;$array_range['range4']['to'] = 10000;
        $array_range['range5']['from'] = 0;$array_range['range5']['to']    = 10000;
    }elseif( $scale == 'hight' ){
        $array_range['range1']['from'] = 0;$array_range['range1']['to']     = 1000;
        $array_range['range2']['from'] = 1000;$array_range['range2']['to']  = 10000;
        $array_range['range3']['from'] = 10000;$array_range['range3']['to'] = 25000;
        $array_range['range4']['from'] = 25000;$array_range['range4']['to'] = 50000;
        $array_range['range5']['from'] = 0;$array_range['range5']['to']     = 50000;
    }

    return $array_range;

}

/**
 * Returns the level of the article
 *
 * @since   6.0.9   2019-07-21 21:12:39     Release
 * @param   int     $views                  View number of the posts
 * @param   string  $scale                  Three scale: low, medium, hight
 * @return  string|int
 */
function yuzo_get_index_level( $views, $scale = 'medium' ){

    $range = yuzo_get_range_counter_by_posts( $scale );

    if( $views < $range['range1']['to'] ){
        return "1";
    }elseif( $views >= $range['range2']['from'] && $views < $range['range2']['to'] ){
        return "2";
    }elseif( $views >= $range['range3']['from'] && $views < $range['range3']['to'] ){
        return "3";
    }elseif( $views >= $range['range4']['from'] && $views < $range['range4']['to'] ){
        return "4";
    }elseif( $views >= $range['range5']['to'] ){
        return "5";
    }

}

/**
 * Funcion que calcula el CPC segun el nivel del articulo
 *
 * @since   6.0.9.8     2019-08-28      Release
 * @param   integer     $level_article  Current level of the item to calculate
 * @return  array
 */
function yuzo_get_price_per_click( $level_article = 1 ){
    $cpc = [ 1 => 0.1, 2 => 0.15, 3 => 0.20, 4 => 0.25, 5 => 0.3 ];
    return $cpc[ (int)$level_article ];
}

/**
 * Valid if you are a permitted user
 *
 * @since   6.0.9.4     2019-07-27 01:17:15     Release
 * @return  void
 */
function yuzo_isUserAllow(){
    if( is_network_admin() ){
        if( ! (defined('LOGGED_IN_COOKIE') && isset($_COOKIE['LOGGED_IN_COOKIE'])) ) return;
        if( ! (defined('SECURE_AUTH_COOKIE') && isset($_COOKIE['SECURE_AUTH_COOKIE'])) ) return;
    }

    if( ! function_exists('wp_get_current_user' ) ) {
        require_once ABSPATH . "wp-includes/pluggable.php" ;
    }
    $allowed_roles = apply_filters( YUZO_ID . '_role_user_allow', array('administrator') );
    $user          = wp_get_current_user();
    $is_user_admin = array_intersect($allowed_roles, $user->roles );

    return ( is_admin() || is_customize_preview() || $is_user_admin );
}

if( ! function_exists('yuzo_get_all_list_instance')  ){
    /**
     * Get all active Yuzo lists
     * @since   6.0     2019-05-18 11:10:46     Release
     */
    function yuzo_get_all_list_instance(){

        global $wpdb, $pagenow;

        $r2 = [];
        if( ! in_array( $pagenow, ['post.php','post-new.php','widgets.php'] ) && ! ( defined('DOING_AJAX') && DOING_AJAX )  ) return;

        // FIXME here you have to add the CPTs selected in the main setting to validate wich show

        $prefix = $wpdb->prefix;

        $sqlbuilder = \sqlQueryBuilder::instance();
        $sqlbuilder->clear();

        // Select table
        $sqlbuilder->table( "{$prefix}posts p" )->idName('p.ID');

        // Inner join metapost
        $sqlbuilder->join( "{$wpdb->prefix}postmeta pm", "p.ID = pm.post_id" );

        // Select cpt: yuzo
        $sqlbuilder->whereIn( 'p.post_type' , 'yuzo' );

        // Show active
        $sqlbuilder->where( 'p.post_status' , 'publish' );
        $sqlbuilder->where( function( $sql ){
            $sql->where( 'pm.meta_key', 'yuzo_related_post_active' );
            $sql->where( 'pm.meta_value', 1 );
        }  );

        $r1 = $wpdb->get_results( $sqlbuilder->select( 'p.ID, p.post_title, pm.meta_value' ), ARRAY_A );

        // ─── Now the configuration of each of the lists ────────
        if( ! empty( $r1 ) ){
            $i = 0;
            foreach( $r1 as $value ){
                $sqlbuilder->clear();
                $sqlbuilder->table("{$wpdb->prefix}postmeta");
                $sqlbuilder->where("post_id", $value['ID']);
                $sqlbuilder->where("meta_key", YUZO_ID );
                //echo $sqlbuilder->select('meta_value as setting');
                $r2[$i] = $wpdb->get_results(
                    $sqlbuilder->select('post_id as ID,meta_value as setting'),
                    ARRAY_A
                );
                $i++;
            }

        }

        return $r2;

    }
}

if( ! function_exists('yuzo_get_list_widget_active')  ){
    /**
     * Get all active Yuzo lists for widget
     * @since   6.0         2019-05-21      Release
     * @since   6.0.9.8     2019-08-28      New message in case there is no active widget Yuzo
     * @since   6.0.9.84    2019-11-25      Now this function is executed as long as you are doing AJAX or you are on the widget page
     */
    function yuzo_get_list_widget_active(){
        global $pagenow;

        if( $pagenow != 'widgets.php' && !(defined( 'DOING_AJAX' ) && DOING_AJAX) ) return;

        $yuzo_widget      = [];
        $list_yuzo_active = yuzo_get_all_list_instance();

        if( ! empty( $list_yuzo_active ) ){
            foreach ($list_yuzo_active as $key => $value) {
                if( ! empty( $value ) ){
                    foreach ($value as $k => $v) {
                        if( ! empty( $v ) ){
                            $id      = $v['ID'];
                            $setting = unserialize( $v['setting'] );
                            $setting = yuzo_fix_var_design( $setting, true );
                            if( isset($setting['fieldset_design']) && $setting['fieldset_design']['panels-design']['where_show'] == 'widget' ){
                                $yuzo_widget[$id] = get_the_title( $id );
                            }
                        }
                    }
                }
            }
        }

        if( empty( $yuzo_widget ) ){
            $yuzo_widget[] = __('❗ There is no active or created Yuzo widget, please check your Yuzo list','yuzo');
        }

        return $yuzo_widget;
    }
}

if( ! function_exists('yuzo_get_custom_type_post')  ){
    /**
     * Get all the custom post type
     *
     * @since   6.0     2019-05-21 22:39:51     Release
     *
     * @param   array   $args
     * @return  array
     */
    function yuzo_get_custom_type_post( $args = [] ){

        // ─── Defaults Vs Custom ────────
        $args_defaults = [
            'only_array_types'                  => false,
            'exclude_menus_attachment_revision' => true,
            'exclude_other'                     => []
        ];
        $args_match = pf_wp_parse_args( $args, $args_defaults );

        // ─── Key to vars ────────
        extract( $args_match );

        $array_post_types = array();
        $post_types       = get_post_types(array(), "objects");

        if( $post_types ){

            $exclude = array();
            if( TRUE === $exclude_menus_attachment_revision ){

                $exclude = array(
                    'attachment',
                    'revision',
                    'nav_menu_item',
                    'custom_css',
                    'customize_changeset',
                    'oembed_cache',
                    'user_request',
                    'wp_block' );

            }elseif( is_array($exclude_other) && $exclude_other ){

                $exclude = $exclude_other;

            }

            foreach ($post_types as $post_type_key => $post_type_value) {

                if( TRUE === in_array( $post_type_value->name, $exclude ) ) continue;

                if($only_array_types == true){

                    $array_post_types[] = $post_type_value->name;

                }else{

                    $array_post_types[$post_type_value->name] = $post_type_value->labels->name;

                }
            }
        }

        return $array_post_types;

    }
}

if( ! function_exists('yuzo_cut_counter')  ){
/**
 * Show 1k instead of 1,000
 * Convert the number in a short format
 * using a separator of thousands or millions
 *
 * @since   6.0     2019-05-27  Release
 * @since   6.1.3   2020-01-02  Function Tabulation
 *
 * @param   int     $number     Number to be cut
 * @param   object  $setting    General plugin configuration
 * @return  string  formatted
 */
function yuzo_cut_counter( $number, $setting = object ){

    if( empty( $setting->general_views_format ) || $setting->general_views_format == 'none' ) {
        $setting->general_views_format = "";
    }

    $number = (int)$number;

    if( empty( $setting->general_views_1k ) ){
        return number_format($number, 0, '', $setting->general_views_format);
    }

    if( $number < 1000 ){
        return $number;
    }elseif($number > 1000000 ){
        return (number_format($number/1000000,1,"$setting->general_views_format","")) . "M";
    }else{
        return (number_format($number/1000,1,".","") + 0) . "k";
    }
}
}

if( ! function_exists('yuzo_disabled_counter')  ){
    /**
     * Validate if the current post is deactivated to count
     *
     * @since   6.0.8   2019-07-16 09:27:05     Release
     * @param   int     $post_id                ID current post
     * @return  bool
     */
    function yuzo_disabled_counter( $post_id, $setting = object ){

        if( ! empty( $setting->general_disabled_counter_view ) &&
            $setting->general_disabled_counter_view ){
            return;
        }else{
            $id_that_is_not_counted = ! empty( $setting->general_disable_counter_only_post ) ?
                explode(",", $setting->general_disable_counter_only_post ) : [];

            return in_array( $post_id, $id_that_is_not_counted ) ? 1 : 0;
        }

    }
}


if( ! function_exists('get_yuzo')  ){
    /**
     * This function is obsolete, it was added to be compatible with Yuzo Pro 0.99
     *
     * @since   6.0.9.7     2019-08-01 23:12:55     Release
     * @param   integer     $id     Yuzo ID to be displayed
     * @deprecated
     * @return  void
     */
    function get_yuzo( $id = 0 ){
        if( empty( $id ) ) return;
        echo do_shortcode( '[yuzo id=' . $id . ' ]' );
    }
}

/**
 * Get the current full URL of the website
 *
 * @since 6.0 2019-04-13 16:19:16 Release
 * @return string
 */
function yuzo_current_location(){

    if (isset($_SERVER['HTTPS']) &&
        ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
        $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {

        $protocol = 'https://';

    } else {

        $protocol = 'http://';

    }

    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Get the place/location of wordpress
 *  * Check if it is: is_home
 *  * Check if it is: is_archive(), is_single(), is_singlular(), etc...
 *
 * @since 6.0 2019-04-13 16:20:02 Release
 *
 * @return array
 */
function yuzo_get_query_flags( $wp_query = null ) {

    if ( !$wp_query )
        $wp_query = $GLOBALS['wp_query'];

    $flags = array();

    foreach ( get_object_vars( $wp_query ) as $key => $val ) {
        if ( 'is_' == substr( $key, 0, 3 ) && $val )
            $flags[] = substr( $key, 3 );
    }

    return ( $flags );

}

/**
 * Convert multidimensional Array into single Array
 *
 * @since   6.0     2019-04-28 19:08:03     Release
 *
 * @param 	array 	$array 		            Array multidimensional
 * @param 	bool 	$key_secuential 	    Does not save the array key
 * @return 	array
 */
function yuzo_array_flatten( $array, $key_secuential = false ) {
    return pf_array_flatten( $array, $key_secuential );
}

/**
 * Function that lets you know if the host under test
 * is in localhost or a real domain.
 *
 * @since   6.0.9.8     2019-08-08      Release
 * @param   array       $whitelist      Ip localhost list
 */
function yuzo_isLocalhost($whitelist = ['127.0.0.1', '::1']) {
    return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
}

if( ! function_exists('pf_global_update_option') ){
    function pf_global_update_option( $id ){
        null;
    }
}

/**
 * This function solves the problems of those with version 6.0.9.7
 * and makes it work normally in function 6.0.9.8.
 * // FUTURE This function should be eliminated in the future.
 *
 * @since   6.0.9.8         2019-08-27      Release
 * @since   6.0.9.83        2019-10-04      - Now return the same object and calculate nothing
 *                                          - The option is deprecated
 * @since   6.0.9.84        2019-11-25      Generic class was created to correct errors, this will soon become obsolete.
 *
 * @deprecated  6.0.9.83
 * @param   object|array    $old_var        variable anterior
 * @param   bool            $convert_array  returns the entire result as an array
 * @return  object
 */
function yuzo_fix_var_design( $old_var, $convert_array = false ){
    //return $old_var;
    $new_var = new stdClass;
    if( is_array( $old_var ) && ! $convert_array ){
        $old_var = is_array( $old_var ) ? (object)$old_var : $old_var;
    }elseif( is_array( $old_var ) && $convert_array ){
        $old_var = (array)$old_var;
    }
    $new_var = $old_var;
    return $new_var;
    if( ! empty( $old_var ) ){
        if( empty($new_var->fieldset_design['panels-design']['title']) ){
            $new_var->fieldset_design['panels-design']['title'] = ! empty( $old_var->title ) ? $old_var->title : '';
        }
        if( empty($new_var->fieldset_design['panels-design']['where_show']) ){
            $new_var->fieldset_design['panels-design']['where_show'] = $old_var->where_show ?: 'content';
        }
        if( empty( $new_var->fieldset_design['panels-design']['content_location'] ) ){
            $new_var->fieldset_design['panels-design']['content_location'] = $old_var->content_location ?: 'below-post-content';
        }
        if( empty( $new_var->fieldset_design['panels-design']['template'] ) ){
            $new_var->fieldset_design['panels-design']['template'] = 'default';
        }
        if( empty( $new_var->fieldset_design['panels-design']['template_type'] ) ){
            $new_var->fieldset_design['panels-design']['template_type'] = $old_var->design_layout ?: 'grid';
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_image_size'] ) ){
            $new_var->fieldset_design['panels-design']['design_image_size'] = $old_var->design_image['design_image_size'] ?: '1:1';
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_screen_mobile']['design_screen_mobile_columns'] ) ){
            $new_var->fieldset_design['panels-design']['design_screen_mobile']['design_screen_mobile_columns'] = $old_var->design_screen_mobile['design_screen_mobile_columns'] ?: 2;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_screen_mobile']['design_screen_mobile_rows'] ) ){
            $new_var->fieldset_design['panels-design']['design_screen_mobile']['design_screen_mobile_rows'] = $old_var->design_screen_mobile['design_screen_mobile_rows'] ?: 2;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_screen_tablet']['design_screen_tablet_columns'] ) ){
            $new_var->fieldset_design['panels-design']['design_screen_tablet']['design_screen_tablet_columns'] = $old_var->design_screen_tablet['design_screen_tablet_columns'] ?: 3;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_screen_tablet']['design_screen_tablet_rows'] ) ){
            $new_var->fieldset_design['panels-design']['design_screen_tablet']['design_screen_tablet_rows'] = $old_var->design_screen_tablet['design_screen_tablet_rows'] ?: 1;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_screen_desktop']['design_screen_desktop_columns'] ) ){
            $new_var->fieldset_design['panels-design']['design_screen_desktop']['design_screen_desktop_columns'] = $old_var->design_screen_desktop['design_screen_desktop_columns'] ?: 3;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_screen_desktop']['design_screen_desktop_rows'] ) ){
            $new_var->fieldset_design['panels-design']['design_screen_desktop']['design_screen_desktop_rows'] = $old_var->design_screen_desktop['design_screen_desktop_rows'] ?: 2;
        }
        if( empty($new_var->fieldset_design['panels-design']['design_text_font_size']['all']) ){
            $new_var->fieldset_design['panels-design']['design_text_font_size']['all'] = null;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_text_font_size']['unit'] ) ){
            $new_var->fieldset_design['panels-design']['design_text_font_size']['unit'] = null;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_text_line_height']['all'] ) ){
            $new_var->fieldset_design['panels-design']['design_text_line_height']['all'] = null;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_text_line_height']['unit'] ) ){
            $new_var->fieldset_design['panels-design']['design_text_line_height']['unit'] = null;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_text_font_weight'] ) ){
            $new_var->fieldset_design['panels-design']['design_text_font_weight'] = null;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_text_color_hover']['color'] ) ){
            $new_var->fieldset_design['panels-design']['design_text_color_hover']['color'] = null;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_text_color_hover']['hover'] ) ){
            $new_var->fieldset_design['panels-design']['design_text_color_hover']['hover'] = null;
        }
        if( empty( $new_var->fieldset_design['panels-design']['content_appende_and_order'][ 'location_priority' ] ) ){
            $new_var->fieldset_design['panels-design']['content_appende_and_order'][ 'location_priority' ] = 10;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_show_excerpt'] ) ){
            $new_var->fieldset_design['panels-design']['design_show_excerpt'] = 0;
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_thumbnail_size'] ) ){
            $new_var->fieldset_design['panels-design']['design_thumbnail_size'] = 'medium';
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_text_length'] ) ){
            $new_var->fieldset_design['panels-design']['design_text_length'] = '80';
        }
        if( empty( $new_var->fieldset_design['panels-design']['design_text_content'] ) ){
            $new_var->fieldset_design['panels-design']['design_text_content'] = 'from_content';
        }
    }
    if( $convert_array ){
        return (array)$new_var;
    }
    return $new_var;
}