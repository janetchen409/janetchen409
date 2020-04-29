<?php
/**
 * Yuzo free
 * @author    Lenin Zapata <leninzapatap@gmail.com>
 * @link      https://leninzapata.com
 * @copyright 2020 Yuzo
 * @package   Yuzo
 *
 * @wordpress-plugin
 * Plugin Name: Yuzo - Related and List post (Free version)
 * Plugin URI: https://yuzopro.com
 * Author: Lenin Zapata
 * Author URI: https://leninzapata.com
 * Version: 6.1.53
 * Description: Increase your page views, avoid bouncing and optimize your posts quickly and easily
 * Text Domain: yuzo
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
// ─── Start ☇ of the standard programming PSR-4 ────────
namespace YUZO;

// ─── Exit if accessed directly ────────
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── If this file is called directly, abort. ────────
if ( ! defined( 'WPINC' ) ) { die; }

// ─── Get data head plugin ────────
$filedata = get_file_data( __FILE__ , array( 'Version', 'Text Domain' ) );

// ─── Global development mode // ← Global mode developer ────────
if( ! defined( 'WP_MODE_DEV' ) ){ define( 'WP_MODE_DEV', ( WP_DEBUG || FALSE ) ? true : false );  }

// ─── Verify if it is in developer mode ────────
define( 'YUZO_MODE_DEV',  WP_MODE_DEV ) ; // WP_MODE_DEV

// ─── Version ────────
define( 'YUZO_VERSION', ! YUZO_MODE_DEV ? $filedata[0] : rand(111,999) );

// ─── Version tables in DB ➘ ────────
define( 'YUZO_VERSION_DB', '6.1.43' );

// ─── Absolute server path ────────
define( 'YUZO_PATH', plugin_dir_path( __FILE__ ) );

// ─── Absolute public url ────────
define( 'YUZO_URL', plugin_dir_url( __FILE__ ) );

// ─── Text Domain for international language ────────
define( 'YUZO_TEXTDOMAIN',  isset( $filedata[1] ) && $filedata[1] ? $filedata[1] : '' );

// ─── Name (slug) or ID plugin ────────
define( 'YUZO_ID', 'yuzo' );

// ─── Api ────────
define( 'YUZO_API', 'v8s31xxliame_nimda2347wwqr.-' );

// ─── Url plugin base name: example plugin/index.php ────────
define( 'YUZO_BASENAME', plugin_basename( __FILE__ ) );

// ─── Default image for all cases ────────
define( 'YUZO_IMAGE_DEFAULT', YUZO_URL . 'public/assets/images/default.png' );

// ─── Force to view changelog ────────
define( 'YUZO_VERSION_CHANGELOG', '1' ); // Only this value is changed to force the change


/*
|--------------------------------------------------------------------------
| Load the autoload to comply with the standard PSR-4
|--------------------------------------------------------------------------
*/ require_once 'autoload.php';


/*
|--------------------------------------------------------------------------
| Start the plugin
|--------------------------------------------------------------------------
|
| This file is plugin initializer
|
*/ require_once 'include/classes/class-init.php';