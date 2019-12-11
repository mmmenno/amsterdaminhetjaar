<?php


$sparqlQueryString = "
SELECT ?item ?itemLabel ?afb ?wwid ?startyear ?endyear WHERE {
  ?item wdt:P2533 ?wwid .
  ?item wdt:P21 wd:Q6581072 .
  OPTIONAL{
  	?item wdt:P18 ?afb .
  }
  ?item p:P937 ?werklocatie .
  ?werklocatie ps:P937 wd:Q727 .
  ?werklocatie pq:P580 ?start . 
  ?werklocatie pq:P582 ?end . 
  BIND(year(?start) AS ?startyear) .
  FILTER(?startyear <= " . $_GET['year'] . " )
  BIND(year(?end) AS ?endyear) .
  FILTER(?endyear >= " . $_GET['year'] . " )
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
order by ?startyear
limit 1000
";


// got to fake, wikidata endpoint returned 403 error without user agent, all of a sudden
$endpointUrl = 'https://query.wikidata.org/sparql';
$url = $endpointUrl . '?query=' . urlencode($sparqlQueryString) . "&format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
$headers = [
    'Accept: application/sparql-results+json'
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec ($ch);

curl_close ($ch);



//$sparqlQueryString = "#defaultView:ImageGrid\n" . $sparqlQueryString;
$queryurl = "https://query.wikidata.org/#" . rawurlencode($sparqlQueryString);

/*

$opts = [
		    'http' => [
		        'method' => 'GET',
		        'header' => [
		            'Accept: application/sparql-results+json'
		        ],
		    ],
		];
$context = stream_context_create($opts);
$endpointUrl = 'https://query.wikidata.org/sparql';
$url = $endpointUrl . '?query=' . urlencode($sparqlQueryString);
$response = file_get_contents($url, false, $context);

*/


$data = json_decode($response, true);


//print_r($response);

$names = array();
$checknames = array();
$checkimgs = array();
$imgs = array();


?>

<table class="table">
<?php
foreach ($data['results']['bindings'] as $row) { 

	$link = $row['item']['value'];

	?>
	
	<tr>
		<td style="width: 60px;">
		<?php if(isset($row['afb']['value'])){ ?>
			<a target="_blank" href="<?= $link ?>">
				<img style="width: 60px;" src="<?= $row['afb']['value'] ?>?width=300px" />
			</a>
		<? }else{ ?>
			<div style="width: 60px; height: 50px; background-color: #EBEBEB;"></div>
		<? } ?>
	</td><td>
		<a target="_blank" href="<?= $link ?>">
			<strong><?= $row['itemLabel']['value'] ?></strong>
		</a><br />
		<span class="smaller"><?= $row['startyear']['value'] ?> - <?= $row['endyear']['value'] ?></span><br />
	</td></tr>

	<?php 
} 
?>
</table>

<p class="smaller">
	Gezocht is naar schrijfsters met een 'WomenWriters-identificatiecode' op Wikidata die 'Amsterdam' als werklocatie hadden in het gevraagde jaar.
</p>


<?php

echo '<p class="smaller"><a target="_blank" href="' . $queryurl . '">SPARQL het zelf</a>, op Wikidata.</p>';

?>


