<?php

class RequestBookingModule{

  protected $object;
  protected $connect;
  protected $booking;
  protected $source_default = "official";
  protected $source = array(
    "official" => array(
      "icon" => "check",
      "label" => "Заявка с официального сайта объекта"
    ),
    "russia" => array(
      "icon" => "globe",
      "label" => "Заявка с сайта санатории-россии.рф"
    )
  );

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->booking = $config->booking;
  }

  public function save_history_booking($text){
    $connect = $this->connect;
    $booking = $this->booking;
    $status = $connect->getOne("SELECT status FROM booking_request_object_module WHERE id=?i", $booking);
    $connect->query("INSERT INTO booking_request_object_module_history(booking, text, status) VALUES(?i, ?s, ?i)", $booking, $text, $status);
  }

  public function calculation_sum_booking(){
    $booking = $this->booking;
    $connect = $this->connect;
    $itog = 0;
    $data = $connect->getAll("SELECT sum, number, type, days FROM booking_request_object_module_position WHERE booking=?i", $booking);
    foreach($data as $row){
      $sum = $row["sum"];
      $number = $row["number"];
      $type = $row["type"];
      $days = $row["days"];
      $itog += calculate_position($sum, $number, $type, $days);
    }
    $connect->query("UPDATE booking_request_object_module SET sum=?s WHERE id=?i", $itog, $booking);
  }

  public function calculation_arrival_date(){
    $booking = $this->booking;
    $connect = $this->connect;
    $arrival = $connect->getOne("SELECT arrival FROM booking_request_object_module_position WHERE booking=?i ORDER BY arrival", $booking);
  	if($arrival)
  		$connect->query("UPDATE booking_request_object_module SET arrival=?s WHERE id=?i", $arrival, $booking);
  	$row = $connect->getRow("SELECT arrival, days FROM booking_request_object_module_position WHERE booking=?i ORDER BY DATE_ADD(arrival, INTERVAL (days) DAY) DESC LIMIT 1", $booking);
  	$arrival = date_change($row["arrival"]);
  	if($arrival){
  		$days = $row["days"];
  		$leaving = date_sum($arrival, $days);
  		$leaving = date("Y-m-d", $leaving);
  		$connect->query("UPDATE booking_request_object_module SET leaving=?s WHERE id=?i", $leaving, $booking);
  	}
  }

  public function check_edit_booking(){
    $booking = $this->booking;
    $connect = $this->connect;
    $status = $connect->getOne("SELECT status FROM booking_request_object_module WHERE id=?i", $booking);
    if($status == 1 OR $status == 2)
      return TRUE;
    return FALSE;
  }

  public function update_quota_booking(){
    $booking = $this->booking;
    $connect = $this->connect;
    $connect->query("UPDATE booking_request_object_module SET quota=2 WHERE quota=1 AND id=?i", $booking);
  }

}

?>
