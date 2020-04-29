<?php

/**
 * Defaults
 */
function codex_defaults( $defaults = array() ) {

	$codex_defaults = array(
		"theme_magazinelayout"		=> 2,
		// "theme_headerheight"		=> 600,
		"theme_lpsliderimage"		=> get_stylesheet_directory_uri() . '/resources/images/slider/codex.jpg',

		"theme_fheight"				=> 400,
		"theme_sitelayout"			=> "1c",
		"theme_magazinelayout"		=> 3,

		// "theme_sitebackground" 		=> "",
		"theme_sitetext" 			=> "#666666",
		"theme_titletext"			=> "#111111",
		"theme_headingstext"		=> "#111111",
		"theme_contentbackground"	=> "#FFFFFF",
		"theme_accent1" 			=> "#a10d0d",
		"theme_accent2" 			=> "#111111",
		"theme_menutext" 			=> "#333333",
		"theme_submenutext" 		=> "#fffff0",
		"theme_submenubackground"	=> "#111111",

		"theme_overlaybackground"  	=> "#111111",
		"theme_overlaytext"			=> "#FFFFF0",
		"theme_overlayopacity"		=> "60",

		"theme_footerbackground"	=> "#000",

		// "theme_fgeneral" 	=> '',
		"theme_fgeneralgoogle" 	=> 'Montserrat',
		"theme_fgeneralsize" 	=> '15',
		"theme_fgeneralweight" 	=> '400',

		//"theme_fsitetitle" 	=> '',
		"theme_fsitetitlegoogle"=> 'Montserrat',
		"theme_fsitetitlesize" 	=> 1.2,
		"theme_fsitetitleweight"=> '800',
		//"theme_fmenu" 		=> '',
		"theme_fmenugoogle"		=> 'Montserrat',
		"theme_fmenusize" 		=> 0.8,
		"theme_fmenuweight"		=> '300',

		//"theme_fht_title" 	=> '',
		"theme_fht_titlegoogle"	=> 'Montserrat',
		"theme_fht_titlesize" 		=> 5,
		"theme_fht_titleweight"	=> '100',

		//"theme_fht_meta" 		=> '',
		"theme_fht_metagoogle"	=> 'Montserrat',
		"theme_fht_metasize" 		=> 0.9,
		"theme_fht_metaweight"	=> '400',
		"theme_fht_metavariant"	=> 'none',

		//"theme_ftitles" 	=> '',
		"theme_ftitlesgoogle"	=> 'Montserrat',
		"theme_ftitlessize" 	=> 2.0,
		"theme_ftitlesweight"	=> '300',

		//"theme_meta" 		=> '',
		"theme_metatitlesgoogle"	=> 'Montserrat',
		"theme_metatitlessize" 		=> 0.9,
		"theme_metatitlesweight"	=> '400',
		"theme_metatitlesvariant"	=> 'none',

		//"theme_singletitle" 		=> '',
		"theme_singletitlegoogle"	=> 'Montserrat',
		"theme_singletitlesize" 	=> 3.6,
		"theme_singletitleweight"	=> '300',
		"theme_singletitlevariant"	=> '',

		//"theme_singlemeta" 		=> '',
		"theme_singlemetagoogle"	=> 'Montserrat',
		"theme_singlemetasize" 		=> 1,
		"theme_singlemetaweight"	=> '700',
		"theme_singlemetavariant"	=> '',

		//"theme_fheadings" 	=> '',
		"theme_fheadingsgoogle"	=> 'Montserrat',
		"theme_fheadingssize" 	=> 1,
		"theme_fheadingsweight"	=> '700',
		"theme_fheadingsvariant"=> '',

		//"theme_fwtitle" 		=> '',
		"theme_fwtitlegoogle"	=> 'Montserrat',
		"theme_fwtitlesize" 	=> 0.9,
		"theme_fwtitleweight"	=> '700',

		//"theme_fwcontent" 	=> '',
		"theme_fwcontentgoogle"	=> 'Montserrat',
		"theme_fwcontentsize" 	=> 1,
		"theme_fwcontentweight"	=> '400',

		"theme_excerptlength"	=> 35,
		"theme_excerptcont"		=> 'Read more'
	); // codex_defaults array

	$result = array_merge( $defaults, $codex_defaults );

	return $result;

} // codex_defaults()
add_filter( 'roseta_option_defaults_array', 'codex_defaults' );

// needed? for the .org preview
function codex_filter_defaults(){
	add_filter( 'roseta_option_defaults_array', 'codex_defaults' );
} // codex_filter_defaults()
add_action( 'customize_register', 'codex_filter_defaults' );


/**
 * Handle theme labels in customize panels
 */
function codex_about_theme_name( $initial ) {
	return __( 'About Codex', 'codex' );
} // codex_about_theme_name()
add_filter( 'cryout_about_theme_name', 'codex_about_theme_name' );

function codex_about_theme_plus_desc( $initial ) {
	$theme = wp_get_theme();
	return '<h3>' . sprintf( esc_attr__( '%1$s is a child theme of %2$s', 'codex' ), esc_attr( $theme->get( 'Name' ) ), ucwords( esc_attr( $theme->get( 'Template' ) ) ) ) . '</h3>' . $initial;
} // codex_about_theme_plus_desc()
add_filter( 'cryout_about_theme_plus_desc', 'codex_about_theme_plus_desc' );

function codex_about_theme_slug_swap( $initial ){
	return str_replace( array( 'roseta', 'Roseta' ), array( 'codex', 'Codex' ), $initial );
} // codex_about_theme_slug_swap()
add_filter( 'cryout_about_theme_review_link', 'codex_about_theme_slug_swap' );
add_filter( 'cryout_about_theme_manage_link', 'codex_about_theme_slug_swap' );

// FIN
