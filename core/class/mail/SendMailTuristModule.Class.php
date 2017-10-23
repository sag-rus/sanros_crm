<?php

class SendMailTuristModule extends SendMail{

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->booking = $config->booking;
    $this->connect = $config->connect;
    $this->account = $config->turist;
  }

  private function select_email(){
    $connect = $this->connect;
    $turist = $this->account;
    $email = clear_email($connect->getOne("SELECT email FROM klient WHERE id=?i", $turist));
    return $email;
  }

  public function send_login(){
    $connect = $this->connect;
    $booking = $this->booking;
    $email = $this->select_email();
    if($email){
      $create = new LoginClient;
      $login = $create->create_login_turist();
      unset($create);
      if($login["create"] == 1){
        $object = $connect->getOne("SELECT object FROM booking_request_object_module WHERE id=?i", $booking);
        $website = $connect->getOne("SELECT website FROM booking_module_object WHERE object=?i", $object);
        $data = array(
          "link" => $login["link"]."?func=activation&email=".$email."&hash=".$login["hash"],
          "email" => $email,
          "object" => get_object($connect, $object, "type"),
          "password" => $login["password"],
          "website" => $website
        );
        $letter = $this->select_template_letter_turist("new-booking", $data);
        $this->send_mail_base("sanata", $email, $letter["title"], $letter["HTML"]);
      }
    }
  }

  public function notification_confirm_booking(){
    $connect = $this->connect;
    $booking = $this->booking;
    $email = $this->select_email();
    if($email){
      $object = $connect->getOne("SELECT object FROM booking_request_object_module WHERE id=?i", $booking);
      $config = ConfigCRM::getInstance();
      $link = $config->clientCabinet["link"];
      $data = array(
        "link" => $link."заявки-объекта/".$booking,
        "object" => get_object($connect, $object, "type")
      );
      $letter = $this->select_template_letter_turist("confirm-booking", $data);
      $this->send_mail_base("sanata", $email, $letter["title"], $letter["HTML"]);
    }
  }

  public function notification_payment_booking(){
    $booking = $this->booking;
    $connect = $this->connect;
    $email = $this->select_email();
    if($email){
      $object = $connect->getOne("SELECT object FROM booking_request_object_module WHERE id=?i", $booking);
      $config = ConfigCRM::getInstance();
      $link = $config->clientCabinet["link"];
      $data = array(
        "link" => $link."заявки-объекта/".$booking,
        "object" => get_object($connect, $object, "type")
      );
      $letter = $this->select_template_letter_turist("payment-booking", $data);
      $this->send_mail_base("sanata", $email, $letter["title"], $letter["HTML"]);
    }
  }

  public function select_template_letter_turist($file, $data){
    $letter = $this->select_template_letter("booking-module-template-turist", "booking-module/turist/".$file, $data);
    return $letter;
  }

}

?>
