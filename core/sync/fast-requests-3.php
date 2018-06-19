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
$conf = new JConfig;
$unisender_api_key = $conf->unisender_api_key;
$connect = connect_to_MySQL_directory();
$token = "d9954a2ef753a0a688cd0dd07ceda98b83d6eb803731a633e9e16967fe767e00";


$client = new GuzzleHttp\Client();

$last_time = NULL;

if(file_exists($directory."/core/sync/file/fast-time-3.txt")) {
  $last_time = (int)file_get_contents($directory."/core/sync/file/fast-time-3.txt");
}

if(is_null($last_time) || time() > $last_time + 60) {
  file_put_contents($directory."/core/sync/file/fast-time-3.txt", time());
  for($i = 0; $i <2000; $i++) {

    if(!$connect) {
      echo 'Database connection exception';
      break;
    }

    try {
      $res = $client->request('POST',"https://sync.tonia.ru/api/request/list/4n",[
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
                $respAr['result'] = $request['action']($connect,json_decode(base64_decode($request['data']),true));
                $respAr['success'] = 1;
              }
              catch (Exception $e) {
                echo $e->getMessage();
                break 2;
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
              break 2;
            }

          }
        }
      }

    }
    catch (Exception $e) {
      echo $e->getMessage();
      break;
    }

    if(!file_exists($directory."/core/sync/file/fast-kill.txt")){
      break;
    }

  }
}