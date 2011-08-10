<?php
$obj->ok = 0;

if ($_REQUEST['spell'] != ''){
	mb_internal_encoding('utf-8');
	if(!mysql_connect('localhost', 'root', ''))
	{
		$obj->msg = 'error connecting database!';
		die();
	}
	mysql_select_db('wtf_cn');
	mysql_query('set names utf8;');
	
	// wtf table
	$spell		= htmlentities($_REQUEST['spell']);
	$prototype		= htmlentities($_REQUEST['prototype']);
	$frequency	= mysql_real_escape_string($_REQUEST['frequency']);
	$phonetic	= mysql_real_escape_string($_REQUEST['phonetic']);
	$soundFile	= mysql_real_escape_string($_REQUEST['soundFile']);
	$reference	= mysql_real_escape_string($_REQUEST['reference']);
	$translate	= mysql_real_escape_string($_REQUEST['translate']);
	$detail		= mysql_real_escape_string($_REQUEST['detail']);
	$json		= mysql_real_escape_string($_REQUEST['json']);
	$obj->word = $spell;
	
	$sql	= "INSERT INTO word (spell, prototype, frequency, phonetic, translate, soundFile, reference, detail, json, store_date) VALUES('$spell', '$prototype', $frequency, '$phonetic', '$translate', '$soundFile', '$reference', '$detail', '$json', NOW())";
	mysql_query($sql);

	$obj->ok = 1;
	$obj->query = $sql;
}

// convert into JSON
$json_obj = json_encode($obj);
header('Content-Type: application/json');
echo $json_obj;
?>