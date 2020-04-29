<?php
/**
 * Theme functions and definitions
 *
 * @package Portfolio Way
 */

if ( ! function_exists( 'portfolio_way_enqueue_styles' ) ) :
	/**
	 * @since Portfolio Way 1.0.0
	 */
	function portfolio_way_enqueue_styles() {
		wp_enqueue_style( 'portfolio-way-style-parent', get_template_directory_uri() . '/style.css' );
		wp_enqueue_style( 'portfolio-way-style', get_stylesheet_directory_uri() . '/style.css', array( 'portfolio-way-style-parent' ), '1.0.0' );
		wp_enqueue_style( 'portfolio-way-google-fonts', '//fonts.googleapis.com/css?family=Montserrat:300,400,400i,500,600,700|Poppins:300,400,500,600,700&display=swap', false );
	}

endif;
add_action( 'wp_enqueue_scripts', 'portfolio_way_enqueue_styles', 99 );



