<?php
use GuzzleHttp\Client;

header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');


$log = print_r($_POST, true).PHP_EOL;
file_put_contents('bnovo_save_matches.txt', $log, FILE_APPEND);


require_once __DIR__."/vendor/autoload.php";
$directory = dirname(__FILE__);
define("_FOLDERSITE_", $directory);

include_once($directory."/config.php");
$conf = new JConfig;
$sync = $conf->sync_base;
$unisender_api_key = $conf->unisender_api_key;
include_once($directory."/core/functions.php");
include_once($directory."/core/lib/Mysql.Class.php");

include_once($directory."/core/lib/mail.php");
include_once($directory."/core/lib/sms.php");
include_once($directory."/core/lib/PHPMailer/class.phpmailer.php");

$clientCabinet = array(
	"link" => $conf->turist_cabinet
);
$objectCabinet = array(
	"link" => $conf->object_cabinet
);
$mail = array(
	"module" => $conf->email_module
);

$connect = connect_to_MySQL_directory();
$config = ConfigCRM::getInstance();
$config->connect = $connect;
$config->directory = $directory;
$config->clientCabinet = $clientCabinet;
$config->objectCabinet = $objectCabinet;

$configNew = \App\lib\CRM\Config\Client::getInstance();
$configNew->connect = $connect;
$configNew->directory = $directory;
$configNew->clientCabinet = $clientCabinet;
$configNew->objectCabinet = $objectCabinet;



/* ---------------------------- */
$response = array();
//$id_obj = 922;

$data = json_decode($_POST['data'], true);

//$data['hotel_id'] = $id_obj; //Костыль на время тестов!!!

//Сохраняем сопоставления тарифов
$connect->query("DELETE FROM bnovo_plans_mathes WHERE id_obj=?i AND id_account_bnovo=?i", $data['hotel_id'], $data['account_id']);
foreach ($data['rates'] as $key => $value) {
	$connect->query("INSERT bnovo_plans_mathes SET id=0, id_obj=?i, id_plan=?i, id_bnovo=?i, id_account_bnovo=?i", $data['hotel_id'], $value[0], $key, $data['account_id']);
}


//Сохраняем сопоставления номеров
$connect->query("DELETE FROM bnovo_rooms_mathes WHERE id_obj=?i AND id_account_bnovo=?i", $data['hotel_id'], $data['account_id']);
foreach ($data['roomtypes'] as $key => $value) {
	$connect->query("INSERT bnovo_rooms_mathes SET id=0, id_obj=?i, id_room=?i, id_bnovo=?i, id_account_bnovo=?i", $data['hotel_id'], $value[0], $key, $data['account_id']);
}



//Сохраняем сопоставления размещений
$connect->query("DELETE FROM bnovo_occupancies_mathes WHERE id_obj=?i AND id_account_bnovo=?i", $data['hotel_id'], $data['account_id']);
foreach ($data['occupancies'] as $key => $value) {
	foreach ($value as $item) {
		//if ($data['roomtypes'][$key][0]>0) {

			$id_room = 0;
			$id_room = explode('_', $item);
			$id_room = $id_room[0];

			$place = $connect->getRow("SELECT * FROM place WHERE id_obj=?i and id_room=?i and `export_id`=?s", $data['hotel_id'], $id_room, $item);	
			if ($place['id']>0) {
				$connect->query("INSERT bnovo_occupancies_mathes SET id=0, id_obj=?i, id_room=?i, id_place=?i, id_bnovo=?i, id_account_bnovo=?i", $data['hotel_id'], $id_room, $place['id'], $key, $data['account_id']);
			}
		//}
	}
}


$response['ok'] = 1;

echo json_encode($response);

/*try{


} catch (Exception $e) {
	$response['code'] = 500;
	$response['message'] = $e->getMessage();
}*/





?>