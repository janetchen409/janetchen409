<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.0
/**
 * Email validate
 *
 * @since 1.0 2019-03-06 03:25:15 Release
 */
function pf_validate_email( $value ) {

	if ( ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
		return esc_html__( 'Please write a valid email address!', 'pf' );
	}

}

/**
 * Numeric validate
 *
 * @since 1.0 2019-03-06 03:26:55 Release
 */
function pf_validate_numeric( $value ) {

	if ( ! is_numeric( $value ) ) {
		return esc_html__( 'Please write a numeric data!', 'pf' );
	}

}

/**
 * Required validate
 *
 * @since 1.0 2019-03-06 03:29:01 Release
 */
function pf_validate_required( $value ) {

	if ( empty( $value ) ) {
		return esc_html__( 'Error! This field is required!', 'pf' );
	}

}

/**
 * URL validate
 *
 * @since 1.0 2019-03-06 03:29:34 Release
 */
function pf_validate_url( $value ) {

	if( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
		return esc_html__( 'Please write a valid url!', 'pf' );
	}

}

/**
 * Email validate for Customizer
 *
 * @since 1.0 2019-03-06 03:29:59 Release
 */
function pf_customize_validate_email( $validity, $value, $wp_customize ) {

	if ( ! sanitize_email( $value ) ) {
		$validity->add( 'required', esc_html__( 'Please write a valid email address!', 'pf' ) );
	}

	return $validity;

}

/**
 * Numeric validate for Customizer
 *
 * @since 1.0 2019-03-06 03:30:53 Release
 */
function pf_customize_validate_numeric( $validity, $value, $wp_customize ) {

	if ( ! \is_numeric( $value ) ) {
		$validity->add( 'required', esc_html__( 'Please write a numeric data!', 'pf' ) );
	}

	return $validity;

}

/**
 * Required validate for Customizer
 *
 * @since 1.0 2019-03-06 03:31:18 Release
 */
function pf_customize_validate_required( $validity, $value, $wp_customize ) {

	if ( empty( $value ) ) {
		$validity->add( 'required', esc_html__( 'Error! This field is required!', 'pf' ) );
	}

	return $validity;

}

/**
 * URL validate for Customizer
 *
 * @since 1.0 2019-03-06 03:32:33 Release
 */
function pf_customize_validate_url( $validity, $value, $wp_customize ) {

	if( ! \filter_var( $value, FILTER_VALIDATE_URL ) ) {
		$validity->add( 'required', esc_html__( 'Please write a valid url!', 'pf' ) );
	}

	return $validity;

}