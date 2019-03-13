<?php

$url = "https://adamlink.nl/data/geojson/streetsperyear/" . $_GET['year'];

$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
$geojson = file_get_contents($url,false,$context);

//$geojson = file_get_contents($url);

echo $geojson;

die;





?>