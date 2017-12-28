<?php

function authorization_object_account($connect, $data){
	$object = array();
	$login = trim($data["login"]);
	$pass = $data["pass"];
	$true_pass = $connect->getOne("SELECT password FROM object_account WHERE login=?s", $login);
	if($pass == $true_pass AND $true_pass){
		$session = md5(uniqid());
		$account = $connect->getOne("SELECT id FROM session_object WHERE login=?s", $login);
		if($account){
			$connect->query("UPDATE session_object SET id_session=?s WHERE login=?s", $session, $login);
		}else{
			$connect->query("INSERT INTO session_object(login, id_session) VALUES (?s, ?s)", $login, $session);
		}
		$account = $connect->getOne("SELECT id FROM object_account WHERE login=?s", $login);

		$data = $connect->getAll("SELECT id FROM object WHERE id_account=?i", $account);
		foreach($data as $row){
			$id = $row["id"];
			$object[$id] = array();
			$object[$id]["name"] = 1;
		}
		$array = array(
			"session" => $session,
			"object" => $object
		);
		$config = ConfigCRM::getInstance();
		$config->account = $account;
		save_history_object("Авторизация в ЛК");
		return $array;
	}
}

function update_password_object_account($connect, $data){
	$old = $data["old"];
	$new = $data["new"];
	$login = $connect->getOne("SELECT login FROM session_object WHERE id_session=?s", $data["session"]);
	$account = $connect->getOne("SELECT id FROM object_account WHERE login=?s AND password=?s", $login, $old);
	if($account){
		$connect->query("UPDATE object_account SET password=?s WHERE id=?i", $new, $account);
		$config = ConfigCRM::getInstance();
		$config->account = $account;
		save_history_object("Изменение пароля");
		return 1;
	}
	return 2;
}

function update_contact_object_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$address = $data["address"];
		$fax = $data["fax"];
		$website = $data["website"];
		$connect->query("UPDATE object SET address=?s, fax=?s, website=?s WHERE id=?i", $address, $fax, $website, $object);
		save_history_object("Изменение контактов");
	}
	return FALSE;
}

function update_treatment_object_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$method = $data["method"];
		$profile = $data["profile"];
		$connect->query("UPDATE object SET id_methods=?s, id_profile=?s, status=2 WHERE id=?i", $method, $profile, $object);
		save_history_object("Изменение описания лечения");
	}
	return FALSE;
}

function update_infrastructure_object_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$infrastructure = $data["infrastructure"];
		$connect->query("UPDATE object SET id_infa=?s, status=2 WHERE id=?i", $infrastructure, $object);
		save_history_object("Изменение инфраструктуры");
	}
	return FALSE;
}

function update_description_object_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$description = $data["description"];
		$connect->query("UPDATE object SET description_check=?s, status=2 WHERE id=?i", $description, $object);
		save_history_object("Изменение текстового описания");
	}
	return FALSE;
}

function update_services_object_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$service = $data["service"];
		$connect->query("UPDATE object SET id_services=?s, status=2 WHERE id=?i", $service, $object);
		save_history_object("Изменение услуг");
	}
	return FALSE;
}

function update_map_marker_object_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$latitude = $data["latitude"];
		$longitude = $data["longitude"];
		$connect->query("UPDATE object SET latitude=?s, longitude=?s, status=2 WHERE id=?i", $latitude, $longitude, $object);
		save_history_object("Изменение координат");
	}
	return FALSE;
}

function save_new_category_housing_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$name = $data["name"];
		if($name){
			$connect->query("INSERT INTO housing(name, id_obj) VALUES (?s, ?i)", $name, $object);
			$insert = $connect->insertId();
			#$connect->query("UPDATE object SET status=2 WHERE id=?i", $object);
			save_history_object("Добавление нового корпуса");
			return $insert;
		}
	}
	return FALSE;
}

function update_category_housing_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$housing = $data["id"];
		$connect->query("UPDATE housing SET name=?s WHERE id=?i AND id_obj=?i", $data["name"], $housing, $object);
		save_history_object("Изменение корпуса ".$data["name"]);
	}
}

function remove_category_housing_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$housing = $data["id"];
		$connect->query("DELETE FROM housing WHERE id=?i", $housing);
		save_history_object("Удаление корпуса " . $data["name"] . " из ЛК объекта");
	}
}

function save_new_category_room_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$name = $data["name"];
		if($name){
			$connect->query("INSERT INTO room(name, id_obj) VALUES (?s, ?i)", $name, $object);
			$insert = $connect->insertId();
			$connect->query("UPDATE object SET status=2 WHERE id=?i", $object);
			save_history_object("Добавление новой категории");
			return $insert;
		}
	}
	return FALSE;
}

function update_category_room_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$room = $data["id"];
		$connect->query("UPDATE room SET name=?s, housing=?s, main_place=?i, add_place=?i, square=?s, food=?s, note=?s, id_comfort=?s, id_best_comfort=?s, number=?i, description=?s WHERE id=?i AND id_obj=?i", $data["name"], $data["housing"], $data["main"], $data["add"], $data["square"], $data["food"], $data["note"], $data["comf"], $data["best"], (int)$data["num"], $data["description"], $room, $object);
		$connect->query("UPDATE object SET status=2 WHERE id=?i", $object);
		save_history_object("Изменение категории ".$data["name"]);
	}
}

function remove_category_room_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$room = $data["id"];
		$connect->query("DELETE FROM room WHERE id=?i", $room);
		save_history_object("Удаление номера " . $data["name"] . " из ЛК объекта");
	}
}

function save_price_object_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$parse_price = json_decode($data["data"], TRUE);
    $date_last_save = date('d.m.Y H:m:s');
		foreach($parse_price as $rate_plan){
			foreach($rate_plan as $id_room => $room){
				foreach($room as $id_range => $value){
					$id_price = $connect->getOne("SELECT id FROM price WHERE id_room=?i AND id_range=?i", $id_room, $id_range);
					if($id_price AND $value > 0)
						$connect->query("UPDATE price SET active=0, price=?s WHERE id=?i", (int)$value, $id_price);
					elseif($id_price)
						$connect->query("DELETE FROM price WHERE id=?i", $id_price);
					elseif($value > 0)
						$connect->query("INSERT INTO price(id_room, id_range, price, date_last_save) VALUES (?i, ?i, ?s, ?s)", $id_room, $id_range, (int)$value, $date_last_save);
				}
			}
		}
		$connect->query("UPDATE object SET status=2 WHERE id=?i", $object);
		save_history_object("Сохранение цен");
	}
}

function save_new_date_price_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$start = $data["start"];
		$end = $data["end"];
		if($start AND $end){
			$connect->query("INSERT INTO date_price(id_obj, start, end) VALUES (?i, ?s, ?s)", $object, $start, $end);
			$insert = $connect->insertId();
			$connect->query("UPDATE object SET status=2 WHERE id=?i", $object);
			save_history_object("Добавление нового интервала цен");
			return $insert;
		}
	}
	return FALSE;
}

function update_date_price_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$id_date = $data["id"];
		$object = $data["object"];
		$start = $data["start"];
		$end = $data["end"];
		$connect->query("UPDATE date_price SET start=?s, end=?s WHERE id=?i AND id_obj=?i", $start, $end, $id_date, $object);
		$connect->query("UPDATE object SET status=2 WHERE id=?i", $object);
		save_history_object("Изменение интервала цен");
	}
	return FALSE;
}

function save_new_range_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$id_date = $data["id"];
		$object = $data["object"];
		$name = $data["name"];
		$place = $data["place"];
		$type = $data["type"];
		$rate_plan = $data["rate_plan"];
		$connect->query("INSERT INTO ranges(name, id_obj, type, place, id_date, rate_plan) VALUES (?s, ?i, ?i, ?i, ?i, ?i)", $name, $object, $type, $place, $id_date, $rate_plan);
		$insert = $connect->insertId();
		$connect->query("UPDATE object SET status=2 WHERE id=?i", $object);
		save_history_object("Добавление нового столбца цен");
		return $insert;
	}
}

function update_range_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$id_range = $data["id"];
		$object = $data["object"];
		$name = $data["name"];
		$place = $data["place"];
		$type = $data["type"];
		$rate_plan = $data["rate_plan"];
		$connect->query("UPDATE ranges SET name=?s, type=?i, place=?i, rate_plan=?i WHERE id=?i AND id_obj=?i", $name, $type, $place, $rate_plan, $id_range, $object);
		$connect->query("UPDATE object SET status=2 WHERE id=?i", $object);
		save_history_object("Добавление столбца цен");
	}
}

function show_questions_object($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$config = ConfigCRM::getInstance();
    $account = $config->account;
		$answer = array("check" => 1);
		$data = $connect->getAll("SELECT id, category FROM talk WHERE client=?i AND type='object'", $account);
		foreach($data as $row){
			$talk = $row["id"];
			$answer["talk"][$talk]["category"] = $connect->getOne("SELECT name FROM question_category WHERE id=?i", $row["category"]);
			$answer["talk"][$talk]["count"] = $connect->getOne("SELECT COUNT(*) FROM message_talk WHERE talk=?i", $talk);
		}
		return $answer;
	}
	return FALSE;
}

function show_talk_object($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$config = ConfigCRM::getInstance();
    $account = $config->account;
		$talk = $data["id"];
		if(!$connect->getOne("SELECT id FROM talk WHERE id=?i AND client=?i AND type='object'", $talk, $account))
			return FALSE;
		$answer = array("check" => 1);
		$category = $connect->getOne("SELECT category FROM talk WHERE id=?i", $talk);
		$answer["category"] = $connect->getOne("SELECT name FROM question_category WHERE id=?i", $category);
		$array = $connect->getAll("SELECT id, DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date, text, type, user FROM message_talk WHERE talk=?i ORDER BY id", $talk);
		foreach($array as $message){
			$answer["message"][$message["id"]]["date"] = $message["date"];
			$answer["message"][$message["id"]]["text"] = $message["text"];
			$answer["message"][$message["id"]]["type"] = $message["type"];
			if($message["user"])
				$answer["message"][$message["id"]]["manager"] = $connect->getOne("SELECT name FROM users WHERE id=?i", $message["user"]);
		}
		$connect->query("UPDATE message_talk SET active=1 WHERE talk=?i AND type='manager'", $talk);
		return $answer;
	}
	return FALSE;
}

function send_question_object($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$config = ConfigCRM::getInstance();
    $account = $config->account;
		$message = $data["text"];
		$category = $data["category"];
		if($message){
			$talk = $connect->getOne("SELECT id FROM talk WHERE id=?i AND client=?i AND type='object'", $data["talk"], $account);
			if(!$talk){
				$connect->query("INSERT INTO talk(client, category, type) VALUES(?i, ?i, 'object')", $account, $category);
				$talk = $connect->insertId();
			}
			$connect->query("INSERT INTO message_talk(talk, text, type) VALUES(?i, ?s, 'client')", $talk, $message);
		}
	}
}

function open_quota_object($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$connect->query("UPDATE object SET check_places=2 WHERE id=?i", $object);
		return 1;
	}
}

function save_new_rate_plan_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$name = $data["name"];
		if($name){
			$connect->query("INSERT INTO rate_plan(name, object, description) VALUES (?s, ?i, '')", $name, $object);
			$insert = $connect->insertId();
			$connect->query("UPDATE object SET status=2 WHERE id=?i", $object);
			save_history_object("Добавление нового тарифного плана");
			return $insert;
		}
	}
	return FALSE;
}

function update_rate_plan_account($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$ratePlan = $data["id"];
		$object = $data["object"];
		$connect->query("UPDATE rate_plan SET name=?s, food=?s, description=?s, days=?i WHERE id=?i", $data["name"], $data["food"], $data["desc"], $data["days"], $data["id"]);
		$connect->query("UPDATE object SET status=2 WHERE id=?i", $object);
		save_history_object("Изменение тарифного плана ".$data["name"]);
	}
}

function create_booking_module_object($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$uniq_key = $data["uniq_key"];
		$check = $connect->getOne("SELECT id FROM booking_module_object WHERE object=?i", $object);
		if(!$check){
			$today = date("Y-m-d");
			$connect->query("INSERT INTO booking_module_object(object, uniq_key, date_create) VALUES(?i, ?s, ?s)", $object, $uniq_key, $today);
			$module = $connect->insertId();
			$connect->query("UPDATE object SET status=2 WHERE id=?i", $object);
			save_history_object("Создание модуля бронирования на сайт");
			return $module;
		}
	}
}

function update_booking_module_object($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$object = $data["object"];
		$telephone = $data["telephone"];
		$email = $data["email"];
		$website = $data["website"];
		$payment_methods = $data["payment_methods"];
		$show_rooms = $data["show_rooms"];
		$add_one_day = $data["add-one-day"];
		$prepay = $data["prepay"];
		$connect->query("UPDATE booking_module_object SET telephone=?s, email=?s, website=?s, payment_methods=?s, show_rooms=?i, prepay=?i WHERE object=?i", $telephone, $email, $website, $payment_methods, $show_rooms, $prepay, $object);
		$connect->query("UPDATE object SET add_one_day=?i, status=2 WHERE id=?i", $add_one_day, $object);
		save_history_object("Изменение модуля бронирования на сайт");
	}
}

function show_booking_requests_object(){
	if(CheckAuthObjectCabinet::check_authorization()){
		$select = new DisplayRequestBookingModule;
		$requests = $select->select_requests_booking();
		return $requests;
	}
}

function show_booking_request_object($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization_booking()){
		$select = new DisplayRequestBookingModule;
		$request = $select->select_request_booking();
		return $request;
	}
}

function save_new_position_booking($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization_booking()){
		$data = $data["data"];
		$edit = new EditRequestBookingModule();
		$edit->add_new_position($data);
		$select = new DisplayRequestBookingModule;
		$request = $select->select_request_booking();
		return $request;
	}
}

function update_position_booking($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization_booking()){
		$data = $data["data"];
		$edit = new EditRequestBookingModule();
		$edit->update_position($data);
		$select = new DisplayRequestBookingModule;
		$request = $select->select_request_booking();
		return $request;
	}
}

function delete_position_booking($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization_booking()){
		$data = $data["data"];
		$edit = new EditRequestBookingModule();
		$edit->delete_position($data);
		$select = new DisplayRequestBookingModule;
		$request = $select->select_request_booking();
		return $request;
	}
}

function save_new_turist_booking($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization_booking()){
		$data = $data["data"];
		$edit = new EditRequestBookingModule;
		$edit->add_new_turist($data);
		$select = new DisplayRequestBookingModule;
		$request = $select->select_request_booking();
		return $request;
	}
}

function update_turist_booking($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization_booking()){
		$data = $data["data"];
		$edit = new EditRequestBookingModule;
		$edit->update_turist($data);
		$select = new DisplayRequestBookingModule;
		$request = $select->select_request_booking();
		return $request;
	}
}

function delete_turist_booking($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization_booking()){
		$data = $data["data"];
		$edit = new EditRequestBookingModule;
		$edit->delete_turist($data);
		$select = new DisplayRequestBookingModule;
		$request = $select->select_request_booking();
		return $request;
	}
}

function change_status_booking($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization_booking()){
		$data = $data["data"];
		$edit = new StatusRequestBookingModule;
		$edit->change_status($data);
		$select = new DisplayRequestBookingModule;
		$request = $select->select_request_booking();
		return $request;
	}
}

function change_payment_booking($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization_booking()){
		$data = $data["data"];
		$bid_pay = $data["bid"];
		$payment = new BookingModuleObjectPayment;
		if($data["status"] == "accept"){
			$request = $payment->deposit_payment($bid_pay);
		}elseif($data["status"] == "cancel"){
			$request = $payment->cancel_payment($bid_pay);
		}
		unset($payment);
		$select = new DisplayRequestBookingModule;
		$request = $select->select_request_booking();
		return $request;
	}
}

function create_comparison_price_module_object($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$create = new ComparisonObject;
		$return = $create->create();
		return $return;
	}
}

function append_object_comparison_price($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$competitor = $data["competitor"];
		$edit = new EditComparisonObject;
		$return = $edit->append_competitor($data["competitor"]);
		return $return;
	}
}

function remove_object_comparison_price($connect, $data) {
	if(CheckAuthObjectCabinet::check_authorization()){
    $competitor = $data["competitor"];
    $edit = new EditComparisonObject;
    $return = $edit->remove_competitors($data["competitor"]);
    return $return;
	}
}

function object_comparison_contract_request($connect, $data) {
	if(CheckAuthObjectCabinet::check_authorization()){
		$info = [
			'rate' => (int)$data['rate'],
			'month' => (int)$data['month'],
      'date' => date("U")
		];
    $edit = new EditComparisonObject;
    $edit->update([
    	'changed_status' => 1,
    	'contract_request_info' => json_encode($info)
		]);
    $edit->addPaymentInvoice($info['rate'], $info['month'], $info['date']);

	}

	return [
		'success' => true
	];
}

function set_default_room_comparison_price($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$update = array(
			"default_room" => (int)$data["room"]
		);
		$edit = new EditComparisonObject;
		$edit->update($update);
	}
}

function set_default_room_object_comparison_price($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$competitor = $data["competitor"];
		$room = $data["room"];
		$edit = new EditComparisonObject;
		$return = $edit->update_competitor($competitor, $room);
		return $return;
	}
}

function display_invoice_payment_comparison_price($connect, $data){
	if(CheckAuthObjectCabinet::check_authorization()){
		$rate_index = $data["rate"];
		$month = $data["month"];
		$array = array(
			"company" => array(),
			"product" => array()
		);
		$company = new CompanyInfo;
		$array["company"] = $company->get_info();
		$comparison = new ComparisonObject;
		$rate = $comparison->select_rate_index($rate_index, $month);
		$array["product"]["name"] = "Оплата услуги «Сравнение цен конкурентов» по тарифу «".$rate["name"]."»";
		$array["product"]["month"] = $rate["month"];
		$array["product"]["price"] = $rate["price"];
		$array["product"]["payer"] = get_object($connect, $data["object"], "full_and_place");
		$array["product"]["bid"] = $data["object"];

    $info = [
      'rate' => (int)$data['rate'],
      'month' => (int)$data['month'],
			'date' => date("U")
    ];
    $edit = new EditComparisonObject;
    $edit->update([
      'changed_status' => 1,
      'contract_request_info' => json_encode($info)
    ]);

    $module_id = $this->connect->getOne("SELECT id FROM comparison_module_object WHERE object=?i", $data["object"]);
    $this->connect->query("INSERT INTO comparison_module_payment_invoice(module_id, rate, month, date, status) VALUES(?i, ?i, ?i, ?i, ?i)", $module_id, $info['rate'], $info['month'], $info['date'], 1);
		return $array;
	}
}

function save_history_object($text){
	$config = ConfigCRM::getInstance();
	$connect = $config->connect;
	$account = $config->account;
	if($account)
		$connect->query("INSERT INTO history_object(object, text) VALUES(?i, ?s)", $account, $text);
}

?>
