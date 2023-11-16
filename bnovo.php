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
$id_obj = 60;

try{

	$object = $connect->getAll("SELECT * FROM object WHERE id=$id_obj");
	$response['code'] = 200;
	$response['hotel_id'] = $object[0]['id'];
	$response['hotel_title'] = $object[0]['full_name'];

	$response['rooms'] = array();

	$today = date("Y-m-d");
	$dates = $connect->getAll("SELECT id, DATE_FORMAT(start, '%e.%m.%Y') as date_start, DATE_FORMAT(end, '%e.%m.%Y') as end FROM date_price WHERE id_obj=?i AND active=0 AND end>=?s ORDER BY start", $id_obj, $today);	

	$ratePlans = $connect->getAll("SELECT `id`, `name` FROM `rate_plan` WHERE `object` = ?i AND `status` = 1 ORDER BY id LIMIT 1", $id_obj);
    if(count($ratePlans) === 0)
        $ratePlans = $connect->getAll("SELECT `id`, `name` FROM `rate_plan` WHERE id = 1");	

	/*echo '<pre>ratePlans';
	print_r($ratePlans);
	echo '</pre>';*/

	if (is_array($dates[0]) && count($ratePlans)>0) {
		$occupancies = $connect->getAll("SELECT ranges.counter, ranges.id, ranges.name, ranges.type, place.name as place, ranges.treatment, place.type as place_type FROM ranges, place WHERE ranges.id_obj=?i AND (ranges.place=place.id) AND ranges.id_date=?i AND ranges.active = 0 AND ranges.rate_plan = ?i ORDER BY ranges.counter, place.type", $id_obj, $dates[0]['id'], $ratePlans[0]['id']);

		$check = '';
		$occu = array();
		foreach ($occupancies as $occupancy) {
			if (mb_strpos($check, ','.$occupancy['type'].'='.$occupancy['place'].'='.$occupancy['treatment'].'='.$occupancy['place_type'].',')===FALSE) {
				$check .= ','.$occupancy['type'].'='.$occupancy['place'].'='.$occupancy['treatment'].'='.$occupancy['place_type'].',';
				$occu[] = $occupancy;
			}
		}

		/*echo '<pre>occu';
		print_r($occu);
		echo '</pre>';*/

		$rooms = $connect->getAll("SELECT room.id, room.name as room, room.main_place, housing.name as housing_name FROM room, housing WHERE room.id_obj=?i AND (room.housing=housing.id) AND room.active=0 ORDER BY housing.name", $id_obj);

		/*echo '<pre>rooms';
		print_r($rooms);
		echo '</pre>';*/

		$cnt = 0;

		foreach ($rooms as $room) {
			$temp = array();
			$temp['title'] = $room['room'].' ('.$room['housing_name'].')';
			$temp['occupancies'] = array();
			if ($room['main_place']==1) {
				$temp['occupancies'][$room['id'].'_a.1'] = '1 взрослый';
			}
			if ($room['main_place']==2) {
				$temp['occupancies'][$room['id'].'_a.1'] = '1 взрослый';
				$temp['occupancies'][$room['id'].'_a.2'] = '2 взрослых';
			}
			if ($room['main_place']==3) {
				$temp['occupancies'][$room['id'].'_a.1'] = '1 взрослый';
				$temp['occupancies'][$room['id'].'_a.2'] = '2 взрослых';
				$temp['occupancies'][$room['id'].'_a.3'] = '3 взрослых';
			}
			if ($room['main_place']==4) {
				$temp['occupancies'][$room['id'].'_a.1'] = '1 взрослый';
				$temp['occupancies'][$room['id'].'_a.2'] = '2 взрослых';
				$temp['occupancies'][$room['id'].'_a.3'] = '3 взрослых';
				$temp['occupancies'][$room['id'].'_a.4'] = '4 взрослых';
			}			
			$response['rooms'][$room['id']] = $temp;
			$cnt++;
			if ($cnt>5) break;
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

	}

	/*$housings = $connect->getAll("SELECT * FROM housing WHERE `id_obj`=$id_obj");
	foreach ($housings as $housing) {
		$rooms = $connect->getAll("SELECT * FROM room WHERE `id_obj`=$id_obj and `housing`='$housing[id]' and `active`=0");

		foreach ($rooms as $room) {
			$response['rooms'][$room['id']]['title'] = $room['name'].' ('.$housing['name'].')';
		}
	}*/


	/*echo '<pre>';
	print_r($response);
	echo '</pre>';*/

	echo json_encode($response);

} catch (Exception $e) {
	$response['code'] = 500;
	$response['message'] = $e->getMessage();
}



?>