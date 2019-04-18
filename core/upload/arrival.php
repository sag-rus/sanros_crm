<?php

function upload_arrival_on_server($connect, $id){
	global $directory;
	if(!$id)
		$id = $_POST["id"];
	$connect_server = connect_to_server();
	if($connect_server == 1)
		return "Ошибка соединения";
	if($connect_server == 2)
		return "Не удалось авторизироваться";

	$data = $connect->getAll("SELECT id FROM object WHERE id=?i", $id);
	foreach($data as $row){
		$id = $row["id"];
		save_arrival_XML_object($connect, $id);
		$file = $directory."/temp/xml/arrival/".$id.".xml";
		$server_file = "/var/www/default-site/public_html/price/XML/arrival/".$id.".xml";
		if(!ftp_put($connect_server, $server_file, $file, FTP_ASCII))
			return "Не удалось загрузить файл на сервер";
		ftp_chmod($connect_server, 0644, $server_file);
	}
	ftp_quit($connect_server);
	if($id)
		return "<div class='alert alert-success'>Загрузка завершена!</div>";
}

function save_arrival_XML_object($connect, $id){
	date_default_timezone_set("UTC");
	global $directory;
	$row = $connect->getRow("SELECT id, name, id_reg FROM object WHERE active=0 AND id=?i", $id);
	$count = $connect->getOne("SELECT COUNT(*) FROM room, object_room WHERE room.id_obj=?i AND room.id=object_room.id_category LIMIT 1", $id_obj);
	if(!$row["id"] AND $count <= 0)
		return FALSE;
	$xml = new DomDocument("1.0", "utf-8");
	$object = $xml->appendChild($xml->createElement("object"));
	$object->setAttribute("name", $row["name"]);
	$object->setAttribute("region", $row["id_reg"]);

	$data = $connect->getAll("SELECT room.id FROM room, object_room WHERE room.id_obj=?i AND room.id=object_room.id_category GROUP BY room.id", $id);
	$today_timestamp = time();
	foreach($data as $row){
		$id_room = $row["id"];
		$balances = array();
		$data2 = $connect->getAll("SELECT id, on_sale FROM object_room WHERE id_category=?i", $id_room);
		foreach($data2 as $row){
			$on_sale = json_decode($row["on_sale"], TRUE);
			foreach($on_sale as $month_year => $month_sale){
				$arr = explode("-", $month_year);
				$month = $arr[0];
				$year = $arr[1];
				foreach($month_sale as $sale){
					$start = $sale["d"];
					$days = $sale["n"];
					$add = 1;
					$date_transform = strToTime($start."-".$month."-".$year);
					$day_transform = ($days - 1) * 86400;
					$end_transform = $date_transform + $day_transform;
					if($end_transform >= $today_timestamp){
						$prev_day = $date_transform - 86400;
						foreach($balances as $date_balances => $row_balances){
							if($start == 1 AND $row_balances["room"] == $row["id"] AND $prev_day == $row_balances["end"]){
								$add = 0;
								$balances[$date_balances]["day"]+= $days;
								$balances[$date_balances]["end"]+= $days * 86400;
								break;
							}
							$day_balances = $row_balances["day"];
							if($date_transform >= $date_balances AND $end_transform <= $row_balances["end"]){
								$add = 0;
								break;
							}

						}
						if($add == 1 AND (!$balances[$date_transform] OR $balances[$date_transform] <= $days)){
							$balances[$date_transform]["room"] = $row["id"];
							$balances[$date_transform]["day"] = $days;
							$balances[$date_transform]["end"] = $end_transform;
						}
					}
				}
			}
		}
		if($balances){
			ksort($balances);
			$room = $object->appendChild($xml->createElement("room"));
			$row = $connect->getRow("SELECT name, main_place, add_place, note FROM room WHERE id=?i", $id_room);
			$room->setAttribute("id", $id_room);
			$room->setAttribute("name", $row["name"]);
			$room->setAttribute("note", $row["note"]);
			$room->setAttribute("main_place", $row["main_place"]);
			$room->setAttribute("add_place", $row["add_place"]);
			foreach($balances as $timestamp => $row){
				$date = date("Y-m-d", $timestamp);
				$end = $timestamp + ($row["day"] - 1) * 86400;
				$arrival = $room->appendChild($xml->createElement("arrival"));
				$arrival->setAttribute("date", $date);
				$arrival->setAttribute("day", $row["day"]);
				$arrival->setAttribute("timestamp", $timestamp);
				$arrival->setAttribute("end", $end);
				$row = $connect->getRow("SELECT id FROM date_price WHERE id_obj=?i AND start<=?s AND end>=?s", $id, $date, date("Y-m-d", $end));
				$data2 = $connect->getAll("SELECT ranges.id, ranges.type, place.type as place_type, place.name as place_name, ranges.place, ranges.name FROM ranges, place WHERE ranges.id_date=?i AND ranges.active=0 AND ranges.place=place.id ORDER BY place.type, place.id", $row["id"]);
				foreach($data2 as $row){
					$price = $connect->getOne("SELECT price FROM price WHERE id_room=?i AND id_range=?i AND active = 0", $id_room, $row["id"]);
					if($price){
						$place_price = $arrival->appendChild($xml->createElement("price"));
						$place_price->setAttribute("price", $price);
						$place_price->setAttribute("name", $row["name"]);
						$place_price->setAttribute("type", $row["type"]);
						$place_price->setAttribute("place", $row["place"]);
						$place_price->setAttribute("place_type", $row["place_type"]);
						$place_price->setAttribute("place_name", $row["place_name"]);
					}
				}
			}
		}
	}
	$xml->formatOutput = TRUE;
	$xml->save($directory."/temp/xml/arrival/".$id.".xml");
}


?>
