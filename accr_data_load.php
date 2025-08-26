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


$items = $connect->getAll("SELECT * FROM `accr_data` WHERE `data`='' OR `data_datetime` < NOW() - INTERVAL 3 MONTH ORDER BY id LIMIT 1");

foreach ($items as $item) {
    $ch = curl_init('https://tourism.fsa.gov.ru/api/v1/export/resorts/'.$item['ext_id'].'/get');

    curl_setopt($ch, CURLOPT_HTTPGET, true); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    

    $headers = [
        'Api-Key: trsm-1_zDMWC8EfC7Qmvz9h7V3A.cq_Wd8vwCLLWymebNgfBuA'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $res = curl_exec($ch);

    // Проверка на ошибки
    if (curl_errno($ch)) {
        echo 'Ошибка cURL: ' . curl_error($ch);
    } else {
        // Получение HTTP-кода ответа
        $response = json_decode($res, true);
        echo '<pre>';
        print_r($response);
        echo '</pre>';

        $connect->query("UPDATE `accr_data` SET `name`=?ы, `data`=?s, `data_dateime`=NOW() WHERE id=?i", $response['hotel']['main']['fullName'], $res, $item['id']);
        echo $connect->last_query();

        echo 'done';
    }

    // Закрытие cURL сессии
    curl_close($ch);
}

//echo '<meta http-equiv="refresh" content="0,URL=/CRM/accr_data_load.php">';

?>