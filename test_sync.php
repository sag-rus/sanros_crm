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

function SyncPricesPack($client, $connect, $priceAr) {
    if (count($priceAr['data'])>0) {
        echo "Отправка пачки цен на https://sites.tonia.ru/api/resort/price/set/".$priceAr['id'].'<br>';
        /*echo '<pre>priceAr';
        print_r($priceAr);
        echo '</pre>';*/

        $res = $client->request('POST',"https://sites.tonia.ru/api/resort/price/set/".$priceAr['id'],[
            'form_params' => $priceAr
        ]);			
        $res = json_decode($res->getBody()->getContents(),true);
        /*echo '<pre>res';
        print_r($res);
        echo '</pre>';*/
        
        if(array_key_exists('success',$res)) {
            $success = (bool)(int)$res['success'];
            if($success) {
                foreach ($priceAr['data'] as $price) { 
                    echo "UPDATE `price` SET `synchronized` = '1' WHERE `id` = $price[id]<br>";
                    $connect->query("UPDATE `price` SET `synchronized` = '1' WHERE `id` = ?i",$price['id']);
                }
            }
            else {
                echo $res['msg'].": ".$price['id'].'<br>';
                print_r($res['fail_messages']);
            }
        }	
    }			
} 

function SyncPricesPack($client, $connect, $priceAr) {
    if (count($priceAr['data'])>0) {
        echo "Отправка пачки цен на https://sites.tonia.ru/api/resort/price/set/".$priceAr['id'].'<br>';
        /*echo '<pre>priceAr';
        print_r($priceAr);
        echo '</pre>';*/

        $res = $client->request('POST',"https://sites.tonia.ru/sagrus_mysql_test/index.php",[
            'form_params' => $priceAr
        ]);			
        $res = json_decode($res->getBody()->getContents(),true);
        echo '<pre>res';
        print_r($res);
        echo '</pre>';
        
        /*if(array_key_exists('success',$res)) {
            $success = (bool)(int)$res['success'];
            if($success) {
                foreach ($priceAr['data'] as $price) { 
                    echo "UPDATE `price` SET `synchronized` = '1' WHERE `id` = $price[id]<br>";
                    $connect->query("UPDATE `price` SET `synchronized` = '1' WHERE `id` = ?i",$price['id']);
                }
            }
            else {
                echo $res['msg'].": ".$price['id'].'<br>';
                print_r($res['fail_messages']);
            }
        }*/	
    }			
}

$client = new \GuzzleHttp\Client(['verify' => false]);

//$prices = $connect->getAll("SELECT `id`, `id_room`, `price`, `id_range`, `active` FROM `price` WHERE `synchronized` = 0 AND ".$pricesYearWhere." LIMIT 5000");
$prices = $connect->getAll("SELECT `id`, `id_room`, `price`, `id_range`, `active` FROM `price` WHERE `synchronized` = 0 LIMIT 5000");

//if ($session_login==75) {
    //синхронизация цен по новому - пачками
    $i=0;
    $priceAr = [];
    $priceAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
    $priceAr['id'] = 1;	
    $priceAr['uid'] = 1;	
    //$priceAr['data'] = [];
    foreach ($prices as $price) { 
        if ($i==0) $priceAr['data'] = [];
        $priceData = [];
        $priceData['id'] = $price['id'];
        $priceData['room_id'] = $price['id_room'];
        $priceData['value'] = (float)$price['price'];
        $priceData['range_id'] = $price['id_range'];
        $priceData['status'] = (int)(!$price['active']);				
        $priceAr['data'][] = $priceData;
        $i++;
        if ($i>=50) {
            $start = time();
            echo 'start timestamp='.$start.'<br>';
            SyncPricesPack2($client, $connect, $priceAr);
            $end = time();
            echo 'start timestamp='.$end.'<br>';
            echo 'between='.($end - $start).'<br>';
            $i=0;
        }
    }
    SyncPricesPack($client, $connect, $priceAr);
?>