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
		$agency_post = json_decode(base64_decode($agency["data"]), TRUE);
		$agency_post = $agency_post['data'];

		if(isset($agency_post['agency']))
			$name = trim(str_replace("plus", "+", $agency_post["agency"]));
		else
			$name = "";

		if(isset($agency_post['short_agency']))
			$short_name = trim(str_replace ('"', "", $agency_post["short_agency"]));
		else 
			$short_name = "";

		if(isset($agency_post['present']))
			$present = trim(str_replace ('"', "", $agency_post["present"]));
		else 
			$present = "";

		if(isset($agency_post['present_short']))
			$present_short = trim(str_replace ('"', "", $agency_post["present_short"]));
		else 
			$present_short = "";

		if(isset($agency_post['post']))
			$post = trim(str_replace ('"', "", $agency_post["post"]));
		else 
			$post = "";

		if(isset($agency_post['post_short']))
			$post_short = trim(str_replace ('"', "", $agency_post["post_short"]));
		else 
			$post_short = "";

		if(isset($agency_post['doc']))
			$doc = trim(str_replace ('"', "", $agency_post["doc"]));
		else 
			$doc = "";

		if(isset($agency_post['telephone']))
			$telephone = trim($agency_post["telephone"]);
		else
			$telephone = "";

		if(isset($agency_post['email']))
			$email = trim($agency_post["email"]);
		else
			$email = "";
		
		if(isset($agency_post['fax']))
			$fax = trim($agency_post["fax"]);
		else 
			$fax = "";

		if(isset($agency_post['icq']))
			$icq = trim($agency_post["icq"]);
		else
			$icq = "";

		if(isset($agency_post['skype']))
			$skype = trim($agency_post["skype"]);
		else
			$skype = "";

		if(isset($agency_post['note_a']))
			$note = trim($agency_post["note_a"]);
		else 
			$note = "";

		if(isset($agency_post['address']))
			$address = trim($agency_post["address"]);
		else
			$address = "";

		if(isset($agency_post['ur_address']))
			$legal_address = trim($agency_post["ur_address"]);
		else
			$legal_address = "";

		if(isset($agency_post['inn']))
			$inn = trim($agency_post["inn"]);
		else
			$inn = "";


		if(isset($agency_post['kpp']))
			$kpp = trim($agency_post["kpp"]);
		else
			$kpp = "";


		if(isset($agency_post['bik']))
			$bik = trim($agency_post["bik"]);
		else
			$bik = "";

		if(isset($agency_post['rs']))
			$rs = trim($agency_post["rs"]);
		else
			$rs = "";

		if(isset($agency_post['bank']))
			$bank = trim($agency_post["bank"]);
		else
			$bank = "";


		if(isset($agency_post['ks']))
			$ks = trim($agency_post["ks"]);
		else
			$ks = "";


		if(isset($agency_post['ogrn']))
			$ogrn = trim($agency_post["ogrn"]);
		else
			$ogrn = "";

		if(isset($agency_post['website']))
			$website = trim($agency_post["website"]);
		else
			$website = "";
		

		if(mb_strlen($name) > 0 && mb_strlen($short_name) > 0 && mb_strlen($present) > 0 && mb_strlen($present_short) > 0 && mb_strlen($post) > 0 && mb_strlen($post_short) > 0 && mb_strlen($doc) > 0 && mb_strlen($address) > 0 && mb_strlen($legal_address) > 0 && mb_strlen($inn) > 0 && mb_strlen($kpp) > 0 && mb_strlen($bik) > 0 && mb_strlen($rs) > 0 && mb_strlen($ks) > 0 && mb_strlen($ogrn) > 0 && mb_strlen($telephone) > 0 && mb_strlen($email) > 0) {
			$module = gen_password(rand(6, 8));

			while($connect->getOne("SELECT id FROM agency WHERE module=?s LIMIT 1", $module))
				$module = gen_password(rand(6, 8));

			$connect->query("INSERT INTO agency(name, short_name, present, telephone, email, fax, icq, skype, note, address, website, legal_address, inn, kpp, bik, rs, ks, bank, post, doc, module, module_email, created) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)", $name, $short_name, $present, $telephone, $email, $fax, $icq, $skype, $note, $address, $website, $legal_address, $inn, $kpp, $bik, $rs, $ks, $bank, $post, $doc, $module, $email, gmdate("U"));
			$id = $connect->insertId();
		}

		$delete[] = $agency['id'];
	}

	$data = request_to_sync(array("func" => "delete_agency_events", "id" => json_encode($delete)));
?>