<?php

$array_type = array(1 => "за чел/сутки", 2 => "за дом/сутки", 3 => "за номер/сутки", 4 => "за заезд");

function upload_price_on_server($connect, $id=false){
	global $directory;
	$url = false;

	if(!$id)
		$id = $_POST["id"];
	$connect_server = connect_to_server();
	//if($connect_server == 1)
	//	return "Ошибка соединения";
	if($connect_server == 2)
		return "Не удалось авторизироваться";

	if(!$id)
		$data = $connect->getAll("SELECT id, url_name, website, source_booking FROM object WHERE object.active=0 OR object.active=1");
	else{
		$data = $connect->getAll("SELECT id, url_name, website, source_booking FROM object WHERE id=?i LIMIT 1", $id);
		$desc = $connect->getOne("SELECT description FROM object WHERE id=?i LIMIT 1", $id);
		$connect->query("UPDATE object SET status=1, description_check=?s WHERE id=?i LIMIT 1", $desc, $id);
	}
	foreach($data as $row){
		$id = $row["id"];
		$url = $row["url_name"];
		$source_booking = $row["source_booking"];
		$website = $row['website'];
		save_price_XML_object($connect, $id);
		save_desc_XML_object($connect, $id);
		$file = $directory."/temp/xml/price/".$id.".xml";
		$server_file = "/var/www/default-site/public_html/price/XML/price/".$id.".xml";
		ftp_chmod($connect_server, 0777, $server_file);
		if(!ftp_put($connect_server, $server_file, $file, FTP_ASCII))
			return "Не удалось загрузить файл на сервер";
		$file = $directory."/temp/xml/desc/".$id.".xml";
		$server_file = "/var/www/default-site/public_html/price/XML/desc/".$id.".xml";
		if(!ftp_put($connect_server, $server_file, $file, FTP_ASCII))
			return "Не удалось загрузить файл на сервер";
		ftp_chmod($connect_server, 0777, $server_file);
	}
	ftp_quit($connect_server);
	if($id) {
		if($url) {
			return "<div class='alert alert-success'>Загрузка завершена! <a class='alert-link' href='https://санатории-россии.рф/объект/" . $url . "' target='_blank'><i class='fa fa-smile-o'></i> Посмотреть как это выглядит на сайте</a></div>";
		} else {
			return "<div class='alert alert-success'>Загрузка завершена! Нет ссылки на объект.</div>";
		}
	} else {
		return "<div class='alert alert-success'>Загрузка по всем объектам завершена!</div>";
	}
		
}

function save_price_XML_object($connect, $id){
	global $array_type, $directory;
	$today = date("Y-m-d");
	$current_time = time();
	$row = $connect->getRow("SELECT id, name, id_reg, city, id_profile, id_methods, id_infa, medical_factors, type, description, add_one_day, latitude, longitude, weather, regular_com, up_com, reward, arrival, leaving, check_places, website, source_booking, booking_uri FROM object WHERE id=?i AND (active=0 OR active=1)", $id);
	$quota = $row["check_places"];
	$count = $connect->getOne("SELECT COUNT(*) FROM price, room WHERE room.id_obj=?i AND room.id=price.id_room", $id);
	if(!$row["id"] AND $count <= 0)
		return FALSE;
	if($row["add_one_day"] != 1)
		$row["add_one_day"] = 0;
	$bonus = 5;
	if($row["reward"] > 0)
		$bonus = $row["reward"] / 2;
	$xml = new DomDocument("1.0", "utf-8");
	$count_index_place = 0;

	$check_price = 0;
	$object = $xml->appendChild($xml->createElement("object"));

	$object->setAttribute("name", $row["name"]);
	$object->setAttribute("id_reg", $row["id_reg"]);
	$object->setAttribute("profile", $row["id_profile"]);
	$object->setAttribute("infa", $row["id_infa"]);
	$object->setAttribute("factors", $row["medical_factors"]);
	$object->setAttribute("method", $row["id_methods"]);
	$object->setAttribute("type", $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]));
	$object->setAttribute("description", $row["description"]);
	$object->setAttribute("add_one_day", $row["add_one_day"]);
	$object->setAttribute("regular_com", $row["regular_com"]);
	$object->setAttribute("up_com", $row["up_com"]);
	$object->setAttribute("bonus", $bonus);
	$object->setAttribute("weather", $row["weather"]);
	$object->setAttribute("city", $row["city"]);
	$object->setAttribute("quota", $quota);
	$object->setAttribute("website",$row["website"]);
  $object->setAttribute("source_booking",$row["source_booking"]);
  $object->setAttribute("booking_uri", $row['booking_uri']);
	if($row["arrival"])
		$object->setAttribute("arrival", $row["arrival"]);
	if($row["leaving"])
		$object->setAttribute("leaving", $row["leaving"]);
	$object->setAttribute("region", $connect->getOne("SELECT name FROM region WHERE id=?i", $row["id_reg"]));
	if($row["latitude"] > 0){
		$object->setAttribute("latitude", $row["latitude"]);
		$object->setAttribute("longitude", $row["longitude"]);
		$sights = $connect->getAll("SELECT latitude, longitude FROM sights");
		foreach($sights as $sight){
			if(calculate_distance($row["latitude"], $row["longitude"], $sight["latitude"], $sight["longitude"]) <= 50){
				$object->setAttribute("sights", "1");
				break;
			}
		}
	}
	$ratePlanDATA = array();
	$data = $connect->getAll("SELECT id, name FROM rate_plan WHERE id=1 OR object=?i", $id);
	foreach($data as $row){
		$ratePlanId = $row["id"];
		$ratePlanDATA[$ratePlanId] = array();
		$ratePlanDATA[$ratePlanId]["name"] = $row["name"];
	}

	$data = $connect->getAll("SELECT name, id, id_best_comfort, id_comfort, main_place, add_place, note, housing, square, food, price_places FROM room WHERE id_obj=?i and active=0 ORDER BY housing ASC", $id);

	//print_r($data);

	foreach($data as $row){
		$name_room = $row["name"];
		$id_room = $row["id"];
		$comfort = $row["id_comfort"];
		$best_comfort = $row["id_best_comfort"];
		$main_place = $row["main_place"];
		$add_place = $row["add_place"];
		$note = $row["note"];
		$food = $row["food"];
		$square = $row["square"];
		$price_places = $row["price_places"];
		$housing = "";
		if($row["housing"])
			$housing = $connect->getOne("SELECT name FROM housing WHERE id=?i", $row["housing"]);
		$room = $object->appendChild($xml->createElement("room"));
		$room->setAttribute("id", $id_room);
		$room->setAttribute("note", $note);
		$room->setAttribute("housing", $housing);
		$room->setAttribute("id_housing", $row["housing"]);
		$room->setAttribute("name", $name_room);
		$room->setAttribute("main_place", $main_place);
		$room->setAttribute("add_place", $add_place);
		$room->setAttribute("comfort", $comfort);
		$room->setAttribute("best_comfort", $best_comfort);
		$room->setAttribute("square", $square);
		$room->setAttribute("food", $food);

		if($quota != 1 OR $id == 42 OR $id == 34 OR $id == 59 OR $id == 20){
			$min_array = get_min_price($connect, $id_room);
			$room->setAttribute("min_price", $min_array["price"]);
			$room->setAttribute("min_price_type", $min_array["type"]);
			$room->setAttribute("min_price_treatment",$min_array["treatment"]);
			$data2 = $connect->getAll("SELECT id, start, end FROM date_price WHERE id_obj=?i AND end>=?s ORDER BY start", $id, $today);
			$index_date = 0;
			foreach($data2 as $row){
				$date_s = $row["start"];
				$date_e = $row["end"];
				$id_date = $row["id"];
				if(have_price($connect, $id_date, $id_room)){
					$index_date++;
					$date_price = $room->appendChild($xml->createElement("date"));
					$date_price->setAttribute("start", $date_s);
					$date_price->setAttribute("end", $date_e);
					$date_price->setAttribute("index", $id_date);

					$check_price = 1;

					$data3 = $connect->getAll("SELECT price, price.id, ranges.name as name, ranges.type, show_date, place, ranges.id as id_range, ranges.treatment, place.name as place_name, place.type as place_type FROM price, ranges, place WHERE price.id_room=?i AND ranges.id_date=?i AND price.active=0 AND price.id_range=ranges.id AND place.id=ranges.place ORDER BY place.type, place.id, ranges.id ASC", $id_room, $id_date);
					foreach($data3 as $row){
						$name_price = str_replace("\"", "", $row["name"]);
						$name_price = str_replace("'", "", $name_price);
						$type_price = $array_type[$row["type"]];
						$value = $row["price"];
						$show_date = $row["show_date"];
						$place = $row["place"];
						$id_price = $row["id"];
						$id_range = $row["id_range"];
						$place_name = $row["place_name"];
						$place_type = $row["place_type"];
						$treatment = $row['treatment'];

						$price = $date_price->appendChild($xml->createElement("price"));

						$name_id = $price->appendChild($xml->createElement("id"));
						$name_id->appendChild($xml->createTextNode("$id_price"));

						$price_name = $price->appendChild($xml->createElement("name"));
						$price_name->appendChild($xml->createTextNode("$name_price"));

						$price_value = $price->appendChild($xml->createElement("value"));
						$price_value->appendChild($xml->createTextNode("$value"));

						$type_p = $price->appendChild($xml->createElement("type"));
						$type_p->appendChild($xml->createTextNode($type_price));

						$type_index = $price->appendChild($xml->createElement("type_index"));
						$type_index->appendChild($xml->createTextNode($row["type"]));

						$s_date = $price->appendChild($xml->createElement("show_date"));
						$s_date->appendChild($xml->createTextNode("$show_date"));

						$place_r = $price->appendChild($xml->createElement("place"));
						$place_r->appendChild($xml->createTextNode("$place"));

						$place_n = $price->appendChild($xml->createElement("place_name"));
						$place_n->appendChild($xml->createTextNode("$place_name"));

						$place_n = $price->appendChild($xml->createElement("place_type"));
						$place_n->appendChild($xml->createTextNode("$place_type"));

						$id_r = $price->appendChild($xml->createElement("range"));
						$id_r->appendChild($xml->createTextNode("$id_range"));

						$price_treatment = $price->appendChild($xml->createElement("treatment"));
						$price_treatment->appendChild($xml->createTextNode("$treatment"));
					}
				}
			}

		}else{

			$min_array = array("price" => 0, "type" => 3);
			$price_places = json_decode($price_places, TRUE);
			$array = array();

			if($price_places) {
				foreach($price_places as $ratePlan => $rate_plan_data){
					foreach($rate_plan_data as $prices){
						$date = $prices["dt"];
						$days = $prices["d"];
						$end = $date + ($days * 86400);
						if($current_time <= $end AND !isset($array[$date])){
							$check = 0;
							foreach($array as $check_start => $array_date){
								if($check_start < $date AND $array_date["end"] > $date)
									$check = 0;
							}
							if($check == 0 and 0){
								foreach($array as $check_start => $array_date){
									if(($array_date["end"] + 86400) == $date OR $array_date["end"] == $date){
										if($array_date["p"][$ratePlan][1] == $prices["p"][$ratePlan][1]){
											$array[$check_start]["end"] = $end;
											$check = 1;
											break;
										}
									}
								}
							}
							if($check == 0){
								$array[$date] = array();
								$array[$date]["end"] = $end;
								$array[$date]["price"] = array();
							}
						}
						if(isset($array[$date])){
							$array[$date]["price"][$ratePlan] = $prices["p"];
						}
					}
				}
			}

			foreach($array as $start => $array_price){
				$date_price = $room->appendChild($xml->createElement("date"));
				$date_price->setAttribute("start", date("Y-m-d", $start));
				$date_price->setAttribute("end", date("Y-m-d", $array_price["end"]));
				$date_price->setAttribute("index", $start);
				foreach($array_price["price"] as $ratePlanId => $row){
					foreach($row as $index_place => $price_room){
						$price_room = (int)$price_room;
						if($price_room > 0){
							$check_price = 1;
							$count_index_place++;
							$place_name = $index_place."-мест. размещение";
							$type_price = "за номер";
							$type_index_place = 2;
							$place_type = 1;
							if($index_place == "add"){
								$type_price = "за чел/сутки";
								$type_index_place = 1;
								$place_name = "";
								$place_type = 2;
							}elseif($min_array["price"] == 0 OR $min_array["price"] > $price_room)
								$min_array["price"] = $price_room;
							$price = $date_price->appendChild($xml->createElement("price"));

							$name_id = $price->appendChild($xml->createElement("id"));
							$name_id->appendChild($xml->createTextNode($count_index_place));

							$price_name = $price->appendChild($xml->createElement("name"));
							$price_name->appendChild($xml->createTextNode($ratePlanDATA[$ratePlanId]["name"]));

							$price_value = $price->appendChild($xml->createElement("value"));
							$price_value->appendChild($xml->createTextNode($price_room));

							$type_p = $price->appendChild($xml->createElement("type"));
							$type_p->appendChild($xml->createTextNode($type_price));

							$place_r = $price->appendChild($xml->createElement("place"));
							$place_r->appendChild($xml->createTextNode($index_place));

							$type_index = $price->appendChild($xml->createElement("type_index"));
							$type_index->appendChild($xml->createTextNode($type_index_place));

							$place_n = $price->appendChild($xml->createElement("place_name"));
							$place_n->appendChild($xml->createTextNode($place_name));

							$place_n = $price->appendChild($xml->createElement("place_type"));
							$place_n->appendChild($xml->createTextNode($place_type));

							$id_r = $price->appendChild($xml->createElement("range"));
							$id_r->appendChild($xml->createTextNode($ratePlanId.$index_place));

							$id_r = $price->appendChild($xml->createElement("ratePlan"));
							$id_r->appendChild($xml->createTextNode($ratePlanId));
						}
					}
				}
			}

			$room->setAttribute("min_price", $min_array["price"]);
			$room->setAttribute("min_price_type", $min_array["type"]);

		}
	}

	$object->setAttribute("check_price", $check_price);

	$xml->formatOutput = true;
	$xml->save($directory."/temp/xml/price/".$id.".xml");
	return TRUE;
}

function save_desc_XML_object($connect, $id){
	global $directory;
	$row = $connect->getRow("SELECT id, id_reg, name, id_profile, id_methods, id_infa, medical_factors, description, id_services FROM object WHERE id=?i AND (active=0 OR active=1)", $id);
	$name_object = $row["name"];
	$region = $row["id_reg"];
	$infa_text = json_encode(parse_index_string_to_array($connect, $row["id_infa"], "infa", "_"));
	$profile_object = $row["id_profile"];
	$methods_object = $row["id_methods"];
	$medical_factors = $row["medical_factors"];
	$description = $row["description"];
	$services_object = json_decode($row["id_services"], TRUE);

	$array = explode("_", $profile_object);
	$profiles = array();
	foreach($array as $index){
		if($index){
			$row = $connect->getRow("SELECT name, description FROM profile WHERE id=?i", $index);
			$profiles[$index] = array();
			$profiles[$index]["name"] = $row["name"];
			$profiles[$index]["desc"] = $row["description"];
		}
	}
	$profile_text = json_encode($profiles);

	$array = explode("_", $methods_object);
	$methods = array();
	foreach($array as $index){
		if($index){
			$row = $connect->getRow("SELECT name, description FROM methods WHERE id=?i", $index);
			$methods[$index] = array();
			$methods[$index]["name"] = $row["name"];
			$methods[$index]["desc"] = $row["description"];
		}
	}
	$method_text = json_encode($methods);

	$services = array();
	$array = $connect->getAll("SELECT id, name, icon FROM services");
	foreach($array as $service){
		$index = $service["id"];
		if(isset($services_object[$index])){
			$services[$index] = array();
			$services[$index]["icon"] = $service["icon"];
			$services[$index]["name"] = $service["name"];
			$services[$index]["text"] = $services_object[$index];
		}
	}
	$service_text = json_encode($services);

	$xml = new DomDocument("1.0", "utf-8");
	$object = $xml->appendChild($xml->createElement("object"));
	$object->setAttribute("name", $name_object);
	$object->setAttribute("region", $region);
	$profile = $object->appendChild($xml->createElement("profile"));
	$profile->appendChild($xml->createTextNode($profile_text));
	$method = $object->appendChild($xml->createElement("method"));
	$method->appendChild($xml->createTextNode($method_text));
	$infa = $object->appendChild($xml->createElement("infa"));
	$infa->appendChild($xml->createTextNode($infa_text));
	$service = $object->appendChild($xml->createElement("service"));
	$service->appendChild($xml->createTextNode($service_text));
	$desc = $object->appendChild($xml->createElement("desc"));
	$desc->appendChild($xml->createTextNode($description));
	$factors = $object->appendChild($xml->createElement("factors"));
	$factors->appendChild($xml->createTextNode($medical_factors));

	$xml->formatOutput = true;
	$xml->save($directory."/temp/xml/desc/".$id.".xml");
	return TRUE;
}

?>
