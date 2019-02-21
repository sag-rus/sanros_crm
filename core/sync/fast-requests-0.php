<?php
require_once __DIR__."/../../vendor/autoload.php";
date_default_timezone_set("Asia/Baghdad");
$directory = dirname(__FILE__)."/../..";
define("_FOLDERSITE_", $directory);

include_once($directory."/core/sync/API/client.php");
include_once($directory."/core/functions.php");
include_once($directory."/core/lib/sms.php");
include_once($directory."/core/lib/Mysql.Class.php");
include_once($directory."/config.php");

include_once(_FOLDERSITE_."/core/sync/API/client.php");
include_once(_FOLDERSITE_."/core/sync/API/agency.php");
include_once(_FOLDERSITE_."/core/sync/API/object.php");
include_once(_FOLDERSITE_."/core/sync/API/payment.php");
include_once(_FOLDERSITE_."/core/sync/API/sitehelp.php");
include_once(_FOLDERSITE_."/core/sync/API/travelline.php");

include_once(_FOLDERSITE_."/core/functions.php");
include_once(_FOLDERSITE_."/core/lib/mail.php");
include_once(_FOLDERSITE_."/core/lib/sms.php");
include_once(_FOLDERSITE_."/core/lib/Mysql.Class.php");
include_once(_FOLDERSITE_."/config.php");
$conf = new JConfig;
$sync = $conf->sync_base;
$CRM = $conf->CRM;
$unisender_api_key = $conf->unisender_api_key;
$connect = connect_to_MySQL_directory();


define("DEFAULT_OBJECT_IMAGE", "http://tonia.ru/price/object/head/default.jpg");
$COLORS = array("success" => "#CAFFC3", "cancel" => "#FFD3C5", "info" => "#D0DDFF", "waiting" => "#E7C97C");

$CHAT_GROUP = array(
  1 => array("name" => "Оператор бронирования", "icon" => "fa-user-plus"),
  2 => array("name" => "Тех.поддержка", "icon" => "fa-cogs")
);
$CHAT_GROUP_AGENCY = array(
  1 => array("name" => "Оператор бронирования", "icon" => "fa-user-plus"),
  2 => array("name" => "Тех.поддержка", "icon" => "fa-cogs")
);
$CHAT_GROUP_CLIENT = array(
  1 => array("name" => "Оператор бронирования", "icon" => "fa-user-plus"),
  2 => array("name" => "Тех.поддержка", "icon" => "fa-cogs")
);
$CHAT_GROUP_OBJECT = array(
  1 => array("name" => "Оператор бронирования", "icon" => "fa-user-plus"),
  2 => array("name" => "Тех.поддержка", "icon" => "fa-cogs")
);


$onlinePaymentInfo = array(
  "link" => $conf->BANK_PAYMENT_LINK,
  "commission" => $conf->BANK_COM,
  "userName" => $conf->USERNAME_ALFA,
  "password" => $conf->PASSWORD_ALFA
);

$onlinePaymentInfoSber = array(
  "link" => $conf->BANK_PAYMENT_LINK_SBERBANK,
  "commission" => $conf->BANK_COM_SBERBANK,
  "userName" => $conf->USERNAME_SBERBANK,
  "password" => $conf->PASSWORD_SBERBANK
);

$clientCabinet = array(
  "link" => $conf->turist_cabinet
);
$contactInfo = array(
  "free-line" => $conf->linia
);
$objectCabinet = array(
  "link" => $conf->object_cabinet
);
$bonus = array(
  "bonus-booking" => $conf->bonus_rec,
  "bonus-affiliate" => $conf->bonus_ref
);

$config = ConfigCRM::getInstance();
$config->connect = $connect;
$config->onlinePaymentInfo = $onlinePaymentInfo;
$config->clientCabinet = $clientCabinet;
$config->objectCabinet = $objectCabinet;
$config->contactInfo = $contactInfo;
$config->bonus = $bonus;
$config->mail = $conf->email_module;
$config->directory = $directory;

$configNew = \App\lib\CRM\Config\Client::getInstance();

$configNew->connect = $connect;
$configNew->onlinePaymentInfo = $onlinePaymentInfoSber;
$configNew->clientCabinet = $clientCabinet;
$configNew->objectCabinet = $objectCabinet;
$configNew->contactInfo = $contactInfo;
$configNew->bonus = $bonus;
$configNew->mail = $conf->email_module;
$configNew->directory = $directory;

//define("CABINET", $clientCabinet);
define("CABINET", "http://xn----7sba6aaba8akdsdekah.xn--p1ai/client/");

$token = "d9954a2ef753a0a688cd0dd07ceda98b83d6eb803731a633e9e16967fe767e00";

$client = new GuzzleHttp\Client();

$last_time = NULL;

if(file_exists($directory."/core/sync/file/fast-time-0.txt")) {
  $last_time = (int)file_get_contents($directory."/core/sync/file/fast-time-0.txt");
}

if(is_null($last_time) || time() > $last_time + 60) {
  while(1) {
    file_put_contents($directory."/core/sync/file/fast-time-0.txt", time());
    if(!$connect) {
      echo 'Database connection exception';
      break;
    }

    try {
      $res = $client->request('POST',"https://sync.tonia.ru/api/request/list/0",[
        'form_params' => [
          'token' => $token
        ]
      ]);

      if($res->getStatusCode() === 200) {
        $res = json_decode($res->getBody(),true);
        if(isset($res['requests']) && is_array($res['requests'])) {
          foreach ($res['requests'] as $id => $request) {

            $respAr = [
              'title' => '',
              'msg' => '',
              'success' => ''
            ];

            if(function_exists($request['action'])) {
              try {
                $config = ConfigCRM::getInstance();
                $configNew = App\lib\CRM\Config\Client::getInstance();
                $requestData = json_decode(base64_decode($request['data']),true);
                if(isset($requestData["session"])) {
                  $config->session = $requestData["session"];
                  $configNew->session = $requestData["session"];
                }

                if(isset($requestData["object"])) {
                  $config->object = $requestData["object"];
                  $configNew->object = $requestData["object"];
                }

                if(isset($requestData["booking"])) {
                  $config->booking = $requestData["booking"];
                  $configNew->booking = $requestData["booking"];
                }
                $respAr['result'] = $request['action']($connect,$requestData,true);
                $respAr['success'] = 1;
              }
              catch (Exception $e) {
                file_put_contents($directory."/core/sync/file/fast-requests-error.log",$e->getMessage().PHP_EOL,FILE_APPEND);
                //break 2;
              }
            }
            else {
              $respAr['msg'] = "Action's method not exists";
              $respAr['title'] = 'Error';
            }

            try {
              $res = $client->request('POST',"https://sync.tonia.ru/api/request/answer/set/".$id,[
                'form_params' => [
                  'token' => $token,
                  'answer' => $respAr
                ]
              ]);
            }
            catch (Exception $e) {
              file_put_contents($directory."/core/sync/file/fast-requests-error.log",$e->getMessage().PHP_EOL,FILE_APPEND);
              break 2;
            }

          }
        }
      }

    }
    catch (Exception $e) {
      file_put_contents($directory."/core/sync/file/fast-requests-error.log",$e->getMessage().PHP_EOL,FILE_APPEND);
      break;
    }


    if(!file_exists($directory."/core/sync/file/fast-kill.txt")){
      break;
    }

  }
}