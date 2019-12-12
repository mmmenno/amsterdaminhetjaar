<?php

$sparqlendpoint = "http://data.bibliotheken.nl/sparql";
$note = 'Sparql het zelf via de <a href="http://data.bibliotheken.nl/sparql">sparqlendpoint</a> van de KB!';

if ($_GET['year'] >= 1800) {

  $source = "<a target='_blank' href='http://data.bibliotheken.nl/'>KB, Nederlandse Bibliografie Totaal (NBT)</a>";
  
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

$urlcount = $sparqlendpoint . "?default-graph-uri=&query=" . urlencode($sparqlcountquery) . "&format=application%2Fsparql-results%2Bjson&timeout=120000&debug=on";
$url = $sparqlendpoint . "?default-graph-uri=&query=" . urlencode($sparqlquery) . "&format=application%2Fsparql-results%2Bjson&timeout=120000&debug=on";


} elseif ($_GET['year'] < 1800) {

  // STCN

  $source = "<a target='_blank' href='https://www.kb.nl/organisatie/onderzoek-expertise/informatie-infrastructuur-diensten-voor-bibliotheken/short-title-catalogue-netherlands-stcn'>STCN</a>";
  
  $sparqlcountquery = '
  SELECT (COUNT(distinct ?boek) AS ?c) WHERE { 

    ?boek foaf:isPrimaryTopicOf/void:inDataset <http://data.bibliotheken.nl/id/dataset/stcn> .

    ?boek schema:name ?boektitel ;
          schema:publication ?pubEvent .

    # There is a typo in here!
    ?pubEvent <http://semanticweb.cs.vu.nl/2009/11/sem/hasEarliestBeginTimestamp> ?earliestTimeStamp ;
              <http://semanticweb.cs.vu.nl/2009/11/sem/hasLatestEndTimestamp> ?latestEndTimeStamp ;
              schema:description ?pubDescription .

    BIND("'. $_GET['year'] .'"^^xsd:gYear AS ?year) .

    FILTER (regex(?pubDescription, "amste", "i"))
    FILTER (?earliestTimeStamp <= ?year && ?latestEndTimeStamp >= ?year)

  }';
  
  $sparqlquery = '
  SELECT (COUNT(distinct ?boek) AS ?c) (SAMPLE(?onderwerplabel) AS ?onderwerplabel) WHERE { 

      ?boek foaf:isPrimaryTopicOf/void:inDataset <http://data.bibliotheken.nl/id/dataset/stcn> .

      ?boek schema:name ?boektitel ;
            schema:about ?onderwerp ;
            schema:publication ?pubEvent .

      ?onderwerp skos:altLabel ?onderwerplabel .

      # There is a typo in here!
      ?pubEvent <http://semanticweb.cs.vu.nl/2009/11/sem/hasEarliestBeginTimestamp> ?earliestTimeStamp ;
                <http://semanticweb.cs.vu.nl/2009/11/sem/hasLatestEndTimestamp> ?latestEndTimeStamp ;
                schema:description ?pubDescription .

      BIND("'. $_GET['year'] .'"^^xsd:gYear AS ?year) .

      FILTER (regex(?pubDescription, "amste", "i"))
      FILTER (?earliestTimeStamp <= ?year && ?latestEndTimeStamp >= ?year)

  } GROUP BY ?onderwerp ORDER BY DESC(?c) 
  LIMIT 10
  ';

  $urlcount = $sparqlendpoint . "?default-graph-uri=&query=" . urlencode($sparqlcountquery) . "&format=application%2Fsparql-results%2Bjson&timeout=120000&debug=on";
  $url = $sparqlendpoint . "?default-graph-uri=&query=" . urlencode($sparqlquery) . "&format=application%2Fsparql-results%2Bjson&timeout=120000&debug=on";
  

}




//echo $sparqlquery;


$jsoncount = file_get_contents($urlcount);
$json = file_get_contents($url);

$datacount = json_decode($jsoncount,true);
$data = json_decode($json,true);

?>

<p>Totaal aantal boeken uitgegeven in Amsterdam:

  <?= $datacount['results']['bindings'][0]['c']['value'] ?>

</p>

<p>Met de volgende onderwerpen/genres:</p>

<table class="table">

  <?

foreach ($data['results']['bindings'] as $row) { 
	?>

  <tr>
    <td class="nroftd">
      <div class="nrof">
        <?= $row['c']['value']?>
      </div>
    </td>
    <td>
      <strong><?= $row['onderwerplabel']['value']?></strong>
    </td>
  </tr>


  <?php 
} 

?>
</table>

<p class="smaller">
  Data afkomstig uit <?= $source ?>.
</p>
<p class="smaller">
  <?= $note ?>
</p>

</div>
<div class="broninfo mt-1">

</div>
