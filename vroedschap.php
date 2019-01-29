<?php

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
$data = json_decode($response, true);


//print_r($data);

$names = array();
$checknames = array();
$checkimgs = array();
$imgs = array();
?>

<ol>
<?php
foreach ($data['results']['bindings'] as $row) { 
	?>
	<li>
	<a target="_blank" href="<?= $row['m']['value'] ?>"><strong><?= $row['mLabel']['value'] ?></strong></a>
	<?= $row['startjaar']['value'] ?> - <?= $row['eindjaar']['value'] ?>
	</li>

	<?php 
} 

?>
</ol>

