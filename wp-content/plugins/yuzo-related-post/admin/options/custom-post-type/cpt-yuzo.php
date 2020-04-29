<?php
/**
 * @since   6.0         2019-04-14      Release
 * @since   6.0.2       2019-07-12      It is validated that only for admin this is executed
 * @since   6.0.5       2019-07-12      - Remove columns from other plugins in the custom post type yuzo
 *                                      - Columns is validated where the counters can be displayed
 *                                      - Remover metabox 'dpsp_share_statistics' infiltrado en yuzo
 * @since   6.0.9       2019-07-21      Significant changes in validations
 * @since   6.0.9.7     2019-08-01      Change of name in the menu of List/Related posts to Related/List posts
 * @since   6.0.9.8     2019-08-28      - Language functions added in tags
 *                                      - New way of interpreting data from Yuzo&#39;s columns
 *                                      - Documentation was added within the help tab in all menus of: General configuration and within each Yuzo
 * @since   6.0.9.84    2019-10-06      The name of the Yuzo list menu was changed
 * @since   6.1         2019-12-13      Yuzo statistics are printed on the footer only if you are on a block editor page
 */
/*
|--------------------------------------------------------------------------
| Creation Custom Post Type: YUZO
|--------------------------------------------------------------------------
*/
// Create a Yuzo post type.
// FUTURE: add language function
global $pagenow, $post;
if( ! is_admin() ) return;
$names = [
    'name'               => 'Yuzo',
    'singular_name'      => 'Yuzo',
    'all_items'          => __('Yuzo Posts','yuzo'),
    'add_new'            => __('Add new Yuzo','yuzo'),
    'add_new_item'       => __('Add new Yuzo','yuzo'),
    'edit_item'          => __('Edit yuzo post','yuzo'),
    'new_item'           => __('New yuzo post','yuzo'),
    'view_item'          => __('View yuzo post','yuzo'),
    'search_items'       => __('Search yuzo post','yuzo'),
    'not_found'          => __('No yuzo post found','yuzo'),
    'not_found_in_trash' => __('No yuzo post found in Trash','yuzo'),
    'parent_item_colon'  => __('Parent yuzo post:','yuzo'),

    'singular' => __( 'Yuzo Post', 'yuzo' ),
    'plural'   => __( 'Yuzo Posts', 'yuzo' ),
];
$yuzo = new Y_PostType( 'yuzo', [
    'name'               => 'yuzo',
    'slug'               => 'yuzo',
    'show_in_menu'       => false,
    'supports'           => array( 'title' ),
    'rewrite'            => false,
    'publicly_queryable' => false, // ← remove preview after save
    'show_in_admin_bar'  => false,
], $names );

// Hide the date and author columns.
$yuzo->columns()->hide( [ 'date', 'author', 'title','check', 'wpfc_column_clear_cache', 'gadwp_stats' ] );

// Set the Yuzo menu icon.
$yuzo->icon( 'dashicons-book-alt' );

// add a price and rating column
$yuzo->columns()->add([
    'status'    => __('Status', 'yuzo' ),
    'title2'    => __('Title', 'yuzo' ),
    'where'     => __('Location', 'yuzo' ),
    'type'      => __('Type', 'yuzo' ),
]);

// Get options current setting yuzo
global $yuzo_list_meta_data, $yuzo_settings;
$yuzo_list_meta_data = null;
$yuzo_settings       = yuzo_get_option();
// Column 'status' callback
$yuzo->columns()->populate('status', function($column, $post_id) {
    global $yuzo_list_meta_data;

    $meta_data             = get_post_meta($post_id, 'yuzo');
    $yuzo_list_meta_data   = (object)$meta_data[0];
    $class_active          = ! empty( $yuzo_list_meta_data->related_post_active ) ? 'pf--active' : '';
    $nonce                 = wp_create_nonce( 'yuzo_list_cpt_nonce' );
    $post_type             = get_post_status( $post_id );
    $class_switcher_active = $post_type == 'trash' ? 'pf-field-switcher-disabled' : '';
    echo '<span class="spinner"></span>';
    echo '<div data-id-post="'. $post_id .'" data-nonce="'. $nonce .'" class="pf-field-switcher '. $class_switcher_active .'"><div class="pf--switcher '. $class_active .'" style="width: 100px;">';
    echo '<span class="pf--on">Enabled</span>';
    echo '<span class="pf--off">Disabled</span>';
    echo '<span class="pf--ball"></span>';
    echo '<input type="text" value="'. $yuzo_list_meta_data->related_post_active .'" ></div></div>';
    echo '<script>jQuery(document).ready(function($){ $(".pf-field-switcher").pf_field_switcher(); });</script>';
});

// Column 'title' callback
$yuzo->columns()->populate('title2', function($column, $post_id) {
    global $yuzo_list_meta_data;
    $t         = get_the_title( $post_id );
    $title     = ! $t ? __('(No title)','yuzo') : $t ;  //;strip_tags($yuzo_list_meta_data->title);
    $post_type = get_post_status( $post_id );

    // Get text template
    $text_template =  ! empty( $yuzo_list_meta_data->fieldset_design['panels-design']['template_type'] ) ? $yuzo_list_meta_data->fieldset_design['panels-design']['template_type'] : 'grid';

    if( $post_type != 'trash' ){
        $link = admin_url( 'post.php?post='. $post_id .'&action=edit&classic-editor' );
        echo "<a href='$link' class='$post_type'>$title</a> — <span title='Template'>$text_template</span>";
    }else{
        echo $title;
    }

});

// Columns  callback
$yuzo->columns()->populate('where', function($column, $post_id) {
    global $yuzo_list_meta_data;
    $labels = [
        'content'   => __('Content','yuzo'),
        'widget'    => __('Widget','yuzo'),
        'shortcode' => __('Shortcode','yuzo'),
    ];
    $yuzo_list_meta_data = yuzo_fix_var_design( $yuzo_list_meta_data );
    $where_show = $yuzo_list_meta_data->fieldset_design['panels-design']['where_show'];
    $location = $yuzo_list_meta_data->fieldset_design['panels-design']['content_location'];
    // Text label
    if( $where_show == 'content' ){
        if( $location == 'top-post-content' ){
            $text_label =  __('Above Post Content','yuzo');
        }elseif( $location == 'middle-post-content' ){
            $text_label =  __('Middle Post Content','yuzo');
        }elseif( $location == 'below-post-content' ){
            $text_label =  __('Below Post Content','yuzo');
        }elseif( $location == 'left-post-content' ){
            $text_label =  __('To the Left of Post Content','yuzo');
        }elseif( $location == 'right-post-content' ){
            $text_label =  __('To the Right of Post Content','yuzo');
        }elseif( $location == 'top-paragraph-number' ){
            $text_label =  __('After the paragraph number (From the top)','yuzo');
        }elseif( $location == 'bottom-paragraph-number' ){
            $text_label =  __('After the paragraph number (From the bottom)','yuzo');
        }
    }elseif( $where_show == 'widget' ){
        $text_label = __('Inside a sidebar','yuzo');
    }elseif( $where_show == 'shortcode' ){
        $text_label = "<input type='text' readonly value='[yuzo id=\"$post_id\"]' /> ";
    }

    echo '<div class="">';
    echo '<div><strong class="yzp-td-location-'.$where_show.'">'. $labels[ $where_show ] .'</strong> '.$text_label.'</div>';
    echo '</div>';
});
$yuzo->columns()->populate('type', function($column, $post_id) {
    global $yuzo_list_meta_data;
    $labels = [
        'related' => __('Related post','yuzo'),
		'list'    => __('List post','yuzo'),
    ];
    echo '<div class="yuzo-list--type2">'. $labels[$yuzo_list_meta_data->related_type] .'</div>'; // $yuzo_list_meta_data->where_show;
});

// Column for CPT 'post' (show the views)
function yuzo_views_in_column_cpt($post_id = 0){
    global $yuzo_settings;
    $return  = '';
    $views   = yuzo_get_views( $post_id );
    $clicks  = yuzo_get_clicks( $post_id, true );
    $clicks2 = yuzo_get_clicks( $post_id );
    $level   = yuzo_get_index_level($views, 'medium');
    $views   = yuzo_cut_counter( $views, $yuzo_settings );

    $return .= '<span class="y-icons-levels y-icon-level'. $level .'"  title="Views"></span> ';
    $return .= '<span class="y-colors-levels y-color-level'. $level.'" title="Views">'. $views .'</span>';
    $return .= '<div></div>';
    $return .= '<span class="y-icons-clicks-wrap">
<span class="y-icons-clicks" title="Clicks on a yuzo from this post"></span>
<span class="y-icons-clicks-label" title="Clicks on a yuzo from this post">'. $clicks .'</span>
</span>
<span class="y-icons-clicks-wrap y-icons-clicks-wrap2">
<span class="y-icons-clicks y-icons-clicks2" title="Visit from another yuzo post to this page"></span>
<span class="y-icons-clicks-label" title="Visit from another yuzo post to this page">'. $clicks2 .'</span>
</span>';

    return $return;
}

// Get the value of a post (based on yuzo click)
function yuzo_post_value_in_column_cpt($post_id = 0){
    return yuzo_get_post_value( $post_id );
}

/*
|--------------------------------------------------------------------------
| get all the CPT selected in the main option
|--------------------------------------------------------------------------
*/
add_action('admin_head', 'register_yuzo_column_in_post_type');
function register_yuzo_column_in_post_type(){
    global $yuzo_settings;

    /* If the counter is deactivated then it does not show it either */
    if( isset(  $yuzo_settings->general_disabled_counter_view ) &&
                $yuzo_settings->general_disabled_counter_view ) return;

    $post_type_counter = empty( $yuzo_settings->general_cpt_to_counter ) ? ['post','page'] : $yuzo_settings->general_cpt_to_counter;
    if( ! empty( $post_type_counter ) ){
        foreach ($post_type_counter as $value) {
            $postcolumn = new Y_PostType($value);
            $postcolumn->columns()->add([
                'yviews'  => __('Views', 'yuzo' ),
            ]);
            $postcolumn->columns()->populate('yviews', function($column, $post_id) {
                echo yuzo_views_in_column_cpt( $post_id );
            });
            $postcolumn->register();
        }
    }
}
add_action('admin_head', 'register_yuzo_column_in_post_type_for_post_value');
/**
 * Post Value Column
 * // optimize: It is currently hidden until a better analysis.
 *
 * @since   6.0.9.83    2019-10-04      Release
 * @return void
 */
function register_yuzo_column_in_post_type_for_post_value(){
    global $yuzo_settings;

    /* If the counter is deactivated then it does not show it either */
    if( isset(  $yuzo_settings->general_disabled_counter_view ) &&
                $yuzo_settings->general_disabled_counter_view ) return;

    $post_type_counter = empty( $yuzo_settings->general_cpt_to_counter ) ? ['post','page'] : $yuzo_settings->general_cpt_to_counter;
    if( ! empty( $post_type_counter ) ){
        foreach ($post_type_counter as $value) {
            $postcolumn = new Y_PostType($value);
            /* $postcolumn->columns()->add([
                'ypost_value'  => __('Post value', 'yuzo' ),
            ]);
            $postcolumn->columns()->populate('ypost_value', function($column, $post_id) {
                if( ! $pv = yuzo_post_value_in_column_cpt( $post_id ) ) return;
                echo '$' . $pv;
            }); */
            $postcolumn->register();
        }
    }
}

// Remove quick edit
add_filter( 'post_row_actions', 'yuzo_cpt_remove_quickedit_row_actions', 10, 2 );
function yuzo_cpt_remove_quickedit_row_actions( $actions, $post ) {
    if ( 'yuzo' === $post->post_type ) {
        // Removes the "Quick Edit" and all action.
        unset( $actions['inline hide-if-no-js'] );
        unset( $actions['edit'] );
        unset( $actions['trash'] );
        unset( $actions['view'] );
    }
    return $actions;
}

// Remove filters
// BUG: tell the author that with its function do not remove all the filters because it is the date, you had to apply the function below
$yuzo->filters( [] );
add_action('admin_head', 'remove_yuzo_date_drop');
function remove_yuzo_date_drop(){
    $screen = get_current_screen();
    if ( 'yuzo' == $screen->post_type ){
        add_filter('months_dropdown_results', '__return_empty_array');
    }
}

// Edit the link header of the list of related post
//add_filter( 'views_edit-yuzo', 'yuzo_custom_draft_translation', 10, 1);
function yuzo_custom_draft_translation( $views ){
    $views['draft'] = str_replace('Draft', 'Inactive', $views['draft']);
    $views['publish'] = str_replace('Published', 'Active', $views['publish']);
    return $views;
}

// Force that all post saved from custom post type of yuzo be made public (active)
//add_filter( 'wp_insert_post_data', 'yuzo_prevent_post_change', 20, 2 );
function yuzo_prevent_post_change( $data, $postarr ) {
    if ( ! isset($postarr['ID']) || ! $postarr['ID'] ) return $data;
    if ( $postarr['post_type'] !== 'yuzo' ) return $data; // only for products
    $old = get_post($postarr['ID']); // the post before update
    if (
        $old->post_status !== 'incomplete' &&
        $old->post_status !== 'trash' && // without this post restoring from trash fail
        $data['post_status'] === 'publish'
    ) {
        // set post to incomplete before being published
        $data['post_status'] = 'incomplete';
    }
    return $data;
}

// Remove the permalinks box
add_filter('get_sample_permalink_html', 'yuzo_hide_permalinks', 10, 5);
function yuzo_hide_permalinks($return, $post_id, $new_title, $new_slug, $post){
    if($post->post_type == 'yuzo') {
        return '';
    }
    return $return;
}

// Remove bulk actions
add_filter( 'bulk_actions-edit-yuzo', 'remove_yuzo_bulk_actions' );
function remove_yuzo_bulk_actions( $actions ){
    unset( $actions[ 'edit' ] );
    unset( $actions[ 'trash' ] );
    return $actions;
}

// Register the post type to WordPress.
$yuzo->register();

// Add CPT as submenu the Yuzo main menu
add_action('admin_menu', function(){
    add_submenu_page(
        'yuzo',
        'Related | List',
        'Related | List',
        'manage_options',
        'edit.php?post_type=yuzo',
        NULL
    );
});

// Add butons and object in metabox main
add_action( 'admin_footer-post.php', 'yuzo_add_button_actions' );
add_action( 'admin_footer-post-new.php', 'yuzo_add_button_actions' );
function yuzo_add_button_actions(){
    global $post;
    if( !isset( $post ) || 'yuzo' != $post->post_type ) return;
    echo '<script>
    jQuery(document).ready(function($){
        var $sw_clone = $(".class-related_post_active").clone();
        $(".class-related_post_active").remove();
        $sw_clone.appendTo("#misc-publishing-actions").pf_field_switcher();
        $("#major-publishing-actions #delete-action a").addClass("button button-large");
        $(".post-type-yuzo .submitbox .submitdelete").text("");
    });</script>';
}

// Save data in meta_post to know if a related is active or not
// This is the only separate meta for 'related_post_active'
add_action('save_post', 'yuzo_save_postdata' );
function yuzo_save_postdata( $post_id ){
    // VIEW: to show the setting data when saving
    //echo json_encode( $_POST['yuzo'] );exit;
    if( ! isset($_POST['yuzo']) ) return;

    // Verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
    // to do anything
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        return $post_id;

    // Check permissions to edit pages and/or posts
    if ( isset($_POST['post_type']) && 'yuzo' == $_POST['post_type']) {
        if ( !current_user_can( 'edit_page', $post_id ) || !current_user_can( 'edit_post', $post_id ))
            return $post_id;
    }

    // Authenticated: we need to find and save the data
    $data = $_POST['yuzo']['related_post_active'] ;

    // save data in INVISIBLE custom field (note the "_" prefixing the custom fields' name
    update_post_meta( $post_id, YUZO_ID . '_related_post_active', $data );

}

// Prevent the autosave from taking effect in Yuzo CPTs
add_action( 'admin_enqueue_scripts', 'yuzo_avoid_admin_enqueue_scripts' );
function yuzo_avoid_admin_enqueue_scripts() {
    if ( 'yuzo' == get_post_type() )
        wp_dequeue_script( 'autosave' );
}

// ─── Remove metabox public not allow ────────
function remove_cuttax_metaboxes() {
    $post_type = 'yuzo';
    remove_meta_box( 'dpsp_share_statistics', $post_type, 'normal' );
    remove_meta_box( 'internal_link', $post_type, 'normal' );
    remove_meta_box( 'amt-metadata-box', $post_type, 'normal' );
    remove_meta_box( 'rocket_post_exclude', $post_type, 'side' );
    remove_meta_box( 'rocket_post_exclude', $post_type, 'normal' );
    remove_meta_box( 'simple_css_metabox', $post_type, 'normal' );
    remove_meta_box( 'simple_css_metabox', $post_type, 'normal' );
    remove_meta_box( 'page-links-to', $post_type, 'normal' );
}
add_action( 'admin_menu' , 'remove_cuttax_metaboxes', 100 );

// ─── Shows number of counters in the submitbox ────────
add_action( 'post_submitbox_misc_actions', 'yuzo_show_count_submitbox' );
add_action( 'admin_footer', 'yuzo_show_count_submitbox' );
function yuzo_show_count_submitbox(){
    global $post, $yuzo_settings, $current_screen;

    $print_stats = false;
    $is_gunterberg = false;
    if( current_action() == 'admin_footer' ){
        if (!isset($current_screen)) {$current_screen = get_current_screen();}
        if ( ( method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor() )
        || ( function_exists('is_gutenberg_page') && is_gutenberg_page() ) ) {
            $print_stats = true;
            $is_gunterberg = true;
        }
    }elseif( current_action() == 'post_submitbox_misc_actions' ){
        $print_stats = true;
    }

    // ─── Valid whether or not to show statistics as HTML ────────
    if( ! $print_stats ) return false;

    /* If the counter is deactivated then it does not show it either */
    if( isset(  $yuzo_settings->general_disabled_counter_view ) &&
                $yuzo_settings->general_disabled_counter_view ) return;

    /* Validate if the current post type is allowed to count views, if so, then show the counters */
    $post_type_allow = empty( $yuzo_settings->general_cpt_to_counter ) ? ['post'] : $yuzo_settings->general_cpt_to_counter;
    if ( ! in_array( get_post_type($post) , $post_type_allow ) ) return false;

    /* You get the numbers of the current post */
    $views      = yuzo_get_views( $post->ID );
    $level      = yuzo_get_index_level( $views, 'medium' );
    $views      = yuzo_cut_counter( $views , $yuzo_settings, false );
    $clicks     = yuzo_get_clicks( $post->ID, true );
    $clicks2    = yuzo_get_clicks( $post->ID );
    $post_value = (float)yuzo_post_value_in_column_cpt( $post->ID );
    // FIXME: awhat you have to put it as it was to continue with the development of posts, what happens elsa says that goes out in the editors and they think that they will win
    // FIXME: do it with the setting part that only adminis can see how much it is worth.
    $class_post_value = ''; // $post_value != 0 ? ' yzp-with-postvalue' : '';
    ?>
        <div <?php if( $is_gunterberg ): ?>style="display:none;"<?php endif; ?> class="misc-pub-section yzp-metabox-stats - <?php echo $class_post_value; ?>">
            <div class="misc-pub-section-view misc-pub-section-view-level<?php echo $level; ?>">
                <span class="y-icons-levels y-icon-level<?php echo $level; ?>" title="Views from Yuzo"></span>
                <span class="y-colors-levels y-color-level<?php echo $level; ?>" title="Views from Yuzo"><?php echo $views; ?></span> </div>
            <div class="misc-pub-section-click" >
                <span class="y-icons-clicks"  title="Clicks on a yuzo from this post"></span>
                <span class="y-icons-clicks-label"  title="Clicks on a yuzo from this post"><?php echo $clicks; ?></span>
                <?php if( $is_gunterberg ): ?><div></div><?php endif; ?>
                <span class="y-icons-clicks y-icons-clicks2"  title="Visit from another yuzo post to this page"></span>
                <span class="y-icons-clicks-label y-icons-clicks-label2"  title="Visit from another yuzo post to this page"><?php echo $clicks2; ?></span>
            </div>
            <?php if( $post_value != 0 ): ?>
            <div class="misc-pub-section-postvalue" >
                <span class="y-icons-postvalue-label"  title="Post value based on Yuzo clicks"><?php echo '$' . $post_value; ?></span>
            </div>
            <?php endif; ?>
        </div>
    <?php
}

/**
 * When user is on a Yuzo admin page, display footer text
 * that graciously asks them to rate us.
 *
 * @since   6.0.9.6     2019-07-28      Release
 * @param   string      $text           Text to place
 * @return  string
 */
add_filter( 'admin_footer_text', 'yuzo_cpt_admin_footer', 1, 2 );
function yuzo_cpt_admin_footer( $text ) {
    global $current_screen;
    $imagen = [
        'https://i.imgur.com/SFIgSmV.jpg','https://i.imgur.com/SygStno.jpg','https://i.imgur.com/7iMtzX3.jpg',
        'https://i.imgur.com/GGvrpzn.jpg','https://i.imgur.com/7B1FFGU.jpg','https://i.imgur.com/OfXQuix.jpg',
        'https://i.imgur.com/Qj4W84O.jpg','https://i.imgur.com/C7T6SvH.jpg','https://i.imgur.com/jFcViTG.jpg',
        'https://i.imgur.com/bzZq35o.jpg',
    ];
    $fivestart = '<div class="fdc-fives fdc-tooltip top"><a href="http://bit.ly/Yuzo5Star" target="_blank" style="text-decoration: none">
<span class="dashicons dashicons-wordpress" style="color:black"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
</a>
<span class="tiptext"><img src="'. $imagen[rand(0,9)] .'.jpg" />Show us some 💙 by writing your review</span>
</div>';
    if ( !empty( $current_screen->id ) && strpos( $current_screen->id, 'yuzo' ) !== false ) {
        $url  = '';
        $text = 'Made with 💙 by <span class="yzp-admin-credit">Lenin Zapata</span><span class="fdc-admin-footer-separate">|</span>' . $fivestart;
    }
    return $text;
}

/**
 * Fired before right side footer text is echoed. Third parameter is just a indication of a version number,
 * Its values doesn't make any other sense.
 *
 * @param   string  2019-10-04      Release
 * @return  string
 */
add_filter('update_footer', 'yuzo_cpt_admin_footer2', 11);
function yuzo_cpt_admin_footer2( $text ) {
    global $current_screen;
    if ( !empty( $current_screen->id ) && strpos( $current_screen->id, 'yuzo' ) !== false ) {
        $url = '<a target="_blank" href="https://yuzopro.com/changelog/">Changelog</a>';
        $text = sprintf( $url, 'yuzo' ) ;
    }
    return $text;
}


/**
 * Add a button to Yuzo's custom post type list
 *
 * @since   6.1.40  2020-01-12  Release
 * @return  string
 *
 * hooked   admin_head
 */
function yuzo_add_button_cpt_js_to_head() {
    ?>
    <script>
    //jQuery( document ).ready(function() {
    jQuery(function(){
        jQuery('<a href="http://bit.ly/YuzoDonate2" target="_blank" class="yuzo-botton-donate-cpt page-title-action">Donate</a>').insertAfter("body.post-type-yuzo .wrap h1");
    });
    </script>
    <?php
}
add_action('admin_head', 'yuzo_add_button_cpt_js_to_head');

/*
|--------------------------------------------------------------------------
| Add a help table for the yuzo list
|--------------------------------------------------------------------------
*/
add_action('load-edit.php', 'yuzo_list_add_help_tab', 9999);
function yuzo_list_add_help_tab () {
    global $post_type;
    if( 'yuzo' != $post_type ){
        $screen = get_current_screen();
        $screen->add_help_tab( array(
            'id'      => 'yuzo_list',
            'title'   => __('General','yuzo'),
            'content' => '<h4>'. __('Columns','yuzo') .'</h4>
            <p><strong>'. __('Status','yuzo') .': </strong>' . __( 'Enabled: Enable yuzo and your personal settings. Disabled: It doesn\'t show that Yuzo.', 'yuzo' ) . '</p>
            <p><strong>'. __('Tile','yuzo') .': </strong>' . __( 'Title that identifies this configuration.', 'yuzo' ) . '</p>
            <p><strong>'. __('Location','yuzo') .': </strong>' . __( 'Location where Yuzo is going to be displayed. There are: Content, Widget and shortcode.', 'yuzo' ) . '</p>
            <p><strong>'. __('Type','yuzo') .': </strong>' . __( 'There are 2 types. <i>Related</i>: Shows related post, <i>List</i>: Shows a list of posts that can be varied according to the configuration.', 'yuzo' ) . '</p>'
        ) );
        $screen->remove_help_tab('yst-columns');
    }
}
add_action('load-post.php', 'yuzo_in_add_help_tab', 9999);
add_action('load-post-new.php', 'yuzo_in_add_help_tab', 9999);
function yuzo_in_add_help_tab () {
    global $post_type;
    if( 'yuzo' != $post_type ){
        $screen = get_current_screen();
        $screen->add_help_tab( array(
            'id'      => 'yuzo_in',
            'title'   => __('General','yuzo'),
            'content' => '<p><strong>✨ Yuzo </strong>'. __(' has configuration necessary to show related and list posts as you wish.
There is a range of varieties of options that are easy to understand and use according to needs. It has several locations to put the posts as well as variations to show them.
Try the options and enjoy Yuzo.','yuzo') .'</p>'
        ) );
        $screen->add_help_tab( array(
            'id'      => 'yuzo_in_design',
            'title'   => __('Design','yuzo'),
            'content' =>
'<p>' .
'<strong>✔️ '. __('Title to show','yuzo') .'</strong>: '. __('It is the title that will be displayed on the posts as a reference that you want to show something to the public, it usually goes at the top according to the layout It supports HTML for a better adaptation to your current theme.','yuzo') . '<br />' .
'<strong>✔️ '. __('Location','yuzo') .'</strong>: '. __('There are 3 locations where posts can be displayed a) Under or inside the content, b) Through a widget (It needs to go to the widget section), c) Through a shortcode (you can put it anywhere on the page or within the content).
In these three ways Yuzo understands that you can make it appear where you want and when you want.','yuzo') .
'<ul>' .
    '<li><strong>'. __('Content','yuzo') .'</strong>: '. __('Show posts within the content of the article in several possible sections.','yuzo') .
        '<ol>' .
            '<li><strong>'. __('Above Post Content','yuzo') .'</strong>: '. __('Show the posts on the article just below the post title.','yuzo') . '</li>' .
            '<li><strong>'. __('Middle Post Content','yuzo') .'</strong>: '. __('Calculate the total number of paragraphs in the article and place it in the middle.','yuzo') . '</li>' .
            '<li><strong>'. __('Below Post Content','yuzo') .'</strong>: '. __('Posts are shown in Classic section just at the end of the article.','yuzo') . '</li>' .
            '<li><strong>'. __('To the Left of Post Content','yuzo') .'</strong>: '. __('Show posts in the upper left of the article.','yuzo') . '</li>' .
            '<li><strong>'. __('To the Right of Post Content','yuzo') .'</strong>: '. __('Show posts in the upper right of the article.','yuzo') . '</li>' .
            '<li><strong>'. __('After the paragraph number (From the top)','yuzo') .'</strong>: '. __('Show just after paragraph number X that you select, from top to bottom','yuzo') . '</li>' .
            '<li><strong>'. __('After the paragraph number (From the bottom)','yuzo') .'</strong>: '. __('Show just before paragraph number X that you select, from bottom to top','yuzo') . '</li>' .
        '</ol>' .
    '</li>' .
    '<li><strong>'. __('Widget','yuzo') .'</strong>: '. __('Show posts on a website sidebar, After saving this configuration you must go to Appearance ⇨ Widgets ⇨ drag a Yuzo Widget and select this configuration.','yuzo') . '</li>' .
    '<li><strong>'. __('Shortcode','yuzo') .'</strong>: '. __('It shows the posts using the respective shortcode, this code is shown by selecting this option and can be placed in a content, block or within the Theme.','yuzo') . '</li>' .
'</ul> ' .
'<strong>✔️ '. __('Template','yuzo') .'</strong>: '. __('Select the template that best suits your needs, you can choose between different layout such as: Grid, List and Inline (there are templates that have dressings for customization).','yuzo') . '<br />' .
'<strong>✔️ '. __('Text Customization','yuzo') .'</strong>: '. __('Customize the text with font sizes and colors, what is necessary to customize the text of the posts.','yuzo') . '<br />' .
'<strong>✔️ '. __('Number posts / Devices','yuzo') .'</strong>: '. __('You can select the number of posts you want to show in the 3 main screen sizes that exist, as well as the number of columns and rows.','yuzo') . '<br />' .
'<strong>✔️ '. __('Excerpt','yuzo') .'</strong>: '. __('Short text configuration of the article posts','yuzo') . '<br />' .
'<strong>✔️ '. __('Image','yuzo') .'</strong>: '. __('Image settings for each post','yuzo') . '<br />' .
'<strong>✔️ '. __('Meta','yuzo') .'</strong>: '. __('Select which goals you want to show in each post as: Date, last modified date, number of views, main category, author.','yuzo') . '<br />' .
'</p>'
        ) );
        $screen->add_help_tab( array(
            'id'      => 'yuzo_in_algorithm',
            'title'   => __('Algorithm','yuzo'),
            'content' =>
'<p>' .
'<strong>✔️ '. __('Algorithm type','yuzo') .'</strong>: '. __('There are two types of algorithm that Yuzo offers its users: a) Related post: It will show post related to your configuration b) List post: It will show a list of post with the variant you want.
Because Yuzo is multi instance, you can create several Yuzo with different algorithms for your needs.','yuzo') . '<br />' .
'<ul>' .
    '<li><strong>'. __('Related post','yuzo') .'</strong>: '. __('Calculate posts related to current content, there are several types of relationship:','yuzo') .
        '<ol>' .
            '<li><strong>'. __('Tags','yuzo') .'</strong>: '. __('Show posts that have the same tags as the current article, this shows them in order, that is, if you first have the \'seo\' tag then you will look for posts related to this tag, in case you don\'t have many results
will look for the following tag and so on until you get the appropriate number of posts.','yuzo') . '</li>' .
            '<li><strong>'. __('Categories','yuzo') .'</strong>: '. __('Show posts that have the same categories as the current article, this shows them in order, that is, if you first have the \'book\' category then you will look for posts related to this category, in case you don\'t have many results
will look for the following category and so on until you get the appropriate number of posts.','yuzo') . '</li>' .
            '<li><strong>'. __('Yuzo Relationship (presicion based on terms)','yuzo') .'</strong>: '. __('Over the years, I was able to understand optimally how to relate posts, it is an algorithm similar to Google, what it does is mix the taxonomies of the current posts (categories & tags) and perform
a query in which the posts that match appear first, this means by the best <strong>rank</strong>. This query is possibly the most accurate and also the lightest and fastest to calculate.','yuzo') . '</li>' .
            '<li><strong>'. __('Relation by coincidence of title of the post','yuzo') .'</strong>: '. __('Select the words of the title of the current post and make a query of the posts that match those words.','yuzo') . '</li>' .
        '</ol>' .
    '</li>' .
    '<li><strong>'. __('List post','yuzo') .'</strong>: '. __('Show a list of posts according to your requirement, there are several list variants:','yuzo') .
        '<ol>' .
            '<li><strong>'. __('Last posts','yuzo') .'</strong>: '. __('Show the latest general posts, you can also mix with different filters such as category, tags, etc..','yuzo') . '</li>' .
            '<li><strong>'. __('Most View','yuzo') .'</strong>: '. __('Show the posts seen by the Yuzo counter in a descending way.','yuzo') . '</li>' .
            '<li><strong>'. __('Most Popular (most Commented)','yuzo') .'</strong>: '. __('Show the most commented posts in descending order.','yuzo') . '</li>' .
            '<li><strong>'. __('Most Clicked','yuzo') .'</strong>: '. __('Show the most clicked posts of Yuzo, you can also add other filters to show according to your criteria.','yuzo') . '</li>' .
            '<li><strong>'. __('Rand','yuzo') .'</strong>: '. __('Show random posts of any category or tag.','yuzo') . '</li>' .
            '<li><strong>'. __('Alphabetically [A-Z]','yuzo') .'</strong>: '. __('Show a list of alphabetical posts from A to Z taking as reference the first letter of the post title.','yuzo') . '</li>' .
            '<li><strong>'. __('Alphabetically [Z-A]','yuzo') .'</strong>: '. __('Show a list of alphabetical posts from Z to A taking as reference the first letter of the post title.','yuzo') . '</li>' .
        '</ol>' .
    '</li>' .
'</ul>' .
'<strong>✔️ '. __('Time and space','yuzo') .'</strong>: '. __('Select from what time you want to show the posts, by default this \'All time\' but you can also choose: last week, last month, last year and a customizable.') . '<br />' .
'<strong>✔️ '. __('Ordering','yuzo') .'</strong>: '. __('(only for related posts) Choose from several order criteria such as: ID, author, title, date, last modification, random, most commented, most visited. Under these criteria you can also sort in ascending and descending order.') . '<br />' .
'<strong>✔️ '. __('If there is no related post, display','yuzo') .'</strong>: '. __('(only for related posts) If there is no result then it does not show anything, but it can also show random posts of the same type of post.') . '<br />' .
'<strong>✔️ '. __('Include post type','yuzo') .'</strong>: '. __('Select the type of posts you want to show either for lists or for related posts.') . '<br />' .
'<strong>✔️ '. __('What do you want to show? (only for list posts)','yuzo') .'</strong>: '. __('Present criteria / filters that you want to show in the list:') . '<br />' .
'<ul>' .
    '<ol>' .
        '<li><strong>'. __('Categories to include','yuzo') .'</strong>: '. __('Select the categories (terms) in several taxonomies that you want to show or enable in the list. Also select the logical operator on them <strong>OR</strong>(show whichever is selected) and <strong>AND</strong>(strictly all terms must match)','yuzo') . '</li>' .
        '<li><strong>'. __('Tags to include','yuzo') .'</strong>: '. __('Select the tags (terms) in several taxonomies that you want to show or enable in the list. Also select the logical operator on them <strong>OR</strong>(show whichever is selected) and <strong>AND</strong>(strictly all terms must match)','yuzo') . '</li>' .
        '<li><strong>'. __('↑ Relationship between Categories and Tags ↓','yuzo') .'</strong>: '. __('What logical operation between categories and tags do you want to join. <strong>OR</strong>(show whichever is selected) and <strong>AND</strong>(strictly all terms must match)','yuzo') . '</li>' .
    '</ol>' .
'</ul>' .
'<strong>✔️ '. __('Exclude','yuzo') . '</strong><br />' .
'<ul>' .
    '<ol>' .
        '<li><strong>'. __('Exclude categories','yuzo') .'</strong>: '. __('Select the categories (terms) you want to exclude in the posts.','yuzo') . '</li>' .
        '<li><strong>'. __('Exclude tags','yuzo') .'</strong>: '. __('Select the tags (terms) you want to exclude in the posts.','yuzo') . '</li>' .
        '<li><strong>'. __('Exclude post by ID','yuzo') .'</strong>: '. __('Write the IDs separated by a commas \',\' which you do not want to be shown to the post.','yuzo') . '</li>' .
        '<li><strong>'. __('Not appear inside','yuzo') .'</strong>: '. __('Write the ID separated by a commas \',\' posts you want to Yuzo not appear.','yuzo') . '</li>' .
    '</ol>' .
'</ul>' .
'<strong>✔️ '. __('Strict','yuzo') . '</strong><br />' .
'<ul>' .
    '<ol>' .
        '<li><strong>'. __('Show only the same post type','yuzo') .'</strong>: '. __('Only show posts that have the same type of posts as current article.','yuzo') . '</li>' .
        '<li><strong>'. __('Related to the title (only for relate posts)','yuzo') .'</strong>: '. __('You can also add a relationship by title to the current relationship different from the based on titles.','yuzo') . '</li>' .
        '<li><strong>'. __('Settings for private post','yuzo') .'</strong>: '. __('Configuration for private posts, there are variants that you can play for your purposes in private posts.','yuzo') . '</li>' .
        '<li><strong>'. __('Show only posts included from the Metabox','yuzo') .'</strong>: '. __('Only posts added manually in this configuration will be displayed. Posts are added within the edition of the article in the Yuzo metabox.','yuzo') . '</li>' .
    '</ol>' .
'</ul>' .
'</p>'
        ) );
        $screen->add_help_tab( array(
            'id'      => 'yuzo_display',
            'title'   => __('Display','yuzo'),
            'content' =>
'<p>' .
'<strong>✔️ '. __('Show only in post type','yuzo') .'</strong>: '. __('They are the types posts where they will be shown. Example: Posts, Pages, Movies...','yuzo') . '<br />' .
'<strong>✔️ '. __('Categories will appear','yuzo') .'</strong>: '. __('Select the categories where you want to show the posts, you can also select all the categories of a taxonomy.','yuzo') . '<br />' .
'<strong>✔️ '. __('Places on the page','yuzo') .'</strong>: '. __('Show the posts in different places of your web page (home, archive, etc ...), this depends a lot on your Theme.','yuzo') . '<br />' .
'<strong>✔️ '. __('Show only in post specific','yuzo') .'</strong>: '. __('Put IDs posts where you want ONLY the posts are displayed (Separated by commas).','yuzo') . '<br />' .
'</p>'
));
$screen->add_help_tab( array(
            'id'      => 'yuzo_in_ie',
            'title'   => __('Export/Import','yuzo'),
            'content' => '<p>' .
'<strong>'. __('Export/Import','yuzo') . '</strong>: ' . __('Inside the empty box paste the configuration of a Yuzo that you want to restore, once you paste it you must press the \'import\' button with this the values ​​are already restored.','yuzo') . '<br />' .
'<strong>'. __('Migrate post manuals from version Pro 0.99 & Lite 5.12 to → 6.0','yuzo') . '</strong>: '
. __('Press the button \'Run\' to migrate to this yuzo the previously added posts manually. This migration is individual for each Yuzo, if you create 3 Yuzo you must do the migration at 3. With this you can decide that Yuzo has old posts manually and that Yuzo you don\'t want to migrate them.','yuzo') . '<br />' .
'</p>'
        ) );
    }
}