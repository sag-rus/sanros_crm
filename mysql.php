<?php
//ini_set("display_errors",1);
//error_reporting(E_ALL);
	$func = isset($_POST["func"])?$_POST['func']:(isset($_GET['func'])?$_GET['func']:"");

	if(!$func)
		return;
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

	$configInstance->mail = $conf->email_module;

	$onlinePaymentInfoSber = array(
		"link" => $conf->BANK_PAYMENT_LINK_SBERBANK,
		"link_test" => $conf->BANK_PAYMENT_LINK_SBERBANK_TEST,
		"link_v2" => $conf->BANK_PAYMENT_LINK_SBERBANK_V2,
		"link_v3" => $conf->BANK_PAYMENT_LINK_SBERBANK_V3,
		"link_v4" => $conf->BANK_PAYMENT_LINK_SBERBANK_V4,
		"commission" => $conf->BANK_COM_SBERBANK,
		"userName" => $conf->USERNAME_SBERBANK,
		"userName_test" => $conf->USERNAME_SBERBANK_TEST,
		"userName_v2" => $conf->USERNAME_SBERBANK_V2,
		"userName_v3" => $conf->USERNAME_SBERBANK_V3,
		"userName_v4" => $conf->USERNAME_SBERBANK_V4,
		"password" => $conf->PASSWORD_SBERBANK,
		"password_test" => $conf->PASSWORD_SBERBANK_TEST,
		"password_v2" => $conf->PASSWORD_SBERBANK_V2,
		"password_v3" => $conf->PASSWORD_SBERBANK_V3,
		"password_v4" => $conf->PASSWORD_SBERBANK_V4
	);

	$configInstance->bonus = [
    "bonus-booking" => $conf->bonus_rec,
    "bonus-affiliate" => $conf->bonus_ref
	];

	$configInstance->contactInfo = [
    "free-line" => $conf->linia
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
		if(is_string($value))
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
	$configInstance->directory = $directory;

	$result = $func($connect);
	echo $result;
	$connect->disconnect();
	return;
}

?>
