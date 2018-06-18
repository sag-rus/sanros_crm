<?php
require_once "../../vendor/autoload.php";
date_default_timezone_set("Asia/Baghdad");
$directory = dirname(__FILE__)."/../..";
define("_FOLDERSITE_", $directory);
include_once($directory."/core/sync/API/client.php");
include_once($directory."/core/functions.php");
include_once($directory."/core/lib/sms.php");
include_once($directory."/core/lib/Mysql.Class.php");
include_once($directory."/config.php");
$conf = new JConfig;
$unisender_api_key = $conf->unisender_api_key;
$connect = connect_to_MySQL_directory();
$token = "d9954a2ef753a0a688cd0dd07ceda98b83d6eb803731a633e9e16967fe767e00";


$client = new GuzzleHttp\Client();

$last_time = NULL;

if(file_exists($directory."/core/sync/file/fast-time.txt")) {
  $last_time = (int)file_get_contents($directory."/core/sync/file/fast-time.txt");
}

if(is_null($last_time) || time() > $last_time + 60) {
  file_put_contents($directory."/core/sync/file/fast-time.txt", time());
  for($i = 0; $i <1000; $i++) {

    if(!$connect)
      break;

    try {
      $res = $client->request('POST',"https://sync.tonia.ru/api/request/list",[
        'form_params' => [
          'token' => $token
        ]
      ]);

      if($res->getStatusCode() === 200) {
        $res = json_decode($res->getBody(),true);
        if(isset($res['requests']) && is_array($res['requests'])) {
          $respAr = [
            'success' => 0,
            'title' => '',
            'msg' => ''
          ];
          foreach ($res['requests'] as $id => $request) {
            if(function_exists($request['action'])) {
              $respAr['success'] = 1;
              try {
                $respAr['result'] = $request['action']($connect,$request['data']);
              }
              catch (Exception $e) {
                break 2;
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
                break 2;
              }
            }
            else {
              $respAr['msg'] = "Action's method not exists";
              $respAr['title'] = 'Error';
            }
          }
        }
      }

    }
    catch (Exception $e) {
      break;
    }

    if(!file_exists($directory."/core/sync/file/fast-kill.txt")){
      break;
    }

  }
}