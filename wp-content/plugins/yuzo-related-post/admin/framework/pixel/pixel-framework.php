<?php
/**
 * Pixel Framework - WordPress Options Framework
 * @author    Lenin Zapata <i@leninzapata.com>
 * @link      https://leninzapata.com
 * @copyright 2017-2019 Pixel Framework
 * @package   pixelframework
 *
 * @wordpress-plugin
 * Plugin Name: Pixel Framework
 * Plugin URI: https://leninzapata.com
 * Author: iLenStudio
 * Author URI: https://leninzapata.com
 * Version: 1.6.31
 * Description: WordPress Option Framework ( for Theme Options, Setting plugins, Metabox options, Widgets, Menus, more... )
 * Text Domain: pf
 * Domain Path: /languages
 */
if( ! class_exists('PF') ) {
    // OPTIMIZE: Here you must add more constants, as well as the plugins
    // como por ejemplos la de la version, si lo vas a dar para los demas
    // entonces debes poner mas constantes.

    // I define the constants
    // ─── Absolute server path ────────
    define( 'PIXEL_PATH', plugin_dir_path( __FILE__ ) );

    // ─── Absolute public url ────────
    define( 'PIXEL_URL', plugin_dir_url( __FILE__ ) );

    // I invoke the nucleus
    require_once PIXEL_PATH .'classes/class-init.php';
} ?>