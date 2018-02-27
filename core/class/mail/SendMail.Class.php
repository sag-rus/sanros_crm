<?php

class SendMail{

  protected $auth = array(
    "login",
    "password",
    "from",
    "from_name",
    "host"
  );
  protected $connect;
  protected $booking;
  protected $account;
  protected $link;

  protected function select_template($file){
    $config = ConfigCRM::getInstance();
    $directory = $config->directory;
    $template = file_get_contents($directory."/templates/template/".$file.".html");
    return $template;
  }

  protected function select_template_letter($template, $file, Array $data = array()){
    if(class_exists('App\lib\CRM\Config\Client')) {
      $config = \App\lib\CRM\Config\Client::getInstance();
    }
    else {
      $config = ConfigCRM::getInstance();
    }
    $directory = $config->directory;
    $HTML = $this->select_template($template);
    $letter = file_get_contents($directory."/templates/".$file.".html");
    $text = explode("<separator>", $letter);
    $answer = array(
      "title" => $text[0],
      "HTML" => $text[1]
    );
    $answer["HTML"] = str_replace("<body-message>", $answer["HTML"], $HTML);
    if(isset($config->contactInfo["free-line"])){
      $answer["HTML"] = str_replace("<telephone>", $config->contactInfo["free-line"], $answer["HTML"]);
    }
    if(isset($config->booking)){
      $answer["title"] = str_replace("<id>", $config->booking, $answer["title"]);
      $answer["HTML"] = str_replace("<id>", $config->booking, $answer["HTML"]);
    }
    foreach($data as $search => $replace){
      $answer["title"] = str_replace("<".$search.">", $replace, $answer["title"]);
      $answer["HTML"] = str_replace("<".$search.">", $replace, $answer["HTML"]);
    }
    return $answer;
  }

  protected function send_mail_base($from_send, $to, $title, $message){
    $to = clear_email($to);
  	if($to == "")
  		return;
    $connect = $this->connect;
    $connect->query("INSERT INTO send_mail(from_send, email, title, body) VALUES (?s, ?s, ?s, ?s)", $from_send, $to, $title, $message);
  }

  protected function send_mail_base_notification($title, $message){
    $from_send = "default";

    if(class_exists('App\lib\CRM\Config\Client')) {
      $to = \App\lib\CRM\Config\Client::getInstance()->mail["default"]["from"];
    }
    else {
      $to = ConfigCRM::getInstance()->mail["default"]["from"];
    }

    $this->send_mail_base($from_send, $to, $title, $message);
  }

  protected function send_mail($to, $title, $message){
    $to = clear_email($to);
  	if($to == "")
  		return;
  	$login = $this->auth["login"];
  	$password = $this->auth["password"];
    $from = $this->auth["from"];
  	$from_name = $this->auth["from_name"];
    $host = $this->auth["host"];

  	$mail = new PHPMailer();
  	$mail->IsSMTP();
  	$mail->CharSet = "UTF-8";
  	$mail->SMTPDebug = 2;
  	$mail->SMTPAuth = true;
  	$mail->SMTPSecure = "ssl";
  	$mail->Host = $host;
  	$mail->Port = 465;
  	$mail->Username = $login;
  	$mail->Password = $password;

  	$mail->SetFrom($from, $from_name);
  	$mail->AddAddress($to);
  	$mail->Subject = htmlspecialchars($title);
  	$mail->Body = $message;
  	$mail->isHTML(TRUE);
  	$mail->Send();
  	return TRUE;
  }

}

?>
