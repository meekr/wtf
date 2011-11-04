<?php
mb_internal_encoding('utf-8');

$page = $_GET['page'];
$limit = 5;
// connect to the database
if (!mysql_connect('localhost', 'root', ''))
{
	$obj->msg = 'error connecting database!';
	die();
}

mysql_select_db('wtf_cn') or die("Error conecting to db.");
mysql_query('set names utf8;');

$result = mysql_query("SELECT COUNT(*) AS count FROM word");
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$count = $row['count'];

if( $count >0 ) {
	$total_pages = ceil($count/$limit);
} else {
	$total_pages = 0;
}
if (!$page) $page = 1;
if ($page > $total_pages) $page=$total_pages;
$start = $limit*$page - $limit; // do not put $limit*($page - 1)
//$SQL = "SELECT id, spell, prototype, reference, frequency, phonetic, soundFile, translate, tags, detail, category, json FROM word ORDER BY spell, frequency DESC LIMIT $start , $limit";
$SQL = "SELECT * FROM word ORDER BY category, frequency DESC LIMIT $start , $limit";
$result = mysql_query( $SQL ) or die("Couldn t execute query.".mysql_error());

$responce->page = $page;
$responce->total = $total_pages;
$responce->records = $count;
$i=0;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $responce->rows[$i]['id'] = $row[id];
    $responce->rows[$i]['spell'] = $row[spell];
    $responce->rows[$i]['prototype'] = $row[prototype];
    $responce->rows[$i]['reference'] = $row[reference];
    $responce->rows[$i]['frequency'] = $row[frequency];
    $responce->rows[$i]['phonetic'] = $row[phonetic];
    $responce->rows[$i]['soundFile'] = $row[soundFile];
    $responce->rows[$i]['translate'] = $row[translate];
    $responce->rows[$i]['tags'] = $row[tags];
    $responce->rows[$i]['detail'] = $row[detail];
    $responce->rows[$i]['category'] = $row[category];
    $responce->rows[$i]['json'] = $row[json];
    $i++;
}


// convert into JSON
header('Content-Type: application/json');
echo json_encode($responce);

?>