<?php

class StatusRequestBookingModule extends RequestBookingModule{

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->object = $config->object;
    $this->booking = $config->booking;
  }

  public function change_status($data){
    $booking = $this->booking;
    $object = $this->object;
    $connect = $this->connect;
    ConfigCRM::getInstance()->turist = $connect->getOne("SELECT turist FROM booking_request_object_module WHERE id=?i", $booking);
    $status = $data["status"];
    $current = $connect->getOne("SELECT status FROM booking_request_object_module WHERE id=?i AND object=?i", $booking, $object);
    $check = 0;
    if($current == 1 AND ($status == 2 OR $status == 5)){
      if($status == 2){
        $send = new SendMailTuristModule;
        $send->notification_confirm_booking();
        unset($send);
      }
      $check = 1;
    }elseif(($current == 2 OR $current = 3) AND $status == 4){
      $check = 1;
      $status = 3;
      if(!isset($data["payment"]) OR $data["payment"] <= 0)
        return;
      $payment = $data["payment"];
      $method = $data["method"];
      $pay = new PaymentRequestBookingModule;
      $sum_to_pay = $pay->select_sum_pay();
      if($payment > $sum_to_pay)
        return;
      if($payment == $sum_to_pay){
        $status = 4;
      }
      $pay->create_payment($payment, $method);
      $send = new SendMailTuristModule;
      $send->notification_payment_booking();
      unset($send);
    }elseif($current == 2 AND $status == 5){
      $check = 1;
    }
    if($check == 1){
      $connect->query("UPDATE booking_request_object_module SET status=?i WHERE id=?i", $status, $booking);
      $this->save_history_booking("Изменение статуса");
    }
    if($status == 4 AND $payment == $sum_to_pay){
      $bonus = new CreateBonus;
      $bonus->create_bonus_module_object();
    }
  }

  public function change_payment($data){
    $booking = $this->booking;
    $object = $this->object;
    $connect = $this->connect;
    $status = $data["status"];
    $payment = new PaymentRequestBookingModule;
    $request = $payment->select_request_payment();
    if($request["sum"] > 0){
      $current = $connect->getOne("SELECT status FROM booking_request_object_module WHERE id=?i AND object=?i", $booking, $object);
      $check = 0;
      if($current == 1 AND ($status == 2 OR $status == 5)){
        $check = 1;
      }elseif(($current == 2 OR $current = 3) AND $status == 4){
        $check = 1;
        $status = 3;
        if(!isset($data["payment"]) OR $data["payment"] <= 0)
          return;
        $payment = $data["payment"];
        $method = $data["method"];
        $pay = new PaymentRequestBookingModule;
        $sum_to_pay = $pay->select_sum_pay();
        if($payment > $sum_to_pay)
          return;
        if($payment == $sum_to_pay)
          $status = 4;
        $pay->create_payment($payment, $method);
      }elseif($current == 2 AND $status == 5){
        $check = 1;
      }
      if($check == 1)
        $connect->query("UPDATE booking_request_object_module SET status=?i WHERE id=?i", $status, $booking);
    }
  }

}

?>
