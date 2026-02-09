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

$connect->query("INSERT 1_ya_form_log SET `id`=0, `datetime`=NOW(), `data`=?s", $_POST['data']);

$data = json_decode($_POST['data'], true);
$cmnt = 'ЗАЯВКА С ЯНДЕКС ФОРМЫ<br>';
$cmnt .= 'Регион: '.$data['answer']['data']['answer_short_text_59666938']['value'].'<br>';
$cmnt .= 'Цель: '.$data['answer']['data']['answer_choices_59659035']['value'][0]['text'].'<br>';
$cmnt .= 'Профиль: '.$data['answer']['data']['answer_choices_59659370']['value'][0]['text'].'<br>';
$cmnt .= 'Цена: '.$data['answer']['data']['answer_choices_59658680']['value'][0]['text'].'<br>';
$cmnt .= 'Дата: '.$data['answer']['data']['answer_choices_59666247']['value'][0]['text'].'<br>';
$cmnt .= 'Водоём: '.$data['answer']['data']['answer_choices_59666734']['value'][0]['text'].'<br>';
$cmnt .= 'Имя отчество: '.$data['answer']['data']['answer_short_text_59643772']['value'].'<br>';
$cmnt .= 'Телефон: '.$data['answer']['data']['answer_phone_59643876']['value'].'<br>';
$cmnt .= 'E-mail: '.$data['answer']['data']['answer_non_profile_email_59643896']['value'].'<br>';
$cmnt .= 'Способ связи: '.$data['answer']['data']['answer_choices_59666785']['value'][0]['text'].'<br>';
$cmnt .= 'Комментарий: '.$data['answer']['data']['answer_long_text_59644358']['value'].'<br>';

$create_client = new CreateClient;

$client_info = array(
	"surname" => "",
	"name" => isset($data['answer']['data']['answer_short_text_59643772']['value'])?$data['answer']['data']['answer_short_text_59643772']['value']:"",
	"otch" => "",
	"telephone" => isset($data['answer']['data']['answer_phone_59643876']['value'])?$data['answer']['data']['answer_phone_59643876']['value']:"",
	"email" => isset($data['answer']['data']['answer_non_profile_email_59643896']['value'])?$data['answer']['data']['answer_non_profile_email_59643896']['value']['value']:"",
	"ip" => ""
);


$user_id = $create_client->create_client($client_info);

$exist_reckoning = $connect->getRow("SELECT * FROM reckoning WHERE turist=?i AND status=1 and `date`>NOW() - INTERVAL 1 DAY ORDER BY id DESC LIMIT 1", $user_id);
//$check_query = $connect->last_query();
if ($exist_reckoning) {
	$connect->query(
		"UPDATE `reckoning` SET `note`=?s WHERE id=?i",
		($exist_reckoning['cmnt'] . '<br><br>' . $cmnt),
		$exist_reckoning['id']
	);
	//$update_query = $connect->last_query();
} else {
	$connect->query("INSERT INTO `reckoning` (`id`, `type`, `date`, `sum`, `count_payment`, `count_holding`, `manager`, `turist`, `id_user`, `active`, `status`, `holding`, `holding_sum`, `holding_cancelled_sum`, `holding_confirmed_sum`, `rest`, `prepay`, `id_obj`, `date_z`, `status_san`, `number_turist`, `payer`, `agency`, `id_com`, `note`, `id_dis`, `id_services`, `id_tour`, `status_agent`, `schet_san`, `date_schet_san`, `date_v`, `hash`, `website`, `website_from`, `reason_delete`, `changes`, `guaranteed`, `doc_schet_san`, `reward`, `note_bid`, `correction`, `commission_value`, `source`, `promo_code`, `form_booking`, `exclude_bank_commission`, `bank_com_auto_excluded`, `state_program`, `is_test`, `children_rest`, `far_east`, `bnovo`, `bnovo_json`, `afl`, `afl_worked`) 
                                  VALUES ('0', '0', '".date('Y-m-d')."', NULL, '0', '0', NULL, '$user_id', NULL, '0', '1', '0', '0.00', '0.00', '0.00', '$user_id', '0.00', '0', NULL, '0', '1', NULL, NULL, NULL, ?s, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '20', NULL, NULL, '0', '0', '0', '0', '0', '0', '0', '\'\'', '', '0');", $cmnt);
}


$response['ok'] = 1;
$response['sql'] = $connect->last_query();

echo json_encode($response);




?>