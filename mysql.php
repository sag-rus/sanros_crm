<?php
//ini_set("display_errors",1);
//error_reporting(E_ALL);
	if(!isset($_POST["func"]) OR $_POST["func"] == "")
		return;
	$func = $_POST["func"];
	$loader = require( __DIR__ . '/vendor/autoload.php');

	session_start();
	header("Content-type: text/html; charset: utf-8");
	date_default_timezone_set("Asia/Baghdad");
	define("_DS_", DIRECTORY_SEPARATOR);

	include_once("core/mysql.php");


if($func AND function_exists($func)){
	include_once("config.php");
	$conf = new JConfig;
	$bonus_rec = $conf->bonus_rec;
	$bonus_ref = $conf->bonus_ref;
	$min_transfer_bonus = $conf->min_transfer_bonus;
	$sync_host = $conf->sync_host;
	$sync_api = $conf->sync_base;
	$directory = dirname(__FILE__);
	define("_FOLDERSITE_", $directory);
  $configInstance = \App\lib\CRM\Config\Client::getInstance();
	$configInstance->clientCabinet = [
    "link" => $conf->turist_cabinet
	];

	$configInstance->onlinePaymentInfo = [
    "userName" => $conf->USERNAME_SBERBANK,
    "password" => $conf->PASSWORD_SBERBANK,
    "link" => $conf->BANK_PAYMENT_LINK_SBERBANK,
    "commission" => $conf->BANK_COM_SBERBANK
	];

	include_once("core/lib/Mysql.Class.php");
	include_once("core/functions.php");
	$connect = connect_to_MySQL();
	if(isset($_COOKIE["session"])){
		$session = $_COOKIE["session"];
		$login = $connect->getOne("SELECT login FROM session WHERE id_session=?s", $session);
		$row = $connect->getRow("SELECT id, name, rights FROM users WHERE login=?s", $login);
		$session_login = $row["id"];
		$name_user = $row["name"];
		$id_rights = $row["rights"];
	}
	foreach($_POST as $index => $value){
		$_POST[$index] = trim($value);
	}

	$config = ConfigCRM::getInstance();
	$config->connect = $connect;
	$config->directory = $directory;
	$config->sync["link"] = $sync_api;
	if(isset($_POST["object"])){
		$config->object = $_POST["object"];
	}

  $configInstance->connect = $connect;

	$result = $func($connect);
	echo $result;
	$connect->disconnect();
	return;
}

?>
