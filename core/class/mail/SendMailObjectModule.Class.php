<?php

class SendMailObjectModule extends SendMail{

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->booking = $config->booking;
    $this->connect = $config->connect;
    $this->account = $config->turist;
  }

  private function select_email(){
    $connect = $this->connect;
    $booking = $this->booking;
    $object = $connect->getOne("SELECT object FROM booking_request_object_module WHERE id=?i", $booking);
    $email = clear_email($connect->getOne("SELECT email FROM booking_module_object WHERE object=?i", $object));
    return $email;
  }

  public function notification_new_booking(){
    $booking = $this->booking;
    $email = $this->select_email();
    if($email){
      $config = ConfigCRM::getInstance();
      $link = $config->objectCabinet["link"];
      $data = array(
        "link" => $link."заявки/".$booking
      );
      $letter = $this->select_template_letter_object("new-booking", $data);
      $this->send_mail_base("sanata", $email, $letter["title"], $letter["HTML"]);
    }
  }

  public function notification_request_payment_booking(){
    $booking = $this->booking;
    $email = $this->select_email();
    if($email){
      $config = ConfigCRM::getInstance();
      $link = $config->objectCabinet["link"];
      $data = array(
        "link" => $link."заявки/".$booking
      );
      $letter = $this->select_template_letter_object("request-payment-booking", $data);
      $this->send_mail_base("sanata", $email, $letter["title"], $letter["HTML"]);
    }
  }

  public function notification_payment_booking(){
    $booking = $this->booking;
    $email = $this->select_email();
    if($email){
      $config = ConfigCRM::getInstance();
      $link = $config->objectCabinet["link"];
      $data = array(
        "link" => $link."заявки/".$booking
      );
      $letter = $this->select_template_letter_object("payment-booking", $data);
      $this->send_mail_base("sanata", $email, $letter["title"], $letter["HTML"]);
    }
  }

  public function select_template_letter_object($file, $data){
    $letter = $this->select_template_letter("booking-module-template-object", "booking-module/object/".$file, $data);
    return $letter;
  }

}

?>
