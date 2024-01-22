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

$create_client = new CreateClient;


if(!$connect)
	return;

$client = new GuzzleHttp\Client(['verify' => false]);
$token = "7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4";

//$data_booking = json_decode(base64_decode($_POST["data"]));
//$data_booking_JSON = json_decode(base64_decode($_POST["data"]), TRUE);

$data_booking = json_decode(json_encode($_POST["data"]));
$data_booking_JSON = $_POST["data"];

if (count($data_booking)==0) exit();

//$log = print_r($data_booking, true).PHP_EOL;
//file_put_contents('kostyl_booking.txt', $log, FILE_APPEND);

//$log = print_r($data_booking_JSON, true).PHP_EOL;
//file_put_contents('kostyl_booking.txt', $log, FILE_APPEND);

foreach($data_booking_JSON as $index => $value){
	if(is_string($value)){
		$value = trim($value);
		$value = strip_tags($value);
	}elseif(is_array($value)){
		foreach($value as $ind => $value_array){
			if(is_string($value_array)){
				$value[$ind] = trim($value_array);
				$value[$ind] = strip_tags($value_array);
			}
		}
	}
	$data_booking->$index = $value;
	$data_booking_JSON[$index] = $value;
}


$booking_source = "";
$website = isset($data_booking->site)?$data_booking->site:"";
$id_obj = isset($data_booking->id_obj)?$data_booking->id_obj:0;
$email = isset($data_booking_JSON["email"])?$data_booking_JSON["email"]:"";
$state_program = isset($data_booking_JSON["state_program"]) ? (int)$data_booking_JSON['state_program'] : 0;

if($state_program) {
	$state_program = 1;
}

if(isset($data_booking->source))
	$booking_source = $data_booking->source;

$client_info = array(
	"surname" => isset($data_booking_JSON["sur"])?$data_booking_JSON["sur"]:"",
	"name" => isset($data_booking_JSON["name"])?$data_booking_JSON["name"]:"",
	"otch" => isset($data_booking_JSON["otch"])?$data_booking_JSON["otch"]:"",
	"telephone" => isset($data_booking_JSON["tel"])?$data_booking_JSON["tel"]:"",
	"email" => $email,
	"ip" => isset($data_booking_JSON["ip"])?$data_booking_JSON["ip"]:""
);

if(isset($data_booking_JSON['sex'])) {
	$client_info['sex'] = $data_booking_JSON['sex'];
	}

if(isset($gsok[$id_obj]))
	$id_obj = 96;

$today = date("Y-m-d");
$hash = md5(uniqid());
$days = isset($data_booking->days)?(int)$data_booking->days:1;
$date_z = isset($data_booking_JSON["date"])?date_change($data_booking_JSON["date"], "-", "."):date_change($today, "-", ".");
$reward = get_reward_object($connect, $id_obj, $date_z);

$source = select_index_source($booking_source);

$last_id = $create_client->create_client($client_info);

save_client_to_history($connect, $last_id, "Создание клиента");
$note_booking = isset($data_booking->note)?trim($data_booking->note):"";

if ($data_booking->bnovo==1) {

	$note_booking .= "\r\nБронирование БНОВО";

}


$aznak = false;

if ($id_obj=='1' && $data_booking_JSON["site"]=='санаторий-азнакаевский.рф') {

	$azn_text = 'Дата заезда: '.$data_booking_JSON["date"].'<br>';
	$azn_text .= 'Количество дней: '.$data_booking->days.'<br>';

	if(isset($data_booking->position) && $data_booking->position){
		$positions = json_decode($data_booking->position, TRUE);
		foreach($positions as $position){
			if ($position['id_room']>0) {
				$room = $connect->getOne("SELECT name FROM room WHERE id=?i", $position['id_room']);
				$azn_text .= 'Номер: '.$room.'<br>';
				$azn_text .= 'Количество: '.$position['number'].'<br>';
				$azn_text .= 'Цена: '.$position['price'].'<br><br>';
			}
		}
	}		

	$azn_text .= 'Имя: '.$data_booking_JSON["name"].'<br>';
	$azn_text .= 'Фамилия: '.$data_booking_JSON["sur"].'<br>';
	$azn_text .= 'Отчество: '.$data_booking_JSON["otch"].'<br>';
	$azn_text .= 'Телефон: '.$data_booking_JSON["tel"].'<br>';
	$azn_text .= 'E-mail: '.$email.'<br><br>';
	$azn_text .= 'Комментарий: '.$note_booking.'<br><br><br><br>';

	send_mail_sanata('sanatazn1@yahoo.com', 'Заявка на бронь с сайта санаторий-азнакаевский.рф', $azn_text);
	$aznak = true;

}

$note_booking .= "\r\n".$data_booking->position;

if ($data_booking->bnovo==1) $bnovo_in_sql = 1; else $bnovo_in_sql = 0;

$connect->query("INSERT INTO reckoning(date, turist, id_obj, rest, hash, website, source, form_booking, note, bnovo) VALUES (?s, ?i, ?i, ?i, ?s, ?s, ?i, 'module',?s, ?i)", $today, $last_id, $id_obj, $last_id, $hash, $website, $source, $note_booking, $bnovo_in_sql);
$id = $connect->insertId();


$log = 'ID='.$id.' bnovo_in_sql='.$bnovo_in_sql;
file_put_contents('kostyl_booking.txt', $log, FILE_APPEND);

$id_tour = $connect->getOne("SELECT id_tour FROM object WHERE id=?i", $id_obj);
if($id_tour)
	$connect->query("UPDATE reckoning SET id_tour=?i WHERE id=?i", $id_tour, $id);
$add_one_day = $connect->getOne("SELECT add_one_day FROM object WHERE id=?i", $id_obj);

if ($aznak) $connect->query("UPDATE reckoning SET status=14, id_user=13 WHERE id=?i", $id);


if ($data_booking->bnovo==1) {

	$bnovo_token = get_bnovo_token($connect);

	//$log = PHP_EOL.'data_booking='.print_r($data_booking, true).PHP_EOL;
	//file_put_contents('kostyl_booking.txt', $log, FILE_APPEND);	

	$data = [];
	$data['token'] = $bnovo_token;
	$data['account_id'] = 34311;
	
	$booking_data = [];
	
	$booking_data['ota_id'] = 'sanata';
	$booking_data['ota_booking_id'] = $id;
	$booking_data['status_id']=1;
	$booking_data['name'] = $data_booking->name;
	$booking_data['surname'] = $data_booking->sur;
	$booking_data['email'] = $data_booking->email;
	$booking_data['phone'] = $data_booking->tel;
	$booking_data['comment'] = '';
	$booking_data['lang'] = 'ru';

	$position = json_decode($data_booking->position, TRUE);
	$position = $position[0];

	$bnovo_rate = $connect->getRow("SELECT * FROM `bnovo_plans_mathes` WHERE id_plan=?i", $position['rate']);
	$place = $connect->getRow("SELECT * FROM `place` WHERE id=?i", $position['place']);
	$occu = $connect->getRow("SELECT * FROM `bnovo_occupancies_mathes` WHERE id_place=?i AND `id_room`=?i", $position['place'], $position['id_room']);
	
	$room_types = [];
	$room_types[0]['arrival'] = date('Y-m-d', strtotime($data_booking->date));
	$room_types[0]['departure'] = date('Y-m-d', strtotime($data_booking->date)+86400*$data_booking->days);
	$room_types[0]['room_type_id'] = $occu['id_bnovo'];
	$room_types[0]['plan_id'] = $bnovo_rate['id_bnovo'];
	$room_types[0]['count'] = 1; //Тут всегда 1
	$room_types[0]['adults'] = $place['adult_on_main_place']+$place['adult_on_add_place']; //Тут количество взрослых согласно размещения
	$room_types[0]['children'] = 0;
	$room_types[0]['amount'] = $data_booking->sum;
	
	
	$prices = [];
	$start = strtotime($data_booking->date);
	while ($start < strtotime($data_booking->date)+86400*$data_booking->days) {
		$prices[date('Y-m-d', $start)] = $position['price'];
		$start = $start + 86400;
	}
	
	//$prices = json_encode($prices);
	
	$room_types[0]['prices'] = $prices;
	
	$booking_data['room_types'] = $room_types;
	
	$data['booking_data'] = $booking_data;

	$log = PHP_EOL.'BNOVO data='.print_r($data, true).PHP_EOL;
	file_put_contents('kostyl_booking.txt', $log, FILE_APPEND);	

	$data = json_encode($data);

	$log = PHP_EOL.'BNOVO data_оыщт='.$data.PHP_EOL;
	file_put_contents('kostyl_booking.txt', $log, FILE_APPEND);		

	
	$ch = curl_init('https://api.reservationsteps.ru/v1/api/channel_manager_bookings'); 
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	$res = json_decode(curl_exec($ch), true);
	curl_close($ch);
	
	$connect->query("UPDATE reckoning SET `bnovo_json`='?s' WHERE `id`=?i", $data, $id);

	$log = PHP_EOL.'UPDATE='.$connect->last_query().PHP_EOL;
	file_put_contents('kostyl_booking.txt', $log, FILE_APPEND);		

	$log = PHP_EOL.'BNOVO res='.print_r($res, true).PHP_EOL;
	file_put_contents('kostyl_booking.txt', $log, FILE_APPEND);

}




$check_quota = 0;

if(isset($data_booking->position) && $data_booking->position){

	$positions = json_decode($data_booking->position, TRUE);
	foreach($positions as $position){
		$type_index = 1;
		if(isset($position["type_index"]) AND $position["type_index"] > 1){
			$type_index = (int)$position["type_index"];
			if($type_index == 3)
				$type_index = 2;
		}
		$id_room = isset($position["id_room"])?(int)$position["id_room"]:0;
		$note = isset($position["place"])?$position["place"]:"";
		$price = isset($position["price"])?(float)$position["price"]:0;
		$number = isset($position["number"])?(int)$position["number"]:1;

		$connect->query("INSERT INTO position_reck(id_room, schet, days, date_z, number, sum, type, note, reward, add_one_day) VALUES (?i, ?i, ?i, ?s, ?s, ?s, ?i, ?s, ?s, ?i)", $id_room, $id, $days, $date_z, $number, $price, $type_index, $note, $reward, (int)$add_one_day);
		if(isset($position["ratePlan"]) AND $position["ratePlan"] > 0){
			if($connect->getOne("SELECT id FROM object WHERE id=?i AND (check_places=1 OR check_places=2)", $id_obj)){
				$check_quota = 1;
				$insert = $connect->insertId();
				$connect->query("UPDATE position_reck SET ratePlan=?i WHERE id=?i", $position["ratePlan"], $insert);
			}
		}
	}
	if($check_quota == 1){
		$connect->query("INSERT INTO booking(bid, from_booking) VALUES (?i, 'site')", $id);
		$connect->query("UPDATE reckoning SET form_booking='quota' WHERE id=?i", $id);
	}
}else{
	$connect->query("INSERT INTO position_reck(schet, id_room, days, date_z, number, type, reward, add_one_day) VALUES (?i, 0, ?i, ?s, 1, 1, ?s, ?i)", $id, $days, $date_z, $reward, (int)$add_one_day);
	$connect->query("UPDATE reckoning SET note=?s, form_booking='default-form' WHERE id=?i", $note_booking, $id);
}

$connect->query("UPDATE reckoning SET number_turist=?i WHERE id=?i", $connect->getOne("SELECT COUNT(*) FROM position_reck WHERE schet=?i", $id), $id);

change_arrival_date($connect, $id);
recalculation_sum($connect, $id);
save_schet_to_history($connect, $id, "Новая заявка от клиента");

if($state_program)
	$connect->query("UPDATE reckoning SET state_program=1 WHERE id=?i", $id);

if(isset($data_booking->promo_code) && $data_booking->promo_code != ""){
	$promo_code = mb_strtolower($data_booking->promo_code);
	$itog = $connect->getOne("SELECT sum FROM reckoning WHERE id=?i", $id);
	$bonus = check_promotional_code($promo_code, $id_obj, $itog, array("arrival" => $date_z, "days" => $days), $last_id, $connect);
	if(!is_array($bonus) && $bonus){
		$connect->query("INSERT INTO bonus(date, turist, sum, type, note, promocode) VALUES (?s, ?i, ?s, 3, ?s, ?s)", $today, $last_id, $bonus, "Подарочный бонус", $promo_code);
		$connect->query("INSERT INTO bonus(date, schet, turist, sum, cause) VALUES (?s, ?i, ?i, ?i, 1)", $today, $id, $last_id, $bonus * (-1));
		$connect->query("UPDATE reckoning SET promo_code=?s WHERE id=?i", $promo_code, $id);
			$connect->query("INSERT INTO promo_code_using(`promo_code`, `client_id`, `reck_id`, `timestamp`) VALUES (?s, ?i, ?i, ?i)", $promo_code, $last_id, $id, gmdate("U"));
		save_schet_to_history($connect, $id, "Использование промокода");
	}
	elseif(is_array($bonus)) {
			save_schet_to_history($connect, $id, $bonus['msg']);
		$connect->query("UPDATE reckoning SET promo_code=?s WHERE id=?i", $promo_code, $id);
	}
}

$config = ConfigCRM::getInstance();
$config->booking = $id;
$config->turist = $last_id;
$config->connect = $connect;

$configNew = \App\lib\CRM\Config\Client::getInstance();
$configNew->connect = $connect;
$configNew->directory = $directory;
$configNew->clientCabinet = $clientCabinet;
$configNew->objectCabinet = $objectCabinet;

$send = new SendMailTurist;
	$send->send_login();

/*$telephone = clear_telephone($connect->getOne("SELECT telephone FROM klient WHERE id=?i", $last_id));
if($telephone){
	$id_obj = $connect->getOne("SELECT id_obj FROM reckoning WHERE id=?i", $id);
	$object = get_object($connect, $id_obj, "type");
	$text = "Заявка №".$id." в ".$object." принята в обработку. 8-800-600-16-20 Санатории-России.рф";
	send_sms($connect, $telephone, $booking, $text, "new-booking");
}*/

$resp = array();
$resp['success'] = 1;
echo json_encode($resp, JSON_UNESCAPED_UNICODE);

//$log = 'DATA='.json_encode($resp, JSON_UNESCAPED_UNICODE).PHP_EOL;
//file_put_contents('kostyl_booking.txt', $log, FILE_APPEND);

?>