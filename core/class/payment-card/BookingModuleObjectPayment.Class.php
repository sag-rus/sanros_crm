<?php

class BookingModuleObjectPayment extends AlfaBank{

  protected function check_payment(){
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;
    $type = $this->type;
    $answer = array(
      "to-pay" => 0
    );
    $answer["total"] = $connect->getOne("SELECT sum FROM booking_request_object_module WHERE id=?i AND turist=?i AND (status=2 OR status=3 OR quota=1)", $booking, $turist);
    if($answer["total"] > 0){
      if($type == "prepay"){
        $object = $connect->getOne("SELECT object FROM booking_request_object_module WHERE id=?i", $booking);
        $percent = $connect->getOne("SELECT prepay FROM booking_module_object WHERE object=?i", $object);
        if($percent > 0){
          $answer["to-pay"] = $answer["total"] * ($percent / 100);
        }
      }else{
        $prepay = 0;
        $payment = $connect->getAll("SELECT sum FROM booking_request_object_module_payment WHERE booking=?i", $booking);
        foreach($payment as $pay){
          $prepay+= $pay["sum"];
        }
        $answer["to-pay"]= $answer["total"] - $prepay;
      }
    }
    return $answer;
  }

  public function show_payment_card($type){
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;
    $this->type = $type;

    $answer = array();
    $sum = $this->check_payment();
		if($sum["to-pay"] > 0){
      $object = $connect->getOne("SELECT object FROM booking_request_object_module WHERE id=?i", $booking);
			$answer["id"] = $booking;
			$answer["check"] = 1;
			$answer["sum"] = $sum["total"];
			$answer["sum-pay"] = add_null($sum["to-pay"]);
			$turist = new DisplayClient($turist);
      $answer["turist"] = $turist->select_fio();
      unset($turist);
			$answer["product"] = "Оплата путевки по заявке №".$booking." (".get_object($connect, $object, "type").")";
		}
		return $answer;
  }

  public function registration_payment($type){
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;
    $this->type = $type;

    $sum = $this->check_payment();
    $sum_to_pay = $sum["to-pay"];
    if($sum_to_pay > 0){
      $object = $connect->getOne("SELECT object FROM booking_request_object_module WHERE id=?i", $booking);
      $count = (int)$connect->getOne("SELECT count_payment FROM booking_request_object_module WHERE id=?i", $booking) + 1;
      $connect->query("UPDATE booking_request_object_module SET count_payment=?i WHERE id=?i", $count, $booking);

      $object = $connect->getOne("SELECT object FROM booking_request_object_module WHERE id=?i", $booking);
      $link = $this->link;
      $orderNumber = $booking."-".$count;
      $returnUrl = $link."core/payment-module/success-payment.php?id=".$orderNumber;
      $failUrl = $link."core/payment-module/fault-payment.php?id=".$booking;
      $description = "Оплата путевки по заявке №".$booking." (".get_object($connect, $object, "type").")";
      $data = array(
        "sum" => $sum_to_pay,
        "orderNumber" => $orderNumber,
        "returnUrl" => $returnUrl,
        "failUrl" => $failUrl,
        "description" => $description
      );
      $this->bookingInfo = $data;
      $answer = $this->registration_payment_alfabank();

      if($answer["orderId"]){
        $check = $connect->getOne("SELECT id FROM payment_request WHERE order_id=?s", $answer["orderId"]);
        if(!$check){
          $bank_com = $this->bankInfo["commission"];
          $connect->query("INSERT INTO payment_request(booking, sum, bank_com, order_id, bid_pay) VALUES (?i, ?s, ?s, ?s, ?s)", $booking, $sum_to_pay, $bank_com, $answer["orderId"], $data["orderNumber"]);
        }
      }
      return $answer;
    }
    return FALSE;
  }

  public function check_status_payment($bid_pay = ""){
    $connect = $this->connect;

    $row = $connect->getRow("SELECT id, booking, sum, order_id FROM payment_request WHERE bid_pay=?s AND booking!=''", $bid_pay);
    if(!$row["id"])
      return;
    $booking = $row["booking"];
    $orderId = $row["order_id"];
    $data = array(
      "orderId" => $orderId
    );
    $this->bookingInfo = $data;
    $data = $this->get_status_alfabank();

    $connect->query("UPDATE payment_request SET status=?i WHERE order_id=?s", $data["OrderStatus"], $orderId);
    if($data["OrderStatus"] != 1)
      return $data["errorMessage"];

    $check = $connect->getOne("SELECT quota FROM booking_request_object_module WHERE id=?i AND status=1 AND quota=1", $booking);
    $bookingInfo = new RequestBookingModule;
    $bookingInfo->update_quota_booking();
    if($check){
      $send = new SendMailObjectModule;
      $send->notification_request_payment_booking();
      unset($send);
      return 1;
    }
    return 2;
  }

  public function deposit_payment($bid_pay = ""){
    $connect = $this->connect;

    $row = $connect->getRow("SELECT id, booking, sum, order_id, type FROM payment_request WHERE bid_pay=?s AND booking!=''", $bid_pay);
    if(!$row["id"])
      return;
    $booking = $row["booking"];
    $orderId = $row["order_id"];
    $sum_to_pay = $row["sum"];
    ConfigCRM::getInstance()->turist = $connect->getOne("SELECT turist FROM booking_request_object_module WHERE id=?i", $booking);

    $data = array(
      "orderId" => $orderId,
      "amount" => $sum_to_pay * 100
    );
    $this->bookingInfo = $data;
    $data = $this->deposit_payment_alfabank();

    if($data["errorCode"] != 0)
      return $data["errorMessage"]." ".($sum_to_pay * 100)." ".$orderId;

    $payment = new PaymentRequestBookingModule;
    $payment->create_payment($sum_to_pay, "card");
    $balance = $payment->select_sum_pay();

    $bookingInfo = new RequestBookingModule;

    if($balance <= 0){

      $connect->query("UPDATE booking_request_object_module SET status=4 WHERE id=?i", $booking);
      $bonus = new CreateBonus;
      $bonus->create_bonus_module_object();
      $bookingInfo->save_history_booking("Оплата клиентом банковской картой. Сумма ".$sum_to_pay);

    }else{

      $connect->query("UPDATE booking_request_object_module SET status=3 WHERE id=?i", $booking);
      $bookingInfo->save_history_booking("Предоплата клиентом банковской картой. Сумма ".$sum_to_pay);

    }

    $send = new SendMailObjectModule;
    $send->notification_payment_booking();
    unset($send);

    $send = new SendMailTuristModule;
    $send->notification_payment_booking();
    unset($send);

    unset($bookingInfo);
    unset($payment);
    $connect->query("UPDATE payment_request SET status=2 WHERE order_id=?s", $orderId);
    return 1;
  }


  public function cancel_payment($bid_pay = ""){
    $connect = $this->connect;

    $row = $connect->getRow("SELECT id, booking, sum, order_id FROM payment_request WHERE bid_pay=?s AND booking!=''", $bid_pay);
    if(!$row["id"])
      return;
    $booking = $row["booking"];
    $orderId = $row["order_id"];
    $data = array(
      "orderId" => $orderId
    );
    $this->bookingInfo = $data;
    $data = $this->cancel_alfabank();
    $data = $this->get_status_alfabank();

    $connect->query("UPDATE payment_request SET status=?i WHERE order_id=?s", $data["OrderStatus"], $orderId);
    if($data["OrderStatus"] != 1)
      return $data["errorMessage"];
  }

}

?>
