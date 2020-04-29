<?php
/**
 * Automatic upload framework files
 *
 * @link http://php.net/manual/es/function.spl-autoload-register.php
 * @package PF
 * @subpackage PF/Functions
 * @since 1.0 2019-02-18 23:55:42 Release
 */
/*
|--------------------------------------------------------------------------
|// FIXED: This autload is no longer applied because wordpress has a lot of plugins and themes
|--------------------------------------------------------------------------
*/
______spl_autoload_register(function($class_name){

	// ─── If the specified $class_name does not include our namespace, duck out. ────────
	if ( false === strpos( $class_name, 'PF' ) ) return;

	// ─── Folders where the files can be ────────
	$folders = ['classes','interfaces'];

	// Split the class name into an array to read the namespace and class.
	$file_parts = explode( '\\', $class_name );

	// Do a reverse loop through $file_parts to build the path to the file.
	$namespace 	= '';
	$file_name  = 'classes/class-init.php'; // Defaults file name
    for ( $i = count( $file_parts ) - 1; $i > 0; $i-- ) {
        // Read the current component of the file part.
		$current = strtolower( $file_parts[ $i ] );
		$current = str_ireplace( '_', '-', $current );
		// If we're at the first entry, then we're at the filename.
        if ( count( $file_parts ) - 1 === $i ) {
			/*
			|--------------------------------------------------------------------------
			| If 'interface' is contained in the parts of the file name
			|--------------------------------------------------------------------------
			|
			| Then define the $file_name differently so that it's properly loaded.
			| Otherwise, just set the $file_name equal to that of the class
			| filename structure.
			|
			*/
			$name_part = strtolower( $file_parts[ count( $file_parts ) - 1 ] );
            if ( strpos( $name_part , 'class' ) ) {
				// For classes
				// NOTE here you can improve by putting the replacement as an array to avoid creating 2
				/*$file_name = 'class-' . str_replace( 'class', '', strtolower(
										str_replace( 'pf_', '', strtolower($name_part))) ) . '.php' ;*/
				$file_name = 'class-' . str_replace( array('class','pf_','pf_field_'), '', strtolower($name_part) ) . '.php';
            } else if( strpos( $name_part , 'interface' ) ) {
                // Grab the name of the interface from its qualified name.
                $interface_name = explode( '_', $file_parts[ count( $file_parts ) - 1 ] );
                $interface_name = $interface_name[0];

                $file_name = "interface-$interface_name.php";
            }
        } else {
			$namespace = '/' . $current . $namespace;
        }
	}

	// Now build a path to the file using mapping to the file location.
	$filepath  = trailingslashit( dirname( dirname( __FILE__ ) ) . $namespace );
	$filepath .= $file_name;

	// If the file exists in the specified path, then include it. );
	//var_dump( file_exists( $filepath ) );
    if ( file_exists( $filepath ) ) {
		include_once $filepath;
    } else {
        wp_die(
            esc_html( "The file attempting to be loaded at $filepath does not exist." )
        );
    }
});