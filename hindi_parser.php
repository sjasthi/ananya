<?php
// gujarati_parser.php primarily deals with splitting an input string
// into a series of logical characters
// contact Siva.Jasthi@metrostate.edu  or (Aslam) zi5379yx@metrostate.edu for any clarifications on
// hindi processing logic used in these functions.

// for unicode reference : http://www.unicode.org/charts/

//Class hindi_parser{

function hindi_stripSpaces($log_chars) {
    $code_points = hindi_parseToCodePoints(implode($log_chars));
    $build = array();
    $build_i = 0;
    for($i=0; $i < count($code_points); $i++) {
        if(strcmp($log_chars[$i], " ") == 0)
            continue;
        else {
            $build[$build_i++] = $log_chars[$i];
            if(hindi_isHalant(end($code_points[$i])) && $i + 1 < count($code_points)) {
                if($code_points[$i+1] == 32) { // if the next character is a space...
                    $build[$build_i][count($build[$build_i])] = json_decode("\u200c");
                }
            }
        }
    }
    return $build;
}

// $word expects a single utf-8 encoded word
// returns a 2 dimensional array, representing the unicoded logical characters of the word
function hindi_parseToCodePoints($word) {
    $word_array = hindi_explode($word);
    $i = 0;
    $logical_chars = array();
    $ch_buffer = array();

    while( $i < count($word_array) ) {
        $current_ch = $word_array[$i++];
        $ch_buffer[count($ch_buffer)] = $current_ch;
        if( $i == count($word_array) ) {
            $logical_chars[count($logical_chars)] = $ch_buffer;
            continue;
        }
        $next_ch = $word_array[$i];
        if(hindi_isDependent($next_ch)) {
            $ch_buffer[count($ch_buffer)] = $next_ch;
            $i++;
            $logical_chars[count($logical_chars)] = $ch_buffer;
            $ch_buffer = array();
            continue;
        }
        if(hindi_isHalant($current_ch)) {
            if(hindi_isConsonant($next_ch)) {
                if($i < count($word_array)) {
                    continue;
                }
                $ch_buffer[count($ch_buffer)] = $current_ch;
            }
            $logical_chars[count($logical_chars)] = $ch_buffer;
            $ch_buffer = array();
            continue;
        } else if(hindi_isConsonant($current_ch)) {
            if(hindi_isHalant($next_ch) || hindi_isDependentVowel($next_ch)) {
                if($i < count($word_array)) {
                    continue;
                }
                $ch_buffer[count($ch_buffer)] = $current_ch;
            }
            $logical_chars[count($logical_chars)] = $ch_buffer;
            $ch_buffer = array();
            continue;
        } else if(hindi_isVowel($current_ch)) {
            if(hindi_isDependentVowel($next_ch)) {
                $ch_buffer[count($ch_buffer)] = $next_ch;
                $i++;
            }
            $logical_chars[count($logical_chars)] = $ch_buffer;
            $ch_buffer = array();
            continue;
        }
        $logical_chars[count($logical_chars)] = $ch_buffer;
        $ch_buffer = array();
    }
    return $logical_chars;
}


// returns a 2d array of the logical characters, but using Hindi characters
function hindi_parseToLogicalCharacters($word) {
    if(is_array($word)) {
        for($i=0; $i < count($word); $i++)
            $word[$i] = hindi_parseToCharacter($word[$i]);
        return $word;
    }
    else return hindi_parseToLogicalCharacters(hindi_parseToCodePoints($word));
}

function hindi_parseToCharacter($logical_char) {
    $hindi_char = "";
    foreach($logical_char as $char) {
        if(hindi_isChar($char))	$hindi_char .= sprintf("\\u%'04s", dechex($char));
        else return chr($char);
    }
    return json_decode('"'.$hindi_char.'"');
}

function hindi_explode($word) {
    $to_explode = json_encode($word);
    $pos=0;
    $e_pos=0;
    $exploded = array();
    while($pos < strlen($to_explode) - 1) {
        if(strcmp($to_explode[$pos], "\"") == 0) {
            $pos++;
            continue;
        }
        if(strcmp($to_explode[$pos], "\\") == 0) { // if the the character in question is a slash...
            if(strcmp($to_explode[$pos + 1], "u") == 0) { // ...followed by a u...
                $char = intval(substr($to_explode, $pos + 2, 4), 16); // convert to a number
                if(hindi_isChar($char)) {
                    // if it matches, add it as a character, bump the counter up by six, and continue
                    $exploded[$e_pos++] = $char;
                    $pos += 6;
                    continue;
                }
            }
        }
        $exploded[$e_pos++] = ord($to_explode[$pos++]);
    }
    return $exploded;
}

function hindi_isConsonant($ch) {
    return ( $ch >= 0x0915 && $ch <= 0x0939 );
}

function hindi_isDependentVowel($ch) {
    return ( $ch >= 0x093e && $ch <= 0x094c );
}

function hindi_isDependent($ch) {
    return ( ($ch == 0x0900) || ($ch == 0x0901) || ($ch == 0x0902) || ($ch == 0x0903) );
}

function hindi_isVowel($ch) {
    return ($ch >= 0x0904 && $ch <= 0x0914);
}
//0c4d
function hindi_isHalant($ch) {
    return $ch == 0x094d;
}

function hindi_isNumber($ch) {
    return ($ch >= 0x0966 && $ch <= 0x096f);
}

// this function is not perfect: there are gaps in the Telugu/Hindi unicode table,
// so it is theoretically possible to get a false positive. However, other than
// intentionally feeding this function bad data, there's no practical way to get
// that false positive, and nothing harmful would happen if you did
function hindi_isChar($ch) {
    return ( $ch >= 0x0900 && $ch <= 0x097f ) || ( $ch == 0x200c );
}
//}