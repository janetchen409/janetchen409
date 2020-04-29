<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Get icons from admin ajax
 *
 * @since   1.0     2019-03-05      Release
 * @since   1.5.5   2019-10-28      Sanitize was added in the request (get, post) for security improvement
 */
function pf_get_icons() {

    // Sanitize request ajax
    array_walk_recursive( $sanitize_post = wp_unslash( $_POST ), 'sanitize_text_field' );

    if( ! empty( $sanitize_post['nonce'] ) && wp_verify_nonce( $sanitize_post['nonce'], 'pf_icon_nonce' ) ) {

        ob_start();

        PF::include_plugin_file( 'fields/icon/default-icons.php' );

        $icon_lists = apply_filters( 'pf_field_icon_add_icons', pf_get_default_icons() );

        if( ! empty( $icon_lists ) ) {

            foreach ( $icon_lists as $list ) {

                echo ( count( $icon_lists ) >= 2 ) ? '<div class="pf-icon-title">'. $list['title'] .'</div>' : '';

                foreach ( $list['icons'] as $icon ) {
                    echo '<a class="pf-icon-tooltip" data-pf-icon="'. $icon .'" title="'. $icon .'"><span class="pf-icon pf-selector"><i class="'. $icon .'"></i></span></a>';
                }

            }

        } else {

            echo '<div class="pf-text-error">'. esc_html__( 'No data provided by developer', 'pf' ) .'</div>';

        }

        wp_send_json_success( array( 'success' => true, 'content' => ob_get_clean() ) );

    } else {

        wp_send_json_error( array( 'success' => false, 'error' => esc_html__( 'Error while saving.', 'pf' ), 'debug' => $_REQUEST ) );

    }

}
add_action( 'wp_ajax_pf-get-icons', 'pf_get_icons' );

/**
 * Export
 * @since   1.0     2019-03-05      Release
 * @since   1.5.5   2019-10-28      Sanitize was added in the request (get, post) for security improvement
 */
function pf_export() {

    // Sanitize request ajax
    array_walk_recursive( $sanitize_get = wp_unslash( $_GET ), 'sanitize_text_field' );

    if( ! empty( $sanitize_get['export'] ) && ! empty( $sanitize_get['nonce'] ) && wp_verify_nonce( $sanitize_get['nonce'], 'pf_backup_nonce' ) ) {

        header('Content-Type: application/json');
        header('Content-disposition: attachment; filename=backup-'. gmdate( 'd-m-Y' ) .'.json');
        header('Content-Transfer-Encoding: binary');
        header('Pragma: no-cache');
        header('Expires: 0');

        if( ! empty( $sanitize_get['where'] ) && $sanitize_get['where'] == 'metabox' ){
            $val = get_post_meta( ( isset($sanitize_get['post_id']) ? $sanitize_get['post_id'] : 0 ) , $sanitize_get['export'] );
			echo json_encode( $val[0] );
        }else{
            echo json_encode( get_option( wp_unslash( $sanitize_get['export'] ) ) );
        }

    }

    die();
}
add_action( 'wp_ajax_pf-export', 'pf_export' );

/**
 *
 * Import Ajax
 *
 * @since   1.0     2019-03-05      Release
 * @since   1.4.9   2019-07-12      Now validate to be able to save import export in metabox
 * @since   1.5.5   2019-10-28      Sanitize was added in the request (get, post) for security improvement
 */
function pf_import_ajax() {

    // Sanitize request ajax
    array_walk_recursive( $sanitize_post = wp_unslash( $_POST ), 'sanitize_text_field' );

    if( ! empty( $sanitize_post['import_data'] ) && ! empty( $sanitize_post['unique'] ) && ! empty( $sanitize_post['nonce'] ) && wp_verify_nonce( $sanitize_post['nonce'], 'pf_backup_nonce' ) ) {

        $import_data = json_decode( wp_unslash( trim( $sanitize_post['import_data'] ) ), true );

        if( is_array( $import_data ) ) {

            if( ! empty( $sanitize_post['where'] ) && $sanitize_post['where'] == 'metabox' && ! empty( $sanitize_post['post_id'] ) ){
                update_post_meta( $sanitize_post['post_id'], wp_unslash( $sanitize_post['unique'] ), wp_unslash( $import_data ) );
                wp_send_json_success( array( 'success' => true ) );
            }else{
                update_option( wp_unslash( $sanitize_post['unique'] ), wp_unslash( $import_data ) );
                wp_send_json_success( array( 'success' => true ) );
            }

        }

    }

    wp_send_json_error( array( 'success' => false, 'error' => esc_html__( 'Error while saving.', 'pf' ), 'debug' => $_REQUEST, 'data-formated' => $import_data ) );

}
add_action( 'wp_ajax_pf-import', 'pf_import_ajax' );

/**
 * Reset Ajax
 *
 * @since   1.0     2019-03-05      Release
 * @since   1.5.5   2019-10-28      Sanitize was added in the request (get, post) for security improvement
 */
function pf_reset_ajax() {

    // Sanitize request ajax
    array_walk_recursive( $sanitize_post = wp_unslash( $_POST ), 'sanitize_text_field' );

    if( ! empty( $sanitize_post['unique'] ) && ! empty( $sanitize_post['nonce'] ) && wp_verify_nonce( $sanitize_post['nonce'], 'pf_backup_nonce' ) ) {
        delete_option( wp_unslash( $sanitize_post['unique'] ) );
        wp_send_json_success( array( 'success' => true ) );
    }

    wp_send_json_error( array( 'success' => false, 'error' => esc_html__( 'Error while saving.', 'pf' ), 'debug' => array_map( 'sanitize_text_field', wp_unslash( $_REQUEST ) ) ) );
}
add_action( 'wp_ajax_pf-reset', 'pf_reset_ajax' );

/**
 * Chosen Ajax
 *
 * @since   1.5.3   2019-10-01      Release
 * @since   1.5.5   2019-10-28      Sanitize was added in the request (get, post) for security improvement
 * @since   1.6     2019-12-02      Improvements in prefixes
 * @return  void
 */
if( ! function_exists( 'pf_chosen_ajax' ) ) {
    function pf_chosen_ajax() {

        // Sanitize request ajax
        array_walk_recursive( $sanitize_post = wp_unslash( $_POST ), 'sanitize_text_field' );

        if( ! empty( $sanitize_post['term'] ) && ! empty( $sanitize_post['type'] ) && ! empty( $sanitize_post['nonce'] ) && wp_verify_nonce( $sanitize_post['nonce'], 'pf_chosen_ajax_nonce' ) ) {

            $capability = apply_filters( 'pf_chosen_ajax_capability', 'manage_options' );

            if( current_user_can( $capability ) ) {

                $type       = $sanitize_post['type'];
                $term       = $sanitize_post['term'];
                $query_args = ( ! empty( $sanitize_post['query_args'] ) ) ? $sanitize_post['query_args'] : array();
                $options    = PF_classFields::field_data( $type, $term, $query_args );

                wp_send_json_success( $options );

            } else {
                wp_send_json_error( array( 'error' => esc_html__( 'You do not have required permissions to access.', 'pf' ) ) );
            }

        } else {
            wp_send_json_error( array( 'error' => esc_html__( 'Error: Nonce verification has failed. Please try again.', 'pf' ) ) );
        }

    }
    add_action( 'wp_ajax_pf-chosen', 'pf_chosen_ajax' );
}

/**
 *
 * Set icons for wp dialog
 *
 * @since   1.0     2019-03-05      Release
 * @since   1.5.5   2019-10-28      Sanitize was added in the request (get, post) for security improvement
 *
 */
function pf_set_icons() {
    ?>
    <div id="pf-modal-icon" class="pf-modal pf-modal-icon">
    <div class="pf-modal-table">
        <div class="pf-modal-table-cell">
        <div class="pf-modal-overlay"></div>
        <div class="pf-modal-inner">
            <div class="pf-modal-title">
            <?php esc_html_e( 'Add Icon', 'pf' ); ?>
            <div class="pf-modal-close pf-icon-close"></div>
            </div>
            <div class="pf-modal-header pf-text-center">
            <input type="text" placeholder="<?php esc_html_e( 'Search a Icon...', 'pf' ); ?>" class="pf-icon-search" />
            </div>
            <div class="pf-modal-content">
            <div class="pf-modal-loading"><div class="pf-loading"></div></div>
            <div class="pf-modal-load"></div>
            </div>
        </div>
        </div>
    </div>
    </div>
    <?php
}
add_action( 'admin_footer', 'pf_set_icons', 10, 1);
//add_action( 'customize_controls_print_footer_scripts', 'pf_set_icons' );

/**
 *
 * Search post Ajax
 *
 * @since   1.4.3   2019-05-10      Release
 * @since   1.5.2   2019-08-31      It was added that by default the CP is 'posts'
 * @since   1.5.5   2019-10-28      Sanitize was added in the request (get, post) for security improvement
 * @return  array|json
 */
function pf_search_post_ajax() {

    global $wpdb;

    // Sanitize request ajax
    array_walk_recursive( $sanitize_request = wp_unslash( $_REQUEST ), 'sanitize_text_field' );

    // Term search from input
    $s = $sanitize_request['q'];

    // Validate filter cpt
    $sql_cpt = '';
    if( $sanitize_request['filter'] == 1 ){
        if( ! empty( $sanitize_request['cpt'] ) ){
            if( $sanitize_request['cpt'] == 'all' || $sanitize_request['cpt'] == ''  || $sanitize_request['cpt'] == 'null' || !$sanitize_request['cpt'] ){
                $sql_cpt = " AND p.post_type = 'post' ";
            }else{
                $sql_cpt = " AND p.post_type =  '". $sanitize_request['cpt'] ."' ";
            }
        }
    }else{
        $sql_cpt = " AND p.post_type = 'post' ";
    }


    // Exclude ids that are already selected
    $sql_exclude = '';
    if( ! empty( $sanitize_request['exclude'] ) ){
        $exclude_array = explode(",",$sanitize_request['exclude']) ;
        if( ! empty( $exclude_array ) ){
            $exclude_sanitize = [];
            foreach ($exclude_array as $key => $value) {
                if( $value )
                    $exclude_sanitize[] = $value;
            }

            if( ! empty( $exclude_sanitize ) ){
                $sql_exclude = " AND p.ID NOT IN (". implode(",",$exclude_sanitize) .") ";
            }
        }
    }


    $sql = "SELECT
    p.ID as id, p.post_title as title
    FROM {$wpdb->prefix}posts p
    WHERE
    p.post_status =  'publish'
    $sql_cpt
    $sql_exclude
    AND  post_title like '%{$s}%'
    AND post_title <> ''
    LIMIT 0, 20";

    if( ! empty( $sanitize_request['nonce'] ) && wp_verify_nonce( $sanitize_request['nonce'], 'pf_search_post_nonce' ) ) {

        $r      = $wpdb->get_results( $sql );
        $result = [];

        if( ! empty( $r ) ){
            $image = new \wpImage;
            foreach ($r as $key => $value) {
                $img = $image->get_image(['post_id' => $value->id, 'size' => 'thumbnail' ]);
                $result[$key]['id']    = $value->id;
                $result[$key]['title'] = $value->title;
                $img                   = $img->src;
                $result[$key]['image'] = ! empty( $img ) ? $img : '' ;
            }
        }

        //wp_send_json_success( [ 'a' => $r, 'b' => $sql, 'c' => $sanitize_request ] );
        if( ! empty( $result ) ){
            wp_send_json_success( $result );
        }else{
            wp_send_json_success( [ 'data' => array( 'title' => __('No results','pixel'), 'id' => null  ) ] );
        }

    }

    wp_send_json_error( array( 'success' => false, 'error' => esc_html__( 'Error while search.', 'pf' ), 'debug' => $sanitize_request, 'sql' => $sql ) );

}
add_action( 'wp_ajax_pf-search-post-ajax', 'pf_search_post_ajax' );

/**
 * Taxonomy no hierarchical Ajax
 *
 * @since   1.5.4       2019-10-26      Release
 * @since   1.5.5       2019-10-28      Sanitize was added in the request (get, post) for security improvement
 * @since   1.5.6       2019-11-02      Value 'hide_empty' Now it's FALSE, to show everyone so you don't have linked posts
 *
 * @return  array|json
 */
function pf_taxonomy_ajax(){
    global $wpdb;

    $terms_ids = [];
    // Sanitize request ajax
    array_walk_recursive( $sanitize_request = wp_unslash( $_REQUEST ), 'sanitize_text_field' );

    // Taxonomy search from input
    $tax = $sanitize_request['tax'];
    // Keyword searched
    $s = $sanitize_request['s'];
     // Get all term of taxonomy
    $array_terms = get_terms([
        'taxonomy'   => $tax,
        'hide_empty' => false,
        'name__like' => $s
    ]);

    if(  !empty( $array_terms )  ){
        // Sanitize id term
        $terms_ids = [];
        foreach ($array_terms as $value) {
            $terms_ids[] = [
                'id'   => $value->term_id,
                'name' => $value->name
            ];
        }
        wp_send_json_success( $terms_ids );
    }else{
        wp_send_json_error( array( 'success' => false, 'error' => esc_html__( 'Error while search terms.', 'pf' ), 'debug' => $sanitize_request,) );
    }

}
add_action( 'wp_ajax_pf-taxonomy-ajax', 'pf_taxonomy_ajax' );