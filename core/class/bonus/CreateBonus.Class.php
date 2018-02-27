<?php

class CreateBonus{

  private $connect;
  private $booking;
  private $turist;
  private $bonusBooking;
  private $bonusAffiliate;

  public function __construct(){
    if(class_exists('App\lib\CRM\Config\Client')) {
      $config = \App\lib\CRM\Config\Client::getInstance();
    }
    else {
      $config = ConfigCRM::getInstance();
    }
    $this->connect = $config->connect;
    $this->booking = $config->booking;
    $this->turist = $config->turist;
    $this->bonusBooking = $config->bonus["bonus-booking"];
    $this->bonusAffiliate = $config->bonus["bonus-affiliate"];
  }

  public function create_bonus(){
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;
    $row = $connect->getRow("SELECT sum, status FROM reckoning WHERE id=?i", $booking);
    $status = $row["status"];
    $sum = $row["sum"];
    if($status == 5){
      $today = date("Y-m-d");
      $connect->query("DELETE FROM bonus WHERE schet=?i AND sum>0", $booking);
      $bonus = (int)$sum * $this->bonusBooking;
      $connect->query("INSERT INTO bonus(date, schet, turist, sum) VALUES (?s, ?i, ?i, ?s)", $today, $booking, $turist, $bonus);
      $invited = $connect->getOne("SELECT invited FROM klient WHERE id=?i", $turist);
      if($invited){
        $bonus_referral = (int)$sum * $this->bonusAffiliate;
        $connect->query("INSERT INTO bonus(date, schet, turist, sum, type) VALUES (?s, ?i, ?i, ?s, 4)", $today, $booking, $invited, $bonus_referral);
      }
    }
  }

  public function create_bonus_module_object(){
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;
    $row = $connect->getRow("SELECT sum, status FROM booking_request_object_module WHERE id=?i", $booking);
    $status = $row["status"];
    $sum = $row["sum"];
    if($status == 4){
      $today = date("Y-m-d");
      $connect->query("DELETE FROM bonus WHERE booking=?i AND sum>0", $booking);
      $bonus = (int)$sum * $this->bonusBooking;
      $connect->query("INSERT INTO bonus(date, booking, turist, sum) VALUES (?s, ?i, ?i, ?s)", $today, $booking, $turist, $bonus);
    }
  }

}

?>
