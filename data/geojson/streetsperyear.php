<?php

$url = "https://adamlink.nl/data/geojson/streetsperyear/" . $_GET['year'];

//$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
//$geojson = file_get_contents($url,false,$context);

//$geojson = file_get_contents($url);

header('Content-Type: application/json');

/* Configure Curl */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

/* Get Response */
$geojson = curl_exec($ch);

curl_close($ch);




?>