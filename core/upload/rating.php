<?php

function upload_rating_object($connect){
	$connect_server = connect_to_server();
	//if($connect_server == 1)
	//	return "Ошибка соединения";
	if($connect_server == 2)
		return "<div class='alert alert-danger'>Не удалось авторизироваться</div>";
	$data = $connect->getAll("SELECT id, schet FROM rating WHERE id_obj=96 AND status=3");
	foreach($data as $row){
		$id = $row["id"];
		$id_room = $connect->getOne("SELECT id_room FROM position_reck WHERE schet=?i", $row["schet"]);
		$id_obj = $connect->getOne("SELECT id_obj FROM room WHERE id=?i", $id_room);
		$connect->query("UPDATE rating SET id_obj=?i WHERE id=?i", $id_obj, $id);
	}
	$data = $connect->getAll("SELECT object.id FROM object, region WHERE region.id=object.id_reg AND (object.active=0 OR object.active=1) GROUP BY object.id");
	foreach($data as $row){
		$id = $row["id"];
		if(save_rating_XML_object($connect, $id)){
			$file = "temp/xml/rating/".$id.".xml";
			$server_file = "/var/www/default-site/public_html/price/XML/rating/".$id.".xml";
			if(!ftp_put($connect_server, $server_file, $file, FTP_ASCII))
				return "<div class='alert alert-danger'>Не удалось загрузить файл на сервер</div>";
			ftp_chmod($connect_server, 0644, $server_file);
		}
	}

	ftp_quit($connect_server);

	return "<div class='alert alert-success'>Отзывы обновлены</div>";
}

function save_rating_XML_object($connect, $id){
	$count = $connect->getOne("SELECT COUNT(*) FROM rating WHERE id_obj=?i AND status=3", $id);
	if($count > 0){
		$page = 1;
		$count_page = 0;
		$xml = new DomDocument("1.0", "utf-8");
		$object = $xml->appendChild($xml->createElement("object"));
		$row = $connect->getRow("SELECT name, type FROM object WHERE id=?i", $id);
		$object->setAttribute("name", $row["name"]);
		$object->setAttribute("type", $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]));
		$object->setAttribute("count", $count);
		$object->setAttribute("name_url", change_text_url($row["name"]));
		$positive = mb_substr($connect->getOne("SELECT positive FROM rating WHERE status=3 AND id_obj=?i AND positive!='' ORDER BY date_send DESC", $id), 0, 80, "UTF-8");
		$object->setAttribute("positive", $positive);
		$data = $connect->getAll("SELECT id, schet, DATE_FORMAT(date_send, '%d.%m.%Y') as date, clean, comfort, location, treatment, staff, leisure, ratio, positive, negative, advice, photos, company_rating, turist, site_from FROM rating WHERE status=3 AND id_obj=?i ORDER BY date_send DESC", $id);
		$average_object = 0;
		foreach($data as $row){
			$schet = $row["schet"];
			$date = month_transform($row["date"]);
			$klient = $connect->getOne("SELECT turist FROM reckoning WHERE id=?i", $schet);
			$klient = $connect->getOne("SELECT name FROM klient WHERE id=?i", $klient);
			if(!$schet)
				$klient = $row["turist"];
			$count_rating = 6;
			$average = $row["clean"] + $row["comfort"] + $row["location"] + $row["staff"] + $row["treatment"] + $row["leisure"] + $row["ratio"];
			if($row["treatment"] != 0)
				$count_rating++;
			$average_clean+= (int)$row["clean"];
			$average_comfort+= (int)$row["comfort"];
			$average_leisure+= (int)$row["leisure"];
			$average_ratio+= (int)$row["ratio"];
			$average_location+= (int)$row["location"];
			$average_staff+= (int)$row["staff"];
			$average_treatment+= (int)$row["treatment"];
			$average = round($average / $count_rating * 2, 1);
			$average_object+= $average;

			$rating = $object->appendChild($xml->createElement("rating"));
			$rating->setAttribute("number", $row["id"]);
			$rating->setAttribute("average", $average);

			if($average >= 9)
				$average_text = "Превосходно";
			elseif($average >= 8)
				$average_text = "Очень хорошо";
			elseif($average >= 7)
				$average_text = "Хорошо";
			elseif($average >= 6)
				$average_text = "Нормально";
			elseif($average >= 5)
				$average_text = "Посредственно";
			else
				$average_text = "Плохо";
			$rating->setAttribute("average_text", $average_text);

			$turist = $rating->appendChild($xml->createElement("turist"));
			$turist->appendChild($xml->createTextNode($klient));
			$date_send = $rating->appendChild($xml->createElement("date"));
			$date_send->appendChild($xml->createTextNode($date));
			$clean = $rating->appendChild($xml->createElement("clean"));
			$clean->appendChild($xml->createTextNode($row["clean"] * 2));
			$comfort = $rating->appendChild($xml->createElement("comfort"));
			$comfort->appendChild($xml->createTextNode($row["comfort"] * 2));
			$location = $rating->appendChild($xml->createElement("location"));
			$location->appendChild($xml->createTextNode($row["location"] * 2));
			$treatment = $rating->appendChild($xml->createElement("treatment"));
			$treatment->appendChild($xml->createTextNode($row["treatment"] * 2));
			$staff = $rating->appendChild($xml->createElement("staff"));
			$staff->appendChild($xml->createTextNode($row["staff"] * 2));
			$leisure = $rating->appendChild($xml->createElement("leisure"));
			$leisure->appendChild($xml->createTextNode($row["leisure"] * 2));
			$ratio = $rating->appendChild($xml->createElement("ratio"));
			$ratio->appendChild($xml->createTextNode($row["ratio"] * 2));
			$positive = $rating->appendChild($xml->createElement("positive"));
			$positive->appendChild($xml->createTextNode($row["positive"]));
			$negative = $rating->appendChild($xml->createElement("negative"));
			$negative->appendChild($xml->createTextNode($row["negative"]));
			$advice = $rating->appendChild($xml->createElement("advice"));
			$advice->appendChild($xml->createTextNode($row["advice"]));
			$company = $rating->appendChild($xml->createElement("company"));
			$company->appendChild($xml->createTextNode($row["company_rating"]));
			if($row["site_from"] != ""){
				$site_from = $rating->appendChild($xml->createElement("site_from"));
				$site_from->appendChild($xml->createTextNode($row["site_from"]));
			}
			if($row["photos"] != ""){
				$photos = $rating->appendChild($xml->createElement("photos"));
				$photos->appendChild($xml->createTextNode($row["photos"]));
			}
			$count_page++;
			$rating->setAttribute("page", $page);
			if($count_page >= 30){
				$count_page = 0;
				$page++;
			}
			if($connect->getOne("SELECT id FROM rating_comment WHERE status=1 AND rating=?i", $row["id"])){
				$comments = $connect->getAll("SELECT name, text, DATE_FORMAT(time, '%d.%m.%Y') as date FROM rating_comment WHERE status=1 AND rating=?i", $row["id"]);
				$comments_rating = $rating->appendChild($xml->createElement("comments"));
				$comments_rating->setAttribute("count", count($comments));
				$comments_rating->appendChild($xml->createTextNode(json_encode($comments)));
			}
		}
		$average = round($average_object / $count, 1);
		if($average >= 9)
			$average_text = "Превосходно";
		elseif($average >= 8)
			$average_text = "Очень хорошо";
		elseif($average >= 7)
			$average_text = "Хорошо";
		elseif($average >= 6)
			$average_text = "Нормально";
		elseif($average >= 5)
			$average_text = "Посредственно";
		else
			$average_text = "Плохо";
		$object->setAttribute("treatment", round($average_treatment * 2 / $count, 1));
		$object->setAttribute("clean", round($average_clean * 2 / $count, 1));
		$object->setAttribute("comfort", round($average_comfort * 2 / $count, 1));
		$object->setAttribute("leisure", round($average_leisure * 2 / $count, 1));
		$object->setAttribute("ratio", round($average_ratio * 2 / $count, 1));
		$object->setAttribute("location", round($average_location * 2 / $count, 1));
		$object->setAttribute("staff", round($average_staff * 2 / $count, 1));

		$object->setAttribute("average", $average);
		$object->setAttribute("average_text", $average_text);
		$object->setAttribute("page", $page);
		$xml->formatOutput = true;
		$xml->save("temp/xml/rating/".$id.".xml");
		return 1;
	}
	return FALSE;
}

function save_rating_XML_company($connect){
	$xml = new DomDocument("1.0", "utf-8");
	$ratings = $xml->appendChild($xml->createElement("ratings"));

	$page = 1;
	$count = 0;
	$data = $connect->getAll("SELECT schet, turist, DATE_FORMAT(date_send, '%d.%m.%Y') as date, id_obj, company_rating FROM rating WHERE company_rating!='' AND status=3 ORDER BY date_send DESC");
	foreach($data as $row){
		$region = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $row["id_obj"]);
		if($connect->getOne("SELECT id FROM region WHERE id=?i AND id_country=1", $region)){
			$count++;
			$reck = $row["schet"];
			$client = $connect->getOne("SELECT turist FROM reckoning WHERE id=?i", $reck);
			$client = $connect->getOne("SELECT name FROM klient WHERE id=?i", $client);
			if(!$reck)
				$client = $row["turist"];
			$object = get_object($connect, $row["id_obj"], "type");
			$url = $connect->getOne("SELECT url_name FROM object WHERE id=?i", $row["id_obj"]);
			$date = month_transform($row["date"]);

			$rating = $ratings->appendChild($xml->createElement("rating"));
			$company = $rating->appendChild($xml->createElement("text"));
			$company->appendChild($xml->createTextNode($row["company_rating"]));
			$rating->setAttribute("turist", $client);
			$rating->setAttribute("object", $object);
			$rating->setAttribute("url", $url);
			$rating->setAttribute("date", $date);
			$rating->setAttribute("page", $page);
			if($count >= 20){
				$count = 0;
				$page++;
			}
		}
	}

	$ratings->setAttribute("page", $page);
	$xml->formatOutput = true;
	$xml->save("temp/xml/overall/rating-company.xml");
}

?>
