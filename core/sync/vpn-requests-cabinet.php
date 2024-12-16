<?php
	$loader = require( __DIR__ . '/../../vendor/autoload.php');
	date_default_timezone_set("Asia/Baghdad");

	$directory = __DIR__.'/../..';
	define("_FOLDERSITE_", $directory);

	$last_time = file_get_contents(_FOLDERSITE_."/core/sync/file/time.txt");
	/*if(time() < ($last_time + 60)){
		return;
	}*/
	file_put_contents(_FOLDERSITE_."/core/sync/file/log.txt", $last_time. " = ".time()." -- запуск скрипта\r\n", FILE_APPEND);

	include_once(_FOLDERSITE_."/core/sync/API/client.php");
	include_once(_FOLDERSITE_."/core/sync/API/agency.php");
	include_once(_FOLDERSITE_."/core/sync/API/object.php");
	include_once(_FOLDERSITE_."/core/object.php");
	include_once(_FOLDERSITE_."/core/admin/news.php");
	include_once(_FOLDERSITE_."/core/sync/API/payment.php");
	include_once(_FOLDERSITE_."/core/sync/API/sitehelp.php");
	include_once(_FOLDERSITE_."/core/sync/API/travelline.php");

	include_once(_FOLDERSITE_."/core/functions.php");
	include_once(_FOLDERSITE_."/core/lib/mail.php");
	include_once(_FOLDERSITE_."/core/lib/sms.php");
	include_once(_FOLDERSITE_."/core/lib/Mysql.Class.php");
	include_once(_FOLDERSITE_."/config.php");

	$conf = new JConfig;
	$sync = $conf->sync_base;
	$CRM = $conf->CRM;

	include_once(_FOLDERSITE_."/core/upload/price.php");
	include_once(_FOLDERSITE_."/core/upload/default.php");
	include_once(_FOLDERSITE_."/core/upload/sync-objects-api.php");


	$unisender_api_key = $conf->unisender_api_key;

	define("DEFAULT_OBJECT_IMAGE", "http://tonia.ru/price/object/head/default.jpg");
	$COLORS = array("success" => "#CAFFC3", "cancel" => "#FFD3C5", "info" => "#D0DDFF", "waiting" => "#E7C97C");

	$CHAT_GROUP = array(
		1 => array("name" => "Оператор бронирования", "icon" => "fa-user-plus"),
		2 => array("name" => "Тех.поддержка", "icon" => "fa-cogs")
	);
	$CHAT_GROUP_AGENCY = array(
		1 => array("name" => "Оператор бронирования", "icon" => "fa-user-plus"),
		2 => array("name" => "Тех.поддержка", "icon" => "fa-cogs")
	);
	$CHAT_GROUP_CLIENT = array(
		1 => array("name" => "Оператор бронирования", "icon" => "fa-user-plus"),
		2 => array("name" => "Тех.поддержка", "icon" => "fa-cogs")
	);
	$CHAT_GROUP_OBJECT = array(
		1 => array("name" => "Оператор бронирования", "icon" => "fa-user-plus"),
		2 => array("name" => "Тех.поддержка", "icon" => "fa-cogs")
	);

	$connect = connect_to_MySQL_directory();

	$index = 0;
	$t = 0;

	$onlinePaymentInfo = array(
		"link" => $conf->BANK_PAYMENT_LINK,
		"commission" => $conf->BANK_COM,
		"userName" => $conf->USERNAME_ALFA,
		"password" => $conf->PASSWORD_ALFA
	);

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

	$clientCabinet = array(
		"link" => $conf->turist_cabinet
	);
	$contactInfo = array(
		"free-line" => $conf->linia
	);
	$objectCabinet = array(
		"link" => $conf->object_cabinet
	);
	$bonus = array(
		"bonus-booking" => $conf->bonus_rec,
		"bonus-affiliate" => $conf->bonus_ref
	);

	$config = ConfigCRM::getInstance();
	$config->connect = $connect;
	$config->onlinePaymentInfo = $onlinePaymentInfo;
	$config->clientCabinet = $clientCabinet;
	$config->objectCabinet = $objectCabinet;
	$config->contactInfo = $contactInfo;
	$config->bonus = $bonus;
	$config->mail = $conf->email_module;
	$config->directory = $directory;

	$configNew = \App\lib\CRM\Config\Client::getInstance();

	$configNew->connect = $connect;
	$configNew->onlinePaymentInfo = $onlinePaymentInfoSber;
	$configNew->clientCabinet = $clientCabinet;
	$configNew->objectCabinet = $objectCabinet;
	$configNew->contactInfo = $contactInfo;
	$configNew->bonus = $bonus;
	$configNew->mail = $conf->email_module;
	$configNew->directory = $directory;

	//define("CABINET", $clientCabinet);
	define("CABINET", "http://xn----7sba6aaba8akdsdekah.xn--p1ai/client/");
	$array_request = [];

    if(!$connect){
        exit();
    }

    //$_POST = ;
    parse_str(file_get_contents('php://input'), $_POST);
    $connect -> query("INSERT INTO `1_vpn_req_log_cabinet` SET `id`=0, `datetime`=NOW(), `ip`='$_POST[ip]', `func`='$_POST[func]', `query`='".print_r($_POST, true)."'");
    $log_id = $connect->insertId();

    $func = $_POST["func"];
    if(function_exists($func)){
        
        $config = ConfigCRM::getInstance();
        $configNew = App\lib\CRM\Config\Client::getInstance();
        if(isset($_POST["session"])) {
            $config->session = $_POST["session"];
            $configNew->session = $_POST["session"];
        }

        if(isset($_POST["object"])) {
            $config->object = $_POST["object"];
            $configNew->object = $_POST["object"];
        }

        if(isset($_POST["booking"])) {
            $config->booking = $_POST["booking"];
            $configNew->booking = $_POST["booking"];
        }

        $answer = $func($connect, $_POST);

		if (!$answer) $connect -> query("UPDATE `1_vpn_req_log_cabinet` SET `answer`='ANSWER: FALSE' WHERE `id`=$log_id");
		else $connect -> query("UPDATE `1_vpn_req_log_cabinet` SET `answer`='ANSWER: ".print_r($answer, true)."' WHERE `id`=$log_id");

        echo json_encode($answer); 
		       
    } else $connect -> query("UPDATE `1_vpn_req_log_cabinet` SET `answer`='func NOT exist' WHERE `id`=$log_id");


?>
