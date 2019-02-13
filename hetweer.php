<?php


//exec("wget --post-data 'a=b&c=d' http://projects.knmi.nl/klimatologie/daggegevens/getdata_dag.cgi");

$ch = curl_init();

$daystring = $_GET['year'] . date("m") . date("d");
//echo $daystring;

$postvars = "start=" . $daystring . "&end=" . $daystring . "&stns=240&vars=TN:TX";

//echo $postvars;

//die;
curl_setopt($ch, CURLOPT_URL,"http://projects.knmi.nl/klimatologie/daggegevens/getdata_dag.cgi");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$postvars);

// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);

curl_close ($ch);

$lines = explode("\n", trim($server_output));

foreach ($lines as $k => $line) {
	//echo trim($line) . "\n";
	if(strpos($line,"#")===false){
		//echo trim($line) . "<<<\n";
		$found = trim($line);
		$fields = explode(",",$found);
	}
}

//echo($fields);
//print_r($fields);

$min = (float)trim($fields[2]) / 10;
$max = (float)trim($fields[3]) / 10;
echo "<h3>min " . $min . "&deg; max " . $max . "&deg;</h3>";



?>


