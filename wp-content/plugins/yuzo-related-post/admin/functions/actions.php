<?php
// ───────────────────────────
use YUZO\Core\YUZO_Core as yuzo;
// ───────────────────────────
/**
 *
 * Change status related Ajax
 * @since   6.0     2019-03-05 23:19:32     Release
 * @since   6.0.2   2019-07-12 06:14:55     The id yuzo is added in the change of status
 */
add_action( 'wp_ajax_yuzo-change-status', 'yuzo_change_status_ajax' );
function yuzo_change_status_ajax() {

    // ─── Sanitize request ajax ────────
    array_walk_recursive( $sanitize_request = wp_unslash( $_REQUEST ), 'sanitize_text_field' );

    if( ! empty( $sanitize_request['post_id'] ) && ! empty( $sanitize_request['nonce'] ) && wp_verify_nonce( $sanitize_request['nonce'], 'yuzo_list_cpt_nonce' ) ) {

        global $wpdb;

        $meta_data           = get_post_meta( $sanitize_request['post_id'] , 'yuzo' );
        $yuzo_list_meta_data = (object)$meta_data[0];

        // update
        $yuzo_list_meta_data->related_post_active = $sanitize_request['value'];

        // convert the entire object into an array
        $arrays = yuzo_object_to_array( $yuzo_list_meta_data );
        $arrays['post_id'] = $sanitize_request['post_id'];

        if( update_post_meta( $sanitize_request['post_id'], 'yuzo', $arrays ) ){

            // update individual status goal
            update_post_meta( $sanitize_request['post_id'] , YUZO_ID . '_related_post_active' , $yuzo_list_meta_data->related_post_active );

            wp_send_json_success(
                array(
                    'valor'       => $sanitize_request['value'],
                    'data_update' => $arrays
                )
            );
        }

    }

    wp_send_json_error(
        array(
            'success' => false,
            'error' => esc_html__( 'Error while saving.', 'yuzo' ),
            'debug' => $sanitize_request
        )
    );
}

/**
 * Action ajax that allows to generate the preview in an html file
 * @since   6.0         2019-06-10  Release
 * @since   6.0.9.7     2019-08-01  Modification of the query to show the posts
 * @since   6.0.9.8     2019-08-28  New way to see the preview, many significant changes
 * @since   6.0.9.81    2019-08-29  The css for margin, padding and background of the yuzo wrap was added & Post spacing added
 * @since   6.0.9.82    2019-09-03  It was added 'show image' to remove the image template initially
 * @since   6.1         2019-12-13  Get data text above and below
 */
add_action( 'wp_ajax_yuzo-live-preview', 'yuzo_live_preview' );
function yuzo_live_preview(){

    // ─── Sanitize request ajax ────────
    array_walk_recursive( $sanitize_request = wp_unslash( $_REQUEST ), 'sanitize_text_field' );

    if( ! empty( $sanitize_request['nonce'] ) && wp_verify_nonce( $sanitize_request['nonce'], 'yuzo_preview_nonce' ) ) {

        $_html = '';

        $_html_css_begin = '<html><head><meta name="viewport" content="width=device-width, initial-scale=1">';
        //$_html_css_begin .= '<link rel="stylesheet" type="text/css" href="'. get_template_directory_uri() . '/style.css" />';
        //$_html_css_begin .= '<link rel="stylesheet" type="text/css" href="'. get_stylesheet_uri() . '" />';
        //$_html_css_begin .= '<link rel="stylesheet" type="text/css" href="'. YUZO_URL . 'public/assets/css/yuzo.css' .'?ver='.(rand(1,100)).'" />';
        $_html_css_begin .= wpse251841_wp_head();
        $_html_css       = '/*Fix the border and others below in the preview*/
.yzp-wrapper .yzp-container .yzp-wrap-item{
    display:none;
}html{margin:20px!important;overflow-x:auto;}
body{padding:0!important;}h1,h2,h3,h4,h5,h6{margin:10px 0!important; }
.yzp-preview-only-p{display:none;}
.yzp_wrap_body .yzp_wrap_sidebar{
    margin-left: 0;
    background-color: #dfe5f347;
    padding: 10px 15px;
    min-height:100px;
    margin-top: 20px;
    position: relative;
}
.yzp_wrap_sidebar:before {
    content: \'sidebar\';
    background: #138BE7;
    color: #ffffff;
    border: 1px solid #0c70bd;
    position: absolute;
    top: 0;
    right: 0;
    font-size: 12px;
    padding: 0 15px;
    font-style: italic;
    z-index: 1;
}
@media screen and (min-width: 1025px) {
    body{ background: #f5f5f5; }
    .yzp-preview-only-p{display:block;}
    .yzp_wrap_body{
        display: grid;
        background: #fff;
        grid-template-columns: 1fr 300px;
    }
    .yzp_wrap_body .yzp_wrap_content{
        padding: 24px;
    }
    .yzp_wrap_body .yzp_wrap_sidebar{
        margin-left: 20px;
        margin-top: 0px;
    }
}
';

        $_html_css_ending = '</style></head><body>';

        // Get styles of the selected template ───────────────────────────
        $opt_tmp = new \stdClass;
        $opt_tmp->post_id = rand(111,222);

        $opt_tmp->fieldset_design['panels-design']['design_screen_mobile']['design_screen_mobile_columns']   = (int)$sanitize_request['mobile_colums'];
        $opt_tmp->fieldset_design['panels-design']['design_screen_mobile']['design_screen_mobile_rows']      = (int)$sanitize_request['mobile_rows'];
        $opt_tmp->fieldset_design['panels-design']['design_screen_tablet']['design_screen_tablet_columns']   = (int)$sanitize_request['tablet_colums'];
        $opt_tmp->fieldset_design['panels-design']['design_screen_tablet']['design_screen_tablet_rows']      = (int)$sanitize_request['tablet_rows'];
        $opt_tmp->fieldset_design['panels-design']['design_screen_desktop']['design_screen_desktop_columns'] = (int)$sanitize_request['desktop_colums'];
        $opt_tmp->fieldset_design['panels-design']['design_screen_desktop']['design_screen_desktop_rows']    = (int)$sanitize_request['desktop_rows'];

        $opt_tmp->fieldset_design['panels-design']['design_image_size']   = $sanitize_request['design_image_size'] ;

        $opt_tmp->fieldset_design['panels-design']['design_metas']['enabled'] = isset($sanitize_request['metas']) && $sanitize_request['metas'][0] == '' ? null : $sanitize_request['metas'];

        $opt_tmp->fieldset_design['panels-design']['design_thumbnail_size'] = $sanitize_request['design_thumbnail_size'] ;

        $opt_tmp->fieldset_design['panels-design']['design_show_excerpt'] = $sanitize_request['design_show_excerpt'] ;
        $opt_tmp->fieldset_design['panels-design']['design_text_length']  = $sanitize_request['design_text_length'] ;
        $opt_tmp->fieldset_design['panels-design']['design_text_content'] = $sanitize_request['design_text_content'] ;

        $opt_tmp->fieldset_design['panels-design']['title'] = $sanitize_request['title'] ;
        $opt_tmp->fieldset_design['panels-design']['design_list_image_size'] = $sanitize_request['design_list_image_size'] ;
        $opt_tmp->fieldset_design['panels-design']['template'] = $template = $sanitize_request['template'];
        $opt_tmp->fieldset_design['panels-design']['template_colours'] = $sanitize_request['template_colours'];
        $opt_tmp->fieldset_design['panels-design']['template_color_1'] = $sanitize_request['template_color_1'];
        $opt_tmp->fieldset_design['panels-design']['template_color_2'] = $sanitize_request['template_color_2'];
        $opt_tmp->fieldset_design['panels-design']['template_type'] = $sanitize_request['template_type'];
        $opt_tmp->fieldset_design['panels-design']['template_show_imagen'] = $sanitize_request['template_show_imagen'];
        $opt_tmp->fieldset_design['panels-design']['content_location'] = $sanitize_request['content_location'];
        $opt_tmp->fieldset_design['panels-design']['where_show'] = $where_show = $sanitize_request['where_show'];
        $opt_tmp->fieldset_design['panels-design']['content_appende_paragraph_order']['location_paragraph'] = $location_paragraph = $sanitize_request['location_paragraph'];

        $opt_tmp->fieldset_design['panels-design']['design_text_font_size']['width'] = $sanitize_request['design_text_font_size'];
        $opt_tmp->fieldset_design['panels-design']['design_text_line_height']['width'] = $sanitize_request['design_text_line_height'];
        $opt_tmp->fieldset_design['panels-design']['design_text_font_weight'] = $sanitize_request['design_text_font_weight'];
        $opt_tmp->fieldset_design['panels-design']['design_text_color_hover']['color'] = $sanitize_request['design_text_color_hover_color'];
        $opt_tmp->fieldset_design['panels-design']['design_text_color_hover']['hover'] = $sanitize_request['design_text_color_hover_hover'];

        $opt_tmp->fieldset_design['panels-design']['design_box_margin']['top'] = $sanitize_request['design_box_margin_top'];
        $opt_tmp->fieldset_design['panels-design']['design_box_margin']['bottom'] = $sanitize_request['design_box_margin_bottom'];
        $opt_tmp->fieldset_design['panels-design']['design_box_margin']['left'] = $sanitize_request['design_box_margin_left'];
        $opt_tmp->fieldset_design['panels-design']['design_box_margin']['right'] = $sanitize_request['design_box_margin_right'];
        $opt_tmp->fieldset_design['panels-design']['design_box_margin']['unit'] = $sanitize_request['design_box_margin_unit'];

        $opt_tmp->fieldset_design['panels-design']['design_box_padding']['top'] = $sanitize_request['design_box_padding_top'];
        $opt_tmp->fieldset_design['panels-design']['design_box_padding']['bottom'] = $sanitize_request['design_box_padding_bottom'];
        $opt_tmp->fieldset_design['panels-design']['design_box_padding']['left'] = $sanitize_request['design_box_padding_left'];
        $opt_tmp->fieldset_design['panels-design']['design_box_padding']['right'] = $sanitize_request['design_box_padding_right'];
        $opt_tmp->fieldset_design['panels-design']['design_box_padding']['unit'] = $sanitize_request['design_box_padding_unit'];
        $opt_tmp->fieldset_design['panels-design']['design_box_background'] = $sanitize_request['design_box_background'];

        $opt_tmp->fieldset_design['panels-design']['design_post_spacing']['width'] = $sanitize_request['design_post_spacing'];

        $opt_tmp->fieldset_design['panels-design']['design_html_above'] = $sanitize_request['design_html_above'];
        $opt_tmp->fieldset_design['panels-design']['design_html_below'] = $sanitize_request['design_html_below'];
        //

        $_html_array_template = yuzo::instance()->public->related_template->get_class_and_css_template( $opt_tmp );
        $_html_class_template = $_html_array_template[0];
        $_html_css_template   = $_html_array_template[1];

        $_html_css_base  =  yuzo::instance()->public->related_template->header_css( $opt_tmp );

        //$template = $opt_tmp->design_layout == 'grid' ? $opt_tmp->design_templates_grid : $opt_tmp->design_templates_list;

        // Add text/html above
        $yuzo_html = isset( $opt_tmp->fieldset_design['panels-design']['design_html_above'] ) ? $opt_tmp->fieldset_design['panels-design']['design_html_above'] : '';

         // Arguments to have random post
        $args =  array(
            'post_type'   => 'post',
            'post_status' => 'publish',
            'orderby'     => 'rand',
            'order'       => 'DESC',
        );

        // If it is INLINE you must only drive 1
        if( $opt_tmp->fieldset_design['panels-design']['template_type'] == 'inline' ){
            $args['posts_per_page'] = 1;
        }

        $query = new \WP_Query( $args );

        // It's time! Go someplace random
        $yuzo_html .= '<section class="wp-yuzo yzp-wrapper '. $_html_class_template .'" >';
        if( $opt_tmp->fieldset_design['panels-design']['template_type'] != 'inline' ){
            $yuzo_html .= $opt_tmp->fieldset_design['panels-design']['title'];
        }
        $yuzo_html .= yuzo::instance()
                        ->public
                        ->related_template
                        ->loop( $template, $opt_tmp, yuzo::instance()->settings, $query );

        $yuzo_html .= '</section>';

        // Add text/html below
		$yuzo_html .= isset( $opt_tmp->fieldset_design['panels-design']['design_html_below'] ) ? $opt_tmp->fieldset_design['panels-design']['design_html_below'] : '';
        // ───────────────────────────

        // Put demo content for the preview
        if( $where_show == 'content' || $where_show == 'shortcode' ){
            $_html .= "<div class='yzp_wrap_body'>";
            $_html .= "<div class='yzp_wrap_content'>";
            $_html .= "<h1>My title post test for Yuzo preview</h1>";
            $_html .= "<div class='entry-content'>";
            $_html_paragraph = "<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.
            Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.
            It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.
            </p>";
            $_html_paragraph2 = "<p class='yzp-preview-only-p'>Lorem Ipsum is simply dummy text of the printing and typesetting industry.
            Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.
            It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.
            </p>";
            if( $opt_tmp->fieldset_design['panels-design']['content_location'] == 'middle-post-content' ){
                $_html .=  $_html_paragraph . $yuzo_html . $_html_paragraph;
            }elseif( $opt_tmp->fieldset_design['panels-design']['content_location'] == 'top-post-content' ){
                $_html .=  $yuzo_html . $_html_paragraph ;
            }elseif( $opt_tmp->fieldset_design['panels-design']['content_location'] == 'left-post-content' ){
                $_html .=   $yuzo_html . str_repeat($_html_paragraph,3) ;
            }elseif( $opt_tmp->fieldset_design['panels-design']['content_location'] == 'right-post-content' ){
                $_html .=   $yuzo_html . str_repeat($_html_paragraph,3) ;
            }elseif( $opt_tmp->fieldset_design['panels-design']['content_location'] == 'top-paragraph-number' ){
                $_html .=   str_repeat($_html_paragraph,$location_paragraph) . $yuzo_html . str_repeat($_html_paragraph, (int)$location_paragraph + 3)  ;
            }elseif( $opt_tmp->fieldset_design['panels-design']['content_location'] == 'bottom-paragraph-number' ){
                $_html .=   str_repeat($_html_paragraph, (int)$location_paragraph + 2) . $yuzo_html . str_repeat($_html_paragraph, (int)$location_paragraph)  ;
            }else{
                $_html .=  $_html_paragraph . $_html_paragraph2 . $yuzo_html ;
            }
            $_html .="</div></div>
            <div class='yzp_wrap_sidebar'></div>";
        }elseif( $where_show == 'widget' ){
            $_html .= "<div class='yzp_wrap_body'>";
            $_html .= "<div class='yzp_wrap_content'>";
            $_html .= "<h1>My title post test for Yuzo preview</h1>";
            $_html .= "<div class='entry-content'>";
            $_html_paragraph = "<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.
            Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.
            It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.
            </p>";
            $_html_paragraph2 = "<p class='yzp-preview-only-p'>Lorem Ipsum is simply dummy text of the printing and typesetting industry.
            Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.
            It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.
            </p>";
            $_html .= str_repeat($_html_paragraph,4);
            $_html .= "</div></div>";
            $_html .= "<div class='yzp_wrap_sidebar'>{$yuzo_html}</div>";
        }


        // Sidebar
        $_html .= "
</div>";

        // Update the file for each change
        $_html_final = $_html_css_begin . '<style>' . $_html_css_base . $_html_css . $_html_css_template . $_html_css_ending . $_html;
        $_html_final .= '</body></html>';
        $fp = fopen( YUZO_PATH . 'admin/assets/preview/preview.html' ,"wb");
        fwrite($fp,$_html_final);
        fclose($fp);

        wp_send_json_success(
            array(
                'success' => true,
            )
        );
    }else{

        wp_send_json_error(
            array(
                'success'        => false,
                'error'          => esc_html__( 'Error while create file.', 'yuzo' ),
                'debug'          => $sanitize_request,
                'validate_nonce' => $sanitize_request['nonce'] . '=' . 'yuzo_preview_nonce',
            )
        );

    }

}

/**
 * Get the wp_head as an unprinted string
 *
 * @since   6.0.9.8     2019-08-28      Release
 * @return  string
 */
function wpse251841_wp_head() {
    ob_start();
    wp_head();
    return ob_get_clean();
}

add_filter( 'https_ssl_verify', '__return_false' );