<?php
// ini_set("display_errors",1);
// error_reporting(E_ALL);
use GuzzleHttp\Client;

require_once __DIR__."/../../vendor/autoload.php";
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

	$configNew = \App\lib\CRM\Config\Client::getInstance();
	$configNew->connect = $connect;
	$configNew->directory = $directory;
	$configNew->clientCabinet = $clientCabinet;
	$configNew->objectCabinet = $objectCabinet;

	$create_client = new CreateClient;


	if(!$connect)
		return;

	$client = new GuzzleHttp\Client(['verify' => false]);
	$token = "7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4";

	try {
		$res = $client->request('POST', "https://sync2.tonia.ru/api/booking/list/10" . '?cache=' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 1, 15), [
			'form_params' => [
				'token' => $token
			]
		]);
		$data = json_decode($res->getBody(),true);
		if(is_array($data) && $data['success']) {
			$data = $data['bookings'];
		}
		else {
			$data = [];
		}
	}
	catch (Exception $e) {
		$data = [];
	}

	$delete = array();

	foreach($data as $booking){
		$data_booking = json_decode(base64_decode($booking["data"]));
		$data_booking_JSON = json_decode(base64_decode($booking["data"]), TRUE);
		$delete[] = $booking["id"];
		$function = "";
		if(isset($data_booking_JSON["func"]))
			$function = $data_booking_JSON["func"];

		foreach($data_booking_JSON as $index => $value){
			if(is_string($value)){
				$value = trim($value);
				$value = strip_tags($value);
			}elseif(is_array($value)){
				foreach($value as $ind => $value_array){
					if(is_string($value_array)){
						$value[$ind] = trim($value_array);
						$value[$ind] = strip_tags($value_array);
					}
				}
			}
			$data_booking->$index = $value;
			$data_booking_JSON[$index] = $value;
		}
		if($function == "booking_object"){
			$booking_source = "";
			$website = isset($data_booking->site)?$data_booking->site:"";
			$id_obj = isset($data_booking->id_obj)?$data_booking->id_obj:0;
			$email = isset($data_booking_JSON["email"])?$data_booking_JSON["email"]:"";
			$state_program = isset($data_booking_JSON["state_program"]) ? (int)$data_booking_JSON['state_program'] : 0;

			if($state_program) {
				$state_program = 1;
			}

			if(isset($data_booking->source))
				$booking_source = $data_booking->source;

			$client_info = array(
				"surname" => isset($data_booking_JSON["sur"])?$data_booking_JSON["sur"]:"",
				"name" => isset($data_booking_JSON["name"])?$data_booking_JSON["name"]:"",
				"otch" => isset($data_booking_JSON["otch"])?$data_booking_JSON["otch"]:"",
				"telephone" => isset($data_booking_JSON["tel"])?$data_booking_JSON["tel"]:"",
				"email" => $email,
				"ip" => isset($data_booking_JSON["ip"])?$data_booking_JSON["ip"]:""
			);

			if(isset($data_booking_JSON['sex'])) {
        $client_info['sex'] = $data_booking_JSON['sex'];
      }

			if(isset($gsok[$id_obj]))
				$id_obj = 96;

			$today = date("Y-m-d");
			$hash = md5(uniqid());
			$days = isset($data_booking->days)?(int)$data_booking->days:1;
			$date_z = isset($data_booking_JSON["date"])?date_change($data_booking_JSON["date"], "-", "."):date_change($today, "-", ".");
			$reward = get_reward_object($connect, $id_obj, $date_z);

			$source = select_index_source($booking_source);

			$last_id = $create_client->create_client($client_info);

			save_client_to_history($connect, $last_id, "Создание клиента");
			$note_booking = isset($data_booking->note)?trim($data_booking->note):"";

			$connect->query("INSERT INTO reckoning(date, turist, id_obj, rest, hash, website, source, form_booking, note) VALUES (?s, ?i, ?i, ?i, ?s, ?s, ?i, 'module',?s)", $today, $last_id, $id_obj, $last_id, $hash, $website, $source, $note_booking);
			$id = $connect->insertId();
			$id_tour = $connect->getOne("SELECT id_tour FROM object WHERE id=?i", $id_obj);
			if($id_tour)
				$connect->query("UPDATE reckoning SET id_tour=?i WHERE id=?i", $id_tour, $id);
			$add_one_day = $connect->getOne("SELECT add_one_day FROM object WHERE id=?i", $id_obj);

			$check_quota = 0;

			if(isset($data_booking->position) && $data_booking->position){
				$positions = json_decode($data_booking->position, TRUE);
				foreach($positions as $position){
					$type_index = 1;
					if(isset($position["type_index"]) AND $position["type_index"] > 1){
						$type_index = (int)$position["type_index"];
						if($type_index == 3)
							$type_index = 2;
					}
					$id_room = isset($position["id_room"])?(int)$position["id_room"]:0;
					$note = isset($position["place"])?$position["place"]:"";
					$price = isset($position["price"])?(float)$position["price"]:0;
					$number = isset($position["number"])?(int)$position["number"]:1;
					$connect->query("INSERT INTO position_reck(id_room, schet, days, date_z, number, sum, type, note, reward, add_one_day) VALUES (?i, ?i, ?i, ?s, ?s, ?s, ?i, ?s, ?s, ?i)", $id_room, $id, $days, $date_z, $number, $price, $type_index, $note, $reward, (int)$add_one_day);
					if(isset($position["ratePlan"]) AND $position["ratePlan"] > 0){
						if($connect->getOne("SELECT id FROM object WHERE id=?i AND (check_places=1 OR check_places=2)", $id_obj)){
							$check_quota = 1;
							$insert = $connect->insertId();
							$connect->query("UPDATE position_reck SET ratePlan=?i WHERE id=?i", $position["ratePlan"], $insert);
						}
					}
				}
				if($check_quota == 1){
					$connect->query("INSERT INTO booking(bid, from_booking) VALUES (?i, 'site')", $id);
					$connect->query("UPDATE reckoning SET form_booking='quota' WHERE id=?i", $id);
				}
			}else{
				$connect->query("INSERT INTO position_reck(schet, id_room, days, date_z, number, type, reward, add_one_day) VALUES (?i, 0, ?i, ?s, 1, 1, ?s, ?i)", $id, $days, $date_z, $reward, (int)$add_one_day);
				$connect->query("UPDATE reckoning SET note=?s, form_booking='default-form' WHERE id=?i", $note_booking, $id);
			}

			$connect->query("UPDATE reckoning SET number_turist=?i WHERE id=?i", $connect->getOne("SELECT COUNT(*) FROM position_reck WHERE schet=?i", $id), $id);

			change_arrival_date($connect, $id);
			recalculation_sum($connect, $id);
			save_schet_to_history($connect, $id, "Новая заявка от клиента");

			if($state_program)
				$connect->query("UPDATE reckoning SET state_program=1 WHERE id=?i", $id);

			if(isset($data_booking->promo_code) && $data_booking->promo_code != ""){
				$promo_code = mb_strtolower($data_booking->promo_code);
				$itog = $connect->getOne("SELECT sum FROM reckoning WHERE id=?i", $id);
				$bonus = check_promotional_code($promo_code, $id_obj, $itog, array("arrival" => $date_z, "days" => $days), $last_id, $connect);
				if(!is_array($bonus) && $bonus){
					$connect->query("INSERT INTO bonus(date, turist, sum, type, note, promocode) VALUES (?s, ?i, ?s, 3, ?s, ?s)", $today, $last_id, $bonus, "Подарочный бонус", $promo_code);
					$connect->query("INSERT INTO bonus(date, schet, turist, sum, cause) VALUES (?s, ?i, ?i, ?i, 1)", $today, $id, $last_id, $bonus * (-1));
					$connect->query("UPDATE reckoning SET promo_code=?s WHERE id=?i", $promo_code, $id);
          $connect->query("INSERT INTO promo_code_using(`promo_code`, `client_id`, `reck_id`, `timestamp`) VALUES (?s, ?i, ?i, ?i)", $promo_code, $last_id, $id, gmdate("U"));
					save_schet_to_history($connect, $id, "Использование промокода");
				}
				elseif(is_array($bonus)) {
          save_schet_to_history($connect, $id, $bonus['msg']);
					$connect->query("UPDATE reckoning SET promo_code=?s WHERE id=?i", $promo_code, $id);
        }
			}

			echo " Client ID = ".$last_id." ";

			$config = ConfigCRM::getInstance();
			$config->booking = $id;
			$config->turist = $last_id;
			$config->connect = $connect;

			$configNew = \App\lib\CRM\Config\Client::getInstance();
			$configNew->connect = $connect;
			$configNew->directory = $directory;
			$configNew->clientCabinet = $clientCabinet;
			$configNew->objectCabinet = $objectCabinet;

			$send = new SendMailTurist;
      $send->send_login();

			$telephone = clear_telephone($connect->getOne("SELECT telephone FROM klient WHERE id=?i", $last_id));
	    if($telephone){
	      $id_obj = $connect->getOne("SELECT id_obj FROM reckoning WHERE id=?i", $id);
	      $object = get_object($connect, $id_obj, "type");
	      $text = "Заявка №".$id." в ".$object." принята в обработку. 8-800-600-16-20 Санатории-России.рф";
	      send_sms($connect, $telephone, $booking, $text, "new-booking");
	    }

		}elseif($function == "send_call_back"){
		  echo PHP_EOL.$function.PHP_EOL;
			$booking_source = "";
			$user_remote_id = 0;
			$form_id = 0;

			if(isset($data_booking_JSON['user_remote_id']))
				$user_remote_id = (int)$data_booking_JSON['user_remote_id'];

			if(isset($data_booking_JSON['form_id']))
				$form_id = (int)$data_booking_JSON['form_id'];

			$client_info = array(
				"surname" => isset($data_booking_JSON["surname"])?$data_booking_JSON["surname"]:"",
				"name" => isset($data_booking_JSON["name"])?$data_booking_JSON["name"]:"",
				"otch" => "",
				"telephone" => isset($data_booking_JSON["telephone"])?$data_booking_JSON["telephone"]:"",
				"email" => "",
				"ip" => isset($data_booking_JSON["ip"])?$data_booking_JSON["ip"]:""
			);

			$fio = "";
			if(mb_strlen($client_info["surname"]) > 0)
				$fio .= $client_info["surname"];

			if(mb_strlen($client_info["name"]) > 0) {
        if(mb_strlen($fio) > 0)
          $fio .= " ";

        $fio .= $client_info["name"];
      }

      if(mb_strlen($client_info["otch"]) > 0) {
        if(mb_strlen($fio) > 0)
          $fio .= " ";

        $fio .= $client_info["otch"];
      }

			if(isset($data_booking_JSON["otch"])) {
				$client_info["otch"] = $data_booking_JSON["otch"];
			}

			if(isset($data_booking_JSON["email"]))
				$client_info["email"] = $data_booking_JSON["email"];

			$website = $data_booking_JSON["website"];
			$id = "";
			if(isset($data_booking_JSON["id"])){
				$id = $data_booking_JSON["id"];
			}
			$question = $data_booking_JSON["question"];
			$arrival = isset($data_booking_JSON["arrival"])?$data_booking_JSON["arrival"]:"";
			$days = isset($data_booking_JSON["days"])?(int)$data_booking_JSON["days"]:1;
			$type = isset($data_booking_JSON["type"])?$data_booking_JSON["type"]:"";
			$page = isset($data_booking_JSON["page"])?$data_booking_JSON["page"]:"";
			$object = isset($data_booking_JSON["object"])?(int)$data_booking_JSON["object"]:0;
			$chat_id = NULL;

			$callback_time = $data_booking_JSON['callback_time']?$data_booking_JSON['callback_time']:'';


			//if(isset($data_booking_JSON["chat_id"]))
				//$chat_id = (int)$data_booking_JSON["chat_id"];

			if(isset($gsok[$object]))
				$object = 96;

			if(isset($data_booking_JSON["source"]))
				$booking_source = $data_booking_JSON["source"];
			if($id){
				$object = $connect->getOne("SELECT id_obj FROM promotions WHERE id=?i", $id);
			}

			if(!$type)
				$type = "site";

			$source = select_index_source($booking_source);

			if(!$arrival || !$object){

				$address = get_address_by_ip($client_info["ip"]);

				if($callback_time)
					$connect->query("INSERT INTO order_call_back(website, turist, telephone, question, address, type, source, href, chat_id, user_remote_id, form_id, callback_time) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i, ?i)", $website, $fio, $client_info["telephone"], $question, $address, $type, $source, $page, $chat_id, $user_remote_id,$form_id, strtotime($callback_time));
				else
					$connect->query("INSERT INTO order_call_back(website, turist, telephone, question, address, type, source, href, chat_id, user_remote_id, form_id) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?i, ?i)", $website, $fio, $client_info["telephone"], $question, $address, $type, $source, $page, $chat_id, $user_remote_id,$form_id);


				if($id){
					$last = $connect->insertId();
					$connect->query("UPDATE order_call_back SET type='module', promo=?i WHERE id=?i", $id, $last);
				}

			}else{

				$today = date("Y-m-d");
				$arrival = date_change($arrival, "-", ".");
				$row = $connect->getRow("SELECT id_tour, add_one_day FROM object WHERE id=?i", $object);
				$reward = get_reward_object($connect, $object, $arrival);
				$tour = $row["id_tour"];
				$add_one_day = $row["add_one_day"];

				if($id){
					$row = $connect->getRow("SELECT title, text FROM promotions WHERE id=?i", $id);
					$question = "Вопрос по акции ".$row["title"]." ".$row["text"]."\n".$question;
				}

				$client = $create_client->create_client($client_info);

				$connect->query("INSERT INTO reckoning(date, turist, id_obj, rest, website, source, form_booking, note) VALUES (?s, ?i, ?i, ?i, ?s, ?i, 'website-call-back', ?s)", $today, $client, $object, $client, $website, $source, $question);
				$id = $connect->insertId();
				$connect->query("INSERT INTO position_reck(schet, id_room, days, date_z, number, type, reward, add_one_day) VALUES (?i, 0, ?i, ?s, 1, 1, ?s, ?i)", $id, $days, $arrival, $reward, (int)$add_one_day);
				if($tour)
					$connect->query("UPDATE reckoning SET id_tour=?i WHERE id=?i", $tour, $id);
				change_arrival_date($connect, $id);
				recalculation_sum($connect, $id);
				save_schet_to_history($connect, $id, "Новая заявка от клиента");

			}

		}elseif($function == "send_request_object"){

			$object = $data_booking->object;
			$address = $data_booking->address;
			$telephone = $data_booking->telephone;
			$email = $data_booking->email;
			$site_object = $data_booking->site_object;
			$website = $data_booking->website;
			$source = $data_booking->source;
			$comment = $data_booking->comment;

			$connect->query("INSERT INTO object_request(website, object, telephone, email, address, website_object, comment, source) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)", $website, $object, $telephone, $email, $address, $site_object, $comment, $source);

		}elseif($function == "send_comment_rating"){

			$website = $data_booking->website;
			$rating = $data_booking->rating;
			$name = $data_booking->name;
			$email = $data_booking->email;
			$text = $data_booking->text;

			if(!filter_var($email, FILTER_VALIDATE_EMAIL))
				$email = "";

			if($text AND $rating){
				$connect->query("INSERT INTO rating_comment(rating, name, email, text, website) VALUES (?i, ?s, ?s, ?s, ?s)", $rating, $name, $email, $text, $website);
			}

		}elseif($function == "booking_object_module"){

			$data = $data_booking_JSON["data"];
			$module = $data["module"];
			$booking = new CreateRequestBookingModule($module);
			$booking->create_new_booking($data);
			unset($booking);

		}elseif($function == "booking_object_agency_module"){

			$surname = $data_booking->surname;
			$name = $data_booking->name;
			$otch = $data_booking->otch;
			$telephone = $data_booking->telephone;
			$email = $data_booking->email;
			if(!filter_var($email, FILTER_VALIDATE_EMAIL))
				$email = "";

			$object = $data_booking->object;
			$date2 = explode(".", $data_booking->arrival);
			$arrival = $date2[2]."-".$date2[1]."-".$date2[0];
			$days = $data_booking->days;
			$position = $data_booking->position;
			$agency_module = $data_booking->agency;

			$row = $connect->getRow("SELECT id, module_email FROM agency WHERE module=?s", $agency_module);
			if(isset($row["id"]) AND $row["id"] > 0){
				$agency = $row["id"];
				$email = $row["module_email"];
				$index = $connect->getOne("SELECT COUNT(*) FROM booking_agency WHERE agency=?i", $agency) + 1;
				$connect->query("INSERT INTO booking_agency(agency, count, surname, name, otch, telephone, object, email, arrival, days, position) VALUES(?i, ?i, ?s, ?s, ?s, ?s, ?i, ?s, ?s, ?i, ?s)", $agency, $index, $surname, $name, $otch, $telephone, $object, $email, $arrival, $days, $position);
				$insert_id = $connect->insertId();

				$object = get_object($connect, $object, "type");
				$message = select_template_letter("agency/module/new-reservation-module", "agency", $index);
				$content = str_replace("<object>", $object, $message["content"]);
				$title = str_replace("<id>", $index, $message["title"]);
				$connect->query("INSERT INTO send_mail(email, title, body) VALUES (?s, ?s, ?s)", $email, $title, $content);
			}

		}

	}

	if($delete) {
		$client = new GuzzleHttp\Client(['verify' => false]);

		$res = $client->request('POST', "https://sync2.tonia.ru/api/booking/deactivate" . '?cache=' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 1, 15), [
			'form_params' => [
				'token' => $token,
				'ids' => $delete
			]
		]);
	}

?>
