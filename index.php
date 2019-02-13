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
	

	
</head>
<body>

<div class="container-fluid">
	<h1>Amsterdam in <?= $year ?></h1>
</div>

<div class="container-fluid">
	<div class="col-md-4">
		<h2>Burgemeester<span class="light">s</span></h2>
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
		<div class="content" id="onstage"></div>


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

	});

</script>



</body>
</html>
