<?php

function get_template_letter_new($file_name, $id = ""){
	global $directory;
	$answer = array();
	$file = $directory."/templates/".$file_name.".html";
	include_once($directory."/config.php");
	$conf = new JConfig;

	return $answer;
}

function get_template_letter($file_name, $id = ""){
	global $directory;
	$answer = array();
	$file = $directory."/templates/".$file_name.".html";
	include_once($directory."/config.php");
	$conf = new JConfig;
	$telephone = "Для звонков из городов:<br /><strong>Москва:</strong> ".$conf->telephones_array["moskow"]."<br /><strong>Казань:</strong> ".$conf->telephones_array["kazan"]."<br /><strong>Самара:</strong> ".$conf->telephones_array["samara"]."<br /><strong>Ульяновск:</strong> ".$conf->telephones_array["ulan"]."<br /><strong>Нижний Новгород:</strong> ".$conf->telephones_array["niz"]."<br /><strong>Чебоксары:</strong> ".$conf->telephones_array["chebox"]."<br /><strong>Йошкар-Ола:</strong> ".$conf->telephones_array["uoshkar"]."<br /><strong>Ижевск:</strong> ".$conf->telephones_array["izevsk"]."<br /><strong>Тольятти:</strong> ".$conf->telephones_array["togliatti"]."<br /><strong>Сызрань:</strong> ".$conf->telephones_array["sizran"]."<br /><strong>Пенза:</strong> ".$conf->telephones_array["penza"];
	if(file_exists($file)){
		$service = get_service_information();
		$text = explode("<separator>", file_get_contents($file));
		$answer["title"] = $text[0];
		$answer["content"] = $text[1];
		$answer["content"] = str_replace("<id>", $id, $answer["content"]);
		$answer["content"] = str_replace("<firma>", $service["firma"], $answer["content"]);
		$answer["content"] = str_replace("<online>", $service["online"], $answer["content"]);
		$answer["content"] = str_replace("<telephone>", $service["tel"], $answer["content"]);
		$answer["content"] = str_replace("<telephones>", $telephone, $answer["content"]);
		$answer["content"] = str_replace("<fax>", $service["fax"], $answer["content"]);
		$answer["content"] = str_replace("<linia>", $service["linia"], $answer["content"]);
	}
	return $answer;
}

function select_template_letter($file_name, $type, $id = ""){
	global $directory;
	$answer = array();
	$file = $directory."/templates/".$file_name.".html";
	include_once($directory."/config.php");
	$conf = new JConfig;
	$telephone = "Для звонков из городов:<br /><strong>Москва:</strong> ".$conf->telephones_array["moskow"]."<br /><strong>Казань:</strong> ".$conf->telephones_array["kazan"]."<br /><strong>Самара:</strong> ".$conf->telephones_array["samara"]."<br /><strong>Ульяновск:</strong> ".$conf->telephones_array["ulan"]."<br /><strong>Нижний Новгород:</strong> ".$conf->telephones_array["niz"]."<br /><strong>Чебоксары:</strong> ".$conf->telephones_array["chebox"]."<br /><strong>Йошкар-Ола:</strong> ".$conf->telephones_array["uoshkar"]."<br /><strong>Ижевск:</strong> ".$conf->telephones_array["izevsk"]."<br /><strong>Тольятти:</strong> ".$conf->telephones_array["togliatti"]."<br /><strong>Сызрань:</strong> ".$conf->telephones_array["sizran"]."<br /><strong>Пенза:</strong> ".$conf->telephones_array["penza"];
	if(file_exists($file)){
		$service = get_service_information();
		$text = explode("<separator>", file_get_contents($file));
		$answer["title"] = $text[0];
		$content = $text[1];
		if($type == "client")
			$template = file_get_contents($directory."/templates/template/client-template.html");
		if($type == "object")
			$template = file_get_contents($directory."/templates/template/object-template.html");
		if($type == "agency")
			$template = file_get_contents($directory."/templates/template/agency-template.html");
		$answer["content"] = str_replace("<body-message>", $content, $template);
		$answer["content"] = str_replace("<id>", $id, $answer["content"]);
		$answer["content"] = str_replace("<firma>", $service["firma"], $answer["content"]);
		$answer["content"] = str_replace("<online>", $service["online"], $answer["content"]);
		$answer["content"] = str_replace("<telephone>", $service["tel"], $answer["content"]);
		$answer["content"] = str_replace("<telephones>", $telephone, $answer["content"]);
		$answer["content"] = str_replace("<fax>", $service["fax"], $answer["content"]);
		$answer["content"] = str_replace("<linia>", isset($service["linia"])?$service["linia"]:null, $answer["content"]);
	}
	return $answer;
}

function get_login_agency($connect){
	$login = "Sanata".get_login_chislo(4);
	while($connect->getOne("SELECT id FROM agency WHERE login=?s", $login))
		$login = "Sanata".get_login_chislo(5);
	return $login;
}

function get_login_chislo($length){
	$arr = array(
		"1", "2", "3", "4", "5",
		"6", "7", "8", "9", "0"
	);

	for($i = 0; $i < $length; $i++)
		$password.= $arr[mt_rand(0, count($arr) - 1)];
	return $password;
}

function gen_password($length){
	$password = "";
	$arr = array(
		"a", "b", "c", "d", "e", "f",
		"g", "h", "i", "j", "k", "l",
		"m", "n", "o", "p", "q", "r",
		"s", "t", "u", "v", "w", "x",
		"y", "z", "A", "B", "C", "D",
		"E", "F", "G", "H", "I", "J",
		"K", "L", "M", "N", "O", "P",
		"Q", "R", "S", "T", "U", "V",
		"W", "X", "Y", "Z", "1", "2",
		"3", "4", "5", "6", "7", "8",
		"9", "0", "1", "2", "3", "4",
		 "5", "6", "7", "8", "9", "0"
	);

	for($i = 0; $i < $length; $i++)
		$password.= $arr[mt_rand(0, count($arr) - 1)];
	return $password;
}

function send_mail_yandex($email, $title, $mess, $file=false, $file2=false, $afl_file=false){
	$email = str_replace(" ", "", $email);
	if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		return FALSE;
	global $mail_username, $mail_from, $directory;
	include_once($directory."/config.php");
	$conf = new JConfig;
	$email_from = $conf->email_from;
	$password_from = $conf->password_from;
	include_once($directory."/core/lib/PHPMailer/class.phpmailer.php");
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->CharSet = "UTF-8";
	$mail->SMTPDebug = 2;
	$mail->SMTPAuth = true;
	$mail->SMTPSecure = "ssl";
	$mail->Host = "smtp.yandex.ru";
	$mail->Port = 465;
	$mail->Username = 'office@sanata.online';
	$mail->Password = 'gelfrfhzfxurhhpa';

	$mail->SetFrom("office@sanata.online", "САНАТОРИИ-РОССИИ");
	$mail->AddAddress($email);
	$mail->Subject = htmlspecialchars($title);
	if($file)
		$mail->AddAttachment($file, "doc.pdf");
	if($file2)
		$mail->AddAttachment($file2, "doc2.pdf");
	if($afl_file)
		$mail->AddAttachment($afl_file, "AFL.txt");
	$mail->Body = $mess;
	$mail->isHTML(TRUE);
	$mail->Send();
	return TRUE;
}

function send_mail($email, $title, $mess, $file=false, $file2=false, $afl_file=false){
	$email = str_replace(" ", "", $email);
	if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		return FALSE;
	global $mail_username, $mail_from, $directory;
	include_once($directory."/config.php");
	$conf = new JConfig;
	$email_from = $conf->email_from;
	$password_from = $conf->password_from;
	include_once($directory."/core/lib/PHPMailer/class.phpmailer.php");
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->CharSet = "UTF-8";
	$mail->SMTPDebug = 2;
	$mail->SMTPAuth = true;
	$mail->SMTPSecure = "ssl";
	$mail->Host = "smtp.gmail.com";
	$mail->Port = 465;
	$mail->Username = $email_from;
	$mail->Password = $password_from;

	$mail->SetFrom("kazangood@gmail.com", "Саната");
	$mail->AddAddress($email);
	$mail->Subject = htmlspecialchars($title);
	if($file)
		$mail->AddAttachment($file, "doc.pdf");
	if($file2)
		$mail->AddAttachment($file2, "doc2.pdf");
	if($afl_file)
		$mail->AddAttachment($afl_file, "AFL.txt");
	$mail->Body = $mess;
	$mail->isHTML(TRUE);
	$mail->Send();
	return TRUE;
}

function send_mail_sanata($email, $title, $mess){
	if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		return FALSE;
	global $directory;
	include_once($directory."/config.php");
	$conf = new JConfig;
	$email_from = $conf->email_from_sanata;
	$password_from = $conf->password_from_sanata;
	include_once($directory."/core/lib/PHPMailer/class.phpmailer.php");
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->CharSet = "UTF-8";
	$mail->SMTPDebug = false;
	$mail->SMTPAuth = true;
	$mail->SMTPSecure = "ssl";
	$mail->Host = "smtp.gmail.com";
	$mail->Port = 465;
	$mail->Username = $email_from;
	$mail->Password = $password_from;

	$mail->SetFrom("info@sanata.online", "Санатории России");
	$mail->AddAddress($email);
	$mail->Subject = htmlspecialchars($title);
	$mail->Body = $mess;
	$mail->isHTML(TRUE);
	$mail->Send();
	return TRUE;
}

?>
