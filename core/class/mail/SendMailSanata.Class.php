<?php

class SendMailSanata extends SendMail{

  public function __construct(){
    $auth = array(
      "login" => "office@sanata.online",
      "password" => "gelfrfhzfxurhhpa",
      "from" => "office@sanata.online",
      "from_name" => "САНАТОРИИ РОССИИ",
      "host" => "smtp.yandex.ru"      
    );
    $this->auth = $auth;
  }

  public function send($to, $title, $message){
    return $this->send_mail($to, $title, $message);
  }

}

?>
