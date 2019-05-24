<?php


$sparqlQueryString = "
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
SELECT DISTINCT ?exh ?label ?begin ?end ?place ?placelabel (SAMPLE(?img) AS ?img) WHERE {
  ?exh sem:eventType wd:Q464980 .
  ?exh rdfs:label ?label .
  ?exh sem:hasEarliestBeginTimeStamp ?begin .
  ?exh sem:hasLatestEndTimeStamp ?end .
  ?exh sem:hasPlace ?place .
  OPTIONAL{ 
  	?cho dc:subject ?exh .
  	?cho foaf:depiction ?img .
  }
  ?place rdfs:label ?placelabel .
  BIND (year(?begin) AS ?startyear)
  FILTER(?startyear = " . $_GET['year'] . ")
} 
GROUP BY ?exh ?label ?begin ?end ?place ?placelabel
ORDER BY ASC(?begin)
LIMIT 200
";

$url = "https://api.druid.datalegend.net/datasets/adamnet/all/services/endpoint/sparql?query=" . urlencode($sparqlQueryString) . "";

$queryurl = "https://druid.datalegend.net/AdamNet/all/sparql/endpoint#query=" . urlencode($sparqlQueryString) . "&endpoint=https%3A%2F%2Fdruid.datalegend.net%2F_api%2Fdatasets%2FAdamNet%2Fall%2Fservices%2Fendpoint%2Fsparql&requestMethod=POST&outputFormat=table";

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

	$monthfrom = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
    $monthto = array("januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
    

	$from = date("j M",strtotime($row['begin']['value']));
	$from = str_replace($monthfrom, $monthto, $from);


	$to = date("j M",strtotime($row['end']['value']));
	$to = str_replace($monthfrom, $monthto, $to);

	if($from==$to){
		$to = "";
	}else{
		$to = " - " . $to;
	}

	$link = $row['exh']['value'];
	?>
	
	<tr>
		<td style="width: 60px;">
		<?php if(isset($row['img']['value'])){ ?>
			<a target="_blank" href="<?= $link ?>">
				<img style="width: 60px;" src="<?= $row['img']['value'] ?>" />
			</a>
		<? }else{ ?>
			<div style="width: 60px; height: 50px; background-color: #EBEBEB;"></div>
		<? } ?>
	</td><td>
		<a target="_blank" href="<?= $link ?>">
			<strong><?= $row['label']['value'] ?></strong>
		</a><br />
		<span class="smaller">
			<?= $row['placelabel']['value'] ?><br />
			<?= $from ?><?= $to ?>
		</span><br />
	</td></tr>

	<?php 
} 
?>
</table>


<?php





echo '<p class="smaller">';
echo "De tentoonstellingen-dataset is nog in wording. De meeste kans maak je vanaf de tweede helft van de 19e eeuw.";
echo '</p>';

echo '<p class="smaller"><a target="_blank" href="' . $queryurl . '">SPARQL het zelf</a>, op AdamNets Druid endpoint.</p>';

?>


