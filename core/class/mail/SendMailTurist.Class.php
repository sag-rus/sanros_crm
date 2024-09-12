<?php

class SendMailTurist extends SendMail{

  public function __construct(){
    if(class_exists('App\lib\CRM\Config\Client')) {
      $config = \App\lib\CRM\Config\Client::getInstance();
    }
    else {
      $config = ConfigCRM::getInstance();
    }

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
        $row = $connect->getRow("SELECT id_obj, website FROM reckoning WHERE id=?i", $booking);
        $website = $row["website"];
        $object = $row["id_obj"];
        $data = array(
          "link" => $login["link"]."?func=activation&email=".$email."&hash=".$login["hash"],
          "email" => $email,
          "object" => get_object($connect, $object, "type"),
          "password" => $login["password"],
          "website" => $website
        );
        $letter = $this->select_template_letter_turist("new-reservation-login", $data);
        $this->send_mail_base("default", $email, $letter["title"], $letter["HTML"]);
        $this->send_mail_base("default", 'office@sanata.online', $letter["title"], $letter["HTML"]);
        return TRUE;
      }
      else return FALSE;
    }
    else return FALSE;
  }

  public function notification_payment_booking(){
    $booking = $this->booking;
    $connect = $this->connect;
    $email = $this->select_email();
    if($email){
      $client = new DisplayClient($this->account);
      $fio = $client->select_fio_array();

      $bonus = new DisplayBonus();
      $bonus_sum = $bonus->select_bonus();

      $row = $connect->getRow("SELECT id_user, id_obj, date_z, status FROM reckoning WHERE id=?i", $booking);
      $object = $row["id_obj"];
      $arrival = $row["date_z"];
      $manager = $row["id_user"];
      $status = $row["status"];

      $config = ConfigCRM::getInstance();
      $link = $config->clientCabinet["link"];
      $data = array(
        "link" => $link."заявки/".$booking,
        "object" => get_object($connect, $object, "type"),
        "client" => $fio["name"]." ".$fio["otch"],
        "arrival" => $arrival,
        "bonus" => $bonus_sum
      );
      if($status == 5){

        $this->send_mail_base_notification("Оплата из ЛК", "Произведена оплата путевки №".$booking);
        $letter = $this->select_template_letter_turist("payment/obmen-payment", $data);

      }else{

        $this->send_mail_base_notification("Оплата из ЛК", "Произведена предоплата путевки №".$booking);
        $date_to_pay = $connect->getOne("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date FROM time_payment WHERE id_schet=?i AND type=1", $booking);
        if($date_to_pay){
          $data["ostatok"] = "Оставшуюся часть суммы Вам необходимо оплатить до ".$date_to_pay."<br /><br />";
        }
        $connect->query("DELETE FROM time_payment WHERE type=2 AND id_schet=?i", $booking);
        $letter = $this->select_template_letter_turist("payment/obmen-prepay", $data);

      }
      $this->send_mail_base("default", $email, $letter["title"], $letter["HTML"]);
    }
  }

  public function notification_holding_booking(){
    $booking = $this->booking;
    $connect = $this->connect;
    $email = $this->select_email();
    if($email){
      $client = new DisplayClient($this->account);
      $fio = $client->select_fio_array();

      $bonus = new DisplayBonus();
      $bonus_sum = $bonus->select_bonus();

      $row = $connect->getRow("SELECT id_user, id_obj, date_z, status FROM reckoning WHERE id=?i", $booking);
      $object = $row["id_obj"];
      $arrival = $row["date_z"];
      $manager = $row["id_user"];
      $status = $row["status"];

      $config = ConfigCRM::getInstance();
      $link = $config->clientCabinet["link"];
      $data = array(
        "link" => $link."заявки/".$booking,
        "object" => get_object($connect, $object, "type"),
        "client" => $fio["name"]." ".$fio["otch"],
        "arrival" => $arrival,
        "bonus" => $bonus_sum
      );

      $this->send_mail_base_notification("Оплата из ЛК", "Произведена заморозка средств в заявке №".$booking);

      $letter = $this->select_template_letter_turist("payment/obmen-holding", $data);

      $this->send_mail_base("default", $email, $letter["title"], $letter["HTML"]);
    }
  }

  public function notification_holding_cancel(float $sum,$order_number){
    $booking = $this->booking;
    $connect = $this->connect;
    $email = $this->select_email();
    if(!$order_number || !$sum)
      return;
    if($email){
      $client = new DisplayClient($this->account);
      $fio = $client->select_fio_array();

      $row = $connect->getRow("SELECT id_user, id_obj, date_z, status FROM reckoning WHERE id=?i", $booking);
      $object = $row["id_obj"];
      $arrival = $row["date_z"];

      $config = ConfigCRM::getInstance();
      $link = $config->clientCabinet["link"];
      $data = array(
        "id" => $this->booking,
        "link" => $link."заявки/".$booking,
        "object" => get_object($connect, $object, "type"),
        "client" => $fio["name"]." ".$fio["otch"],
        "arrival" => $arrival,
        "sum" => $sum,
        "order_number" => $order_number
      );

      $this->send_mail_base_notification("Отмена холдирования", "Произведена отмена холдирования средств в заявке №".$booking);

      $letter = $this->select_template_letter_turist("payment/obmen-cancel-holding", $data);

      $this->send_mail_base("default", $email, $letter["title"], $letter["HTML"]);
    }
  }

  public function select_template_letter_turist($file, $data){
    $letter = $this->select_template_letter("client-template", "turist/".$file, $data);
    return $letter;
  }

  public function notification_holding_confirm(){
    $booking = $this->booking;
    $connect = $this->connect;
    $email = $this->select_email();
    if($email){
      $client = new DisplayClient($this->account);
      $fio = $client->select_fio_array();

      $bonus = new DisplayBonus();
      $bonus_sum = $bonus->select_bonus();

      $row = $connect->getRow("SELECT id_user, id_obj, date_z, status FROM reckoning WHERE id=?i", $booking);
      $object = $row["id_obj"];
      $arrival = $row["date_z"];
      $manager = $row["id_user"];
      $status = $row["status"];

      $config = ConfigCRM::getInstance();
      $link = $config->clientCabinet["link"];
      $data = array(
        "link" => $link."заявки/".$booking,
        "object" => get_object($connect, $object, "type"),
        "client" => $fio["name"]." ".$fio["otch"],
        "arrival" => $arrival,
        "bonus" => $bonus_sum,
        "id" => $booking
      );
      if($status == 5){
        $this->send_mail_base_notification("Оплата из ЛК", "Произведено принятие замороженных средств в качестве оплаты путевки №".$booking);
        $letter = $this->select_template_letter_turist("payment/obmen-confirm-holding-payment", $data);

      }else{

        $this->send_mail_base_notification("Оплата из ЛК", "Произведено принятие замороженных средств в качестве предоплаты путевки №".$booking);
        $date_to_pay = $connect->getOne("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date FROM time_payment WHERE id_schet=?i AND type=1", $booking);
        if($date_to_pay){
          $data["ostatok"] = "Оставшуюся часть суммы Вам необходимо оплатить до ".$date_to_pay."<br /><br />";
        }
        $connect->query("DELETE FROM time_payment WHERE type=2 AND id_schet=?i", $booking);
        $letter = $this->select_template_letter_turist("payment/obmen-confirm-holding-prepay", $data);

      }
      $this->send_mail_base("default", $email, $letter["title"], $letter["HTML"]);
    }
  }
}

?>
