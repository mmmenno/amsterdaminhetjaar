<?php

$endyear = $_GET['year']+1;
$fromyear = $endyear-5;

$sparqlQueryString = '
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX hg: <http://rdf.histograph.io/>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SELECT DISTINCT ?gebouw ?label ?time ?actie (SAMPLE(?cho) AS ?cho) (SAMPLE(?img) AS ?img) WHERE{
	{
		?gebouw a hg:Building .
		?gebouw sem:hasEarliestBeginTimeStamp ?begintime .
		?gebouw skos:prefLabel ?label .
		?cho dct:spatial ?gebouw .
		?cho foaf:depiction ?img .
		BIND(year(?begintime) AS ?time) .
		BIND("gebouwd" AS ?actie)
	} UNION {
		?gebouw a hg:Building .
		?gebouw sem:hasEarliestEndTimeStamp ?endtime .
		?cho dct:spatial ?gebouw .
		?gebouw skos:prefLabel ?label .
		?cho foaf:depiction ?img .
		BIND(year(?endtime) AS ?time) .
		BIND("verdwenen" AS ?actie)
	} 
	FILTER(?time<' . $endyear . ')
	FILTER(?time>' . $fromyear . ')
}
ORDER BY DESC(?time)
LIMIT 10';

$url = "https://api.data.adamlink.nl/datasets/AdamNet/all/services/endpoint/sparql?default-graph-uri=&query=" . urlencode($sparqlQueryString) . "&format=application%2Fsparql-results%2Bjson&timeout=12000&debug=on";

$queryurl = "https://data.adamlink.nl/AdamNet/all/sparql/endpoint#query=" . urlencode($sparqlQueryString) . "&endpoint=https%3A%2F%2Fdata.adamlink.nl%2F_api%2Fdatasets%2FAdamNet%2Fall%2Fservices%2Fendpoint%2Fsparql&requestMethod=POST&outputFormat=table";

$json = file_get_contents($url);
$data = json_decode($json,true);

$names = array();
$checknames = array();
$checkimgs = array();
$imgs = array();

?>

<table class="table">
<?php
foreach ($data['results']['bindings'] as $row) { 

	$trstyle = "";
	if($row['actie']['value']=="verdwenen"){
		$trstyle = 'style="background-color: #EBEBEB;"';
	}
	
	?>
	
	<tr <?= $trstyle ?>>
		<td style="width: 60px;">
		<?php if(isset($row['img']['value'])){ ?>
			<a target="_blank" href="<?= $row['cho']['value'] ?>">
				<img style="width: 60px;" src="<?= $row['img']['value'] ?>" />
			</a>
		<? }else{ ?>
			<div style="width: 60px; height: 50px; background-color: #EBEBEB;"></div>
		<? } ?>
	</td><td>
		<a target="_blank" href="<?= $row['gebouw']['value'] ?>">
			<strong><?= $row['label']['value'] ?></strong>
		</a><br />
		<span class="smaller"><?= $row['time']['value'] ?> - <?= $row['actie']['value'] ?></span><br />
	</td></tr>

	<?php 
} 
?>
</table>


<?php

if(count($data['results']['bindings'])<1){
	echo '<p class="smaller">';
	echo "Geen gebouwen gevonden die onlangs gebouwd of verdwenen zijn.";
	echo '</p>';
}

?>

<p class="smaller">We gebruiken hier de <a target="_blank" href="https://adamlink.nl/geo/buildings/list">Adamlink gebouwenlijst</a>, vooral omdat die verbonden zijn met afbeeldingen in Amsterdamse collecties. In de BAG heeft <a href="https://code.waag.org/buildings/#52.3662,4.9121,13" target="_blank">elk gebouw een bouwjaar</a> (volledig, maar niet altijd even precies), op Wikidata vind je veel bouwjaren, <a target="_blank" href="http://verdwenengebouwen.nl/">Verdwenen Gebouwen</a> weet weer veel van ... juist.</p>

<p class="smaller"><a target="_blank" href="<?= $queryurl ?>">SPARQL het zelf</a>, bij Adamlink.</p>




