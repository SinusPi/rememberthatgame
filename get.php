<?php
$q = intval($_SERVER['QUERY_STRING']) ?: intval($_REQUEST['q']);
$fn = ((array)glob("data/".sprintf("%05d",$q)." - *.[mp3ngj][mp3ngj][mp3ngj]"))[0];
if (!$fn) { http_response_code(500); die(); }
//$path_parts = pathinfo($fn);
header("Content-Type: ".mime_content_type($fn));
header("Content-Length: ".filesize($fn));
readfile($fn);