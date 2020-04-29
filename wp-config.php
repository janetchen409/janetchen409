<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */

// Enable WP_DEBUG mode
define( 'WP_DEBUG', true );

// Enable Debug logging to the /wp-content/debug.log file
define( 'WP_DEBUG_LOG', true );

// Disable display of errors and warnings
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

// Use dev versions of core JS and CSS files (only needed if you are modifying these core files)
define( 'SCRIPT_DEBUG', true );

define('WP_CACHE', true);
define( 'WPCACHEHOME', '/home/vda07apsj9as/public_html/wp-content/plugins/wp-super-cache/' );
define( 'DB_NAME', 'i5744951_wp2' );

/** MySQL database username */
define( 'DB_USER', 'i5744951_wp2' );

/** MySQL database password */
define( 'DB_PASSWORD', 'L.58T26dVeGaooaRdin46' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'xE3TmbqJMN1Tg6KrTBybfJmCX5y5ADMCuH33MDBulDOJETUaZr17uQEM7ak15pMg');
define('SECURE_AUTH_KEY',  'uyg6x4akEJCAK7OGNgQFdcc0HFQxXZlqKyPH9m2Kfj80eoHFCEf1Ce78BfnaYcyD');
define('LOGGED_IN_KEY',    'IWa4qtM9GWmz5AhH6XXEwhQadEmKLM9cAijqy0l6pu8MWsFtJoVrh18bJkBHBjBF');
define('NONCE_KEY',        '9oM7MBpL9QnYLpc6rDDCmg9rU2tjuFGDELIH5jl8kZQBQRSqLFkkPcUVw0Qzy5wN');
define('AUTH_SALT',        'fe5PZwuVYtycVnzRxmwf7hmA4ey2ETEUs9O20oXyPb4YWNMLvq6X2MMjxnZm4eHo');
define('SECURE_AUTH_SALT', 'IOPbqRdYIg25cmW8iU8SwUjt6t2E0KzPsEyLpunj26hGfCRJnwOjvSowbqcVKNp1');
define('LOGGED_IN_SALT',   'DOjPaHLJA621kgpAqeFqj7Fl7pcrZA3gtj0j9UWudJqhaftCgTbTs0HRXFnSlcge');
define('NONCE_SALT',       'QWSvCNpgCg1fm7Rvmxj1CiULVNxSAtX8C8DAFVojJpKLBfEEw3D1pHLAiyPP1Kw0');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');
define('FS_CHMOD_DIR',0755);
define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed externally by Installatron.
 * If you remove this define() to re-enable WordPress's automatic background updating
 * then it's advised to disable auto-updating in Installatron.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
