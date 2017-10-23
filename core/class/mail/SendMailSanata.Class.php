<?php

class SendMailSanata extends SendMail{

  public function __construct(){
    $auth = array(
      "login" => "booking@sanata.online",
      "password" => "htlfrnjh1",
      "from" => "booking@sanata.online",
      "from_name" => "Система САНАТА",
      "host" => "smtp.yandex.ru"
    );
    $this->auth = $auth;
  }

  public function send($to, $title, $message){
    return $this->send_mail($to, $title, $message);
  }

}

?>
