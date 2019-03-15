<?php

$year = $_GET['year'];

$json = file_get_contents("data/onstage.json");
$data = json_decode($json,true);

$rows = $data[$year];

$i = 0;

if (!array_key_exists($year, $data)) {

    echo '<p class="smaller">De data in <a href="http://www.vondel.humanities.uva.nl/onstage/"">OnStage</a> beperkt zich tot de periode 1638-1890</a>';

    die;
    

}


?>

<table class="table">

  <?

foreach ($rows as $play) { 

  if(++$i > 10) break;

	?>

  <tr>
    <td class="nroftd">
      <div class="nrof">
        <?= $play[0]?>
      </div>
    </td>
    <td>
      <strong><a target="_blank" href="<?= $play[2]?>"><?= $play[1]?></a></strong>
    </td>
  </tr>


  <?php 
} 

?>
</table>

<p class="smaller">
  Meer dan de 10 meest opgevoerde stukken dit jaar vind je in <a target="_blank" href='http://www.vondel.humanities.uva.nl/onstage/'>OnStage</a>, waarin alle voorstellingen in de Amsterdamse Schouwburg zijn opgenomen.
</p>
<p class="smaller">
  De data is nu alleen als <a target="_blank" href="https://en.wikipedia.org/wiki/RDFa">RDFa</a> in de html van de site opgenomen, maar aan een betere ontsluiting wordt gewerkt. Wij maakten voor nu <a target="_blank" href="data/onstage.json">dit jsonbestand</a>. 
</p>
