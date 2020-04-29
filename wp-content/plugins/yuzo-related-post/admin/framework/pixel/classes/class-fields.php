<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
if ( ! class_exists( 'PF_classFields' ) ) {
/**
 *
 * Fields Classs
 *
 * @package PF
 * @subpackage PF/Classes
 *
 * @since   1.0     2019-02-25      Release
 * @since   1.2.2   2019-03-20      Removed spaces in the file header
 * @since   1.5.3   2019-09-30      - Missing documentation added
 *                                  - Now get the data through WP_Query
 *
 */
abstract class PF_classFields extends PF_classAbstract {

    /**
     * Contructor inicial de Field
     * @since   1.0     2019-02-25  Release
     *
     */
    public function __construct( $field = array(), $value = '', $unique = '', $where = '', $parent = '' ) {
        $this->field  = $field;
        $this->value  = $value;
        $this->unique = $unique;
        $this->where  = $where;
        $this->parent = $parent;
    }

    /**
     * Get the name of the set field
     *
     * @since   1.0         2019-02-25      Release
     * @param   string      $nested_name    Variable name
     * @return  void
     */
    public function field_name( $nested_name = '' ) {

        $field_id   = ( ! empty( $this->field['id'] ) ) ? $this->field['id'] : '';
        $unique_id  = ( ! empty( $this->unique ) ) ? $this->unique .'['. $field_id .']' : $field_id;
        $field_name = ( ! empty( $this->field['name'] ) ) ? $this->field['name'] : $unique_id;
        $tag_prefix = ( ! empty( $this->field['tag_prefix'] ) ) ? $this->field['tag_prefix'] : '';

        if( ! empty( $tag_prefix ) ) {
            $nested_name = str_replace( '[', '['. $tag_prefix, $nested_name );
        }

        return $field_name . $nested_name;
    }

    /**
     * Additional attributes that go in the field
     *
     * @since   1.0     2019-02-25      Release
     * @since   1.6     2019-12-02      Function Tabulation
     *
     * @param   array   $custom_atts    Array-like attributes
     * @return  void
     */
    public function field_attributes( $custom_atts = array() ) {

        $field_id   = ( ! empty( $this->field['id'] ) ) ? $this->field['id'] : '';
        $attributes = ( ! empty( $this->field['attributes'] ) ) ? $this->field['attributes'] : array();

        if( ! empty( $field_id ) ) {
            $attributes['data-depend-id'] = $field_id;
        }

        if( ! empty( $this->field['placeholder'] ) ) {
            $attributes['placeholder'] = $this->field['placeholder'];
        }

        $attributes = wp_parse_args( $attributes, $custom_atts );

        $atts = '';

        if( ! empty( $attributes ) ) {
            foreach( $attributes as $key => $value ) {
                if( $value === 'only-key' ) {
                    $atts .= ' '. $key;
                } else {
                    $atts .= ' '. $key . '="'. $value .'"';
                }
            }
        }

        return $atts;
    }

    /**
     * Add the tooltip attribute for fields
     *
     * @since   1.5.1           2019-07-16 09:54:57 Release
     * @param   array|string    $attr               Tooltip parameter, even incomplete
     * @return  string
     */
    public function field_tooltip( $attr = null ){

        if( is_string($attr) ){
            return ' data-tooltip = "' . $attr . '" ';
        }elseif( is_array( $attr ) ){
            // FUTURE here you must put the color attribute, position among others
        }

        return '';

    }

    public function field_before() {
        return ( ! empty( $this->field['before'] ) ) ? $this->field['before'] : '';
    }

    /**
     * Add data after the field
     *
     * @since   1.2.3   2019-03-20 23:22:50     Improve: add class 'pf-after'
     * @since   1.4.6   2019-06-12 18:34:12     Change of position of variables
     * @return void
     */
    public function field_after() {

        $output = ( ! empty( $this->field['after'] ) ) ? '<span class="pf-after">' . $this->field['after'] . '</span>' : '';
        $output .= ( ! empty( $this->field['desc'] ) ) ? '<p class="pf-text-desc">'. $this->field['desc'] .'</p>' : '';
        $output .= ( ! empty( $this->field['help'] ) ) ? '<span class="pf-help"><span class="pf-help-text">'. $this->field['help'] .'</span><span class="fa fa-question-circle"></span></span>' : '';
        $output .= ( ! empty( $this->field['_error'] ) ) ? '<p class="pf-text-error">'. $this->field['_error'] .'</p>' : '';

        return $output;

    }

    /**
     * JS Arguments for jQuery plugins
     *
     * @since   1.4.8   2019-07-11 01:08:49     Release
     * @return  string|html
     */
    public function field_attributes_js(){

        $out      = '';
        $out_html = '';
        $property_A = [];
        if( ! empty( $this->field['attributes_js'] ) ){
            $out      .= '{';
            foreach( $this->field['attributes_js'] as $property => $value ){
                $property_A[] = " $property : $value ";
            }
            $out      .= implode(",",$property_A) . '}';
            $out_html = " data-attributes-js = '".($out)."'";
        }

        return $out_html;

    }

    /**
     * Field callback functions
     *
     * @since   1.5     2019-07-14  Documented
     * @since   1.5.3   2019-09-30  Now get the data through WP_Query
     * @since   1.5.4   2019-10-26  - Now you can get taxonomies
     *                              - Now you can get status_post
     * @since   1.5.8   2019-11-25  Added the list of menu locations that has a Theme
     * @since   1.6     2019-12-02  Tabulation Improvement
     *
     * @param       string  $type
     * @return      array|mixed
     */
    public static function field_data( $type = '', $term = false, $query_args = array()  ) {

        $options      = array();
        $array_search = false;

        // sanitize type name
        if( in_array( $type, array( 'page', 'pages' ) ) ) {
            $option = 'page';
        } else if( in_array( $type, array( 'post', 'posts' ) ) ) {
            $option = 'post';
        } else if( in_array( $type, array( 'category', 'categories' ) ) ) {
            $option = 'category';
        } else if( in_array( $type, array( 'tag', 'tags' ) ) ) {
            $option = 'post_tag';
        } else if( in_array( $type, array( 'menu', 'menus' ) ) ) {
            $option = 'nav_menu';
        } else {
            $option  = '';
        }

        switch( $type ) {

            case 'page':
            case 'pages':
            case 'post':
            case 'posts':

                // term query required for ajax select
                if( ! empty( $term ) ) {

                    $query             = new WP_Query( wp_parse_args( $query_args, array(
                        's'              => $term,
                        'post_type'      => $option,
                        'post_status'    => 'publish',
                        'posts_per_page' => 25,
                    ) ) );

                } else {

                    $query          = new WP_Query( wp_parse_args( $query_args, array(
                        'post_type'   => $option,
                        'post_status' => 'publish',
                    ) ) );

                }

                if( ! is_wp_error( $query ) && ! empty( $query->posts ) ) {
                    foreach( $query->posts as $item ) {
                        $options[$item->ID] = $item->post_title;
                    }
                }

            break;

            case 'category':
            case 'categories':
            case 'tag':
            case 'tags':
            case 'menu':
            case 'menus':

                if( ! empty( $term ) ) {

                    $query         = new WP_Term_Query( wp_parse_args( $query_args, array(
                        'search'     => $term,
                        'taxonomy'   => $option,
                        'hide_empty' => false,
                        'number'     => 25,
                    ) ) );

                } else {

                    $query         = new WP_Term_Query( wp_parse_args( $query_args, array(
                        'taxonomy'   => $option,
                        'hide_empty' => false,
                    ) ) );

                }

                if( ! is_wp_error( $query ) && ! empty( $query->terms ) ) {
                    foreach( $query->terms as $item ) {
                        $options[$item->term_id] = $item->name;
                    }
                }

            break;

            case 'user':
            case 'users':

                if( ! empty( $term ) ) {

                    $query    = new WP_User_Query( array(
                        'search'  => '*'. $term .'*',
                        'number'  => 25,
                        'orderby' => 'title',
                        'order'   => 'ASC',
                        'fields'  => array( 'display_name', 'ID' )
                    ) );

                } else {

                    $query = new WP_User_Query( array( 'fields' => array( 'display_name', 'ID' ) ) );

                }

                if( ! is_wp_error( $query ) && ! empty( $query->get_results() ) ) {
                    foreach( $query->get_results() as $item ) {
                    $options[$item->ID] = $item->display_name;
                    }
                }

            break;

            case 'sidebar':
            case 'sidebars':

                global $wp_registered_sidebars;

                if( ! empty( $wp_registered_sidebars ) ) {
                    foreach( $wp_registered_sidebars as $sidebar ) {
                    $options[$sidebar['id']] = $sidebar['name'];
                    }
                }

                $array_search = true;

            break;

            case 'role':
            case 'roles':

                global $wp_roles;

                if( ! empty( $wp_roles ) ) {
                    if( ! empty( $wp_roles->roles ) ) {
                    foreach( $wp_roles->roles as $role_key => $role_value ) {
                        $options[$role_key] = $role_value['name'];
                    }
                    }
                }

                $array_search = true;

            break;

            case 'post_type':
            case 'post_types':

                $post_types = get_post_types( array(
                    'show_in_nav_menus' => true
                ), "objects" );
                $exclude = array(
                    'attachment',
                    'revision',
                    'nav_menu_item',
                    'custom_css',
                    'customize_changeset',
                    'oembed_cache',
                    'user_request',
                    'wp_block',
                    'yuzo'
                );

                if( ! empty( $query_args['exclude'] ) && is_array( $query_args['exclude'] ) ){
                    $exclude = array_merge($exclude, $query_args['exclude']);
                }

                if ( ! is_wp_error( $post_types ) && ! empty( $post_types ) ) {
                    foreach ( $post_types as $post_type ) {
                        if( TRUE === in_array( $post_type->name, $exclude ) ) continue;
                        $options[$post_type->name] = ucfirst($post_type->labels->name);
                    }
                }

            break;

            case 'taxonomies':

                $post_types = get_post_types( array(), "objects" );
                $out = [];

                $exclude = ['attachment','revision','wp_block','nav_menu_item','custom_css','oembed_cache','customize_changeset','user_request'];
                if(  !empty( $post_types )  ){
                    foreach($post_types as $key => $value){
                        if( TRUE == in_array( $value->name, $exclude ) ) continue;
                        $out[$value->name] = $value->labels->name;
                    }
                }
                if(  !empty( $out )  ){
                    $exclude =  ! empty( $query_args['exclude'] ) ? $query_args['exclude'] : [];
                    foreach ($out as $cpt_key => $cpt_value) {
                        $taxonomies = get_object_taxonomies( $cpt_key, 'objects' );
                        if( is_array($taxonomies) ){
                            foreach ($taxonomies as $key => $value) {
                                if( ! in_array( $value->name, $exclude )  ){
                                    $options[$key] = $value->labels->name;
                                }
                            }
                        }
                    }
                }

                break;

            case 'status_post':

                $options =  array_reverse( get_post_statuses() );

                break;

            case 'menus_register':

                $options = get_registered_nav_menus();

                break;

            default:
                if( function_exists( $type ) ) {
                    if( ! empty( $term ) ) {
                        $options = call_user_func( $type, $query_args );
                    } else {
                        $options = call_user_func( $type, $term, $query_args );
                    }
                }
            break;

        }

        // Array search by "term"
        if( ! empty( $term ) && ! empty( $options ) && ! empty( $array_search ) ) {
            $options = preg_grep( '/'. $term .'/i', $options );
        }

        // Make multidimensional array for ajax search
        if( ! empty( $term ) && ! empty( $options ) ) {
            $arr = array();
            foreach( $options as $option_key => $option_value ) {
            $arr[] = array( 'value' => $option_key, 'text' => $option_value );
            }
            $options = $arr;
        }

        return $options;

    }

    /**
     * Get the titles of the results of a query
     *
     * @since   1.5.3   2019-09-30      Release
     * @since   1.6     2019-12-02      Improvements in 'post_type' and default
     *
     * @param   string  $type           Search Type
     * @param   mixed   $values         Values
     * @return  void
     */
    public function field_wp_query_data_title( $type, $values ) {

        $options = array();

        if( ! empty( $values ) && is_array( $values ) ) {

            foreach( $values as $value ) {

                switch( $type ) {

                case 'post':
                case 'posts':
                case 'page':
                case 'pages':

                    $title = get_the_title( $value );

                    if( ! is_wp_error( $title ) && ! empty( $title ) ) {
                        $options[$value] = $title;
                    }

                break;

                case 'category':
                case 'categories':
                case 'tag':
                case 'tags':
                case 'menu':
                case 'menus':

                    $term = get_term( $value );

                    if( ! is_wp_error( $term ) && ! empty( $term ) ) {
                        $options[$value] = $term->name;
                    }

                break;

                case 'user':
                case 'users':

                    $user = get_user_by( 'id', $value );

                    if( ! is_wp_error( $user ) && ! empty( $user ) ) {
                        $options[$value] = $user->display_name;
                    }

                break;

                case 'sidebar':
                case 'sidebars':

                    global $wp_registered_sidebars;

                    if( ! empty( $wp_registered_sidebars[$value] ) ) {
                        $options[$value] = $wp_registered_sidebars[$value]['name'];
                    }

                break;

                case 'role':
                case 'roles':

                    global $wp_roles;

                    if( ! empty( $wp_roles ) && ! empty( $wp_roles->roles ) && ! empty( $wp_roles->roles[$value] ) ) {
                        $options[$value] = $wp_roles->roles[$value]['name'];
                    }

                break;

                case 'post_type':
                case 'post_types':

                    $post_types = get_post_types( array( 'show_in_nav_menus' => true ) );

                    if( ! is_wp_error( $post_types ) && ! empty( $post_types ) && ! empty( $post_types[$value] ) ) {
                    $options[$value] = ucfirst( $value );
                    }

                break;

                default:

                    if( function_exists( $type .'_title' ) ) {
                        $options[$value] = call_user_func( $type .'_title', $value );
                    } else {
                        $options[$value] = ucfirst( $value );
                    }

                break;

                }

            }

        }

        return $options;

    }

} }