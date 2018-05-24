<?php

function send_sms($connect, $phone, $bid = NULL, $text, $type, $check_old = false){
	global $unisender_api_key;
	$check = ($check_old && $connect->getOne("SELECT id FROM send_sms WHERE phone=?s AND type=?s", $phone, $type));
	if(mb_substr($phone,0,1) === '+')
		$phone = mb_substr($phone,1);

  if(!$check AND mb_strlen($phone) == 11){
		$POST = array(
			"api_key" => $unisender_api_key,
			"phone" => $phone,
			"sender" => "Sanata",
			"text" => $text
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_URL, "https://api.unisender.com/ru/api/sendSms?format=json");
		$result = curl_exec($ch);
		$result = json_decode($result, TRUE);
		if($result["result"]["sms_id"]){
			$id_api = $result["result"]["sms_id"];
			$spend = $result["result"]["price"];
			$connect->query("INSERT INTO send_sms(phone, bid, type, id_api, spend) VALUES (?s, ?i, ?s, ?s, ?s)", $phone, $bid, $type, $id_api, $spend);
		}
	}
}

?>
