<?php

function upload_promo_object_on_server($connect, $id){
	$ftp_folder = "/var/www/default-site/public_html/price/XML/";
	if(!$id)
		$id = $_POST["id"];
	$connect_server = connect_to_server_directory();
//	if($connect_server == 1)
//		return "<div class='alert alert-danger'>Ошибка соединения</div>";
	if($connect_server == 2)
		return "<div class='alert alert-danger'>Не удалось авторизироваться</div>";
	$connect->query("UPDATE promotions SET active=0 WHERE date_end<?s", date("Y-m-d"));
	if(!$id)
		$data = $connect->getAll("SELECT object.id FROM object, region WHERE region.id=object.id_reg AND region.active=0 AND object.active=0 GROUP BY object.id");
	else
		$data = $connect->getAll("SELECT id FROM object WHERE id=?i", $id);
	foreach($data as $row){
		$id = $row["id"];
		save_promo_XML_object($connect, $id);
		$file = "temp/xml/promo/".$id.".xml";
		$server_file = $ftp_folder."promo/".$id.".xml";
		if(!ftp_put($connect_server, $server_file, $file, FTP_ASCII))
			return "Не удалось загрузить файл на сервер";
		ftp_chmod($connect_server, 0644, $server_file);
	}
	save_primary_promo_XML($connect);
	save_VIP_promo_XML($connect);
	save_all_promo_XML($connect);
	ftp_put($connect_server, $ftp_folder."overall/VIPpromo.xml", "temp/VIPpromo.xml", FTP_ASCII);
	ftp_put($connect_server, $ftp_folder."overall/PrimaryPromo.xml", "temp/PrimaryPromo.xml", FTP_ASCII);
	ftp_put($connect_server, $ftp_folder."overall/promotions.xml", "temp/promotions.xml", FTP_ASCII);
	ftp_chmod($connect_server, 0644, $ftp_folder."overall/VIPpromo.xml");
	ftp_chmod($connect_server, 0644, $ftp_folder."overall/PrimaryPromo.xml");
	ftp_chmod($connect_server, 0644, $ftp_folder."overall/promotions.xml");
	ftp_quit($connect_server);
	if(!$_POST["id"])
		return "<div class='alert alert-success'>Загрузка завершена!</div>";
	return "<div class='alert alert-success'>Загрузка завершена!<br /><a class='alert-link' href='http://xn----dtbmnhpbbghbyj0jwa2c.xn--p1ai/price.html#object/".$id."/promo' target='_blank'><i class='fa fa-smile-o'></i> Посмотреть как это выглядит на сайте</a></div>";
}

function save_promo_XML_object($connect, $id){
	$xml = new DomDocument("1.0", "utf-8");
	$promotions = $xml->appendChild($xml->createElement("object"));
	$data = $connect->getAll("SELECT id, type, id_room, title, text, active FROM promotions WHERE active!=0 AND id_obj=?i ORDER BY active DESC", $id);
	foreach($data as $row){
		$id_promo = $row["id"];
		$type = $row["type"];
		$room = $row["id_room"];
		$title = $row["title"];
		$text = $row["text"];
		$active = $row["active"];
		$promo = $promotions->appendChild($xml->createElement("promo"));
		if($room){
			$promo->setAttribute("id_room", $room);
			$promo->setAttribute("room", $connect->getOne("SELECT name FROM room WHERE id=?i", $room));
		}
		$promo->setAttribute("type", $type);
		$promo->setAttribute("number", $id_promo);
		if($active == 2 OR $active == 3)
			$promo->setAttribute("primary", 1);
		$title_xml = $promo->appendChild($xml->createElement("title"));
		$title_xml->appendChild($xml->createTextNode("$title"));
		$text_xml = $promo->appendChild($xml->createElement("text"));
		$text_xml->appendChild($xml->createTextNode("$text"));
	}
	if($connect->getOne("SELECT object.id FROM reservation, object_room, room, object WHERE reservation.status=1 AND reservation.active=0 AND object.id=?i AND reservation.room=object_room.id AND object_room.id_category=room.id AND room.id_obj=object.id AND reservation.sum!='' LIMIT 1", $id)){
		$promo = $promotions->appendChild($xml->createElement("promo"));
		$promo->setAttribute("type", "guaranteed");
		$promo->setAttribute("primary", "1");
	}
	$rating = select_last_rating($connect, $id);
	if(count($rating) > 0)
		$promotions->setAttribute("rating", json_encode($rating));
	$xml->formatOutput = true;
	$xml->save("temp/xml/promo/".$id.".xml");
}

function select_last_rating($connect, $id){
	$count = 0;
	$rating = array();
	$data = $connect->getAll("SELECT DATE_FORMAT(date_send, '%d.%m.%Y') as date, positive, turist, schet FROM rating WHERE id_obj=?i AND positive!='' AND status=3 ORDER BY date_send DESC", $id);
	foreach($data as $row){
		$positive = $row["positive"];
		if(strlen($positive) > 20){
			$count++;
			$rating[$count] = array();
			$positive = mb_substr($positive, 0, 80, "UTF-8");
			$rating[$count]["date"] = $row["date"];
			$rating[$count]["text"] = $positive;
			if($row["turist"])
				$rating[$count]["turist"] = $row["turist"];
			else{
				$turist = $connect->getOne("SELECT turist FROM reckoning WHERE id=?i", $row["schet"]);
				$rating[$count]["turist"] = $connect->getOne("SELECT name FROM klient WHERE id=?i", $turist);
			}
		}
		if($count == 5)
			return $rating;
	}
	return $rating;
}

?>
