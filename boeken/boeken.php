<?php

if ($_GET['year'] >= 1800) {

  $sparqlendpoint = "http://data.bibliotheken.nl/sparql";
  $source = "<a target='_blank' href='http://data.bibliotheken.nl/'>KB, Nederlandse Bibliografie Totaal (NBT)</a>";
  $note = 'Sparql het zelf via de <a href="http://data.bibliotheken.nl/sparql">sparqlendpoint</a> van de KB!';

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
  // Oude versie, geen impressum opgenomen bij het boek, dus dit moet via de drukker... 

  $sparqlendpoint = "http://openvirtuoso.kbresearch.nl/sparql";
  $source = "<a target='_blank' href='https://www.kb.nl/organisatie/onderzoek-expertise/informatie-infrastructuur-diensten-voor-bibliotheken/short-title-catalogue-netherlands-stcn'>STCN</a>";
  $note = "Er is nu nog gebruikgemaakt van een oudere linked-dataversie van de STCN-catalogus, maar een nieuwe is onderweg. De aantallen hierboven zijn gebaseerd op de vestigingsplaats [=Amsterdam] van een drukker en die is mogelijk niet correct gekoppeld aan het publicatiejaar. Sparql het zelf via de <a href='http://openvirtuoso.kbresearch.nl/sparql'>sparqlendpoint</a> van de STCN!";

  $sparqlcountquery = '
  select (COUNT(?boek) AS ?c) WHERE { 

    ?boek dc:title ?boektitel ;
          dc:publisher ?drukker ;
          dc:date "' . $_GET['year'] . '" .

    ?drukker skos:prefLabel ?drukkernaam ;
             skos:editorialNote ?impressum .

FILTER (regex(?impressum, "amste", "i"))

}
  ';
  
  $sparqlquery = '
  select (COUNT(?onderwerp) AS ?c) (SAMPLE(?onderwerplabel) AS ?onderwerplabel) WHERE { 

    ?boek dc:title ?boektitel ;
          dc:publisher ?drukker ;
          dc:subject ?onderwerp ;
          dc:date "' . $_GET['year'] . '" .

    ?drukker skos:prefLabel ?drukkernaam ;
             skos:editorialNote ?impressum .

    ?onderwerp skos:prefLabel ?onderwerplabel .

FILTER (regex(?impressum, "amste", "i"))

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
