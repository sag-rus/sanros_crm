<?php

	$directory = dirname(__FILE__)."/../..";
	include_once($directory."/core/functions.php");
	include_once($directory."/core/lib/mail.php");
	include_once($directory."/core/lib/Mysql.Class.php");
	$connect = connect_to_MySQL_directory();
	$today = date("Y-m-d");
	$days = 365;
	$last_timestamp = strtotime($today)+$days*86400;

	$data = $connect->getAll("SELECT id, otch, name, login FROM klient WHERE email IS NOT NULL AND email != ''");
	foreach($data as $row){
		$id = $row["id"];
		$name = $row["name"]." ".$row["otch"];
		$email = $row["login"];
		$bonus = 500;
		$connect->query("INSERT INTO bonus(turist, type, sum, date, note,`last_timestamp`) VALUES(?i, 3, ?i, ?s, 'Подарочный бонус на Новый год',?i)", $id, $bonus, $today,$last_timestamp);
		$message = select_template_letter("cron/new-year", "client");
		$message["content"] = str_replace("<name>", $name, $message["content"]);
		$message["content"] = str_replace("<bonus>", $bonus, $message["content"]);
   	 	$message["content"] = str_replace("<days>", $days, $message["content"]);
    	$message["content"] = str_replace("<last_date>", date("d.m.Y",$last_timestamp), $message["content"]);
		send_mail($email, $message["title"], $message["content"]);
		sleep(3);
	}


?>
