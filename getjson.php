<?php
mb_internal_encoding('utf-8');

$id = $_GET['id'];
// connect to the database
if (!mysql_connect('localhost', 'root', ''))
{
	$obj->msg = 'error connecting database!';
	die();
}

mysql_select_db('wtf_cn') or die("Error conecting to db.");
mysql_query('set names utf8;');

$SQL = "SELECT json FROM word WHERE id=$id";
$result = mysql_query( $SQL ) or die("Couldn t execute query.".mysql_error());

$json = "";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $json = $row[json];
}


// convert into JSON
header('Content-Type: application/json');
echo $json;

?>