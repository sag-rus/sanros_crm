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



$connect->query("INSERT INTO klient(surname, name, otch, telephone, email, original_data) VALUES (?s, ?s, ?s, ?s, ?s, ?s)", 'Рустем', 'тест ', 'добавление', '79093071969', 'sag@sagrus.ru', 'original_data');
$connect->query("INSERT INTO klient(date, surname, name, otch, telephone, email, original_data) VALUES (?s, ?s, ?s, ?s, ?s, ?s)", NULL, 'Рустем', 'тест ', 'добавление', '79093071969', 'sag@sagrus.ru', 'original_data');
$connect->query("INSERT INTO klient(date, surname, name, otch, telephone, email, original_data) VALUES (?s, ?s, ?s, ?s, ?s, ?s)", '1983-09-28', 'Рустем', 'тест ', 'добавление', '79093071969', 'sag@sagrus.ru', 'original_data');

?>