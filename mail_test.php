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



$message = select_template_letter("turist/cabinet/new-account", "client", $id);
$link = CABINET."?func=activation&email=".$email."&hash=".$hash;
$message["content"] = str_replace("<hash>", $link, $message["content"]);
send_mail_sanata('sagrus@yandex.ru', $message["title"], $message["content"]);


?>