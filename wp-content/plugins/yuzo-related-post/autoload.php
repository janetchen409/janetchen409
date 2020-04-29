<?php
namespace   YUZO;
// Help autoload
// stackoverflow.com/questions/6284553/using-an-array-as-needles-in-strpos#answer-9220624
if( ! function_exists('_strposa') ){
    function _strposa($haystack, $needle, $offset=0) {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $query) {
            if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
        }
        return false;
    }
}

// change $path, $prefix
spl_autoload_register(function( $class_name ){

    // ─── Exclude class ────────
    if( _strposa( $class_name , [
        'yuzo',
        'sqlQueryBuilder',
        'wpImage',
        'WPUpdatesPluginUpdater_2120',
        'phpConsole',
    ] ) ) return;

    // ─── If the specified $class_name does not include our namespace, duck out. ────────
    if ( false === strpos( $class_name, ucfirst( YUZO_ID ) ) ) return;

    // ─── Vars ────────
    $path   = YUZO_PATH;
    $prefix = ucfirst( YUZO_ID );

    // ─── Folders where the files can be ────────
    $folders = [
        'core'  => array( 'include/classes', 'include/interfaces' ),
        'admin' => array( 'admin/classes', 'admin/interfaces' ),
        'publi' => array( 'public/classes', 'public/interfaces' ),
    ];

    // ─── If class or interface ────────
    $index_file_where = 0; // 0 = class, 1 = interface

    // Split the class name into an array to read the namespace and class.
    $file_parts = explode( '\\', $class_name );

    // Do a reverse loop through $file_parts to build the path to the file.
    $namespace 	= '';
    $file_name  = 'include/classes/class-init.php'; // Defaults file name

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
            | */
            $name_part = strtolower( $file_parts[ count( $file_parts ) - 1 ] );

            if ( strpos( $name_part , strtolower( YUZO_ID ) ) !== false ) {

                $file_name = '/class-' . str_replace( array( 'class', $prefix, '_', '-', ( YUZO_ID ) ), '', strtolower($name_part) ) . '.php';

            } else if( strpos( $name_part , 'interface' ) ) {

                // Grab the name of the interface from its qualified name.
                $interface_name = str_replace( array( 'interface', $prefix, '_', '-' ), '', strtolower($name_part) ) . '.php';

                $file_name        = "interface-$interface_name";
                $index_file_where = 1;
            }
        } else {
            $namespace = '/' . $current . $namespace;
        }
    }

    // Now build a path to the file using mapping to the file location.
    $filepath     = '';
    $module_point = str_replace( '/', '', $namespace );

    // If a folder does not exist, that class does not exist
    if( empty( $folders[$module_point][$index_file_where] ) ){ return; }

    $filepath     .= $path . ( $folders[$module_point][$index_file_where] ) . $file_name;
    //$filepath     .= $path . $file_name;

    // If the file exists in the specified path, then include it. );
    if ( file_exists( $filepath ) ) {
        require_once $filepath;
    } else {
        wp_die(
            esc_html( "The file attempting to be loaded at $filepath does not exist." )
        );
    }
});