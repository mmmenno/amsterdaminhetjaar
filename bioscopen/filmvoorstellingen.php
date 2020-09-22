<?php

$nextyear = $_GET['year']+1;

$sparqlQueryString = "
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SELECT 	
	?film ?filmtitle 
	(COUNT(?program) AS ?number) 
	(GROUP_CONCAT(DISTINCT ?venuename; SEPARATOR=\", \") AS ?cinemas) 
WHERE {
	?film a schema:Movie .
	?film schema:name ?filmtitle .
	?program schema:subEvent/schema:workPresented ?film .
  	FILTER (!REGEX(str(?program),\"http://www.cinemacontext.nl/id/V$\"))
	?program schema:location ?venue .
	?program schema:startDate ?date .
	FILTER(?date > \"" . $_GET['year'] . "\"^^xsd:gYear)
	FILTER(?date < \"" . $nextyear . "\"^^xsd:gYear)
	?venue schema:location ?place .
	?venue schema:name ?venuename .
	?place schema:address/schema:addressLocality 'Amsterdam' .
}
GROUP BY ?film ?filmtitle
ORDER BY DESC(?number)
limit 25
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

$list = array();
$singles = array();

foreach ($data['results']['bindings'] as $k => $v) {
	$cinemas = explode(", ", $v['cinemas']['value']);
	if(count($cinemas) < 2){
		if(count($singles)==0){
			$singles = array(
				"film" => $v['film']['value'],
				"filmtitle" => $v['filmtitle']['value'],
				"number" => $v['number']['value'],
				"cinema" =>  $cinemas[0]
			);
		}
		continue;
	}

	$list[] = array(
		"film" => $v['film']['value'],
		"filmtitle" => $v['filmtitle']['value'],
		"number" => $v['number']['value'],
		"cinemas" => $cinemas
	);

	if(count($list)==10){
		break;
	}
}

//print_r($list);

?>

<table class="table">
<?php
foreach ($list as $row) { 

	$bioscopen = implode(", ", $row['cinemas']);
	
	?>
	
	<tr>
		<td class="nroftd">
      		<div class="nrof"><?= $row['number'] ?></div>
		</td>
		<td>
			<a target="_blank" href="<?= $link ?>">
				<strong><a target="_blank" href="<?= $row['film'] ?>"><?= $row['filmtitle'] ?></a></strong>
			</a><br />
			<span class="evensmaller">
				In: <?= $bioscopen ?>
			</span><br />
	</td></tr>

	<?php 
} 
?>
</table>


<?php if(count($singles)){ ?>

	<p class="smaller">
		De film <a href="<?= $singles['film'] ?>"><?= $singles['filmtitle'] ?></a> is <?= $singles['number'] ?> keer in een programma opgenomen, maar niet in het overzicht meegenomen, omdat ie alleen in <?= $singles['cinema'] ?> vertoond werd.
	</p>

<?php } ?> 


<?php if(count($list)){ ?>
	<p class="smaller">
		Het nummer toont het aantal weken waarin de film in een bioscoop liep.
	</p>
<?php }else{ ?> 
	<p class="smaller">
		Niet elk jaar is ingevoerd. Bekijk <a target="_blank" href="https://data.create.humanities.uva.nl/#query=PREFIX%20xsd%3A%20%3Chttp%3A%2F%2Fwww.w3.org%2F2001%2FXMLSchema%23%3E%0APREFIX%20schema%3A%20%3Chttp%3A%2F%2Fschema.org%2F%3E%0APREFIX%20rdfs%3A%20%3Chttp%3A%2F%2Fwww.w3.org%2F2000%2F01%2Frdf-schema%23%3E%0ASELECT%20%3Fyear%20(COUNT(%3Fprogram)%20AS%20%3FnumberOfProgrammes)%20WHERE%20%7B%0A%20%20%3Fprogram%20a%20schema%3AEvent%20.%0A%20%20%3Fprogram%20schema%3Alocation%20%3Fvenue%20.%0A%20%20%3Fprogram%20schema%3AstartDate%20%3Fdate%20.%0A%20%20BIND(IF(COALESCE(xsd%3Adatetime(str(%3Fdate))%2C%20'!')%20!%3D%20'!'%2C%0A%20%20%20%20year(xsd%3AdateTime(str(%3Fdate)))%2C%222100%22%5E%5Exsd%3AgYear)%20AS%20%3Fyear%20)%20.%0A%20%20%3Fvenue%20schema%3Alocation%20%3Fplace%20.%0A%20%20%3Fplace%20schema%3Aaddress%2Fschema%3AaddressLocality%20'Amsterdam'%20.%0A%7D%0AGROUP%20BY%20%3Fyear%0AORDER%20BY%20ASC(%3Fyear)%0Alimit%20125&endpoint=https%3A%2F%2Fdata.create.humanities.uva.nl%2Fsparql&requestMethod=POST&tabTitle=Query&headers=%7B%7D&contentTypeConstruct=text%2Fturtle%2C*%2F*%3Bq%3D0.9&contentTypeSelect=application%2Fsparql-results%2Bjson%2C*%2F*%3Bq%3D0.9&outputFormat=table&outputSettings=%7B%22pageSize%22%3A100%7D">hier</a> een lijst met aantallen voorstellingen per jaar
	</p>
<?php } ?> 

<p class="smaller">
	<a target="_blank" href="<?= $queryurl ?>">SPARQL het zelf</a> in de Cinema Context data, op de CREATE sparql endpoint.
</p>




