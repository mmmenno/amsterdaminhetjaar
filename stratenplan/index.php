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

     <link rel="stylesheet" href="../assets/styles.css" />

	<style type="text/css">
		#bigmap{
			height: 600px;
		}
		.legenditem{
			width: 20px;
			height: 20px;
			float: left;
			margin-top: 3px;
			margin-right: 10px;
		}
		h2{
			cursor: default;
		}
	</style>

	
</head>
<body>


<div class="container-fluid">
	<div class="col-md-12">
		<h2>Het stratenplan van Amsterdam in <input autofocus value="<?= $year ?>" type="text" id="yearBox" name="yearBox" style="max-width:80px;"></h2>
	</div>
</div>

<div class="container-fluid">
	<div class="col-md-3">
		<h2>Legenda</h2>
		<div id="legenda">
			<div class="legenditem" style="background-color: #4575b4;"></div> net nieuw<br />
			<div class="legenditem" style="background-color: #74add1;"></div> 10+ jaar oud<br />
			<div class="legenditem" style="background-color: #abd9e9;"></div> 20+ jaar oud<br />
			<div class="legenditem" style="background-color: #ffffbf;"></div> 30+ jaar oud<br />
			<div class="legenditem" style="background-color: #fee090;"></div> 60+ jaar oud<br />
			<div class="legenditem" style="background-color: #fdae61;"></div> 120+ jaar oud<br />
			<div class="legenditem" style="background-color: #f46d43;"></div> 240+ jaar oud<br />
			<div class="legenditem" style="background-color: #a50026;"></div> 400+ jaar oud

		</div>

		<div id="straatinfo"></div>

		<div id="over">
			<h2>Over de data</h2>

			<p class="smaller">
				Amsterdam heeft in zijn bestaan meer dan 6.500 straten gekend. Die allemaal precies dateren is lastig, <a href="https://github.com/mmmenno/asap" target="_blank">deze bronnen</a> hebben daarbij geholpen. Het kan beter, hulp daarbij is altijd welkom.
			</p>

			<p class="smaller">
				Je kunt <a href="https://adamlink.nl/data/geojson/streetsperyear/<?= $year ?>" target="_blank">het stratenplan van dit jaar als geojson</a> pakken, mocht je er iets mee willen. 
			</p>

		</div>

	</div>
	<div class="col-md-9">
		<div id="bigmap"></div>
	</div>
</div>

<script>

	

	

	$(document).ready(function(){

		createMap();

		refreshMap();


	});

	function createMap(){
		center = [52.369716,4.900029];
		zoomlevel = 14;
		
		map = L.map('bigmap', {
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

		map.on('zoomend', function () {
		    currentZoom = map.getZoom();
		    if (currentZoom < 14) {
		        streets.setStyle({weight: 1});
		    } else if (currentZoom < 16){
		        streets.setStyle({weight: 2});
		    } else {
		        streets.setStyle({weight: 6});
		    }
		});
	
	}

	function refreshMap(){

		$('#straatinfo').append('<h2>Aan het laden ...</h2><div class="loader"></div>');

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
				            weight: 2,
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
			    

	            //map.fitBounds(streets.getBounds());
	            
	            $('#straatinfo').html('');
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
		console.log($(this)[0].feature.properties);
		var props = $(this)[0].feature.properties;
		var streetinfo = '<h2>' + props['preflabel'] + '</h2>';
		var from = 'aanleg ' + props['street_since_min'] + '/' + props['street_since_max'] + '<br />'
		streetinfo += from;
		if(props['street_until_min'] > 0){
			var until = 'verdwenen ' + props['street_until_min'] + '/' + props['street_until_max'] + '<br />';
			streetinfo += until;
		}
		streetinfo += '<a target="_blank" href="' + props['adamlink_uri'] + '">bekijk in het Adamlink stratenregister</a>';  
		$('#straatinfo').html(streetinfo);
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
