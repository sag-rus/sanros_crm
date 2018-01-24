<?php
	$loader = require( __DIR__ . '/../../vendor/autoload.php');
	date_default_timezone_set("Asia/Baghdad");

	$directory = dirname(__FILE__)."/../..";
	define("_FOLDERSITE_", $directory);

	$last_time = file_get_contents(_FOLDERSITE_."/core/sync/file/time.txt");
	if(time() < ($last_time + 60)){
		return;
	}
	file_put_contents(_FOLDERSITE_."/core/sync/file/log.txt", $last_time. " = ".time()." - запуск скрипта\r\n", FILE_APPEND);

	include_once(_FOLDERSITE_."/core/sync/API/client.php");
	include_once(_FOLDERSITE_."/core/sync/API/agency.php");
	include_once(_FOLDERSITE_."/core/sync/API/object.php");
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
  	"commission" => $conf->BANK_COM_SBERBANK,
  	"userName" => $conf->USERNAME_SBERBANK,
  	"password" => $conf->PASSWORD_SBERBANK
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

	$array_request = array();

	while($t != 1){

		$index++;

		if(!$connect){
			$t = 1;
		}
		if(!file_exists($directory."/core/sync/file/kill.txt")){
			$t = 1;
		}
		if($index >= 1000){
			$t = 1;
		}

		$data = request_to_sync(array("func" => "get_query_cabinet"));

		$answer = array();
		foreach($data as $query){
			$id = $query["id"];
			$query = json_decode(base64_decode($query["query"]), TRUE);
			$func = $query["func"];
			$check = $connect->getOne("SELECT id FROM cabinet_request WHERE request=?i LIMIT 1", $id);
			if(!$check AND function_exists($func)){
				echo " ".$func." ";
				$config = ConfigCRM::getInstance();
				$configNew = App\lib\CRM\Config\Client::getInstance();
				if(isset($query["session"])) {
          $config->session = $query["session"];
          $configNew->session = $query["session"];
        }

        if(isset($query["object"])) {
          $config->object = $query["object"];
          $configNew->object = $query["object"];
        }

				if(isset($query["booking"])) {
          $config->booking = $query["booking"];
          $configNew->booking = $query["booking"];
        }
				$answer[$id] = $func($connect, $query);
				$connect->query("INSERT INTO cabinet_request(request) VALUES(?i)", $id);
			}else{
				file_put_contents($directory."/core/sync/file/no-func.txt", $func);
			}
		}

		if($answer)
			request_to_sync(array("func" => "answer_query_cabinet", "data" => json_encode($answer)));

		$bookings = check_new_update_booking($connect);
		if($bookings["check"] == 1){
			$request = array("func" => "update_new_bookings_travelline", "data" => json_encode($bookings["bookings"]));
			$return = request_to_sync($request);
			confirm_update_booking($connect, $return);
		}

		file_put_contents($directory."/core/sync/file/time.txt", time());
		sleep(5);
	}

	function testConnect(){
		return 1;
	}

?>
