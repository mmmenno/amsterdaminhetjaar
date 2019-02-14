<?php


$url = "https://adamlink.nl/data/geojson/streetsperyear/" . $_GET['year'];

$geojson = file_get_contents($url);

echo $geojson;

die;





?>