<?php

	$directory = dirname(__FILE__)."/../..";
	include_once($directory."/core/functions.php");
	include_once($directory."/core/lib/mail.php");
	include_once($directory."/core/lib/Mysql.Class.php");
	$connect = connect_to_MySQL_directory();
	$today = date("Y-m-d");

	$data = $connect->getAll("SELECT id, otch, name, login FROM klient WHERE MONTH(date)=MONTH(CURRENT_DATE) AND DAYOFMONTH(date)=DAYOFMONTH(CURRENT_DATE) AND login!=''");
	foreach($data as $row){
		$id = $row["id"];
		$name = $row["name"]." ".$row["otch"];
		$email = $row["login"];
		$bonus = 300;
		$connect->query("INSERT INTO bonus(turist, type, sum, date, note) VALUES(?i, 3, ?i, ?s, 'Подарочный бонус на день рождения')", $id, $bonus, $today);
		$message = select_template_letter("cron/birthday-account", "client");
		$message["content"] = str_replace("<name>", $name, $message["content"]);
		$message["content"] = str_replace("<bonus>", $bonus, $message["content"]);
		send_mail($email, $message["title"], $message["content"]);
		sleep(3);
	}

	$data = $connect->getAll("SELECT id, otch, name, email FROM klient WHERE MONTH(date)=MONTH(CURRENT_DATE) AND DAYOFMONTH(date)=DAYOFMONTH(CURRENT_DATE) AND email!='' AND (login='' OR login is NULL)");
	foreach($data as $row){
		$id = $row["id"];
		$klient = $row["name"]." ".$row["otch"];
		$email = $row["email"];
		$bonus = 300;
		$message = select_template_letter("cron/birthday", "client");
		$message["content"] = str_replace("<name>", $klient, $message["content"]);
		$message["content"] = str_replace("<bonus>", $bonus, $message["content"]);
		send_mail($email, $message["title"], $message["content"]);
		sleep(3);
	}

?>
