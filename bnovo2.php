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


$token = get_bnovo_token($connect);

$data = [];
$data['token'] = $token;
$data['account_id'] = 34311;

$booking_data = [];

$booking_data['ota_id'] = 'sanata';
$booking_data['ota_booking_id'] = '555'; //Тут будем указывать номер заявки!
$booking_data['status_id']=1;
$booking_data['name'] = 'Рустем';
$booking_data['surname'] = 'Сагдиев';
$booking_data['email'] = 'sagrus@yandex.ru';
$booking_data['phone'] = '+79093071969';
$booking_data['comment'] = 'комментарий гостя к бронированию';
$booking_data['lang'] = 'ru';

$room_types = [];
$room_types['arrival'] = '2024-01-05';
$room_types['departure'] = '2024-01-08';
$room_types['room_type_id'] = 398056;
$room_types['plan_id'] = 169964;
$room_types['count'] = 1; //Тут всегда 1
$room_types['adults'] = 2; //Тут количество взрослых согласно размещения
$room_types['children'] = 0;
$room_types['amount'] = 8800;

$prices = [];
$prices['2024-01-05'] = 2200;
$prices['2024-01-06'] = 2200;
$prices['2024-01-07'] = 2200;
$prices['2024-01-08'] = 2200;

$room_types['prices'] = $prices;

$booking_data['room_types'] = $room_types;

$data['booking_data'] = $booking_data;


echo '<pre>';
print_r($data);
echo '</pre>';

$data = json_encode($data);

echo 'json='.$data."\r\n";


$ch = curl_init('https://api.reservationsteps.ru/v1/api/channel_manager_bookings'); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$res = json_decode(curl_exec($ch), true);
curl_close($ch);

echo '<pre>';
print_r($res);
echo '</pre>';

?>