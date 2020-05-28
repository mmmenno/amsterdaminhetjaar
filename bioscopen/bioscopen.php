<?php

$nextyear = $_GET['year']+1;

$sparqlQueryString = "
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX dbo: <http://dbpedia.org/ontology/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

SELECT 	
?venue ?venuename ?wkt ?captime ?cap ?begin ?end
WHERE {
	?venue a schema:MovieTheater .
  	?venue schema:temporalCoverage ?existence .
  	?existence sem:hasEarliestBeginTimeStamp ?begin .
  	OPTIONAL{
  		?existence sem:hasLatestEndTimeStamp ?end .
  	}
	FILTER(?begin <= \"" . $_GET['year'] . "\"^^xsd:gYear) .
  	?venue schema:location ?place .
	?venue schema:name ?venuename .
	?place schema:address/schema:addressLocality 'Amsterdam' .
  	?place geo:hasGeometry/geo:asWKT ?wkt .
  	OPTIONAL{
	  	?venue dbo:seatingCapacity ?capnode .
	  	?capnode dbo:seatingCapacity ?cap .
	  	?capnode sem:hasLatestBeginTimeStamp ?captime .
		FILTER(?captime <= \"" . $nextyear . "\"^^xsd:gYear)	
	}
}
ORDER BY ASC(?venue) DESC(?captime)
limit 250
";

$url = "https://data.create.humanities.uva.nl/sparql?query=" . urlencode($sparqlQueryString) . "";

$queryurl = "https://data.create.humanities.uva.nl/#query=" . urlencode($sparqlQueryString) . "&endpoint=https%3A%2F%2Fdata.create.humanities.uva.nl%2Fsparql&requestMethod=POST&tabTitle=Query&headers=%7B%7D&contentTypeConstruct=text%2Fturtle%2C*%2F*%3Bq%3D0.9&contentTypeSelect=application%2Fsparql-results%2Bjson%2C*%2F*%3Bq%3D0.9&outputFormat=table";


// Druid does not like url parameters, send accept header instead
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "Accept: application/sparql-results+json\r\n"
    ]
];

$context = stream_context_create($opts);

// Open the file using the HTTP headers set above
$json = file_get_contents($url, false, $context);

$data = json_decode($json,true);

//print_r($data);

$cinemas = array();
$nrs = array();

foreach ($data['results']['bindings'] as $k => $v) {

	if(isset($v['end']['value'])){
		$endyear = substr($v['end']['value'],0,4);
		if($endyear < $nextyear){
			continue;
		}
	}

	if(!isset($cinemas[$v['venue']['value']])){

		$cinemas[$v['venue']['value']] = array(
			"bioscoop" => $v['venuename']['value'],
			"capacity" => $v['cap']['value'],
			"captime" => $v['captime']['value'],
			"begin" => $v['begin']['value']
		);
		$nrs[$v['venue']['value']] = $v['cap']['value'];
	}


	
}
arsort($nrs);
//print_r($nrs);
//print_r($cinemas);

?>

<table class="table">
<?php
foreach ($nrs as $k => $v) { 

	$total += $cinemas[$k]['capacity'];

	if(strlen($cinemas[$k]['captime'])){
		$capacity = $cinemas[$k]['capacity'];
		$description = "Bestaat sinds " . substr($cinemas[$k]['begin'],0,4) . ", capaciteit gemeten in " . substr($cinemas[$k]['captime'],0,4);
	}else{
		$capacity = "?";
		$description = "Bestaat sinds " . substr($cinemas[$k]['begin'],0,4) . ", capaciteit onbekend";
	}
	?>
	
	<tr>
		<td class="nroftd">
      		<div class="nrof"><?= $capacity ?></div>
		</td>
		<td>
			<a target="_blank" href="<?= $link ?>">
				<strong><a target="_blank" href="<?= $k ?>"><?= $cinemas[$k]['bioscoop'] ?></a></strong>
			</a><br />
			<span class="evensmaller">
				<?= $description ?>
			</span><br />
	</td></tr>

	<?php 
} 
?>
</table>





<?php if(count($cinemas)){ ?>
	<p class="smaller">
		De bioscopen zijn gesorteerd op capaciteit. De cijfers zijn zeker niet altijd recent en compleet, maar het lijkt erop dat er dit jaar zeker voor <?= $total ?> mensen een zitplaats was.
	</p>
<?php }else{ ?> 
	<p class="smaller">
		
	</p>
<?php } ?> 

<p class="smaller">
	<a target="_blank" href="<?= $queryurl ?>">SPARQL het zelf</a> in de Cinema Context data, op de CREATE sparql endpoint.
</p>




