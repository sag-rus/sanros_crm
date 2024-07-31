<?php

	$directory = dirname(__FILE__)."/../..";

	include_once($directory."/config.php");
	include_once($directory."/core/functions.php");
	include_once($directory."/core/lib/Mysql.Class.php");

	include_once($directory."/core/lib/PHPMailer/class.phpmailer.php");
	include_once($directory."/core/class/mail/SendMail.Class.php");
	include_once($directory."/core/class/mail/SendMailDefault.Class.php");
	include_once($directory."/core/class/mail/SendMailSanata.Class.php");

	$connect = connect_to_MySQL();

	$data = $connect->getAll("SELECT id, email, title, body, from_send FROM send_mail WHERE status=0");
	foreach($data as $row){
		echo '<pre>';
		print_r($row);
		echo '</pre>';
		$id = $row["id"];
		$email = $row["email"];
		$title = $row["title"];
		$body = $row["body"];
		$from = $row["from_send"];
		if($from == "sanata"){
			$send = new SendMailSanata;
		}else{
			$send = new SendMailDefault;
		}
		//$answer = $send->send($email, $title, $body);
		if($answer){
			$connect->query("UPDATE send_mail SET status=1 WHERE id=?i", $id);
		}
		sleep(3);
		echo $email;
	}

?>
