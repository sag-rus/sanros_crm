<?php


include_once("core/functions.php");
include_once("core/lib/Mysql.Class.php");
include_once("config.php");
$conf = new JConfig;
$connect = connect_to_MySQL();
$rows = $connect->getAll("SELECT email FROM object WHERE email!='' AND email!='[]'");

$emails = array();
if($rows){
	foreach($rows as $row) {
		$string = $row['email'];
		$matches = array(); //create array
		$pattern = '/[A-Za-z0-9_-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_]+)/'; //regex for pattern of e-mail address
		preg_match_all($pattern, $string, $matches); //find matching pattern
		$emails = array_merge($emails, $matches[0]);
	}

	$list = '<ul style="padding:0,margin: 0;list-style:none;"><li>' . implode('</li><li>', $emails) . '</li>';
	echo "Всего: " . count($emails);
	echo $list;
}


