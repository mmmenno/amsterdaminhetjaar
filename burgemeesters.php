<?php


$sparqlQueryString = "
SELECT DISTINCT ?m ?mLabel ?startjaar ?eindjaar ?wikipedia
WHERE 
{
 ?m p:P39 ?ambt .
 ?ambt ps:P39 wd:Q13423495 . 
 OPTIONAL {
  ?wikipedia schema:about ?m .
  FILTER (SUBSTR(str(?wikipedia), 1, 25) = \"https://nl.wikipedia.org/\")
}
 ?ambt pq:P580 ?start . 
 BIND(year(?start) AS ?startjaar) .
 FILTER(?startjaar <= " . $_GET['year'] . " )
 ?ambt pq:P582 ?eind . 
 BIND(year(?eind) AS ?eindjaar) .
 FILTER(?eindjaar >= " . $_GET['year'] . " )
 SERVICE wikibase:label { bd:serviceParam wikibase:language \"[AUTO_LANGUAGE],nl\". }
} 
GROUP BY ?m ?mLabel ?startjaar ?eindjaar ?wikipedia
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


<?php
foreach ($data['results']['bindings'] as $row) { 

	if(isset($row['wikipedia']['value'])){
		$link = $row['wikipedia']['value'];
	}else{
		$link = $row['m']['value'];
	}
	?>
	
	<a target="_blank" href="<?= $link ?>"><strong><?= $row['mLabel']['value'] ?></strong></a>
	<?= $row['startjaar']['value'] ?> - <?= $row['eindjaar']['value'] ?><br />
	

	<?php 
} 

if(count($data['results']['bindings'])<1 && $_GET['year'] > 2017){
	?>

	<a target="_blank" href="https://nl.wikipedia.org/wiki/Femke_Halsema"><strong>Femke Halsema</strong></a>
	2018 - <br />

	<?php
}elseif(count($data['results']['bindings'])<1){

	echo "geen burgemeesters gevonden - leef je uit op Wikidata!";
}

?>


