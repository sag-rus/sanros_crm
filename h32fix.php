<?php
use GuzzleHttp\Client;

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


$items = $connect->getAll("SELECT * FROM `sites_contents` WHERE `body` LIKE '%<h3%' AND `body` NOT LIKE '%<h2%'");
foreach($items as $item) {
    
    if (mb_strpos($item['body'], '<h3')!==FALSE && mb_strpos($item['body'], '<h2')===FALSE) {
        $item['body'] = str_replace('<h3', '<h2', $item['body']);
        $item['body'] = str_replace('</h3', '</h2', $item['body']);

        $item['body'] = str_replace('<h4', '<h3', $item['body']);
        $item['body'] = str_replace('</h4', '</h3', $item['body']);        

        echo 'Изменены заголовки на странице: https://санатории-россии.рф'.$item['path'].'<br>';
    }

}



?>