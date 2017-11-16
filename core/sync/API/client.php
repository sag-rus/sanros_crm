<?php


function save_source_booking_data($connect, $data) {
	$surname = $data["surname"];
  $name = $data["name"];
  $otch = $data["otch"];
  $telephone = trim($data["telephone"]);
  $email = trim($data["email"]);
  $today = date("Y-m-d");
  $object_id = $data['object_id'];

  $sex = null;
  if(isset($data['sex'])) {
    $sex = (int)$data['sex'];
    if($sex !== 0 && $sex !== 1) {
      $sex = null;
    }
  }

  if(mb_strlen($telephone) > 0) {
    $id = $connect->getOne("SELECT id FROM klient WHERE login=?s OR email=?s OR telephone=?s LIMIT 1", $email, $email, $telephone);
  }
  else {
    $id = $connect->getOne("SELECT id FROM klient WHERE login=?s OR email=?s LIMIT 1", $email, $email);
  }

  if(!$id) {
    $original_data = [
      'surname' => $surname,
      'name' => $name,
      'otch' => $otch,
      'telephone' => $telephone,
      'email' => $email,
      'date_reg' => $today,
			'source_booking_object' => $object_id,
			'sex' => $sex
    ];

    if(is_null($sex))
    	$connect->query("INSERT INTO klient(surname, name, otch, telephone, email, date_reg, source_booking_object, original_data) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?i, ?s)", $surname, $name, $otch, $telephone, $email, $today, $object_id, json_encode($original_data));
    else
      $connect->query("INSERT INTO klient(surname, name, otch, sex, telephone, email, date_reg, source_booking_object, original_data) VALUES (?s, ?s, ?s, ?i, ?s, ?s, ?s, ?i, ?s)", $surname, $name, $otch, $sex, $telephone, $email, $today, $object_id, json_encode($original_data));

    $id = $connect->insertId();
    if($id > 0) {
      save_client_to_history($connect, $id, "Добавлен новый клиент через форму перехода к бронированию на сайте объекта");
      return $id;
    }
    else
    	return 0;
	}
	else return $id;
}

function register_new_account($connect, $data){
	$surname = $data["surname"];
	$name = $data["name"];
	$otch = $data["otch"];
	$date = $data["date"];
	$telephone = $data["telephone"];
	$email = trim($data["email"]);
	$password = $data["password"];
	$invited = $data["invited"];
  $sex = null;
  if(isset($data['sex'])) {
    $sex = (int)$data['sex'];
    if($sex !== 0 && $sex !== 1) {
      $sex = null;
    }
  }

	$count = $connect->getOne("SELECT id FROM klient WHERE login=?s LIMIT 1", $email);
	if(!$count AND $email != ""){
		$account = $connect->getOne("SELECT id FROM klient WHERE email=?s AND (login='' OR login IS NULL) LIMIT 1", $email);
		$today = date("Y-m-d");
		if($account) {
			$connect->query("UPDATE klient SET login=?s, password=?s, date_reg=?s, date=?s WHERE id=?i", $email, $password, $today, $date, $account);
    }
		else{
      $original_data = [
        'surname' => $surname,
        'name' => $name,
        'otch' => $otch,
        'telephone' => $telephone,
        'email' => $email,
				'login' => $email,
				'password' => $password,
				'date_reg' => $today,
				'date' => $date,
				'sex' => $sex
      ];

      if(is_null($sex))
      	$connect->query("INSERT INTO klient(surname, name, otch, telephone, email, login, password, date_reg, date, original_data) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)", $surname, $name, $otch, $telephone, $email, $email, $password, $today, $date, json_encode($original_data));
      else
        $connect->query("INSERT INTO klient(surname, name, otch, sex, telephone, email, login, password, date_reg, date, original_data) VALUES (?s, ?s, ?s, ?i, ?s, ?s, ?s, ?s, ?s, ?s, ?s)", $surname, $name, $otch, $sex, $telephone, $email, $email, $password, $today, $date, json_encode($original_data));

      $account = $connect->insertId();
		}
		save_client_to_history($connect, $account, "Регистрация нового аккаутна");
		if($invited == "birthday"){
			$bonus = 300;
			$connect->query("INSERT INTO bonus(turist, type, sum, date, note) VALUES(?i, 3, ?i, ?s, 'Подарочный бонус на день рождения')", $account, $bonus, $today);
		}elseif($invited != ""){
			$id_invited = $connect->getOne("SELECT id FROM klient WHERE hash=?s", $invited);
			$connect->query("UPDATE klient SET invited=?i WHERE id=?i LIMIT 1", $id_invited, $account);
			//Бонус при регистрации
			$bonus = 300;
			$connect->query("INSERT INTO bonus(turist, type, sum, date, note) VALUES(?i, 3, ?i, ?s, 'Подарочный бонус за регистрацию')", $account, $bonus, $today);
		}

		$hash = uniqid();
		$connect->query("UPDATE klient SET hash=?s WHERE id=?i", $hash, $account);
		$message = select_template_letter("turist/cabinet/new-account", "client", $id);
		$link = CABINET."?func=activation&email=".$email."&hash=".$hash;
		$message["content"] = str_replace("<hash>", $link, $message["content"]);
		send_mail($email, $message["title"], $message["content"]);
		return $account;
	}
	return FALSE;
}

function authorization_account($connect, $data){
	$login = $data["login"];
	$pass = $data["pass"];
	$row = $connect->getRow("SELECT id, password, active, favorites, hash, name, surname, otch, photo FROM klient WHERE login=?s", $login);
	$id = $row["id"];
	$true_pass = $row["password"];
	$activate = $row["active"];
	$favorites = json_decode($row["favorites"], TRUE);
	if($pass == $true_pass AND $true_pass){
		if($activate == 1){
			$no_pay = array();
			$no_rating = array();
			$name = convert_name($row["name"]);
			$surname = convert_name($row["surname"]);
			$otch = convert_name($row["otch"]);
			$hash = $row["hash"];
			$photo = $row["photo"];
			$session = md5(uniqid());
			if($connect->getOne("SELECT id FROM session_account WHERE login=?s", $login))
				$connect->query("UPDATE session_account SET id_session=?s WHERE login=?s", $session, $login);
			else
				$connect->query("INSERT INTO session_account(login, id_session) VALUES (?s, ?s)", $login, $session);

			$data = $connect->getAll("SELECT id FROM reckoning WHERE (status=3 OR status=4) AND turist=?i", $id);
			foreach($data as $row){
				$no_pay[] = $row["id"];
			}

			$data = $connect->getAll("SELECT id FROM reckoning WHERE status=5 AND date_v<?s AND turist=?i", date("Y-m-d"), $id);
			foreach($data as $row){
				if(!$connect->getOne("SELECT id FROM rating WHERE (status=3 OR status=2) AND schet=?i", $row["id"]))
					$no_rating[] = $row["id"];
			}

			$array = array("session" => $session, "status" => 1, "id" => $id, "name" => $name, "surname" => $surname, "otch" => $otch, "photo" => $photo, "favorites" => $favorites, "hash" => $hash, "no-pay" => $no_pay, "no-rating" => $no_rating);
			$array["bonus"] = all_klient_bonus($connect, $id);
			save_client_to_history($connect, $id, "Авторизация в Личном кабинете");
		}else
			$array = array("status" => 2);
		return $array;
	}
	return FALSE;
}

function send_activation_email($connect, $data){
	global $directory;
	$email = $data["email"];
	if($connect->getOne("SELECT id FROM klient WHERE login=?s AND active=0", $email)){
		$hash = uniqid();
		$connect->query("UPDATE klient SET hash=?s WHERE login=?s LIMIT 1", $hash, $email);
		$message = select_template_letter("turist/cabinet/new-account", "client");
		$link = CABINET."?func=activation&email=".$email."&hash=".$hash;
		$message["content"] = str_replace("<hash>", $link, $message["content"]);
		send_mail($email, $message["title"], $message["content"]);
		return 1;
	}elseif($connect->getOne("SELECT id FROM klient WHERE login=?s AND active=1", $email))
		return 2;
	return 0;
}

function send_recovery_email($connect, $data){
	global $directory;
	$email = $data["email"];
	$id = $connect->getOne("SELECT id FROM klient WHERE login=?s", $email);
	if($id){
		$hash = md5(uniqid("", TRUE));
		$connect->query("UPDATE klient SET recovery=?s WHERE login=?s LIMIT 1", $hash, $email);
		$message = select_template_letter("turist/cabinet/recovery-account", "client");
		$link = CABINET."восстановить-пароль/email=".$email."&hash=".$hash;
		$message["content"] = str_replace("<hash>", $link, $message["content"]);
		save_client_to_history($connect, $id, "Отправка письма с восстановлением пароля");
		send_mail($email, $message["title"], $message["content"]);
		return 1;
	}
	return FALSE;
}

function recovery_password_account($connect, $data){
	$email = $data["email"];
	$hash = $data["hash"];
	$pass = $data["new"];
	if($email AND $hash AND $pass){
		$id = $connect->getOne("SELECT id FROM klient WHERE (login=?s AND login!='') AND (recovery=?s AND recovery!='')", $email, $hash);
		if($id){
			$connect->query("UPDATE klient SET recovery='', password=?s, active=1 WHERE id=?i", $pass, $id);
			save_client_to_history($connect, $id, "Восстановил пароль");
			return 1;
		}
	}
	return FALSE;
}

function activation_account($connect, $data){
	$email = $data["email"];
	$hash = $data["hash"];
	$row = $connect->getRow("SELECT id, active FROM klient WHERE login=?s AND hash=?s", $email, $hash);
	$client = $row["id"];
	$active = $row["active"];
	if($client){
		if($active == 0){
			$connect->query("UPDATE klient SET active=1 WHERE id=?i", $client);
			save_client_to_history($connect, $client, "Активация аккаунта");
		}
		return 1;
	}
	return FALSE;
}

function personal_page_account($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization()){
		$array = array();
		$config = ConfigCRM::getInstance();
    $client = $config->account;
		$today = date("Y-m-d");
		$answer = $connect->getRow("SELECT surname, name, otch, date, telephone, favorites, photo FROM klient WHERE id=?i LIMIT 1", $client);
		$array["surname"] = $answer["surname"];
		$array["name"] = $answer["name"];
		$array["bonus"] = all_klient_bonus($connect, $client);
		$array["trip"] = 0;
		$array["future_trip"] = 0;
		$array["day_rest"] = 0;
		$array["photo"] = "images/NoPicture.jpg";
		$array["refferal"] = $connect->getOne("SELECT COUNT(*) FROM klient WHERE invited=?i", $client);
		if($answer["photo"])
			$array["photo"] = "data:image/jpg;base64,".$answer["photo"];
		$favorites = array();
		if($answer["favorites"]){
			$favorites = json_decode($answer["favorites"]);
		}
		foreach($favorites as $id_obj => $f){
			$row = $connect->getRow("SELECT city, id_reg, url_name FROM object WHERE id=?i", $id_obj);
			$array["favorites"][$id_obj]["object"] = get_object($connect, $id_obj, "type");
			$array["favorites"][$id_obj]["address"] = $connect->getOne("SELECT name FROM region WHERE id=?i", $row["id_reg"]);
			if($row["city"])
				$array["favorites"][$id_obj]["address"].= ", ".$row["city"];
			$array["favorites"][$id_obj]["object-link"] = $row["url_name"];
		}
		$answer = $connect->getAll("SELECT id, id_obj FROM reckoning WHERE turist=?i AND status=3", $client);
		foreach($answer as $row){
			$array["reminder_bill"][$row["id"]]["object"] = get_object($connect, $row["id_obj"], "place");
		}
		$answer = $connect->getAll("SELECT id, id_obj, date_z, date_v FROM reckoning WHERE turist=?i AND status=5", $client);
		foreach($answer as $row){
			$id = $row["id"];
			$date_z = $row["date_z"];
			$date_v = $row["date_v"];
			$id_obj = $row["id_obj"];
			$object = get_object($connect, $id_obj, "type");
			if(time() > strToTime($date_z)){
				$type = "been";
				$array["trip"]++;
				$array["day_rest"]+= $connect->getOne("SELECT days FROM position_reck WHERE schet=?i ORDER BY days LIMIT 1", $id);
				if(!$connect->getOne("SELECT id FROM rating WHERE schet=?i", $id) OR $connect->getOne("SELECT id FROM rating WHERE schet=?i AND status=0", $id))
					$array["been"][$id]["rating"] = "no";
			}else{
				$type = "future";
				$array["future_trip"]++;
				$array["reminder_payment"][$id]["object"] = $object;
			}
			$row = $connect->getRow("SELECT city, id_reg, url_name FROM object WHERE id=?i", $id_obj);
			$array[$type][$id]["id_obj"] = $id_obj;
			$array[$type][$id]["object"] = $object;
			$array[$type][$id]["object-link"] = $row["url_name"];
			$array[$type][$id]["dates"] = date_change($date_z, ".")." - ".date_change($date_v, ".");
			$array[$type][$id]["address"] = $connect->getOne("SELECT name FROM region WHERE id=?i", $row["id_reg"]);
			if($row["city"])
				$array[$type][$id]["address"].= ", ".$row["city"];
		}
		return $array;
	}
	return FALSE;
}

function get_photo_account($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$photo = $connect->getOne("SELECT photo FROM klient WHERE login=?s", $login);
	if($photo AND $login)
		return $photo;
}

function save_photo_account($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization()){
		$photo = $data["photo"];
		save_client_to_history($connect, $client, "Изменил аватарку");
		$connect->query("UPDATE klient SET photo=?s WHERE id=?i", $photo, $client);
	}
}

function edit_account(){
	if(CheckAuthTuristCabinet::check_authorization()){
		$answer = array();
		$client = new DisplayClient;
		$answer = $client->select_fio_array();
		$answer+= $client->select_contact();
		$answer+= $client->select_info();
		return $answer;
	}
	return FALSE;
}

function update_account_client($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$row = $connect->getRow("SELECT id, telephone, date FROM klient WHERE login=?s", $login);
	$client = $row["id"];
	$telephone = $row["telephone"];
	$date = $row["date"];
	if($login AND $client){
		$connect->query("UPDATE klient SET telephone=?s, address=?s WHERE id=?i", $data["telephone"], $data["address"], $client);
		if($data["date"]){
			$date_old = date_change($connect->getOne("SELECT date FROM klient WHERE id=?i", $client));
			if(!$date_old)
				$connect->query("UPDATE klient SET date=?s WHERE id=?i", $data["date"], $client);
		}
		$note_update = "Изменил свои данные из Личного кабинета;";
		if($data["telephone"] != $telephone)
			$note_update.= " Изменен телефон «".$telephone."» ; ";
		if($data["date"] AND $data["date"] != $date)
			$note_update.= " Изменена дата рождения «".$date."» ; ";
		save_client_to_history($connect, $client, $note_update);
	}
}

function show_tours_account($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization()){
		$array = array(
			"check" => 1
		);
		$select = new DisplayBookingTurist;
		$array["tour"] = $select->display_booking_turist();
		unset($select);
		$select = new DisplayRequestBookingModule;
		$array["tour-object"] = $select->select_requests_booking();
		unset($select);
		return $array;
	}
	return FALSE;
}

function show_tour_bid_account($connect, $data){
	$id = $data["id"];
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($login AND $client)
		$true = $connect->getOne("SELECT id FROM reckoning WHERE id=?i AND turist=?i AND active!=3 LIMIT 1", $id, $client);
	if($true == $id){
		$array = array("check" => 1);
		$answer = $connect->getRow("SELECT id, id_obj, date_z, date_v, sum, status, status_san, rest, changes, id_user, id_dis FROM reckoning WHERE id=?i LIMIT 1", $id);
		$rest = $answer["rest"];
		$array["id"] = $answer["id"];
		$array["object"] = get_object($connect, $answer["id_obj"], "type");
		$array["date_z"] = month_transform(date_change($answer["date_z"]));
		$array["date_v"] = month_transform(date_change($answer["date_v"]));
		$array["sum"] = $answer["sum"];
		$array["changes"] = $answer["changes"];
		$array["status"] = $connect->getOne("SELECT name FROM status WHERE id=?i", $answer["status"]);
		$array["bonus"] = ABS($connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum<0", $id));
		$array["manager"] = "";
		if($answer["id_user"]){
			$manager = $connect->getRow("SELECT name, photo FROM users WHERE id=?i", $answer["id_user"]);
			$array["manager"] = $manager["name"];
			if($manager["photo"])
				$array["manager-photo"] = "data:image/jpg;base64,".$manager["photo"];
		}
		if($answer["id_dis"]){
			$array["discount"] = $connect->getOne("SELECT value FROM discount WHERE id=?i", $answer["id_dis"]);
			$array["sum-discount"] = ($array["discount"] / 100) * $answer["sum"];
		}
		$array["doc"] = 0;
		if($answer["status"] == 3 OR $answer["status"] == 4){
			$array["doc"] = 2;
			$reward = get_reward_schet($connect, $id);
			if(($reward / $answer["sum"] * 100) >= 4 OR $id == 43125){
				$array["pay_button"] = 1;
				$check = $connect->getOne("SELECT sum FROM time_payment WHERE type=2 AND id_schet=?i", $id);
				if($check AND ($answer["status"] == 3 OR $answer["status"] == 4))
					$array["prepay_sum"] = $check;
			}
		}
		if($answer["status"] == 5)
			$array["doc"] = 3;
		if($answer["status"] == 1 OR $answer["status"] == 2)
			$array["doc"] = 1;
		$answer = $connect->getAll("SELECT id, id_room, id_service, date_z, days, sum, number, type, note FROM position_reck WHERE schet=?i", $id);
		foreach($answer as $a){
			$array["position"][$a["id"]]["date_z"] = month_transform(date_change($a["date_z"]));
			$array["position"][$a["id"]]["days"] = $a["days"];
			$array["position"][$a["id"]]["type"] = $a["type"];
			$array["position"][$a["id"]]["note"] = $a["note"];
			$array["position"][$a["id"]]["number"] = $a["number"];
			if($a["id_room"])
				$array["position"][$a["id"]]["room"] = get_room($connect, $a["id_room"], "full");
			else
				$array["position"][$a["id"]]["room"] = $connect->getOne("SELECT name FROM service_schet WHERE id=?i", $a["id_service"]);
			if($a["sum"] > 0){
				$array["position"][$a["id"]]["sum"] = $a["sum"]." рублей";
				$array["position"][$a["id"]]["all_sum"] = calculate_position($a["sum"], $a["number"], $a["type"], $a["days"]);
				$array["position"][$a["id"]]["all_sum"] = add_null($array["position"][$a["id"]]["all_sum"])." рублей";
			}else{
				$array["position"][$a["id"]]["sum"] = "уточняется";
				$array["position"][$a["id"]]["all_sum"] = "уточняется";
			}
		}
		$rest = explode(",", $rest);
		$rest = array_diff($rest, array(""));
		foreach($rest as $turist){
			$answer = $connect->getRow("SELECT id, name, surname, otch, passport, date, date_pas, output, birth_certificate FROM klient WHERE id=?i", $turist);
			$array["rest"][$answer["id"]]["fio"] = $answer["surname"]." ".$answer["name"]." ".$answer["otch"];
			$array["rest"][$answer["id"]]["date"] = date_change($answer["date"], ".");
			$document = "";
			if(!$answer["passport"] AND $answer["birth_certificate"])
				$document = "Свид. о рожд. ".$answer["birth_certificate"];
			elseif($answer["passport"]){
				//$document = "Паспорт ".substr_replace($answer["passport"], " ", 4, 0);
				$document = "Паспорт ".$answer["passport"];
				if($answer["date_pas"])
					$document.= " выдан ".date_change($answer["date_pas"], ".")." ".$answer["output"];
			}
			$array["rest"][$answer["id"]]["document"] = $document;
		}
		$talk = $connect->getOne("SELECT id FROM talk WHERE client=?i AND type='turist' AND id_reck=?i", $turist, $id);
		$data = $connect->getAll("SELECT id, DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date, text, type, user FROM message_talk WHERE talk=?i ORDER BY id", $talk);
		foreach($data as $row){
			$array["message"][$row["id"]]["date"] = $row["date"];
			$array["message"][$row["id"]]["text"] = $row["text"];
			$array["message"][$row["id"]]["type"] = $row["type"];
			if($row["user"])
				$array["message"][$row["id"]]["manager"] = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["user"]);
		}
		$connect->query("UPDATE message_talk SET active=1 WHERE talk=?i AND type='manager'", $talk);
		return $array;
	}
	return FALSE;
}

function show_tour_module_object_account($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization_booking_object()){
		$select = new DisplayRequestBookingModule;
		$request = $select->select_request_booking();
		$request["object-info"] = $select->select_info_module();
		return $request;
	}
	return FALSE;
}

function show_bonus_account($connect, $data){
	global $COLORS;
	$array = array();
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($login AND $client){
		$answer = $connect->getAll("SELECT id, date, sum, schet, type, note, booking FROM bonus WHERE turist=?i", $client);
		foreach($answer as $a){
			$today = date("Y-m-d");
			$id = $a["id"];
			$array[$id]["date"] = date_change($a["date"], ".");
			$array[$id]["timestamp"] = strToTime($a['date']);
			$dateO = new DateTime($a['date']);
			$dateO->modify("+1 year");
			$dateO->modify("+6 month");
			$array[$id]['last_timestamp'] = $dateO->format("U");

			$array[$id]["sum"] = $a["sum"];
			$type = $a["type"];
			$array[$id]["type"] = $connect->getOne("SELECT name FROM type_bonus WHERE id=?i", $type);
			$array[$id]["access"] = 1;
			if($array[$id]["sum"] > 0) {
				if($array[$id]['last_timestamp']  > time())
					$array[$id]["color"] = $COLORS["success"];
				else
					$array[$id]["color"] = $COLORS["cancel"];
			}
			else
				$array[$id]["color"] = $COLORS["cancel"];
			$note = "";
			if($type == 1){
				if($a["schet"]){
					$id_obj = $connect->getOne("SELECT id_obj FROM reckoning WHERE id=?i", $a["schet"]);
				}else{
					$id_obj = $connect->getOne("SELECT object FROM booking_request_object_module WHERE id=?i", $a["booking"]);
				}
				$object = get_object($connect, $id_obj, "type");
				$note = "Путевка в ".$object;
			}elseif($type == 2)
				$note = "Передача";
			elseif($type == 3)
				$note = $a["note"];
			elseif($type == 4){
				$note = "Партнерка";
				if($connect->getOne("SELECT id FROM reckoning WHERE date_v>?s AND id=?i", $today, $a["schet"]))
					$array[$id]["access"] = 0;
			}
			$array[$id]["note"] = $note;
		}
		$array = array('list' => $array);
		return $array;
	}
	return 0;
}

function show_affiliate_program($connect, $data){
	$array = array();
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$account = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($account){
		$array["hash"] = $connect->getOne("SELECT hash FROM klient WHERE id=?i LIMIT 1", $account);
		$klient_id = $connect->getOne("SELECT id FROM klient WHERE id=?i", $account);
		$id = 0;
		$answer = $connect->getAll("SELECT date_reg, id, name, surname FROM klient WHERE invited=?i", $account);
		foreach($answer as $a){
			$id++;
			$date = date_change($a["date_reg"]);
			$array["ref"][$id]["date"] = month_transform($date);
			$array["ref"][$id]["name"] = $a["name"];
			$array["ref"][$id]["surname"] = $a["surname"];
			$klient = $a["id"];
			$res = $connect->getAll("SELECT id FROM reckoning WHERE status=5 AND turist=?i", $klient);
			foreach($res as $b){
				$array["ref"][$id]["bonus"]+= $connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND type=4 AND turist=?i", $b["id"], $klient_id)." ";
			}
		}
		return $array;
	}
	return FALSE;
}

function new_booking_turist_cabinet($connect, $data){
	global $directory;
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($client){
		$object = $data["object"];
		$number_turist = $data["number_turist"];
		$note = $data["note"];
		$bonus = $data["bonus"];
		$days = $data["days"];
		$arrival = $data["arrival"];
		$rooms = $data["room"];
		$rest = $data["rest"];
		$today = date("Y-m-d");
		$hash = md5(uniqid());
		$site = "Личный кабинет";
		$promo_code = '';

		if(isset($data['promo_code'])) {
			$promo_code = trim($data['promo_code']);
		}

		if(isset($gsok[$object]))
			$object = 96;

		$date2 = explode(".", $arrival);
		$arrival = $date2[2]."-".$date2[1]."-".$date2[0];

		$connect->query("INSERT INTO reckoning(date, turist, note, id_obj, website, number_turist, form_booking) VALUES (?s, ?i, ?s, ?i, ?s, ?i, 'client')", $today, $client, $note, $object, $site, $number_turist);
		$booking = $connect->insertId();
		$reward = get_reward_object($connect, $object, $arrival);
		$tour = $connect->getOne("SELECT id_tour FROM object WHERE id=?i", $object);
		if($tour)
			$connect->query("UPDATE reckoning SET id_tour=?i WHERE id=?i", $tour, $booking);

		foreach($rooms as $id_room => $room){
			if($id_room){
				foreach($room["position"] as $position){
					$number = $position["number"];
					$note = $position["place"]." ".$position["note"];
					$sum = $position["price"];
					if($number)
						$connect->query("INSERT INTO position_reck(id_room, sum, schet, days, date_z, number, note, type, reward) VALUES (?i, ?s, ?i, ?i, ?s, ?i, ?s, 1, ?s)", $id_room, $sum, $booking, $days, $arrival, $number, $note, $reward);
				}
			}
		}

		$rest_string = "";
		foreach($rest as $turist){
			$surname = $turist["surname"];
			$name = $turist["name"];
			$otch = $turist["otch"];
			$date_b = $turist["date"];
			$id_old = $turist["id"];
      $sex = null;
      if(isset($turist['sex'])) {
        $sex = (int)$turist['sex'];
        if($sex !== 0 && $sex !== 1) {
          $sex = null;
        }
      }

			if($surname AND !$id_old){
        $original_data = [
          'surname' => $surname,
          'name' => $name,
          'otch' => $otch,
          'date' => $date_b,
					'sex' => $sex
        ];
        if(is_null($sex))
        	$connect->query("INSERT INTO klient(surname, name, otch, date, original_data) VALUES (?s, ?s, ?s, ?s, ?s)", $surname, $name, $otch, $date_b, json_encode($original_data));
        else
          $connect->query("INSERT INTO klient(surname, name, otch, sex, date, original_data) VALUES (?s, ?s, ?s, ?i, ?s, ?s)", $surname, $name, $otch, $sex, $date_b, json_encode($original_data));

        if($rest_string)
					$rest_string.= ",";
				$rest_string.= $connect->insertId();
			}elseif($id_old){
				if($rest_string)
					$rest_string.= ",";
				$rest_string.= $id_old;
			}
		}

		$connect->query("UPDATE reckoning SET rest=?s WHERE id=?i", $rest_string, $booking);
		save_schet_to_history($connect, $booking, "Новая заявка от туриста");
		change_arrival_date($connect, $booking);
		recalculation_sum($connect, $booking);

    if($promo_code !== "") {
      $promo_code = mb_strtolower($promo_code);
      $itog = $connect->getOne("SELECT sum FROM reckoning WHERE id=?i", $booking);
      $promo_bonus = check_promotional_code($promo_code, $object, $itog, array("arrival" => $arrival, "days" => $days));
			if($promo_bonus) {
        $connect->query("INSERT INTO bonus(date, turist, sum, type, note, promocode) VALUES (?s, ?i, ?s, 3, ?s, ?s)", $today, $client, $promo_bonus, "Подарочный бонус", $promo_code);
        $connect->query("INSERT INTO bonus(date, schet, turist, sum, cause) VALUES (?s, ?i, ?i, ?i, 1)", $today, $booking, $client, $promo_bonus * (-1));
        $connect->query("UPDATE reckoning SET promo_code=?s WHERE id=?i", $promo_code, $booking);
        save_schet_to_history($connect, $booking, "Использование промокода");
			}
    }

		if($bonus == 1){
			$all_bonus = all_klient_bonus($connect, $client);
			if($all_bonus > 0){
				$reward = $connect->getOne("SELECT reward FROM object WHERE id=?i", $object);
				$sum = $connect->getOne("SELECT sum FROM reckoning WHERE id=?i", $booking);
				$percent = 5;
				if($reward > 0)
					$percent = $reward / 2;

				$max = $sum * ($percent / 100);
				if($max >= $all_bonus OR $max == 0)
					$sale = $all_bonus;
				else
					$sale = $max;

				$sale = $sale * (-1);
				$connect->query("INSERT INTO bonus(date, turist, schet, sum) VALUES(?s, ?i, ?i, ?s)", $today, $client, $booking, $sale);
			}
		}

		$message = select_template_letter("turist/cabinet/new-reservation", "client", $booking);
		$connect->query("INSERT INTO send_mail(email, title, body) VALUES (?s, ?s, ?s)", $login, $message["title"], $message["content"]);
		return $booking;
	}
}

function add_object_to_favorites($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	$object = $data["object"];
	if($client AND $object){
		$favorites = json_decode($connect->getOne("SELECT favorites FROM klient WHERE id=?i", $client), TRUE);
		$favorites[$object] = "f";
		$new_favorites = json_encode($favorites);
		$connect->query("UPDATE klient SET favorites=?s WHERE id=?i LIMIT 1", $new_favorites, $client);
		save_client_to_history($connect, $client, "Добавил объект «".$connect->getOne("SELECT name FROM object WHERE id=?i", $object)."» к избранному");
	}
}

function delete_object_from_favorites($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	$object = $data["object"];
	if($client AND $object){
		$favorites = json_decode($connect->getOne("SELECT favorites FROM klient WHERE id=?i", $client), TRUE);
		unset($favorites[$object]);
		$new_favorites = json_encode($favorites);
		$connect->query("UPDATE klient SET favorites=?s WHERE id=?i LIMIT 1", $new_favorites, $client);
		save_client_to_history($connect, $client, "Удалил объект «".$connect->getOne("SELECT name FROM object WHERE id=?i", $object)."» из избранного");
	}
}

function get_document_bill($connect, $data){
	global $directory;

	include_once($directory."/config.php");
	$conf = new JConfig;

	$array = array(
		"all_sum",
		"all_num"
	);
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$klient = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($login AND $klient){
		$id = $data["id"];
		if($connect->getOne("SELECT id FROM reckoning WHERE id=?i AND turist=?i", $id, $klient)){
			$array["id"] = $id;
			$answer = $connect->getRow("SELECT id_obj, id_com, agency, id_dis, date, status, id_user FROM reckoning WHERE id=?i", $id);
			$array["agency"] = $answer["agency"];
			$array["id_dis"] = $answer["id_dis"];

			$check = $connect->getOne("SELECT sum FROM time_payment WHERE type=2 AND id_schet=?i", $id);
			if($check AND ($answer["status"] == 3 OR $answer["status"] == 4))
				$array["prepay_sum"] = $check;

			if($answer["status"] != 3 AND $answer["status"] != 4 AND $answer["status"] != 5)
				return 0;
			$manager = $answer["id_user"];
			$array["object"] = get_object($connect, $answer["id_obj"], "full");
			$array["date"] = month_transform(date_change($answer["date"]));
			$array["bonus"] = abs($connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum < 0", $id));
			if($array["bonus"] > 0)
				$array["bonus"] = add_null($array["bonus"]);
			$index = 0;
			$answer = $connect->getRow("SELECT surname, name, otch FROM klient WHERE id=?i", $klient);
			$array["payer"] = $answer["surname"]." ".$answer["name"]." ".$answer["otch"];
			$answer = $connect->getAll("SELECT date_z, number, days, id_room, id_service, note, sum, type, add_one_day FROM position_reck WHERE schet=?i", $id);
			foreach($answer as $a){
				$index++;
				if($a["id_room"]){
					$days_sum = $a["days"];
					if($a["add_one_day"] == 0)
						$days_sum--;
					$date_v = date_sum($a["date_z"], $days_sum);
					$date_z = date_change($a["date_z"], ".");
					$room = get_room($connect, $a["id_room"], "full");
					$array["position"][$index]["room"] = $array["object"]." c ".$date_z." по ".date("d.m.Y", $date_v)." (".$a["days"]." дн., ".$room.") ".$a["note"];
				}else
					$array["position"][$index]["room"] = $connect->getOne("SELECT name FROM service_schet WHERE id=?i", $a["id_service"]);
				$array["position"][$index]["sum"] = add_null($a["sum"]);
				$array["position"][$index]["itog"] = calculate_position($a["sum"], $a["number"], $a["type"], $a["days"]);
				$array["position"][$index]["itog"] = add_null($array["position"][$index]["itog"]);
				$array["position"][$index]["type"] = $a["type"];
				$array["position"][$index]["number"] = $a["number"];
				$array["all_sum"]+= $array["position"][$index]["itog"];
				$array["all_num"]+= $array["position"][$index]["number"];
			}
			$array["all_sum"] = add_null($array["all_sum"]);
			$array["itog_sum"] = add_null($array["all_sum"] - $array["sum_commission"] - $array["bonus"]);
			$t = explode(".", $array["itog_sum"]);

			$array["itog"] = $array["itog_sum"];
			$array["sum3"] = $array["itog_sum"];
			$discount = $connect->getOne("SELECT value FROM discount WHERE id=?i", $array["id_dis"]);
			if($array["agency"]){
				$array["sum3"] = get_reward_agency($connect, $id);
				$array["itog_sum"] = $array["itog_sum"] - $array["sum3"];
			}elseif($discount){
				//if($type_dis == 1){
				$array["sum3"] = ($discount / 100) * $array["itog"];
				$array["type_dis"] = "%";
				//}else{
				//	$sum3 = $discount;
				//	$type_dis = " руб.";
				//}
				$array["itog_sum"] = $array["itog_sum"] - $array["sum3"];
			}


			$array["sum_text"] = convert_number_to_string($t[0])." рублей ".convert_number_to_string($t[1])." копеек";
			$array["sum_text"] = first_symbol_to_title($array["sum_text"]);
			$array["service"] = get_service_information();
			$row = $connect->getRow("SELECT address, post, bank, rs, ks, bik, inn, kpp FROM office WHERE id=?i", $connect->getOne("SELECT office FROM users WHERE id=?i", $manager));
			if($row){
				$array["service"]["BIK"] = $row["bik"];
				$array["service"]["KS"] = $row["ks"];
				$array["service"]["post"] = $conf->director;
				$array["service"]["booker"] = $conf->booker;
				$array["service"]["bank"] = $row["bank"];
				$array["service"]["reck"] = $row["rs"];
				$array["service"]["sep_address"] = $row["address"];
			}

			$array['test_row'] = $row;

			$array["service_reckoning"] = explode("_", $connect->getOne("SELECT id_services FROM reckoning WHERE id=?i", $id));
		
			$array["services"] = $connect->getAll("SELECT id, name, type FROM price_includes ORDER BY head DESC, type, id");

			return $array;
		}
	}
	return FALSE;
}

function get_document_obmen($connect, $data){
	$array = array();
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$klient = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($klient AND $login){
		$id = $data["id"];
		if($connect->getOne("SELECT id FROM reckoning WHERE id=?i AND turist=?i", $id, $klient)){
			$array["id"] = $id;
			$answer = $connect->getRow("SELECT status, agency, date_v, date_z, id_obj, sum, rest, number_turist FROM reckoning WHERE id=?i", $id);
			$agency = $answer['agency'];
			if($answer["status"] != 5)
				return 0;
			$array["id_obj"] = $answer["id_obj"];
			$array["date_z"] = date_change($answer["date_z"], ".");
			$array["date_v"] = date_change($answer["date_v"], ".");
			$array["object"] = get_object($connect, $answer["id_obj"], "full_and_place");
			$array["number_turist"] = $answer["number_turist"];
			$array["sum"] = $answer["sum"];
			$arr = explode(".", $answer["sum"]);
			$array["sum_text"] = convert_number_to_string($arr[0])." рублей ".$arr[1]." копеек";
			$array["sum_text"] = first_symbol_to_title($array["sum_text"]);
			$array["arrival"] = $connect->getOne("SELECT arrival FROM object WHERE id=?i", $answer["id_obj"]);
			$array["leaving"] = $connect->getOne("SELECT leaving FROM object WHERE id=?i", $answer["id_obj"]);
			$rest = explode(",", $answer["rest"]);
			$index = 0;
			foreach($rest as $turist){
				$answer = $connect->getRow("SELECT surname, name, otch, passport, date FROM klient WHERE id=?i", $turist);
				if($answer["surname"]){
					$index++;
					$array["turist"][$index]["fio"] = $answer["surname"]." ".$answer["name"]." ".$answer["otch"];
					$array["turist"][$index]["passport"] = $answer["passport"];
					$array["turist"][$index]["date"] = date_change($answer["date"], ".");
				}
			}
			$index = 0;
			$answer = $connect->getAll("SELECT id_room, id_service, date_z, days, add_one_day, note FROM position_reck WHERE schet=?i", $id);
			foreach($answer as $a){
				$index++;
				if($a["id_room"])
					$array["position"][$index]["room"] = get_room($connect, $a["id_room"], "full");
				else
					$array["position"][$index]["room"] = $connect->getOne("SELECT name FROM service_schet WHERE id=?i", $a["id_service"]);
				if($a["note"])
					$array["position"][$index]["room"].= " (".$a["note"].")";
				$array["position"][$index]["date_z"] = date_change($a["date_z"], ".");
				$days = $a["days"];
				if($a["add_one_day"] == 0)
					$days--;
				$date = date_sum($a["date_z"], $days);
				$array["position"][$index]["date_v"] = date("d.m.Y", $date);
			}

			$array["service"] = get_service_information();
			$array["service"]['agency'] = $agency;
			$array["service_reckoning"] = explode("_", $connect->getOne("SELECT id_services FROM reckoning WHERE id=?i", $id));
		
			$array["services"] = $connect->getAll("SELECT id, name, type FROM price_includes ORDER BY head DESC, type, id");

			return $array;
		}
	}
	return FALSE;
}

function get_document_return($connect){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $_POST["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($client){
		$array = get_service_information();
		return $array;
	}
	return FALSE;
}

function update_password_client_account($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$array = $connect->getRow("SELECT id, password FROM klient WHERE login=?s", $login);
	$klient = $array["id"];
	if(($array["password"] == $data["old"]) AND $login){
		$new = $data["new"];
		$connect->query("UPDATE klient SET password=?s WHERE id=?i", $new, $klient);
		save_client_to_history($connect, $klient, "Изменил пароль");
		return 1;
	}
	return 0;
}

function request_cancel_account($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($client){
		$id = $data["id"];
		$text = $data["text"];
		if($connect->getOne("SELECT id FROM reckoning WHERE id=?i AND turist=?i", $id, $client)){
			$user = $connect->getOne("SELECT id_user FROM reckoning WHERE id=?i", $id);
			$connect->query("UPDATE reckoning SET status=11 WHERE id=?i LIMIT 1", $id);
			save_notification($connect, "Запрос аннуляции заявки №".$id." Причина - ".$text, $user);
			save_schet_to_history($connect, $id, "Причина - ".$text);
			save_client_to_history($connect, $client, "Запрос аннуляции заявки №".$id);
		}
	}
}

function request_contract_account($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($client){
		$id = $data["id"];
		if($connect->getOne("SELECT id FROM reckoning WHERE id=?i AND turist=?i", $id, $client)){
			$user = $connect->getOne("SELECT id_user FROM reckoning WHERE id=?i", $id);
			save_notification($connect, "Запрос договора по заявке №".$id, $user);
			save_client_to_history($connect, $client, "Запрос договора №".$id);
		}
	}
}

function show_question_account($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($client){
		$answer = array("check" => 1);
		$data = $connect->getAll("SELECT id, category, id_reck FROM talk WHERE client=?i AND type='turist'", $client);
		foreach($data as $row){
			$talk = $row["id"];
			$answer["talk"][$talk]["category"] = $connect->getOne("SELECT name FROM question_category WHERE id=?i", $row["category"]);
			$answer["talk"][$talk]["count"] = $connect->getOne("SELECT COUNT(*) FROM message_talk WHERE talk=?i", $talk);
			$answer["talk"][$talk]["bid"] = $row["id_reck"];
		}
		return $answer;
	}
	return FALSE;
}

function show_talk_account($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($client){
		$talk = $data["id"];
		if(!$connect->getOne("SELECT id FROM talk WHERE id=?i AND Client=?i AND type='turist'", $talk, $client))
			return FALSE;
		$answer = array("check" => 1);
		$category = $connect->getOne("SELECT category FROM talk WHERE id=?i AND Client=?i AND type='turist'", $talk, $client);
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

function send_question_account($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	$text = $data["text"];
	$category = $data["category"];
	if($client AND $text){
		$connect->query("INSERT INTO talk(client, category) VALUES(?i, ?i)", $client, $category);
		$talk = $connect->insertId();
		$connect->query("INSERT INTO message_talk(talk, text, type) VALUES(?i, ?s, 'client')", $talk, $text);
	}
}

function send_message_talk_account($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	$text = $data["text"];
	$talk = $connect->getOne("SELECT id FROM talk WHERE id=?i AND type='turist'", $data["talk"]);
	if($client AND $text AND $talk){
		$connect->query("INSERT INTO message_talk(talk, text, type) VALUES(?i, ?s, 'client')", $talk, $text);
		return $connect->getOne("SELECT DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date FROM message_talk WHERE id=?i", $connect->insertId());
	}
}

function send_new_message_bid_account($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
	if($client){
		$id = $data["id"];
		$message = $data["message"];
		if($connect->getOne("SELECT id FROM reckoning WHERE id=?i AND turist=?i", $id, $client) AND $message){
			$talk = $connect->getOne("SELECT id FROM talk WHERE id_reck=?i", $id);
			if(!$talk){
				$connect->query("INSERT INTO talk(client, category, type, id_reck) VALUES(?i, 6, 'turist', ?i)", $client, $id);
				$talk = $connect->insertId();
			}
			$connect->query("INSERT INTO message_talk(talk, text, type) VALUES(?i, ?s, 'client')", $talk, $message);
			return $connect->getOne("SELECT DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date FROM message_talk WHERE id=?i", $connect->insertId());
		}
	}
}

function show_list_account($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization()){
		$list = $connect->getOne("SELECT list FROM klient WHERE id=?i LIMIT 1", $klient);
		return json_decode($list, TRUE);
	}
}

function save_list_account($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization()){
		$li = $data["li"];
		if($li == "DELETE_ALL")
			$connect->query("UPDATE klient SET list='' WHERE id=?i LIMIT 1", $klient);
		else{
			$old = json_decode($connect->getOne("SELECT list FROM klient WHERE id=?i LIMIT 1", $klient), TRUE);
			$old[] = $li;
			$json = json_encode($old);
			$connect->query("UPDATE klient SET list=?s WHERE id=?i LIMIT 1", $json, $klient);
		}
	}
}

function show_reviews_account($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization()){
		$answer = array();
		$turist = ConfigCRM::getInstance()->account;
		$data = $connect->getAll("SELECT id, id_obj, DATE_FORMAT(date_z, '%d.%m.%Y') as arrival FROM reckoning WHERE turist=?i AND status=5 AND date_v<?s", $turist, date("Y-m-d"));
		foreach($data as $row){
			$id = $row["id"];
			$rating = $connect->getRow("SELECT id, clean, comfort, location, staff, leisure, ratio, treatment FROM rating WHERE schet=?i AND status>1", $id);
			$answer[$id]["object"] = get_object($connect, $row["id_obj"], "type");
			$answer[$id]["arrival"] = $row["arrival"];
			if($rating["id"]){
				$count_rating = 6;
				$average = $rating["clean"] + $rating["comfort"] + $rating["location"] + $rating["staff"] + $rating["treatment"] + $rating["leisure"] + $rating["ratio"];
				if($rating["treatment"] != 0)
					$count_rating++;
				$answer[$id]["average"] = round($average / $count_rating * 2, 2);
			}

		}
		return $answer;
	}
}

function show_comment_tour_account($connect, $data){
	$id = $data["id"];
	$hash = $data["hash"];
	$session = $data["session"];
	$check = "";
	if($hash)
		$check = $connect->getOne("SELECT id FROM rating WHERE (status=0 OR status=1) AND hash=?s", $hash);
	elseif($session){
		$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $data["session"]);
		$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
		$check = $connect->getOne("SELECT id FROM reckoning WHERE id=?i AND turist=?i", $id, $client);
	}
	if($check){
		if($hash)
			$id = $connect->getOne("SELECT schet FROM rating WHERE hash=?s", $hash);
		$answer = array();
		$id_rating = $connect->getOne("SELECT id FROM rating WHERE schet=?i", $id);
		if(!$id_rating OR $connect->getOne("SELECT id FROM rating WHERE schet=?i AND (status=0 OR status=1)", $id)){
			if(!$connect->getOne("SELECT id FROM rating WHERE schet=?i", $id)){
				$id_obj = $connect->getOne("SELECT id_obj FROM reckoning WHERE id=?i", $id);
				$connect->query("INSERT INTO rating(schet, date, id_obj) VALUES (?i, ?s, ?i)", $id, date("Y-m-d"), $id_obj);
			}
			$array = $connect->getRow("SELECT id, id_obj, date_z, date_v FROM reckoning WHERE id=?i", $id);
			if($array["id"]){
				$answer["rating"] = 1;
				$answer["object"] = get_object($connect, $array["id_obj"], "type");
				$answer["object-link"] = $connect->getOne("SELECT url_name FROM object WHERE id=?i", $array["id_obj"]);
				$answer["arrival"] = date_change($array["date_z"], ".");
				$answer["leaving"] = date_change($array["date_v"], ".");
			}else
				return FALSE;
		}else{
			$answer["rating"] = 2;
			$array = $connect->getRow("SELECT id, id_obj, date_z, date_v FROM reckoning WHERE id=?i", $id);
			$answer["object"] = get_object($connect, $array["id_obj"], "type");
			$answer["arrival"] = date_change($array["date_z"], ".");
			$answer["leaving"] = date_change($array["date_v"], ".");
			$array = $connect->getRow("SELECT ratio, treatment, leisure, staff, comfort, location, clean, negative, positive, advice, company, photos FROM rating WHERE id=?i", $id_rating);
			$answer["ratio"] = $array["ratio"] * 2;
			$answer["treatment"] = $array["treatment"] * 2;
			$answer["leisure"] = $array["leisure"] * 2;
			$answer["staff"] = $array["staff"] * 2;
			$answer["comfort"] = $array["comfort"] * 2;
			$answer["location"] = $array["location"] * 2;
			$answer["clean"] = $array["clean"] * 2;
			$answer["negative"] = $array["negative"];
			$answer["positive"] = $array["positive"];
			$answer["advice"] = $array["advice"];
			$answer["photos"] = $array["photos"];
			if($array["company"] == 1)
				$answer["company"] = "Нет";
			elseif($array["company"] == 2)
				$answer["company"] = "Да";
			$count_rating = 6;
			$average = ($array["clean"] + $array["comfort"] + $array["location"] + $array["staff"] + $array["treatment"] + $array["leisure"] + $array["ratio"]);
			if($array["treatment"] != 0)
				$count_rating++;
			$answer["average"] = round($average / $count_rating * 2, 2);
		}
		return $answer;
	}
}

function save_rating_tour_client($connect, $data){
	$id = $data["id"];
	$hash = $data["hash"];
	$session = $data["session"];
	if($hash)
		$rating = $connect->getOne("SELECT id FROM rating WHERE (status=0 OR status=1) AND hash=?s", $hash);
	elseif($session)
		$rating = $connect->getOne("SELECT id FROM rating WHERE schet=?i AND (status=0 OR status=1)", $id);
	if(($id OR $hash) AND $rating){
		$today = date("Y-m-d");
		$clean = $data["clean"];
		$staff = $data["staff"];
		$comfort = $data["comfort"];
		$location = $data["location"];
		$ratio = $data["ratio"];
		$leisure = $data["leisure"];
		$treatment = $data["treatment"];
		$company = $data["company"];
		$photos = $data["photos"];
		$positive = $data["positive"];
		$negative = $data["negative"];
		$advice = $data["advice"];
		$from = $data["from"];
		$connect->query("UPDATE rating SET date_send=?s, clean=?s, staff=?s, comfort=?s, location=?s, ratio=?s, leisure=?s, treatment=?s, company_rating=?s, photos=?s, positive=?s, negative=?s, advice=?s, status=2, hash='', from_whence=?s WHERE id=?i", $today, $clean, $staff, $comfort, $location, $ratio, $leisure, $treatment, $company, $photos, $positive, $negative, $advice, $from, $rating);
	}
	return FALSE;
}

function get_count_news($connect, $client){
	$read_news = $connect->getOne("SELECT read_news FROM klient WHERE id=?i", $client);
	$read_news = count(json_decode($read_news, TRUE));
	$all = $connect->getOne("SELECT COUNT(*) FROM news WHERE type='client' AND active=1");
	$count = $all - $read_news;
	$all = $connect->getAll("SELECT id FROM reckoning WHERE status=5 AND turist=?i AND date_v<?s", $client, date("Y-m-d"));
	foreach($all as $row){
		$id = $row["id"];
		if($connect->getOne("SELECT id FROM rating WHERE schet=?i AND (status=0 OR status=1)", $id) OR !$connect->getOne("SELECT id FROM rating WHERE schet=?i", $id))
			$count++;
	}
	if($count <= 0)
		$count = "";
	return $count;
}







function get_bil_for_hash($connect, $data){
	$hash = $data["hash"];
	$array = array();
	$row = $connect->getRow("SELECT id, id_obj, date_z, date_v, sum, status, rest, date FROM reckoning WHERE hash=?s AND active!=3 LIMIT 1", $hash);
	$id = $row["id"];
	if($id){
		$rest = explode(",", $row["rest"]);
		$status = $row["status"];
		$array["id"] = $id;
		$array["object"] = get_object($connect, $row["id_obj"], "full_and_place");
		$array["date"] = date_change($row["date"]);
		$array["date"] = month_transform($array["date"]);
		$array["date_z"] = date_change($row["date_z"]);
		$array["date_z"] = month_transform($array["date_z"]);
		$array["date_v"] = date_change($row["date_v"]);
		$array["date_v"] = month_transform($array["date_v"]);
		if($row["sum"] > 0)
			$array["sum"] = number_format($row["sum"], 2, ",", " ")." рублей";
		else
			$array["sum"] = "уточняется";
		$array["status"] = $connect->getOne("SELECT name FROM status WHERE id=?i", $status);
		$index = 0;
		$answer = $connect->getAll("SELECT id_room, date_z, days, sum, number, type, note FROM position_reck WHERE schet=?i", $id);
		foreach($answer as $row){
			$index++;
			$date_v = date_sum($row["date_z"], $row["days"]);
			$array["position"][$index]["date_v"] = date("d.m.Y", $date_v);
			$array["position"][$index]["date_z"] = date_change($row["date_z"], ".");
			$array["position"][$index]["days"] = $row["days"];
			$array["position"][$index]["type"] = $row["type"];
			$array["position"][$index]["note"] = $row["note"];
			$array["position"][$index]["number"] = $row["number"];
			$array["position"][$index]["room"] = get_room($connect, $row["id_room"], "full");
			if($row["sum"]){
				$array["position"][$index]["sum"] = $row["sum"]." рублей";
				$array["position"][$index]["all_sum"] = calculate_position($row["sum"], $row["number"], $row["type"], $row["days"]);
				$array["position"][$index]["all_sum"] = add_null($array["position"][$index]["all_sum"])." рублей";
			}else{
				$array["position"][$index]["sum"] = "уточняется";
				$array["position"][$index]["all_sum"] = "уточняется";
			}
		}
		$index = 0;
		$rest = array_diff($rest, array(""));
		foreach($rest as $klient){
			$index++;
			$row = $connect->getRow("SELECT name, surname, otch FROM klient WHERE id=?i", $klient);
			$array["rest"][$index]["fio"] = $row["surname"]." ".$row["name"]." ".$row["otch"];
		}
	}
	return $array;
}

function get_rating_comments_for_hash($connect, $data){
	$hash = $data["hash"];
	$email = $data["email"];
	$array = array();
	$row = $connect->getRow("SELECT id, schet, date, status, id_obj, ratio, treatment, leisure, staff, comfort, location, clean, negative, positive, advice, company_rating, photos FROM rating WHERE hash=?s AND status>=2", $hash);
	$status = $row["status"];
	if(!$row["id"])
		return FALSE;
	$schet = $row["schet"];
	$array["status"] = 1;
	$rating = $row["id"];
	$array["ratio"] = $row["ratio"] * 2;
	$array["treatment"] = $row["treatment"] * 2;
	$array["leisure"] = $row["leisure"] * 2;
	$array["staff"] = $row["staff"] * 2;
	$array["comfort"] = $row["comfort"] * 2;
	$array["location"] = $row["location"] * 2;
	$array["clean"] = $row["clean"] * 2;
	$array["negative"] = $row["negative"];
	$array["positive"] = $row["positive"];
	$array["advice"] = $row["advice"];
	$array["company_rating"] = $row["company_rating"];
	$array["photos"] = $row["photos"];
	$count_rating = 6;
	$average = ($row["clean"] + $row["comfort"] + $row["location"] + $row["staff"] + $row["treatment"] + $row["leisure"] + $row["ratio"]);
	if($row["treatment"] != 0)
		$count_rating++;
	$array["average"] = round($average / $count_rating * 2, 2);

	$row = $connect->getRow("SELECT id_obj, DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, DATE_FORMAT(date_v, '%d.%m.%Y') as date_v FROM reckoning WHERE id=?i", $schet);
	$array["object"] = get_object($connect, $row["id_obj"], "type");

	$data = $connect->getAll("SELECT name, text, email FROM rating_comment WHERE rating=?i AND status!=2 ORDER BY id", $rating);
	$index = 0;
	foreach($data as $row){
		if($email == $row["email"])
			$array["comment"][$index]["whose"] = "my";
		else
			$array["comment"][$index]["name"] = $row["name"];
		$array["comment"][$index]["text"] = $row["text"];
		$index++;
	}

	return $array;
}

function send_comment_rating($connect, $data){
	$hash = $data["hash"];
	$email = $data["email"];
	$text = $data["comment"];

	if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		$email = "";

	$rating = $connect->getOne("SELECT id FROM rating WHERE hash=?s AND status>=2", $hash);

	if($text AND $rating){
		$connect->query("INSERT INTO rating_comment(rating, name, email, text, website) VALUES (?i, ?s, ?s, ?s, 'form')", $rating, $name, $email, $text);
	}
}

?>
