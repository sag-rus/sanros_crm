<?php
use GuzzleHttp\Client;

//header('Content-Type: application/json;charset=utf-8');
//header('Access-Control-Allow-Origin: *');

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


$data = file_get_contents('compare_result_id_unique.json');
$data = json_decode($data, true);

foreach ($data as $item) {
    print_r($item);
    break;
}


//$connect->query("UPDATE `accr_data` SET `name`=?s, `address`=?s, `data`=?s, `data_datetime`=NOW() WHERE id=?i", $response['hotel']['main']['fullName'],$response['hotel']['main']['addressList'][0]['name'], $res, $item['id']);


echo '<meta http-equiv="refresh" content="0,URL=/CRM/accr_data_load.php">';

?>