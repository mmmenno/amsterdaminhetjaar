<?php

$sparqlquery = '
PREFIX void: <http://rdfs.org/ns/void#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX hg: <http://rdf.histograph.io/>
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
SELECT DISTINCT ?werk (SAMPLE(?titel) AS ?titel) (SAMPLE(?img) AS ?img) (SAMPLE(?straatnaam) AS ?naam) ?set WHERE {
  ?werk sem:hasBeginTimeStamp ?begin .
  ?werk void:inDataset ?set .
  ?werk dct:spatial ?street .
  ?werk dc:type ?type .
  ?street a hg:Street .
  ?street skos:prefLabel ?straatnaam .
  BIND(IF(COALESCE(xsd:datetime(str(?begin)), "!") != "!",
     year(xsd:dateTime(str(?begin))),"3"^^xsd:integer) AS ?jaar ) .
  ?werk dc:title ?titel .
  ?werk foaf:depiction ?img .
  FILTER(?jaar=' . $_GET['year'] . ')
  FILTER(?type != <http://vocab.getty.edu/aat/300034787>)
}
GROUP BY ?werk ?set
LIMIT 20';

//echo $sparqlquery;


$url = "https://api.data.adamlink.nl/datasets/AdamNet/all/services/endpoint/sparql?default-graph-uri=&query=" . urlencode($sparqlquery) . "&format=application%2Fsparql-results%2Bjson&timeout=120000&debug=on";


// just for the link:

$linksparqlquery = '
PREFIX void: <http://rdfs.org/ns/void#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX hg: <http://rdf.histograph.io/>
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
SELECT DISTINCT ?werk ?widget WHERE {
  ?werk sem:hasBeginTimeStamp ?begin .
  ?werk void:inDataset ?set .
  ?werk dct:spatial ?street .
  ?werk dc:type ?type .
  ?street a hg:Street .
  ?street skos:prefLabel ?straatnaam .
  BIND(IF(COALESCE(xsd:datetime(str(?begin)), "!") != "!",
     year(xsd:dateTime(str(?begin))),"3"^^xsd:integer) AS ?jaar ) .
  ?werk dc:title ?titel .
  ?werk foaf:depiction ?img .
  BIND(CONCAT(\'<a href="\',?werk,\'"><img style="height:170px;" src="\',?img,\'"></a>\',?straatnaam) AS ?widget)
  FILTER(?jaar=' . $_GET['year'] . ')
  FILTER(?type != <http://vocab.getty.edu/aat/300034787>)
}
GROUP BY ?werk ?set
LIMIT 20';

$queryurl = "https://data.adamlink.nl/AdamNet/all/sparql/endpoint#query=" . urlencode($linksparqlquery) . "&endpoint=https%3A%2F%2Fdata.adamlink.nl%2F_api%2Fdatasets%2FAdamNet%2Fall%2Fservices%2Fendpoint%2Fsparql&requestMethod=POST&outputFormat=gallery";


$json = file_get_contents($url);

$data = json_decode($json,true);

$i = 0;
$shownr = 16;
if(count($data['results']['bindings'])>$shownr){
  $more = "meer";
}else{
  $more = "deze";
}

$names = array();
$checknames = array();
$checkimgs = array();
$imgs = array();

$i=0;
foreach ($data['results']['bindings'] as $row) { 
  $i++;
  if($i<=$shownr){
  	?>

  	<a target="_blank" href="<?= $row['werk']['value'] ?>" title="<?= $row['titel']['value'] ?> | <?= $row['naam']['value'] ?>"><img src="<?= $row['img']['value'] ?>"></a>
  	

  	<?php 
  }
} 


?>

<p class="smaller">Doorzoek de AdamNet collecties op <a target="_blank" href="<?= $queryurl ?>"><?= $more ?> straatbeelden uit <?= $_GET['year'] ?></a></p>
