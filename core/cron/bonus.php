<?php

	$directory = dirname(__FILE__)."/../..";
	include_once($directory."/core/functions.php");
	include_once($directory."/core/lib/mail.php");
	include_once($directory."/core/lib/Mysql.Class.php");
	$connect = connect_to_MySQL_directory();

	$clear_date = date("Y-m-d", strtotime("-18 month"));
	$clear_to_date = date("Y-m-d", strtotime("-18 month -3 days"));

	$connect->query("UPDATE reckoning SET id_obj=96 WHERE (id_obj=61 OR id_obj=62 OR id_obj=63 OR id_obj=64 OR id_obj=71 OR id_obj=138 OR id_obj=494)");
	$connect->query("DELETE FROM bonus WHERE sum=0");

	$data = $connect->getAll("SELECT id FROM reckoning WHERE status=5 AND date_v<=?s AND date_v>=?s AND turist!=''", $clear_date, $clear_to_date);
	foreach($data as $row){
		$id = $row["id"];
		$connect->query("UPDATE bonus SET active=0 WHERE schet=?i", $id);
	}

	$clear_date = date("Y-m-d", strtotime("-17 month"));
	$data = $connect->getAll("SELECT id, date_v, turist, id_obj, DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, DATE_FORMAT(date_v, '%d.%m.%Y') as date_v FROM reckoning WHERE status=5 AND date_v=?s AND turist!=''", $clear_date);
	foreach($data as $row){
		$id = $row["id"];
		$id_obj = $row["id_obj"];
		$client = $row["turist"];
		$date_z = month_transform($row["date_z"]);
		$date_v = month_transform($row["date_v"]);
		$bonus_minus = 0;
		$bonus_row = $connect->getRow("SELECT id, sum FROM bonus WHERE sum>0 AND schet=?i", $id);
		$bonus_plus = $bonus_row["sum"];
		$bonus_id = $bonus_row["id"];
		$array = $connect->getAll("SELECT sum FROM bonus WHERE turist=?i AND sum<0", $client);
		foreach($array as $minus)
			$bonus_minus+= $minus["sum"];
		if(($bonus_plus + $bonus_minus) > 0){
			$turist_array = $connect->getRow("SELECT name, otch, email FROM klient WHERE id=?i", $client);
			$email = $turist_array["email"];
			if($email){
				$bonus = all_klient_bonus($connect, $client);
				$object = "«".get_object($connect, $id_obj, "type")."»";
				$turist = $turist_array["name"]." ".$turist_array["otch"];
				$message = select_template_letter("cron/bonus", "client");
				$message["title"] = str_replace("<object>", $object, $message["title"]);
				$message["content"] = str_replace("<name>", $turist, $message["content"]);
				$message["content"] = str_replace("<bonus>", $bonus, $message["content"]);
				$message["content"] = str_replace("<end_bonus>", $bonus_plus, $message["content"]);
				$message["content"] = str_replace("<object>", $object, $message["content"]);
				$message["content"] = str_replace("<date_z>", $date_z, $message["content"]);
				$message["content"] = str_replace("<date_v>", $date_v, $message["content"]);
				$message["content"] = str_replace("<linia>", $linia, $message["content"]);
				$message["content"] = str_replace("<tel>", $tel, $message["content"]);
				$message["content"] = str_replace("<email>", $new_email, $message["content"]);
				$message["content"] = str_replace("<telephones>", $telephone, $message["content"]);
				$message["content"] = str_replace("<time_end>", ", и они заканчиваются через <strong>30 дней</strong>", $message["content"]);
				$message["content"] = str_replace("<email_request>", $email, $message["content"]);
				send_mail($email, $message["title"], $message["content"]);
				sleep(3);
			}
		}
	}

	$clear_date = date("Y-m-d", strtotime("-15 month"));
	$data = $connect->getAll("SELECT id, date_v, turist, id_obj, DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, DATE_FORMAT(date_v, '%d.%m.%Y') as date_v FROM reckoning WHERE status=5 AND date_v=?s AND turist!=''", $clear_date);
	foreach($data as $row){
		$id = $row["id"];
		$id_obj = $row["id_obj"];
		$client = $row["turist"];
		$date_z = month_transform($row["date_z"]);
		$date_v = month_transform($row["date_v"]);
		$bonus_minus = 0;
		$bonus_row = $connect->getRow("SELECT id, sum FROM bonus WHERE sum>0 AND schet=?i", $id);
		$bonus_plus = $bonus_row["sum"];
		$bonus_id = $bonus_row["id"];
		$array = $connect->getAll("SELECT sum FROM bonus WHERE turist=?i AND sum<0", $client);
		foreach($array as $minus)
			$bonus_minus+= $minus["sum"];
		if(($bonus_plus + $bonus_minus) > 0){
			$turist_array = $connect->getRow("SELECT name, otch, email FROM klient WHERE id=?i", $client);
			$email = $turist_array["email"];
			if($email){
				$bonus = all_klient_bonus($connect, $client);
				$object = "«".get_object($connect, $id_obj, "type")."»";
				$turist = $turist_array["name"]." ".$turist_array["otch"];
				$message = select_template_letter("cron/bonus", "client");
				$message["title"] = str_replace("<object>", $object, $message["title"]);
				$message["content"] = str_replace("<name>", $turist, $message["content"]);
				$message["content"] = str_replace("<bonus>", $bonus, $message["content"]);
				$message["content"] = str_replace("<end_bonus>", $bonus_plus, $message["content"]);
				$message["content"] = str_replace("<object>", $object, $message["content"]);
				$message["content"] = str_replace("<date_z>", $date_z, $message["content"]);
				$message["content"] = str_replace("<date_v>", $date_v, $message["content"]);
				$message["content"] = str_replace("<linia>", $linia, $message["content"]);
				$message["content"] = str_replace("<tel>", $tel, $message["content"]);
				$message["content"] = str_replace("<email>", $new_email, $message["content"]);
				$message["content"] = str_replace("<telephones>", $telephone, $message["content"]);
				$message["content"] = str_replace("<time_end>", ", и они заканчиваются через <strong>3 месяца</strong>", $message["content"]);
				$message["content"] = str_replace("<email_request>", $email, $message["content"]);
				send_mail($email, $message["title"], $message["content"]);
				sleep(3);
			}
		}
	}

	$clear_date = date("Y-m-d", strtotime("-12 month"));
	$data = $connect->getAll("SELECT id, date_v, turist, id_obj, DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, DATE_FORMAT(date_v, '%d.%m.%Y') as date_v FROM reckoning WHERE status=5 AND date_v=?s AND turist!=''", $clear_date);
	foreach($data as $row){
		$id = $row["id"];
		$id_obj = $row["id_obj"];
		$client = $row["turist"];
		$date_z = month_transform($row["date_z"]);
		$date_v = month_transform($row["date_v"]);
		$bonus_minus = 0;
		$bonus_row = $connect->getRow("SELECT id, sum FROM bonus WHERE sum>0 AND schet=?i", $id);
		$bonus_plus = $bonus_row["sum"];
		$bonus_id = $bonus_row["id"];
		$array = $connect->getAll("SELECT sum FROM bonus WHERE turist=?i AND sum<0", $client);
		foreach($array as $minus)
			$bonus_minus+= $minus["sum"];
		if(($bonus_plus + $bonus_minus) > 0){
			$turist_array = $connect->getRow("SELECT name, otch, email FROM klient WHERE id=?i", $client);
			$email = $turist_array["email"];
			if($email){
				$bonus = all_klient_bonus($connect, $client);
				$object = "«".get_object($connect, $id_obj, "type")."»";
				$turist = $turist_array["name"]." ".$turist_array["otch"];
				$message = select_template_letter("cron/bonus", "client");
				$message["title"] = str_replace("<object>", $object, $message["title"]);
				$message["content"] = str_replace("<name>", $turist, $message["content"]);
				$message["content"] = str_replace("<bonus>", $bonus, $message["content"]);
				$message["content"] = str_replace("<end_bonus>", $bonus_plus, $message["content"]);
				$message["content"] = str_replace("<object>", $object, $message["content"]);
				$message["content"] = str_replace("<date_z>", $date_z, $message["content"]);
				$message["content"] = str_replace("<date_v>", $date_v, $message["content"]);
				$message["content"] = str_replace("<linia>", $linia, $message["content"]);
				$message["content"] = str_replace("<tel>", $tel, $message["content"]);
				$message["content"] = str_replace("<email>", $new_email, $message["content"]);
				$message["content"] = str_replace("<telephones>", $telephone, $message["content"]);
				$message["content"] = str_replace("<time_end>", ", и они заканчиваются через <strong>6 месяцев</strong>", $message["content"]);
				$message["content"] = str_replace("<email_request>", $email, $message["content"]);
				send_mail($email, $message["title"], $message["content"]);
				sleep(3);
			}
		}
	}

	$clear_date = date("Y-m-d", strtotime("-8 month"));
	$data = $connect->getAll("SELECT id, date_v, turist, id_obj, DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, DATE_FORMAT(date_v, '%d.%m.%Y') as date_v FROM reckoning WHERE status=5 AND date_v=?s AND turist!=''", $clear_date);
	foreach($data as $row){
		$id = $row["id"];
		$id_obj = $row["id_obj"];
		$client = $row["turist"];
		$date_z = month_transform($row["date_z"]);
		$date_v = month_transform($row["date_v"]);
		$bonus_minus = 0;
		$bonus_row = $connect->getRow("SELECT id, sum FROM bonus WHERE sum>0 AND schet=?i", $id);
		$bonus_plus = $bonus_row["sum"];
		$bonus_id = $bonus_row["id"];
		$array = $connect->getAll("SELECT sum FROM bonus WHERE turist=?i AND sum<0", $client);
		foreach($array as $minus)
			$bonus_minus+= $minus["sum"];
		if(($bonus_plus + $bonus_minus) > 0){
			$turist_array = $connect->getRow("SELECT name, otch, email FROM klient WHERE id=?i", $client);
			$email = $turist_array["email"];
			if($email){
				$bonus = all_klient_bonus($connect, $client);
				$object = "«".get_object($connect, $id_obj, "type")."»";
				$turist = $turist_array["name"]." ".$turist_array["otch"];
				$message = select_template_letter("cron/bonus", "client");
				$message["title"] = str_replace("<object>", $object, $message["title"]);
				$message["content"] = str_replace("<name>", $turist, $message["content"]);
				$message["content"] = str_replace("<bonus>", $bonus, $message["content"]);
				$message["content"] = str_replace("<end_bonus>", $bonus_plus, $message["content"]);
				$message["content"] = str_replace("<object>", $object, $message["content"]);
				$message["content"] = str_replace("<date_z>", $date_z, $message["content"]);
				$message["content"] = str_replace("<date_v>", $date_v, $message["content"]);
				$message["content"] = str_replace("<linia>", $linia, $message["content"]);
				$message["content"] = str_replace("<tel>", $tel, $message["content"]);
				$message["content"] = str_replace("<email>", $new_email, $message["content"]);
				$message["content"] = str_replace("<telephones>", $telephone, $message["content"]);
				$message["content"] = str_replace("<time_end>", "", $message["content"]);
				$message["content"] = str_replace("<email_request>", $email, $message["content"]);
				send_mail($email, $message["title"], $message["content"]);
				sleep(3);
			}
		}
	}


	$clear_date = date("Y-m-d", strtotime("-4 month"));
	$data = $connect->getAll("SELECT id, date_v, turist, id_obj, DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, DATE_FORMAT(date_v, '%d.%m.%Y') as date_v FROM reckoning WHERE status=5 AND date_v=?s AND turist!=''", $clear_date);
	foreach($data as $row){
		$id = $row["id"];
		$id_obj = $row["id_obj"];
		$client = $row["turist"];
		$date_z = month_transform($row["date_z"]);
		$date_v = month_transform($row["date_v"]);
		$bonus_minus = 0;
		$bonus_row = $connect->getRow("SELECT id, sum FROM bonus WHERE sum>0 AND schet=?i", $id);
		$bonus_plus = $bonus_row["sum"];
		$bonus_id = $bonus_row["id"];
		$array = $connect->getAll("SELECT sum FROM bonus WHERE turist=?i AND sum<0", $client);
		foreach($array as $minus)
			$bonus_minus+= $minus["sum"];
		if(($bonus_plus + $bonus_minus) > 0){
			$turist_array = $connect->getRow("SELECT name, otch, email FROM klient WHERE id=?i", $client);
			$email = $turist_array["email"];
			if($email){
				$bonus = all_klient_bonus($connect, $client);
				$object = "«".get_object($connect, $id_obj, "type")."»";
				$turist = $turist_array["name"]." ".$turist_array["otch"];
				$message = select_template_letter("cron/bonus", "client");
				$message["title"] = str_replace("<object>", $object, $message["title"]);
				$message["content"] = str_replace("<name>", $turist, $message["content"]);
				$message["content"] = str_replace("<bonus>", $bonus, $message["content"]);
				$message["content"] = str_replace("<end_bonus>", $bonus_plus, $message["content"]);
				$message["content"] = str_replace("<object>", $object, $message["content"]);
				$message["content"] = str_replace("<date_z>", $date_z, $message["content"]);
				$message["content"] = str_replace("<date_v>", $date_v, $message["content"]);
				$message["content"] = str_replace("<linia>", $linia, $message["content"]);
				$message["content"] = str_replace("<tel>", $tel, $message["content"]);
				$message["content"] = str_replace("<email>", $new_email, $message["content"]);
				$message["content"] = str_replace("<telephones>", $telephone, $message["content"]);
				$message["content"] = str_replace("<time_end>", "", $message["content"]);
				$message["content"] = str_replace("<email_request>", $email, $message["content"]);
				send_mail($email, $message["title"], $message["content"]);
				sleep(3);
			}
		}
	}

?>
