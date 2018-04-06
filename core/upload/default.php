<?php

function upload_information_object($connect){
	global $directory;
	$array_region = array();
	$price_region = array();
	$today = date("Y-m-d");

	$data = $connect->getAll("SELECT id FROM date_price WHERE end<?s AND active=0", $today);
	foreach($data as $row){
		$id_date = $row["id"];
		$array = $connect->getAll("SELECT id FROM ranges WHERE id_date=?i", $id_date);
		foreach($array as $range)
			$connect->query("UPDATE price SET active=1 WHERE id_range=?i", $range["id"]);
		$connect->query("UPDATE ranges SET active=1 WHERE id_date=?i", $id_date);
	}
	$connect->query("UPDATE date_price SET active=1 WHERE end<?s AND active=0", $today);

	$array_type = array(1 => "за чел/сутки", 2 => "за дом/сутки", 3 => "за номер/сутки", 4 => "за заезд");

	$xml = new DomDocument("1.0", "utf-8");
	$profiles = $xml->appendChild($xml->createElement("profiles"));
	$data = $connect->getAll("SELECT id, name, description FROM profile ORDER BY name");
	$i = 0;
	foreach($data as $row){
		$i++;
		$profile = $profiles->appendChild($xml->createElement("profile"));
		$profile->setAttribute("id", $row["id"]);
		$profile->appendChild($xml->createTextNode($row["name"]));

		$profile_desc = $profile->appendChild($xml->createElement("desc"));
		$profile_desc->appendChild($xml->createTextNode($row["description"]));
	}
	$profiles->setAttribute("count", $i);
	$xml->formatOutput = true;
	$xml->save("temp/profile.xml");

	$xml = new DomDocument("1.0", "utf-8");
	$infrastructure = $xml->appendChild($xml->createElement("infrastructure"));
	$data = $connect->getAll("SELECT id, name FROM infa ORDER BY name");
	$i = 0;
	foreach($data as $row){
		$i++;
		$infa = $infrastructure->appendChild($xml->createElement("infa"));
		$infa->setAttribute("id", $row["id"]);
		$infa->appendChild($xml->createTextNode($row["name"]));
	}
	$infrastructure->setAttribute("count", $i);
	$xml->formatOutput = true;
	$xml->save("temp/infa.xml");

	$xml = new DomDocument("1.0", "utf-8");
	$comforts = $xml->appendChild($xml->createElement("comforts"));
	$data = $connect->getAll("SELECT id, name, icon, type FROM comfort");
	foreach($data as $row){
		$comfort = $comforts->appendChild($xml->createElement("comfort"));
		$comfort->setAttribute("id", $row["id"]);
		$comfort->setAttribute("icon", $row["icon"]);
		$comfort->setAttribute("type", $row["type"]);
		$comfort->appendChild($xml->createTextNode($row["name"]));
	}
	$xml->formatOutput = true;
	$xml->save("temp/comfort.xml");

	$xml = new DomDocument("1.0", "utf-8");
	$objects = $xml->appendChild($xml->createElement("objects"));
	$data = $connect->getAll("SELECT id, id_services FROM object");
	foreach($data as $row){
		$id = $row["id"];
		$services = json_decode($row["id_services"], TRUE);

		if(is_array($services)) {
			$services = array_diff($services, array(""));
		} else {
			$services = array();
		}

		if($services){
			$object = $objects->appendChild($xml->createElement("object"));
			$object->setAttribute("id", $id);
			foreach($services as $key => $text){
				if($key){
					$name_service = $connect->getOne("SELECT name FROM services WHERE id=?i", $key);
					$icon_service = $connect->getOne("SELECT icon FROM services WHERE id=?i", $key);
					$service_node = $object->appendChild($xml->createElement("service"));
					$service_node->setAttribute("name", $name_service);
					$service_node->setAttribute("icon", $icon_service);
					$service_node->appendChild($xml->createTextNode("$text"));
				}
			}
		}
	}
	$xml->formatOutput = true;
	$xml->save("temp/services.xml");

	$xml = new DomDocument("1.0", "utf-8");
	$objects = $xml->appendChild($xml->createElement("objects"));
	$sights = $connect->getAll("SELECT latitude, longitude FROM sights");
	$data = $connect->getAll("SELECT object.name as object, object.id, object.image, object.id_reg, object.direction, object.city, object.id_profile, object.id_methods, object.id_infa, object.type, object.check_places, object.description, object.similar, object.add_one_day, object.latitude, object.longitude, object.weather, object.url_name, object.reward, object.source_booking, object.booking_uri, region.name as region, region.name_rod as region_rod FROM region, object WHERE region.id_country=1 AND (object.active=0) AND object.id_reg=region.id AND object.url_name!='' ORDER BY region.name");
	foreach($data as $row){
		$id = $row["id"];
		$prices = get_prices_object($connect, $id);
		//if($prices["min"]){
			$name = $row["object"];
			$id_reg = $row["id_reg"];
			$id_dir = $row["direction"];
			$region = $row["region"];
			$region_rod = $row["region_rod"];
			$id_profile = $row["id_profile"];
			$id_infa = $row["id_infa"];
			$id_method = $row["id_methods"];
			$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);
			$city = $row["city"];
			$add_one_day = $row["add_one_day"];
			if($add_one_day != 1)
				$add_one_day = 0;
			$description = $row["description"];
			$latitude = $row["latitude"];
			$longitude = $row["longitude"];
			$similar = $row["similar"];
			$weather = $row["weather"];
			$image = $row["image"];
			$url_name = $row["url_name"];
			$reward = $row["reward"];
			$check_places = $row["check_places"];
			$source_booking  = $row['source_booking'];
			$booking_uri = $row['booking_uri'];

			if(!isset($array_region[$id_reg]))
				$array_region[$id_reg] = 0;
			else
				$array_region[$id_reg]++;
			$count_rating = $connect->getOne("SELECT COUNT(*) FROM rating WHERE id_obj=?i AND status=3 AND average!=0", $id);
			$sum_rating_object = $connect->getOne("SELECT SUM(average) FROM rating WHERE id_obj=?i AND status=3 AND average!=0", $id);
			if(!(int)$count_rating || !(int)$sum_rating_object) {
				$average_rating = 0;
			} else {
				$average_rating = round($sum_rating_object / $count_rating, 1);
			}
			$direction = $connect->getOne("SELECT id_direction FROM region WHERE id=?i", $id_reg);
			$name_direction = $connect->getOne("SELECT name FROM direction_object WHERE id=?i", $direction);
			$object = $objects->appendChild($xml->createElement("object"));
			$object->setAttribute("id", $id);
			$object->setAttribute("name", $name);
			$object->setAttribute("name_url", $url_name);
			$object->setAttribute("profile", $id_profile);
			$object->setAttribute("infa", $id_infa);
			$object->setAttribute("method", $id_method);
			$object->setAttribute("type", $type);
			$object->setAttribute("count_rating", $count_rating);
			$object->setAttribute("average_rating", $average_rating);
			$object->setAttribute("reward", $reward);
			$object->setAttribute("add_one_day", $add_one_day);
			$object->setAttribute("id_reg", $id_reg);
			$object->setAttribute("id_country", $connect->getOne("SELECT id_country FROM region WHERE id=?i", $id_reg));
			$object->setAttribute("id_dir", $id_dir);
			$object->setAttribute("direction", $direction);
			$object->setAttribute("region", $region);
			$object->setAttribute("region_rod", $region_rod);
			$object->setAttribute("region_url", change_text_url($region));
			$object->setAttribute("name_direction", $name_direction);
			$object->setAttribute("direction_url", change_text_url($name_direction));
			$object->setAttribute("city", $city);
			$object->setAttribute("similar", $similar);
			$object->setAttribute("quota", $check_places);
			$object->setAttribute("min", $prices["min"]);
			if(isset($prices["min_treatment"]))
				$object->setAttribute("min_treatment",$prices["min_treatment"]);
			$object->setAttribute("page", intval($array_region[$id_reg]/10) + 1);
			$object->setAttribute("source_booking", $source_booking);
			$object->setAttribute("booking_uri",$booking_uri);
			if($latitude > 0){
				$object->setAttribute("latitude", $latitude);
				$object->setAttribute("longitude", $longitude);
				foreach($sights as $sight){
					if(calculate_distance($latitude, $longitude, $sight["latitude"], $sight["longitude"]) <= 50){
						$object->setAttribute("sights", "1");
						break;
					}
				}
			}
			$data2 = $connect->getAll("SELECT id, start, end FROM date_price WHERE id_obj=?i AND active=0", $id);
			foreach($data2 as $row){
				$min_row = $connect->getRow("SELECT price.price AS price, ranges.treatment AS treatment FROM price, ranges WHERE (ranges.active=0 AND price.active=0 AND ranges.id_obj=?i AND price.id_range=ranges.id AND ranges.place=1 AND ranges.id_date=?i) ORDER BY price.price ASC LIMIT 1", $id, $row["id"]);
				$min = $min_row['price'];
				if($min){
					$min_price = $object->appendChild($xml->createElement("price"));
					$min_price->setAttribute("start", strToTime($row["start"]));
					$min_price->setAttribute("end", strToTime($row["end"]));
					$min_price->setAttribute("price", $min);
					$min_price->setAttribute("treatment",$min_row['treatment']);
				}
			}
			if(!isset($price_region[$id_reg]))
				$price_region[$id_reg] = array("min" => 0, "max" => 0);

			if($prices["min"] > 0 AND ($price_region[$id_reg]["min"] > $prices["min"] OR $price_region[$id_reg]["min"] == 0)) {
        $price_region[$id_reg]["min"] = $prices["min"];
        if(isset($prices["min_treatment"]))
        	$price_region[$id_reg]["min_treatment"] = $prices["min_treatment"];
      }

      if($price_region[$id_reg]["max"] < $prices["min"]) {
        $price_region[$id_reg]["max"] = $prices["min"];
        if(isset($prices["min_treatment"]))
        	$price_region[$id_reg]["max_treatment"] = $prices["min_treatment"];
      }
		//}
	}
	$xml->formatOutput = true;
	$xml->save("temp/object.xml");

	$xml = new DomDocument("1.0", "utf-8");
	$regions = $xml->appendChild($xml->createElement("regions"));
	$data = $connect->getAll("SELECT id, name, id_country, id_direction, description, meta_desc, name_rod FROM region WHERE id_country=1 ORDER BY name");
	foreach($data as $row){
		$id = $row["id"];
		$name_region = $row["name"];
		$name_region_url = change_text_url($name_region);
		$name_region_rod = $row["name_rod"];
		$region = $regions->appendChild($xml->createElement("region"));
		$region->appendChild($xml->createTextNode("$name_region"));
		$region->setAttribute("id", $id);
		$region->setAttribute("min", $price_region[$id]["min"]);
		if(isset($price_region[$id]["min_treatment"]))
      $region->setAttribute("min_treatment", $price_region[$id]["min_treatment"]);

		$region->setAttribute("max", $price_region[$id]["max"]);
    if(isset($price_region[$id]["max_treatment"]))
      $region->setAttribute("max_treatment", $price_region[$id]["max_treatment"]);

		$region->setAttribute("name_url", $name_region_url);
		$region->setAttribute("name_rod", $name_region_rod);
		$region->setAttribute("id_country", $row["id_country"]);
		$region->setAttribute("direction", $row["id_direction"]);
		$region->setAttribute("desc", $row["description"]);
		$region->setAttribute("meta_desc", $row["meta_desc"]);
	}
	$xml->formatOutput = true;
	$xml->save("temp/region.xml");


	$xml = new DomDocument("1.0", "utf-8");
	$directions = $xml->appendChild($xml->createElement("directions"));
	$data = $connect->getAll("SELECT id, name, meta_desc, name_rod FROM direction_object WHERE id_country=1");
	foreach($data as $row){
		$id = $row["id"];
		$name_direction = $row["name"];
		$name_direction_rod = $row["name_rod"];
		$description = $row["meta_desc"];
		$name_direction_url = change_text_url($name_direction);
		$direction = $directions->appendChild($xml->createElement("direction"));
		$direction->setAttribute("name", $name_direction);
		$direction->setAttribute("name_rod", $name_direction_rod);
		$direction->setAttribute("name_url", $name_direction_url);
		$direction->setAttribute("id", $id);
		$direction->setAttribute("desc", $description);
		$min = 0;
		$min_treatment = null;
		$data2 = $connect->getAll("SELECT id, name, description, name_rod, meta_desc FROM region WHERE id_direction=?i", $id);
		foreach($data2 as $row){
			$id_region = $row["id"];
			if(!isset($array_region[$id_region]))
				$page = 1;
			else
				$page = intval($array_region[$id_region]/10) + 1;
			$name_region = $row["name"];
			$name_region_rod = $row["name_rod"];
			$name_region_url = change_text_url($name_region);
			$direction_region = $direction->appendChild($xml->createElement("region"));
			$direction_region->setAttribute("name", $name_region);
			$direction_region->setAttribute("name_rod", $name_region_rod);
			$direction_region->setAttribute("name_url", $name_region_url);
			$direction_region->setAttribute("id", $id_region);
			$direction_region->setAttribute("desc", $row["description"]);
			$direction_region->setAttribute("meta_desc", $row["meta_desc"]);
			$direction_region->setAttribute("min", $price_region[$id_region]["min"]);
			if(isset($price_region[$id_region]["min_treatment"]))
        $direction_region->setAttribute("min_treatment", $price_region[$id_region]["min_treatment"]);

			$direction_region->setAttribute("max", $price_region[$id_region]["max"]);
      if(isset($price_region[$id_region]["max_treatment"]))
        $direction_region->setAttribute("max_treatment", $price_region[$id_region]["max_treatment"]);


			$direction_region->setAttribute("page", $page);
			if(($min < $price_region[$id_region]["min"] OR $min == 0) AND $price_region[$id_region]["min"] > 0) {
        $min = $price_region[$id_region]["min"];
        if(isset($price_region[$id_region]["min_treatment"])) {
        	$min_treatment = $price_region[$id_region]["min_treatment"];
				}
				else {
        	$min_treatment = null;
				}
      }
		}
		$direction->setAttribute("min", $min);
		if(!is_null($min_treatment))
      $direction->setAttribute("min_treatment", $min_treatment);
	}
	$xml->formatOutput = true;
	$xml->save("temp/direction.xml");



	$xml = new DomDocument("1.0", "utf-8");
	$country = $xml->appendChild($xml->createElement("data"));
	$data = $connect->getAll("SELECT id, name FROM country WHERE id=1 OR id=2 ORDER BY id");
	foreach($data as $row){
		$id_country = $row["id"];
		$name_country = $row["name"];
		$country_xml = $country->appendChild($xml->createElement("country"));
		$country_xml->setAttribute("id", $id_country);
		$country_xml->setAttribute("name", $name_country);
		$data2 = $connect->getAll("SELECT id, name FROM direction_object WHERE id_country=?i AND id_country!=1", $id_country);
		if($data2){
			foreach($data2 as $row){
				$id_direction = $row["id"];
				$region = $country_xml->appendChild($xml->createElement("region"));
				$region->setAttribute("type", "direction");
				$region->setAttribute("id", $id_direction);
				$region->setAttribute("name", $row["name"]);
				$data3 = $connect->getAll("SELECT id, name, type, id_reg, city FROM object WHERE direction=?i AND active=0", $id_direction);
				foreach($data3 as $row){
					$prices = get_prices_object($connect, $row["id"]);
					if($prices["min"]){
						$address = $connect->getOne("SELECT name FROM region WHERE id=?i", $row["id_reg"]);
						if($row["city"])
							$address.= ", ".$row["city"];
						$object = $region->appendChild($xml->createElement("object"));
						$object->setAttribute("type", $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]));
						$object->setAttribute("id", $row["id"]);
						$object->setAttribute("name", $row["name"]);
						$object->setAttribute("min", $prices["min"]);
						if(isset($prices["min_treatment"]))
              $object->setAttribute("min_treatment", $prices["min_treatment"]);

						$object->setAttribute("address", $address);
					}
				}
			}
		}else{
			$data2 = $connect->getAll("SELECT id, name FROM region WHERE id_country=?i AND active=0", $id_country);
			foreach($data2 as $row){
				$id_region = $row["id"];
				$region = $country_xml->appendChild($xml->createElement("region"));
				$region->setAttribute("type", "region");
				$region->setAttribute("id", $id_region);
				$region->setAttribute("name", $row["name"]);
				$data3 = $connect->getAll("SELECT id, name FROM direction_object WHERE id_reg=?i", $id_region);
				if($data3){
					foreach($data3 as $row){
						$id_direction = $row["id"];
						$direction = $region->appendChild($xml->createElement("region"));
						$direction->setAttribute("type", "direction");
						$direction->setAttribute("id", $id_direction);
						$direction->setAttribute("name", $row["name"]);
						$region->setAttribute("direction", "have");
						$data4 = $connect->getAll("SELECT id, name, type, id_reg, city, regular_com, up_com FROM object WHERE direction=?i AND active=0", $id_direction);
						foreach($data4 as $row){
							$prices = get_prices_object($connect, $row["id"]);
							if($prices["min"]){
								$address = $connect->getOne("SELECT name FROM region WHERE id=?i", $row["id_reg"]);
								if($row["city"])
									$address.= ", ".$row["city"];
								$object = $direction->appendChild($xml->createElement("object"));
								$object->setAttribute("type", $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]));
								$object->setAttribute("id", $row["id"]);
								$object->setAttribute("name", $row["name"]);
								$object->setAttribute("min", $prices["min"]);
                if(isset($prices["min_treatment"]))
                  $object->setAttribute("min_treatment", $prices["min_treatment"]);

								$object->setAttribute("address", $address);
								$object->setAttribute("reg_com", $row["regular_com"]);
								$object->setAttribute("up_com", $row["up_com"]);
							}
						}
					}
				}else{
					$data3 = $connect->getAll("SELECT id, name, type, id_reg, city, regular_com, up_com FROM object WHERE id_reg=?i AND active=0", $id_region);
					foreach($data3 as $row){
						$prices = get_prices_object($connect, $row["id"]);
						if($prices["min"]){
							$address = $connect->getOne("SELECT name FROM region WHERE id=?i", $row["id_reg"]);
							if($row["city"])
								$address.= ", ".$row["city"];
							$object = $region->appendChild($xml->createElement("object"));
							$object->setAttribute("type", $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]));
							$object->setAttribute("id", $row["id"]);
							$object->setAttribute("name", $row["name"]);
							$object->setAttribute("min", $prices["min"]);
              if(isset($prices["min_treatment"]))
                $object->setAttribute("min_treatment", $prices["min_treatment"]);

							$object->setAttribute("address", $address);
							$object->setAttribute("reg_com", $row["regular_com"]);
							$object->setAttribute("up_com", $row["up_com"]);
						}
					}
				}
			}
		}
	}
	$xml->formatOutput = true;
	$xml->save("temp/all-object.xml");







	$xml = new DomDocument("1.0", "utf-8");
	$all = $xml->appendChild($xml->createElement("data"));
	$region = $all->appendChild($xml->createElement("region"));
	$region->setAttribute("id", 1);
	$region->setAttribute("name", "Поволжье");
	$region->setAttribute("type", "region");
	$data = $connect->getAll("SELECT id, name FROM region WHERE (id>=1 AND id<=10 OR id=44) AND active=0 ORDER BY id");
	foreach($data as $row){
		$id = $row["id"];
		$name_direction = $row["name"];
		$direction = $region->appendChild($xml->createElement("direction"));
		$direction->setAttribute("id", $id);
		$direction->setAttribute("name", $name_direction);
		$direction->setAttribute("min", $price_region[$id]["min"]);
    if(isset($price_region[$id]["min_treatment"]))
      $direction->setAttribute("min_treatment", $price_region[$id]["min_treatment"]);

		$direction->setAttribute("max", $price_region[$id]["max"]);
    if(isset($price_region[$id]["max_treatment"]))
      $direction->setAttribute("max_treatment", $price_region[$id]["max_treatment"]);
	}
	$region = $all->appendChild($xml->createElement("region"));
	$region->setAttribute("id", 2);
	$region->setAttribute("name", "КавМинВоды");
	$region->setAttribute("type", "direction");
	$data = $connect->getAll("SELECT id, name FROM direction_object WHERE id_reg=33 ORDER BY id");
	foreach($data as $row){
		$id = $row["id"];
		$name_direction = $row["name"];
		$direction = $region->appendChild($xml->createElement("direction"));
		$direction->setAttribute("id", $id);
		$direction->setAttribute("name", $name_direction);
		$direction->setAttribute("min", $price_region[33]["min"]);
    if(isset($price_region[33]["min_treatment"]))
      $direction->setAttribute("min_treatment", $price_region[33]["min_treatment"]);

		$direction->setAttribute("max", $price_region[33]["max"]);

    if(isset($price_region[33]["max_treatment"]))
      $direction->setAttribute("max_treatment", $price_region[33]["max_treatment"]);
	}
	$region = $all->appendChild($xml->createElement("region"));
	$region->setAttribute("id", 3);
	$region->setAttribute("name", "Краснодарский край");
	$region->setAttribute("type", "direction");
	$data = $connect->getAll("SELECT id, name FROM direction_object WHERE id_reg=27 ORDER BY id");
	foreach($data as $row){
		$id = $row["id"];
		$name_direction = $row["name"];
		$direction = $region->appendChild($xml->createElement("direction"));
		$direction->setAttribute("id", $id);
		$direction->setAttribute("name", $name_direction);
		$direction->setAttribute("min", $price_region[27]["min"]);
    if(isset($price_region[27]["min_treatment"]))
      $direction->setAttribute("min_treatment", $price_region[27]["min_treatment"]);

		$direction->setAttribute("max", $price_region[27]["max"]);
    if(isset($price_region[27]["max_treatment"]))
      $direction->setAttribute("max_treatment", $price_region[27]["max_treatment"]);


	}
	$region = $all->appendChild($xml->createElement("region"));
	$region->setAttribute("id", 4);
	$region->setAttribute("name", "Крым");
	$region->setAttribute("type", "direction");
	$data = $connect->getAll("SELECT id, name FROM direction_object WHERE id_reg=40 ORDER BY id");
	foreach($data as $row){
		$id = $row["id"];
		$name_direction = $row["name"];
		$direction = $region->appendChild($xml->createElement("direction"));
		$direction->setAttribute("id", $id);
		$direction->setAttribute("name", $name_direction);
		$direction->setAttribute("min", $price_region[40]["min"]);
    if(isset($price_region[40]["min_treatment"]))
      $direction->setAttribute("min_treatment", $price_region[40]["min_treatment"]);

		$direction->setAttribute("max", $price_region[40]["max"]);

    if(isset($price_region[40]["max_treatment"]))
      $direction->setAttribute("max_treatment", $price_region[40]["max_treatment"]);

	}
	$region = $all->appendChild($xml->createElement("region"));
	$region->setAttribute("id", 5);
	$region->setAttribute("name", "Абхазия");
	$region->setAttribute("type", "direction");
	$data = $connect->getAll("SELECT id, name FROM direction_object WHERE id_country=2 ORDER BY id");
	foreach($data as $row){
		$id = $row["id"];
		$name_direction = $row["name"];
		$direction = $region->appendChild($xml->createElement("direction"));
		$direction->setAttribute("id", $id);
		$direction->setAttribute("name", $name_direction);
	}
	$xml->formatOutput = true;
	$xml->save("temp/all-region.xml");






	$xml = new DomDocument("1.0", "utf-8");
	$images = $xml->appendChild($xml->createElement("images"));
	$data = $connect->getAll("SELECT id, image FROM object WHERE active=0 ORDER BY name");
	foreach($data as $row){
		$id = $row["id"];
		$image = $row["image"];
		if($image){
			$icon = $images->appendChild($xml->createElement("image"));
			$icon->setAttribute("id", $id);
			$icon->setAttribute("base", $image);
		}
	}
	$xml->formatOutput = true;
	$xml->save("temp/images.xml");

	save_primary_promo_XML($connect);
	save_all_promo_XML($connect);
	save_VIP_promo_XML($connect);
	save_rating_XML_company($connect);
	save_ratePlan_XML($connect);

	$connect_server = connect_to_server();

	//if($connect_server == 1)
	//	return "Ошибка соединения";
	//else{

		if($connect_server == 2)
			return "Не удалось авторизироваться";

		$ftp_folder = "/var/www/default-site/public_html/price/XML/overall/";
		$ftp_folder_image = "/var/www/default-site/public_html/price/";
		$file = "temp/profile.xml";
		if(!ftp_put($connect_server, $ftp_folder."profile.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/rateplan.xml";
		if(!ftp_put($connect_server, $ftp_folder."rateplan.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/method.xml";
		if(!ftp_put($connect_server, $ftp_folder."method.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/infa.xml";
		if(!ftp_put($connect_server, $ftp_folder."infa.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/comfort.xml";
		if(!ftp_put($connect_server, $ftp_folder."comfort.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/object.xml";
		if(!ftp_put($connect_server, $ftp_folder."object.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/all-object.xml";
		if(!ftp_put($connect_server, $ftp_folder."all-object.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/all-region.xml";
		if(!ftp_put($connect_server, $ftp_folder."all-region.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/region.xml";
		if(!ftp_put($connect_server, $ftp_folder."region.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/direction.xml";
		if(!ftp_put($connect_server, $ftp_folder."direction.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/services.xml";
		if(!ftp_put($connect_server, $ftp_folder."services.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/PrimaryPromo.xml";
		if(!ftp_put($connect_server, $ftp_folder."PrimaryPromo.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/VIPpromo.xml";
		if(!ftp_put($connect_server, $ftp_folder."VIPpromo.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/promotions.xml";
		if(!ftp_put($connect_server, $ftp_folder."promotions.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/images.xml";
		if(!ftp_put($connect_server, $ftp_folder."images.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		$file = "temp/xml/overall/rating-company.xml";
		if(!ftp_put($connect_server, $ftp_folder."rating-company.xml", $file, FTP_ASCII))
			echo "Ошибка загрузки";
		ftp_chmod($connect_server, 0644, $ftp_folder."profile.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."method.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."infa.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."comfort.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."object.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."all-object.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."all-region.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."region.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."direction.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."services.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."PrimaryPromo.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."VIPpromo.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."promotions.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."images.xml");
		ftp_chmod($connect_server, 0644, $ftp_folder."rating-company.xml");

		do_upload_images($connect_server, $directory."/temp/region/", $ftp_folder_image."/image/region/");
		do_upload_images($connect_server, $directory."/temp/direction/", $ftp_folder_image."/image/direction/");
	//}

	ftp_quit($connect_server);
	if($mis == 0)
		return FALSE;

}

function upload_method_on_server($connect){
	global $directory;
	save_methods_XML($connect);
	$connect_server = connect_to_server();
	//if($connect_server == 1)
		//return "Ошибка соединения";
	//else{
		if($connect_server == 2)
			return "Не удалось авторизироваться";

		$ftp_folder = "/var/www/default-site/public_html/price/XML/overall/";
		$ftp_image_folder = "/var/www/default-site/public_html/price/image/methods/";
		$file = $directory."/temp/methods.xml";
		if(!ftp_put($connect_server, $ftp_folder."methods.xml", $file, FTP_ASCII))
			return "Ошибка загрузки";
		ftp_chmod($connect_server, 0644, $ftp_folder."methods.xml");
		include_once($directory."/core/upload/image.php");
		do_upload_images($connect_server, $directory."/temp/method/", $ftp_image_folder);
	//}
	ftp_quit($connect_server);
}

function save_methods_XML($connect){
	$xml = new DomDocument("1.0", "utf-8");
	$methods = $xml->appendChild($xml->createElement("methods"));
	$data = $connect->getAll("SELECT id, name, description FROM methods ORDER BY name");
	$i = 0;
	foreach($data as $row){
		$i++;
		$method = $methods->appendChild($xml->createElement("method"));
		$method->setAttribute("id", $row["id"]);
		$method->setAttribute("name", $row["name"]);
		$method->appendChild($xml->createTextNode($row["description"]));
	}
	$methods->setAttribute("count", $i);
	$xml->formatOutput = true;
	$xml->save("temp/methods.xml");
}

function save_ratePlan_XML($connect){
	$xml = new DomDocument("1.0", "utf-8");
	$ratePlans = $xml->appendChild($xml->createElement("ratePlans"));
	$data = $connect->getAll("SELECT id, name, object, description, food, days FROM rate_plan");
	$i = 0;
	foreach($data as $row){
		$i++;
		$ratePlan = $ratePlans->appendChild($xml->createElement("ratePlan"));
		$ratePlan->setAttribute("id", $row["id"]);
		$ratePlan->setAttribute("name", str_replace("\"", "", $row["name"]));
		$ratePlan->setAttribute("food", str_replace("\"", "", $row["food"]));
		$ratePlan->setAttribute("days", $row["days"]);
		$ratePlan->setAttribute("object", $row["object"]);
		$ratePlan->appendChild($xml->createTextNode(str_replace("\"", "", $row["description"])));
	}
	$xml->formatOutput = true;
	$xml->save("temp/rateplan.xml");
}

function save_primary_promo_XML($connect){
	$xml = new DomDocument("1.0", "utf-8");
	$promotions = $xml->appendChild($xml->createElement("promotions"));
	$data = $connect->getAll("SELECT object.id FROM reservation, object_room, room, object WHERE reservation.status=1 AND reservation.active=0 AND reservation.room=object_room.id AND object_room.id_category=room.id AND room.id_obj=object.id AND reservation.sum!='' GROUP BY object.id");
	foreach($data as $row){
		$promo = $promotions->appendChild($xml->createElement("promo"));
		$promo->setAttribute("type", "guaranteed");
		$promo->setAttribute("id_obj", $row["id"]);
		$promo->setAttribute("object", get_object($connect, $row["id"]));
		$id_reg = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $row["id"]);
		$promo->setAttribute("id_reg", $id_reg);
	}
	$data = $connect->getAll("SELECT type, id_obj, title, text FROM promotions WHERE active=2 OR active=3");
	foreach($data as $row){
		$type = $row["type"];
		$title = $row["title"];
		$text = $row["text"];
		$id_obj = $row["id_obj"];
		$id_reg = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $id_obj);
		$promo = $promotions->appendChild($xml->createElement("promo"));
		$promo->setAttribute("type", $type);
		$promo->setAttribute("id_obj", $id_obj);
		$promo->setAttribute("id_reg", $id_reg);
		$promo->setAttribute("object", get_object($connect, $id_obj));
		$title_xml = $promo->appendChild($xml->createElement("title"));
		$title_xml->appendChild($xml->createTextNode("$title"));
		$text_xml = $promo->appendChild($xml->createElement("text"));
		$text_xml->appendChild($xml->createTextNode("$text"));
	}

	$xml->formatOutput = true;
	$xml->save("temp/PrimaryPromo.xml");
}

function save_VIP_promo_XML($connect){
	$xml = new DomDocument("1.0", "utf-8");
	$promotions = $xml->appendChild($xml->createElement("promotions"));
	$data = $connect->getAll("SELECT type, id_obj, title, text FROM promotions WHERE active=3");
	foreach($data as $row){
		$type = $row["type"];
		$title = $row["title"];
		$text = $row["text"];
		$id_obj = $row["id_obj"];
		$id_reg = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $id_obj);
		$url_name = $connect->getOne("SELECT url_name FROM object WHERE id=?i", $id_obj);
		$promo = $promotions->appendChild($xml->createElement("promo"));
		$promo->setAttribute("type", $type);
		$promo->setAttribute("id_obj", $id_obj);
		$promo->setAttribute("id_reg", $id_reg);
		$promo->setAttribute("object", get_object($connect, $id_obj, "type"));
		$promo->setAttribute("object_url", $url_name);
		$title_xml = $promo->appendChild($xml->createElement("title"));
		$title_xml->appendChild($xml->createTextNode("$title"));
		$text_xml = $promo->appendChild($xml->createElement("text"));
		$text_xml->appendChild($xml->createTextNode("$text"));
	}

	$xml->formatOutput = true;
	$xml->save("temp/VIPpromo.xml");
}

function save_all_promo_XML($connect){
	$xml = new DomDocument("1.0", "utf-8");
	$objects = $xml->appendChild($xml->createElement("objects"));
	$data = $connect->getAll("SELECT id, id_reg FROM object WHERE active=0");
	foreach($data as $row){
		$id_obj = $row["id"];
		$id_reg = $row["id_reg"];
		$data2 = $connect->getAll("SELECT id, type, title, text, active FROM promotions WHERE active!=0 AND id_obj=?i ORDER BY active DESC", $id_obj);
		if(count($data2)){
			$object = $objects->appendChild($xml->createElement("object"));
			$object->setAttribute("id", $id_obj);
			$object->setAttribute("id_reg", $id_reg);
			$object->setAttribute("region", $connect->getOne("SELECT name FROM region WHERE id=?i", $id_reg));
			$row = $connect->getRow("SELECT name, type FROM object WHERE id=?i", $id_obj);
			$object->setAttribute("object", $row["name"]);
			$object->setAttribute("type", $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]));
			foreach($data2 as $row){
				$id = $row["id"];
				$type = $row["type"];
				$active = $row["active"];
				$title = $row["title"];
				$text = $row["text"];
				$promo = $object->appendChild($xml->createElement("promo"));
				$promo->setAttribute("type", $type);
				$promo->setAttribute("number", $id);
				$promo->setAttribute("primary", $active);
				$title_xml = $promo->appendChild($xml->createElement("title"));
				$title_xml->appendChild($xml->createTextNode("$title"));
				$text_xml = $promo->appendChild($xml->createElement("text"));
				$text_xml->appendChild($xml->createTextNode("$text"));
			}
		}
	}

	$xml->formatOutput = true;
	$xml->save("temp/promotions.xml");
}

function have_price($connect, $id_date, $id_room){
	$data = $connect->getAll("SELECT id FROM ranges WHERE id_date=?i", $id_date);
	foreach($data as $row){
		$id_range = $row["id"];
		$have = $connect->getOne("SELECT id FROM price WHERE id_range=?i AND id_room=?i", $id_range, $id_room);
		if($have)
			return 1;
	}
	return FALSE;
}

function get_prices_object($connect, $id){
	$array = array("min" => 0, "max" => 0);
	$check_places = $connect->getOne("SELECT check_places FROM object WHERE id=?i", $id);
	if($check_places == 1){
		$time = time();
		$data = $connect->getAll("SELECT price_places FROM room WHERE id_obj=?i AND price_places!='' ORDER BY `housing` ASC", $id);
		foreach($data as $row){
			$prices = json_decode($row["price_places"], TRUE);
			foreach($prices as $price_rate_plan){
				foreach($price_rate_plan as $price_data){
					$date = $price_data["dt"];
					if($date > $time){
						foreach($price_data["p"] as $place => $value){
							if($place != "add" AND $value > 0){
								$current = $value;
								if($array["min"] == 0 OR $current < $array["min"])
									$array["min"] = (int)$current;
							}
						}
					}
				}
			}
		}
	}else{
		$min_row = $connect->getRow("SELECT price.price AS price, ranges.treatment AS treatment FROM price, ranges, date_price WHERE (ranges.active=0 AND price.active=0 AND ranges.id_obj=?i AND price.id_range=ranges.id AND ranges.place=1 AND date_price.id_obj=?i AND ranges.id_date=date_price.id AND date_price.active=0) ORDER BY price.price ASC LIMIT 1", $id, $id);
		$array["min"] = (int)$min_row['price'];
		$array["min_treatment"] = (int)$min_row['treatment'];
	}
//	if(!$array["min"])
//		$array["min"] = $connect->getOne("SELECT price.price FROM price, ranges, date_price WHERE ranges.id_obj=?i AND price.id_range=ranges.id AND ranges.place=1 AND date_price.id_obj=?i AND ranges.id_date=date_price.id AND date_price.end>=?s ORDER BY price.price ASC LIMIT 1", $id, $id, date("Y-m-d", strtotime("-6 month")));
	//$array["max"] = $connect->getOne("SELECT price.price FROM price, ranges WHERE (ranges.active=0 AND price.active=0 AND ranges.id_obj=?i AND price.id_range=ranges.id AND ranges.place=1) ORDER BY price.price DESC LIMIT 1", $id);
	return $array;
}

function get_prices_region($connect, $region){
	$price = array("min" => 0, "max" => 0);
	$data = $connect->getAll("SELECT id FROM object WHERE id_reg=?i AND active=0", $region);
	foreach($data as $row){
		$prices = get_prices_object($connect, $row["id"]);
		if($prices["min"] < $price["min"] OR $price["min"] == 0)
			$price["min"] = $prices["min"];
		if($prices["min"] > $price["max"] OR $price["max"] == 0)
			$price["max"] = $prices["min"];
	}
	return $price;
}

function get_min_price($connect, $id_room){
	$array_type = array(1 => "за чел/сутки", 2 => "за дом/сутки", 3 => "за номер/сутки", 4 => "за заезд");
	$time = time();
	$answer = array();
	$data = $connect->getAll("SELECT price.price AS price, ranges.id_date AS id_date, ranges.type AS type, ranges.treatment AS treatment FROM price, ranges WHERE price.id_room=?i AND price.active=0 AND price.id_range=ranges.id AND ranges.place=1 ORDER BY price ASC", $id_room);
	$min_price = 0;
	foreach($data as $row){
		if($min_price == 0)
			$min_price = $row["price"];
		$end = $connect->getOne("SELECT end FROM date_price WHERE id=?i", $row["id_date"]);
		$end = strToTime($end);
		if($end > $time){
			$answer["price"] = $row["price"];
			$answer["type"] = $array_type[$row["type"]];
			$answer["treatment"] = $row["treatment"];
			return $answer;
		}
	}
	$data = $connect->getAll("SELECT price, id_date, type FROM price, ranges WHERE price.id_room=?i AND price.active=0 AND price.id_range=ranges.id AND place=1 ORDER BY price ASC", $id_room);
	foreach($data as $row){
		if($min_price == 0)
			$min_price = $row["price"];
		$end = $connect->getOne("SELECT end FROM date_price WHERE id=?i", $row["id_date"]);
		$end = strToTime($end);
		if($end > $time){
			$answer["price"] = $row["price"];
			$answer["type"] = $array_type[$row["type"]];
      $answer["treatment"] = $row["treatment"];
			return $answer;
		}
	}
}

?>
