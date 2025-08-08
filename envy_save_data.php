<?php
use GuzzleHttp\Client;

header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');


$log = print_r($_POST, true).PHP_EOL;
file_put_contents('bnovo_save_matches.txt', $log, FILE_APPEND);


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

$connect->query("INSERT 1_envy_log SET `id`=0, `datetime`=NOW(), `data`=?s", $_POST['data']);

parse_str($_POST['data'], $data);

$cmnt = 'ЗАЯВКА С ФОРМЫ ENVYBO<br>';
$cmnt .= 'Имя отчество: '.$data['name'].'<br>';
$cmnt .= 'Телефон: '.$data['phone'].'<br>';
$cmnt .= 'E-mail: '.$data['email'].'<br>';
$cmnt = 'Регион посетителя: '.$data['place'].'<br>';
$cmnt .= 'IP: '.$data['ip'].'<br>';

$create_client = new CreateClient;

$client_info = array(
	"surname" => "",
	"name" => isset($data['name'])?$data['name']:"",
	"otch" => "",
	"telephone" => isset($data['phone'])?$data['phone']:"",
	"email" => isset($data['email'])?$data['email']:"",
	"ip" => ""
);


$user_id = $create_client->create_client($client_info);

$connect->query("INSERT INTO `reckoning` (`id`, `type`, `date`, `sum`, `count_payment`, `count_holding`, `manager`, `turist`, `id_user`, `active`, `status`, `holding`, `holding_sum`, `holding_cancelled_sum`, `holding_confirmed_sum`, `rest`, `prepay`, `id_obj`, `date_z`, `status_san`, `number_turist`, `payer`, `agency`, `id_com`, `note`, `id_dis`, `id_services`, `id_tour`, `status_agent`, `schet_san`, `date_schet_san`, `date_v`, `hash`, `website`, `website_from`, `reason_delete`, `changes`, `guaranteed`, `doc_schet_san`, `reward`, `note_bid`, `correction`, `commission_value`, `source`, `promo_code`, `form_booking`, `exclude_bank_commission`, `bank_com_auto_excluded`, `state_program`, `is_test`, `children_rest`, `far_east`, `bnovo`, `bnovo_json`, `afl`, `afl_worked`) 
                                  VALUES ('0', '0', '".date('Y-m-d')."', NULL, '0', '0', NULL, '$user_id', NULL, '0', '1', '0', '0.00', '0.00', '0.00', NULL, '0.00', '0', '".date('Y-m-d', time() + 86400)."', '0', '1', NULL, NULL, NULL, ?s, NULL, NULL, NULL, '0', NULL, NULL, '".date('Y-m-d', time() + 86400*2)."', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, '0', '0', '0', '0', '0', '0', '0', '\'\'', '', '0');", $cmnt);




$response['ok'] = 1;
//$response['sql'] = $connect->last_query();

echo json_encode($response);




?>