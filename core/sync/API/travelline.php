<?php

function update_room_places_quota($connect, $data){
	$update = $data["update"] ?? [];

	foreach($update as $room => $update_places){
		$connect->query("UPDATE room SET accessible_places=?s, synchronized = 0 WHERE id=?i", $update_places, $room);
	}
}

function update_room_prices_quota($connect, $data){
	$update = $data["update"] ?? [];
	foreach($update as $room => $update_price){
		$connect->query("UPDATE room SET price_places=?s, synchronized = 0 WHERE id=?i", $update_price, $room);
	}
}

function check_new_update_booking($connect){
	global $profkurort_sync;

	$bookings = array("check" => 0, "bookings" => array());
	$data = $connect->getAll("SELECT id, bid, created, status FROM booking WHERE update_bid=1 AND bid!=''");
	foreach($data as $row){
		$id = $row["id"];
		$bid = $row["bid"];
		$status = $row["status"];
		$reckoning = $connect->getRow("SELECT turist, id_obj, rest, sum FROM reckoning WHERE id=?i", $bid);
		$turist = $reckoning["turist"];
		$id_obj = $reckoning["id_obj"];
		$rest = explode(",", $reckoning["rest"]);
		$rest = array_diff($rest, array(""));
		$objectRow = $connect->getRow("SELECT check_places, reward FROM object WHERE id=?i", $id_obj);

		$check_places = $objectRow ? $objectRow['check_places'] : null;
		$reward = $objectRow ? $objectRow['reward'] : 0;

		if($check_places == 1 OR $check_places == 2){

			$booking = array();
			$booking["number"] = $id;
			$booking["created"] = $row["created"];
			$booking["status"] = $status;
			$guests = array();
			foreach($rest as $tur){
				$tur_info = $connect->getRow("SELECT surname, name, otch, `date` FROM klient WHERE id=?i", $tur);
				$guest = array();
				$guest["firstName"] = $tur_info["name"];
				$guest["lastName"] = $tur_info["surname"];
				$guest["middleName"] = $tur_info["otch"];
				$guest["email"] = "";
				$guest["phone"] = "";

				$isChild = false;

				try {
					if($tur_info['date'] != '0000-00-00') {
						$birthday = new DateTime($tur_info['date']);
						$interval = $birthday->diff(new DateTime);
						$isChild = $interval->y < 18;
					}
				} catch (Throwable $e) {

				}

				$guest["isChild"] = $isChild;

				/*if($isChild) {
					$booking['children']++;
				} else {
					$booking['adults']++;
				}*/

				$guests[] = $guest;
			}
			$booking["hotelId"] = $id_obj;
			$booking["currencyCode"] = "RUB";
			$booking["paymentMethod"] = "CREDIT";
			$booking["paymentMethodComment"] = "По договору " . $id;
			$object = $connect->getRow("SELECT arrival, leaving FROM object WHERE id=?i", $id_obj);
			$booking["arrivalTime"] = str_replace([".","-"], ":", $object["arrival"]);
			$booking["departureTime"] = str_replace([".","-"], ":", $object["leaving"]);
			$booking["roomStays"] = array();
			$positions = $connect->getAll("SELECT id, id_room, number, sum, date_z, days, reward, ratePlan FROM position_reck WHERE ratePlan>0 AND schet=?i", $bid);
			foreach($positions as $position){
				$id_position = $position["id"];
				$number = $position["number"];
				$room = array();
				$room["roomTypeId"] = $position["id_room"];
				$room["ratePlanId"] = $position["ratePlan"];
				$room["adults"] = $number;
				$room["children"] = 0;
				$room["commission"] = get_reward_schet_position($connect, $id_position);
				$room["bookingPerDayPrices"] = array();
				$timestamp = strToTime($position["date_z"]);
				$price = $position["sum"] * $number;

				$room['total'] = [
					"amountAfterTaxes" => 0
				];

				for($i = 1; $i <= $position["days"]; $i++){
					$date_price = array();
					$date = date("Y-m-d", $timestamp);
					$date_price["dateYmd"] = $date;
					$date_price["price"] = $price;
					$timestamp+= 86400;
					$room["bookingPerDayPrices"][] = $date_price;
					$room['total']["amountAfterTaxes"] += $price;
				}

				$room["guests"] = array();
				$copy_guests = $guests;
				$check = 0;
				foreach($copy_guests as $index => $guest){
					$check++;
					if($number < $check)
						break;
					$room["guests"][] = $guest;
					unset($guests[$index]);
				}

				$room["services"] = array();
				$add_places = $connect->getAll("SELECT number, sum, note FROM position_reck WHERE add_place=?i AND schet=?i", $id_position, $bid);
				foreach($add_places as $add_place){
					$services = array();
					$services["name"] = $add_place["note"];
					$services["price"] = $add_place["sum"];
					$room["services"][] = $services;
					$room["adults"]+= $add_place["number"];
				}
				$booking["roomStays"][] = $room;
			}
			$booking["customer"] = array();
			$customer = $connect->getRow("SELECT surname, name, otch FROM klient WHERE id=?i", $turist);
			$booking["customer"]["firstName"] = $customer["name"];
			$booking["customer"]["lastName"] = $customer["surname"];
			$booking["customer"]["middleName"] = $customer["otch"];
			$booking["customer"]["email"] = "";
			$booking["customer"]["phone"] = "";

			$bookings["bookings"][] = $booking;
			$bookings["check"] = 1;

		}elseif($check_places == 3){

			$sync_object = $connect->getOne("SELECT sync_id FROM object WHERE id=?i", $id_obj);
			$categs = array();
			$clidata = array();
			$Suppdata = array();

			$index = 0;
			$positions = $connect->getAll("SELECT id, id_room, number, sum, date_z, days, reward, ratePlan FROM position_reck WHERE ratePlan>0 AND schet=?i", $bid);
			foreach($positions as $position){
				$id_position = $position["id"];
				$number = $position["number"];
				$sync_room = $connect->getOne("SELECT sync_id FROM room WHERE id=?i", $position["id_room"]);
				if($sync_room){
					$index++;
					$room = array();
					$room["Catcod"] = $sync_room;
					$room["rooms0"] = 1;
					$room["Roomnr"] = $index;
					$categs[] = $room;
				}
			}

			$rest = explode(",", $connect->getOne("SELECT rest FROM reckoning WHERE id=?i", $bid));
			$rest = array_diff($rest, array(""));
			foreach($rest as $turist){
				$turist_info = $connect->getRow("SELECT surname, name, otch FROM klient WHERE id=?i", $turist);
				$guest = array();
				$guest["clinam1"] = $turist_info["name"];
				$guest["clinam2"] = $turist_info["surname"];
				$guest["clinam3"] = $turist_info["otch"];
				$guest["Catcod"] = 0;
				$guest["Sexcod"] = 1;
				$guest["Agecod"] = 1;
				$guest["Countintcod"] = 643;
				$guest["Dopplacefl"] = 0;
				$guest["Roomnr"] = 1;
				$guest["Progid"] = 0;
				$guest["Enablefl"] = 1;
				$clidata[] = $guest;
			}

			$categs = json_encode($categs);
			$clidata = json_encode($clidata);
			$Suppdata = json_encode($Suppdata);

			$row = $connect->getRow("SELECT date_z, date_v FROM reckoning WHERE id=?i", $bid);
			$arrival = $row["date_z"];
			$leaving = $row["date_v"];

			$row = $connect->getRow("SELECT arrival, leaving FROM object WHERE id=?i", $object);
			if($object["arrival"]){
				$arrival.= " ".str_replace([".","-"], ":", $row["arrival"]);
				$leaving.= " ".str_replace([".","-"], ":", $row["leaving"]);
			}

			if($status == "new")
				$data = $profkurort_sync->create_booking($sync_object, $arrival, $leaving, $categs, $clidata, $Suppdata);
			else{
				$id_profkurort = $connect->getOne("SELECT id_travelline FROM booking WHERE id=?i", $id);
				if($id_profkurort)
					$data = $profkurort_sync->update_booking($sync_object, $arrival, $leaving, $id_profkurort, $clidata);
			}
			$connect->query("UPDATE booking SET update_bid=0 WHERE id=?i", $id);

			if(($status == "new" AND $data["ref"] > 0) OR ($status == "modified" AND $data["ref"] == 1)){
				if($status == "new")
					$connect->query("UPDATE booking SET confirm=1, id_travelline=?s WHERE id=?i", $data["ref"], $id);
				else
					$connect->query("UPDATE booking SET confirm=1 WHERE id=?i", $id);
				$manager = $connect->getOne("SELECT id_user FROM reckoning WHERE id=?i", $bid);
				if($manager){
					$text = "Заявка №".$bid." подтверждена Профкурортом";
					save_notification($connect, $text, $manager);
				}
			}

		}

	}

	$data = $connect->getAll("SELECT id, booking_object, status FROM booking WHERE update_bid=1 AND booking_object!='' AND status='new'");
	foreach($data as $row){
		$id = $row["id"];
		$bid = $row["booking_object"];
		$status = $row["status"];
		$reckoning = $connect->getRow("SELECT object FROM booking_request_object_module WHERE id=?i", $bid);
		$object = $reckoning["object"];
		$check_places = $connect->getOne("SELECT check_places FROM object WHERE id=?i", $object);

		if($check_places == 2){

			$booking = array();
			$booking["number"] = $id;
			$booking["status"] = $status;
			$booking["hotelId"] = $object;
			$booking["roomStays"] = array();
			$positions = $connect->getAll("SELECT room, number, arrival, days FROM booking_request_object_module_position WHERE booking=?i", $bid);
			foreach($positions as $position){
				$number = $position["number"];
				$room = array();
				$room["roomTypeId"] = $position["room"];
				$room["bookingPerDayPrices"] = array();
				$timestamp = strToTime($position["arrival"]);
				for($i = 1; $i <= $position["days"]; $i++){
					$date_price = array();
					$date = date("Y-m-d", $timestamp);
					$date_price["dateYmd"] = $date;
					$timestamp+= 86400;
					$room["bookingPerDayPrices"][] = $date_price;
				}

				$booking["roomStays"][] = $room;
			}

			$bookings["bookings"][] = $booking;
			$bookings["check"] = 1;
		}
	}

	return $bookings;
}

function confirm_update_booking($connect, $confirm){
	foreach($confirm as $id => $conf){
		$connect->query("UPDATE booking SET update_bid=0 WHERE id=?i", $id);
	}
}

function confirm_room_places_quota($connect, $data){
	$confirm = $data["confirm"];
	foreach($confirm as $id => $travelline){
		$connect->query("UPDATE booking SET confirm=1, id_travelline=?s WHERE id=?i", $travelline, $id);
		$bid = $connect->getOne("SELECT bid FROM booking WHERE id=?i", $id);
		$manager = $connect->getOne("SELECT id_user FROM reckoning WHERE id=?i", $bid);
		if($manager){
			$text = "Заявка №".$bid." подтверждена объектом";
			save_notification($connect, $text, $manager);
		}
	}
}

?>
