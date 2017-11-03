<?php
$directory = dirname(__FILE__)."/../..";
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

	if(!$connect)
		return;

	$data = request_to_sync(array("func" => "get_new_agency_list"));
	$delete = array();

	foreach($data as $agency){
		$delete[] = $agency['id'];
	}

	$data = request_to_sync(array("func" => "delete_agency_events", "id" => json_encode($delete)));
?>