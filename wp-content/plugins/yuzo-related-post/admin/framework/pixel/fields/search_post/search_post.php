<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly
/**
 *
 * Field Search Post
 *
 * @since   1.4.3   2019-05-09 18:50:26     Release
 * @since   1.4.6   2019-06-11 00:40:42     Add object to get the image
 *
 */
if( ! class_exists( 'PF_Field_search_post' ) ) {
class PF_Field_search_post extends PF_classFields {

    /**
     * Variable object to obtain the images
     *
     * @var object
     */
    private $objImage = null;

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
    }

    /**
     * Render field
     *
     * @since   1.4.6   2019-06-12  The functionality of the post searches was improved
     * @since   1.5.2   2019-08-31  -Fixed a bad close of select options
     *                              -Validation of posts type was added, as default argument.
     * @since   1.5.6   2019-11-02  The list of posts were not showing, now they do
     * @return  string
     */
    public function render() {

        // Default image
        $image_default = '';
        if( isset(PF::$args_instanced[$this->unique]['metaboxes']->args['image_default']) ){
            $image_default = PF::$args_instanced[$this->unique]['metaboxes']->args['image_default'];
        }

        $args = wp_parse_args( $this->field, array(

            'enable_sortable'   => false,
            'enable_show_image' => false,
            'enable_show_filter'=> false,

            'image_default'     => $image_default,
            'post_type'         => [],

        ) );

        // Get image default
        $path_imagen_default = $args['image_default'];

        echo $this->field_before();

        echo '<div class="pf--search-post-wrap">';

        echo '<div class="pf-search-post-inputs-wrap">';

            echo '<div class="pf-search-post-custom-result">';
            echo '<input type="text" class="pf-search-input" />';
            echo '<div class="pf-search-post-custom-result-wrap"></div>';
            echo '</div>';

            if( $args['enable_show_filter'] ){
                echo '<select class="pf-search-select">';
                echo '<option value="">'. __('Filter (custom post type)','pixel') .'</option>';
                $options = $this->get_custom_type_post( $args );
                if( ! empty( $options ) ){
                    $a = 0;
                    foreach ($options as $key => $value) {
                        if( ! empty( $key ) && ! empty( $value ) ){
                            echo '<option value="'. $key .'">'. $value .'</option>';
                        }
                    }
                }
                echo '</select>';
            }

            echo '<a href="#" class="pf-search-post-button button button-primary"><i class="fa fa-plus-circle"></i></a>';
            echo '<input type="hidden" class="pf-search-input-value" />';
            echo '<input type="hidden" class="pf-search-input-data" />';
            echo '<input type="hidden" class="pf-search-post-values" name="'. $this->field_name() .'" value="'. $this->value .'" />';
            echo '<input type="hidden" class="pf-search-post-image-default" value="'. $path_imagen_default .'" />';

            echo '<input type="hidden" class="pf-search-post-is-sortable" value="'. ( $args['enable_sortable'] ? 1 : 0 ) .'" />';
            echo '<input type="hidden" class="pf-search-post-is-image" value="'. ( $args['enable_show_image'] ? 1 : 0 ) .'" />';
            echo '<input type="hidden" class="pf-search-post-is-filter" value="'. ( $args['enable_show_filter'] ? 1 : 0 ) .'" />';
        echo '</div>';

        echo '<ul class="pf-search-post-results">';

        // Sanitize value
        $this->value = implode( ',', array_filter( explode(',', $this->value), function ($k){ return (int)($k); } ) );
        if( ! empty( $posts = $this->get_posts( $this->value ) ) ){

            //echo '<ul>';

            foreach( $posts as $v ){
                $v = (object)$v;
                echo '<li data-id="'. $v->id .'" class="pf-search-post-item-index pf-search-post-item-id-'. $v->id .'" >';

                if( $args['enable_show_image'] ){
                    echo '<div class="pf-search-post-item-index-img">';
                        echo '<img src="'. ( empty( $v->image ) ? $path_imagen_default : $v->image ) .'" >';
                    echo '</div>';
                }

                    echo '<div class="pf-autocomplete-item-content">';
                        echo '<p>'. $v->title .'</p>';
                    echo '</div>';

                    echo '<div class="pf-search-post-helper" >';

                        if( $args['enable_sortable'] ){
                            echo '<i class="pf-sp-sort fa fa-arrows ui-sortable-handle"></i>';
                        }

                        echo '<i data-id="'. $v->id .'" class="pf-sp-remove fa fa-times"></i>';
                    echo '</div>';

                echo '</li>';
            }
            //echo '</ul>';
        }

        echo '</ul>';
        echo '</div>';
        echo '<input type="hidden" value="'. wp_create_nonce( 'pf_search_post_nonce' ) .'" class="pf-search-post-nonce" />';
        echo '<div class="clear"></div>';
        echo '<style>.pf-field-search_post .ui-autocomplete-loading {
background:url('. admin_url() .'images/spinner.gif) no-repeat right center !important;
}</style>';

        echo $this->field_after();

    }

    /**
     * Gets the custom post type of the system
     *
     * @since   6.0     2019-05-12  Release
     * @since   1.5.2   2019-08-31  The same CP was added as Key but with lower case letters, before it was not.
     *
     * @param   bool    $only_array_types       TRUE = array only key, FALSE = array key|name
     * @param   bool    $exclude_menus_attachment_revision      TRUE = return array normal object without 'attachment',
     *                                                          'revision', 'nav_menu_item'
     * @param   array   $exclude_other          add in array the element that not show in post_type, ei: movie
     * @return  array   $array_post_types
     */
    private function get_custom_type_post( $args = [] ){

        // ─── Valid if there is post type to add ────────
        if( !empty( $args['post_type'] ) ){
            $arr_return = [];
            foreach ($args['post_type'] as $value) {
                $arr_return[$value] = ucfirst($value);
            }
            return $arr_return;
        }

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
        $post_types       = get_post_types( array(), "objects" );

        if( $post_types ){

            $exclude = array();
            if( TRUE === $exclude_menus_attachment_revision ){

                $exclude = array( 'attachment', 'revision', 'nav_menu_item' );

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

    /**
     * Get posts
     *
     * @since   1.5.8   2019-11-25      - Release doc
     *                                  - The database table prefix was added
     * @return  array
     */
    private function get_posts(){

        global $wpdb;

        $ids            = $this->value;
        $ids_array      = explode( "," , $ids );
        $ids_sanitize   = [];
        $this->objImage = empty( $this->objImage ) ? new \wpImage : $this->objImage;

        if( ! empty( $ids_array ) ){
            foreach ($ids_array as $key => $value) {
                if( ! empty( $value ) ){
                    $ids_sanitize[] = $value;
                }
            }
        }

        if( ! empty( $ids_sanitize ) ){

            $sql = "SELECT
            p.ID as id, p.post_title as title
            FROM {$wpdb->prefix}posts p
            WHERE
            ID in (". implode( ",", $ids_sanitize ) .")
            ORDER BY FIELD(p.ID, ". implode( ",", $ids_sanitize ) .")";
            $r = $wpdb->get_results( $sql );

            $result = [];

            if( ! empty( $r ) ){
                foreach ($r as $key => $value) {
                    $result[$key]['id']    = $value->id;
                    $result[$key]['title'] = $value->title;
                    $result[$key]['image'] = ! empty( $img = $this->objImage->get_image( ['post_id' => $value->id] ) ) ? $img->src : '' ;
                }
            }

            return $result;

        }

        return null;

    }

}
}
