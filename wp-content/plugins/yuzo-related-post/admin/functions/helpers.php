<?php

if( ! function_exists('yuzo_object_to_array')  ){
/**
 * Convert a nested object to every nested array
 *
 * @since   6.0     2019-04-24 13:02:34     Release
 * @param   object  $obj                    Var object
 */
function yuzo_object_to_array($obj) {
    //only process if it's an object or array being passed to the function
    if(is_object($obj) || is_array($obj)) {
        $ret = (array) $obj;
        foreach($ret as &$item) {
            //recursively process EACH element regardless of type
            $item = yuzo_object_to_array($item);
        }
        return $ret;
    }
    //otherwise (i.e. for scalar values) return without modification
    else {
        return $obj;
    }
}
}

if( ! function_exists('yuzo_sanitize_for_string')  ){
function yuzo_sanitize_for_string( $s ){
    $s = str_replace([0,1,2,3,4,5,6,7,8,9,'-','.','b','c','f',
                    'g','h','j','k','o','p','q','r','s','u',
                    'v','w','x','y','z'],'', $s);
    $array_words = explode(" ", $s);
    $array_new_words = [];
    for( $i = count($array_words)-1; $i>=0; $i-- ){
        $array_new_words[] = $array_words[$i];
    }
    $news_letter = '';
    $news_word = [];
    if( ! empty( $array_new_words ) ){
        foreach( $array_new_words as $letter){
            if( ! empty( $letter ) ){
                $array_letter = str_split($letter);
                $news_letter = '';
                for( $i = count($array_letter)-1; $i>=0; $i-- ){
                    $news_letter .= $array_letter[$i];
                }
                $news_word[] = ($news_letter);
            }
        }
    }
    return implode(" ", $news_word);
}
}