<?php

	$directory = dirname(__FILE__)."/../..";
	date_default_timezone_set("Asia/Baghdad");

	$last_time = file_get_contents($directory."/core/sync/file/time.txt");
	if(time() < ($last_time + 60)){
		return;
	}
	file_put_contents($directory."/core/sync/file/log.txt", $last_time. " = ".time()." - запуск скрипта\r\n", FILE_APPEND);

	include_once($directory."/config.php");
	$conf = new JConfig;
	$sync = $conf->sync_base;
	$CRM = $conf->CRM;
	include_once($directory."/core/functions.php");
	include_once($directory."/core/lib/Mysql.Class.php");
	include_once($directory."/core/lib/mail.php");

	include_once($directory."/core/sync/API/client.php");
	include_once($directory."/core/sync/API/agency.php");
	include_once($directory."/core/sync/API/object.php");
	include_once($directory."/core/sync/API/payment.php");
	include_once($directory."/core/sync/API/sitehelp.php");

	define("DEFAULT_OBJECT_IMAGE", "http://tonia.ru/price/object/head/default.jpg");
	define("CABINET", "http://sanata-trevel.ru/client/");
	define("BANK_COM", 2.5);
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

	$connect = connect_to_MySQL();

	$index = 0;
	$t = 0;

	while($t != 1){

		$index++;

		if(!$connect){
			$t = 1;
			//file_put_contents($directory."/core/sync/file/log.txt", time()." - остановка скрипта - нет коннекта\r\n", FILE_APPEND);
		}
		if($index >= 10000){
			$t = 1;
			//file_put_contents($directory."/core/sync/file/log.txt", time()." - остановка скрипта - лимит\r\n", FILE_APPEND);
		}
		if(!file_exists($directory."/core/sync/file/kill.txt")){
			$t = 1;
			//file_put_contents($directory."/core/sync/file/log.txt", time()." - принудительная остановка скрипта\r\n", FILE_APPEND);
		}

		$data = request_to_sync(array("func" => "get_query_cabinet"));

		$answer = array();

		foreach($data as $query){
			$id = $query["id"];
			$query = json_decode(base64_decode($query["query"]), TRUE);

			$func = $query["func"];
			if(function_exists($func)){
				$answer[$id] = $func($connect, $query);
			}
		}
		if($answer){
			request_to_sync(array("func" => "answer_query_cabinet", "data" => json_encode($answer)));
		}
		file_put_contents($directory."/core/sync/file/time.txt", time());
		sleep(5);
	}

	function testConnect(){
		return 1;
	}

?>
