<?php

if($_GET['year']>1795){
	?>
		<p class="smaller">De vroedschap werd in 1795 opgeheven</p>


	<?php
	die;
}

$sparqlQueryString = <<< 'SPARQL'
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX wikibase: <http://wikiba.se/ontology#>
PREFIX p: <http://www.wikidata.org/prop/>
PREFIX ps: <http://www.wikidata.org/prop/statement/>
PREFIX pq: <http://www.wikidata.org/prop/qualifier/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX bd: <http://www.bigdata.com/rdf#>
SELECT DISTINCT ?m ?mLabel (SAMPLE(?geb) AS ?geb) (SAMPLE(?sterf) AS ?sterf) ?startjaar ?eindjaar
WHERE 
{
 ?m p:P463 ?org .
 ?org ps:P463 wd:Q56764476 . 
 ?org pq:P580 ?start . 
 ?org pq:P582 ?eind . 
 OPTIONAL { ?m wdt:P569 ?geb . }
 OPTIONAL { ?m wdt:P570 ?sterf . }
 BIND(year(?start) AS ?startjaar) .
 BIND(year(?eind) AS ?eindjaar) .
SPARQL;

$sparqlQueryString .= "
 FILTER(?startjaar <= " . $_GET['year'] . " )
 FILTER(?eindjaar >= " . $_GET['year'] . " )
 SERVICE wikibase:label { bd:serviceParam wikibase:language \"[AUTO_LANGUAGE],nl\". }
} 
GROUP BY ?m ?mLabel ?startjaar ?eindjaar
ORDER BY ?startjaar
";


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

$data = json_decode($response, true);


//print_r($data);

$names = array();
$checknames = array();
$checkimgs = array();
$imgs = array();
?>

<table class="table">
<?php
foreach ($data['results']['bindings'] as $row) { 
	?>
	<tr><td>
	<a target="_blank" href="<?= $row['m']['value'] ?>"><strong><?= $row['mLabel']['value'] ?></strong></a>
	<span class="smaller"><?= $row['startjaar']['value'] ?> - <?= $row['eindjaar']['value'] ?></span>
	</td></tr>

	<?php 
} 

?>
</table>


<p class="smaller">Deze <?= count($data['results']['bindings']) ?> vroedschapsleden (gesorteerd naar vroedschapssenioriteit) komen uit Wikidata, je kunt ze daar <a target="_blank" href="<?= $queryurl ?>">zelf SPARQLen</a></p>
