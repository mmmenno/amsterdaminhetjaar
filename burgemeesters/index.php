<?php

if(isset($_GET['from'])){
	$from = $_GET['from'];
}else{
	$from = 1900;
}

if(isset($_GET['until'])){
	$until = $_GET['until'];
}else{
	$until = 2020;
}

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
 FILTER(?startjaar <= " . $until . " )
 ?ambt pq:P582 ?eind . 
 BIND(year(?eind) AS ?eindjaar) .
 FILTER(?eindjaar >= " . $from . " )
 SERVICE wikibase:label { bd:serviceParam wikibase:language \"[AUTO_LANGUAGE],nl\". }
} 
GROUP BY ?m ?mLabel ?startjaar ?eindjaar ?wikipedia ?afb
ORDER BY ?startjaar
";

//$sparqlQueryString = "#defaultView:ImageGrid\n" . $sparqlQueryString;
$queryurl = "https://query.wikidata.org/#" . rawurlencode($sparqlQueryString);

/* this way doesn't work as per june 17 2019??
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

if($until > 2017){
	$data['results']['bindings'][] = array(
		"wikipedia" => array("value" => "https://nl.wikipedia.org/wiki/Femke_Halsema"),
		"mLabel" => array("value" => "Femke Halsema"),
		"startjaar" => array("value" => "2018"),
		"eindjaar" => array("value" => ""),
		"afb" => array("value" => "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Femke_Halsema_2.jpg/800px-Femke_Halsema_2.jpg")
	);
}

//print_r($data);
$allrows = $data['results']['bindings'];
$rows = array();

foreach ($allrows as $k => $v) {
	if(!array_key_exists($v['m']['value'], $rows)){
		$rows[$v['m']['value']] = $v;
	}
	if($v['startjaar']['value'] == $v['eindjaar']['value']){
		$period = $v['startjaar']['value'];
	}else{
		$period = $v['startjaar']['value'] . "-" . $v['eindjaar']['value'];
	}
	if(isset($rows[$v['m']['value']]['periods'])){
		if(!in_array($period,$rows[$v['m']['value']]['periods'])){
			$rows[$v['m']['value']]['periods'][] = $period;
		}
	}else{
		$rows[$v['m']['value']]['periods'][] = $period;
	}
}
$rows = array_values($rows); // nice, wdids as keys, but we need them numeric later on

//print_r($rows);

$onethird = ceil(count($rows)/3);
$twothirds = $onethird*2;


?>
<!DOCTYPE html>
<html>
<head>
	
	<title>Amsterdamse burgemeesters</title>

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link href="https://fonts.googleapis.com/css?family=Nunito:300,700" rel="stylesheet">

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>


    <link rel="stylesheet" href="../assets/styles.css" />

	<style>

	</style>

	
</head>
<body>

<div class="container-fluid">
	<h1>A'damse burgemeesters <?= $_GET['from'] ?>-<?= $_GET['until'] ?></h1>
</div>

<div class="container-fluid">
	<div class="col-md-3">
		<h3>De data</h3>
		<div class="opencontent">
			De hier getoonde data is afkomstig van <a target="_blank" href="https://www.wikidata.org">Wikidata</a>. Mist er data of klopt er iets niet? Je kunt er zelf gegevens of afbeeldingen toevoegen, die worden hier dan ook zichtbaar. Je kan deze query ook daar <a target="_blank" href="<?= $queryurl ?>">bekijken en aanpassen</a> naar je eigen behoefte.
		</div>

		<h3>Amsterdam in ....</h3>
		<div class="opencontent">
			Dit burgemeestersoverzicht is onderdeel van het project <a href="../">'Amsterdam in ....'</a>, dat wil laten zien welke Amsterdamse bronnen al beschikbaar zijn als linked open data.
		</div>
		
	</div>
	<div class="col-md-3">
		<?php
			for($i=0; $i<$onethird; $i++) { 

				//if(isset($rows[$i]['wikipedia']['value'])){
				//	$link = $rows[$i]['wikipedia']['value'];
				//}else{
					$link = $rows[$i]['m']['value'];
				//}
				$allyears = implode(", ",$rows[$i]['periods']);
				?>
				<h3>
					<a target="_blank" href="<?= $link ?>">
					<?= $rows[$i]['mLabel']['value'] ?>
					</a>
					<a target="_blank" href="https://tools.wmflabs.org/wikidata-todo/relator/#/<?= str_replace("http://www.wikidata.org/entity/", "", $rows[$i]['m']['value']) ?>">⌖</a>
					<?php if(isset($rows[$i]['wikipedia']['value'])){ ?>
						<a target="_blank" href="<?= $rows[$i]['wikipedia']['value'] ?>">
							<img style="height: 16px;" src="../assets/img/wp.png" />
						</a>
					<?php } ?>
				</h3>

				<p class="smaller"><strong>
					<?= $allyears ?></strong>
				</p>
				<?php if(isset($rows[$i]['afb']['value'])){ ?>
					<img style="width: 100%;" src="<?= $rows[$i]['afb']['value'] ?>?width=300px" />
				<?php } ?>
				<?php 
			} 

		?>
	</div>
	<div class="col-md-3">
		<?php
			for($i=$onethird; $i<$twothirds; $i++) { 

				//if(isset($rows[$i]['wikipedia']['value'])){
				//	$link = $rows[$i]['wikipedia']['value'];
				//}else{
					$link = $rows[$i]['m']['value'];
				//}
				$allyears = implode(", ",$rows[$i]['periods']);
				?>
				<h3>
					<a target="_blank" href="<?= $link ?>">
					<?= $rows[$i]['mLabel']['value'] ?>
					</a>
					<a target="_blank" href="https://tools.wmflabs.org/wikidata-todo/relator/#/<?= str_replace("http://www.wikidata.org/entity/", "", $rows[$i]['m']['value']) ?>">⌖</a>
					<?php if(isset($rows[$i]['wikipedia']['value'])){ ?>
						<a target="_blank" href="<?= $rows[$i]['wikipedia']['value'] ?>">
							<img style="height: 16px;" src="../assets/img/wp.png" />
						</a>
					<?php } ?>
				</h3>

				<p class="smaller"><strong>
					<?= $allyears ?></strong>
				</p>
				<?php if(isset($rows[$i]['afb']['value'])){ ?>
					<img style="width: 100%;" src="<?= $rows[$i]['afb']['value'] ?>?width=300px" />
				<?php } ?>
				<?php 
			} 

		?>
	</div>
	<div class="col-md-3">
		<?php
			for($i=$twothirds; $i<count($rows); $i++) { 

				//if(isset($rows[$i]['wikipedia']['value'])){
				//	$link = $rows[$i]['wikipedia']['value'];
				//}else{
					$link = $rows[$i]['m']['value'];
				//}
				$allyears = implode(", ",$rows[$i]['periods']);
				?>
				<h3>
					<a target="_blank" href="<?= $link ?>">
					<?= $rows[$i]['mLabel']['value'] ?>
					</a>
					<a target="_blank" href="https://tools.wmflabs.org/wikidata-todo/relator/#/<?= str_replace("http://www.wikidata.org/entity/", "", $rows[$i]['m']['value']) ?>">⌖</a>
					<?php if(isset($rows[$i]['wikipedia']['value'])){ ?>
						<a target="_blank" href="<?= $rows[$i]['wikipedia']['value'] ?>">
							<img style="height: 16px;" src="../assets/img/wp.png" />
						</a>
					<?php } ?>
				</h3>

				<p class="smaller"><strong>
					<?= $allyears ?></strong>
				</p>
				<?php if(isset($rows[$i]['afb']['value'])){ ?>
					<img style="width: 100%;" src="<?= $rows[$i]['afb']['value'] ?>?width=300px" />
				<?php } ?>
				<?php 
			} 

		?>
	</div>
</div>



<script>

	
	$(document).ready(function(){
		
		
		

	});

</script>



</body>
</html>
