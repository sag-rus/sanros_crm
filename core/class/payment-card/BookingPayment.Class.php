<?php

class BookingPayment extends AlfaBank{

  public function check_payment(){
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;
    $type = $this->type;

    $answer = array(
      "to-pay" => 0,
      "bonus" => 0,
      "prepay" => 0
    );
    $answer["total"] = $connect->getOne("SELECT sum FROM reckoning WHERE id=?i AND turist=?i AND (status=3 OR status=4)", $booking, $turist);
    if($answer["total"] > 0){
      if($type == "prepay"){
        $sum_prepay = $connect->getOne("SELECT sum FROM time_payment WHERE type=2 AND id_schet=?i", $booking);
        if($sum_prepay){
          $answer["to-pay"] = $sum_prepay;
        }
      }else{
        $answer["bonus"] = abs($connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum < 0", $booking));
        if($answer["bonus"] > 0){
          $max_bonus = $this->check_bonus_payment_bank_card();
          if($max_bonus < $answer["bonus"]){
            $answer["check_bonus"] = 1;
            $answer["bonus"] = $max_bonus;
          }
        }
        $payment = $connect->getAll("SELECT sum FROM payment WHERE type=1 AND schet=?i", $booking);
        foreach($payment as $pay){
          $answer["prepay"]+= $pay["sum"];
        }
        $answer["to-pay"] = $answer["total"] - $answer["bonus"] - $answer["prepay"];
      }

    }
    return $answer;
  }

  public function show_payment_card($type){
    $connect = $this->connect;
    $booking = $this->booking;
    $client = $this->turist;
    $this->type = $type;

    $answer = array();
    $sum = $this->check_payment();
    if($sum["to-pay"] > 0){
      $array = $connect->getRow("SELECT id, sum, id_obj FROM reckoning WHERE id=?i AND turist=?i AND (status=3 OR status=4)", $booking, $client);
      $answer["id"] = $array["id"];
      $answer["check"] = 1;

      $answer["all_sum"] = add_null($sum["total"]);
      $answer["sum"] = add_null($sum["to-pay"]);
			$answer["bonus"] = add_null($sum["bonus"]);
			$answer["prepay"] = add_null($sum["prepay"]);
      $answer["commission_info"] = $this->bankInfo["commission"];
			$turist = new DisplayClient($client);
      $answer["turist"] = $turist->select_fio();
      unset($turist);
			$answer["product"] = "Оплата путевки по заявке №".$booking." (".get_object($connect, $array["id_obj"], "type").")";
    }
		return $answer;
  }

  public function registration_payment($type = ""){
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;

    $sum = $connect->getOne("SELECT sum FROM reckoning WHERE id=?i AND (status=3 OR status=4) AND turist=?i", $booking, $turist);
  	if($sum > 0){
  		$count = (int)$connect->getOne("SELECT count_payment FROM reckoning WHERE id=?i", $booking) + 1;
  		$connect->query("UPDATE reckoning SET count_payment=?i WHERE id=?i", $count, $booking);
  		$bonus = abs($connect->getOne("SELECT sum FROM bonus WHERE sum<0 AND schet=?i", $booking));
  		if($bonus > 0){
  			$max_bonus = $this->check_bonus_payment_bank_card();
  			if($bonus > $max_bonus){
  				if($max_bonus == 0)
  					$connect->query("DELETE FROM bonus WHERE sum<0 AND schet=?i", $booking);
  				else
  					$connect->query("UPDATE bonus SET sum=?s WHERE sum<0 AND schet=?i", $max_bonus * (-1), $booking);
  				save_schet_to_history($connect, $booking, "Корректировка суммы бонусов");
  				$bonus = $max_bonus;
  			}
  		}
  		$type_pay = 1;
  		$prepay = 0;
  		$payment = $connect->getAll("SELECT sum FROM payment WHERE type=1 AND schet=?i", $booking);
  		foreach($payment as $pay)
  			$prepay+= $pay["sum"];
  		$sum_to_pay = $sum - $bonus - $prepay;
  		if($type == "prepay"){
  			$type_pay = 2;
  			$sum_prepay = $connect->getOne("SELECT sum FROM time_payment WHERE type=2 AND id_schet=?i", $booking);
  			if($sum_prepay)
  				$sum_to_pay = $sum_prepay;
  		}

      $object = $connect->getOne("SELECT id_obj FROM reckoning WHERE id=?i", $booking);
      $link = $this->link;
      $orderNumber = $booking."-".$count;
      $returnUrl = $link."core/payment/success-payment.php?id=".$orderNumber;
      $failUrl = $link."core/payment/fault-payment.php?id=".$booking;
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
          $connect->query("INSERT INTO payment_request(bid, type, pay_method, sum, bank_com, order_id, bid_pay) VALUES (?i, ?i, 5, ?s, ?s, ?s, ?s)", $booking, $type_pay, $sum_to_pay, $bank_com, $answer["orderId"], $data["orderNumber"]);
        }
      }
      return $answer;
    }
    return FALSE;
  }

  public function deposit_payment($bid_pay = ""){
    $connect = $this->connect;

    $row = $connect->getRow("SELECT id, bid, sum, order_id, type FROM payment_request WHERE bid_pay=?s", $bid_pay);
    if(!$row["id"])
      return;
    $bid = $row["bid"];
    $orderId = $row["order_id"];
    $sum_to_pay = $row["sum"];
    $type_pay = $row["type"];
    $row = $connect->getRow("SELECT id_obj, id_user, date_v, turist FROM reckoning WHERE id=?i", $bid);
    $object = $row["id_obj"];
    $manager = $row["id_user"];
    $client = $row["turist"];
    $arrival = date_change($row["date_v"], ".");

    ConfigCRM::getInstance()->turist = $client;
    ConfigCRM::getInstance()->booking = $bid;

    $data = array(
      "orderId" => $orderId
    );
    $this->bookingInfo = $data;
    $data = $this->get_status_alfabank();

    $connect->query("UPDATE payment_request SET status=?i WHERE order_id=?s", $data["OrderStatus"], $orderId);
    if($data["OrderStatus"] != 1)
      return $data["errorMessage"];

    $sum = $data["Amount"] / 100;
    if($sum != $sum_to_pay)
      return;

    $data = array(
      "orderId" => $orderId,
      "amount" => $sum * 100
    );
    $this->bookingInfo = $data;
    $data = $this->deposit_payment_alfabank();

    if($data["errorCode"] != 0)
      return $data["errorMessage"]." ".($sum * 100)." ".$orderId;

    $bank_com = $bank_com = $this->bankInfo["commission"];
    $today = date("Y-m-d");

    if($type_pay == 1){

      $connect->query("INSERT INTO payment(schet, date, type, pay_method, sum, bank_com) VALUES (?i, ?s, 2, 5, ?s, ?s)", $bid, $today, $sum, $bank_com);
      $connect->query("UPDATE reckoning SET status=5 WHERE id=?i LIMIT 1", $bid);
      $bonus = new CreateBonus;
      $bonus->create_bonus();
      unset($bonus);
      save_notification($connect, "Оплата картой №".$bid, $manager);
      save_schet_to_history($connect, $bid, "Оплата клиентом банковской картой. Сумма ".$sum);

    }elseif($type_pay == 2){

      $connect->query("INSERT INTO payment(schet, date, type, pay_method, sum, bank_com) VALUES (?i, ?s, 1, 5, ?s, ?s)", $bid, $today, $sum, $bank_com);
      $connect->query("UPDATE reckoning SET status=4 WHERE id=?i LIMIT 1", $bid);
      save_notification($connect, "Предоплата картой №".$bid, $manager);
      save_schet_to_history($connect, $bid, "Предоплата клиентом банковской картой. Сумма ".$sum);

    }

    $connect->query("UPDATE payment_request SET status=2 WHERE order_id=?s", $orderId);
    $send = new SendMailTurist;
    $send->notification_payment_booking();
    unset($send);

    return 1;
  }

  private function check_bonus_payment_bank_card(){
    $connect = $this->connect;
    $booking = $this->booking;
    $bank_com = $this->bankInfo["commission"];

  	$bonus = $connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum < 0", $booking);
  	if($bonus)
  		$bonus = abs($bonus);
  	else
  		return FALSE;
  	$reward = 0;
  	$reck_reward = $connect->getOne("SELECT reward FROM reckoning WHERE id=?i", $booking);
  	if($reck_reward > 0){
  		$reward = $reck_reward;
  	}else{
  		$data = $connect->getAll("SELECT id FROM position_reck WHERE schet=?i", $booking);
  		foreach($data as $row)
  			$reward+= get_reward_schet_position($connect, $row["id"]);
  	}
  	$reward = round($reward, 2);
  	$reck = $connect->getRow("SELECT sum, id_com, correction FROM reckoning WHERE id=?i", $booking);
  	$raz = 0;
  	$bank_com = ($bank_com * $reck["sum"]) / 100;
  	if($reck["correction"])
  		$raz-= $reck["correction"];

  	$reward = round($reward - $raz, 2);
  	$max_bonus = ($reward / 2) - $bank_com;
  	if($max_bonus < $bonus)
  		$bonus = $max_bonus;
  	if($bonus < 0)
  		return FALSE;
  	return round($bonus);
  }

}

?>
