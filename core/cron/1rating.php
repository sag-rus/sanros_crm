<?php

	$directory = dirname(__FILE__)."/../..";
	include_once($directory."/core/functions.php");
	include_once($directory."/core/lib/mail.php");
	include_once($directory."/core/lib/Mysql.Class.php");
	$connect = connect_to_MySQL_directory();

	include_once($directory."/config.php");
	$conf = new JConfig;
	$sync_host = $conf->sync_host;
	
	$date_v = strtotime("-4 days", time());
	$date_v = date("Y-m-d", $date_v);

	$data = $connect->getAll("SELECT id, id_obj, turist FROM reckoning WHERE status=5 AND date_v=?s AND turist!=''", $date_v);
	foreach($data as $row){
		$id = $row["id"];
		$id_obj = $row["id_obj"];
		$object = get_object($connect, $id_obj, "type");
		$client = $row["turist"];
		$row = $connect->getRow("SELECT surname, name, otch, email FROM klient WHERE email!='' AND id=?i", $client);
		$name = $row["name"]." ".$row["otch"];
		$email = $row["email"];
		if($email AND !$connect->getOne("SELECT id FROM rating WHERE schet=?i", $id)){
			$today = date("Y-m-d");
			$hash = md5(uniqid());
			$href = "http://xn----7sba6aaba8akdsdekah.xn--p1ai/client/оставить-отзыв/".$hash;
			$connect->query("INSERT INTO rating(schet, date, hash, id_obj) VALUES (?i, ?s, ?s, ?i)", $id, $today, $hash, $id_obj);
			$image = $sync_host."/price/object/head/default.jpg";
			if($connect->getOne("SELECT image FROM object WHERE id=?i", $id_obj))
				$image = $sync_host."/price/object/head/".$id_obj.".jpg";
			$message = select_template_letter("cron/rating", "client", $id);
			$message["content"] = str_replace("<name>", $name, $message["content"]);
			$message["content"] = str_replace("<object>", $object, $message["content"]);
			$message["content"] = str_replace("<image>", $image, $message["content"]);
			$message["content"] = str_replace("<href>", $href, $message["content"]);
			$message["title"] = str_replace("<object>", $object, $message["title"]);
			send_mail($email, $message["title"], $message["content"]);
			sleep(3);
			echo $id." ".$email."<br />";
		}
	}

	$date_v = strtotime("-14 days", time());
	$date_v = date("Y-m-d", $date_v);

	$data = $connect->getAll("SELECT id, id_obj, turist FROM reckoning WHERE status=5 AND date_v=?s AND turist!=''", $date_v);
	foreach($data as $row){
		$id = $row["id"];
		$id_obj = $row["id_obj"];
		$object = get_object($connect, $id_obj, "type");
		$client = $row["turist"];
		$row = $connect->getRow("SELECT surname, name, otch, email FROM klient WHERE email!='' AND id=?i", $client);
		$name = $row["name"]." ".$row["otch"];
		$email = $row["email"];
		$id_rating = $connect->getOne("SELECT id FROM rating WHERE schet=?i AND (status=0 OR status=1)", $id);
		if($email AND $id_rating){
			$today = date("Y-m-d");
			$hash = $connect->getOne("SELECT hash FROM rating WHERE id=?i", $id_rating);
			$href = "http://xn----7sba6aaba8akdsdekah.xn--p1ai/client/оставить-отзыв/".$hash;
			$image = $sync_host."/price/object/head/default.jpg";
			if($connect->getOne("SELECT image FROM object WHERE id=?i", $id_obj))
				$image = $sync_host."/price/object/head/".$id_obj.".jpg";
			$message = select_template_letter("cron/rating-reminder", "client", $id);
			$message["content"] = str_replace("<name>", $name, $message["content"]);
			$message["content"] = str_replace("<object>", $object, $message["content"]);
			$message["content"] = str_replace("<image>", $image, $message["content"]);
			$message["content"] = str_replace("<href>", $href, $message["content"]);
			$message["title"] = str_replace("<object>", $object, $message["title"]);
			send_mail($email, $message["title"], $message["content"]);
			sleep(3);
			echo $id." ".$email."<br />";
		}
	}

?>
