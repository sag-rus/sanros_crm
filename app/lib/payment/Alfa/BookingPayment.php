<?php
namespace App\lib\payment\Alfa;

use App\lib\CRM\Client\Display;
use App\Model\Bonus;

class BookingPayment {
  protected $connect;
  protected $link;
  protected $booking;
  protected $type;
  protected $turist;

  protected $bookingInfo = array(
    "id",
    "sum",
    "orderId",
    "orderNumber",
    "returnUrl",
    "failUrl",
    "orderId"
  );
  protected $bankInfo = array(
    "userName",
    "password",
    "link",
    "commission",
    "commission_qr"
  );

  protected function getObject($connect, $id, $type_view = ""){
    $data_object = $connect->getRow("SELECT name, full_name, type, id_reg, city FROM object WHERE id=?i", $id);
    $short = $data_object["name"];
    $full = $data_object["full_name"];
    $type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $data_object["type"]);
    $id_reg = $data_object["id_reg"];
    $city = $data_object["city"];
    if($type_view == "full"){
      $object = $full;
      if(!$object)
        $object = $short;
    }elseif($type_view == "full_and_place"){
      $object = $full;
      if(!$object)
        $object = $short;
      if($city)
        $city = ", ".$city;
      $data_region = $connect->getRow("SELECT name, id_country FROM region WHERE id=?i", $id_reg);
      $region = $data_region["name"];
      $id_country = $data_region["id_country"];
      $country = $connect->getOne("SELECT name FROM country WHERE id=?i", $id_country);
      if($country)
        $object = $object." (".$country.", ".$region.$city.")";
    }elseif($type_view == "place"){
      $object = $short;
      $data_region = $connect->getRow("SELECT name, id_country FROM region WHERE id=?i", $id_reg);
      $region = $data_region["name"];
      $id_country = $data_region["id_country"];
      if($city)
        $city = ", ".$city;
      $country = $connect->getOne("SELECT name FROM country WHERE id=?i", $id_country);
      if($type)
        $type.= " ";
      if($country)
        $object = $type.$object." (".$country.", ".$region.$city.")";
    }elseif($type_view == "type"){
      $object = $type." ".$short;
    }else
      $object = $short;
    return $object;
  }

  protected static function calculatePosition($sum, $number, $type, $days){
    //Тип 1 - за человека в сутки
    if($type == 1){
      $all_sum = ($sum * $number) * $days;
      //Тип 2 - за номер (дом)
    }elseif($type == 2){
      $all_sum = ($sum * $number) * $days;
      //Тип 3 - за заезд
    }elseif($type == 3){
      $all_sum = $sum * $number;
    }
    return $all_sum;
  }

  public function __construct(array $settings = [])
  {
    $config = \App\lib\CRM\Config\Client::getInstance();
    $this->connect = $config->connect;
    $this->link = $config->clientCabinet["link"];
    $this->bankInfo = $config->onlinePaymentInfoAlfa;
    $this->booking = $config->booking;
    $this->turist = $config->account;

  }

  protected function getRewardSchetPosition($id){
    $row = $this->connect->getRow("SELECT reward, sum, number, type, days FROM position_reck WHERE id=?i", $id);
    $reward = $row["reward"];
    $sum = $row["sum"];
    $number = $row["number"];
    $type = $row["type"];
    $days = $row["days"];
    $all_sum = self::calculatePosition($sum, $number, $type, $days);
    $reward = $all_sum * ($reward / 100);
    return add_null($reward);
  }

  public function checkBonusPaymentBankCard()
  {
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
        $reward+= $this->getRewardSchetPosition($row["id"]);
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

  public function checkPayment()
  {
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;
    $type = $this->type;

    $answer = array(
      "to-pay" => 0,
      "bonus" => 0,
      "prepay" => 0
    );
    $reck_properties = $connect->getRow("SELECT sum, id_dis, exclude_bank_commission FROM reckoning WHERE id=?i AND turist=?i AND (status=3 OR status=4) LIMIT 1", $booking, $turist);
    $answer["total"] = $reck_properties['sum'];
    if($answer["total"] > 0){
      if($type == "prepay"){
        $sum_prepay = $connect->getOne("SELECT sum FROM time_payment WHERE type=2 AND id_schet=?i", $booking);
        if($sum_prepay){
          $answer["to-pay"] = $sum_prepay;
        }
      }else{
        $dis = 0;

        if($reck_properties['id_dis'] > 0) {
          $dis_row = $connect->getRow("SELECT `id`, `value`, `type` FROM `discount` WHERE `id` = ?i", $reck_properties['id_dis']);
          if($dis_row && $dis_row['value'] > 0) {
            if($dis_row['type'] == 2) {
              $dis = $dis_row['value'];
            }
            else {
              $dis = round($answer["total"]*$dis_row['value']/100,2);
            }
          }
        }

        $answer['all_bonus_count'] = $connect->getOne("SELECT COUNT(*) FROM `bonus` WHERE `turist` = ?i AND `sum` > 0",$turist);
        $answer['reckonings_count'] = $connect->getOne("SELECT COUNT(*) FROM `reckoning` WHERE `turist` = ?i AND `status` = 5", $turist);

        $answer["bonus"] = abs($connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum < 0", $booking));

        if($answer["bonus"] > 0){
          $max_bonus = $this->checkBonusPaymentBankCard();
          if($max_bonus < $answer["bonus"]){
            $answer["check_bonus"] = 1;
            $answer["bonus"] = $max_bonus;
          }
        }
        $payment = $connect->getAll("SELECT sum FROM payment WHERE type=1 AND status != 0 AND schet=?i", $booking);
        foreach($payment as $pay){
          $answer["prepay"]+= $pay["sum"];
        }


        $answer["to-pay-no-com"] = $answer["total"] - $answer["bonus"] - $answer["prepay"]-$dis;

        if($answer['all_bonus_count'] > 1 && $answer['reckonings_count'] > 0 && !$reck_properties['exclude_bank_commission']) {
          $com = $this->bankInfo['commission'] / 100;
          $answer["to-pay"] = round($answer["to-pay-no-com"] * (1 + ($com/(1-$com))),2);
        }
        else
          $answer["to-pay"] = $answer["to-pay-no-com"];
      }

    }
    return $answer;
  }

  public function showPaymentCard($type)
  {
    $connect = $this->connect;
    $booking = $this->booking;
    $client = $this->turist;
    $this->type = $type;
    $answer = array();
    $sum = $this->checkPayment();
    if($sum["to-pay"] > 0){
      $array = $connect->getRow("SELECT id, sum, id_obj, exclude_bank_commission FROM reckoning WHERE id=?i AND turist=?i AND (status=3 OR status=4)", $booking, $client);
      $answer["id"] = $array["id"];
      $answer["check"] = 1;

      $answer["all_sum"] = add_null($sum["total"]);
      $answer["sum"] = add_null($sum["to-pay"]);
      $answer["sum_no_commission"] = add_null($sum["to-pay-no-com"]);
      $answer["bonus"] = add_null($sum["bonus"]);
      $answer["prepay"] = add_null($sum["prepay"]);
      $answer['all_bonus_count'] = $sum['all_bonus_count'];
      $answer['reckonings_count'] = $sum['reckonings_count'];
      $answer['exclude_bank_commission'] = $array['exclude_bank_commission'];
      $turist = new Display($client);
      $answer["turist"] = $turist->selectFio();
      unset($turist);
      $answer["product"] = "Оплата путевки по заявке №".$booking." (".self::getObject($connect, $array["id_obj"], "type").")";
    }
    else {
      $answer['msg'] = 'Pay sum is not correct';
      //$answer['data_dump'] = $sum;
      //$answer['connect_dump'] = \App\lib\CRM\Config\Client::getInstance();
    }
    return $answer;

  }

  public function registerPayment($type = "", $qr = ''){
    $config = \App\lib\CRM\Config\Client::getInstance();
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;

    $log = PHP_EOL."START registerPayment".PHP_EOL;
    file_put_contents('alfa_deposit_log_'.date('Y-m-d').'.txt', $log, FILE_APPEND);

    $reck_properties = $connect->getRow("SELECT sum, id_dis, exclude_bank_commission, is_test, state_program, children_rest, far_east FROM reckoning WHERE id=?i AND turist=?i AND (status=3 OR status=4) LIMIT 1", $booking, $turist);

    //$sum = $connect->getOne("SELECT sum FROM reckoning WHERE id=?i AND (status=3 OR status=4) AND turist=?i", $booking, $turist);
    $sum = $reck_properties['sum'];
    if($sum > 0){
      $count = (int)$connect->getOne("SELECT count_payment FROM reckoning WHERE id=?i", $booking) + 1;
      $connect->query("UPDATE reckoning SET count_payment=?i WHERE id=?i", $count, $booking);
      $bonus = abs($connect->getOne("SELECT sum FROM bonus WHERE sum<0 AND schet=?i", $booking));

      $dis = 0;

      if($reck_properties['id_dis'] > 0) {
        $dis_row = $connect->getRow("SELECT `id`, `value`, `type` FROM `discount` WHERE `id` = ?i", $reck_properties['id_dis']);
        if($dis_row && $dis_row['value'] > 0) {
          if($dis_row['type'] == 2) {
            $dis = $dis_row['value'];
          }
          else {
            $dis = round($sum*$dis_row['value']/100,2);
          }
        }
      }      

      if($bonus > 0){
        $max_bonus = $this->checkBonusPaymentBankCard();
        if($bonus > $max_bonus){
          if($max_bonus == 0)
            $connect->query("DELETE FROM bonus WHERE sum<0 AND schet=?i", $booking);
          else
            $connect->query("UPDATE bonus SET sum=?s WHERE sum<0 AND schet=?i", $max_bonus * (-1), $booking);
          $this->saveSchetToHistory($booking, "Корректировка суммы бонусов");
          $bonus = $max_bonus;
        }
      }
      $type_pay = 1;
      $prepay = 0;
      $payment = $connect->getAll("SELECT sum FROM payment WHERE type=1 AND status != 0 AND schet=?i", $booking);
      foreach($payment as $pay)
        $prepay+= $pay["sum"];

      $all_bonus_count = $connect->getOne("SELECT COUNT(*) FROM `bonus` WHERE `turist` = ?i AND `sum` > 0",$turist);
      $reckonings_count = $connect->getOne("SELECT COUNT(*) FROM `reckoning` WHERE `turist` = ?i AND `status` = 5", $turist);


      $sum_to_pay = $sum - $bonus - $prepay-$dis;

      if($all_bonus_count > 1 && $reckonings_count > 0 && !$reck_properties['exclude_bank_commission']) {
        if ($qr=='1') $com = $this->bankInfo['commission_qr'] / 100;
        else $com = $this->bankInfo['commission'] / 100;
        $sum_to_pay *= (1 + ($com/(1-$com)));
        $sum_to_pay = round($sum_to_pay,2);
      }

      if($type == "prepay"){
        $type_pay = 2;
        $sum_prepay = $connect->getOne("SELECT sum FROM time_payment WHERE type=2 AND id_schet=?i", $booking);
        if($sum_prepay)
          $sum_to_pay = $sum_prepay;
      }

      $object = $connect->getOne("SELECT id_obj FROM reckoning WHERE id=?i", $booking);
      $link = $this->link;
      $orderNumber = $booking."-".$count;
      $returnUrl = $link."core/payment/return-payment-alfa.php?id=".$orderNumber.'&act=success';
      $failUrl   = $link."core/payment/return-payment-alfa.php?id=".$orderNumber.'&act=fail';
      
      $description = "Оплата путевки по заявке №".$booking." (".self::getObject($connect, $object, "type").")";


      $url = $this->bankInfo['link'].'register.do?userName='.$this->bankInfo['userName'].'&password='.$this->bankInfo['password'].'&amount='.($sum_to_pay*100).'&currency=810&language=ru&description='.urlencode($description).'&orderNumber='.$orderNumber.'&returnUrl='.urlencode($returnUrl).'&failUrl='.urlencode($failUrl).'&expirationDate='.date("Y-m-d", time()+86400*7).'T'.date("H:i:s", time()+86400*7);

      $log = "action=register.do url=".$url.PHP_EOL;
      file_put_contents('alfa_deposit_log_'.date('Y-m-d').'.txt', $log, FILE_APPEND);

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $answer = curl_exec($ch);
      curl_close($ch);  
      $answer = json_decode($answer, true);
      $answer['renderedQr'] = '';


      $log = "action=register.do RESULT".PHP_EOL;
      $log .= print_r($answer, true).PHP_EOL;
      file_put_contents('alfa_deposit_log_'.date('Y-m-d').'.txt', $log, FILE_APPEND);

      if ($qr=='1')  {

        $url = $this->bankInfo['link'].'sbp/c2b/qr/dynamic/get.do?userName='.$this->bankInfo['userName'].'&password='.$this->bankInfo['password'].'&mdOrder='.$answer['orderId'].'&qrFormat=image';

        $log = "action=get.do START=".$url.PHP_EOL;
        file_put_contents('alfa_deposit_log_'.date('Y-m-d').'.txt', $log, FILE_APPEND);        

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);    
        $response = json_decode($response, true); 
        if ($response['qrStatus']=='STARTED') {
          $answer['renderedQr'] = $response['renderedQr'];
          $answer['payload'] = $response['payload'];
        }

        $log = "action=get.do RESULT".PHP_EOL;
        $log .= print_r($response, true).PHP_EOL;
        file_put_contents('alfa_deposit_log_'.date('Y-m-d').'.txt', $log, FILE_APPEND);        

      }
      $answer['orderNumber'] = $orderNumber;

      if($answer["orderId"]){
        $check = $connect->getOne("SELECT id FROM payment_request WHERE order_id=?s", $answer["orderId"]);
        if(!$check){
          $bank_com = $this->bankInfo["commission"];
          $pay_method=6; //Альфа (картой)
          if ($qr=='1') $pay_method=7; //Альфа (СБП)
          $connect->query("INSERT INTO payment_request(bid, type, pay_method, sum, bank_com, order_id, bid_pay) VALUES (?i, ?i, ?i, ?s, ?s, ?s, ?s)", $booking, $type_pay, $pay_method, $sum_to_pay, $bank_com, $answer["orderId"], $orderNumber);
        }
      }

      return $answer;
    }
    return FALSE;
  }

  protected function saveNotification($text, $user){
    $this->connect->query("INSERT INTO notification(text, user) VALUES (?s, ?i)", $text, $user);
  }

  protected function saveSchetToHistory($id, $note = ""){
    global $session_login;
    $row = $this->connect->getRow("SELECT status, status_san FROM reckoning WHERE id=?i", $id);
    $new_status = $row['status'];
    $new_status_san = $row['status_san'];
    $today = date("Y-m-d");
    $time = date("H:i:s");
    $this->connect->query("INSERT INTO history_schet(date, time, id_schet, id_user, new_status, new_status_san, note) VALUES(?s, ?s, ?i, ?i, ?i, ?i, ?s)", $today, $time, $id, $session_login, $new_status, $new_status_san, $note);
  }

  public function depositPayment($bid_pay = ""){
    
    try {
      $connect = $this->connect;


      $log = PHP_EOL."START depositPayment bid_pay=".$bid_pay.PHP_EOL;
      file_put_contents('alfa_deposit_log_'.date('Y-m-d').'.txt', $log, FILE_APPEND);      

      $row = $connect->getRow("SELECT id, bid, sum, pay_method, order_id, type, status FROM payment_request WHERE bid_pay=?s", $bid_pay);

      if(!$row["id"]) return '---';

      if ($row['status']=='2') return 1; //Значит успешная отметка по оплате уже была сделана через cron-робота check-info.php

      $bid = $row["bid"];
      $orderId = $row["order_id"];
      $sum_to_pay = $row["sum"];
      $type_pay = $row["type"];
      $pay_method = $row["pay_method"];
      $row = $connect->getRow("SELECT id_obj, id_user, date_v, turist FROM reckoning WHERE id=?i", $bid);
      $object = $row["id_obj"];
      $manager = $row["id_user"];
      $client = $row["turist"];
      $arrival = date_change($row["date_v"], ".");
      $timestamp = date("U");





      \App\lib\CRM\Config\Client::getInstance()->turist = $client;
      \App\lib\CRM\Config\Client::getInstance()->booking = $bid;



      /*$data = array(
        "orderId" => $orderId
      );
      $this->bookingInfo = $data;*/
      //$data = $this->getPaymentStatus();

      //Проверка состояния заказа getOrderStatusExtended.do
      $url = $this->bankInfo['link'].'getOrderStatusExtended.do?userName='.$this->bankInfo['userName'].'&password='.$this->bankInfo['password'].'&language=ru&orderId='.$orderId;
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $data = curl_exec($ch);
      curl_close($ch);  
      $data = json_decode($data, true);

      $log = "action=getOrderStatusExtended.do".PHP_EOL;
      $log .= print_r($data, true).PHP_EOL;
      file_put_contents('alfa_deposit_log_'.date('Y-m-d').'.txt', $log, FILE_APPEND);              
      //Проверка состояния заказа getOrderStatusExtended.do


      $connect->query("UPDATE payment_request SET status=?i WHERE order_id=?s", $data["orderStatus"], $orderId);
      if($data["orderStatus"] != 2) {
        return $data["errorMessage"];
      }

      $sum = $data["amount"] / 100;
      if($sum != $sum_to_pay)
        return;

      /*$data = array(
        "orderId" => $orderId,
        "amount" => $sum * 100
      );*/
      $this->bookingInfo = $data;
      //$data = $this->deposit($orderId,$sum*100);

      //Запрос завершения заказа deposit.do
      $url = $this->bankInfo['link'].'/deposit.do?userName='.$this->bankInfo['userName'].'&password='.$this->bankInfo['password'].'&language=ru&orderId='.$orderId.'&amount='.($sum*100);
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $data = curl_exec($ch);
      curl_close($ch);  
      $data = json_decode($data, true);      

      $log = "action=deposit.do".PHP_EOL;
      $log .= print_r($data, true).PHP_EOL;
      file_put_contents('alfa_deposit_log_'.date('Y-m-d').'.txt', $log, FILE_APPEND);
      //Запрос завершения заказа deposit.do

      if($data["ErrorCode"] != 0)
        return $data["ErrorMessage"]." ".($sum * 100)." ".$orderId;

      if ($pay_method==6) $bank_com = $this->bankInfo["commission"];
      else $bank_com = $this->bankInfo["commission_qr"];

      $today = date("Y-m-d");

      

      if ($type_pay == 1) {

        $connect->query("INSERT INTO payment(schet, date, created, processed, type, pay_method, sum, bank_com) VALUES (?i, ?s, ?i, ?i, 2, ?s, ?s, ?s)", $bid, $today, $timestamp, $timestamp, $pay_method, $sum, $bank_com);
        $connect->query("UPDATE reckoning SET status=5 WHERE id=?i LIMIT 1", $bid);
        $bonus = new Bonus();
        $bonus->create();
        unset($bonus);
        $this->saveNotification("Оплата картой №".$bid, $manager);
        $this->saveSchetToHistory($bid, "Оплата клиентом банковской картой. Сумма ".$sum);

        $log = "Оплата картой №".$bid.PHP_EOL;
        file_put_contents('alfa_deposit_log_'.date('Y-m-d').'.txt', $log, FILE_APPEND);        

      } elseif ($type_pay == 2) {

        $connect->query("INSERT INTO payment(schet, date, created, processed, type, pay_method, sum, bank_com) VALUES (?i, ?s, ?i, ?i, 1, ?s, ?s, ?s)", $bid, $today, $timestamp, $timestamp, $pay_method, $sum, $bank_com);
        $connect->query("UPDATE reckoning SET status=4 WHERE id=?i LIMIT 1", $bid);
        $this->saveNotification("Предоплата картой №".$bid, $manager);
        $this->saveSchetToHistory($bid, "Предоплата клиентом банковской картой. Сумма ".$sum);

        $log = "Предоплата картой №".$bid.PHP_EOL;
        file_put_contents('alfa_deposit_log_'.date('Y-m-d').'.txt', $log, FILE_APPEND);        

      }

      $connect->query("UPDATE payment_request SET status=2 WHERE order_id=?s", $orderId);
      $send = new \SendMailTurist;
      $send->notification_payment_booking();
      unset($send);

      return 1;
    }
    catch (ActionException $e) {
      return;
    }
  }

}