<?php

$year = $_GET['year'];

$json = file_get_contents("data/onstage.json");
$data = json_decode($json,true);

$rows = $data[$year];

$i = 0;

if (!array_key_exists($year, $data)) {

    echo "Geen data beschikbaar in <a href='http://www.vondel.humanities.uva.nl/onstage/'>OnStage</a> voor dit jaar!";

    die;
    

}


?>

<p>De tien vaakst opgevoerde toneelstukken in de Amsterdamse Schouwburg:

</p>

<div class="list">

  <?

foreach ($rows as $play) { 

  if(++$i > 10) break;

	?>

  <span>
    <?= $play[0]?> <a href="  <?= $play[2]?>">
      <?= $play[1]?></a></span><br />




  <?php 
} 

?>

</div>
<div class="broninfo mt-1">
  <p>Data afkomstig uit <a href='http://www.vondel.humanities.uva.nl/onstage/'>OnStage</a>.</p>
</div>
