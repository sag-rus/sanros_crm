<?php
namespace App\lib\payment\Sberbank;

use App\lib\CRM\Client\Display;
use App\Model\Bonus;
use Voronkovich\SberbankAcquiring\Client;
use Voronkovich\SberbankAcquiring\Exception\ActionException;

class BookingPayment {
  protected $connect;
  protected $link;
  protected $booking;
  protected $type;
  protected $turist;
  protected $clients = [];

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
      "userName_v2",
      "userName_test",
      "password",
      "password_v2",
      "password_test",
      "link",
      "commission"
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

  public function getSberbankClient($type = null, $settings = array())
  {
      if(!$type) {
          $clientKey = 'default';
      }
      else {
          $clientKey = $type;
      }

      if(!array_key_exists($clientKey, $this->clients)) {
          if(!isset($settings['currency']))
              $settings['currency'] = 643;

          if(!isset($settings['language']))
              $settings['language'] = 'ru';

          if(!isset($settings['userName'])) {
              if($type)
                  $settings['userName'] = $this->bankInfo['userName_'.$type];
              else
                  $settings['userName'] = $this->bankInfo['userName'];
          }

          if(!isset($settings['password'])) {
              if($type)
                  $settings['password'] = $this->bankInfo['password_'.$type];
              else
                  $settings['password'] = $this->bankInfo['password'];

          }

          if(!isset($settings['apiUri'])) {
              if($type === 'test')
                  $settings['apiUri'] = Client::API_URI_TEST;
              else
                  $settings['apiUri'] = Client::API_URI;
          }

          $this->clients[$clientKey] =  new Client($settings);
      }

      return $this->clients[$clientKey];
  }

  public function __construct(array $settings = [])
  {
    $config = \App\lib\CRM\Config\Client::getInstance();
    $this->connect = $config->connect;
    $this->link = $config->clientCabinet["link"];
    $this->bankInfo = $config->onlinePaymentInfo;
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
    $reck = $connect->getRow("SELECT sum, id_com, correction, exclude_bank_commission FROM reckoning WHERE id=?i", $booking);
    $raz = 0;
    $all_bonus_count = $connect->getOne("SELECT COUNT(*) FROM `bonus` WHERE `turist` = ?i AND `sum` > 0",$this->turist);
    $reckonings_count = $connect->getOne("SELECT COUNT(*) FROM `reckoning` WHERE `turist` = ?i AND `status` = 5", $this->turist);

    if($all_bonus_count > 1 && $reckonings_count > 0 && !$reck['exclude_bank_commission']) {
      $bank_com = 0;
    }
    else $bank_com = ($bank_com * $reck["sum"]) / 100;

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

  public function checkHolding()
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
    $reck_properties = $connect->getRow("SELECT sum, id_dis FROM reckoning WHERE id=?i AND turist=?i AND (status=1 OR status=2) LIMIT 1", $booking, $turist);
    $answer["total"] = $reck_properties['sum'];
    if($answer["total"] > 0){

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
        $answer["to-pay"] = $answer["total"] - $answer["bonus"] - $answer["prepay"]-$dis;
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


  public function showHoldingCard($type)
  {
    $connect = $this->connect;
    $booking = $this->booking;
    $client = $this->turist;
    $this->type = $type;
    $answer = array();
    $sum = $this->checkHolding();
    if($sum["to-pay"] > 0){
      $array = $connect->getRow("SELECT id, sum, id_obj FROM reckoning WHERE id=?i AND turist=?i AND (status=1 OR status=2)", $booking, $client);
      $answer["id"] = $array["id"];
      $answer["check"] = 1;

      $answer["all_sum"] = add_null($sum["total"]);
      $answer["sum"] = add_null($sum["to-pay"]);
      $answer["bonus"] = add_null($sum["bonus"]);
      $answer["prepay"] = add_null($sum["prepay"]);
      $answer['fast_booking'] = $connect->getOne("SELECT fast_booking FROM object WHERE id = ?i",$array["id_obj"]);
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

  public function registerPayment($type = ""){
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;

    $reck_properties = $connect->getRow("SELECT sum, id_dis, exclude_bank_commission, is_test, state_program FROM reckoning WHERE id=?i AND turist=?i AND (status=3 OR status=4) LIMIT 1", $booking, $turist);

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
        $com = $this->bankInfo['commission'] / 100;
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
      $returnUrl = $link."core/payment/return-payment-sberbank.php?id=".$orderNumber;
      //$failUrl = $link."core/payment/fault-payment.php?id=".$booking."&bank=sber";

      $description = "Оплата путевки по заявке №".$booking." (".self::getObject($connect, $object, "type").")";

      if($reck_properties['is_test']) {
          $sberbankClient = $this->getSberbankClient('test');
      }
      else {
          $sberbankClient = $this->getSberbankClient();
      }

      $answer = $sberbankClient->registerOrderPreAuth($orderNumber,$sum_to_pay*100,$returnUrl,[
        "description" => $description
      ]);

      //$this->

      if($answer["orderId"]){
        $check = $connect->getOne("SELECT id FROM payment_request WHERE order_id=?s", $answer["orderId"]);
        if(!$check){
          $bank_com = $this->bankInfo["commission"];
          $connect->query("INSERT INTO payment_request(bid, type, pay_method, sum, bank_com, order_id, bid_pay) VALUES (?i, ?i, 5, ?s, ?s, ?s, ?s)", $booking, $type_pay, $sum_to_pay, $bank_com, $answer["orderId"], $orderNumber);
        }
      }
      else {
          file_put_contents(__DIR__.'/../../../../core/sync/file/payment-log-'.date('Y-m-d').'.log', print_r($answer, true).PHP_EOL, FILE_APPEND);
      }
      return $answer;
    }
    return FALSE;
  }

  public function registerHolding(float $holding_sum){
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;

    $reck_properties = $connect->getRow("SELECT sum, id_dis, is_test, state_program FROM reckoning WHERE id=?i AND turist=?i AND (status=1 OR status=2) LIMIT 1", $booking, $turist);
    $sum = $reck_properties['sum'];

    if($sum > 0 && $holding_sum > 0){
      $count = (int)$connect->getOne("SELECT count_holding FROM reckoning WHERE id=?i", $booking) + 1;
      $connect->query("UPDATE reckoning SET count_holding=?i WHERE id=?i", $count, $booking);
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

      $type_pay = 2;
      $prepay = 0;

      $payment = $connect->getAll("SELECT sum FROM payment WHERE type=1 AND status = 1 AND schet=?i", $booking);

      foreach($payment as $pay)
        $prepay+= $pay["sum"];

      $sum_to_pay = $sum - $bonus - $prepay - $dis;

      if($holding_sum < $sum_to_pay) {
        if($holding_sum < 100)
          $sum_to_pay = 100;
        else
          $sum_to_pay = $holding_sum;
      }

      $object = $connect->getOne("SELECT id_obj FROM reckoning WHERE id=?i", $booking);
      $link = $this->link;
      $orderNumber = $booking."-holding-".$count;
      $returnUrl = $link."core/payment/return-holding-sberbank.php?id=".$orderNumber;
      //$failUrl = $link."core/payment/fault-payment.php?id=".$booking."&bank=sber";

      $description = "Заморозка средств по заявке №".$booking." (".self::getObject($connect, $object, "type").")";

        if($reck_properties['is_test']) {
            $sberbankClient = $this->getSberbankClient('test');
        }
        else {
            $sberbankClient = $this->getSberbankClient();
        }

      $answer = $sberbankClient->registerOrderPreAuth($orderNumber,$sum_to_pay*100,$returnUrl,[
        "description" => $description
      ]);

      //$this->

      if($answer["orderId"]){
        $check = $connect->getOne("SELECT id FROM payment_request WHERE order_id=?s", $answer["orderId"]);
        if(!$check){
          $bank_com = $this->bankInfo["commission"];
          $connect->query("INSERT INTO payment_request(bid, type, pay_method, sum, bank_com, order_id, bid_pay) VALUES (?i, ?i, 5, ?s, ?s, ?s, ?s)", $booking, $type_pay, $sum_to_pay, $bank_com, $answer["orderId"], $orderNumber);
        }
      }
      return $answer;
    }
    return FALSE;
  }

  protected function getPaymentStatus($type = null){
    $sberbankClient = $this->getSberbankClient($type);

    $answer = $sberbankClient->getOrderStatus($this->bookingInfo["orderId"]);
    return $answer;
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

  public function depositPayment($bid_pay = "", $holding = FALSE){
    try {
      $connect = $this->connect;

      $row = $connect->getRow("SELECT id, bid, sum, order_id, type FROM payment_request WHERE bid_pay=?s", $bid_pay);
      if(!$row["id"])
        return "Incorrect payment request";
      $bid = $row["bid"];
      $orderId = $row["order_id"];
      $sum_to_pay = $row["sum"];
      $request_id = $row['id'];
      $type_pay = $row["type"];
      $row = $connect->getRow("SELECT id_obj, id_user, date_v, turist, status, is_test, state_program FROM reckoning WHERE id=?i", $bid);

      $is_test = $row['is_test'];
      $state_program = $row['state_program'];

      $object = $row["id_obj"];
      $manager = $row["id_user"];
      $client = $row["turist"];
      $arrival = date_change($row["date_v"], ".");
      $timestamp = date("U");
      $reck_row_status = $row['status'];
      \App\lib\CRM\Config\Client::getInstance()->turist = $client;
      \App\lib\CRM\Config\Client::getInstance()->booking = $bid;

      $data = array(
        "orderId" => $orderId
      );
      $this->bookingInfo = $data;

      if($is_test) {
          $sberbankClient = $this->getSberbankClient('test');
          $data = $this->getPaymentStatus('test');
      }
      else {
          $sberbankClient = $this->getSberbankClient();
          $data = $this->getPaymentStatus();
      }

      $connect->query("UPDATE payment_request SET status=?i WHERE id=?i", (int)$data["OrderStatus"], $request_id);
      if($data["OrderStatus"] != 1) {
        return $data["ErrorMessage"]." OrderStatus = ".$data["OrderStatus"];
      }

      $sum = $data["Amount"] / 100;
      if($sum != $sum_to_pay) {
        if($holding)
          return "Holding sum is not equal to sum to holding";
        else
          return "Payment sum is not equal to sum to pay";
      }

      $data = array(
        "orderId" => $orderId,
        "amount" => $sum * 100
      );

      $this->bookingInfo = $data;

      if(!$holding)
        $data = $sberbankClient->deposit($orderId,$sum*100);

      if($data["ErrorCode"] != 0)
        return $data["ErrorMessage"]." ".($sum * 100)." ".$orderId;

      $bank_com = $this->bankInfo["commission"];
      $today = date("Y-m-d");

      if($holding) {
        if($type_pay == 1){
          $connect->query("INSERT INTO payment(schet, status, created, date, type, pay_method, request_id, sum, bank_com) VALUES (?i, ?i, ?i, ?s, 2, 5, ?i, ?s, ?s)", $bid, 1, $timestamp, $today, $request_id, $sum, $bank_com);
          $connect->query("UPDATE reckoning SET `holding`=1, `holding_sum` = `holding_sum` + ".((float)$sum)." WHERE id=?i LIMIT 1", $bid);
          $this->saveNotification("Заморозка средств картой №".$bid, $manager);
          $this->saveSchetToHistory($bid, "Заморозка средств клиентом банковской картой. Сумма ".$sum);

        }elseif($type_pay == 2){

          $connect->query("INSERT INTO payment(schet, status, created, date, type, pay_method, request_id, sum, bank_com) VALUES (?i, ?i, ?i, ?s, 1, 5, ?i, ?s, ?s)", $bid, 1, $timestamp, $today, $request_id, $sum, $bank_com);
          $connect->query("UPDATE reckoning SET `holding`=1, `holding_sum` = `holding_sum` + ".((float)$sum)." WHERE id=?i LIMIT 1", $bid);
          $this->saveNotification("Заморозка средств картой №".$bid, $manager);
          $this->saveSchetToHistory($bid, "Заморозка средств клиентом банковской картой. Сумма ".$sum);

        }
      }
      else {
        if($type_pay == 1){
          $type_pay_addit = 2;

          if($reck_row_status == 4)
            $type_pay_addit = 6;

          $connect->query("INSERT INTO payment(schet, date, created, processed, type, pay_method, request_id, sum, bank_com) VALUES (?i, ?s, ?i, ?i, $type_pay_addit, 5, ?i, ?s, ?s)", $bid, $today, $timestamp, $timestamp, $request_id, $sum, $bank_com);
          $connect->query("UPDATE reckoning SET status=5 WHERE id=?i LIMIT 1", $bid);
          $bonus = new Bonus();
          $bonus->create();
          unset($bonus);
          if($type_pay_addit == 6) {
            $this->saveNotification("Доплата картой №" . $bid, $manager);
            $this->saveSchetToHistory($bid, "Доплата клиентом банковской картой. Сумма " . $sum);
          }
          else {
            $this->saveNotification("Оплата картой №" . $bid, $manager);
            $this->saveSchetToHistory($bid, "Оплата клиентом банковской картой. Сумма " . $sum);
          }

        }elseif($type_pay == 2){

          $connect->query("INSERT INTO payment(schet, date, created, processed, type, pay_method, request_id, sum, bank_com) VALUES (?i, ?s, ?i, ?i, 1, 5, ?i, ?s, ?s)", $bid, $today, $timestamp, $timestamp, $request_id, $sum, $bank_com);
          $connect->query("UPDATE reckoning SET status=4 WHERE id=?i LIMIT 1", $bid);
          $this->saveNotification("Предоплата картой №".$bid, $manager);
          $this->saveSchetToHistory($bid, "Предоплата клиентом банковской картой. Сумма ".$sum);

        }
      }

      if($holding) {
        $send = new \SendMailTurist;
        $send->notification_holding_booking();
        unset($send);
      }
      else {
        $connect->query("UPDATE payment_request SET status=2 WHERE order_id=?s", $orderId);
        $send = new \SendMailTurist;
        $send->notification_payment_booking();
        unset($send);
      }

      return 1;
    }
    catch (ActionException $e) {
      return $e->getMessage();
    }
  }

  public function cancelPayment(int $id) {
    $connect = $this->connect;
    $timestamp = date("U");
    $responseAr = [
      'success' => 0,
      'msg' => '',
      'error_code' => 0
    ];

    if($id > 0) {
      $payment = $connect->getRow("SELECT `id`, `request_id`, `schet`, `sum`  FROM payment WHERE id = ?i AND status = 1", $id);
      if($payment) {
        $reck_id = $payment['schet'];
        $reckoning = $connect->getRow("SELECT id, turist, is_test, state_program FROM reckoning WHERE id = ?i", $reck_id);
        $config = \App\lib\CRM\Config\Client::getInstance();
        $config->booking = $reck_id;
        $config->turist = $reckoning['turist'];
        $config->connect = $this->connect;
        if($payment['request_id']) {
          $request = $connect->getRow("SELECT id, order_id, bid_pay FROM payment_request WHERE id = ?i",$payment['request_id']);
          if($request) {
            try {
                if($reckoning['is_test'])
                    $sberbankClient = $this->getSberbankClient('test');
                else
                    $sberbankClient = $this->getSberbankClient();


                $response = $sberbankClient->reverseOrder($request['order_id']);
              $connect->query("UPDATE payment_request SET status = ?i WHERE id = ?i AND status = 1",0,$payment['request_id']);
              $connect->query("UPDATE payment SET status = ?i, processed = ?i WHERE id = ?i AND status = 1",0,$timestamp,$id);
              $connect->query("UPDATE reckoning SET `holding_sum` = `holding_sum`-".$payment['sum'].", `holding_cancelled_sum` = `holding_cancelled_sum` + ".$payment['sum']." WHERE id = ?i",$reck_id);
              $this->saveSchetToHistory($reck_id, "Отмена заморозки средств по заявке. Сумма ".$payment['sum']);
              $responseAr['msg'] = 'Платеж успешно отменен';
              $responseAr['success'] = 1;
              if($reckoning && $reckoning['turist']) {
                $x = new \SendMailTurist();
                $x->notification_holding_cancel($payment['sum'],$request['bid_pay']);
              }
            }
            catch (\Exception $e) {
              $responseAr['msg'] = $e->getMessage();
              $responseAr['error_code'] = $e->getCode();
            }
          }
          else {
            $responseAr['msg'] = 'Не найден запрос на оплату';
          }
        }
        else {
          $responseAr['msg'] = 'Отсутствует запрос на оплату';
        }
      }
      else {
        $responseAr['msg'] = 'Не найден платеж с таким ID';
      }
    }

    return $responseAr;
  }

  public function confirmPayment(int $id) {
    $connect = $this->connect;
    $timestamp = date("U");
    $responseAr = [
      'success' => 0,
      'msg' => '',
      'error_code' => 0
    ];
    if($id > 0) {
      $payment = $connect->getRow("SELECT `id`, `request_id`, `schet`, `sum`, `created`  FROM payment WHERE id = ?i AND status = 1", $id);
      if ($payment) {
        $reck_id = $payment['schet'];
        $reckoning = $connect->getRow("SELECT `id`, `turist`, `sum`, `is_test`, `state_program` FROM reckoning WHERE id = ?i", $reck_id);
        $config = \App\lib\CRM\Config\Client::getInstance();
        $config->booking = $reck_id;
        $config->turist = $reckoning['turist'];
        $config->connect = $this->connect;
        if($reckoning) {
          if($reckoning['sum'] > 0) {
            if($payment['request_id']) {
              $older_holding = $connect->getRow("SELECT id FROM `payment` WHERE `id` < ?i AND `status` = 1 AND `schet` = ?i LIMIT 1",$payment['id'], $reck_id);
              if(!$older_holding) {
                $request = $connect->getRow("SELECT id, order_id, bid_pay FROM payment_request WHERE id = ?i",$payment['request_id']);
                if($request) {
                  $type = 1;
                  $reck_new_status = 4;
                  $bonus = abs($connect->getOne("SELECT `sum` FROM bonus WHERE sum<0 AND schet=?i", $reck_id));
                  if($bonus > 0){
                    $max_bonus = $this->checkBonusPaymentBankCard();
                    if($bonus > $max_bonus){
                      if($max_bonus == 0)
                        $connect->query("DELETE FROM bonus WHERE sum<0 AND schet=?i", $reck_id);
                      else
                        $connect->query("UPDATE bonus SET sum=?s WHERE sum<0 AND schet=?i", $max_bonus * (-1), $reck_id);
                      $this->saveSchetToHistory($reck_id, "Корректировка суммы бонусов");
                      $bonus = $max_bonus;
                    }
                  }

                  $prepay = 0;
                  $old_payments = $connect->getAll("SELECT `sum` FROM payment WHERE `status` = 2 AND schet=?i", $reck_id);

                  foreach($old_payments as $old_payment)
                    $prepay+= $old_payment["sum"];

                  $sum_to_pay = (float)($reckoning['sum'] - $bonus - $prepay);
                  $payment_sum = (float)$payment['sum'];
                  if($sum_to_pay > 0) {
                    if($sum_to_pay === $payment_sum) {
                      $reck_new_status = 5;
                      if($prepay) {
                        $type = 6;
                      }
                      else {
                        $type = 2;
                      }
                    }

                    if ($sum_to_pay < $payment_sum) {
                      $responseAr['msg'] = 'Платеж больше, чем нужно в заявке';
                    }
                    else {
                      try {
                         if($reckoning['is_test']) {
                             $sberbankClient = $this->getSberbankClient('test');
                         }
                         else {
                             $sberbankClient = $this->getSberbankClient();
                         }

                        $response = $sberbankClient->deposit($request['order_id'],$payment_sum*100);
                        $this->saveSchetToHistory($reck_id, "Принятие замороженных клиентом средств в качестве платежа. Сумма ".$payment_sum);
                        if($reck_new_status == 4)
                          $connect->query("UPDATE `reckoning` SET `status` = ?i, `holding_sum` = `holding_sum` - ".$payment_sum.", `holding_confirmed_sum` = `holding_confirmed_sum` + ".$payment_sum.", `prepay` = `prepay` + ".$payment_sum." WHERE id = ?i",$reck_new_status, $reck_id);
                        else
                          $connect->query("UPDATE `reckoning` SET `status` = ?i, `holding_sum` = `holding_sum` - ".$payment_sum.", `holding_confirmed_sum` = `holding_confirmed_sum` + ".$payment_sum." WHERE id = ?i",$reck_new_status, $reck_id);

                        $connect->query("UPDATE payment_request SET status = ?i WHERE id = ?i AND status = 1",2,$payment['request_id']);
                        $connect->query("UPDATE payment SET `status` = ?i, `type` = ?i, `processed` = ?i WHERE id = ?i AND status = 1",2,$type, $timestamp,$payment['id']);

                        $bonus = new Bonus();
                        $bonus->create();
                        unset($bonus);

                        $x = new \SendMailTurist();
                        $x->notification_holding_confirm();

                        $responseAr['msg'] = 'Платеж успешно принят!';
                        $responseAr['reck_id'] = $reck_id;
                        $responseAr['success'] = 1;
                      }
                      catch (\Exception $e) {
                        $responseAr['msg'] = $e->getMessage();
                        $responseAr['error_code'] = $e->getCode();
                      }
                    }
                  }
                  else {
                    $responseAr['msg'] = 'По данной заявке не требуются платежи';
                  }
                }
                else {
                  $responseAr['msg'] = 'Не найден запрос на оплату';
                }
              }
              else {
                $responseAr['msg'] = 'Сначала обработайте предыдущие платежи';
              }
            }
            else {
              $responseAr['msg'] = 'Отсутствует запрос на оплату';
            }
          }
          else {
            $responseAr['msg'] = 'Не указана сумма заявки';
          }
        }
        else {
          $responseAr['msg'] = 'Не найдена заявка';
        }
      }
      else {
        $responseAr['msg'] = 'Не найден платеж с таким ID';
      }
    }
    return $responseAr;
  }
}