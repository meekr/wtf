<?php
$obj->ok = 0;

if ($_REQUEST['id'] != ''){
	mb_internal_encoding('utf-8');
	if(!mysql_connect('localhost', 'root', ''))
	{
		$obj->msg = 'error connecting database!';
		die();
	}
	mysql_select_db('wtf_cn');
	mysql_query('set names utf8;');
	
	// wtf table
	$id			= $_REQUEST['id'];
	$spell		= mysql_real_escape_string($_REQUEST['spell']);
	$frequency	= mysql_real_escape_string($_REQUEST['frequency']);
	$phonetic	= mysql_real_escape_string($_REQUEST['phonetic']);
	$soundFile	= mysql_real_escape_string($_REQUEST['soundFile']);
	$translate	= mysql_real_escape_string($_REQUEST['translate']);
	$tags		= mysql_real_escape_string($_REQUEST['tags']);
	$detail		= mysql_real_escape_string($_REQUEST['detail']);
	$category	= mysql_real_escape_string($_REQUEST['category']);
	
	$sql	= "UPDATE word SET spell='$spell', frequency=$frequency, phonetic='$phonetic', translate='$translate', soundFile='$soundFile', detail='$detail', tags='$tags', category=$category WHERE id=$id";
	mysql_query($sql);

	$obj->ok = 1;
	$obj->word = $spell;
	$obj->query = $sql;
}

// convert into JSON
$json_obj = json_encode($obj);
header('Content-Type: application/json');
echo $json_obj;
?>