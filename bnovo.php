<?php
use GuzzleHttp\Client;

header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');


$log = print_r($_POST, true).PHP_EOL;
file_put_contents('kostyl_booking.txt', $log, FILE_APPEND);


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
$id_obj = '';
//$id_obj = 922;
$id_obj = $_GET['id_obj'];
if ($id_obj=='') exit();

try{

	$object = $connect->getAll("SELECT * FROM object WHERE id=$id_obj");
	$response['code'] = 200;
	$response['hotel_id'] = $object[0]['id'];
	$response['hotel_title'] = $object[0]['full_name'];

	$response['rooms'] = array();

	$ratePlans = $connect->getAll("SELECT `id`, `name` FROM `rate_plan` WHERE `object` = ?i AND `status` = 1 ORDER BY id", $id_obj);
    if(count($ratePlans) === 0)
        $ratePlans = $connect->getAll("SELECT `id`, `name` FROM `rate_plan` WHERE id = 1");	

	/*echo '<pre>ratePlans';
	print_r($ratePlans);
	echo '</pre>';*/

	$rooms = $connect->getAll("SELECT room.id, room.name as room, room.main_place, housing.name as housing_name FROM room, housing WHERE room.id_obj=?i AND (room.housing=housing.id) AND room.active=0 ORDER BY housing.name", $id_obj);

	$cnt = 0;
	if (count($room)>0) {
		if ($_GET['debug']==1) echo 'rooms with housing';
		//есть здания
		foreach ($rooms as $room) {
			$temp = array();
			$temp['title'] = $room['room'].' ('.$room['housing_name'].')';
			$temp['occupancies'] = array();

			$occupancies = $connect->getAll("SELECT * FROM place WHERE id_obj=?i AND id_room=?i ORDER BY id", $id_obj, $room['id']);

			foreach ($occupancies as $occu) {
				/*$occu_name = '';
				if ($occu['adult_on_main_place']>0) $occu_name .= '_a.'.$occu['adult_on_main_place'];
				if ($occu['id_child_on_main_place']>0 && $occu['child_on_main_place']>0) $occu_name .= '_c.'.$occu['child_on_main_place'].'.'.$occu['id_child_on_main_place'];
				if ($occu['adult_on_add_place']>0) $occu_name .= '_e.'.$occu['adult_on_add_place'];
				if ($occu['id_child_on_add_place']>0 && $occu['child_on_add_place']>0) $occu_name .= '_x.'.$occu['child_on_add_place'].'.'.$occu['id_child_on_add_place'].'.1';
				if ($occu['id_child_no_place']>0 && $occu['child_no_place']>0) $occu_name .= '_x.'.$occu['child_no_place'].'.'.$occu['id_child_no_place'].'.0';
				$temp['occupancies'][$room['id'].$occu_name] = $occu['name'];*/
				$temp['occupancies'][get_place_export_id($room['id'], $occu)] = $occu['name'];
			}

			$response['rooms'][$room['id']] = $temp;
			$cnt++;
			if ($cnt>5) break;
		}
	} else {
		if ($_GET['debug']==1) echo 'rooms without housing';
		//нет зданий
		$rooms = $connect->getAll("SELECT room.id, room.name as room, room.main_place FROM room WHERE room.id_obj=?i and room.active=0 ORDER BY room.name", $id_obj);
		foreach ($rooms as $room) {
			$temp = array();
			$temp['title'] = $room['room'];
			$temp['occupancies'] = array();

			$occupancies = $connect->getAll("SELECT * FROM place WHERE id_obj=?i AND id_room=?i ORDER BY id", $id_obj, $room['id']);

			foreach ($occupancies as $occu) {
				$temp['occupancies'][get_place_export_id($room['id'], $occu)] = $occu['name'];
			}

			$response['rooms'][$room['id']] = $temp;
			$cnt++;
			if ($cnt>5) break;
		}		
	}

	foreach ($ratePlans as $plan) {
		$temp = array();
		$temp['title'] = $plan['name'];
		$temp['permitted_data'] = 'all';
		$temp['rooms'] = array();
		foreach ($rooms as $room) {
			$temp['rooms'][] = $room['id'];
		}
		$response['plans'][$plan['id']] = $temp;
	}

	$ages = $connect->getAll("SELECT * FROM child_occupancy WHERE id_obj=?i ORDER BY id", $id_obj);	

	foreach ($ages as $age) {
		$temp = array();
		$temp['min'] = $age['age_from'];
		$temp['max'] = $age['age_to'];
		$response['ages'][$age['id']] = $temp;
	}		

	/*$housings = $connect->getAll("SELECT * FROM housing WHERE `id_obj`=$id_obj");
	foreach ($housings as $housing) {
		$rooms = $connect->getAll("SELECT * FROM room WHERE `id_obj`=$id_obj and `housing`='$housing[id]' and `active`=0");

		foreach ($rooms as $room) {
			$response['rooms'][$room['id']]['title'] = $room['name'].' ('.$housing['name'].')';
		}
	}*/

	if ($_GET['debug']==1) {
		echo '<pre>';
		print_r($response);
		echo '</pre>';
	}

	echo json_encode($response);

} catch (Exception $e) {
	$response['code'] = 500;
	$response['message'] = $e->getMessage();
}



?>