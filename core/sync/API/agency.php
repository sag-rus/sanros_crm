<?php

function authorization_agency($connect, $data){
	global $CHAT_GROUP_AGENCY;
	$pass = $data["pass"];
	$login = $data["login"];
	$row = $connect->getRow("SELECT id, module, name, password, email, module_email, type_com, read_news FROM agency WHERE login=?s AND active=0 LIMIT 1", $login);
	$true_pass = $row["password"];
	if($pass == $true_pass){
		$id = $row["id"];
		$name = $row["name"];
		$module = $row["module"];
		$email = $row["email"];
		$module_email = $row["module_email"];
		if(!$module_email){
			$module_email = $email;
			$connect->query("UPDATE agency SET module_email=?s WHERE id=?i LIMIT 1", $module_email, $id);
		}
		$type_com = $row["type_com"];
		$ag_contract = select_agency_contract($connect, $id, "", "cabinet");
		$session = md5(uniqid());

		if($foundedSessionId = $connect->getOne("SELECT id FROM session_agency WHERE login=?s", $login))
			$connect->query("UPDATE session_agency SET id_session=?s WHERE id=?i", $session, $foundedSessionId);
		else
			$connect->query("INSERT INTO session_agency (login, id_session) VALUES (?s, ?s)", $login, $session);
		$dostup = 1;
		if($ag_contract["status"] <= 0)
			$dostup = 0;

		$count_new = 0;
		$data = $connect->getAll("SELECT id FROM talk WHERE client=?i AND type='agency'", $id);
		foreach($data as $row){
			$talk = $row["id"];
			$count_new_talk+= $connect->getOne("SELECT COUNT(*) FROM message_talk WHERE talk=?i AND type='manager' AND active=0", $talk);
		}

		$object_quota = array();
		$data = $connect->getAll("SELECT id FROM object WHERE check_places!=0");
		foreach($data as $row){
			$id_obj = $row["id"];
			$check = $connect->getOne("SELECT id FROM room WHERE accessible_places!='' AND id_obj=?i", $id_obj);
			if($check)
				$object_quota[$id_obj] = get_object($connect, $id_obj, "place");
		}

		$module_bid = $connect->getOne("SELECT COUNT(*) FROM booking_agency WHERE agency=?i AND active=0", $id);

		$array = array("session" => $session, "agency" => $name, "module-id" => $module, "email" => $email, "module-email" => $module_email, "type-com" => $type_com, "dostup" => $dostup, "talk" => $count_new_talk, "object-quota" => $object_quota, "count-module-bid" => $module_bid);
		save_history_agency($connect, $id, "Авторизация в ЛК");
		return $array;
	}else
		return FALSE;
}

function create_agency_booking($connect, $data){
	global $directory;
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	if(!$login)
		return FALSE;
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	$object = $data["object"];
	$from = $data["from"];
	$reward = $data["reward"];
	$room = $data["room"];
	$rest = $data["rest"];
	$number_turist = count($rest);
	$add_one_day = $data["add-one-day"];
	$note = $data["note"];
	$module_bid = $data["module"];
	$rest_string = "";
	$today = date("Y-m-d");

	if(isset($gsok[$object]))
		$object = 96;

	if(!$connect->getOne("SELECT id FROM commission WHERE id_obj='' AND id_agency='' AND value=?s", $reward))
		$connect->query("INSERT INTO commission(id_obj, id_agency, value) VALUES('', '', ?s)", $reward);
	$id_reward = $connect->getOne("SELECT id FROM commission WHERE id_obj='' AND id_agency='' AND value=?s", $reward);
	$hash = md5(uniqid());
	$site = "Личный кабинет";
	if($from == "module")
		$site = "Личный кабинет модуль";
	$connect->query("INSERT INTO reckoning(date, agency, note, id_obj, hash, website, number_turist, id_com, form_booking) VALUES (?s, ?i, ?s, ?i, ?s, ?s, ?i, ?s, 'agency')", $today, $agency, $note, $object, $hash, $site, $number_turist, $id_reward);
	$last_id_s = $connect->insertId();
	$id_tour = $connect->getOne("SELECT id_tour FROM object WHERE id=?i", $object);
	if($id_tour)
		$connect->query("UPDATE reckoning SET id_tour=?i WHERE id=?i", $id_tour, $last_id_s);
	save_schet_to_history($connect, $last_id_s, "Новая заявка от агентства");

	foreach($room as $position){
		$id_room = $position["id_room"];
		$number = $position["number"];
		$date2 = explode(".", $position["date_z"]);
		$date_z = $date2[2]."-".$date2[1]."-".$date2[0];
		$days = $position["days"];
		$note = $position["name"]." ".$position["note"];
		$sum = $position["value"];
		$id_type = $position["type"];
		$reward_object = get_reward_object($connect, $object, $date_z);
		$connect->query("INSERT INTO position_reck(id_room, sum, schet, days, date_z, number, note, type, add_one_day, reward) VALUES (?i, ?s, ?i, ?i, ?s, ?i, ?s, ?s, ?i, ?s)", $id_room, $sum, $last_id_s, (int)$days, $date_z, $number, $note, $id_type, (int)$add_one_day, $reward_object);
	}

	foreach($rest as $turist){
		$surname = $turist["surname"];
		$name = $turist["name"];
		$otch = $turist["otch"];
		$date2 = explode(".", $turist["birthday"]);
		$birthday = $date2[2]."-".$date2[1]."-".$date2[0];
		$passport = $turist["passport"];
		$birth_certificate = $turist["cert_birthday"];
    $sex = null;

    if(isset($turist['sex'])) {
      $sex = (int)$turist['sex'];
      if($sex !== 0 && $sex !== 1) {
        $sex = null;
      }
    }

		if($surname){
      $original_data = [
        'surname' => $surname,
        'name' => $name,
        'otch' => $otch,
        'date' => $birthday,
        'passport' => $passport,
        'birth_certificate' => $birth_certificate,
				'sex' => $sex
      ];

      if(is_null($sex))
      	$connect->query("INSERT INTO klient(surname, name, otch, date, passport, birth_certificate, original_data) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s)", $surname, $name, $otch, $birthday, $passport, $birth_certificate, json_encode($original_data));
      else
        $connect->query("INSERT INTO klient(surname, name, otch, sex, date, passport, birth_certificate, original_data) VALUES (?s, ?s, ?s, ?i, ?s, ?s, ?s, ?s)", $surname, $name, $otch, $sex, $birthday, $passport, $birth_certificate, json_encode($original_data));

      if($rest_string)
				$rest_string.= ",";
			$rest_string.= $connect->insertId();
		}
	}
	$connect->query("UPDATE reckoning SET rest=?s WHERE id=?i", $rest_string, $last_id_s);

	change_arrival_date($connect, $last_id_s);
	recalculation_sum($connect, $last_id_s);

	if($module_bid)
		$connect->query("UPDATE booking_agency SET active=2, bid=?i WHERE count=?i AND agency=?i", $last_id_s, $module_bid, $agency);

	include_once($directory."/core/lib/mail.php");
	$email = $connect->getOne("SELECT email FROM agency WHERE login=?s", $login);
	$message = select_template_letter("agency/new-reservation", "agency", $last_id_s);
	$connect->query("INSERT INTO send_mail(email, title, body) VALUES (?s, ?s, ?s)", $email, $message["title"], $message["content"]);

	return $last_id_s;
}

function show_details_page_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["id_session"]);
	if($login){
		$array = $connect->getRow("SELECT name, short_name, email, telephone, address, legal_address, inn, kpp, bik, rs, ks, bank, icq, website, fax, skype FROM agency WHERE login=?s LIMIT 1", $login);
		return $array;
	}
	return 0;
}

function show_tour_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s", $data["session"]);
	if($login){
		$array_status = get_status_array($connect, "status");
		$answer = array("check" => 1, "tour" => array());
		$id = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
		$data = $connect->getAll("SELECT id, id_obj, sum, status, date_z, date_v, rest FROM reckoning WHERE agency=?i AND ((active=0 OR active=1) AND ((status=6 AND date_v>=?s) OR status!=6))", $id, date("Y-m-d"));
		foreach($data as $row){
			$array_rest = explode(",", $row["rest"]);
			$array_rest = array_diff($array_rest, array(""));
			$rest = array();
			foreach($array_rest as $turist)
				$rest[] = select_name_klient($connect, $turist);
			$bid = $row["id"];
			$answer["tour"][$bid] = array();
			$answer["tour"][$bid]["object"] = get_object($connect, $row["id_obj"]);
			$answer["tour"][$bid]["sum"] = $row["sum"];
			$answer["tour"][$bid]["rest"] = $rest;
			$answer["tour"][$bid]["status"] = $array_status[$row["status"]];
			$answer["tour"][$bid]["arrival"] = date_change($row["date_z"], ".");
			$answer["tour"][$bid]["leaving"] = date_change($row["date_v"], ".");
		}
		return $answer;
	}
	return FALSE;
}

function show_tour_archive_agency($connect, $data){
	$LIMIT = 10;
	$page = $data["page"];
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	if($login){
		$array_status = get_status_array($connect, "status");
		$answer = array("check" => 1, "tour" => array());
		$id = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
		$count_page = ($page - 1) * $LIMIT;
		$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE agency=?i AND (active=2 OR (date_v<?s AND status=6))", $id, date("Y-m-d"));
		$answer["pages"] = ceil($count / $LIMIT);
		$data = $connect->getAll("SELECT id, id_obj, sum, status, date_z, date_v, rest FROM reckoning WHERE agency=?i AND (active=2 OR (date_v<?s AND status=6)) LIMIT ?i, ?i", $id, date("Y-m-d"), $count_page, $LIMIT);
		foreach($data as $row){
			$array_rest = explode(",", $row["rest"]);
			$array_rest = array_diff($array_rest, array(""));
			$rest = array();
			foreach($array_rest as $turist)
				$rest[] = select_name_klient($connect, $turist);
			$bid = $row["id"];
			$answer["tour"][$bid] = array();
			$answer["tour"][$bid]["object"] = get_object($connect, $row["id_obj"]);
			$answer["tour"][$bid]["sum"] = $row["sum"];
			$answer["tour"][$bid]["rest"] = $rest;
			$answer["tour"][$bid]["status"] = $array_status[$row["status"]];
			$answer["tour"][$bid]["arrival"] = date_change($row["date_z"], ".");
			$answer["tour"][$bid]["leaving"] = date_change($row["date_v"], ".");
		}
		return $answer;
	}
	return FALSE;
}

function show_tour_bid_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	if($login){
		$id = $data["id"];
		$agency = $connect->getOne("SELECT agency FROM reckoning WHERE id=?i AND active!=3", $id);
		if($connect->getOne("SELECT id FROM agency WHERE id=?i AND login=?s", $agency, $login)){
			$array = $connect->getRow("SELECT date, date_z, date_v, sum, number_turist, id_com, status, id_obj, rest, active FROM reckoning WHERE id=?i", $id);
			$id_obj = $array["id_obj"];
			$answer = array("check" => 1, "room" => array(), "rest" => array(), "message" => array());
			$answer["date"] = $array["date"];
			$answer["arrival-bid"] = $array["date_z"];
			$answer["leaving-bid"] = $array["date_v"];
			$answer["sum"] = $array["sum"];
			$answer["object"] = get_object($connect, $id_obj, "place");
			$answer["number-turist"] = $array["number_turist"];
			$id_com = $array["id_com"];
			$array2 = $connect->getRow("SELECT arrival, leaving FROM object WHERE id=?i", $id_obj);
			$answer["arrival"] = $array["arrival"];
			$answer["leaving"] = $array["leaving"];
			$answer["commission"] = $connect->getOne("SELECT value FROM commission WHERE id=?i", $id_com);
			$answer["reward"] = add_null(get_reward_agency($connect, $id));
			$status = $array["status"];
			$answer["status"] = $connect->getOne("SELECT name FROM status WHERE id=?i", $status);
			$answer["id-status"] = 0;
			if($status < 5)
				$answer["id-status"] = 1;
			if($status > 2 AND $status < 5)
				$answer["document"] = 1;
			if($status == 5){
				if($connect->getOne("SELECT putevka FROM agency_document WHERE id_reck=?i", $id) >= 1)
					$answer["document"] = 2;
				else
					$answer["document"] = 1;
				if(time() > strToTime($answer["arrival-bid"]))
					$answer["document"] = 3;
				$answer["id-status"] = 2;
			}
			$answer["active"] = $array["active"];
			$answer["changes"] = $connect->getOne("SELECT changes FROM reckoning WHERE id=?i", $id);
			$data = $connect->getAll("SELECT id, id_room, sum, date_z, days, number, note, reward, type FROM position_reck WHERE schet=?i", $id);
			foreach($data as $row){
				$answer["room"][$row["id"]]["room"] = get_room($connect, $row["id_room"], "full", "view_schet");
				$answer["room"][$row["id"]]["sum"] = $row["sum"];
				$answer["room"][$row["id"]]["arrival"] = $row["date_z"];
				$answer["room"][$row["id"]]["days"] = $row["days"];
				$answer["room"][$row["id"]]["number"] = $row["number"];
				$answer["room"][$row["id"]]["note"] = $row["note"];
			}
			$rest = explode(",", $array["rest"]);
			foreach($rest as $turist){
				if($turist){
					$row = $connect->getRow("SELECT surname, name, otch, date, passport FROM klient WHERE id=?i", $turist);
					$answer["rest"][$turist]["surname"] = $row["surname"];
					$answer["rest"][$turist]["name"] = $row["name"];
					$answer["rest"][$turist]["otch"] = $row["otch"];
					$answer["rest"][$turist]["date"] = $row["date"];
					$answer["rest"][$turist]["passport"] = $row["passport"];
				}
			}
			$talk = $connect->getOne("SELECT id FROM talk WHERE client=?i AND type='agency' AND id_reck=?i", $agency, $id);
			$data = $connect->getAll("SELECT id, DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date, text, type, user FROM message_talk WHERE talk=?i ORDER BY id", $talk);
			foreach($data as $row){
				$answer["message"][$row["id"]]["date"] = $row["date"];
				$answer["message"][$row["id"]]["text"] = $row["text"];
				$answer["message"][$row["id"]]["type"] = $row["type"];
				if($row["user"])
					$answer["message"][$row["id"]]["manager"] = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["user"]);
			}
			$connect->query("UPDATE message_talk SET active=1 WHERE talk=?i AND type='manager'", $talk);
			return $answer;
		}
	}
	return FALSE;
}

function show_tours_module_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	if($login){
		$answer = array("check" => 1, "tour" => array());
		$id = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
		$data = $connect->getAll("SELECT count, surname, name, object, DATE_FORMAT(arrival, '%d.%m.%Y') as arrival, days, active, DATE_FORMAT(time, '%d.%m.%Y') as date FROM booking_agency WHERE agency=?i ORDER BY id", $id);
		foreach($data as $row){
			$bid = $row["count"];
			$answer["tour"][$bid] = array();
			$answer["tour"][$bid]["object"] = get_object($connect, $row["object"]);
			$answer["tour"][$bid]["surname"] = $row["surname"];
			$answer["tour"][$bid]["name"] = $row["name"];
			$answer["tour"][$bid]["active"] = $row["active"];
			$answer["tour"][$bid]["arrival"] = $row["arrival"];
			$answer["tour"][$bid]["days"] = $row["days"];
			$answer["tour"][$bid]["date"] = $row["date"];
		}
		return $answer;
	}
	return FALSE;
}

function show_tour_module_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	if($login AND $agency){
		$count = $data["id"];
		$id = $connect->getOne("SELECT id FROM booking_agency WHERE count=?i AND agency=?i", $count, $agency);
		if($id){
			$answer = array("check" => 1, "position" => array());
			$row = $connect->getRow("SELECT surname, name, otch, email, telephone, object, DATE_FORMAT(arrival, '%d.%m.%Y') as arrival, days, active, DATE_FORMAT(time, '%d.%m.%Y') as date, position FROM booking_agency WHERE agency=?i AND id=?i", $agency, $id);
			if($row["active"] == 0){
				$connect->query("UPDATE booking_agency SET active=1 WHERE id=?i", $id);
				$row["active"] = 1;
			}
			$id_object = $row["object"];
			$answer["id"] = $count;
			$answer["id-object"] = $id_object;
			$answer["object"] = get_object($connect, $id_object, "place");
			$data_object = $connect->getRow("SELECT regular_com, add_one_day FROM object WHERE id=?i", $id_object);
			$answer["add-one-day"] = $data_object["add_one_day"];
			$answer["reward"] = $data_object["regular_com"];
			$answer["surname"] = $row["surname"];
			$answer["name"] = $row["name"];
			$answer["otch"] = $row["otch"];
			$answer["status"] = $row["status"];
			$answer["arrival"] = $row["arrival"];
			$answer["days"] = $row["days"];
			$answer["date"] = $row["date"];
			$answer["telephone"] = $row["telephone"];
			$answer["email"] = $row["email"];
			$answer["active"] = $row["active"];
			$position = json_decode($row["position"], TRUE);
			foreach($position as $room){
				$id_room = $room["id_room"];
				$answer["position"][$id_room] = array();
				$answer["position"][$id_room]["number"] = $room["number"];
				$answer["position"][$id_room]["id-place"] = $room["id_place"];
				$answer["position"][$id_room]["place"] = $room["place"];
				$answer["position"][$id_room]["price"] = $room["price"];
				$answer["position"][$id_room]["room"] = get_room($connect, $id_room, "full");
			}
			$answer["count-module-bid"] = $connect->getOne("SELECT COUNT(*) FROM booking_agency WHERE agency=?i AND active=0", $agency);
			return $answer;
		}
	}
	return FALSE;
}

function tour_module_agency_archive($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	if($login AND $agency){
		$count = $data["id"];
		$id = $connect->getOne("SELECT id FROM booking_agency WHERE count=?i AND agency=?i AND (active=0 OR active=1)", $count, $agency);
		if($id){
			$connect->query("UPDATE booking_agency SET active=3 WHERE id=?i", $id);
			$count = $connect->getOne("SELECT COUNT(*) FROM booking_agency WHERE agency=?i AND active=0", $agency);
			return $count;
		}
	}
}

function edit_information_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	if($login){
		$array = $connect->getRow("SELECT id, name, short_name, email, telephone, inn, bik, ks, rs, address, fax, icq, skype, website, legal_address, bank, kpp, present, post, doc FROM agency WHERE login=?s", $login);
		$contract = $connect->getRow("SELECT id, date, number, status FROM ag_contract WHERE agency=?i ORDER BY date DESC LIMIT 1", $array["id"]);
		$array["id_contract"] = $contract["number"];
		$array = clear_quotes($array);
		return $array;
	}
	return 0;
}

function save_all_information_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["id_session"]);
	if($login){
		$connect->query("UPDATE agency SET email=?s, telephone=?s, icq=?s, skype=?s, website=?s, fax=?s WHERE login=?s LIMIT 1", $data["email"], $data["telephone"], $data["icq"], $data["skype"], $data["website"], $data["fax"], $login);
		return 1;
	}
	return 0;
}

function cancellation_bid($connect, $data){
	global $directory;
	$id = $data["id"];
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s AND active=0 LIMIT 1", $login);
	$check = $connect->getOne("SELECT id FROM reckoning WHERE id=?i AND agency=?i", $id, $agency);
	if($check){
		$reason = $data["reason"];
		$history = " Причина - ".$reason;
		include_once($directory."/core/lib/mail.php");
		$message = "Прошу аннулировать заявку №".$id." от ".$date." в связи с тем, что ".$reason;
		$connect->query("UPDATE reckoning SET status=10 WHERE id=?i", $id);
		save_schet_to_history($connect, $id, $history);
		$title = "Аннуляция заявки";
		$connect->query("INSERT INTO send_mail(email, title, body) VALUES (?s, ?s, ?s)", "kazangood@gmail.com", $title, $message);
	}
}

function update_password_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	if($login){
		$old = $data["old"];
		$new = $data["new"];
		if($old AND $new){
			$id = $connect->getOne("SELECT id FROM agency WHERE login=?s AND password=?s LIMIT 1", $login, $old);
			if($id){
				$connect->query("UPDATE agency SET password=?s WHERE id=?i", $new, $id);
				save_history_agency($connect, $id, "Изменение пароля");
				return 1;
			}
		}
	}
}

function get_document_bill_agency($connect, $data){
	$from = $data["from"];
	$id = $data["id"];
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	if($login){
		$array = array("check" => 1, "position" => array());
		$array["payer"] = $connect->getOne("SELECT name FROM agency WHERE id=?i", $agency);
		$array["id"] = $id;
		$row = $connect->getRow("SELECT id_obj, id_com, date, sum, id_user FROM reckoning WHERE id=?i", $id);
		$array["object"] = get_object($connect, $row["id_obj"], "full");
		$array["date"] = $row["date"];
		$array["all-sum"] = add_null($row["sum"]);
		$manager = $row["id_user"];
		$array["commission"] = $connect->getOne("SELECT value FROM commission WHERE id=?i", $row["id_com"]);
		$data = $connect->getAll("SELECT id, date_z, number, days, id_room, note, sum, type, add_one_day FROM position_reck WHERE schet=?i", $id);
		foreach($data as $row){
			$index = $row["id"];
			$array["position"][$index] = array();
			$array["position"][$index]["days"] = $row["days"];
			$array["position"][$index]["arrival"] = $row["date_z"];
			$days_sum = $row["days"];
			if($row["add_one_day"] == 0)
				$days_sum--;
			$leaving = date_sum($row["date_z"], $days_sum);
			$array["position"][$index]["leaving"] = date("Y-m-d", $leaving);
			$array["position"][$index]["note"] = $row["note"];
			$array["position"][$index]["room"] = get_room($connect, $row["id_room"], "full");
			$array["position"][$index]["sum"] = add_null($row["sum"]);
			$array["position"][$index]["itog"] = calculate_position($row["sum"], $row["number"], $row["type"], $row["days"]);
			$array["position"][$index]["itog"] = add_null($array["position"][$index]["itog"]);
			$array["position"][$index]["type"] = $row["type"];
			$array["position"][$index]["number"] = $row["number"];
			$array["all-num"]+= $array["position"][$index]["number"];
		}
		$array["sum-commission"] = add_null(get_reward_agency($connect, $id));
		$array["itog-sum"] = add_null($array["all-sum"] - $array["sum-commission"]);
		$t = explode(".", $array["itog-sum"]);
		$array["sum-text"] = convert_number_to_string($t[0])." рублей ".convert_number_to_string($t[1])." копеек";
		$array["sum-text"] = first_symbol_to_title($array["sum-text"]);
		$array["service"] = get_service_information();
		$row = $connect->getRow("SELECT address, bank, rs, ks, bik, inn, kpp FROM office WHERE id=?i", $connect->getOne("SELECT office FROM users WHERE id=?i", $manager));
		if($row["bank"]){
			$array["service"]["BIK"] = $row["bik"];
			$array["service"]["KS"] = $row["ks"];
			$array["service"]["bank"] = $row["bank"];
			$array["service"]["reck"] = $row["rs"];
			$array["service"]["sep_address"] = $row["address"];
		}

		$array["service_reckoning"] = explode("_", $connect->getOne("SELECT id_services FROM reckoning WHERE id=?i", $id));
		
		$array["services"] = $connect->getAll("SELECT id, name, type FROM price_includes WHERE id IN(" . implode(",", $array["service_reckoning"]) . ")  ORDER BY head DESC, type, id");

		if($connect->getOne("SELECT id FROM agency_document WHERE id_reck=?i", $id))
			$connect->query("UPDATE agency_document set schet=2 WHERE id_reck=?i", $id);
		else
			$connect->query("INSERT INTO agency_document (id_reck, schet) VALUES (?i, 2)", $id);
		save_history_agency($connect, $agency, "Документ счет, заявка №".$id);
		return $array;
	}
}

function get_document_dover_agency($connect, $data) {
	$from = $data["from"];
	$id = $data["id"];
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	$check = $connect->getOne("SELECT id FROM reckoning WHERE id=?i AND agency=?i", $id, $agency);
	
	if($login AND $check){
		global $directory;
		include_once($directory."/config.php");
		include_once($directory."/core/document/dover.php");
		$array = array("check" => 1, "position" => array(), 'as'=>'test');

		$conf = new JConfig;
		$array['firma'] = $conf->firma;
		$array['email'] = $conf->Email;
		$array['leg_address'] = $conf->leg_address;
		$array['inn'] = $conf->INN;
		$array['kpp'] = $conf->KPP;
		$array['bik'] = $conf->BIK;
		$array['ks'] = $conf->KS;
		$array['bank'] = $conf->bank;
		$array['reck'] = $conf->reck;
		$array['director'] = $conf->director;
		$array['booker'] = $conf->booker;

		$array['reckoning'] = $row = $connect->getRow("SELECT id_obj, DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, DATE_FORMAT(date_v, '%d.%m.%Y') as date_v, manager, rest, number_turist, DATE_FORMAT(date_schet_san, '%d.%m.%Y') as date_schet_san, schet_san, turist FROM reckoning WHERE id=?i", $data['id']);
		$id_obj = $array['reckoning']["id_obj"];

		$date_z = $array['reckoning']["date_z"];
		$date_v = $array['reckoning']["date_v"];

		if(!$data['turist'])
			$turist = $a['turist'];

		$array['klient'] = $connect->getRow("SELECT surname, name, otch, passport, output, DATE_FORMAT(date_pas, '%d.%m.%Y') as date_pas FROM klient WHERE id=?i", $turist);

		$array['trans'] = get_translit($array['klient']["surname"])."_".$data['id'];

		$array['position_reck'] = $connect->getRow("SELECT id_room, days, number FROM position_reck WHERE schet=?i", $data['id']);
		$array['room'] = get_room($connect, $array['position_reck']["id_room"]);
		$array['putevka'] = naimenovanie($id_obj, $array['room'], $date_z, $date_v, $array['position_reck']['days']);

		$array['check_service'] = $connect->getOne("SELECT id_service FROM position_reck WHERE schet=?i AND id_room=0", $id);

		if($array['check_service']) {
			$array['service'] = $connect->getOne("SELECT name FROM service_schet WHERE id=?i", $array['check_service']);
		}

		$array['number_id_obj'] = number($id_obj);

		$date_dei = date_sum($date_z, $days + 1);
		$array['date_dei'] = date("d.m.Y", $date_dei);
		$date_out = date_sum($date_z, -1);
		$array['date_out'] = date("d.m.Y", $date_out);

		return $array;
	}
}

function get_document_obmen_agency($connect, $data){
	$from = $data["from"];
	$id = $data["id"];
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	$check = $connect->getOne("SELECT id FROM reckoning WHERE id=?i AND agency=?i", $id, $agency);
	if($login AND $check){
		$array = array("check" => 1, "position" => array());
		$array["id"] = $id;
		$row = $connect->getRow("SELECT status, date_v, date_z, id_obj, sum, rest, number_turist FROM reckoning WHERE id=?i", $id);
		if($row["status"] != 5)
			return 0;
		$object = $row["id_obj"];
		$array["id-object"] = $object;
		$array["arrival"] = $row["date_z"];
		$array["leaving"] = $row["date_v"];
		$array["object"] = get_object($connect, $object, "full_and_place");
		$array["number-turist"] = $row["number_turist"];
		$array["sum"] = $row["sum"];
		$arr = explode(".", $row["sum"]);
		$array["sum-text"] = convert_number_to_string($arr[0])." рублей ".$arr[1]." копеек";
		$array["sum-text"] = first_symbol_to_title($array["sum-text"]);
		$array["arrival-time"] = $connect->getOne("SELECT arrival FROM object WHERE id=?i", $object);
		$array["leaving-time"] = $connect->getOne("SELECT leaving FROM object WHERE id=?i", $object);
		$rest = explode(",", $row["rest"]);
		$index = 0;
		foreach($rest as $turist){
			$row = $connect->getRow("SELECT surname, name, otch, passport, date, birth_certificate FROM klient WHERE id=?i", $turist);
			if($row["surname"]){
				$index++;
				$array["turist"][$index]["fio"] = $row["surname"]." ".$row["name"]." ".$row["otch"];
				$array["turist"][$index]["passport"] = $row["passport"];
				$array["turist"][$index]["date"] = $row["date"];
				if($row["passport"] == "")
					$array["turist"][$index]["passport"] = $row["birth_certificate"];
			}
		}
		$data = $connect->getAll("SELECT id, id_room, date_z, days, add_one_day, note FROM position_reck WHERE schet=?i", $id);
		foreach($data as $row){
			$index = $row["id"];
			$array["position"][$index] = array();
			$array["position"][$index]["room"] = get_room($connect, $row["id_room"], "full");
			if($row["note"])
				$array["position"][$index]["room"].= " (".$row["note"].")";
			$array["position"][$index]["arrival"] = $row["date_z"];
			$days = $row["days"];
			if($row["add_one_day"] == 0)
				$days--;
			$date = date_sum($row["date_z"], $days);
			$array["position"][$index]["leaving"] = date("Y-m-d", $date);
		}
		$array["service"] = get_service_information();
		$array["service_reckoning"] = explode("_", $connect->getOne("SELECT id_services FROM reckoning WHERE id=?i", $id));
		
		$array["services"] = $connect->getAll("SELECT id, name, type FROM price_includes ORDER BY head DESC, type, id");

		if($connect->getOne("SELECT id FROM agency_document WHERE id_reck=?i", $id))
			$connect->query("UPDATE agency_document set putevka=2 WHERE id_reck=?i", $id);
		else
			$connect->query("INSERT INTO agency_document (id_reck, putevka) VALUES (?i, 2)", $id);
		save_history_agency($connect, $agency, "Документ обменная путевка, заявка №".$id);
		return $array;
	}
}

function get_document_report_agency($connect, $data){
	$from = $data["from"];
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$id = $data["id"];
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	$check = $connect->getOne("SELECT id FROM reckoning WHERE id=?i AND agency=?i", $id, $agency);
	if($login AND $check){
		$array = array("check" => 1);
		$array["id"] = $id;
		$row = $connect->getRow("SELECT reckoning.date_z, reckoning.sum, reckoning.id_obj, reckoning.agency, reckoning.id_com, position_reck.days FROM reckoning, position_reck WHERE reckoning.id=?i AND reckoning.id=position_reck.schet", $id);
		$array["object"] = get_object($connect, $row["id_obj"], "type");
		$array["arrival"] = date_change($row["date_z"]);
		$array["arrival-trans"] = month_transform($array["arrival"]);
		$array["days"] = $row["days"];
		$array["sum"] = add_null($row["sum"]);
		$id_com = $row["id_com"];
		$row = $connect->getRow("SELECT name, legal_address, inn, kpp FROM agency WHERE id=?i", $agency);
		$array["agency"] = $row["name"];
		$array["leg-address-agency"] = $row["legal_address"];
		$array["agency-contract"] = select_agency_contract($connect, $agency, "all");
		$array["INN-agency"] = $row["inn"];
		$array["KPP-agency"] = $row["kpp"];
		$commis = $connect->getOne("SELECT value FROM commission WHERE id=?i", $id_com);
		$reward = $array["sum"] * ($commis / 100);
		$array["reward"] = add_null($reward);
		$data = $connect->getAll("SELECT sum FROM payment WHERE schet=?i AND (type=1 OR type=2)", $id);
		foreach($data as $row)
			$array["oplata"]+= $row["sum"];
		$row = $connect->getRow("SELECT date FROM history_schet WHERE id_schet=?i AND new_status=3 ORDER BY id LIMIT 1", $id);
		$array["date"] = date_change($row["date"]);
		$array["date"] = month_transform($array["date"]);
		$arr = explode(".", $array["sum"]);
		$itog_sum_string = convert_number_to_string($arr[0])." рублей ".$arr[1]." копеек";
		$itog_sum_string = first_symbol_to_title($itog_sum_string);
		$array["itog-sum-string"] = $itog_sum_string;
		$arr = explode(".", $array["reward"]);
		$itog_reward_string = convert_number_to_string($arr[0])." рублей ".$arr[1]." копеек";
		$itog_reward_string = first_symbol_to_title($itog_reward_string);
		$array["itog-reward-string"] = $itog_reward_string;
		$array["service"] = get_service_information();
		save_history_agency($connect, $agency, "Документ отчет агента, заявка №".$id);
		return $array;
	}
}

function show_question_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	if($agency){
		$answer = array();
		$data = $connect->getAll("SELECT id, category, id_reck FROM talk WHERE client=?i AND type='agency'", $agency);
		foreach($data as $row){
			$id = $row["id"];
			$answer[$id] = array();
			$answer[$id]["category"] = $connect->getOne("SELECT name FROM question_category WHERE id=?i", $row["category"]);
			$answer[$id]["date"] = $connect->getOne("SELECT DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date FROM message_talk WHERE talk=?i ORDER BY date", $id);
			$answer[$id]["new"] = $connect->getOne("SELECT COUNT(*) FROM message_talk WHERE talk=?i AND type='manager' AND active=0", $id);
			$answer[$id]["bid"] = $row["id_reck"];
		}
		return $answer;
	}
	return FALSE;
}

function show_talk_agency($connect, $data){
	$id = $data["id"];
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	$category = $connect->getOne("SELECT category FROM talk WHERE id=?i AND client=?i AND type='agency'", $id, $agency);
	if($agency AND $category){
		$answer = array("check" => 1, "message" => array(), "count" => 0);
		$answer["category"] = $connect->getOne("SELECT name FROM question_category WHERE id=?i", $category);
		$answer["bid"] = $connect->getOne("SELECT id_reck FROM talk WHERE id=?i AND client=?i AND type='agency'", $id, $agency);
		$data = $connect->getAll("SELECT id, DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date, text, type, user FROM message_talk WHERE talk=?i ORDER BY id", $id);
		foreach($data as $row){
			$index = $row["id"];
			$answer["message"][$index] = array();
			$answer["message"][$index]["date"] = $row["date"];
			$answer["message"][$index]["text"] = $row["text"];
			$answer["message"][$index]["type"] = $row["type"];
			if($row["user"])
				$answer["message"][$index]["manager"] = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["user"]);
		}
		$connect->query("UPDATE message_talk SET active=1 WHERE talk=?i AND type='manager'", $id);

		$data = $connect->getAll("SELECT id FROM talk WHERE client=?i AND type='agency'", $agency);
		foreach($data as $row){
			$talk = $row["id"];
			$answer["count"]+= $connect->getOne("SELECT COUNT(*) FROM message_talk WHERE talk=?i AND type='manager' AND active=0", $talk);
		}

		return $answer;
	}
	return FALSE;
}

function send_question_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	$text = $data["text"];
	$category = $data["category"];
	if($agency AND $text AND $category){
		$connect->query("INSERT INTO talk(client, category, type) VALUES(?i, ?i, 'agency')", $agency, $category);
		$talk = $connect->insertId();
		$connect->query("INSERT INTO message_talk(talk, text, type) VALUES(?i, ?s, 'client')", $talk, $text);
		return $talk;
	}
}

function send_message_talk_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	$text = $data["text"];
	$talk = $data["talk"];
	$true = $connect->getOne("SELECT id FROM talk WHERE id=?i AND type='agency'", $talk);
	if($agency AND $text AND $true){
		$connect->query("INSERT INTO message_talk(talk, text, type) VALUES(?i, ?s, 'client')", $talk, $text);
		return $connect->getOne("SELECT DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date FROM message_talk WHERE id=?i", $connect->insertId());
	}
}

function send_new_message_bid_agency($connect, $data){
	$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s LIMIT 1", $data["session"]);
	$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s LIMIT 1", $login);
	if($agency){
		$id = $data["id"];
		$message = $data["message"];
		if($connect->getOne("SELECT id FROM reckoning WHERE id=?i AND agency=?i", $id, $agency) AND $message){
			$talk = $connect->getOne("SELECT id FROM talk WHERE id_reck=?i", $id);
			if(!$talk){
				$connect->query("INSERT INTO talk(client, category, type, id_reck) VALUES(?i, 6, 'agency', ?i)", $agency, $id);
				$talk = $connect->insertId();
			}
			$connect->query("INSERT INTO message_talk(talk, text, type) VALUES(?i, ?s, 'client')", $talk, $message);
			$time = $connect->getOne("SELECT DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date FROM message_talk WHERE id=?i", $connect->insertId());
			return $time;
		}
	}
}

function dover_naimenovanie($id_obj, $room, $date_z, $date_v, $days){
	$date_z = str_replace("-", ".", $date_z);
	$date_v = str_replace("-", ".", $date_v);
	$html = "";
	if($id_obj == 3) //Бакирово
		$html = "Санаторий \"Бакирово\" ".$room." ".$date_z."-".$date_v;
	elseif($id_obj == 6) //варзи-ятчи
		$html = "Санаторий \"Варзи-Ятчи\"";
	elseif($id_obj == 59) //ян
		$html = "Путевка";
	elseif($id_obj == 31) //лениногорский
		$html = "Путевка в санаторий-профилакторий";
	elseif($id_obj == 60) //янган-тау
		$html = "Санаторий \"Янган-Тау\" с ".$date_z." по ".$date_v." ".$room;
	elseif($id_obj == 50) //ува
		$html = "Санаторий \"Ува\" ".$room;
	elseif($id_obj == 57) //юматово
		$html = "Санаторий \"Юматово\" ".$room." с ".$date_z." на ".$days."дн";
	return $html;
}

function dover_name_object($id_obj){
	$html = "";
	if($id_obj == 3) //Бакирово
		$html = "ЛПУП санаторий \"Бакирово\"";
	elseif($id_obj == 6) //варзи-ятчи
		$html = "ООО \"Санаторий Варзи-Ятчи\"";
	elseif($id_obj == 59) //ян
		$html = "НГДУ \"Ямашнефрь\" ОАО \"Татнефть\"";
	elseif($id_obj == 31) //лениногорский
		$html = "ОАО \"Татнефть\" НГДУ \"Лениногорскнефть\" ";
	elseif($id_obj == 60) //янган-тау
		$html = "ГУП санаторий \"Янган-Тау\" РБ";
	elseif($id_obj == 50) //ува
		$html = "ООО \"Санаторий Ува\"";
	elseif($id_obj == 57) //юматово
		$html = "Государственное унитарное предприятие санаторий \"Юматово\" Республики Башкортостан";
	return $html;
}

function dover_number($id_obj){
	if($id_obj == 57 OR $id_obj == 59 OR $id_obj == 3 OR $id_obj == 31)
		return "шт";
	else
		return "";
}

function save_history_agency($connect, $agency, $text){
	$connect->query("INSERT INTO history_agency(agency, text) VALUES(?i, ?s)", $agency, $text);
}

function enter_dogovor_agency($connect, $data){
	global $directory;
	$data = $data["data"];
	$responseArray = [
		'id' => 0,
		'msg' => '',
		'post_data' => $data
	];
  $agency_post = $data;
  $number = 0;

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
  $er = false;

  if(mb_strlen($name) == 0) {
  	$er = true;
  	$responseArray['msg'] = 'Incorrect name';
	}

	if(mb_strlen($short_name) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect short name';
	}

	if(mb_strlen($present) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect present';
	}

	if(mb_strlen($present_short) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect present short';
	}

	if(mb_strlen($post) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect post';
	}

	if(mb_strlen($post_short) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect post short';
	}

	if(mb_strlen($doc)  == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect doc';
	}

	if(mb_strlen($address) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect address';
	}

	if(mb_strlen($legal_address) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect legal address';
	}

	if(mb_strlen($inn) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect inn';
	}

	if(mb_strlen($kpp) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect kpp';
	}

	if(mb_strlen($bik) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect bik';
	}

	if(mb_strlen($rs) == 0) {
    $er = true;
    $responseArray['msg'] = 'Incorrect rs';
	}

	if(mb_strlen($ks) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect ks';
	}

	if(mb_strlen($ogrn) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect ogrn';
	}

	if(mb_strlen($telephone) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect telephone';
	}

	if(mb_strlen($email) == 0) {
  	$er = true;
    $responseArray['msg'] = 'Incorrect email';
	}

  if(!$er) {
    $module = gen_password(rand(6, 8));

    while($connect->getOne("SELECT id FROM agency WHERE module=?s LIMIT 1", $module))
      $module = gen_password(rand(6, 8));

    if(mb_strlen($module) == 0) {
    	$responseArray['msg'] = 'Module string generating error';
		}

    try {
      $connect->query("INSERT INTO agency(name, short_name, present, telephone, email, fax, icq, skype, note, address, website, legal_address, inn, kpp, bik, rs, ks, bank, post, doc, module, module_email, created) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)", $name, $short_name, $present, $telephone, $email, $fax, $icq, $skype, $note, $address, $website, $legal_address, $inn, $kpp, $bik, $rs, $ks, $bank, $post, $doc, $module, $email, gmdate("U"));
      $id = $connect->insertId();
		}
		catch (Exception $e) {
    	$id = 0;
    	$responseArray['msg'] = 'Agency insert error';
		}

		if($id > 0) {
      $number = $id;
      $responseArray['id'] = $id;

      $message = "Номер договора: ".$number."<br />";
      $message.= "Юридическое название фирмы: ".$data["agency"]."<br />";
      $message.= "Сокращенное название фирмы: ".$data["short_agency"]."<br />";
      $message.= "Email: ".$data["email"]."<br />";
      $message.= "Телефон: ".$data["telephone"]."<br />";
      $message.= "Факс: ".$data["fax"]."<br />";
      $message.= "Почтовый адрес: ".$data["address"]."<br />";
      $message.= "Юридический адрес: ".$data["ur_address"]."<br />";
      $message.= "Представитель: ".$data["present"]."<br />";
      $message.= "Должность: ".$data["post_short"]."<br />";
      $message.= "Действует на основании: ".$data["doc"]."<br />";
      $message.= "ИНН: ".$data["inn"]."<br />";
      $message.= "КПП: ".$data["kpp"]."<br />";
      $message.= "БИК: ".$data["bik"]."<br />";
      $message.= "Р/с: ".$data["rs"]."<br />";
      $message.= "К/с: ".$data["ks"]."<br />";
      $message.= "Банк: ".$data["bank"]."<br />";
      $message.= "ОГРН: ".$data["ogrn"];

      $title = "Агентский договор";
      $email = "2602323@2602323.ru";
      $connect->query("INSERT INTO send_mail(email, title, body) VALUES (?s, ?s, ?s)", $email, $title, $message);
		}
		else {
    	if(empty($responseArray['msg']))
    		$responseArray['msg'] = 'Agency insert error';
		}
  }

	return $responseArray;

}

function renew_dogovor_agency($connect, $data){
	$data = $data["data"];

	$message = "<strong>Агентство перезаключает договор из ЛК</strong><br /><br />";
	$message.= "Номер договора: ".$data["id"]."<br />";
	$message.= "Юридическое название фирмы: ".$data["agency"]."<br />";
	$message.= "Сокращенное название фирмы: ".$data["short_agency"]."<br />";
	$message.= "Email: ".$data["email"]."<br />";
	$message.= "Телефон: ".$data["telephone"]."<br />";
	$message.= "Факс: ".$data["fax"]."<br />";
	$message.= "Почтовый адрес: ".$data["address"]."<br />";
	$message.= "Юридический адрес: ".$data["ur_address"]."<br />";
	$message.= "Представитель: ".$data["present"]."<br />";
	$message.= "Должность: ".$data["post_short"]."<br />";
	$message.= "Действует на основании: ".$data["doc"]."<br />";
	$message.= "ИНН: ".$data["inn"]."<br />";
	$message.= "КПП: ".$data["kpp"]."<br />";
	$message.= "БИК: ".$data["bik"]."<br />";
	$message.= "Р/с: ".$data["rs"]."<br />";
	$message.= "К/с: ".$data["ks"]."<br />";
	$message.= "Банк: ".$data["bank"]."<br />";
	$message.= "ОГРН: ".$data["ogrn"];

	$title = "Агентский договор";
	$email = "2602323@2602323.ru";
	$connect->query("INSERT INTO send_mail(email, title, body) VALUES (?s, ?s, ?s)", $email, $title, $message);

}

?>
