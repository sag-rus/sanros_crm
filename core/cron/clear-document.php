<?php

	$directory = dirname(__FILE__)."/../..";
	include_once($directory."/core/functions.php");
	include_once($directory."/core/lib/Mysql.Class.php");
	$connect = connect_to_MySQL_directory();

	$date = "2015-12-01";

	$data = $connect->getAll("SELECT doc_schet_san FROM reckoning WHERE date_z<=?s", $date);
	foreach($data as $row){
		$array = json_decode($row["doc_schet_san"], TRUE);
		foreach($array as $document){
			unlink($directory."/temp/schet/".$document["doc"]);
		}
		$connect->query("UPDATE reckoning SET doc_schet_san='' WHERE id=?i", $row["id"]);
	}

?>
