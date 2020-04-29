<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 * Pages class
 *
 * @package PF
 * @subpackage PF/Classes
 *
 * @since   1.3.5    2019-04-15 21:13:48    Release
 * @since   1.4.6   2019-06-12 19:03:43     Added fields 'page_html' and 'page_require'
 *                                          in the global variable of the class.
 */
class PF_classPages{

    // ─── Constans class ────────
    public
    $unique    = '',
    $abstract  = 'pages',
    $options   = array(),
    $messagess = array();

    // ─── Default args ────────
    public $args   = array(

        // ─── menu settings ────────
        'menu_title'      => '',
        'menu_slug'       => '',
        'menu_type'       => 'menu',
        'menu_capability' => 'manage_options',
        'menu_icon'       => null,
        'menu_position'   => null,
        'menu_hidden'     => false,
        'menu_parent'     => '',
        'menu_title_sub'  => '', // Change the title of the first submenu

        // ─── footer ────────
        'footer_text'   => '',
        'footer_after'  => '',
        'footer_credit' => '',

        // ─── print html ────────
        'page_html'    => '',
        'page_require' => '',

    );

    /**
     * Create an Instance of Options
     * You can create multiple instances with different $keys.
     *
     * @since   1.3.5     2019-04-15 21:20:24     Release
     *
     * @param string $key ID unique setting/options
     * @param array $params
     * @return object
     */
    public static function instance( $key, $params = array() ) {
        return new self( $key, $params );
    }

    /**
     * Run construct
     *
     * @since   1.3.5     2019-04-15 21:20:24     Release
     *
     * @param   string  $key    ID unique setting/options
     * @param   array   $params
     */
    public function __construct( $key, $params = array() ) {

        // ─── Assign the unique ID (id plugin), argument of the Options and Section ────────
        $this->unique       = $key;
        $this->args         = apply_filters( "pf_{$this->unique}_args", pf_wp_parse_args( $params['args'], $this->args ), $this );

        // ─── Load Options hook ────────
        $this->load_hooks();
    }


    public function add_pages(){
        /*
        |--------------------------------------------------------------------------
        | @menu_parent argument examples.
        |--------------------------------------------------------------------------
        |
        | For Dashboard: 'index.php'
        | For Posts: 'edit.php'
        | For Media: 'upload.php'
        | For Pages: 'edit.php?post_type=page'
        | For Comments: 'edit-comments.php'
        | For Custom Post Types: 'edit.php?post_type=your_post_type'
        | For Appearance: 'themes.php'
        | For Plugins: 'plugins.php'
        | For Users: 'users.php'
        | For Tools: 'tools.php'
        | For Settings: 'options-general.php'
        |
        */

        extract( $this->args );
        if( $menu_type === 'menu' ) {
            $menu_page = call_user_func('add_menu_page',
                                        $menu_title,
                                        $menu_title,
                                        $menu_capability,
                                        $menu_slug,
                                        array( &$this, 'add_page_html' ),
                                        $menu_icon,
                                        $menu_position );
            if ( ! empty( $menu_title_sub ) ){
                call_user_func('add_submenu_page', $menu_slug, $menu_title, $menu_title_sub, $menu_capability, $menu_slug );
            }
        }else{
            if( $menu_type === 'submenu' ) {
                $menu_page = call_user_func('add_submenu_page',
                                            $menu_parent,
                                            $menu_title,
                                            $menu_title,
                                            $menu_capability,
                                            $menu_slug,
                                            array( &$this, 'add_page_html' ) );
            }
        }

        add_action( 'load-'. $menu_page, array( &$this, 'add_page_on_load' ) );
    }

    /**
     * Load processes, hook, filter right on the page
     *
     * @since   1.3.5   2019-04-15 21:30:22     Release
     * @return void
     */
    public function add_page_on_load(){
        add_filter( 'admin_footer_text', array( &$this, 'add_admin_footer_text' ) );
    }

    /**
     * Load hooks Options
     *
     * @since	6.0	    2019-04-15 23:02:49     Release
     * @return	void
     */
    public function load_hooks(){
        add_action( 'admin_menu', array( &$this, 'add_pages' ) );
    }

    /**
     * HTML of the menu (output page)
     *
     * @since	1.3.5   2019-04-15 23:16:01     Release
     * @return  string|html
     */
    public function add_page_html(){
        if( ! empty( $this->args['page_html'] ) ){
            echo $this->args['page_html'];
        }
    }

    /**
     * Text from the bottom left of the panel
     *
     * @since   1.3.5   2019-04-16 00:06:32    Release
     * @return  void
     */
    public function add_admin_footer_text() {
        $default = 'Pixel by Lenin Zapata';
        echo ( ! empty( $this->args['footer_credit'] ) ) ? $this->args['footer_credit'] : $default;
    }
}