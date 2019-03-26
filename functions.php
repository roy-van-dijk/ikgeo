<?php

function getJSON($url) {
    $json = json_encode(strip(file_get_contents($url)));
    $json_parsed = "";
    // for($i = 0; $i < $json)
    // echo $json;
    return $json;
}
    
function strip($text) {
    return trim(preg_replace('/\s*/m', '', $text));
    // return $text;
}