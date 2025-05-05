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
include_once($directory."/core/objects/object.php");
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

$lines = $connect->getAll("SELECT * FROM `1_tl_webhook` WHERE `eventType`='PropertyModified' AND `worked`=1 ORDER BY id DESC LIMIT 3");
echo '<pre>';
print_r($lines);
echo '</pre>';
foreach ($lines as $line) {

    if (!empty($line['content_api_data'])) {
        tl_webhook_work_modified($connect, $line['id']);
    }
}

?>