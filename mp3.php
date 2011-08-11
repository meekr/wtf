<?php
mb_internal_encoding('utf-8');

// connect to the database
if (!mysql_connect('localhost', 'root', ''))
{
	$obj->msg = 'error connecting database!';
	die();
}

mysql_select_db('wtf_cn') or die("Error conecting to db.");
mysql_query('set names utf8;');

$SQL = "SELECT soundFile FROM word";
$result = mysql_query( $SQL ) or die("Couldn t execute query.".mysql_error());

$array = array();

while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	$files = explode(' ', $row[soundFile]);
	
	$suffix = ".mp3";
	$suffix = preg_quote($suffix);

	foreach ($files as $i => $file) {
		if (preg_match("/.*{$suffix}$/", $file))
			array_push($array, $file);
	}
}

echo 'old array size: ' . count($array) . '<br>';

$unique_array = array_unique($array);
echo 'unique array size: ' . count($unique_array) . '<br><br><br>';

foreach ($unique_array as $i => $file) {
	echo $file . '<br>';
}
?>