<?php

/**
 * Replace parent theme admin page with child theme page to use appropriate theme labelling
 */
function codex_replace_admin_page() {
	remove_action( 'admin_menu', 'roseta_add_page_fn' );
} // codex_replace_admin_page()
add_action( 'init', 'codex_replace_admin_page', 11 );

function codex_add_page_fn() {
	global $roseta_page;
	$roseta_page = add_theme_page( __( 'Codex Theme', 'codex' ), __( 'Codex Theme', 'codex' ), 'edit_theme_options', 'about-codex-theme', 'roseta_page_fn' );
	add_action( 'admin_enqueue_scripts', 'roseta_admin_scripts' );
} // codex_add_page_fn()
add_action( 'admin_menu', 'codex_add_page_fn' );

/**
 * Add child theme version to admin page
 */
function codex_admin_version_output( $parent ) {
	$theme = wp_get_theme();
	$name = $theme->get( 'Name' );
	$version = $theme->get( 'Version' );

	return sprintf( __( '<em>%1$s v%2$s</em> &ndash; a child theme of %3$s', 'codex' ), $theme, $version, $parent );
} // codex_admin_version_output()
// this filter is applied in codex_setup()

/**
 * Extend description to reference the use of the child theme
 */
function codex_custom_description( $description ) {
	// Child theme
	$theme = wp_get_theme();
	$template = esc_attr( $theme->get( 'Template' ) );
	$name = esc_attr( $theme->get( 'Name' ) );

	// Parent theme
	$template_theme = wp_get_theme( $template );
	$template_desc = $template_theme->get( 'Description');

	$output = '<h3>' . sprintf( esc_attr__( '%1$s is a child theme of %2$s', 'codex' ), $name,  ucfirst( $template ) ) . '</h3>';

	return  $output . $description . '<br><br><hr><br><em>' . $template_desc . '</em>';
} // codex_custom_description()
// this filter is added in codex_setup()


// FIN
