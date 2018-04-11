<?php

	$directory = dirname(__FILE__)."/../..";
	include_once($directory."/core/functions.php");
	include_once($directory."/core/lib/mail.php");
	include_once($directory."/core/lib/Mysql.Class.php");
	$connect = connect_to_MySQL_directory();
	$today = date("Y-m-d");
	$days = 30;
	$last_timestamp = strtotime($today)+$days*86400;

	$data = $connect->getAll("SELECT id, otch, name, login FROM klient WHERE MONTH(date)=MONTH(CURRENT_DATE) AND DAYOFMONTH(date)=DAYOFMONTH(CURRENT_DATE) AND login!=''");
	foreach($data as $row){
		$id = $row["id"];
		$name = $row["name"]." ".$row["otch"];
		$email = $row["login"];
		$bonus = 1000;
		$connect->query("INSERT INTO bonus(turist, type, sum, date, note,`last_timestamp`) VALUES(?i, 3, ?i, ?s, 'Подарочный бонус на день рождения',?i)", $id, $bonus, $today,$last_timestamp);
		$message = select_template_letter("cron/birthday-account", "client");
		$message["content"] = str_replace("<name>", $name, $message["content"]);
		$message["content"] = str_replace("<bonus>", $bonus, $message["content"]);
    $message["content"] = str_replace("<days>", $days, $message["content"]);
    $message["content"] = str_replace("<last_date>", date("d.m.Y",$last_timestamp), $message["content"]);
		send_mail($email, $message["title"], $message["content"]);
		sleep(3);
	}

	$data = $connect->getAll("SELECT id, otch, name, email FROM klient WHERE MONTH(date)=MONTH(CURRENT_DATE) AND DAYOFMONTH(date)=DAYOFMONTH(CURRENT_DATE) AND email!='' AND (login='' OR login is NULL)");
	foreach($data as $row){
		$id = $row["id"];
		$klient = $row["name"]." ".$row["otch"];
		$email = $row["email"];
		$bonus = 1000;
		$message = select_template_letter("cron/birthday", "client");
		$message["content"] = str_replace("<name>", $klient, $message["content"]);
		$message["content"] = str_replace("<bonus>", $bonus, $message["content"]);
    $message["content"] = str_replace("<days>", $days, $message["content"]);
    $message["content"] = str_replace("<last_date>", date("d.m.Y",$last_timestamp), $message["content"]);
		send_mail($email, $message["title"], $message["content"]);
		sleep(3);
	}

?>
