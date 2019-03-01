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
	<h1>Amsterdam in <input value="<?= $year ?>" type="text" id="yearBox" name="yearBox" style="max-width:160px;"></h1>

</div>

<div class="container-fluid">
	<div class="col-md-4">
		<h2>Burgemeesters</h2>
		<div class="content" id="burgemeesters"></div>
		


		<h2>Vroedschap</h2>
		<div class="content" id="vroedschap"></div>

		<h2>Gebeurtenissen</h2>
	</div>
	<div class="col-md-4">
		<h2>Stratenplan</h2>
		<div class="content" style="display: block;" id="kaart">
			<div id='map'></div>
			<p class="smaller">
				Een grotere kaart kleurt de leeftijd der straten in <?= $year ?>
			</p>
		</div>

		<h2>Wijkindeling</h2>


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
			<ul>
				<li>Publieksapplicatie, een snelle 'Couleur Locale Temporale', portal naar meer</li>
				<li>Toont welke data beschikbaar is (alle data live uit open bronnen)</li>
				<li>Aanjager voor crowdsource-projecten (samen missende data aanvullen)</li>
			</ul>
		</div>


		<h2>Tentoonstellingen</h2>
		<div class="content" id="tentoonstellingen"></div>
	</div>
</div>

<script src="assets/jquery.timeliny.js"></script>

<script>

	var center = [52.369716,4.900029];
	var zoomlevel = 12;
	
	var map = L.map('map', {
        center: center,
        zoom: zoomlevel,
        minZoom: 6,
        maxZoom: 20,
        scrollWheelZoom: false
    });

	L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner/{z}/{x}/{y}{r}.{ext}', {
	    attribution: 'Tiles <a href="http://stamen.com">Stamen Design</a> - Data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
	    subdomains: 'abcd',
		minZoom: 0,
		maxZoom: 20,
		ext: 'png'
	}).addTo(map);

	

	

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

				if(div.attr('id') == "afgebeeld"){
					$('#afgebeeld').load('streetdepictions.php?year=<?= $year ?>');
				}else if(div.attr('id') == "vroedschap"){
					$('#vroedschap').load('vroedschap.php?year=<?= $year ?>');
				}else if(div.attr('id') == "boeken"){
					$('#boeken').load('boeken.php?year=<?= $year ?>');
				}else if(div.attr('id') == "onstage"){
					$('#onstage').load('onstage.php?year=<?= $year ?>');
				}else if(div.attr('id') == "tentoonstellingen"){
					$('#tentoonstellingen').load('tentoonstellingen.php?year=<?= $year ?>');
				}else if(div.attr('id') == "hetweer"){
					$('#hetweer').load('hetweer.php?year=<?= $year ?>');
				}else if(div.attr('id') == "burgemeesters"){
					$('#burgemeesters').load('burgemeesters.php?year=<?= $year ?>');
				}
			}

			div.toggle();
		});

		refreshMap();

	});

	function refreshMap(){
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
		                return new L.CircleMarker(latlng, {
		                    radius: 3,
		                    color: "#E95C90",
		                    weight: 1,
		                    opacity: 1,
		                    fillOpacity: 0.3
		                });
		            },
				    style: function(feature) {
				        return {
				            color: '#E95C90',
				            weight: 1,
				            opacity: 1,
				            clickable: true
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
