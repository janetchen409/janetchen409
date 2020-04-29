<?php
/**
* Similar to wp_parse_args() just a bit extended to work with multidimensional arrays :)
*
* @since  1.0 2019-02-19 19:39:43 Release
* @link mekshq.com/recursive-wp-parse-args-wordpress-function/
*/
if( ! function_exists( 'pf_wp_parse_args' ) ){
	function pf_wp_parse_args( &$args, $defaults ) {
		$args     = (array) $args;
		$defaults = (array) $defaults;
		$result   = $defaults;
		foreach ( $args as $k => &$v ) {
			if ( is_array( $v ) && isset( $result[ $k ] ) ) {
				$result[ $k ] = pf_wp_parse_args( $v, $result[ $k ] );
			} else {
				$result[ $k ] = $v;
			}
		}
		return $result;
	}
}


/**
 * Sanitize and Validate fields when saving them
 * What you do first is a sweep then apply sanitize and then
 * Validation applies
 *
 * @since 1.2.7 2019-03-27 17:56:23 Release
 *
 * @param mixed $args
 * @param mixed $defaults
 * @param mixed $object
 *
 * @return mixed|array|string
 */
if( ! function_exists( 'pf_sanitize_fields_recursive' ) ){
	function pf_sanitize_fields_recursive( &$args, $defaults, &$object ) {
		$args     = (array) $args;
		$defaults = is_array($defaults) ? (array) $defaults : $defaults;
		$result   = $defaults;

		if( ! empty( $args['fields'] ) ){
			foreach ( $args['fields'] as $k => &$v ) {
				if ( ! empty( $v['fields'] ) && is_array( $v['fields'] ) ) {
					if( isset( $result[ $k ] ) ){
						$result[ $k ] = pf_sanitize_fields_recursive( $v, $result[ $k ], $object );
					}
				} else {
					$value_sanitize = ! empty( $v['id'] ) && isset( $result[$v['id']] ) ? $result[$v['id']] : '';
					if( ! empty( $v['sanitize'] ) ){
						$defaults[$v['id']]   = call_user_func( $v['sanitize'], $value_sanitize );
						$result  [ $v['id'] ] = $defaults[$v['id']];
					}
					if( ! empty( $v['validate'] ) ) {
						$has_validated  = call_user_func( $v['validate'], $value_sanitize );
						if( ! empty( $has_validated ) ) {
							$result[$v['id']] = ( isset( $object->options[$v['id']] ) ) ? $object->options[$v['id']] : '';
							$object->errors[$v['id']] = $has_validated;
						}
					}

					if( ! empty( $v['id'] ) &&  (! isset( $result[$v['id']] ) || is_null( $result[$v['id']] ) ) ) {
						// BUG: This was causing conflict in the fields that are repetitive,
						// I think you just have to validate that the guy is not repeater
						// → $result[$v['id']] = '';
					}
				}
			}
		}else{
			$value_sanitize = isset( $defaults ) ? $defaults : '';
			if( ! empty( $args['sanitize'] )  ){
				$result  = call_user_func( $args['sanitize'], $value_sanitize );
			}
			if( ! empty( $args['validate']  ) ) {
				$has_validated  = call_user_func( $args['validate'], $value_sanitize );
				if( ! empty( $has_validated ) ) {
					$result = ( isset( $object->options[$args['id']] ) ) ? $object->options[$args['id']] : '';
					$object->errors[$args['id']] = $has_validated;
				}
			}
			if( ! isset( $result ) || is_null( $result ) ) {
				$result = '';
			}
		}
		return $result;
	}
}

/**
 * HTML sanitization callback example.
 *
 * - Sanitization: html
 * - Control: text, textarea
 *
 * Sanitization callback for 'html' type text inputs. This callback sanitizes `$html`
 * for HTML allowable in posts.
 *
 * NOTE: wp_filter_post_kses() can be passed directly as `$wp_customize->add_setting()`
 * 'sanitize_callback'. It is wrapped in a callback here merely for example purposes.
 *
 * @see wp_filter_post_kses() https://developer.wordpress.org/reference/functions/wp_filter_post_kses/
 *
 * @param string $html HTML to sanitize.
 * @return string Sanitized HTML.
 */
if( ! function_exists( 'pf_sanitize_html' ) ){
	function pf_sanitize_html( $html ) {
		return wp_filter_post_kses( $html );
	}
}

/**
 * Sanitize
 * Replace letter a to letter b
 *
 * @since 1.0 2019-03-07 05:07:50 Release
 *
 */
if( ! function_exists( 'pf_sanitize_replace_a_to_b' ) ){
	function pf_sanitize_replace_a_to_b( $value ) {
		return str_replace( 'a', 'b', $value );
	}
}

/**
 * Sanitize title
 *
 * @since 1.0 2019-03-07 05:08:14 Release
 *
 */
if( ! function_exists( 'pf_sanitize_title' ) ){
	function pf_sanitize_title( $value ) {
		return sanitize_title( $value );
	}
}

/**
 * Sanitize only number
 *
 * @since 1.2.6 2019-03-07 05:08:14 Release
 *
 */
if( ! function_exists( 'pf_onlynumber' ) ){
	function pf_onlynumber( $value ) {
		return str_replace( array( "+", " " ), "", $value);
	}
}

/**
 * Convierte una cadena mal formaterada a una array o string formateado
 * por medio de un valor de separacion.
 *
 * @since	1.4.3	2019-05-19 17:44:02		Release
 * @since	1.4.8	2019-07-11 01:18:38		Third parameter that combierte to string
 *
 * @param 	string 	$var 					Variable to be formatted
 * @param 	string 	$split					Character string separator
 * @param	bool	$convert_to_string		FALSE = return array, TRUE = return string
 * @return	string	$result					Result of each value
 */
if( ! function_exists( 'pf_string_to_array_valid' ) ){
	function pf_string_to_array_valid( $var = '', $split = ',', $convert_to_string = false ){
		$earch_element = explode( $split, $var );
		$result = [];
		if( ! empty( $earch_element ) ){
			foreach ($earch_element as $value) {
				if( ! empty( $value ) ){
					$result[] = $value;
				}
			}
		}

		if( $convert_to_string == true ){
			return implode( $split , $result);
		}

		return $result;
	}
}