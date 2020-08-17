<?php


$sparqlQueryString = "
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX rkd: <http://data.rkd.nl/def#>

SELECT ?startyear ?birthdate ?name ?rkdid WHERE {
  ?event rdf:type rkd:Place_of_Activity ;
      schema:location \"Amsterdam\"@nl ;
      schema:startDate ?start ;
      schema:endDate ?end ;
      schema:actor ?actor .
  BIND(IF(COALESCE(xsd:datetime(str(?start)), '!') != '!',
    year(xsd:dateTime(str(?start))),\"2100-01-01\"^^xsd:dateTime) AS ?startyear ) .
  BIND(IF(COALESCE(xsd:datetime(str(?end)), '!') != '!',
    year(xsd:dateTime(str(?end))),\"2100-01-01\"^^xsd:dateTime) AS ?endyear ) .
  FILTER(?startyear <= " . $_GET['year'] . ") .
  FILTER(?endyear > " . $_GET['year'] . ") .
  ?event schema:actor ?actor .
  BIND(STRAFTER(STR(?actor),\"https://data.rkd.nl/artists/\") AS ?rkdid) .
  BIND(IRI(?actor) AS ?artist) .
  ?artist rkd:Death/schema:startDate ?deathdate .
  BIND(IF(COALESCE(xsd:datetime(str(?deathdate)), '!') != '!',
    year(xsd:dateTime(str(?deathdate))),\"1100-01-01\"^^xsd:dateTime) AS ?deathdateyear ) .
  FILTER(?deathdateyear >= " . $_GET['year'] . ") .
  ?artist schema:name ?name .
  ?artist rkd:Birth/schema:startDate ?birthdate .
} 
ORDER BY DESC(?startyear)
LIMIT 25
";

$url = "https://api.data.netwerkdigitaalerfgoed.nl/datasets/rkd/rkdartists/services/rkdartists/sparql?query=" . urlencode($sparqlQueryString) . "";

$queryurl = "https://data.netwerkdigitaalerfgoed.nl/rkd/rkdartists/sparql/rkdartists#query=" . urlencode($sparqlQueryString) . "&endpoint=https%3A%2F%2Fdruid.datalegend.net%2F_api%2Fdatasets%2FAdamNet%2Fall%2Fservices%2Fendpoint%2Fsparql&requestMethod=POST&outputFormat=table";

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


?>

<table class="table">
<?php
foreach ($data['results']['bindings'] as $row) { 

	$link = "https://rkd.nl/explore/artists/" . $row['rkdid']['value'];

	$birthyear = (int)substr($row['birthdate']['value'], 0,4);
	$approximateage = $_GET['year'] - $birthyear;

	?>
	
	<tr>
		<td>
		<a target="_blank" href="<?= $link ?>">
			<strong><?= $row['name']['value'] ?></strong>
		</a><br />
		<span class="smaller">
			werkzaam in A'dam vanaf <?= $row['startyear']['value'] ?>,  ± <?= $approximateage ?> jaar oud
		</span><br />
	</td></tr>

	<?php 
} 
?>
</table>


<?php





echo '<p class="smaller">';
echo "Deze net aangekomen kunstenaars, of net met hun carriere begonnen Amsterdamse jongens (en een enkel meisje), komen uit de LOD versie van RKDartists& (die & staat er sinds kort achter omdat ook kunsthandelaren, verzamelaars, critici, etc. opgenomen zijn).";
echo '</p>';

echo '<p class="smaller"><a target="_blank" href="' . $queryurl . '">SPARQL het zelf</a>, op het NDE Druid endpoint.</p>';

?>


