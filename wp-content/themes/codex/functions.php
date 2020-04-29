<?php
/****************************
*
* Codex v1.0.0 - An Roseta Child Theme
* (c) 2019 Cryout Creations
* www.cryoutcreations.eu
*
*****************************/

/**
 * Load additional theme files
 */
require_once( get_stylesheet_directory() . '/includes/admin.php' );
require_once( get_stylesheet_directory() . '/includes/options.php' );
require_once( get_stylesheet_directory() . '/includes/notices.php' );
require_once( get_stylesheet_directory() . "/includes/custom-styles.php" );

/**
 * Enqueue parent styling
 */
function codex_child_styling(){
	wp_enqueue_style( 'roseta-main', get_template_directory_uri() . '/style.css', array(), _CRYOUT_THEME_VERSION );  // restore correct parent stylesheet
	wp_enqueue_style( 'codex', get_stylesheet_directory_uri() . '/style.css', array( 'roseta-main' ), _CRYOUT_THEME_VERSION  ); 		// enqueue child stylesheet
}
add_action( 'wp_enqueue_scripts', 'codex_child_styling' );

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function codex_setup() {

	// Add support for flexible headers
	add_theme_support( 'custom-header', array(
		'default-image'	=> get_stylesheet_directory_uri() . '/resources/images/headers/front.jpg',
	));

	// Default custom headers packaged with the theme.
	register_default_headers( array(
		'front' => array(
			'url' => '%s/resources/images/headers/front.jpg',
			'thumbnail_url' => '%s/resources/images/headers/front.jpg',
			'description' => __( 'Front', 'codex' )
		),
	) );

	// Filters
	add_filter( 'roseta_custom_styles', 'codex_custom_styles' );
	add_filter( 'cryout_theme_description', 'codex_custom_description' );
	add_filter( 'cryout_admin_version', 'codex_admin_version_output' );

	// Initialize first time notice
	new Cryout_Notice( array(
		'slug' => 'codex',
		'strings' => array(
			1 => esc_html__( 'It appears that you might have already configured %1$s. For best results it is recommended to %2$s upon child theme activation.', 'codex' ), // %1 is theme name, %2 is next string
			2 => esc_html__( 'reset the theme settings', 'codex' ),
			3 => esc_html__( 'If you have already reset the options it is safe to dismiss this message.', 'codex' ),
			4 => esc_html__( 'Do not display again', 'codex' ),
		),
	) );

} // codex_setup()
add_action( 'after_setup_theme', 'codex_setup' );


/* FIN */
