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



/* ---------------------------- */

//AUTH
$url = 'https://partner.qatl.ru/auth/token';
$data = array(
    "grant_type"=> 'client_credentials', 
    "client_id" => 'chm_sr2',
    "client_secret" => 'T5AipL1NMo61LdA3xwnKb7cGbldbaDpS'
);
$ch = curl_init($url); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_HEADER, false);
$result = json_decode(curl_exec($ch), true);
curl_close($ch);

$token = $result['access_token'];
echo 'token='.$result['access_token'].'<br><br><br>';
//AUTH

$lines = $connect->getAll("SELECT * FROM `1_tl_webhook` WHERE `worked`=0 ORDER BY id DESC LIMIT 3");

foreach ($lines as $line) {

    $ch = curl_init('https://partner.qatl.ru/api/content/v1/properties/'.$line['entityId']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , "Authorization: Bearer ".$token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);

    echo '<pre>';
    print_r(json_decode($result, true));
    echo '</pre>';

    $connect->getAll("UPDATE `1_tl_webhook` SET `worked`=1, `content_api_data`=?s WHERE `id`=?i", $result, $line['id']);

}

?>