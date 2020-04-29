<?php
/**
 * Save the record with a click
 *
 * @since   6.0         2019-05-03  Release
 * @since   6.0.9       2019-07-21  Calculation of estimated price of the post
 * @since   6.0.9.8     2019-08-28  Nueva funcionalidad para calcular el CPC y guardar este dato.
 * @return  json|array
 */
function yuzo_save_click(){

    // ─── Sanitize request ajax ────────
    array_walk_recursive( $sanitize_request = wp_unslash( $_REQUEST ), 'sanitize_text_field' );

    // ─── Verifies Nonce for security  ────────
    if ( empty( $sanitize_request['nonce'] ) || ! wp_verify_nonce( $sanitize_request['nonce'], 'yuzo-click' ) )  return;

    global $wpdb;

    // ─── We include the geolocation class ────────
    require_once YUZO_PATH . 'public/classes/class-geo.php';

    // ─── We get all GEO data ────────
    $ip        = yuzo_get_real_ip();
    $geoplugin = new geoPlugin( $ip );

    // ─── We obtain the data transported from the page ────────
    $post_id         = (int)$sanitize_request['post_id'];
    $date_click      = date( "Y-m-d H:i:s" );
    $timestamp_click = time();
    $ip              = $ip;
    $la              = $geoplugin->latitude;
    $lo              = $geoplugin->longitude;
    $country         = $geoplugin->countryName;
    $country_code    = $geoplugin->countryCode;
    $region          = $geoplugin->regionName;
    $city            = $geoplugin->city;
    $device          = $sanitize_request['device'];
    $url             = $sanitize_request['url'];
    $where_is        = $sanitize_request['where_is'];
    $browser_details = $sanitize_request['browser_details'];
    $others          = ! empty( $sanitize_request['others'] ) ? json_decode( stripslashes( $sanitize_request['others'] ), true ): null;  // ◄ return array from JSON.stringify javascript

    // ─── Process the 'other' field ────────
    $type                  = ! empty( $others['type'] ) ? $others['type'] : 'c';
    $post_id_click         = ! empty( $others['post_id_click'] ) ? $others['post_id_click'] : 0;
    $yuzo_id               = (int)$others['yuzo_id'];
    //$level_article         = $others['level_article']; // settings
    $current_article_level = $others['current_article_level'];

    // ─── Calculation of CPC ────────
    $price_per_click = yuzo_get_price_per_click( $current_article_level );

    // ─── Insert the click that the user made ────────
    $sql = "INSERT INTO {$wpdb->prefix}yuzoclicks
        (post_id,
        date_click,
        timestamp_click,
        ip,
        la,
        lo,
        country,
        country_code,
        region,
        city,
        device,
        url,
        where_is,
        browser_details,
        type_click,
        post_from,
        yuzo_list_id,
        price_per_click,
        level_click)
        values(
        $post_id_click,
        '$date_click',
        $timestamp_click,
        '$ip',
        '$la',
        '$lo',
        '$country',
        '$country_code',
        '$region',
        '$city',
        '$device',
        '$url',
        '$where_is',
        '$browser_details',
        '$type',
        $post_id,
        $yuzo_id,
        '$price_per_click',
        $current_article_level
    )";
    // OPTIMIZE: here the prepare does not work, I still do not understand why?
    //values( %i, %s, %i, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    $r = $wpdb->query( $sql );
/*
    $last_click_id = 0;
    if( !empty( $r ) ){
        $last_click_id = $wpdb->insert_id;
    }

    // ─── Calculation of estimated price of the post ────────
    $sql_views =  $wpdb->get_row( "SELECT views FROM {$wpdb->prefix}yuzoviews WHERE post_id = $post_id_click" );
    if( !empty( $sql_views->views ) ){
        $level = yuzo_get_index_level( $sql_views->views, $level_article );
        $price_per_click = 0.10;
        if( $level == 1 ){ $price_per_click = 0.1; }
        if( $level == 2 ){ $price_per_click = 0.15; }
        if( $level == 3 ){ $price_per_click = 0.2; }
        if( $level == 4 ){ $price_per_click = 0.25; }
        if( $level == 5 ){ $price_per_click = 0.3; }

        //$u = $wpdb->query( "UPDATE {$wpdb->prefix}yuzoclicks set price_per_click = '$price_per_click' WHERE ID = $last_click_id " );
    } */

    /*
    |--------------------------------------------------------------------------
    | Returns the data to affirm that the insert was made
    |--------------------------------------------------------------------------
    */
    wp_send_json_success(
        array(
            'result'    => ( $r ? true : false ),
            'error_sql' => YUZO_MODE_DEV ? yuzo_sql_print_error()  : null,
            'sql'       => YUZO_MODE_DEV ? $sql : null,
            'request'   => YUZO_MODE_DEV ? $_REQUEST : null,
            'others'    => YUZO_MODE_DEV ? $others : null,
            'output'    => $geoplugin,
        )
    );
}

/**
 * Save the record view
 *
 * @since   6.0     2019-05-03 02:02:02     Release
 * @since   6.0.8.5 2019-07-19 19:09:34     Change name var count to '$timestamp_views'
 * @since   6.0.9   2019-07-21 21:03:23     Improvements when counting a real visit
 * @since   6.0.9.2 2019-07-24 07:40:46     Adjustments were made to the counter algorithm
 * @since   6.0.9.8 2019-08-28              Ajustes minimos
 * @return  json|array
 */
function yuzo_save_view(){

    // ─── Variable that indicates if something happened and I don't count ────────
    $return = 1;

    // ─── Sanitize request ajax ────────
    $sanitize_request = wp_unslash( $_REQUEST );
    array_walk_recursive( $sanitize_request, 'sanitize_text_field' );

    // ─── Verifies Nonce for security  ────────
    if ( empty( $sanitize_request['nonce'] ) || ! wp_verify_nonce( $sanitize_request['nonce'], 'yuzo-view' ) )  $return = 0;

    // ─── Verify that there is a valid post to tell  ────────
    if ( $sanitize_request['post_id'] == 0 ) $return = -1;

    global $wpdb;

    // ─── We obtain the data transported from the page ────────
    $post_id         = (int)$sanitize_request['post_id'];
    $date_click      = date( "Y-m-d H:i:s" );
    $timestamp_views = time();
    $others          = ! empty( $sanitize_request['others'] ) ? json_decode( stripslashes( $sanitize_request['others'] ), true ): null;  // ◄ return array from JSON.stringify javascript

    // ─── Insert/Update the views that the user made ────────
    $table_name = $wpdb->prefix . 'yuzoviews';
    $count = (int)$others['is_logged'] == 1 || yuzo_isLocalhost() ? 1 : ( (mt_rand ( 0 , 100 ) / 10) <= 2.5 ? 2 : 1 );
    $sql1 = "UPDATE $table_name
                SET views = views + $count,
                last_viewed = '" . $date_click . "',
                modified = ".$timestamp_views."
            WHERE post_id = $post_id";

    $sql2 = "INSERT INTO $table_name values(
            0, $post_id, 1, '$date_click', ".$timestamp_views.")";

    if( ! $r = $wpdb->query($sql1) ){

        $r = @$wpdb->query($sql2);

    }
    // FIXME: here the prepare does not work, I still do not understand why
    //values( %i, %s, %i, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)


    /*
    |--------------------------------------------------------------------------
    | Returns the data to affirm that the insert was made
    |--------------------------------------------------------------------------
    */
    if( $return <= 0 ){
        wp_send_json_error([ 'return' => $return, 'nonce' => $sanitize_request['nonce'], 'verify_nonce' => wp_verify_nonce( $sanitize_request['nonce'], 'yuzo-view' ) ]);
    }else{
        wp_send_json_success(
            array(
                'result'    => ( $r ? true : false ),
                'error_sql' => YUZO_MODE_DEV ? yuzo_sql_print_error()  : null,
                'sql'       => YUZO_MODE_DEV ? $sql1 . '|' . $sql2 : null,
                'request'   => YUZO_MODE_DEV ? $sanitize_request : null,
            )
        );
    }
}

/**
 * Imprime error si algo ocurre en los procesos de AJAX
 *
 * @since   6.0.9.8     2019-08-28      Release
 * @return  void
 */
function yuzo_sql_print_error(){

    global $wpdb;

    if( $wpdb->last_error !== '' ) :

        $str   = htmlspecialchars( $wpdb->last_result, ENT_QUOTES );
        $query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );

        return "<div id='error'>
        <p class='wpdberror'><strong>WordPress database error:</strong> [$str]<br />
        <code>$query</code></p>
        </div>";

    endif;

}