<?php
/**
 * @since       6.0         2019-04-13 17:27:55     Release
 * @package     YUZO
 * @subpackage  YUZO/Core
 */
// ───────────────────────────
namespace YUZO\Core;
// ───────────────────────────

if( ! class_exists( 'Yuzo_Activator' ) ){
/*
|--------------------------------------------------------------------------
| Fired during plugin activation
|--------------------------------------------------------------------------
|
| This class defines all code necessary to run during the plugin's activation.
|
*/
class Yuzo_Activator{

    /**
     * Perform necessary processes when activating the plugin
     * @return void
     */
    public static function activate() {
        // Registers the configurations
        self::register_config();
        // Registers the plugin tables
        self::create_tables();
        // Registra el yuzo por defecto
        self::register_default_post();
    }

    /**
     * Save and show installation errors
     *
     * If you have problems with characters that escape the installation,
     * with this function you can save the error to know which characters
     * are escaping.
     *
     * @since       6.0   2019-04-13 17:28:15     Release
     * @static
     * @access      public
     * @see         Yuzo_Admin -> admin_save_and_display_error_to_install
     *
     * @param boolean Action to save the escaped string or show it
     * @return void
     */
    public static function save_and_display_error( $save_error = true ){
        if( true === $save_error ){
            update_option( 'plugin_error',  ob_get_contents() );
        }elseif( false === $save_error ){
            echo get_option('plugin_error');
        }
    }

    /**
     * Creation of plugin tables
     *
     * @since       6.0     2019-04-13 17:28:33     Release
     * @since       6.0.2   2019-07-12 06:23:52     You run the 2 tables separately to avoid fewer errors
     * @since       6.0.9   2019-07-21 21:08:36     New price field added
     * @since       6.0.9.8 2019-08-28              The field &#39;level_click&#39; was added in the yuzoclicks table
     *                                              This will allow to know the true value of the click in its time
     *
     * @static
     * @access public
     *
     * @return void
     */
    public static function create_tables(){

        global $wpdb;

        $prefix             = $wpdb->prefix;
        $current_db_version = YUZO_VERSION_DB;
        $table_name         = $wpdb->prefix . 'yuzoclicks';
        $table_name2        = $wpdb->prefix . 'yuzoviews';
        //$table_name2        = $wpdb->prefix . 'yuzo_views';

        $installed_ver      = yuzo_get_option( YUZO_ID .'-config', 'version_db' );
        $add_table_db       = false;

        if ( $installed_ver != $current_db_version ) {

            /**
            * We'll set the default character set and collation for this table.
            * If we don't do this, some characters could end up being converted
            * to just ?'s when saved in our table.
            */

            $charset_collate = '';

            if ( ! empty( $wpdb->charset ) ) {
                $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
            }

            if ( ! empty( $wpdb->collate ) ) {
                $charset_collate .= " COLLATE {$wpdb->collate}";
            }

            // ─── We validate if the table exists ────────
            $row = $wpdb->query(  "SHOW TABLES LIKE '$table_name'"  );
            if( empty( $row ) ){
                $sql = "CREATE TABLE $table_name (

                            ID int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID autoincrement',
                            post_id int(15) NOT NULL COMMENT 'ID post',
                            date_click datetime NOT NULL COMMENT 'Date and time when the click was made',
                            timestamp_click int(15) NOT NULL COMMENT 'Timestamp when the click was made',
                            ip varchar(100) NOT NULL COMMENT 'Ip of the click',
                            la varchar(100) NOT NULL COMMENT 'Latitude of the click',
                            lo varchar(100) NOT NULL COMMENT 'Longitude of the click',
                            country varchar(100) NOT NULL COMMENT 'Country name of the click',
                            country_code varchar(100) NOT NULL COMMENT 'Country code of the click',
                            region varchar(100) NOT NULL COMMENT  'Region code of the click',
                            city varchar(100) NOT NULL COMMENT 'City code of the click',
                            device varchar(10) NOT NULL COMMENT 'Device that was clicked (d=desktop,m=mobile,t=tablet)',

                        UNIQUE KEY ID (ID)
                ) $charset_collate;";

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                @dbDelta( $sql );
            }

            $row = $wpdb->query(  "SHOW TABLES LIKE '$table_name2'"  );
            if( empty( $row ) ){
                $sql = "CREATE TABLE $table_name2 (
                            ID int(11) NOT NULL AUTO_INCREMENT,
                            post_id int(15) NOT NULL,
                            views int(14) NOT NULL,
                            last_viewed datetime NOT NULL,
                            modified int(12) NULL,
                        UNIQUE KEY ID (ID)
                    ) $charset_collate;";
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                @dbDelta( $sql );
            }

            /**
             * @since 6.0   2019-05-03 01:40:21     Release
             */
            $row = $wpdb->query(  "SHOW COLUMNS FROM $table_name LIKE 'yuzo_list_id'"  );
            if( empty( $row ) ){
                $wpdb->query( "ALTER TABLE $table_name ADD url varchar(250) NOT NULL" );
                $wpdb->query( "ALTER TABLE $table_name ADD where_is varchar(30) NULL" );
                $wpdb->query( "ALTER TABLE $table_name ADD browser_details varchar(500) NULL" );
                $wpdb->query( "ALTER TABLE $table_name ADD type_click CHAR(20) NOT NULL DEFAULT 'c' COMMENT 'c=conten,s=shortcode,w=widget'" );
                $wpdb->query( "ALTER TABLE $table_name ADD post_from CHAR(20) NULL COMMENT 'Save the ID of the post from where the click was made'" );
                $wpdb->query( "ALTER TABLE $table_name ADD yuzo_list_id int(11) NOT NULL COMMENT 'Save the ID yuzo'" );
            }

            /**
             * @since 6.0.9     2019-07-20 03:01:50     The price per click column is added
             */
            $row = $wpdb->query(  "SHOW COLUMNS FROM $table_name LIKE 'price_per_click'"  );
            if( empty( $row ) ){
                $wpdb->query( "ALTER TABLE `$table_name` ADD `price_per_click` DECIMAL(10,2) NULL DEFAULT '0.00' COMMENT 'Calculate the price per click based on the item level';" );
            }

            /**
             * @since 6.0.9.8   2019-08-28      The level_click column is added
             */
            $row = $wpdb->query(  "SHOW COLUMNS FROM $table_name LIKE 'level_click'"  );
            if( empty( $row ) ){
                $wpdb->query( "ALTER TABLE `$table_name` ADD `level_click` INT(2) NULL DEFAULT '1' COMMENT 'Level of the article that was clicked, this helps calculate how much each click costs';" );
            }

            yuzo_update_option( YUZO_ID .'-config',  $current_db_version , 'version_db' );
        }

    }


    /**
     * Verify if there is a new version of the table
     *
     * @since       6.0     2019-03-21 05:00:47     Release
     * @static
     * @access      public
     *
     * @return boolean
     */
    public static function if_changes_in_table_db(){

        // Get the current installed version
        $settings = yuzo_get_option( YUZO_ID .'-config' );
        $current_version_db = $settings->version_db;
        // Current version of the plugin
        $version_db         = YUZO_VERSION_DB;

        if( $current_version_db != $version_db ){
            return $version_db;
        }else{
            return false;
        }
    }

    /**
     * Registers the basic plugin information
     *
     * @since   6.0.9   2019-07-21  Release
     * @since   6.1.52  2020-02-09  News values
     * @return  void
     */
    public static function register_config(){
        $config = get_option( YUZO_ID .'-config' );
        if( empty( $config['date_install'] ) ){
            update_option( YUZO_ID.'-config', [
                'date_last_update' => date("Y-m-d H:i:s"),
                'date_install'     => date("Y-m-d H:i:s"),
                'update_count'     => ! isset($config['update_count']) ? 0 : (int)$config['update_count'] + 1,
                'version_db'       => '',                                                                                                  // It must be empty to create the tables
                'nonce_api'        => base64_encode(get_option(yuzo_sanitize_for_string(constant( strtoupper(YUZO_ID) . '_' . 'API')))),
                'version'          => YUZO_VERSION,
                'id'               => YUZO_ID,
                ]
            );
        }
    }

    /**
     * Update the date of the last update
     *
     * @since   6.1.40  2020-01-12  Release
     * @since   6.1.52  2020-02-09  News values
     * @return  void
     */
    public static function update_config(){
        $config = get_option( YUZO_ID .'-config' );
        yuzo_update_option('yuzo-config', date("Y-m-d H:i:s"), 'date_last_update');
        yuzo_update_option('yuzo-config', ( ! isset($config['update_count']) ? 0 : (int)$config['update_count'] + 1 ), 'update_count');
        yuzo_update_option('yuzo-config', YUZO_VERSION, 'version');
        yuzo_update_option('yuzo-config', base64_encode( get_option(yuzo_sanitize_for_string(constant( strtoupper(YUZO_ID) . '_' . 'API')))), 'nonce_api');
        yuzo_update_option('yuzo-config', '-', 'info_install' );
        //self::yuzo_scheduled();
    }

    /**
     * Document update
     *
     * @since   6.1.40  2020-01-12  Release
     * @return  void
     */
    public static function yuzo_scheduled(){
        /* if( ! wp_next_scheduled( 'yuzo_add_every_day_event' ) ){
            wp_schedule_event( time(), 'every_day_yuzo_1', 'yuzo_add_every_day_event' );
        } */
        //require_once YUZO_PATH . 'include/functions/helper.php';
        //pf_global_update_option( YUZO_ID );
    }

    /**
     * When the plugin is installed, it registers by default a tyipo yuzo post
     *
     * @since   6.0.9   2019-07-21 21:10:52     Release
     * @since   6.0.9.8 2019-08-28              4 new posts were added due to defects for better understanding
     * @return  void
     */
    public static function register_default_post(){
        global $wpdb;
        $prefix = $wpdb->prefix;
        $r = $wpdb->get_row("SELECT count(*) t_cpt FROM {$prefix}posts WHERE post_type = 'yuzo'");
        if( $r->t_cpt == 0 ){

            // ─── Various variables ────────
            $date   = date("Y-m-d H:i:s");
            $author = get_current_user_id();

            // ─── 1) Related post based on Tags (Grid Template) ────────
            $title  = __('Related post based on Tags','yuzo');
            $slug   = \str_replace(" ","-",$title);
            $r = $wpdb->query("INSERT INTO {$prefix}posts ( ID, post_author, post_date, post_date_gmt, post_content, post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                    ) values (0,$author,'$date','$date','','$title','','publish','closed','closed','','$slug','','','$date','$date','',0,'',0,'yuzo','',0 )" );
            if( $r ){
                // Add post meta
                $post_id = $wpdb->insert_id;
                $value   = 'a:29:{s:19:"related_post_active";s:1:"1";s:15:"fieldset_design";a:2:{s:13:"panels-design";a:31:{s:5:"title";s:26:"<h4>You may also like</h4>";s:10:"where_show";s:7:"content";s:16:"content_location";s:18:"below-post-content";s:25:"content_appende_and_order";a:1:{s:17:"location_priority";s:2:"10";}s:31:"content_appende_paragraph_order";a:1:{s:18:"location_paragraph";s:1:"2";}s:8:"template";s:4:"yuzo";s:13:"template_type";s:4:"grid";s:16:"template_colours";s:5:"set-1";s:16:"template_color_1";s:7:"#f1f1f1";s:16:"template_color_2";s:7:"#2f7eff";s:21:"design_text_font_size";a:1:{s:5:"width";s:0:"";}s:23:"design_text_line_height";a:1:{s:5:"width";s:0:"";}s:23:"design_text_font_weight";s:3:"100";s:23:"design_text_color_hover";a:2:{s:5:"color";s:4:"#000";s:5:"hover";s:0:"";}s:13:"design_screen";s:7:"desktop";s:20:"design_screen_mobile";a:2:{s:28:"design_screen_mobile_columns";s:1:"2";s:25:"design_screen_mobile_rows";s:1:"2";}s:20:"design_screen_tablet";a:2:{s:28:"design_screen_tablet_columns";s:1:"3";s:25:"design_screen_tablet_rows";s:1:"1";}s:21:"design_screen_desktop";a:2:{s:29:"design_screen_desktop_columns";s:1:"3";s:26:"design_screen_desktop_rows";s:1:"2";}s:19:"design_show_excerpt";s:1:"0";s:18:"design_text_length";s:2:"80";s:19:"design_text_content";s:12:"from_content";s:20:"template_show_imagen";s:1:"1";s:21:"design_thumbnail_size";s:6:"medium";s:17:"design_image_size";s:3:"1-1";s:12:"design_metas";a:1:{s:8:"disabled";a:6:{s:9:"meta-date";s:4:"Date";s:16:"meta-date-update";s:16:"Date last update";s:13:"meta-category";s:8:"Category";s:9:"meta-view";s:4:"View";s:11:"meta-author";s:6:"Author";s:12:"meta-comment";s:7:"Comment";}}s:17:"design_box_margin";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:18:"design_box_padding";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:21:"design_box_background";s:0:"";s:19:"design_post_spacing";a:1:{s:5:"width";s:0:"";}s:17:"design_html_above";s:0:"";s:17:"design_html_below";s:0:"";}s:23:"fieldset_design_preview";a:1:{s:14:"design_screen2";s:7:"desktop";}}s:12:"related_type";s:7:"related";s:9:"list_post";s:9:"last-post";s:10:"related_to";s:4:"tags";s:8:"order_by";a:2:{s:5:"order";s:4:"desc";s:2:"by";s:8:"modified";}s:18:"relation_no_result";s:16:"random_based_cpt";s:14:"time_and_space";a:1:{s:5:"range";s:9:"all-along";}s:14:"cpt_to_related";a:1:{i:0;s:4:"post";}s:29:"include_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{i:0;s:13:"uncategorized";}}s:38:"include_taxonomy_hierarchical_operator";a:1:{s:25:"include_taxonomy_relation";s:2:"or";}s:27:"taxonomies_cat_tag_relation";s:2:"or";s:32:"related_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:42:"include_taxonomy_not_hierarchical_operator";a:1:{s:28:"include_taxonomy_no_relation";s:2:"or";}s:32:"exclude_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:15:"exclude_post_id";s:0:"";s:17:"not_appear_inside";s:0:"";s:29:"related_post_only_add_metabox";s:0:"";s:22:"show_only_in_type_post";a:1:{i:0;s:4:"post";}s:34:"show_only_in_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:31:"show_only_in_places_on_the_page";a:1:{i:0;s:7:"archive";}s:24:"display_as_list_template";s:0:"";s:28:"display_only_specific_postid";s:0:"";s:10:"group_list";s:0:"";s:13:"group_related";s:0:"";s:13:"group_exclude";s:0:"";s:29:"exclude_taxonomy_hierarchical";s:0:"";s:12:"group_strict";s:0:"";s:18:"yuzo_msg_show_cpt1";s:0:"";}';
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo','$value')");
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo_related_post_active','1')");
            }

            // ─── 2) The most popular (commented) on the month after the first paragraph (Inline Template) ────────
            $title  = __('The most popular (commented) on the month after the first paragraph','yuzo');
            $slug   = \str_replace(" ","-",$title);
            $r = $wpdb->query("INSERT INTO {$prefix}posts ( ID, post_author, post_date, post_date_gmt, post_content, post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                    ) values (0,$author,'$date','$date','','$title','','publish','closed','closed','','$slug','','','$date','$date','',0,'',0,'yuzo','',0 )" );
            if( $r ){
                // Add post meta
                $post_id = $wpdb->insert_id;
                $value   = 'a:29:{s:19:"related_post_active";s:1:"1";s:15:"fieldset_design";a:2:{s:13:"panels-design";a:31:{s:5:"title";s:0:"";s:10:"where_show";s:7:"content";s:16:"content_location";s:20:"top-paragraph-number";s:25:"content_appende_and_order";a:1:{s:17:"location_priority";s:2:"10";}s:31:"content_appende_paragraph_order";a:1:{s:18:"location_paragraph";s:1:"2";}s:8:"template";s:7:"yuzo-i2";s:13:"template_type";s:6:"inline";s:16:"template_colours";s:5:"set-1";s:16:"template_color_1";s:7:"#f1f1f1";s:16:"template_color_2";s:7:"#2f7eff";s:21:"design_text_font_size";a:1:{s:5:"width";s:0:"";}s:23:"design_text_line_height";a:1:{s:5:"width";s:0:"";}s:23:"design_text_font_weight";s:3:"100";s:23:"design_text_color_hover";a:2:{s:5:"color";s:4:"#000";s:5:"hover";s:0:"";}s:13:"design_screen";s:6:"tablet";s:20:"design_screen_mobile";a:2:{s:28:"design_screen_mobile_columns";s:1:"2";s:25:"design_screen_mobile_rows";s:1:"2";}s:20:"design_screen_tablet";a:2:{s:28:"design_screen_tablet_columns";s:1:"3";s:25:"design_screen_tablet_rows";s:1:"1";}s:21:"design_screen_desktop";a:2:{s:29:"design_screen_desktop_columns";s:1:"3";s:26:"design_screen_desktop_rows";s:1:"2";}s:19:"design_show_excerpt";s:0:"";s:18:"design_text_length";s:2:"80";s:19:"design_text_content";s:12:"from_content";s:20:"template_show_imagen";s:1:"1";s:21:"design_thumbnail_size";s:6:"medium";s:17:"design_image_size";s:3:"1-1";s:12:"design_metas";a:1:{s:8:"disabled";a:6:{s:9:"meta-date";s:4:"Date";s:16:"meta-date-update";s:16:"Date last update";s:13:"meta-category";s:8:"Category";s:9:"meta-view";s:4:"View";s:11:"meta-author";s:6:"Author";s:12:"meta-comment";s:7:"Comment";}}s:17:"design_box_margin";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:18:"design_box_padding";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:21:"design_box_background";s:0:"";s:19:"design_post_spacing";a:1:{s:5:"width";s:0:"";}s:17:"design_html_above";s:0:"";s:17:"design_html_below";s:0:"";}s:23:"fieldset_design_preview";a:1:{s:14:"design_screen2";s:6:"tablet";}}s:12:"related_type";s:4:"list";s:9:"list_post";s:12:"most-popular";s:10:"related_to";s:4:"tags";s:8:"order_by";a:2:{s:5:"order";s:4:"desc";s:2:"by";s:8:"modified";}s:18:"relation_no_result";s:16:"random_based_cpt";s:14:"time_and_space";a:1:{s:5:"range";s:9:"last-week";}s:14:"cpt_to_related";a:1:{i:0;s:4:"post";}s:29:"include_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:38:"include_taxonomy_hierarchical_operator";a:1:{s:25:"include_taxonomy_relation";s:2:"or";}s:27:"taxonomies_cat_tag_relation";s:2:"or";s:32:"related_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:42:"include_taxonomy_not_hierarchical_operator";a:1:{s:28:"include_taxonomy_no_relation";s:2:"or";}s:32:"exclude_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:15:"exclude_post_id";s:0:"";s:17:"not_appear_inside";s:0:"";s:29:"related_post_only_add_metabox";s:0:"";s:22:"show_only_in_type_post";a:1:{i:0;s:4:"post";}s:34:"show_only_in_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:31:"show_only_in_places_on_the_page";a:1:{i:0;s:7:"archive";}s:24:"display_as_list_template";s:0:"";s:28:"display_only_specific_postid";s:0:"";s:10:"group_list";s:0:"";s:13:"group_related";s:0:"";s:13:"group_exclude";s:0:"";s:29:"exclude_taxonomy_hierarchical";s:0:"";s:12:"group_strict";s:0:"";s:18:"yuzo_msg_show_cpt1";s:0:"";}';
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo','$value')");
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo_related_post_active','1')");
            }

            // ─── 3) Current Trend before last paragraph (Inline Template) ────────
            $title  = __('Current Trend before last paragraph','yuzo');
            $slug   = \str_replace(" ","-",$title);
            $r = $wpdb->query("INSERT INTO {$prefix}posts ( ID, post_author, post_date, post_date_gmt, post_content, post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                    ) values (0,$author,'$date','$date','','$title','','publish','closed','closed','','$slug','','','$date','$date','',0,'',0,'yuzo','',0 )" );
            if( $r ){
                // Add post meta
                $post_id = $wpdb->insert_id;
                $value   = 'a:29:{s:19:"related_post_active";s:1:"1";s:15:"fieldset_design";a:2:{s:13:"panels-design";a:31:{s:5:"title";s:10:"Trend 🔥";s:10:"where_show";s:7:"content";s:16:"content_location";s:23:"bottom-paragraph-number";s:25:"content_appende_and_order";a:1:{s:17:"location_priority";s:2:"10";}s:31:"content_appende_paragraph_order";a:1:{s:18:"location_paragraph";s:1:"1";}s:8:"template";s:9:"default-i";s:13:"template_type";s:6:"inline";s:16:"template_colours";s:5:"set-1";s:16:"template_color_1";s:7:"#f1f1f1";s:16:"template_color_2";s:7:"#2f7eff";s:21:"design_text_font_size";a:1:{s:5:"width";s:2:"18";}s:23:"design_text_line_height";a:1:{s:5:"width";s:2:"20";}s:23:"design_text_font_weight";s:3:"100";s:23:"design_text_color_hover";a:2:{s:5:"color";s:4:"#000";s:5:"hover";s:0:"";}s:13:"design_screen";s:6:"mobile";s:20:"design_screen_mobile";a:2:{s:28:"design_screen_mobile_columns";s:1:"2";s:25:"design_screen_mobile_rows";s:1:"2";}s:20:"design_screen_tablet";a:2:{s:28:"design_screen_tablet_columns";s:1:"3";s:25:"design_screen_tablet_rows";s:1:"1";}s:21:"design_screen_desktop";a:2:{s:29:"design_screen_desktop_columns";s:1:"3";s:26:"design_screen_desktop_rows";s:1:"2";}s:19:"design_show_excerpt";s:0:"";s:18:"design_text_length";s:2:"80";s:19:"design_text_content";s:12:"from_content";s:20:"template_show_imagen";s:1:"1";s:21:"design_thumbnail_size";s:6:"medium";s:17:"design_image_size";s:3:"1-1";s:12:"design_metas";a:1:{s:8:"disabled";a:6:{s:9:"meta-date";s:4:"Date";s:16:"meta-date-update";s:16:"Date last update";s:13:"meta-category";s:8:"Category";s:9:"meta-view";s:4:"View";s:11:"meta-author";s:6:"Author";s:12:"meta-comment";s:7:"Comment";}}s:17:"design_box_margin";a:5:{s:3:"top";s:2:"20";s:5:"right";s:0:"";s:6:"bottom";s:2:"20";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:18:"design_box_padding";a:5:{s:3:"top";s:2:"20";s:5:"right";s:0:"";s:6:"bottom";s:2:"20";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:21:"design_box_background";s:0:"";s:19:"design_post_spacing";a:1:{s:5:"width";s:0:"";}s:17:"design_html_above";s:0:"";s:17:"design_html_below";s:0:"";}s:23:"fieldset_design_preview";a:1:{s:14:"design_screen2";s:6:"mobile";}}s:12:"related_type";s:4:"list";s:9:"list_post";s:9:"most-view";s:10:"related_to";s:4:"tags";s:8:"order_by";a:2:{s:5:"order";s:4:"desc";s:2:"by";s:8:"modified";}s:18:"relation_no_result";s:16:"random_based_cpt";s:14:"time_and_space";a:1:{s:5:"range";s:9:"last-week";}s:14:"cpt_to_related";a:1:{i:0;s:4:"post";}s:29:"include_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:38:"include_taxonomy_hierarchical_operator";a:1:{s:25:"include_taxonomy_relation";s:2:"or";}s:27:"taxonomies_cat_tag_relation";s:2:"or";s:32:"related_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:42:"include_taxonomy_not_hierarchical_operator";a:1:{s:28:"include_taxonomy_no_relation";s:2:"or";}s:32:"exclude_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:15:"exclude_post_id";s:0:"";s:17:"not_appear_inside";s:0:"";s:29:"related_post_only_add_metabox";s:0:"";s:22:"show_only_in_type_post";a:1:{i:0;s:4:"post";}s:34:"show_only_in_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:31:"show_only_in_places_on_the_page";a:1:{i:0;s:7:"archive";}s:24:"display_as_list_template";s:0:"";s:28:"display_only_specific_postid";s:0:"";s:10:"group_list";s:0:"";s:13:"group_related";s:0:"";s:13:"group_exclude";s:0:"";s:29:"exclude_taxonomy_hierarchical";s:0:"";s:12:"group_strict";s:0:"";s:18:"yuzo_msg_show_cpt1";s:0:"";}';
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo','$value')");
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo_related_post_active','1')");
            }

            // ─── 4) Widget – The latest posts (List Template with image) ────────
            $title  = __('Widget – The latest posts','yuzo');
            $slug   = \str_replace(" ","-",$title);
            $r = $wpdb->query("INSERT INTO {$prefix}posts ( ID, post_author, post_date, post_date_gmt, post_content, post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                    ) values (0,$author,'$date','$date','','$title','','publish','closed','closed','','$slug','','','$date','$date','',0,'',0,'yuzo','',0 )" );
            if( $r ){
                // Add post meta
                $post_id = $wpdb->insert_id;
                $value   = 'a:29:{s:19:"related_post_active";s:1:"1";s:15:"fieldset_design";a:2:{s:13:"panels-design";a:31:{s:5:"title";s:19:"(Title from widget)";s:10:"where_show";s:6:"widget";s:16:"content_location";s:18:"below-post-content";s:25:"content_appende_and_order";a:1:{s:17:"location_priority";s:2:"10";}s:31:"content_appende_paragraph_order";a:1:{s:18:"location_paragraph";s:1:"2";}s:8:"template";s:20:"default-image-medium";s:13:"template_type";s:4:"list";s:16:"template_colours";s:5:"set-1";s:16:"template_color_1";s:7:"#f1f1f1";s:16:"template_color_2";s:7:"#2f7eff";s:21:"design_text_font_size";a:1:{s:5:"width";s:0:"";}s:23:"design_text_line_height";a:1:{s:5:"width";s:0:"";}s:23:"design_text_font_weight";s:3:"100";s:23:"design_text_color_hover";a:2:{s:5:"color";s:4:"#000";s:5:"hover";s:0:"";}s:13:"design_screen";s:6:"mobile";s:20:"design_screen_mobile";a:2:{s:28:"design_screen_mobile_columns";s:1:"2";s:25:"design_screen_mobile_rows";s:1:"2";}s:20:"design_screen_tablet";a:2:{s:28:"design_screen_tablet_columns";s:1:"3";s:25:"design_screen_tablet_rows";s:1:"1";}s:21:"design_screen_desktop";a:2:{s:29:"design_screen_desktop_columns";s:1:"3";s:26:"design_screen_desktop_rows";s:1:"2";}s:19:"design_show_excerpt";s:0:"";s:18:"design_text_length";s:2:"80";s:19:"design_text_content";s:12:"from_content";s:20:"template_show_imagen";s:1:"1";s:21:"design_thumbnail_size";s:6:"medium";s:17:"design_image_size";s:3:"1-1";s:12:"design_metas";a:2:{s:7:"enabled";a:1:{s:13:"meta-category";s:8:"Category";}s:8:"disabled";a:5:{s:9:"meta-date";s:4:"Date";s:16:"meta-date-update";s:16:"Date last update";s:9:"meta-view";s:4:"View";s:11:"meta-author";s:6:"Author";s:12:"meta-comment";s:7:"Comment";}}s:17:"design_box_margin";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:18:"design_box_padding";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:21:"design_box_background";s:0:"";s:19:"design_post_spacing";a:1:{s:5:"width";s:0:"";}s:17:"design_html_above";s:0:"";s:17:"design_html_below";s:0:"";}s:23:"fieldset_design_preview";a:1:{s:14:"design_screen2";s:6:"mobile";}}s:12:"related_type";s:4:"list";s:9:"list_post";s:9:"last-post";s:10:"related_to";s:4:"tags";s:8:"order_by";a:2:{s:5:"order";s:4:"desc";s:2:"by";s:8:"modified";}s:18:"relation_no_result";s:16:"random_based_cpt";s:14:"time_and_space";a:1:{s:5:"range";s:9:"all-along";}s:14:"cpt_to_related";a:1:{i:0;s:4:"post";}s:29:"include_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:38:"include_taxonomy_hierarchical_operator";a:1:{s:25:"include_taxonomy_relation";s:2:"or";}s:27:"taxonomies_cat_tag_relation";s:2:"or";s:32:"related_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:42:"include_taxonomy_not_hierarchical_operator";a:1:{s:28:"include_taxonomy_no_relation";s:2:"or";}s:32:"exclude_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:15:"exclude_post_id";s:0:"";s:17:"not_appear_inside";s:0:"";s:29:"related_post_only_add_metabox";s:0:"";s:22:"show_only_in_type_post";a:1:{i:0;s:4:"post";}s:34:"show_only_in_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:31:"show_only_in_places_on_the_page";a:1:{i:0;s:7:"archive";}s:24:"display_as_list_template";s:0:"";s:28:"display_only_specific_postid";s:0:"";s:10:"group_list";s:0:"";s:13:"group_related";s:0:"";s:13:"group_exclude";s:0:"";s:29:"exclude_taxonomy_hierarchical";s:0:"";s:12:"group_strict";s:0:"";s:18:"yuzo_msg_show_cpt1";s:0:"";}';
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo','$value')");
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo_related_post_active','1')");
            }

            // ─── 5) Widget – The most popular of the last week (List Template) ────────
            $title  = __('Widget – The most popular of the last week','yuzo');
            $slug   = \str_replace(" ","-",$title);
            $r = $wpdb->query("INSERT INTO {$prefix}posts ( ID, post_author, post_date, post_date_gmt, post_content, post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                    ) values (0,$author,'$date','$date','','$title','','publish','closed','closed','','$slug','','','$date','$date','',0,'',0,'yuzo','',0 )" );
            if( $r ){
                // Add post meta
                $post_id = $wpdb->insert_id;
                $value   = 'a:29:{s:19:"related_post_active";s:1:"1";s:15:"fieldset_design";a:2:{s:13:"panels-design";a:31:{s:5:"title";s:19:"(Title from widget)";s:10:"where_show";s:6:"widget";s:16:"content_location";s:18:"below-post-content";s:25:"content_appende_and_order";a:1:{s:17:"location_priority";s:2:"10";}s:31:"content_appende_paragraph_order";a:1:{s:18:"location_paragraph";s:1:"2";}s:8:"template";s:20:"default-image-medium";s:13:"template_type";s:4:"list";s:16:"template_colours";s:5:"set-1";s:16:"template_color_1";s:7:"#f1f1f1";s:16:"template_color_2";s:7:"#2f7eff";s:21:"design_text_font_size";a:1:{s:5:"width";s:0:"";}s:23:"design_text_line_height";a:1:{s:5:"width";s:0:"";}s:23:"design_text_font_weight";s:3:"100";s:23:"design_text_color_hover";a:2:{s:5:"color";s:4:"#000";s:5:"hover";s:0:"";}s:13:"design_screen";s:7:"desktop";s:20:"design_screen_mobile";a:2:{s:28:"design_screen_mobile_columns";s:1:"2";s:25:"design_screen_mobile_rows";s:1:"2";}s:20:"design_screen_tablet";a:2:{s:28:"design_screen_tablet_columns";s:1:"3";s:25:"design_screen_tablet_rows";s:1:"1";}s:21:"design_screen_desktop";a:2:{s:29:"design_screen_desktop_columns";s:1:"3";s:26:"design_screen_desktop_rows";s:1:"2";}s:19:"design_show_excerpt";s:1:"0";s:18:"design_text_length";s:2:"80";s:19:"design_text_content";s:12:"from_content";s:20:"template_show_imagen";s:1:"1";s:21:"design_thumbnail_size";s:6:"medium";s:17:"design_image_size";s:3:"1-1";s:12:"design_metas";a:2:{s:7:"enabled";a:2:{s:13:"meta-category";s:8:"Category";s:12:"meta-comment";s:7:"Comment";}s:8:"disabled";a:4:{s:9:"meta-date";s:4:"Date";s:16:"meta-date-update";s:16:"Date last update";s:9:"meta-view";s:4:"View";s:11:"meta-author";s:6:"Author";}}s:17:"design_box_margin";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:18:"design_box_padding";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:21:"design_box_background";s:0:"";s:19:"design_post_spacing";a:1:{s:5:"width";s:0:"";}s:17:"design_html_above";s:0:"";s:17:"design_html_below";s:0:"";}s:23:"fieldset_design_preview";a:1:{s:14:"design_screen2";s:7:"desktop";}}s:12:"related_type";s:4:"list";s:9:"list_post";s:12:"most-popular";s:10:"related_to";s:4:"tags";s:8:"order_by";a:2:{s:5:"order";s:4:"desc";s:2:"by";s:8:"modified";}s:18:"relation_no_result";s:16:"random_based_cpt";s:14:"time_and_space";a:1:{s:5:"range";s:9:"last-week";}s:14:"cpt_to_related";a:1:{i:0;s:4:"post";}s:29:"include_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:38:"include_taxonomy_hierarchical_operator";a:1:{s:25:"include_taxonomy_relation";s:2:"or";}s:27:"taxonomies_cat_tag_relation";s:2:"or";s:32:"related_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:42:"include_taxonomy_not_hierarchical_operator";a:1:{s:28:"include_taxonomy_no_relation";s:2:"or";}s:32:"exclude_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:15:"exclude_post_id";s:0:"";s:17:"not_appear_inside";s:0:"";s:29:"related_post_only_add_metabox";s:0:"";s:22:"show_only_in_type_post";a:1:{i:0;s:4:"post";}s:34:"show_only_in_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:31:"show_only_in_places_on_the_page";a:1:{i:0;s:7:"archive";}s:24:"display_as_list_template";s:0:"";s:28:"display_only_specific_postid";s:0:"";s:10:"group_list";s:0:"";s:13:"group_related";s:0:"";s:13:"group_exclude";s:0:"";s:29:"exclude_taxonomy_hierarchical";s:0:"";s:12:"group_strict";s:0:"";s:18:"yuzo_msg_show_cpt1";s:0:"";}';
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo','$value')");
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo_related_post_active','1')");
            }

            // ─── 6) Widget – Most viewed of the month (List Template) ────────
            $title  = __('Widget – Most viewed of the month','yuzo');
            $slug   = \str_replace(" ","-",$title);
            $r = $wpdb->query("INSERT INTO {$prefix}posts ( ID, post_author, post_date, post_date_gmt, post_content, post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                    ) values (0,$author,'$date','$date','','$title','','publish','closed','closed','','$slug','','','$date','$date','',0,'',0,'yuzo','',0 )" );
            if( $r ){
                // Add post meta
                $post_id = $wpdb->insert_id;
                $value   = 'a:29:{s:19:"related_post_active";s:1:"1";s:15:"fieldset_design";a:2:{s:13:"panels-design";a:31:{s:5:"title";s:19:"(title from widget)";s:10:"where_show";s:6:"widget";s:16:"content_location";s:18:"below-post-content";s:25:"content_appende_and_order";a:1:{s:17:"location_priority";s:2:"10";}s:31:"content_appende_paragraph_order";a:1:{s:18:"location_paragraph";s:1:"2";}s:8:"template";s:20:"default-image-medium";s:13:"template_type";s:4:"list";s:16:"template_colours";s:5:"set-1";s:16:"template_color_1";s:7:"#f1f1f1";s:16:"template_color_2";s:7:"#2f7eff";s:21:"design_text_font_size";a:1:{s:5:"width";s:0:"";}s:23:"design_text_line_height";a:1:{s:5:"width";s:0:"";}s:23:"design_text_font_weight";s:3:"100";s:23:"design_text_color_hover";a:2:{s:5:"color";s:4:"#000";s:5:"hover";s:0:"";}s:13:"design_screen";s:6:"mobile";s:20:"design_screen_mobile";a:2:{s:28:"design_screen_mobile_columns";s:1:"2";s:25:"design_screen_mobile_rows";s:1:"2";}s:20:"design_screen_tablet";a:2:{s:28:"design_screen_tablet_columns";s:1:"3";s:25:"design_screen_tablet_rows";s:1:"1";}s:21:"design_screen_desktop";a:2:{s:29:"design_screen_desktop_columns";s:1:"3";s:26:"design_screen_desktop_rows";s:1:"2";}s:19:"design_show_excerpt";s:0:"";s:18:"design_text_length";s:2:"80";s:19:"design_text_content";s:12:"from_content";s:20:"template_show_imagen";s:1:"1";s:21:"design_thumbnail_size";s:6:"medium";s:17:"design_image_size";s:3:"1-1";s:12:"design_metas";a:2:{s:7:"enabled";a:1:{s:9:"meta-view";s:4:"View";}s:8:"disabled";a:5:{s:9:"meta-date";s:4:"Date";s:16:"meta-date-update";s:16:"Date last update";s:13:"meta-category";s:8:"Category";s:11:"meta-author";s:6:"Author";s:12:"meta-comment";s:7:"Comment";}}s:17:"design_box_margin";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:18:"design_box_padding";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:21:"design_box_background";s:0:"";s:19:"design_post_spacing";a:1:{s:5:"width";s:0:"";}s:17:"design_html_above";s:0:"";s:17:"design_html_below";s:0:"";}s:23:"fieldset_design_preview";a:1:{s:14:"design_screen2";s:6:"mobile";}}s:12:"related_type";s:4:"list";s:9:"list_post";s:9:"most-view";s:10:"related_to";s:4:"tags";s:8:"order_by";a:2:{s:5:"order";s:4:"desc";s:2:"by";s:8:"modified";}s:18:"relation_no_result";s:16:"random_based_cpt";s:14:"time_and_space";a:1:{s:5:"range";s:10:"last-month";}s:14:"cpt_to_related";a:1:{i:0;s:4:"post";}s:29:"include_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:38:"include_taxonomy_hierarchical_operator";a:1:{s:25:"include_taxonomy_relation";s:2:"or";}s:27:"taxonomies_cat_tag_relation";s:2:"or";s:32:"related_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:42:"include_taxonomy_not_hierarchical_operator";a:1:{s:28:"include_taxonomy_no_relation";s:2:"or";}s:32:"exclude_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:15:"exclude_post_id";s:0:"";s:17:"not_appear_inside";s:0:"";s:29:"related_post_only_add_metabox";s:0:"";s:22:"show_only_in_type_post";a:1:{i:0;s:4:"post";}s:34:"show_only_in_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:31:"show_only_in_places_on_the_page";a:1:{i:0;s:7:"archive";}s:24:"display_as_list_template";s:0:"";s:28:"display_only_specific_postid";s:0:"";s:10:"group_list";s:0:"";s:13:"group_related";s:0:"";s:13:"group_exclude";s:0:"";s:29:"exclude_taxonomy_hierarchical";s:0:"";s:12:"group_strict";s:0:"";s:18:"yuzo_msg_show_cpt1";s:0:"";}';
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo','$value')");
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo_related_post_active','1')");
            }

            // ─── 7) Related posts based on Categories (Grid Template) ────────
            $title  = __('Related posts based on Categories','yuzo');
            $slug   = \str_replace(" ","-",$title);
            $r = $wpdb->query("INSERT INTO {$prefix}posts ( ID, post_author, post_date, post_date_gmt, post_content, post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                    ) values (0,$author,'$date','$date','','$title','','publish','closed','closed','','$slug','','','$date','$date','',0,'',0,'yuzo','',0 )" );
            if( $r ){
                // Add post meta
                $post_id = $wpdb->insert_id;
                $value   = 'a:29:{s:19:"related_post_active";s:1:"0";s:15:"fieldset_design";a:2:{s:13:"panels-design";a:31:{s:5:"title";s:26:"<h4>You may also like</h4>";s:10:"where_show";s:7:"content";s:16:"content_location";s:18:"below-post-content";s:25:"content_appende_and_order";a:1:{s:17:"location_priority";s:2:"10";}s:31:"content_appende_paragraph_order";a:1:{s:18:"location_paragraph";s:1:"2";}s:8:"template";s:11:"old-related";s:13:"template_type";s:4:"grid";s:16:"template_colours";s:5:"set-1";s:16:"template_color_1";s:7:"#f1f1f1";s:16:"template_color_2";s:7:"#2f7eff";s:21:"design_text_font_size";a:1:{s:5:"width";s:0:"";}s:23:"design_text_line_height";a:1:{s:5:"width";s:0:"";}s:23:"design_text_font_weight";s:3:"100";s:23:"design_text_color_hover";a:2:{s:5:"color";s:4:"#000";s:5:"hover";s:0:"";}s:13:"design_screen";s:6:"mobile";s:20:"design_screen_mobile";a:2:{s:28:"design_screen_mobile_columns";s:1:"2";s:25:"design_screen_mobile_rows";s:1:"2";}s:20:"design_screen_tablet";a:2:{s:28:"design_screen_tablet_columns";s:1:"3";s:25:"design_screen_tablet_rows";s:1:"1";}s:21:"design_screen_desktop";a:2:{s:29:"design_screen_desktop_columns";s:1:"3";s:26:"design_screen_desktop_rows";s:1:"2";}s:19:"design_show_excerpt";s:1:"1";s:18:"design_text_length";s:2:"80";s:19:"design_text_content";s:12:"from_content";s:20:"template_show_imagen";s:1:"1";s:21:"design_thumbnail_size";s:6:"medium";s:17:"design_image_size";s:3:"1-1";s:12:"design_metas";a:2:{s:7:"enabled";a:1:{s:13:"meta-category";s:8:"Category";}s:8:"disabled";a:5:{s:9:"meta-date";s:4:"Date";s:16:"meta-date-update";s:16:"Date last update";s:9:"meta-view";s:4:"View";s:11:"meta-author";s:6:"Author";s:12:"meta-comment";s:7:"Comment";}}s:17:"design_box_margin";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:18:"design_box_padding";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:21:"design_box_background";s:0:"";s:19:"design_post_spacing";a:1:{s:5:"width";s:0:"";}s:17:"design_html_above";s:0:"";s:17:"design_html_below";s:0:"";}s:23:"fieldset_design_preview";a:1:{s:14:"design_screen2";s:6:"mobile";}}s:12:"related_type";s:7:"related";s:9:"list_post";s:9:"last-post";s:10:"related_to";s:10:"categories";s:8:"order_by";a:2:{s:5:"order";s:4:"desc";s:2:"by";s:8:"modified";}s:18:"relation_no_result";s:16:"random_based_cpt";s:14:"time_and_space";a:1:{s:5:"range";s:9:"all-along";}s:14:"cpt_to_related";a:1:{i:0;s:4:"post";}s:29:"include_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:38:"include_taxonomy_hierarchical_operator";a:1:{s:25:"include_taxonomy_relation";s:2:"or";}s:27:"taxonomies_cat_tag_relation";s:2:"or";s:32:"related_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:42:"include_taxonomy_not_hierarchical_operator";a:1:{s:28:"include_taxonomy_no_relation";s:2:"or";}s:32:"exclude_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:15:"exclude_post_id";s:0:"";s:17:"not_appear_inside";s:0:"";s:29:"related_post_only_add_metabox";s:0:"";s:22:"show_only_in_type_post";a:1:{i:0;s:4:"post";}s:34:"show_only_in_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:31:"show_only_in_places_on_the_page";a:1:{i:0;s:7:"archive";}s:24:"display_as_list_template";s:0:"";s:28:"display_only_specific_postid";s:0:"";s:10:"group_list";s:0:"";s:13:"group_related";s:0:"";s:13:"group_exclude";s:0:"";s:29:"exclude_taxonomy_hierarchical";s:0:"";s:12:"group_strict";s:0:"";s:18:"yuzo_msg_show_cpt1";s:0:"";}';
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo','$value')");
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo_related_post_active','0')");
            }

            // ─── 8) Shortcode – Show last 2 posts only from one category – (List Template) ────────
            $title  = __('Shortcode – Show last 2 posts only from one category','yuzo');
            $slug   = \str_replace(" ","-",$title);
            $r = $wpdb->query("INSERT INTO {$prefix}posts ( ID, post_author, post_date, post_date_gmt, post_content, post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                    ) values (0,$author,'$date','$date','','$title','','publish','closed','closed','','$slug','','','$date','$date','',0,'',0,'yuzo','',0 )" );
            if( $r ){
                // Add post meta
                $post_id = $wpdb->insert_id;
                $value   = 'a:29:{s:19:"related_post_active";s:1:"0";s:15:"fieldset_design";a:2:{s:13:"panels-design";a:31:{s:5:"title";s:0:"";s:10:"where_show";s:9:"shortcode";s:16:"content_location";s:18:"below-post-content";s:25:"content_appende_and_order";a:1:{s:17:"location_priority";s:2:"10";}s:31:"content_appende_paragraph_order";a:1:{s:18:"location_paragraph";s:1:"2";}s:8:"template";s:19:"default-image-large";s:13:"template_type";s:4:"list";s:16:"template_colours";s:5:"set-1";s:16:"template_color_1";s:7:"#f1f1f1";s:16:"template_color_2";s:7:"#2f7eff";s:21:"design_text_font_size";a:1:{s:5:"width";s:0:"";}s:23:"design_text_line_height";a:1:{s:5:"width";s:0:"";}s:23:"design_text_font_weight";s:3:"100";s:23:"design_text_color_hover";a:2:{s:5:"color";s:4:"#000";s:5:"hover";s:0:"";}s:13:"design_screen";s:6:"mobile";s:20:"design_screen_mobile";a:2:{s:28:"design_screen_mobile_columns";s:1:"2";s:25:"design_screen_mobile_rows";s:1:"2";}s:20:"design_screen_tablet";a:2:{s:28:"design_screen_tablet_columns";s:1:"3";s:25:"design_screen_tablet_rows";s:1:"1";}s:21:"design_screen_desktop";a:2:{s:29:"design_screen_desktop_columns";s:1:"3";s:26:"design_screen_desktop_rows";s:1:"2";}s:19:"design_show_excerpt";s:1:"1";s:18:"design_text_length";s:2:"80";s:19:"design_text_content";s:12:"from_content";s:20:"template_show_imagen";s:1:"1";s:21:"design_thumbnail_size";s:6:"medium";s:17:"design_image_size";s:3:"1-1";s:12:"design_metas";a:1:{s:8:"disabled";a:6:{s:9:"meta-date";s:4:"Date";s:16:"meta-date-update";s:16:"Date last update";s:13:"meta-category";s:8:"Category";s:9:"meta-view";s:4:"View";s:11:"meta-author";s:6:"Author";s:12:"meta-comment";s:7:"Comment";}}s:17:"design_box_margin";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:18:"design_box_padding";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:21:"design_box_background";s:0:"";s:19:"design_post_spacing";a:1:{s:5:"width";s:0:"";}s:17:"design_html_above";s:0:"";s:17:"design_html_below";s:0:"";}s:23:"fieldset_design_preview";a:1:{s:14:"design_screen2";s:6:"mobile";}}s:12:"related_type";s:4:"list";s:9:"list_post";s:9:"last-post";s:10:"related_to";s:4:"tags";s:8:"order_by";a:2:{s:5:"order";s:4:"desc";s:2:"by";s:8:"modified";}s:18:"relation_no_result";s:16:"random_based_cpt";s:14:"time_and_space";a:1:{s:5:"range";s:9:"all-along";}s:14:"cpt_to_related";a:1:{i:0;s:4:"post";}s:29:"include_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{i:0;s:13:"uncategorized";}}s:38:"include_taxonomy_hierarchical_operator";a:1:{s:25:"include_taxonomy_relation";s:2:"or";}s:27:"taxonomies_cat_tag_relation";s:2:"or";s:32:"related_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:42:"include_taxonomy_not_hierarchical_operator";a:1:{s:28:"include_taxonomy_no_relation";s:2:"or";}s:32:"exclude_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:15:"exclude_post_id";s:0:"";s:17:"not_appear_inside";s:0:"";s:29:"related_post_only_add_metabox";s:0:"";s:22:"show_only_in_type_post";a:1:{i:0;s:4:"post";}s:34:"show_only_in_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:31:"show_only_in_places_on_the_page";a:1:{i:0;s:7:"archive";}s:24:"display_as_list_template";s:0:"";s:28:"display_only_specific_postid";s:0:"";s:10:"group_list";s:0:"";s:13:"group_related";s:0:"";s:13:"group_exclude";s:0:"";s:29:"exclude_taxonomy_hierarchical";s:0:"";s:12:"group_strict";s:0:"";s:18:"yuzo_msg_show_cpt1";s:0:"";}';
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo','$value')");
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo_related_post_active','0')");
            }

            // ─── 9) My Custom ────────
            $title  = __('My Custom','yuzo');
            $slug   = \str_replace(" ","-",$title);
            $r = $wpdb->query("INSERT INTO {$prefix}posts ( ID, post_author, post_date, post_date_gmt, post_content, post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                    ) values (0,$author,'$date','$date','','$title','','publish','closed','closed','','$slug','','','$date','$date','',0,'',0,'yuzo','',0 )" );
            if( $r ){
                // Add post meta
                $post_id = $wpdb->insert_id;
                $value   = 'a:29:{s:19:"related_post_active";s:1:"0";s:15:"fieldset_design";a:2:{s:13:"panels-design";a:31:{s:5:"title";s:26:"<h4>You may also like</h4>";s:10:"where_show";s:7:"content";s:16:"content_location";s:18:"below-post-content";s:25:"content_appende_and_order";a:1:{s:17:"location_priority";s:2:"10";}s:31:"content_appende_paragraph_order";a:1:{s:18:"location_paragraph";s:1:"2";}s:8:"template";s:6:"yuzo-l";s:13:"template_type";s:4:"list";s:16:"template_colours";s:5:"set-1";s:16:"template_color_1";s:7:"#f1f1f1";s:16:"template_color_2";s:7:"#2f7eff";s:21:"design_text_font_size";a:1:{s:5:"width";s:0:"";}s:23:"design_text_line_height";a:1:{s:5:"width";s:0:"";}s:23:"design_text_font_weight";s:3:"100";s:23:"design_text_color_hover";a:2:{s:5:"color";s:4:"#000";s:5:"hover";s:0:"";}s:13:"design_screen";s:7:"desktop";s:20:"design_screen_mobile";a:2:{s:28:"design_screen_mobile_columns";s:1:"2";s:25:"design_screen_mobile_rows";s:1:"2";}s:20:"design_screen_tablet";a:2:{s:28:"design_screen_tablet_columns";s:1:"3";s:25:"design_screen_tablet_rows";s:1:"1";}s:21:"design_screen_desktop";a:2:{s:29:"design_screen_desktop_columns";s:1:"2";s:26:"design_screen_desktop_rows";s:1:"2";}s:19:"design_show_excerpt";s:1:"0";s:18:"design_text_length";s:2:"80";s:19:"design_text_content";s:12:"from_content";s:20:"template_show_imagen";s:1:"1";s:21:"design_thumbnail_size";s:6:"medium";s:17:"design_image_size";s:3:"4-3";s:12:"design_metas";a:1:{s:8:"disabled";a:6:{s:9:"meta-date";s:4:"Date";s:16:"meta-date-update";s:16:"Date last update";s:13:"meta-category";s:8:"Category";s:9:"meta-view";s:4:"View";s:11:"meta-author";s:6:"Author";s:12:"meta-comment";s:7:"Comment";}}s:17:"design_box_margin";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:18:"design_box_padding";a:5:{s:3:"top";s:0:"";s:5:"right";s:0:"";s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:4:"unit";s:2:"px";}s:21:"design_box_background";s:0:"";s:19:"design_post_spacing";a:1:{s:5:"width";s:0:"";}s:17:"design_html_above";s:0:"";s:17:"design_html_below";s:0:"";}s:23:"fieldset_design_preview";a:1:{s:14:"design_screen2";s:7:"desktop";}}s:12:"related_type";s:4:"list";s:9:"list_post";s:3:"a-z";s:10:"related_to";s:4:"tags";s:8:"order_by";a:2:{s:5:"order";s:4:"desc";s:2:"by";s:8:"modified";}s:18:"relation_no_result";s:16:"random_based_cpt";s:14:"time_and_space";a:1:{s:5:"range";s:10:"last-month";}s:14:"cpt_to_related";a:1:{i:0;s:4:"post";}s:29:"include_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:38:"include_taxonomy_hierarchical_operator";a:1:{s:25:"include_taxonomy_relation";s:2:"or";}s:27:"taxonomies_cat_tag_relation";s:2:"or";s:32:"related_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:42:"include_taxonomy_not_hierarchical_operator";a:1:{s:28:"include_taxonomy_no_relation";s:2:"or";}s:32:"exclude_taxonomy_no_hierarchical";a:1:{s:8:"post_tag";s:0:"";}s:15:"exclude_post_id";s:0:"";s:17:"not_appear_inside";s:0:"";s:29:"related_post_only_add_metabox";s:0:"";s:22:"show_only_in_type_post";a:1:{i:0;s:4:"post";}s:34:"show_only_in_taxonomy_hierarchical";a:1:{s:8:"category";a:1:{s:3:"all";s:12:"all|category";}}s:31:"show_only_in_places_on_the_page";a:1:{i:0;s:7:"archive";}s:24:"display_as_list_template";s:0:"";s:28:"display_only_specific_postid";s:0:"";s:10:"group_list";s:0:"";s:13:"group_related";s:0:"";s:13:"group_exclude";s:0:"";s:29:"exclude_taxonomy_hierarchical";s:0:"";s:12:"group_strict";s:0:"";s:18:"yuzo_msg_show_cpt1";s:0:"";}';
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo','$value')");
                $r       = $wpdb->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) values( $post_id, 'yuzo_related_post_active','0')");
            }

        }
    }
}
}