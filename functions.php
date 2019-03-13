<?php

function getJSON($url) {
    return json_encode(strip(file_get_contents($url)));
}
    
function strip($text) {
    return trim(preg_replace('/\s*/m', '', $text));
    // return $text;
}