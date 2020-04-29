<?php
/**
 * Formatted var_dump() for HTML output
 *
 * @param array $arr
 *
 * @since	1.0	2019-02-21 18:28:46 Release
 * @since   1.2.7 2019-03-27 17:49:09 Funciona funcionalidades
 * @return	string
 */
function pf_do_var_dump( $arr ){
    echo "<pre>";
    var_dump($arr);
    echo "</pre>";
}

/**
 *
 * Getting POST|GET Var
 *
 * @param   mixed $var Value to validate if it is declared POST | GET
 *
 * @since   1.0     2019-02-26  Release
 * @since   1.6.3   2019-12-22  Apply sanitize_text_fields (Temporarily removed)
 * @since   1.6.32  2020-01-02  The sanitize_text_fields function was removed for reasons that
 *                              the metabox nonce was not taking the string after moving to this
 *                              function, this must be checked and corrected.
 *                              // FIXME 👆🏻
 * @return  mixed
 */
function pf_get_var( $var, $default = '' ) {
    if( isset( $_POST[$var] ) ) {
        return ($_POST[$var]);
    }

    if( isset( $_GET[$var] ) ) {
        return ($_GET[$var]);
    }

    return $default;

}

/**
 * Check for wp editor api
 *
 * @since 1.0
 *
 */
function pf_wp_editor_api() {

    global $wp_version;

    return version_compare( $wp_version, '4.8', '>=' );

}

/**
 * Generar aleatorio codigo para id
 *
 * @since   1.4.1       2019-04-21 10:41:35     Release
 * @param   int         $length                 Size of the chain
 * @return  string
 */
function pf_generate_ramdon_code( $length = 10 ){
    $characters = '0123456789abcdefghijklmnopqrs092u3tuvwxyzaskdhfhf9882323ABCDEFGHIJKLMNksadf9044OPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if( ! class_exists('wpImage') ){
    /**
     * Find the image of a post from many locations
     *
     * @author      Lenin Zapata  <leninzapata.com>
     * @copyright   2011 - 2019 (c) Lenin Zapata - wpImage
     * @license     https://opensource.org/licenses/MIT - The MIT License (MIT)
     * @link
     *
     * @since       1.0     2019-06-10 15:25:22     Release
     * @since       1.1     2019-06-11 15:57:52     The constructor with default variables was added
     * @since       1.2     2019-06-11 18:45:48     Modification of the get_image function to get the image from an int parameter
     * @since       1.3     2019-06-12 03:49:28     For the search of attachment post the orderby parameter is added
     *
     */
class wpImage{

    public
    /**
     * Array variable that contains image information
     * @access public
     */
    $wpimage = [],
    /**
     * Place the default image with which this class will execute
     * @access  public
     * @since   1.4.8     2019-06-11 15:55:21   elease
     */
    $default = '',
    /**
     * General arguments of the instance of the class
     * @access  public
     * @since   1.4.8     2019-07-02 00:19:54   Release
     */
    $args = [];

    /**
     * Function that returns an image in 4 instances
     * 1.- Search featured image
     * 2.- Search image inserted in the post
     * 3.- Search imbedded image in the post html
     * 4.- Retorna una imagen por defecto en caso no encontrar otra
     *
     * @since   1.5.4   2019-10-06  - Now in the get_image_post_attachment function you get the ALT
     *                              - Now get_catch_that_image catches the ALT
     *
     * @param   array   $args       Default parameters
     * @return  array
     */

    public function __construct( $args = [] ){

        // Default parameters
        $defaults = array(
			'size'    => 'medium',
			'default' => '',
            'order'   => 'DESC',
            'orderby' => 'post_modified',
            'post_id' => 0,
        );
        $this->args = (object)wp_parse_args( $args, $defaults );

        // Put the default image
        $this->default = $this->args->default;

    }

    /**
     * Show an image of the post
     *
     * @since   1.5.5   2019-11-02  Correction when you have no image uploaded and want to grab one of the text or default
     *
     * @param   array   $args       Default values
     * @return  void
     */
    public function get_image( $args ){

        $post_id = null;
        // ─── Valid if the argument is a single parameter ────────
        if( is_int( $args ) ){
            $post_id = $args;
            $this->args->post_id = $post_id;
        }

        $args = pf_wp_parse_args( $args, (array)$this->args );
        extract( $args );

        // If you have a featured image then it will return
        $this->wpimage = $this->get_featured_image( $size, $post_id );
        if( isset( $this->wpimage['src'] ) && ! empty( $this->wpimage ) ){
			return (object)$this->wpimage;
        }

        // If you have an image inserted in the post, this will return it
        $this->wpimage = $this->get_image_post_attachment( $args );
        if( isset( $this->wpimage['src'] ) && ! empty( $this->wpimage ) ){
			return (object)$this->wpimage;
        }

        $this->wpimage = $this->get_catch_that_image( $post_id );
        if( isset( $this->wpimage['src'] ) && ! empty( $this->wpimage['src'] ) ){
			return (object)$this->wpimage;
        }else{
            return (object)$this->get_image_default();
        }
    }

    protected function get_featured_image(  $size = "medium", $post_id = null ){

        $url = array();
        if ( has_post_thumbnail((int)$post_id) ) { // check if the post has a Post Thumbnail assigned to it.
            $thumb      = wp_get_attachment_image_src( get_post_thumbnail_id((int)$post_id), $size );
            $url['alt'] = self::get_alt_image( (int)$post_id );
            $url['src'] = $thumb['0'];
        }

        return $url;
    }

    protected function get_image_post_attachment( $args = [] ){

        $image = [];
        $args['post_id'] = isset($args['post_id']) ? $args['post_id'] : 0;
        // Perform the query to get the images inserted in a post
        $defaults = array(
			'post_type'   => 'attachment',
            'numberposts' => 5,
            'post_parent' => isset($args['post_id']) ? $args['post_id'] : 0 ,
        );
        $args = wp_parse_args( $args, $defaults );
        $args = apply_filters( 'pf_attachment_query' , $args );
        extract( $args );

        $attachments = get_posts( $args );

        if ( $attachments ) {
            foreach ( $attachments as $attachment ) {
                $_array_img     = wp_get_attachment_image_src( $attachment->ID, $size );
                $image['alt'] = self::get_alt_image( $attachment->ID );
                $image['src'] = $_array_img[0];
                return $image;
            }
        }

        return $image;
    }

    protected function get_catch_that_image( $post_id = 0 ) {

        global $post;

        if( is_object( $post ) && ! is_admin() ){
            $ypost   = $post;
            $post_id = $post->ID;
        }elseif( is_admin() ) {
            $ypost = get_post($post_id);
        }

        if( $post_id ){
            $first_img = array();
            $matches   = array();
            $attr      = [];

            ob_start();
            ob_end_clean();

            // src attribute
            preg_match_all('/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i', $ypost->post_content, $matches);
            array_push($attr, ( is_array($matches) && isset($matches[1][0]) ? $matches[1][0] : null ) );

            // alt attribute
            preg_match_all('/<img.+?alt=[\'"]([^\'"]+)[\'"].*?>/i', $ypost->post_content, $matches);
            array_push($attr, ( is_array($matches) && isset($matches[1][0]) ? $matches[1][0] : null ) );

            $first_img['src'] = isset($attr[0]) ? $attr[0] : '';
            $first_img['alt'] = isset($attr[1]) ? $attr[1] : '';

            return $first_img;
        }else{
            return null;
        }

        return [];

    }

    protected function get_image_default(){
        $image = array();
        $image['alt']='';
        $image['src']= $this->default;
        return $image;
    }

    protected function get_alt_image( $ID = 0 ){
        return get_post_meta( $ID  , '_wp_attachment_image_alt', true );
    }

}
}

/**
 * Ouput css form field Styling
 *
 * @since   6.0         2019-06-15 19:27:06     Release
 * @param   array       $args                   Configuration of the function
 * @return  string|css
 */
function pf_css_output_styling( $args ){

    $output    = '';
    $args = wp_parse_args( $args , [
        'field_value'  => [],
        'class_text'   => '._customtext_',
        'class_box'    => '._custombox_',
        'is_important' => false,
    ]);

    // Vars
    extract( $args );

    // Validate if there is an array above level where it contains the data
    if( isset( $field_value ) ){
        $field_value = $field_value;
    }

    // Valid if personalization data exists
    if( empty($field_value) ) return;

    $important = ( $is_important ) ? '!important' : '';
    if( isset($field_value['all']) ){
        $devices   = ['all' => '[all]'];
    }else{
        $devices = ['desktop'=>'[desktop]','tablet'=>'[tablet]','mobile'=>'[mobile]'];
    }

    foreach ($devices as $_key => $_value) {
        // Common font properties
        $properties = array(
            'color',
            'font-weight',
            'font-style',
            'text-transform',
            //'text-align',
            //'text-decoration',

            //'text-decoration',
            //'font-variant',
        );

        if( $_key == 'mobile' ){
            $output .= "@media (max-width: 480px) {";
        }elseif( $_key == 'tablet' ){
            $output .= "@media (max-width: 768px) {";
        }

        $output .= $class_text . '{';
        foreach( $properties as $property ) {
            $property_k = str_replace( "-", "_", $property);
            if( ! empty($field_value[$_key][$property_k]) && trim($field_value[$_key][$property_k]) != '' ){
                $output .= $property .':'. $field_value[$_key][$property_k] . $important .';';
            }
        }
        $properties = array(
            'font-size',
            'line-height',
            'letter-spacing',
        );
        foreach( $properties as $property ) {
            $property_k = str_replace( "-", "_", $property);
            if( ! empty($field_value[$_key][$property_k]) && trim($field_value[$_key][$property_k]['size']) != '' ){
                $output .= $property .':'. $field_value[$_key][$property_k]['size'] . $field_value[$_key][$property_k]['unit'] . $important .';';
            }
        }

        $output .=  '}';

        $output .= $class_box . '{ border-width: 0; ';
        $output_a = [];
        // Box properties
        if( ! empty( $field_value[$_key] ) ){
            foreach ($field_value[$_key] as $key => $value) {
                if( $key == 'background' ){
                    if( ! empty( $value ) ){
                        $output_a[] = 'background-color:' . $value . $important;
                    }
                }
                if( $key == 'border' ){
                    $v_p = '';
                    foreach (['top','right','bottom','left',] as $_k) {
                        if( ! empty( $value[$_k] ) || $value[$_k] == "0" ){
                            $v_p .="border-".$_k."-width : " . $value[$_k] . $value['unit'] . $important . ';';
                        }
                    }
                    if( $v_p ){
                        $v_p .= ";border-style:solid;";
                    }
                    $output_a[] .= $v_p ;
                }
                if( $key == 'border_radius' ){
                    $v_p = '';
                    foreach (['top','right','bottom','left',] as $_k) {
                        if( ! empty( $value[$_k] ) ){
                            $v_p .=" " . $value[$_k]. $value['unit'];
                        }else{
                            $v_p .=" 0";
                        }
                    }
                    $output_a[] .= 'border-radius:' . $v_p . $important ;
                }
                if( $key == 'padding' ){
                    $v_p = '';
                    foreach (['top','right','bottom','left',] as $_k) {
                        if( ! empty( $value[$_k] )  ){
                            $v_p .="padding-".$_k." : " . $value[$_k] . $value['unit'] . $important . ';';
                        }
                    }
                    if( $v_p ){
                        $output_a[] .=  $v_p;
                    }
                }
                if( $key == 'border_color' ){
                    $v_p = '';
                    if( ! empty( $value ) ){
                        foreach (['top','right','bottom','left',] as $_k) {
                            $v_p .= "border-".$_k."-color : " . $value . $important . ';';
                        }
                    }
                    $output_a[] .= $v_p;
                }
                if( $key == 'margin' ){
                    $v_p = '';
                    foreach (['top','right','bottom','left',] as $_k) {
                        if( ! empty( $value[$_k] )  ){
                            $v_p .="margin-".$_k." : " . $value[$_k] . $value['unit'] . $important . ';';
                        }
                    }
                    if( $v_p ){
                        $output_a[] .=  $v_p;
                    }
                }
            }
        }


        $output .= implode(";",$output_a) . ";}";

        if( in_array( $_key, ['mobile','tablet'] ) ){
            $output .= "}";
        }
    }

    return $output;
}

/**
 * Help the field: taxonomy
 *
 * It helps to obtain the data of the available taxonomy and its terms
 *
 * @since   6.0     2019-06-22 15:36:33     Release
 *
 * @param   array   $object_value_taxonomy
 * @param   int     $type                   1= for hierarchical taxonomies, 2 = for non-hierarchical taxonomies
 * @return  array
 */
function pf_get_taxonomy_and_terms_available( $object_value_taxonomy = [], $type = 1 ){

    $taxonomy_terms = [];
    if( $type == 1 ){
        $taxonomy_all   = [];
        if( ! empty( $object_value_taxonomy ) ){

            foreach ($object_value_taxonomy as $tanoxomy => $terms) {
                if( is_array($terms) && isset($terms['all']) ){
                    $taxonomy_all[] = $tanoxomy;
                }else{
                    $taxonomy_terms[$tanoxomy][] = $terms;
                }

            }

        }
        // 0=All available taxonomies and their terms
        // 1=All the taxonomies that have the option of all
        return [ $taxonomy_all, $taxonomy_terms ];
    }elseif( $type == 2 ){
        $terms_item = [];
        if( ! empty( $object_value_taxonomy ) ){

            foreach( $object_value_taxonomy as $k => $v ){
                $array_term_no_hierarchical[] = explode(",", $v);
            }
            $array_term_no_hierarchical = pf_array_flatten( $array_term_no_hierarchical , true );

            if( is_array($array_term_no_hierarchical) ){

                foreach ($array_term_no_hierarchical as $value) {

                    $split_string = explode ("|", $value);

                    if( ! empty( $split_string[0] ) && ! empty( $split_string[1] ) ){

                        $temp_term = get_term_by( 'name', (string)$split_string[0], (string)$split_string[1] );

                        if( ! empty( $temp_term ) ) { $terms_item[(string)$split_string[1]][] = $temp_term->slug; }

                    }
                }

            }

        }

        return $terms_item;
    }


}

if( ! function_exists('pf_array_flatten') ){
/**
 * Convert multidimensional Array into single Array
 *
 * @since   1.4.8   2019-06-24 13:02:02     Release
 *
 * @param 	array 	$array 		            Array multidimensional
 * @param 	bool 	$key_secuential 	    Does not save the array key
 * @return 	array
 */
function pf_array_flatten( $array, $key_secuential = false ) {
    if (!is_array($array)) {
        return FALSE;
    }
    $result = array();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = array_merge($result, pf_array_flatten($value));
        }
        elseif( is_object($value) ) {
            $value = (array) $value;
            $result = array_merge($result, pf_array_flatten($value));
        } else {
            if( TRUE == $key_secuential ){
                $result[] = $value;
            }else{
                $result[$key] = $value;
            }

        }
    }
    return $result;
}
}

if( ! function_exists('pf_is_user_login_allow') ){
/**
 * Validate a user according to the role he has
 * default is now the 'administrator'
 *
 * @since       6.0         2019-04-13 16:29:57     Release
 * @access      public
 * @static
 *
 * @return boolean
 */
function pf_is_user_login_allow(){
    if( ! function_exists('wp_get_current_user' ) ) {
        require_once ABSPATH . "wp-includes/pluggable.php" ;
    }
    $allowed_roles = apply_filters( 'pf_role_user_allow',array('administrator') );
    $user          = wp_get_current_user();
    $is_user_admin = array_intersect($allowed_roles, $user->roles );
    $is_value      = ( is_admin() || is_customize_preview() || $is_user_admin );
    return $is_value;
}
}


if( ! function_exists('pf_hex2rgba') ){
/**
 * Convert hexdec color string to rgb(a) string
 *
 * @since   1.5.2   2019-08-31  Release
 * @since   1.6.31  2019-12-22  Change to accept RGB | RGBA values
 * @return  string
 */
function pf_hex2rgba($color, $opacity = false) {

    $default = 'rgb(0,0,0)';
    //Return default if no color provided
    if(empty($color))
        return $default;
    //Sanitize $color if "#" is provided
    if ($color[0] == '#' ) {
        $color = substr( $color, 1 );
    }

    //Check if color has 6 or 3 characters and get values
    if( strlen($color) == 8 ){
        $opacity = round( hexdec($color[0].$color[1]) / 255, 2 );
        $hex = array($color[2].$color[3], $color[4].$color[5], $color[6].$color[7]);
    } elseif (strlen($color) == 6) {
        $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
    } elseif ( strlen( $color ) == 3 ) {
        $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
    } else {
        return function() use( $color, $default, $opacity ) {
            // syntax rgba(0,0,0,1);
            $explode = explode( ",", $color );
            if( isset( $explode[3] ) ){
                $explode[3] = $opacity;
                return implode(",",$explode) . ')';
            }
            return ! empty( $color ) ? $color : $default;
        };
    }

    //Convert hexadec to rgb
    $rgb =  array_map('hexdec', $hex);

    //Check if opacity is set(rgba or rgb)
    if($opacity){
        if(abs($opacity) > 1)
            $opacity = 1.0;
        $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
    } else {
        $output = 'rgb('.implode(",",$rgb).')';
    }

    //Return rgb(a) color string
    return $output;
}}