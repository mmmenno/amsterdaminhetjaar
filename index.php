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


	<style>

		@import url('https://fonts.googleapis.com/css?family=Cardo:700');

		html, body{
			height: 100%;
			margin:0;
			font-family: 'Cardo', serif;
			font-size: 20px;
		}
		h1{
			margin-top: 30px;
			text-align: center;
			font-size: 64px;
			border-bottom: 4px solid #000;
		}
		h2{
			margin-top: 30px;
			font-size: 32px;
			border-bottom: 4px solid #000;
			cursor: pointer;
		}
		#map {
			width: 100%;
			height: 200px;
			margin-bottom: 20px;
		}
		.leaflet-left .leaflet-control{
			margin-top: 10px;
			margin-left: 10px;
		}
		.leaflet-container .leaflet-control-attribution{
			color: #000;
		}
		.leaflet-control-attribution a{
			color: #000;
		}
		.leaflet-touch .leaflet-control-layers, .leaflet-touch .leaflet-bar{
			border: 2px solid #000;
		}
		.btn-primary{
			margin-top: 20px;
			background-color: #fff;
			color: #000;
			font-size: 20px;
			border-color: #fff;
		}
		.btn-primary:hover{
			background-color: #BC000C;
			color: #fff;
			border-color: #fff;
		}
		button:focus, input:focus, .btn-primary:focus {
			outline:0;
			background-color: #fff;
			color: #000;
			border-color: #fff;
		}
		#afgebeeld img{
			width: 100%;
		}
		ol{
			font-size: 16px;
			padding-left: 24px;
		}
		.content{
			display: none;
		}
	</style>

	
</head>
<body>

<div class="container-fluid">
	<h1>Amsterdam in <?= $year ?></h1>
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
		<div id='map'></div>


		<h2>Wijkindeling</h2>


		<h2>Afgebeeld</h2>
		<div class="content" id="afgebeeld"></div>


	</div>
	<div class="col-md-4">
		<h2>Gepubliceerde boeken</h2>
		<div class="content" id="boeken"></div>


		<h2>In de theaters</h2>



		<h2>Tentoonstellingen</h2>
		<div class="content" id="tentoonstellingen"></div>



		<h2>Het weer</h2>
		<div class="content" id="hetweer"></div>

		<h2>3 vliegen in 1 klap</h2>
		<div class="content" id="drievliegen">
			<ul>
				<li>Publieksapplicatie, een snelle 'Couleur Locale Temporale', portal naar meer</li>
				<li>Toont welke data beschikbaar is (alle data live uit open bronnen)</li>
				<li>Aanjager voor crowdsource-projecten (samen missende data aanvullen)</li>
			</ul>
		</div>
	</div>
</div>



<script>

	var center = [52.369716,4.900029];
	var zoomlevel = 14;
	
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

	});

</script>



</body>
</html>
