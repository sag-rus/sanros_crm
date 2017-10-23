<?php

class PaymentRequestBookingModule extends RequestBookingModule{

  private $method = array(
    1 => "noncash",
    2 => "cash",
    3 => "card"
  );
  private $method_icon = array(
    "noncash" => "bank",
    "cash" => "rub",
    "card" => "credit-card"
  );

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->booking = $config->booking;
  }

  public function display_payment(){
    $connect = $this->connect;
    $booking = $this->booking;
    $icons = $this->method_icon;
    $payment = array();
    $data = $connect->getAll("SELECT id, DATE_FORMAT(time, '%d.%m.%Y') as date, sum, method FROM booking_request_object_module_payment WHERE booking=?i", $booking);
    foreach($data as $row){
      $id = $row["id"];
      $payment[$id] = array();
      $payment[$id]["date"] = $row["date"];
      $payment[$id]["sum"] = $row["sum"];
      $payment[$id]["method"] = $row["method"];
      $payment[$id]["icon"] = $icons[$row["method"]];
    }
    return $payment;
  }

  public function create_payment($sum, $method){
    $connect = $this->connect;
    $booking = $this->booking;
    $methods = $this->method;
    $sum_to_pay = $this->select_sum_pay();
    if($sum_to_pay <= 0 OR $sum > $sum_to_pay)
      return;
    if(!in_array($method, $methods))
      $method = $methods[1];
    $connect->query("INSERT INTO booking_request_object_module_payment(booking, sum, method) VALUES(?i, ?s, ?s)", $booking, $sum, $method);
  }

  public function select_sum_pay(){
    $connect = $this->connect;
    $booking = $this->booking;
    $sum = $connect->getOne("SELECT sum FROM booking_request_object_module WHERE id=?i", $booking);
    $data = $connect->getAll("SELECT sum FROM booking_request_object_module_payment WHERE booking=?i", $booking);
    $pay = 0;
    foreach($data as $row){
      $pay+= $row["sum"];
    }
    $raz = $sum - $pay;
    return $raz;
  }

  public function select_request_payment(){
    $connect = $this->connect;
    $booking = $this->booking;
    $row = $connect->getRow("SELECT id, sum, DATE_FORMAT(time, '%d.%m.%Y') as date, bid_pay FROM payment_request WHERE booking=?i AND status=1", $booking);
    return $row;
  }

}

?>
