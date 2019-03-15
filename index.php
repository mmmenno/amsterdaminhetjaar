<?php

if(isset($_GET['year'])){
	$year = $_GET['year'];
}else{
	$year = 1654;
}

?>
<!DOCTYPE html>
<html>
<head>
	
	<title>Amsterdam in <?= $year ?></title>

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

     <link rel="stylesheet" href="assets/styles.css" />

	 <link rel="stylesheet" href="assets/timeliny.css" />

	

	
</head>
<body>

<div id="bigimg"></div>


<div id="timeline-wrapper"><div id="notimeline">

	<?php 
	foreach (range(1500, date("Y")) as $y) {
		if($y == $year) {
			echo "<div data-year='{$y}' class='active'></div>";
		} else {
			echo "<div data-year='{$y}'></div>";
		}
	}
	
	?>
</div></div>

<div class="container-fluid">
	<h1>Amsterdam in <input autofocus value="<?= $year ?>" type="text" id="yearBox" name="yearBox" style="max-width:160px;"></h1>

</div>

<div class="container-fluid">
	<div class="col-md-4">
		<h2>Burgemeesters</h2>
		<div class="content" id="burgemeesters"></div>
		


		<h2>Vroedschap</h2>
		<div class="content" id="vroedschap"></div>

		<h2>Gebouwd en verdwenen</h2>
		<div class="content" id="gebouwdverdwenen"></div>

		<h2>Gebeurtenissen</h2>
		<div class="content" id="gebeurtenissen">
			<p class="smaller">Tja, een gebeurtenissenoverzicht lijkt nog niet voorhanden te zijn, dat wil zeggen niet in gestructureerde vorm.</p>
		</div>


	</div>
	<div class="col-md-4">
		<h2>Stratenplan</h2>
		<div class="content" id="kaart"></div>

		<h2>Wijkindeling</h2>
		<div class="content" id="wijkindeling">
			<p class="smaller">Voor de wijken verwijzen we nu nog even naar <a target="_blank" href="https://adamlink.nl/geo/districts">Adamlink.nl/geo/districts</a>. Daar vind je overzichten van de huidige wijken en buurten, maar ook de 19e-eeuwse buurtindeling en de indeling van 1909.</p>
		</div>


		<h2>Straatbeelden</h2>
		<div class="content" id="afgebeeld"></div>


	</div>
	<div class="col-md-4">
		<h2>Gepubliceerde boeken</h2>
		<div class="content" id="boeken"></div>


		<h2>In de Schouwburg</h2>
		<div class="content" id="onstage"></div>



		<h2>Over deze website</h2>
		<div class="content" id="drievliegen">
			<p class="smaller">Met deze site willen we een snel overzicht geven van de Amsterdamse geschiedenis in willekeurig welk jaar - wie heeft het voor 't zeggen, hoe ziet de stad eruit en wat gebeurt er zoal op cultureel gebied?</p>

			<p class="smaller">We gebruiken daarvoor online databronnen - het (beschikbaar) maken van goede data is een belangrijke doelstelling van de Amsterdam Time Machine. We verwijzen daarbij zoveel mogelijk naar API's en SPARQL endpoints. Vaardigheid in het schrijven van SPARQL queries is voor historici in de nabije toekomst waarschijnlijk net zo belangrijk als iets op kunnen diepen uit een archief.</p>

			<p class="smaller">Niet alle data is compleet. We laten zien waar we samen verder kunnen (en moeten) bouwen aan het Amsterdamse datalandschap. Iedereen kan een bijdrage leveren - door een eigen dataset toegankelijker te maken bijvoorbeeld, of door op Wikidata data aan te vullen en verbeteren.</p>
		</div>


		<h2>Tentoonstellingen</h2>
		<div class="content" id="tentoonstellingen">
			<p class="smaller">We zouden hier graag laten zien welke tentoonstellingen er in een bepaald jaar te zien waren en kijken nu of we dit voor elkaar kunnen krijgen. Suggesties en - liever nog - bijdragen zijn welkom!</p>
		</div>
	</div>
</div>

<script src="assets/jquery.timeliny.js"></script>

<script>

	

	

	$(document).ready(function(){

		$(function() {
			$('#timeline').timeliny({
				order: 'asc',
				className: 'timeliny',
				wrapper: '<div class="timeliny-wrapper"></div>',
				boundaries: 20,
				animationSpeed: 1500,
				hideBlankYears: false,
				onInit: function() {},
				onDestroy: function() {},
				afterLoad: function(currYear) {},
				onLeave: function(currYear, nextYear) {},
				afterChange: function(currYear) {
					location.replace(`?year=${currYear}`)
				},
				afterResize: function() {}
			});
		});
		
		
		$('h2').click(function(){
			var div = $(this).next('div');
			console.log(div.attr('id'));

			if(div.html()==""){
				console.log('leeg!');

				div.append('<div class="loader"></div>');

				if(div.attr('id') == "afgebeeld"){
					$('#afgebeeld').load('streetdepictions.php?year=<?= $year ?>',function(){
						//setImgClick();
					});
				}else if(div.attr('id') == "kaart"){
					$('#kaart').load('stratenplan/stratenplan.php?year=<?= $year ?>', function(){
						createMap();
						refreshMap();
					});
				}else if(div.attr('id') == "vroedschap"){
					$('#vroedschap').load('vroedschap.php?year=<?= $year ?>');
				}else if(div.attr('id') == "boeken"){
					$('#boeken').load('boeken/boeken.php?year=<?= $year ?>');
				}else if(div.attr('id') == "onstage"){
					$('#onstage').load('onstage.php?year=<?= $year ?>');
				}else if(div.attr('id') == "burgemeesters"){
					$('#burgemeesters').load('burgemeesters/burgemeesters.php?year=<?= $year ?>');
				}else if(div.attr('id') == "gebouwdverdwenen"){
					$('#gebouwdverdwenen').load('gebouwdverdwenen/gebouwdverdwenen.php?year=<?= $year ?>',function(){
						//setImgClick();
					});
				}
			}

			div.toggle();
		});

		//setImgClick();

		$('#bigimg').click(function(){
			$(this).hide();
		});

	});

	function setImgClick(){
		$('a img').click(function (e){
			e.preventDefault();
			var imglink = $(this).parent('a').attr('href');
			var imgurl = $(this).attr('src');
			var imgtitle = $(this).parent('a').attr('title');
			var html = '<div class="bigimgcontent">';
			html += '<img src="' + imgurl + '" /><br />';
			if(typeof imgtitle !== 'undefined'){
				html += imgtitle + '<br />';
			}
			html += '<a target="_blank" href="' + imglink + '">bekijk op ' + imglink + '</a>';
			html += '</div>';
			$('#bigimg').html(html);
			$('#bigimg').show();
		});
	}

	function createMap(){
		center = [52.369716,4.900029];
		zoomlevel = 13;
		
		map = L.map('map', {
	        center: center,
	        zoom: zoomlevel,
	        minZoom: 6,
	        maxZoom: 20,
	        scrollWheelZoom: false
	    });

		L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/dark_nolabels/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
			id: 'CartoDB.DarkMatterNoLabels',
			minZoom: 0,
			maxZoom: 20,
			ext: 'png'
		}).addTo(map);

	
	}

	function refreshMap(){
		console.log('start refreshing...');
		$.ajax({
	        type: 'GET',
	        url: '/data/geojson/streetsperyear.php?year=<?= $year ?>',
		    dataType: 'json',
	        success: function(jsonData) {

	            if (typeof streets !== 'undefined') {
				    map.removeLayer(streets);
				}

	            streets = L.geoJson(null, {
	            	pointToLayer: function (feature, latlng) {                    
		                return false;
		            },
				    style: function(feature) {
				        return {
				            color: getColor(feature.properties.street_since_min),
				            weight: 1,
				            opacity: 1,
				            clickable: false
				        };
				    },
				    onEachFeature: function(feature, layer) {
						layer.on({
					        click: whenStreetClicked
					    });
				    }
				}).addTo(map);

	            streets.addData(jsonData).bringToFront();
			    

	            //map.fitBounds(streets);
	            

	        },
	        error: function() {
	            console.log('Error loading data');
	        }
	    });
	}

	function getColor(streetyear) {
		var now = <?= $year ?>;
		var d = now - streetyear;
	    return d > 400 ? '#a50026' :
	           d > 240 ? '#f46d43' :
	           d > 120  ? '#fdae61' :
	           d > 60  ? '#fee090' :
	           d > 30  ? '#ffffbf' :
	           d > 20  ? '#abd9e9' :
	           d > 10   ? '#74add1' :
	                     '#4575b4';
	}


	function whenStreetClicked(){
		console.log('clicked');
	}

	$('#yearBox').change(function(event){
		var gotoyear = $('#yearBox').val();
		location.href = 'index.php?year=' + gotoyear;
	});
	$('#yearBox').keypress(function(event){
		if(event.keyCode == 13){
			var gotoyear = $('#yearBox').val();
			location.href = 'index.php?year=' + gotoyear;
		}
	});


</script>



</body>
</html>
