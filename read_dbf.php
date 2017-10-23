<?php
header('Content-type: text/plain;charset=utf8');

$filename = __DIR__ . "/temp/1C/CRM.dbf";
header('Content-type: text/html;charset=utf8');
$file = file_get_contents($filename);
$encoding = mb_detect_encoding($file);
file_put_contents(__DIR__ . 'db.dbf', mb_convert_encoding($file, 'UTF-8', $encoding));
$db = dbase_open(__DIR__ . 'db.dbf', 0);
$row = dbase_get_record($db, 1);
var_dump($row);


 // if($DBF = dbase_open($filename, 0)) {
 // 	for($i = 1; $i <= dbase_numrecords($DBF); $i++) {
 // 		$record = dbase_get_record($DBF, $i);
 // 		foreach($record as $r) {
	// 		$encoding = mb_detect_encoding($r);
	//  		echo mb_convert_encoding($r, 'UTF-8', $encoding);
 // 		}
 // 	}

 // 	dbase_close($DBF);
 // }