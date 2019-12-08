<?php

class SendMailDefault extends SendMail{

  public function __construct(){
    $auth = array(
      "login" => "kazangood@gmail.com",
      "password" => "Profilaktika-56124",
      "from" => "kazangood@gmail.com",
      "from_name" => "Саната-Тревел",
      "host" => "smtp.gmail.com"
    );
    $this->auth = $auth;
  }

  public function send($to, $title, $message){
    return $this->send_mail($to, $title, $message);
  }

}

?>
