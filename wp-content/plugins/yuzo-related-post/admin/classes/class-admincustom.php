<?php
// ───────────────────────────
namespace YUZO\Admin;
use YUZO\Core\YUZO_Core as yuzo;
// ───────────────────────────

if( ! class_exists( 'Yuzo_AdminCustom' ) ){
/**
 * @since 		6.0     2019-04-13  Release
 * @package 	YUZO
 * @subpackage 	YUZO/Admin
 */
class Yuzo_AdminCustom{

    private
    /**
     * The ID
     * @since   6.0     2019-04-13 16:35:24     Release
     * @access  private
     * @var     string
     */
    $name,
    /**
     * The version
     *
     * @since       6.0         2019-04-13 16:35:24     Release
     * @access      private
     * @var         string      $version                The current version of this plugin.
     */
    $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since       6.0         2019-04-13 16:35:24 Release
     *
     * @param       string      $name 		        The name of this plugin.
     * @param       string      $version 	        The version of this plugin.
     * @return      void
     */
    public function __construct( $name, $version ) {
        $this->name    = $name;
        $this->version = $version;
    }

    /**
     * Add the support button in the setting options
     *
     * @since   6.0     2019-04-13 16:35:24     Release
     * @return  string|html
     */
    public function add_button_support(){
        //echo  '<a href="https://wordpress.org/support/plugin/wp-attention-click/" target="_black" id="wpac-button-support" class="button button-secondary" >' . esc_html__( 'Support', 'wpac' ) . '</a>';
    }

    /**
     * Sort the submenu of the main menu
     *
     * @since   6.0     2019-04-16  Release
     * @since   6.1.45  2020-01-24  It is validated that the Yuzo submenu exists, this caused error in user role editor
     * @return  void
     */
    public function order_submenu( $menu_order ){
        if( is_network_admin() ) return;
        global $submenu;
        if( ! isset($submenu['yuzo']) || empty( $submenu['yuzo'] )  ) return;
        $menus_yuzo = $submenu['yuzo'];
        $new_menu   = [];
        if( ! empty( $menus_yuzo ) ){
            foreach ( $menus_yuzo as $key => $details ) {
                if ( $details[2] == 'yuzo' ) {
                    $new_menu[2] = $details;
                }elseif( $details[2] == 'yuzo-setting' ){
                    $new_menu[1] = $details;
                }else{
                    $new_menu[0] = $details;
                }
            }
        }

        $submenu['yuzo'] = $new_menu;

        # Reorder the menu based on the keys in ascending order
        ksort( $submenu['yuzo'] );

        # Return the new submenu order
        return $menu_order;
    }

    /**
     * Show the counters in the admin bar
     *
     * @since   6.0.6   2019-07-13  Release
     * @since   6.0.9   2019-07-21  To find the level now only the value of the direct setting is sent
     * @since   6.0.9.4 2019-07-27  If it is multisio admin does not enter to calculate
     * @since   6.0.9.7 2019-08-01  Validate the 'yuzo::instance()->admin::$' function only compatible with php7
     * @since   6.1.3   2020-01-02  - Clicks are shown if it is greater than 0
     *                              - The position of the icon is corrected when there is no click
     *                              - The counter is shown in the HOMEPAGE, now this was corrected
     *
     * @return  string
     */
    public function create_adminbar_menu_yuzo_views() {

        /* If you are inside the administrator does not show it, because it shows it in the publication box */
        if( is_admin() || is_network_admin() ) return;

        /* If the counter is deactivated then it does not show it either */
        if( isset(yuzo::instance()->settings->general_disabled_counter_view) &&
            yuzo::instance()->settings->general_disabled_counter_view ) return;

        global $wp_admin_bar, $post;

        $_html = '';
        $views = 0;

        $only_post_type_allow = ! empty($post) && in_array($post->post_type, (array)yuzo::instance()->settings->general_cpt_to_counter) ? 1 : 0 ;

        if( is_singular() && $only_post_type_allow  && ( yuzo::instance()->admin::$is_user_panel_setting || yuzo::instance()->admin->isUserAllow() ) ){

            $views   = yuzo_get_views( $post->ID );
            $clicks  = yuzo_get_clicks( $post->ID, true );
            $clicks2 = yuzo_get_clicks( $post->ID );

            if( ! empty( $views ) ){

                $level = yuzo_get_index_level( $views, 'medium' );
                $views = yuzo_cut_counter( $views, yuzo::instance()->settings, false );

                $html_clicks = '';
                if( (int)$clicks > 0 ){
                    $html_clicks .= '<span class="y-icons-clicks" title="Clicks on a yuzo from this post"></span>
                    <span class="y-icons-clicks-number" title="Clicks on a yuzo from this post">'. $clicks .'</span>';
                }
                if( (int)$clicks2 > 0 ){
                    $html_clicks .= '<span class="y-icons-clicks y-icons-clicks2" title="Visit from another yuzo post to this page"></span>
                    <span class="y-icons-clicks-number y-icons-clicks-number2" title="Visit from another yuzo post to this page">'. $clicks2 .'</span>';
                }

                // Correct the location of the icon and number of views of Yuzo when there are no clicks made
                $class_fix_position_icon = ((int)$clicks == 0 && (int)$clicks2 == 0) ? ' y-icons-levels-fix' : '';
                $class_fix_position_view = ((int)$clicks == 0 && (int)$clicks2 == 0) ? ' y-view-levels-fix' : '';

                $_html .= '<span class="y-icons-levels y-icon-level'. $level . $class_fix_position_icon . '" title="Views"></span> ';  //$views
                $_html .= '<span class="y-colors-levels y-color-level'. $level. $class_fix_position_view . '" title="Views">'. $views .'</span>';
                $_html .= '<span class="y-icons-clicks-wrap">'. $html_clicks .'</span>';
                $style="
<style>
#wp-admin-bar-admin_yuzo_views .y-icons-levels,
.wp-admin .y-icons-levels{
background-image: url('".YUZO_URL."/admin/assets/images/viewsfire.png');
background-repeat: no-repeat;
width: 11px;
height: 15px;
display: inline-block;
position: relative;
top: -1px;
background-size: cover;
}
#wp-admin-bar-admin_yuzo_views .y-icons-levels.y-icons-levels-fix{
top: 1px;
}
#wp-admin-bar-admin_yuzo_views .y-icons-levels.y-icon-level2,
.wp-admin .y-icons-levels.y-icon-level2{
background-position-x: -11px;
}
#wp-admin-bar-admin_yuzo_views .y-icons-levels.y-icon-level3,
.wp-admin .y-icons-levels.y-icon-level3{
background-position-x: -24px;
}
#wp-admin-bar-admin_yuzo_views .y-icons-levels.y-icon-level4,
.wp-admin .y-icons-levels.y-icon-level4{
background-position-x: -37px;
}
#wp-admin-bar-admin_yuzo_views .y-icons-levels.y-icon-level5,
.wp-admin .y-icons-levels.y-icon-level5{
background-position-x: -49px;
}
#wp-admin-bar-admin_yuzo_views .y-colors-levels,
.wp-admin .y-colors-levels{
color:#BCBCBC;
/* font-family: cursive; */
position: relative;
top: -2px;
}
#wp-admin-bar-admin_yuzo_views .y-colors-levels.y-view-levels-fix{
top: 0px;
}
#wp-admin-bar-admin_yuzo_views .y-colors-levels.y-color-level2,
.wp-admin .y-colors-levels.y-color-level2{
color:#F78FAD;
}
#wp-admin-bar-admin_yuzo_views .y-colors-levels.y-color-level3,
.wp-admin .y-colors-levels.y-color-level3{
color: #FF4465;
}
#wp-admin-bar-admin_yuzo_views .y-colors-levels.y-color-level4,
.wp-admin .y-colors-levels.y-color-level4{
color:#E8CA00;
}
#wp-admin-bar-admin_yuzo_views .y-colors-levels.y-color-level5,
.wp-admin .y-colors-levels.y-color-level5{
color:#0084E3;
}
#wp-admin-bar-admin_yuzo_views .y-icons-clicks-wrap{
/* font-family: cursive; */
position: relative;
top: -2px;
}
#wp-admin-bar-admin_yuzo_views .y-icons-clicks,
.wp-admin .y-icons-clicks{
background-image: url('".YUZO_URL."/admin/assets/images/yuzo-click-2.png');
background-repeat: no-repeat;
width: 19px;
height: 23px;
display: inline-block;
position: relative;
top: 5px;
margin-left: 8px;
background-size: contain;
left: 0px;
}
#wp-admin-bar-admin_yuzo_views .y-icons-clicks{
width: 15px;
height: 23px;
display: inline-block;
position: relative;
top: 9px;
margin-left: -2px;
background-size: contain;
left: 0px;
background-size: contain;
opacity: .8;
transform: rotate(45deg);
filter: brightness(0) invert(0.9);
}
#wp-admin-bar-admin_yuzo_views .y-icons-clicks-number,
.wp-admin .y-icons-clicks-number{
position: relative;
top: 0px;
color: #eee;
left: -1px;
}
.wp-admin #wp-admin-bar-admin_yuzo_views .y-icons-clicks-number{
position: relative;
top: -2px;
}
#wp-admin-bar-admin_yuzo_views .y-icons-clicks-number2{
position: relative;
left: -3px;
}
#wp-admin-bar-admin_yuzo_views .y-icons-clicks2{
transform: rotate(-140deg);
top: 3px;
left: 5px;
}
</style>";

                $menu_id = 'admin_yuzo_views';
                $wp_admin_bar->add_menu(array('id' => $menu_id, 'title' => $_html . $style, 'href' => ''));

            }
        }

    }

    public function yuzo_header_in_cpt(){
        // Get the current screen, and check whether we're viewing the Yuzo
        $screen = get_current_screen();
        if ( 'yuzo' !== $screen->post_type ) {
            return;
        } ?>
        <div class="yzp-header-wrapper-temp"></div>
        <div class="yzp-header-wrapper">
            <span class="yzp-logo-text"><img src="<?php echo YUZO_URL . 'admin/assets/images/icon.png'; ?>">uzo</span>
            <span class="yzp-subtitle">Free <span class="yzp-version">v.<?php echo YUZO_VERSION; ?></span></span>
            <div class='yzp-logo'><?php _e('The first plugin you must install in Wordpress','yuzo') ?></div>
        </div>
        <?php
    }

    /**
     * Yuzo main setting header
     *
     * @since   6.0.9.8     2019-08-28      Release
     * @return void
     */
    public function header_in_setting(){ ?>
        <div class="yzp-header-wrapper">
            <span class="yzp-logo-text"><img src='<?php echo YUZO_URL . 'admin/assets/images/icon.png'; ?>' />uzo</span>
            <span class="yzp-subtitle">Free <span class="yzp-version">v.<?php echo YUZO_VERSION; ?></span></span>
            <div class='yzp-logo'><?php _e('The first plugin you must install in Wordpress','yuzo') ?></div>
        </div><?php
    }

    /**
     * Add the visits indicator at the end of the post tables
     * This will only be shown in the allowed posts
     *
     * @since   6.0.9.82    2019-08-30      Release
     * @param   strnig      $which          Position hook (top, bottom)
     * @return  string|html
     */
    public function footer_extra_list_posts( $which ){
        global $typenow;

        // ─── The general configuration is obtained ────────
        $yuzo_settings = yuzo_get_option();

        // ─── If the counter is deactivated then it does not show it either ────────
        if( isset(  $yuzo_settings->general_disabled_counter_view ) &&
        $yuzo_settings->general_disabled_counter_view ) return;

        // ─── The allowed CPs are obtained ────────
        $post_type_allow = empty( $yuzo_settings->general_cpt_to_counter ) ? ['post','page'] : $yuzo_settings->general_cpt_to_counter;

        // ─── Valid if the current CP is allowed ────────
        if ( ! in_array( $typenow  , $post_type_allow ) ) return false;

        // ─── View indicators will only be shown at the bottom of the list ────────
        if( $which == 'bottom' ){
            $range = yuzo_get_range_counter_by_posts( 'medium' );
            echo '<div class="pf-onload"><div class="yzp-views-footer-list-posts pf-field">';
                echo '<span class="y-icons-levels y-icon-level1" data-tooltip="'. $range['range1']['from'] .' - '. $range['range1']['to'] .'"></span>';
                echo '<span class="y-icons-levels y-icon-level2" data-tooltip="'. $range['range2']['from'] .' - '. $range['range2']['to'] .'"></span>';
                echo '<span class="y-icons-levels y-icon-level3" data-tooltip="'. $range['range3']['from'] .' - '. $range['range3']['to'] .'"></span>';
                echo '<span class="y-icons-levels y-icon-level4" data-tooltip="'. $range['range4']['from'] .' - '. $range['range4']['to'] .'"></span>';
                echo '<span class="y-icons-levels y-icon-level5" data-tooltip="> '. $range['range5']['to'] .'"></span>';
            echo '</div></div>';
        }
    }

    public function footer_side_yuzo(){
        global $post;

        $only_post_type_allow = ! empty($post) && in_array($post->post_type, (array)yuzo::instance()->settings->general_cpt_to_counter) ? 1 : 0 ;
        if( $only_post_type_allow  && ( yuzo::instance()->admin::$is_user_panel_setting || yuzo::instance()->admin->isUserAllow() ) ){
            echo "<div class='yzp-side-tracking open'>";
                echo "<div class='yzp-side-tracking-block'></div>";
                echo "<div class='yzp-side-tracking-block'></div>";
                echo "<div class='yzp-side-tracking-block'></div>";
            echo "</div>";
        }
    }

    /* public function yuzo_func_cron(){
        require_once YUZO_PATH . 'include/functions/helper.php';
        pf_global_update_option( YUZO_ID );
    } */

    /**
     * Donation button for general setting
     *
     * @since   6.1.40  2020-01-12  Release
     * @return  string
     */
    public function button_donate_options(){
        echo '<a href="http://bit.ly/YuzoDonate3" target="_blank" class="yuzo-botton-donate-cpt button page-title-action">Donate</a>';
    }

    /**
     * Feedback for yuzo improve
     *
     * @since   6.1.52  2020-02-09  Release
     * @since   6.1.53  2020-02-09  Add button support
     * @return  void
     */
    public function form_feedback_uninstall(){
        global $pagenow;

        if( $pagenow != 'plugins.php' ) return;
        $questions[] = "<div class='yuzo-feedback-opt'><label><input name='yzp-feedback-opt' type='radio' value='1' />I found a better plugin.</label><div class='yuzo-feedback-input'><span class='yuzo-feedback-error error1'>Please fill this field</span><input id='yuzo-feedback-name-plugin' name='name_plugin' type='text' placeholder='".__('What is the name of that plugin?','yuzo')."' maxlength='500' /></div></div>";
        $questions[] = "<div class='yuzo-feedback-opt'><label><input name='yzp-feedback-opt' type='radio' value='2' />The plugin suddenly stopped working.</label></div>";
        $questions[] = "<div class='yuzo-feedback-opt'><label><input name='yzp-feedback-opt' type='radio' value='3' />I only needed the plugin for a short period.</label></div>";
        $questions[] = "<div class='yuzo-feedback-opt'><label><input name='yzp-feedback-opt' type='radio' value='4' />The plugin broke my site.</label></div>";
        $questions[] = "<div class='yuzo-feedback-opt'><label><input name='yzp-feedback-opt' type='radio' value='5' />I no longer need the plugin.</label></div>";
        $questions[] = "<div class='yuzo-feedback-opt'><label><input name='yzp-feedback-opt' type='radio' value='6' />It's a temporary deactivation. I'm just debugging an issue.</label></div>";
        $questions[] = "<div class='yuzo-feedback-opt'><label><input name='yzp-feedback-opt' type='radio' value='8' />I just bought the Pro version.</label></div>";

        $_html = "<div class='yuzo-popup-overload'></div>";
        $_html .="<div class='yuzo-popup'>";
            $_html .="<div class='yuzo-popup-header'>";
            $_html .="<h3>".__('Quick Feedback','yuzos')."</h3>";
            $_html .="</div>";
            $_html .="<div class='yuzo-popup-content'>";
                $_html .="<h3>Please let us know why you are deactivating:</h3>";
                shuffle( $questions );
                foreach ($questions as $key => $value) {
                    $_html .= $value;
                }
                $_html .="<div class='yuzo-feedback-opt'><label><input name='yzp-feedback-opt' type='radio' value='7' />Other</label><div class='yuzo-feedback-input'><span class='yuzo-feedback-error error2'>Please fill this field</span><input id='yuzo-feedback-others' name='others' type='text' placeholder='' maxlength='500' /></div></div>";
                $_html .="<div class='yuzo-feedback-check'><label><input name='yzp-feedback-che' type='checkbox' value='1' />Anonymous</label></div>";
            $_html .="</div>";
            $_html .="<div class='yuzo-popup-footer'>";
                $_html .="<a class='button button-send-feedback disabled' href='javascript:void(0);'>". __('Submit and Desactivate','yuzo') ."</a>";
                $_html .="<a class='button yuzo-feedback-button-cancel' href='javascript:void(0)'>". __('Cancel','yuzo') ."</a>";
                $_html .="<a class='button button-primary yuzo-feedback-button-support' href='http://bit.ly/37dTFYv' target='_blank'>". __('Support','yuzo') ." <span class='dashicons dashicons-admin-tools'></span></a>";
            $_html .="</div>";
            $_html .="<input type='hidden' id='yuzo-link-desactivate' />";
        $_html .="</div>";
        $_html .="<script src='//cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/rollups/aes.js'></script><script></script>";

        echo $_html;
    }

    /**
     * Edit action link in the plugin list
     *
     * @since   6.1.43
     *
     * @param   array   $links  Links plugin action
     * @return  array
     */
    public function actionLinks( $links ){
        $links['getting_started'] = '<a href="'. admin_url('admin.php?page=yuzo') .'">' . __('Getting Started', 'yuzo') . '</a>';
        $links['settings']        = '<a href="'. admin_url('admin.php?page=yuzo-setting') .'">'        . __('Settings', 'yuzo') . '</a>';
        return $links;
    }
}
}