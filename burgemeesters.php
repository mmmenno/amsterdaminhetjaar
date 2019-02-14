<?php


$sparqlQueryString = "
SELECT DISTINCT ?m ?mLabel ?startjaar ?eindjaar ?wikipedia ?afb
WHERE 
{
 ?m p:P39 ?ambt .
 ?ambt ps:P39 wd:Q13423495 . 
 OPTIONAL { ?m wdt:P18 ?afb . }
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
GROUP BY ?m ?mLabel ?startjaar ?eindjaar ?wikipedia ?afb
ORDER BY ?startjaar
";

//$sparqlQueryString = "#defaultView:ImageGrid\n" . $sparqlQueryString;
$queryurl = "https://query.wikidata.org/#" . rawurlencode($sparqlQueryString);


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

if(count($data['results']['bindings'])<1 && $_GET['year'] > 2017){
	$data['results']['bindings'][] = array(
		"wikipedia" => array("value" => "https://nl.wikipedia.org/wiki/Femke_Halsema"),
		"mLabel" => array("value" => "Femke Halsema"),
		"startjaar" => array("value" => "2018"),
		"eindjaar" => array("value" => ""),
		"afb" => array("value" => "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Femke_Halsema_2.jpg/800px-Femke_Halsema_2.jpg")
	);
}

?>

<table class="table">
<?php
foreach ($data['results']['bindings'] as $row) { 

	if(isset($row['wikipedia']['value'])){
		$link = $row['wikipedia']['value'];
	}else{
		$link = $row['m']['value'];
	}
	?>
	
	<tr>
		<td style="width: 60px;">
		<?php if(isset($row['afb']['value'])){ ?>
			<a target="_blank" href="<?= $link ?>">
				<img style="width: 60px;" src="<?= $row['afb']['value'] ?>" />
			</a>
		<? }else{ ?>
			<div style="width: 60px; height: 50px; background-color: #EBEBEB;"></div>
		<? } ?>
	</td><td>
		<a target="_blank" href="<?= $link ?>">
			<strong><?= $row['mLabel']['value'] ?></strong>
		</a><br />
		<span class="smaller"><?= $row['startjaar']['value'] ?> - <?= $row['eindjaar']['value'] ?></span><br />
	</td></tr>

	<?php 
} 
?>
</table>


<?php

if(count($data['results']['bindings'])<1){
	echo '<p class="smaller">';
	echo "We hebben geen burgemeesters gevonden voor dit jaar. We halen deze data van Wikidata. Je kunt daar zelf data toevoegen en verbeteren.";
	echo '</p>';
}



echo '<p class="smaller">';
if($_GET['year']>1823){
	echo "Vanaf 1824 had Amsterdam één burgemeester.";
}elseif($_GET['year']>1812){
	echo "Na de Franse Tijd had Amsterdam, tot 1824, weer meerdere burgemeesters.";
}
echo '</p>';


echo '<p class="smaller">';
if($_GET['year']>1900){
	echo '<a target="_blank" href="burgemeesters/index.php?from=1900&until=2020">Bekijk alle burgemeesters na 1900</a>.';
}elseif($_GET['year']>1800){
	echo '<a target="_blank" href="burgemeesters/index.php?from=1800&until=1900">Bekijk alle 19e-eeuwse burgemeesters</a>.';
}elseif($_GET['year']>1700){
	echo '<a target="_blank" href="burgemeesters/index.php?from=1700&until=1800">Bekijk alle 18e-eeuwse burgemeesters</a>.';
}elseif($_GET['year']>1600){
	echo '<a target="_blank" href="burgemeesters/index.php?from=1600&until=1700">Bekijk alle 17e-eeuwse burgemeesters</a>.';
}elseif($_GET['year']>1000){
	echo '<a target="_blank" href="burgemeesters/index.php?from=1000&until=1600">Bekijk alle burgemeesters voor 1600</a>.';
}
echo '</p>';

echo '<p class="smaller">Of, <a target="_blank" href="' . $queryurl . '">SPARQL het zelf</a>, op Wikidata.</p>';

?>


