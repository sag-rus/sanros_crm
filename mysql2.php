<?php
//ini_set("display_errors",1);
//error_reporting(E_ALL);
	$loader = require( __DIR__ . '/vendor/autoload.php');

	session_start();
	header("Content-type: text/html; charset: utf-8");
	date_default_timezone_set("Asia/Baghdad");
	define("_DS_", DIRECTORY_SEPARATOR);

	include_once("core/mysql.php");


	include_once("config.php");
	$conf = new JConfig;
	$bonus_rec = $conf->bonus_rec;
	$bonus_ref = $conf->bonus_ref;
	$min_transfer_bonus = $conf->min_transfer_bonus;
	$sync_host = $conf->sync_host;
	$sync_api = $conf->sync_base;
	$directory = dirname(__FILE__);


	include_once("core/lib/Mysql.Class.php");
	include_once("core/functions.php");
	$connect = connect_to_MySQL();

	$objects = $connect->getAll("SELECT `id`, `name`, `type`, `telephone` FROM `object` WHERE `active` = 0");
  $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
  $reader = $reader->load(__DIR__.'/objects.xlsx');
  $sheet = $reader->getSheet(0);
	$types = [
		1 => 'Санаторий',
		2 => 'Отель',
		3 => 'Мини-отель',
		4 => 'Пансионат',
		5 => 'Дом отдыха',
		6 => 'База отдыха',
		7 => 'SPA-Отель',
		8 => 'Детский лагерь',
		9 => 'Курорт',
		10 => 'Гостиница',
		11 => 'Турбаза',
		12 => 'Пансионат с лечением',
		13 => 'Клиника-санаторий'
	];
  foreach ($objects as $i => $object) {
    $sheet->getCellByColumnAndRow(1,$i+2)->setValue((isset($types[$object['type']])?$types[$object['type']]." ":"")."«".$object['name']."»");
    $phoneStr = "";
    $phones = $object['telephone']?json_decode($object['telephone'],true):[];
    foreach ($phones as $phone) {
    	if($phoneStr)
    		$phoneStr .= ", ".PHP_EOL;
    	if($phone["note"])
    		$phoneStr .= $phone['value']." (".$phone['note'].")";
    	else
        $phoneStr .= $phone['value'];
    }
    $sheet->getCellByColumnAndRow(2,$i+2)->setValue($phoneStr);

  }
  $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($reader, "Xlsx");
  $writer->save("objects.xlsx");


?>
