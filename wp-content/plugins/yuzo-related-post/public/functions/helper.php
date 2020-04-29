<?php
/**
 * PHP associative array duplicate key
 *
 * @since 6.0
 *
 * @param $string string chain to clean
 * @param $primary_separate separate main
 * @param $second_separate separate second, betweeen value and key
 * @return $AssocSAPerDomain
 *  array compact
 * @link stackoverflow.com/questions/2879132/php-associative-array-duplicate-key#answer-23783234
*/
function yuzo_fusion_array_under_the_same_key( $string, $primary_separate, $second_separate ){

    $first_array  = null;
    $second_array = null;
    $tree_array   = array();

    if( ! $string ){ return; }
    $first_array  = explode($primary_separate,$string);
    if( is_array($first_array) && $first_array){
        $k = 1;
        foreach ($first_array as $first_array_key => $first_array_value) {
            if( $first_array_value ){
                $second_array = explode($second_separate,$first_array_value);
                $k_string = str_pad("$k", 3, "0", STR_PAD_LEFT);
                $_key = isset($second_array[1]) ? $second_array[1] : null;
                if( $_key == null){ continue; }
                $_key = "{$k}_string-" . $_key;
                $tree_array[$_key] = isset($second_array[0])?$second_array[0]:null;
                $k++;
            }
        }
    }

    $AssocSAPerDomain = array();
    $TempDomain       = "";
    $TempDomain_first = 0;
    if( is_array($tree_array) ){
        foreach($tree_array as $id_domain => $id_sa){
            if( !$TempDomain && $TempDomain_first == 0 ){  $TempDomain = substr(strrchr($id_domain, "-"), 1); $TempDomain_first = 1; }
            $currentDomain = substr(strrchr($id_domain, "-"), 1);
            $AssocSAPerDomain[$currentDomain][] = $id_sa;
            $TempDomain = substr(strrchr($id_domain, "-"), 1);
        }
    }
    return $AssocSAPerDomain;
}

/**
 * Convert multidimensional array into single array
 *  only for one field
 *
 * @since   6.0
 *
 * @param 	array 	$array 		Array multidimensional
 * @return 	array
 */
function yuzo_formater_ids( $ids = array(), $field = 'ID' ){
    $out = [];
    foreach ($ids as $key => $value) {
        if( $field == null ){
            if( is_array($value) )
                foreach ($value as $k => $v)  $out[] = $v;
            else
                $out[] = $value;
        }else{
            $out[] = $value[$field];
        }
    }
    return array_diff( $out, ['-1'] );
                            //└────┘→ Array values which to delete
}

/**
 * Format a composite text and take it to a clean text
 *
 * @since   6.0     2019-07-03 15:54:19     Release
 *
 * @param   string  $text   Text to be formatted
 * @param   integer $length Maximum length that the text will show
 * @return  string
 */
function yuzo_formater_text( $text = '', $length = 50 ){
    if( ! $text ) return '';
    // remove tags
    $text = strip_tags($text);
    // remove line jump
    $text = preg_replace( "/\r|\n/", "", $text );
    // remove shortcode via wp
    $text = strip_shortcodes($text);
    // remove shortcode yuzo (just in case a bad text has leaked)
    $text = preg_replace( '/\[[^\]]+\].*\[\/[^\]]+\]/', '', $text );
    // Remove part of the string and leave the required
    $text = mb_substr( $text , 0 , $length )."...";

    return $text;
}