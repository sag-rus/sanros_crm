<?php

function select_objects_quota($connect){
	global $id_rights;
	$profkurort = null;
	$result = array("object" => array(), "region" => array(), "info" => array("all" => 0, "quota" => 0));
	$index = 0;
	$data = $connect->getAll("SELECT id, check_places, id_reg, sync_id FROM object WHERE check_places=1 OR check_places=2 OR check_places=3 ORDER BY id_reg, name");

	foreach($data as $row){
		$index++;
		$object = $row["id"];
		$region = $row["id_reg"];
		$result["object"][$index] = array("check-places" => 0, "have-places" => 0, "contract" => "");
		$result["object"][$index]["name"] = get_object($connect, $object, "type");
		$result["object"][$index]["id"] = $object;
		$result["object"][$index]["region"] = $region;
		$result["object"][$index]["address"] = get_object_address($connect, $object);
		if(!isset($result["region"][$region])){
			$result["region"][$region] = array();
			$result["region"][$region]["name"] = $connect->getOne("SELECT name FROM region WHERE id=?i", $region);
			$result["region"][$region]["count"] = 1;
		}else{
			$result["region"][$region]["count"]++;
		}
		$contracts = select_object_contract($connect, $object);
		foreach($contracts as $contract){
			$result["object"][$index]["contract"] = $contract["type"];
		}
		if($id_rights == 5)
			$result["object"][$index]["check-places"] = $row["check_places"];

        if ($row['check_places'] == 3) {
            if(is_null($profkurort)) {
              $profkurort = new ProfkurortSync();
            }
            //print_r($profkurort->get_quota_object($data['sync_id'],date("Y-m-d")." ".date("H:s"),1));
        }
		elseif($connect->getOne("SELECT id FROM room WHERE id_obj=?i AND accessible_places!=''", $object)){
			$result["object"][$index]["have-places"] = 1;
			$result["info"]["quota"]++;
		}
		$result["info"]["all"]++;
	}
	return json_encode($result);
}

function view_quota_object($connect, $data = array()){
	date_default_timezone_set("UTC");
	global $session_login;
	$object = $_POST["object"];
	$result = array("room" => array(), "bid" => array(), "type" => 1, "object-name" => "", "ratePlan" => array());
	$result["ratePlan"][1] = array();
	$result["ratePlan"][1]["name"] = "Основной тариф";
	$result["object-name"] = get_object($connect, $object, "place");
	$month = date("m");
	$year = date("Y");
	if($_POST["date"]){
		$date = explode("-", $_POST["date"]);
		$month = $date[0];
		$year = $date[1];
	}

	$months = array();

	$months[1]["month"] = (int)$month;
	$months[1]["year"] = $year;
	$months[1]["max-day"] = cal_days_in_month(CAL_GREGORIAN, $month, $year);
	$next_month = $month + 1;
	$next_year = $year;
	if($next_month > 12){
		$next_month = 1;
		$next_year++;
	}
	$months[2]["month"] = $next_month;
	$months[2]["year"] = $next_year;
	$months[2]["max-day"] = cal_days_in_month(CAL_GREGORIAN, $next_month, $next_year);

	$dates_price_object = array();
	$status_quota = $connect->getOne("SELECT check_places FROM object WHERE id=?i", $object);
	$result["type"] = $status_quota;

	if($status_quota == 3):
		$data = array();
	else:
		if($status_quota == 2){
			$data = $connect->getAll("SELECT id, start, end FROM date_price WHERE id_obj=?i AND active=0", $object);
			foreach($data as $row){
				$index = $row["id"];
				$dates_price_object[$index] = array();
				$dates_price_object[$index]["start"] = strToTime($row["start"]);
				$dates_price_object[$index]["end"] = strToTime($row["end"]);
				$dates_price_object[$index]["range"] = array();
				$data_range = $connect->getAll("SELECT id, name, place, type FROM ranges WHERE active=0 AND id_date=?i", $index);
				foreach($data_range as $row_range){
					$type_range = $row_range["type"];
					$place_row = $connect->getRow("SELECT name, type FROM place WHERE id=?i", $row_range["place"]);
					$type_place = "Основное";
					if($place_row["type"] == 2)
						$type_place = "Доп.";
					$index_range = $row_range["id"];
					$dates_price_object[$index]["range"][$index_range] = array();
					$dates_price_object[$index]["range"][$index_range]["name"] = $row_range["name"];
					$dates_price_object[$index]["range"][$index_range]["place"] = $type_place." ".$place_row["name"];
					$dates_price_object[$index]["range"][$index_range]["type-place"] = $place_row["type"];
					$dates_price_object[$index]["range"][$index_range]["type-range"] = $type_range;
				}
			}
		}

		$data = $connect->getAll("SELECT id, name, accessible_places, price_places, main_place, add_place, note, housing FROM room WHERE id_obj=?i AND accessible_places!=''", $object);

		foreach($data as $row){
			$room = $row["id"];
			$result["room"][$room] = array();
			$result["room"][$room]["name"] = $row["name"];
			$result["room"][$room]["main"] = $row["main_place"];
			$result["room"][$room]["add"] = $row["add_place"];
			if($row["housing"])
				$result["room"][$room]["name"].= " ".$connect->getOne("SELECT name FROM housing WHERE id=?i", $row["housing"]);
			$places = json_decode($row["accessible_places"], TRUE);
			$prices = json_decode($row["price_places"], TRUE);

			if(is_array($places)) {
              foreach($places as $index => $place){
                $start_place = $place["dt"];
                $days_place = $place["d"];
                $end_place = $start_place + $days_place * 86400;
                $places[$index]["end"] = $end_place;
              }
            }

			if($status_quota == 1){
				#Travelline
				foreach($prices as $ratePlan => $ratePlanPrice){
					foreach($ratePlanPrice as $index => $price){
						$start_place = $price["dt"];
						$days_place = $price["d"];
						$end_place = $start_place + $days_place * 86400;
						$prices[$ratePlan][$index]["end"] = $end_place;
					}
				}
			}elseif($status_quota == 2){
				#Sanata
				$prices[1] = array();
				foreach($dates_price_object as $index => $date_price){
					$prices[1][$index]["dt"] = $date_price["start"];
					$prices[1][$index]["end"] = $date_price["end"];
					$price_object = array();
					$name_price_object = array();
					foreach($date_price["range"] as $index_range => $range_price){
						$value_price = $connect->getOne("SELECT price FROM price WHERE id_range=?i AND id_room=?i AND active=0", $index_range, $room);
						if($value_price > 0){
							$length_price = count($price_object);
							$price_object[$length_price] = $value_price;
							$array_price = array("n" => $range_price["name"]." ".$range_price["place"], "t" => $range_price["type-place"], "p" => $range_price["type-range"]);
							$name_price_object[$length_price] = $array_price;
						}
					}
					$prices[1][$index]["p"] = $price_object;
					$prices[1][$index]["name"] = $name_price_object;
				}
			}

			$quota = array(
				$months[1]["month"] => array(),
				$months[2]["month"] => array()
			);
			$max_quota = 0;
			foreach($months as $month){
				$current_month = $month["month"];
				$current_year = $month["year"];
				$max_day = $month["max-day"];
				for($day = 1; $day <= $max_day; $day++){
					$quota[$current_year."-".$current_month][$day] = array("quota" => 0, "price" => array());
					$quota[$current_year."-".$current_month][$day]["date"] = $day.".".$current_month.".".$current_year;
					$current = strToTime($current_year."-".$current_month."-".$day);

					if(is_array($places)) {
                      foreach($places as $place){
                        $start_place = $place["dt"];
                        $end_place = $place["end"];
                        if($current >= $start_place AND $current < $end_place){
                          $quota[$current_year."-".$current_month][$day]["quota"] = $place["q"];
                          if($place["q"] > $max_quota){
                            if($place["q"] > 3)
                              $place["q"] = 3;
                            $max_quota = $place["q"];
                          }
                        }
                      }
                    }

					foreach($prices as $ratePlan => $ratePlanPrice){
						foreach($ratePlanPrice as $price){
							$start_place = $price["dt"];
							$end_place = $price["end"];
							if($current >= $start_place AND $current < $end_place){
								if(!isset($quota[$current_year."-".$current_month][$day]["price"][$ratePlan]))
									$quota[$current_year."-".$current_month][$day]["price"][$ratePlan] = array();
								$quota[$current_year."-".$current_month][$day]["price"][$ratePlan]["price"] = $price["p"];
								$quota[$current_year."-".$current_month][$day]["price"][$ratePlan]["name-price"] = "";
								if(isset($price["name"]))
									$quota[$current_year."-".$current_month][$day]["price"][$ratePlan]["name"] = $price["name"];
							}
						}
					}
				}
			}
			$result["room"][$room]["max-quota"] = $max_quota;
			$result["room"][$room]["quota"] = $quota;
		}
		$data = $connect->getAll("SELECT id, DATE_FORMAT(date_z, '%d.%m.%Y') as date FROM reckoning WHERE id_obj=?i AND id_user=?i AND (status<=3 OR status=9)", $object, $session_login);
		foreach($data as $row){
			$bid = $row["id"];
			$result["bid"][$bid] = array();
			$result["bid"][$bid]["date"] = $row["date"];
		}
		$data = $connect->getAll("SELECT id, name FROM rate_plan WHERE object=?i", $object);
		foreach($data as $row){
			$id = $row["id"];
			$result["ratePlan"][$id] = array();
			$result["ratePlan"][$id]["name"] = str_replace("\"", "", $row["name"]);
		}
	endif;


	return json_encode($result);
}

function booking_quota_room($connect){
	global $session_login;
	$data = json_decode($_POST["data"], TRUE);
	$object = $data["object"];
	$rest = array();
	if(!$object)
		return;

	foreach($data["turist"] as $turist){
		$surname = $turist["surname"];
		$name = $turist["name"];
		$otch = $turist["otch"];
		$email = $turist["email"];
		$telephone = $turist["telephone"];

      $sex = null;
      if(isset($turist['sex'])) {
        $sex = (int)$turist['sex'];
        if($sex !== 0 && $sex !== 1) {
          $sex = null;
        }
      }

		if($surname AND $name){
			if(!filter_var($email, FILTER_VALIDATE_EMAIL))
				$email = "";
            $original_data = [
              'surname' => $surname,
              'name' => $name,
              'otch' => $otch,
              'email' => $email,
              'telephone' => $telephone,
              'sex' => $turist
            ];

            if(is_null($sex))
                $connect->query("INSERT INTO klient(surname, name, otch, email, telephone, original_data) VALUES (?s, ?s, ?s, ?s, ?s, ?s)", $surname, $name, $otch, $email, $telephone, json_encode($original_data));
			else
			    $connect->query("INSERT INTO klient(surname, name, otch, sex, email, telephone, original_data) VALUES (?s, ?s, ?s, ?i, ?s, ?s, ?s)", $surname, $name, $otch, $sex, $email, $telephone, json_encode($original_data));

          $insert = $connect->insertId();
			$rest[] = $insert;
			if($turist["head"] == 1)
				$client = $insert;
		}
	}

	$today = date("Y-m-d");
	$row = $connect->getRow("SELECT reward, id_tour, add_one_day FROM object WHERE id=?i", $object);
	$reward = $row["reward"];
	$touroperator = $row["id_tour"];
	$add_one_day = (int)$row["add_one_day"];

	$connect->query("INSERT INTO reckoning(date, turist, id_obj, rest, number_turist, form_booking, id_user) VALUES (?s, ?i, ?i, ?s, ?i, 'quota', ?i)", $today, $client, $object, implode(",", $rest), count($rest), $session_login);
	$bid = $connect->insertId();

	if($touroperator)
		$connect->query("UPDATE reckoning SET id_tour=?i WHERE id=?i", $touroperator, $bid);

	foreach($data["room"] as $position){
		$room = $position["room"];
		$days = $position["days"];
		$number = $position["number"];
		$price = (int)$position["price"];
		$note = $position["note"];
		$ratePlan = $position["ratePlan"];
		$type_place = $position["type"];
		if($type_place == 3)
			$type_place = 2;
		$arrival = date("Y-m-d", strToTime($position["arrival"]));
		$connect->query("INSERT INTO position_reck(id_room, schet, days, date_z, number, sum, type, note, reward, ratePlan, add_one_day) VALUES (?i, ?i, ?i, ?s, ?i, ?s, ?i, ?s, ?s, ?s, ?s)", $room, $bid, $days, $arrival, $number, $price, $type_place, $note, $reward, $ratePlan, $add_one_day);
		if($ratePlan > 0){
			$last_rate_plan = $connect->insertId();
		}else
			$last_add_place = $connect->insertId();
	}
	if($last_add_place != "")
		$connect->query("UPDATE position_reck SET add_place=?i WHERE id=?i", $last_rate_plan, $last_add_place);

	if($connect->getOne("SELECT id FROM object WHERE id=?i AND (check_places=1 OR check_places=2)", $object))
		$connect->query("INSERT INTO booking(bid) VALUES (?i)", $bid);
	change_arrival_date($connect, $bid);
	recalculation_sum($connect, $bid);
	save_schet_to_history($connect, $bid, "Бронирование из квоты мест");
	setCookie("reck", $bid);
	return json_encode($client);

}

function booking_quota_room_add_bid($connect){
	global $session_login;
	$room = json_decode($_POST["room"], TRUE);
	$bid = $_POST["bid"];
	if(!$bid)
		return;
	$object = $connect->getOne("SELECT id_obj FROM reckoning WHERE id=?i", $bid);
	$row = $connect->getRow("SELECT reward, add_one_day FROM object WHERE id=?i", $object);
	$reward = $row["reward"];
	$add_one_day = (int)$row["add_one_day"];
	$last_rate_plan = "";
	$last_add_place = "";

	foreach($room as $position){
		$room = $position["room"];
		$days = $position["days"];
		$number = $position["number"];
		$price = (int)$position["price"];
		$note = $position["note"];
		$ratePlan = $position["ratePlan"];
		$type_place = $position["type"];
		if($type_place == 3)
			$type_place = 2;
		$arrival = date("Y-m-d", strToTime($position["arrival"]));
		$connect->query("INSERT INTO position_reck(id_room, schet, days, date_z, number, sum, type, note, reward, ratePlan, add_one_day) VALUES (?i, ?i, ?i, ?s, ?i, ?s, ?i, ?s, ?s, ?s, ?s)", $room, $bid, $days, $arrival, $number, $price, $type_place, $note, $reward, $ratePlan, $add_one_day);
		if($ratePlan > 0){
			$last_rate_plan = $connect->insertId();
		}else
			$last_add_place = $connect->insertId();
	}
	if($last_add_place != "")
		$connect->query("UPDATE position_reck SET add_place=?i WHERE id=?i", $last_rate_plan, $last_add_place);

	$connect->query("UPDATE reckoning SET form_booking='quota' WHERE id=?i", $bid);
	if($connect->getOne("SELECT id FROM object WHERE id=?i AND (check_places=1 OR check_places=2)", $object)){
		$booking = $connect->getOne("SELECT id FROM booking WHERE bid=?i", $bid);
		if(!$booking)
			$connect->query("INSERT INTO booking(bid) VALUES (?i)", $bid);
		else
			$connect->query("UPDATE booking SET update_bid=1, confirm=0, status='modified' WHERE id=?i", $booking);
	}
	change_arrival_date($connect, $bid);
	recalculation_sum($connect, $bid);
	save_schet_to_history($connect, $bid, "Бронирование из квоты мест");
	setCookie("reck", $bid);
	$client = $connect->getOne("SELECT turist FROM reckoning WHERE id=?i", $bid);
	return json_encode($client);
}

function view_calendar_rooms($connect){
	$object = $_POST["id"];
	$region = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $object);
	$data = $connect->getAll("SELECT id, name, housing, note FROM room WHERE id_obj=?i AND active=0", $object);
	ob_start();
?>
	<button style="margin-bottom: 5px" type="button" class="btn btn-warning btn-xs" onclick="search_object_reservation()"><i class="fa fa-angle-double-left"></i> вернуться назад</button>
<?php
	foreach($data as $room){
		$id_room = $room["id"];
		if($connect->getOne("SELECT COUNT(*) FROM object_room WHERE active=0 AND id_category=?i", $id_room)){
			$image = select_image($region, $object, $id_room, 1);
			$housing = "";
			if($room["housing"])
				$housing = $connect->getOne("SELECT name FROM housing WHERE id=?i", $room["housing"]);
?>
	<div class="form-group form-group-bottom well well-sm room-block-<?php echo $id_room; ?>" style="margin: 0 0 10px 0">
		<div class="col-sm-1">
			<img src="<?php echo $image; ?>" class="img-small thumbnail" />
		</div>
		<div class="col-sm-6">
			<?php echo $room["name"]; ?>
			<address><?php echo $housing." ".$room["note"]; ?></address>
		</div>
		<div class="col-sm-5">
			<button type="button" class="btn btn-success btn-xs btn-see-room" onclick="show_calendar_room('<?php echo $id_room; ?>')"><i class="fa fa-angle-double-down"></i> Смотреть места</button>
			<button type="button" class="btn btn-info btn-xs btn-update-room" style="display: none" onclick="show_calendar_room('<?php echo $id_room; ?>')"><i class="fa fa-refresh"></i> Обновить</button>
			<button type="button" class="btn btn-danger btn-xs btn-update-room" style="display: none" onclick="remove_calendar_room('<?php echo $id_room; ?>')"><i class="fa fa-times-circle"></i> Закрыть</button>
		</div>
		<div style="clear: both"></div>
		<div class="calendar-block" style="position: relative; width: 950px;"></div>
	</div>
<?php
		}
	}
?>
	<button type="button" class="btn btn-success btn-sm" onclick="upload_reservation_object('<?php echo $id; ?>')"><i class="fa fa-cloud-upload"></i> Загрузить</button>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_calendar_room($connect){
	global $array_month, $id_rights;
	$id = $_POST["id"];
	$type_day = $connect->getOne("SELECT add_one_day FROM object WHERE id=?i", $id);
	$html = "";
	$month = date("m") * 1;
	$year = date("Y");
	$object = $connect->getOne("SELECT id_obj FROM room WHERE id=?i", $id);
	if($object == 23 OR $object == 16 OR $object == 5 OR $object == 39 OR $object == 26 OR $object == 32)
		$month = 12;
	$max_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
	$start_date = $year."-".$month."-1";
	$end_date = $year."-".$month."-".$max_day;
?>
	<div class="div-calendar">
		<table class="calendar-table">
		<thead>
		<tr class="head-calendar">
			<th colspan="<?php echo ($max_day * 2); ?>" class="month-<?php echo $month; ?>"><?php echo $array_month[$month]; ?></th>
		</tr>
		</thead>
		<tbody>
<?php
	$row = $connect->getRow("SELECT id, name FROM room WHERE id=?i AND active=0", $id);
	$category = $row["id"];
	$array = select_reservation_calendar($connect, $category, $month, $year, $max_day, 1);
	foreach($array["room"] as $id_room => $room){
		$number = $connect->getOne("SELECT number FROM object_room WHERE id=?i", $id_room);
		if($id_rights == 5)
			$number.= " id - ".$id_room;
?>
		<tr class="tr-range tr-<?php echo $id_room; ?> head-tr" name="<?php echo $id_room; ?>">
			<td class="head-col text-right"><?php echo $number; ?></td>
			<?php echo $room["head"]; ?>
		</tr>
		<tr class="tr-range tr-<?php echo $id_room; ?> add-tr" name="<?php echo $id_room; ?>">
			<td class="head-col"></td>
			<?php echo $room["add"]; ?>
		</tr>
<?php
	}
?>
		<tr class="tr-<?php echo $id_room; ?> append-room-tr">
			<td class="head-col"></td>
		<?php for($u = 1; $u <= $max_day; $u++){ ?>
			<td class="calendar-td" colspan="2" style="padding: 0"></td>
		<?php } ?>
		</tr>
		</tbody>
		</table>
	</div>
<?php
}

function append_calendar_room($connect){
	global $array_month;
	$data = $connect->getAll("SELECT id, color FROM status_reservation");
	foreach($data as $row)
		$status_color[$row["id"]] = $row["color"];
	$id_category = $_POST["id"];
	$date = $_POST["date"];
	$array = explode("-", $date);
	$year = $array[1];
	$month = $array[0] * 1;
	$max_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
	$start_date = $year."-".$month."-1";
	$end_date = $year."-".$month."-".$max_day;
	$array = select_reservation_calendar($connect, $id_category, $month, $year, $max_day);
	$array["head"] = "<th colspan='".($max_day * 2 + $add + 1)."' class='th-month' date='".$month."-".$year."'>".$array_month[$month]."</th>";
	for($u = 1; $u <= $max_day; $u++)
		$array["room"][$id_room]["append-tr"].= "<td class='calendar-td' colspan='2' style='padding: 0'></td>";
	return json_encode($array);
}

function select_reservation_calendar($connect, $category, $month, $year, $max_day, $is_first){
	$data = $connect->getAll("SELECT id, color FROM status_reservation");
	foreach($data as $row)
		$status_color[$row["id"]] = $row["color"];
	$array = array();
	$next_month = $month + 1;
	$next_year = $year;
	if($next_month > 12){
		$next_month = 1;
		$next_year++;
	}
	$data = $connect->getAll("SELECT id, number, note, on_sale FROM object_room WHERE active=0 AND id_category=?i ORDER BY number", $category);
	$rowspan_append = count($data) * 2;
	foreach($data as $row){
		$id_room = $row["id"];
		$check_rowspan = 0;
		if($connect->getOne("SELECT id FROM reservation WHERE room=?i AND date>=?s AND active=0 AND type_place=2", $id_room, date("Y-m-1")))
			$check_rowspan = 1;
		$rowspan = "";
		if($check_rowspan == 1)
			$rowspan = " rowspan='2' ";
		$on_sale = json_decode($row["on_sale"], TRUE);
		$append_tr = "";
		$raz = 1;
		$sale_array = array();
		foreach($on_sale[$month."-".$year] as $sale){
			$index = count($sale_array);
			$sale_array[$index]["start"] = strTotime($year."-".$month."-".$sale["d"]);
			$sale_array[$index]["end"] = strTotime($year."-".$month."-".($sale["d"] + $sale["n"] - 1));
		}
		if(($raz > 1 OR ($raz == 1 AND $check_i == 1)) AND $raz <= $max_day)
			$raz++;
		$check_i = 0;
		for($i = 1; $i <= $max_day; $i++){
			$name = "";
			$color = "";
			$colspan = "";
			$class = "";
			$text = "";
			$set_id = "";
			$attr = "";
			$append_reserv = "";
			$date = $year."-".$month."-".$i;
			$row = $connect->getRow("SELECT id, day, date, type_place, id_reck FROM reservation WHERE room=?i AND date=?s AND active=0", $id_room, $date);
			if($row["id"]){
				$attr = " reserv='".$row["id"]."' ";
				$color = " style='background: ".$status_color[check_status_reckoning($connect, $row["id_reck"])]."' ";
				$colspan = " colspan='".($row["day"] * 2)."' ";
				$class = "td-reserv";
				$name = " name='reserv' ";
				if($row["type_place"] == 2){
					$rowspan = "";
					$date2 = date("Y-m-d", strToTime($row["date"]) + ($row["day"] - 1) * 86400);
					$data_rows = $connect->getAll("SELECT id, day, date, id_reck FROM reservation WHERE room=?i AND date>=?s AND date<=?s AND active=0 AND type_place=2 AND id!=?i ORDER BY date", $id_room, $row["date"], $date2, $row["id"]);
					$date_timestamp = strTotime($row["date"]);
					$add_day = 0;
					foreach($data_rows as $row2){
						$date_timestamp2 = strTotime($row2["date"]);
						$attr2 = " reserv='".$row2["id"]."' ";
						$color2 = " style='background: ".$status_color[check_status_reckoning($connect, $row2["id_reck"])]."' ";
						$colspan2 = " colspan='".($row2["day"] * 2)."' ";
						$class2 = "td-reserv";
						$name2 = " name='reserv' ";
						$text = get_html_reservation($connect, $row2["id"]);
						$append_reserv = "<td class='calendar-td ".$class2."' date='".$row2["date"]."' ".$color2.$colspan2.$attr2.$name2.">".$text."</td>";
						$day_prepend = ($date_timestamp2 - $date_timestamp) / 86400;
						$date2 = date("Y-m-d", strToTime($date) + $add_day * 86400);
						$e = 1;
						while(!$connect->getOne("SELECT id FROM reservation WHERE room=?i AND date<=?s AND DATE_ADD(date, INTERVAL day DAY)>?s AND active=0 AND id!=?i", $id_room, $date2, $date2, $row["id"]) AND $e <= $day_prepend){
							$y = explode("-", $date2);
							$append_tr.= "<td class='calendar-td td-on-sale ui-selectee' name='on-sale' date='".$date2."' colspan='2'>&nbsp;</td>";
							$e++;
							$date2 = date("Y-m-d", strToTime($date) + ($e + $add_day - 1) * 86400);
						}
						$add_day+= $e - 1;
						$add_day+= $row2["day"];
						$append_tr.= $append_reserv;
						$day_append = ($date_timestamp - $date_timestamp2) / 86400 + $row["day"] - $row2["day"];
						$date2 = date("Y-m-d", strToTime($date) + $add_day * 86400);
						$e = 1;
						while(!$connect->getOne("SELECT id FROM reservation WHERE room=?i AND date<=?s AND DATE_ADD(date, INTERVAL day DAY)>?s AND active=0 AND id!=?i", $id_room, $date2, $date2, $row["id"]) AND $e <= $day_append){
							$append_tr.= "<td class='calendar-td td-on-sale ui-selectee' name='on-sale' date='".$date2."' colspan='2'>&nbsp;</td>";
							$e++;
							$date2 = date("Y-m-d", strToTime($date) + ($e + $add_day - 1) * 86400);
						}
						$add_day+= $e - 1;
					}
					if(!count($data_rows)){
						for($e = 1; $e <= $row["day"]; $e++){
							$date2 = date("Y-m-d", strToTime($date) + ($e - 1) * 86400);
							if(!$connect->getOne("SELECT id FROM reservation WHERE room=?i AND date<=?s AND DATE_ADD(date, INTERVAL day DAY)>?s AND active=0 AND id!=?i", $id_room, $date2, $date2, $row["id"])){
								$append_tr.= "<td class='calendar-td td-on-sale ui-selectee' name='on-sale' date='".$date2."' colspan='2'>&nbsp;</td>";
							}
						}
					}
				}
				$text = get_html_reservation($connect, $row["id"]);
				$old_i = $i;
				$i+= $row["day"] - 1;
				if($i > $max_day){
					$raz = $i - $max_day;
					$check_i = 1;
				}
			}elseif($connect->getOne("SELECT id FROM reservation WHERE room=?i AND date<=?s AND DATE_ADD(date, INTERVAL day DAY)>?s AND active=0", $id_room, $date, $date)){
				$rowspan = "";
				$class = " td-on-sale ";
				$name = " name='on-sale' ".$date.$id_room;
			}
//			if(!$color AND $check_rowspan == 1 AND $connect->getOne("SELECT id FROM reservation WHERE room=?i AND date<=?s AND DATE_ADD(date, INTERVAL day DAY)>?s AND active=0", $id_room, $date, $date)){
//				$append_tr.= "<td class='calendar-td td-on-sale ui-selectee' name='on-sale' date='".$date."' colspan='2'></td>";
//			}
			if(!$color){
				$c_date = strToTime($date);
				foreach($sale_array as $sale){
					if($sale["start"] <= $c_date AND $sale["end"] >= $c_date){
						$name = " name='on-sale' ";
						$class = " td-on-sale ";
					}
				}
			}
			if(!$colspan)
				$colspan = " colspan='2' ";
			if($color OR !$connect->getOne("SELECT id FROM reservation WHERE room=?i AND date<=?s AND DATE_ADD(date, INTERVAL day DAY)>?s AND active=0", $id_room, $date, $date) OR $is_first)
				$array["room"][$id_room]["head"].= "<td class='calendar-td ".$class."' date='".$date."' ".$name.$color.$colspan.$rowspan.$attr."><span>".$i."</span>".$text."</td>";
			$rowspan = "";
			if($check_rowspan == 1)
				$rowspan = " rowspan='2' ";
		}
		//if($first_tr != 1){
		$array["room"][$id_room]["head"].= "<td class='append-td' rowspan='".$rowspan_append."' date='".$next_month."-".$next_year."'></td>";
		//	$first_tr = 1;
		//}
		$array["room"][$id_room]["add"] = $append_tr;
	}
	return $array;
}

function save_new_reservation($connect){
	$room = $_POST["room"];
	$date = $_POST["date"];
	$day = $_POST["day"];
	$type_place = check_reservation_date($connect, $room, $date, $day);
	if($type_place){
		$connect->query("INSERT INTO reservation(room, date, day, status, type_place) VALUES (?i, ?s, ?i, 2, ?i)", $room, $date, $day, $type_place);
		$id = $connect->insertId();
		save_reservation_history($connect, $id, "Новая заявка");
		clear_sale_calendar_object($connect, $id);
		$data = array();
		$data["id"] = $id;
		$data["type"] = $type_place;
		return json_encode($data);
	}
}

function edit_reservation($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id_reck, note, status, sum, service_note, type_place, room, date, day FROM reservation WHERE id=?i", $id);
	$status = $row["status"];
	$select = array();
	$select[$row["type_place"]] = " SELECTED ";
	$date = $row["date"];
	$date_end = date("Y-m-d", strToTime($date) + ($row["day"] - 1) *  86400);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить информацию</h4>
			</div>
			<div class="modal-body form-horizontal add-note">
				<?php if($status != 1){ ?>
				<div class="form-group">
					<label class="col-sm-4 control-label">№ заявки</label>
					<div class="col-sm-8">
						<input type="text" class="form-control id" value="<?php echo $row['id_reck']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Место</label>
					<div class="col-sm-8">
						<select class="form-control type-place">
							<?php if(!$connect->getOne("SELECT id FROM reservation WHERE room=?i AND id!=?i AND ((date>=?s AND date<=?s) OR  (DATE_ADD(date, INTERVAL day DAY)>?s AND DATE_ADD(date, INTERVAL day DAY)<?s) OR (date<=?s AND DATE_ADD(date, INTERVAL day DAY)>=?s)) AND active=0", $row["room"], $id, $date, $date_end, $date, $date_end, $date, $date_end)){ ?>
								<option value="1" <?php echo $select["1"]; ?>>Весь номер</option>
							<?php } ?>
							<option value="2" <?php echo $select["2"]; ?>>Место в номере</option>
						</select>
					</div>
				</div>
				<?php } ?>
				<?php if($status == 1){ ?>
				<div class="form-group">
					<label class="col-sm-4 control-label">Стоимость путевки</label>
					<div class="col-sm-8">
						<input type="text" class="form-control sum" value="<?php echo $row['sum']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Примечание для туристов</label>
					<div class="col-sm-8">
						<input type="text" class="form-control note" value="<?php echo $row['note']; ?>" />
					</div>
				</div>
				<?php } ?>
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Служебное примечание</label>
					<div class="col-sm-8">
						<input type="text" class="form-control service-note" value="<?php echo $row['service_note']; ?>" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_reservation('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_reservation($connect){
	$id = $_POST["id"];
	$id_reck = $_POST["id_reck"];
	$sum = $_POST["sum"];
	$note = $_POST["note"];
	$service_note = $_POST["service_note"];
	$type_place = $_POST["type_place"];
	$status = $connect->getOne("SELECT status FROM reservation WHERE id=?i", $id);
	if($status == 1)
		$connect->query("UPDATE reservation SET note=?s, service_note=?s, sum=?s WHERE id=?i", $note, $service_note, $sum, $id);
	else{
		$connect->query("UPDATE reservation SET service_note=?s WHERE id=?i", $service_note, $id);
		if($id_reck)
			$connect->query("UPDATE reservation SET id_reck=?i WHERE id=?i", $id_reck, $id);
	}
	save_reservation_history($connect, $id, "Изменена информация");
	if($status != 1 AND $connect->getOne("SELECT type_place FROM reservation WHERE id=?i", $id) != $type_place)
		$connect->query("UPDATE reservation SET type_place=?i WHERE id=?i", $type_place, $id);
	$room = $connect->getOne("SELECT room FROM reservation WHERE id=?i", $id);
	return $connect->getOne("SELECT id_category FROM object_room WHERE id=?i", $room);
}

function set_note_to_object_room(){
	$id = $_POST["id"];
	$date = $_POST["date"];
	ob_start();
?>
<div class="form-horizontal add-note">
	<div class="form-group">
		<label class="col-sm-4 control-label">Стоимость</label>
		<div class="col-sm-8">
			<input type="text" class="form-control sum" />
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-4 control-label">Примечание</label>
		<div class="col-sm-8">
			<input type="text" class="form-control note" />
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-6 col-sm-6">
			<button type="button" class="btn btn-success btn-sm" onclick="save_note_to_object_room('<?php echo $id; ?>', '<?php echo $date; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_info_window_calendar($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id_reck, note, service_note, sum FROM reservation WHERE id=?i", $id);
	$id_reck = $row["id_reck"];
	$status = check_status_reckoning($connect, $row["id_reck"]);
	$sum = $row["sum"];
	$note = $row["note"];
	$service_note = $row["service_note"];
	$row = $connect->getRow("SELECT id, turist, agency, sum, id_obj, id_user FROM reckoning WHERE id=?i", $id_reck);
	if($row["turist"])
		$turist = select_name_klient($connect, $row["turist"]);
	else
		$turist = $connect->getOne("SELECT name FROM agency WHERE id=?i", $row["agency"]);
	$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
	ob_start();
?>
	<?php if($row["id"] AND $status != 1){ ?>
		<span>Заявка <?php echo $id_reck; ?></span>
		<span>Менеджер: <?php echo $manager; ?></span>
		<span>Объект: <?php echo get_object($connect, $row["id_obj"]); ?></span>
		<span>Сумма путевки: <?php echo add_null($row["sum"]); ?></span>
		<span>Турист: <?php echo $turist; ?></span>
		<hr />
	<?php }elseif($status == 1){ ?>
		<span>Стоимость путевки: <?php echo $sum; ?></span>
		<span>Примечание туристам: <?php echo $note; ?></span>
		<span>Служебное примечание: <?php echo $service_note; ?></span>
		<hr />
	<?php } ?>
	<span onclick="change_date_reservation('<?php echo $id; ?>')"><i class="fa fa-arrows"></i> Изменить даты</span>
	<span onclick="edit_reservation('<?php echo $id; ?>')"><i class="fa fa-pencil"></i> Изменить информацию</span>
	<span onclick="show_history_reservation('<?php echo $id; ?>')"><i class="fa fa-history"></i> Смотреть историю</span>
	<span onclick="deferred_reservation('<?php echo $id; ?>')"><i class="fa fa-reply"></i> В отложенные</span>
	<span onclick="delete_reservation('<?php echo $id; ?>')"><i class="fa fa-trash"></i> Аннулировать</span>

<?php
	$html = ob_get_clean();
	return $html;
}

function show_history_reservation($connect){
	$id = $_POST["id"];
?>
<div class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">История заезда</h4>
			</div>
			<div class="modal-body form-horizontal">
				<table class="table table-condensed table-border">
				<tr>
					<th>Время</th>
					<th>Менеджер</th>
					<th>Статус</th>
					<th>Примечание</th>
				</tr>
<?php
	$data = $connect->getAll("SELECT DATE_FORMAT(time, '%H:%i:%s %d.%m.%Y') as date, id_user, status, note FROM history_reservation WHERE id_reserv=?i ORDER BY time", $id);
	foreach($data as $row){
	?>
				<tr>
					<td style="width: 25%"><?php echo $row["date"]; ?></td>
					<td style="width: 15%"><?php echo $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]); ?></td>
					<td style="width: 20%"><?php echo $connect->getOne("SELECT name FROM status_reservation WHERE id=?i", $row["status"]); ?></td>
					<td style="width: 40%"><?php echo $row["note"]; ?></td>
				</tr>
	<?php
	}
?>
				</table>
			</div>
		</div>
	</div>
</div>
<?php
}

function paint_reservation($connect){
	$id = $_POST["id"];
	$data = array();
	$row = $connect->getRow("SELECT room, status FROM reservation WHERE id=?i", $id);
	$data["room"] = $row["room"];
	$data["color"] = $connect->getOne("SELECT color FROM status_reservation WHERE id=?i", $row["status"]);
	return json_encode($data);
}

function change_date_reservation($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT date, day FROM reservation WHERE id=?i", $id);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить даты заезда</h4>
			</div>
			<div class="modal-body form-horizontal change-date">
				<div class="form-group">
					<label class="col-sm-4 control-label">Заезд</label>
					<div class="col-sm-8">
						<input type="date" class="form-control date" value="<?php echo $row['date']; ?>" />
					</div>
				</div>
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Дней</label>
					<div class="col-sm-8">
						<input type="text" class="form-control day" value="<?php echo $row['day']; ?>" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_date_reservation('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_date_reservation($connect){
	global $session_login;
	$id = $_POST["id"];
	$date = $_POST["date"];
	$day = $_POST["day"];
	$room = $connect->getOne("SELECT room FROM reservation WHERE id=?i", $id);
	$type_place_reserv = $connect->getOne("SELECT type_place FROM reservation WHERE id=?i", $id);
	$connect->query("UPDATE reservation SET active=1 WHERE id=?i", $id);
	$type_place = check_reservation_date($connect, $room, $date, $day, $id);
	if($type_place OR $session_login == 2){
		if($type_place == 2 AND $type_place_reserv == 1)
			$connect->query("UPDATE reservation SET type_place=2 WHERE id=?i", $id);
		$row = $connect->getRow("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date, day FROM reservation WHERE id=?i", $id);
		$connect->query("UPDATE reservation SET date=?s, day=?i WHERE id=?i", $date, $day, $id);
		save_reservation_history($connect, $id, "Изменение дат заезда. Были: заезд ".$row["date"].", дней ".$row["day"]);
		clear_sale_calendar_object($connect, $id);
		$connect->query("UPDATE reservation SET active=0 WHERE id=?i", $id);
		$data = array();
		$data["room"] = $connect->getOne("SELECT id_category FROM object_room WHERE id=?i", $room);
		$data["type"] = $connect->getOne("SELECT type_place FROM reservation WHERE id=?i", $id);
		$data["html"] = get_html_reservation($connect, $id);
		return json_encode($data);
	}
	$connect->query("UPDATE reservation SET active=0 WHERE id=?i", $id);
	return json_encode("");
}

function get_html_reservation($connect, $id){
	$html = "";
	$row = $connect->getRow("SELECT id_reck, service_note, status FROM reservation WHERE id=?i", $id);
	//if($row["status"] == 1)
		//$html = $row["service_note"];
	//else{
	if($row["id_reck"])
		$html = "№".$row["id_reck"];
	if($row["service_note"])
		$html.= " ".$row["service_note"];
		//if($row["service_note"])
		//	$html.= " ".$row["service_note"];
	//}
	if(!$html)
		$html = "&nbsp;";
	return $html;
}

function delete_reservation($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reservation SET active=1 WHERE id=?i", $id);
	save_reservation_history($connect, $id, "В архив");
	$data = $connect->getRow("SELECT room, date FROM reservation WHERE id=?i", $id);
	$data["room"] = $connect->getOne("SELECT id_category FROM object_room WHERE id=?i", $data["room"]);
	return json_encode($data);
}

function deferred_reservation($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reservation SET id_reck='' WHERE id=?i", $id);
	save_reservation_history($connect, $id, "В отложенные");
}

function check_status_reckoning($connect, $id){
	$row = $connect->getRow("SELECT id, status, status_san FROM reckoning WHERE id=?i", $id);
	if(!$row["id"])
		return 1;
	$status = $row["status"];
	$status_san = $row["status_san"];
	if($status_san == 1)
		return 6;
	if($status_san == 4 OR $status_san == 5)
		return 7;
	if($status == 3)
		return 3;
	if($status == 4)
		return 4;
	if($status == 5)
		return 5;
	return 2;
}

?>
