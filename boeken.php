<?php

$sparqlcountquery = '
select (COUNT(?uri) AS ?c) where {

  ?uri a schema:Book ;
       schema:name ?titel ;
       schema:publication [ schema:startDate "' . $_GET['year'] . '" ;
                            schema:location ?locatie ] ;
       schema:about/rdfs:label ?onderwerp .
FILTER regex(?locatie, "Amsterdam")

}
';

$sparqlquery = '
select (COUNT(?onderwerp) AS ?c) (SAMPLE(?onderwerplabel) AS ?onderwerplabel) where {

  ?uri a schema:Book ;
       schema:name ?titel ;
       schema:publication [ schema:startDate "' . $_GET['year'] . '" ;
                            schema:location ?locatie ] ;
       schema:about ?onderwerp .
  ?onderwerp rdfs:label ?onderwerplabel .
FILTER regex(?locatie, "Amsterdam")

} GROUP BY ?onderwerp ORDER BY DESC(?c)
LIMIT 10
';

//echo $sparqlquery;

$urlcount = "http://data.bibliotheken.nl/sparql?default-graph-uri=&query=" . urlencode($sparqlcountquery) . "&format=application%2Fsparql-results%2Bjson&timeout=120000&debug=on";
$url = "http://data.bibliotheken.nl/sparql?default-graph-uri=&query=" . urlencode($sparqlquery) . "&format=application%2Fsparql-results%2Bjson&timeout=120000&debug=on";

$jsoncount = file_get_contents($urlcount);
$json = file_get_contents($url);

$datacount = json_decode($jsoncount,true);
$data = json_decode($json,true);

?>

<p>Totaal aantal boeken uitgegeven in Amsterdam:

  <?= $datacount['results']['bindings'][0]['c']['value'] ?>

</p>

<p>Met de volgende onderwerpen/genres:</p>
<div class="list">

  <?

foreach ($data['results']['bindings'] as $row) { 
	?>

  <span>
    <?= $row['c']['value'] ?>
    <?= $row['onderwerplabel']['value'] ?></span><br />



  <?php 
} 

?>

</div>
<div class="broninfo mt-1">
  <p>Sparql het zelf <a href="http://data.bibliotheken.nl/sparql">via de kb.nl!</a></p>
</div>
