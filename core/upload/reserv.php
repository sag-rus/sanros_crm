<?php

function upload_reserv_object_on_server($connect, $id){
	global $directory;
	if(!$id){
		$id = $_POST["id"];
		upload_promo_object_on_server($connect, $id);
	}
	$connect_server = connect_to_server();
	if($connect_server == 1)
		return "Ошибка соединения";
	if($connect_server == 2)
		return "Не удалось авторизироваться";
	if(!$id)
		$data = $connect->getAll("SELECT object.id FROM object, region WHERE region.id=object.id_reg AND region.active=0 AND object.active=0 GROUP BY object.id");
	else
		$data = $connect->getAll("SELECT id FROM object WHERE id=?i", $id);
	foreach($data as $row){
		$id = $row["id"];
		save_reserv_XML_object($connect, $id);
		$file = $directory."/temp/xml/reserv/".$id.".xml";
		//$server_file = "/var/www/default-site/public_html/price/XML/reserv/".$id.".xml";
		$server_file = "/load_price/XML/reserv/".$id.".xml";
		if(!ftp_put($connect_server, $server_file, $file, FTP_ASCII))
			return "Не удалось загрузить файл на сервер";
		ftp_chmod($connect_server, 0644, $server_file);
	}
	ftp_quit($connect_server);
	if(!$id)
		return "<div class='alert alert-success'>Загрузка завершена!</div>";
	return "<div class='alert alert-success'>Загрузка завершена!<br /><a class='alert-link' href='http://xn----dtbmnhpbbghbyj0jwa2c.xn--p1ai/price.html#object/".$id."/reservation' target='_blank'><i class='fa fa-smile-o'></i> Посмотреть как это выглядит на сайте</a></div>";
}

function save_reserv_XML_object($connect, $id){
	global $directory;
	$xml = new DomDocument("1.0", "utf-8");
	$reservation = $xml->appendChild($xml->createElement("object"));
	$data = $connect->getAll("SELECT room.id as id_room, object.id as id_obj, room.name, reservation.id, reservation.date, reservation.sum, reservation.day, reservation.note FROM reservation, object_room, room, object WHERE reservation.status=1 AND reservation.active=0 AND object.id=?i AND reservation.room=object_room.id AND object_room.id_category=room.id AND room.id_obj=object.id AND reservation.sum!='' ORDER BY reservation.date", $id);
	$reservation->setAttribute("number", count($data));
	foreach($data as $row){
		$reserv = $reservation->appendChild($xml->createElement("reserv"));
		$reserv->setAttribute("number", $row["id"]);

		$date = $reserv->appendChild($xml->createElement("date"));
		$date->appendChild($xml->createTextNode($row["date"]));
		$day = $reserv->appendChild($xml->createElement("day"));
		$day->appendChild($xml->createTextNode($row["day"]));
		$note = $reserv->appendChild($xml->createElement("note"));
		$note->appendChild($xml->createTextNode($row["note"]));
		$sum = $reserv->appendChild($xml->createElement("sum"));
		$sum->appendChild($xml->createTextNode($row["sum"]));
		$room = $reserv->appendChild($xml->createElement("room"));
		$room->appendChild($xml->createTextNode($row["name"]));
		$room->setAttribute("id", $row["id_room"]);
		$room->setAttribute("object", $row["id_obj"]);
		$room->setAttribute("region", $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $row["id_obj"]));
	}
	$xml->formatOutput = true;
	$xml->save($directory."/temp/xml/reserv/".$id.".xml");
}

function check_free_place_object($connect, $id, $type = ""){
	$today = strToTime(date("1-m-Y"));
	if($type == "room_category")
		$data = $connect->getAll("SELECT on_sale FROM object_room WHERE id=?i", $id);
	elseif($type == "room")
		$data = $connect->getAll("SELECT on_sale FROM object_room WHERE id_category=?i", $id);
	else
		$data = $connect->getAll("SELECT on_sale FROM object_room, room WHERE object_room.id_category=room.id AND room.id_obj=?i", $id);
	foreach($data as $row){
		$on_sale = json_decode($row["on_sale"], TRUE);
		if(is_array($on_sale)){
			foreach($on_sale as $date => $sale){
				if($today <= strToTime("1-".$date) AND $sale)
					return TRUE;
			}
		}
	}
	return FALSE;
}

function clear_sale_calendar_object($connect, $id){
	if(!$id)
		return FALSE;
	$row = $connect->getRow("SELECT date, day, room FROM reservation WHERE id=?i", $id);
	$start_reserv = strToTime($row["date"]);
	$end_reserv = $start_reserv + ($row["day"] - 1) * 86400;
	$on_sale = json_decode($connect->getOne("SELECT on_sale FROM object_room WHERE id=?i", $row["room"]), TRUE);
	foreach($on_sale as $month => $month_data){
		foreach($month_data as $index => $range){
			$start = strToTime($range["d"]."-".$month);
			$end = $start + ($range["n"] - 1) * 86400;
			if($start_reserv >= $start AND $end_reserv >= $end AND $end >= $start_reserv)
				$on_sale[$month][$index]["n"] = ($start_reserv - $start) / 86400;
			elseif($start_reserv <= $start AND $end_reserv <= $end AND $start <= $end_reserv){
				$on_sale[$month][$index]["d"] = date("d", $end_reserv + 86400);
				$on_sale[$month][$index]["n"] = ($end - $end_reserv) / 86400;
			}elseif($start_reserv >= $start AND $end_reserv <= $end AND $end_reserv >= $start){
				if($start_reserv - $start > 0)
					$on_sale[$month][$index]["n"] = ($start_reserv - $start) / 86400;
				else
					unset($on_sale[$month][$index]);
				$count = count($on_sale[$month]);
				if($end - $end_reserv > 0){
					$on_sale[$month][$count]["d"] = date("d", $end_reserv + 86400);
					$on_sale[$month][$count]["n"] = ($end - $end_reserv) / 86400;
				}
			}elseif($start_reserv <= $start AND $end_reserv >= $end)
				unset($on_sale[$month][$index]);
		}
	}
	$data = json_encode($on_sale);
	$connect->query("UPDATE object_room SET on_sale=?s WHERE id=?i", $data, $row["room"]);
}

?>
