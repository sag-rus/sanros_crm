<?php

function create_account_client($connect){
	global $directory;
	include_once($directory."/core/lib/mail.php");
	$id = $_POST["id"];
	$email = $connect->getOne("SELECT email FROM klient WHERE id=?i", $id);
	$today = date("Y-m-d");
	$password = gen_password(rand(6, 8));
	$hash = uniqid();
	$link = "http://xn----7sba6aaba8akdsdekah.xn--p1ai/client?func=activation&email=".$email."&hash=".$hash;
	$connect->query("UPDATE klient SET login=?s, password=?s, hash=?s, date_reg=?s WHERE id=?i", $email, md5($password), $hash, $today, $id);
	$message = select_template_letter("turist/send-login", "client");
	$row = $connect->getRow("SELECT name, otch FROM klient WHERE id=?i", $id);
	$turist = $row["name"]." ".$row["otch"];
	$message["content"] = str_replace("<email>", $email, $message["content"]);
	$message["content"] = str_replace("<password>", $password, $message["content"]);
	$message["content"] = str_replace("<hash>", $link, $message["content"]);
	$message["content"] = str_replace("<turist>", $turist, $message["content"]);
	send_mail_sanata($email, $message["title"], $message["content"]);
	send_mail_sanata('office@sanata.online', $message["title"], $message["content"]);
}

function send_login_agency($connect){
	global $directory;
	include_once($directory."/core/lib/mail.php");
	$id = $_POST["id"];
	$login = get_login_agency($connect);
	$password = gen_password(rand(6, 8));
	$connect->query("UPDATE agency SET login=?s, password=?s WHERE id=?i", $login, md5($password), $id);
	$message = select_template_letter("agency/send-login", "agency");
	$message["content"] = str_replace("<login>", $login, $message["content"]);
	$message["content"] = str_replace("<password>", $password, $message["content"]);
	$email = $connect->getOne("SELECT email FROM agency WHERE id=?i", $id);
	send_mail_sanata($email, $message["title"], $message["content"]);
}

function send_mail_client_document($connect){
	$id = $_POST["id"];
	$doc = $_POST["doc"];
	if($doc == "schet")
		send_mail_client_schet($connect, $id);
	elseif($doc == "obmen")
		send_mail_client_obmen($connect, $id);
	elseif($doc == "cancel")
		send_mail_client_cancel($connect, $id);
}

function send_mail_client_schet($connect, $id){
	global $directory;
	include_once($directory."/core/lib/mail.php");
	$time_payment = "";
	$message = select_template_letter("turist/document/send-schet", "client", $id);
	$data = $connect->getAll("SELECT type, sum, DATE_FORMAT(date, '%d.%m.%Y') as date FROM time_payment WHERE id_schet=?i ORDER BY type DESC", $id);
	foreach($data as $row){
		$date = $row["date"];
		if($row["type"] == 2 AND $row["sum"] > 0 AND $date){
			$time_payment = "Предоплату заявки в размере <strong>".$row["sum"]."</strong> необходимо произвести <strong>до ".$date."</strong>.";
		}elseif($row["type"] == 1 AND $date){
			if($time_payment)
				$time_payment.= "<br />";
			$time_payment.= "Полную оплату заявки необходимо произвести <strong>до ".$date."</strong>.";
		}
	}
	if(!$time_payment)
		$time_payment = "Оплату заявки необходимо произвести <strong>в течение 5 рабочих дней</strong>.";
	$object = get_object($connect, $connect->getOne("SELECT id_obj FROM reckoning WHERE id=?i", $id), "type");
	$row = $connect->getRow("SELECT surname, name, otch, login FROM klient WHERE id=?i", $connect->getOne("SELECT turist FROM reckoning WHERE id=?i", $id));
	$login = $row["login"];
	$client = $row["surname"]." ".$row["name"]." ".$row["otch"];
	$message["content"] = str_replace("<client>", $client, $message["content"]);
	$message["content"] = str_replace("<object>", $object, $message["content"]);
	$message["content"] = str_replace("<date_payment>", $time_payment, $message["content"]);
	send_mail_sanata($login, $message["title"], $message["content"]);
}

function send_mail_client_obmen($connect, $id){
	global $directory;
	include_once($directory."/core/lib/mail.php");
	$row = $connect->getRow("SELECT turist, id_obj, DATE_FORMAT(date_v, '%d.%m.%Y') date, id_user FROM reckoning WHERE id=?i", $id);
	$date_v = $row["date"];
	$turist = $row["turist"];
	$id_obj = $row["id_obj"];
	$object = get_object($connect, $id_obj, "type");
	$bonus = all_klient_bonus($connect, $turist);
	$row = $connect->getRow("SELECT name, otch, email, login, active FROM klient WHERE id=?i", $turist);
	if($row["login"] AND $row["active"] == 1){
		$message = select_template_letter("turist/document/send-obmen-client", "client", $id);
		$email = $row["login"];
		$files = array();
	}elseif($row["email"]){
		$message = select_template_letter("turist/document/send-obmen", "client", $id);
		$email = $row["email"];
		$files = array(save_to_PDF($connect, $id, "obmen"), save_to_PDF($connect, $id, "dover"));
	}else
		return FALSE;
	$turist = $row["name"]." ".$row["otch"];
	$message["content"] = str_replace("<fio>", $turist, $message["content"]);
	$message["content"] = str_replace("<object>", $object, $message["content"]);
	$message["content"] = str_replace("<bonus>", $bonus, $message["content"]);
	$message["content"] = str_replace("<date_v>", $date_v, $message["content"]);
	$message["content"] = str_replace("<manager>", $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]), $message["content"]);
	$message["title"] = str_replace("<id>", $id, $message["title"]);
	send_mail($email, $message["title"], $message["content"], $files[0], $files[1]);
}

function send_mail_client_cancel($connect, $id){
	global $directory;
	include_once($directory."/core/lib/mail.php");
	$row = $connect->getRow("SELECT turist, id_obj FROM reckoning WHERE id=?i", $id);
	$turist = $row["turist"];
	$id_obj = $row["id_obj"];
	$row = $connect->getRow("SELECT name, otch, email FROM klient WHERE id=?i AND email!=''", $turist);
	$email = $row["email"];
	if(!$email)
		return FALSE;
	$object = get_object($connect, $id_obj, "type");
	$turist = $row["name"]." ".$row["otch"];
	$cause = $connect->getOne("SELECT cause_turist FROM cancellation WHERE schet=?i", $id);
	$message = select_template_letter("turist/document/send-cancel-client", "client", $id);
	$message["content"] = str_replace("<client>", $turist, $message["content"]);
	$message["content"] = str_replace("<object>", $object, $message["content"]);
	$message["content"] = str_replace("<cause>", $cause, $message["content"]);
	send_mail_sanata($email, $message["title"], $message["content"]);
}

function send_mail_client_changes($connect, $id){
	global $directory;
	$check = $connect->getOne("SELECT id FROM send_mail WHERE bid=?i AND status=0", $id);
	if($check)
		return;
	include_once($directory."/core/lib/mail.php");
	$row = $connect->getRow("SELECT turist, id_user FROM reckoning WHERE id=?i", $id);
	$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
	$row = $connect->getRow("SELECT name, otch, email, login FROM klient WHERE id=?i", $row["turist"]);
	if(!$row["login"])
		return FALSE;
	$email = $row["login"];
	$client = $row["name"]." ".$row["otch"];
	$message = select_template_letter("turist/send-changes", "client", $id);
	$message["content"] = str_replace("<client>", $client, $message["content"]);
	$message["content"] = str_replace("<manager>", $manager, $message["content"]);
	$connect->query("INSERT INTO send_mail(email, title, body, bid) VALUES (?s, ?s, ?s, ?i)", $email, $message["title"], $message["content"], $id);
}

function send_mail_agency_document($connect){
	$id = $_POST["id"];
	$doc = $_POST["doc"];
	if($doc == "schet")
		send_mail_agency_schet($connect, $id);
	elseif($doc == "obmen")
		send_mail_agency_obmen($connect, $id);
}

function send_mail_agency_schet($connect, $id){
	global $directory;
	include_once($directory."/core/lib/mail.php");
	$row = $connect->getRow("SELECT agency, id_user FROM reckoning WHERE id=?i", $id);
	$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
	$email = $connect->getOne("SELECT email FROM agency WHERE id=?i", $row["agency"]);
	$message = select_template_letter("agency/document/send-schet", "agency", $id);
	$message["content"] = str_replace("<manager>", $manager, $message["content"]);
	send_mail_sanata($email, $message["title"], $message["content"]);
}

function send_mail_agency_obmen($connect, $id){
	global $directory;
	include_once($directory."/core/lib/mail.php");
	$row = $connect->getRow("SELECT agency, id_user FROM reckoning WHERE id=?i", $id);
	$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
	$email = $connect->getOne("SELECT email FROM agency WHERE id=?i", $row["agency"]);
	$message = select_template_letter("agency/document/send-obmen", "agency", $id);
	$message["content"] = str_replace("<manager>", $manager, $message["content"]);
	send_mail_sanata($email, $message["title"], $message["content"]);
}

function send_mail_agency_changes($connect, $id){
	global $directory;
	$check = $connect->getOne("SELECT id FROM send_mail WHERE bid=?i AND status=0", $id);
	if($check)
		return;
	include_once($directory."/core/lib/mail.php");
	$row = $connect->getRow("SELECT agency, id_user FROM reckoning WHERE id=?i", $id);
	$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
	$email = $connect->getOne("SELECT email FROM agency WHERE id=?i", $row["agency"]);
	$message = select_template_letter("agency/send-changes", "agency", $id);
	$message["content"] = str_replace("<manager>", $manager, $message["content"]);
	$connect->query("INSERT INTO send_mail(email, title, body, bid) VALUES (?s, ?s, ?s, ?i)", $email, $message["title"], $message["content"], $id);
}

function answer_client_question($connect){
	global $directory, $session_login;
	$talk = $_POST["id"];
	$reck = $_POST["reck"];
	$answer = $_POST["answer"];
	$answer = str_replace("plus", "+", $answer);
	if((!$reck AND !$talk) OR !$answer)
		return;
	if(!$talk AND $reck){
		$talk = $connect->getOne("SELECT id FROM talk WHERE id_reck=?i", $reck);
		if(!$talk){
			$type = "turist";
			$row = $connect->getRow("SELECT turist, agency FROM reckoning WHERE id=?i", $reck);
			if($row["turist"])
				$client = $row["turist"];
			else{
				$client = $row["agency"];
				$type = "agency";
			}
			$connect->query("INSERT INTO talk(client, category, type, id_reck) VALUES(?i, 6, ?s, ?i)", $client, $type, $reck);
			$talk = $connect->insertId();
		}
	}
	$connect->query("INSERT INTO message_talk(talk, type, user, text) VALUES (?i, 'manager', ?i, ?s)", $talk, $session_login, $answer);
	$insert = $connect->insertId();
	$connect->query("UPDATE message_talk SET active=1 WHERE type='client' AND talk=?i", $talk);
	$check = $connect->getOne("SELECT id FROM send_mail WHERE talk=?i AND status=0", $talk);
	$id_client = $connect->getOne("SELECT client FROM talk WHERE id=?i", $talk);
	$type = $connect->getOne("SELECT type FROM talk WHERE id=?i", $talk);
	if($type == "turist" AND !$check){
		$row = $connect->getRow("SELECT name, otch, login FROM klient WHERE id=?i", $id_client);
		if($row["login"]){
			$email = $row["login"];
			include_once($directory."/core/lib/mail.php");
			$client = $row["name"]." ".$row["otch"];
			$message = select_template_letter("turist/cabinet/answer-question", "client");
			$link = "http://xn----7sba6aaba8akdsdekah.xn--p1ai/client/беседы/".$talk;
			$message["content"] = str_replace("<link>", $link, $message["content"]);
			$message["content"] = str_replace("<client>", $client, $message["content"]);
			$connect->query("INSERT INTO send_mail(email, title, body, talk) VALUES (?s, ?s, ?s, ?i)", $email, $message["title"], $message["content"], $talk);
		}
	}elseif($type == "agency" AND !$check){
		$email = $connect->getOne("SELECT email FROM agency WHERE id=?i", $id_client);
		if($email){
			include_once($directory."/core/lib/mail.php");
			$message = select_template_letter("agency/answer-question", "agency");
			$link = "http://xn----7sbaalrb2cl7afpc.xn--p1ai/cabinet/беседы/".$talk;
			$message["content"] = str_replace("<link>", $link, $message["content"]);
			$message["content"] = str_replace("<client>", $client, $message["content"]);
			$connect->query("INSERT INTO send_mail(email, title, body, talk) VALUES (?s, ?s, ?s, ?i)", $email, $message["title"], $message["content"], $talk);
		}
	}elseif($type == "object" AND !$check){
		$email = $connect->getOne("SELECT email FROM object_account WHERE id=?i", $id_client);
		if($email){
			include_once($directory."/core/lib/mail.php");
			$message = select_template_letter("object/answer-question", "object");
			$link = "http://admin.xn----7sba6aaba8akdsdekah.xn--p1ai/задать-вопрос/".$talk;
			$message["content"] = str_replace("<link>", $link, $message["content"]);
			$message["content"] = str_replace("<client>", $client, $message["content"]);
			$connect->query("INSERT INTO send_mail(email, title, body, talk) VALUES (?s, ?s, ?s, ?i)", $email, $message["title"], $message["content"], $talk);
		}
	}
	$label = $connect->getOne("SELECT name FROM users WHERE id=?i", $session_login).", ".$date_send;
	return write_talk_message($connect, $insert);
}

function send_mail_confirm_rating($connect){
	global $directory, $sync_host;
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT schet, id_obj FROM rating WHERE id=?i AND status=3", $id);
	$id_obj = $row["id_obj"];
	$schet = $row["schet"];
	$object = get_object($connect, $id_obj, "type");
	$row = $connect->getRow("SELECT url_name, id_reg FROM object WHERE id=?i", $id_obj);
	$country = $connect->getOne("SELECT id_country FROM region WHERE id=?i", $row["id_reg"]);
	if($country != 1)
		return;
	$link = "http://xn----7sba6aaba8akdsdekah.xn--p1ai/объект/".$row["url_name"]."/отзывы/";
	$image = $sync_host."/price/object/head/default.jpg";
	if($connect->getOne("SELECT image FROM object WHERE id=?i", $id_obj))
		$image = $sync_host."/price/object/head/".$id_obj.".jpg";
	$turist = $connect->getOne("SELECT turist FROM reckoning WHERE id=?i", $schet);
	$row = $connect->getRow("SELECT name, otch, email FROM klient WHERE id=?i", $turist);
	$email = $row["email"];
	$name = $row["name"]." ".$row["otch"];
	include_once($directory."/core/lib/mail.php");
	$message = select_template_letter("turist/rating/confirm-rating", "client");
	$message["content"] = str_replace("<name>", $name, $message["content"]);
	$message["content"] = str_replace("<object>", $object, $message["content"]);
	$message["content"] = str_replace("<link>", $link, $message["content"]);
	$message["content"] = str_replace("<image>", $image, $message["content"]);
	send_mail_sanata($email, $message["title"], $message["content"]);

	$connect_server = connect_to_server();
	if(save_rating_XML_object($connect, $id_obj)){
		$file = "temp/xml/rating/".$id_obj.".xml";
		$fileJSON = __DIR__.'/../../temp/json/rating/'.$id_obj.'.json';
		$fileCache = __DIR__.'/../../temp/json/rating/'.$id_obj.'.cache';

		$server_file = "/var/www/default-site/public_html/price/XML/rating/".$id_obj.".xml";
		$server_file_json = "/var/www/default-site/public_html/price/json/rating/".$id_obj.".json";
		$server_file_cache = "/var/www/default-site/public_html/price/json/rating/".$id_obj.".cache";


		if(!ftp_put($connect_server, $server_file, $file, FTP_ASCII))
			return "Не удалось загрузить файл на сервер";
		if(!ftp_put($connect_server, $server_file_json, $fileJSON, FTP_ASCII))
			return "Не удалось загрузить файл на сервер";
		if(!ftp_put($connect_server, $server_file_cache, $fileCache, FTP_ASCII))
			return "Не удалось загрузить файл на сервер";

		ftp_chmod($connect_server, 0644, $server_file);
		ftp_chmod($connect_server, 0644, $server_file_json);
		ftp_chmod($connect_server, 0644, $server_file_cache);

	}
	ftp_quit($connect_server);
}

function send_mail_confirm_rating_comment($connect){
	global $directory, $sync_host;
	$id = $_POST["id"];
	$email = explode(",", $_POST["email"]);
	$email = array_diff($email, array(""));

	$rating = $connect->getOne("SELECT rating FROM rating_comment WHERE id=?i", $id);
	$row = $connect->getRow("SELECT schet, id_obj, hash FROM rating WHERE id=?i AND status=3", $rating);
	$hash = $row["hash"];
	$id_obj = $row["id_obj"];
	$object = get_object($connect, $id_obj, "type");
	if(!$row["hash"]){
		$hash = md5(uniqid());
		$connect->query("UPDATE rating SET hash=?s, synchronized = 0 WHERE id=?i", $hash, $rating);
	}
	$image = $sync_host."/price/object/head/default.jpg";
	if($connect->getOne("SELECT image FROM object WHERE id=?i", $id_obj))
		$image = $sync_host."/price/object/head/".$id_obj.".jpg";
	include_once($directory."/core/lib/mail.php");
	foreach($email as $send_email){
		$link = "http://sanata-trevel.ru/client/act/comment.php?hash=".$hash."&email=".$send_email;
		$message = select_template_letter("turist/rating/confirm-rating-comment", "client");
		$message["content"] = str_replace("<object>", $object, $message["content"]);
		$message["content"] = str_replace("<link>", $link, $message["content"]);
		$message["content"] = str_replace("<image>", $image, $message["content"]);
		send_mail_sanata($send_email, $message["title"], $message["content"]);
		sleep(2);
	}
}

function send_login_object_account($connect){
	global $directory;
	include_once($directory."/core/lib/mail.php");
	$account = $_POST["account"];
	$row = $connect->getRow("SELECT login, email FROM object_account WHERE id=?i", $account);
	$login = $row["login"];
	$email = $row["email"];
	if(!$email)
		return;
	$password = gen_password(rand(6, 8));
	$connect->query("UPDATE object_account SET password=?s WHERE id=?i", md5($password), $account);
	$message = select_template_letter("object/send-login", "object");
	$message["content"] = str_replace("<login>", $login, $message["content"]);
	$message["content"] = str_replace("<password>", $password, $message["content"]);
	send_mail_sanata($email, $message["title"], $message["content"]);
}

?>
