<?php
/**
 * Master generated style function
 *
 * @package Codex
 */

/**
 * Add body classes to identify the child theme
 */
function codex_body_classes( $classes ) {
	$classes[] = strtolower( wp_get_theme() ) . '-child';
	return $classes;
}
add_filter( 'body_class', 'codex_body_classes', 15 );

/**
 * Dynamic styles for the frontend
 */
function codex_custom_styling() {
    $options = cryout_get_option();
    extract($options);

    ob_start(); ?>

    /* Codex custom style */

	.single #author-info {
		border-color: rgba( <?php echo cryout_hex2rgb( esc_html( $theme_accent2 ) ) ?>, 0.05 );
	}

	.widget-title,
	#comments-title,
	#reply-title,
	#author-link a,
	.logged-in-as a {
		color: <?php echo esc_html( $theme_accent2 ) ?>;
	}

	#footer a:hover {
		color: <?php echo esc_html( $theme_accent1 ) ?>;
	}

    /* end Codex custom style */
    <?php
    return preg_replace( '/((background-)?color:\s*?)[;}]/i', '', ob_get_clean() );

} // codex_custom_styling()


/**
 * Load custom styles
 */
function codex_custom_styles( $style = '' ) {

	return $style . codex_custom_styling();

} // codex_custom_styles()
// this filer is applied in codex_setup()


/* FIN */
