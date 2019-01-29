<?php

$year = $_GET['year'];

$json = file_get_contents("data/onstage.json");
$data = json_decode($json,true);

$rows = $data[$year];

if (!array_key_exists($year, $data)) {

    echo "We hebben niks!";

    die;
    

}


?>

<?

foreach ($rows as $play) { 
	?>

  <span> <?= $play[0]?>  <a href="  <?= $play[2]?>"><?= $play[1]?><a></span><br/>



	<?php 
} 

?>

</div>


