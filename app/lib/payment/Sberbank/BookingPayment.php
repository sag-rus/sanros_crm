<?php
namespace App\lib\payment\Sberbank;

use App\lib\CRM\Client\Display;
use Voronkovich\SberbankAcquiring\Client;

class BookingPayment extends Client {
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

  public function __construct(array $settings = [])
  {
    $config = \App\lib\CRM\Config\Client::getInstance();
    $this->connect = $config->connect;
    $this->link = $config->clientCabinet["link"];
    $this->bankInfo = $config->onlinePaymentInfo;
    $this->booking = $config->booking;
    $this->turist = $config->account;

    if(!isset($settings['currency']))
      $settings['currency'] = 643;

    if(!isset($settings['language']))
      $settings['language'] = 'ru';

    if(!isset($settings['userName']))
      $settings['userName'] = $this->bankInfo['userName'];

    if(!isset($settings['password']))
      $settings['password'] = $this->bankInfo['password'];

    if(!isset($settings['apiUri']))
      $settings['apiUri'] = self::API_URI;

    parent::__construct($settings);
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
          $max_bonus = $this->checkBonusPaymentBankCard();
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

  protected function saveSchetToHistory($id, $note = ""){
    global $session_login;
    $row = $this->connect->getRow("SELECT status, status_san FROM reckoning WHERE id=?i", $id);
    $new_status = $row['status'];
    $new_status_san = $row['status_san'];
    $today = date("Y-m-d");
    $time = date("H:i:s");
    $this->connect->query("INSERT INTO history_schet(date, time, id_schet, id_user, new_status, new_status_san, note) VALUES(?s, ?s, ?i, ?i, ?i, ?i, ?s)", $today, $time, $id, $session_login, $new_status, $new_status_san, $note);
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
      $array = $connect->getRow("SELECT id, sum, id_obj FROM reckoning WHERE id=?i AND turist=?i AND (status=3 OR status=4)", $booking, $client);
      $answer["id"] = $array["id"];
      $answer["check"] = 1;

      $answer["all_sum"] = add_null($sum["total"]);
      $answer["sum"] = add_null($sum["to-pay"]);
      $answer["bonus"] = add_null($sum["bonus"]);
      $answer["prepay"] = add_null($sum["prepay"]);
      $turist = new Display($client);
      $answer["turist"] = $turist->selectFio();
      unset($turist);
      $answer["product"] = "Оплата путевки по заявке №".$booking." (".self::getObject($connect, $array["id_obj"], "type").")";
    }
    else {
      $answer['msg'] = 'Pay sum is not correct';
      $answer['data_dump'] = $sum;
      $answer['connect_dump'] = is_object($connect);
    }
    return $answer;

  }

  public function registerPayment($type = ""){
    $connect = $this->connect;
    $booking = $this->booking;
    $turist = $this->turist;

    $sum = $connect->getOne("SELECT sum FROM reckoning WHERE id=?i AND (status=3 OR status=4) AND turist=?i", $booking, $turist);
    if($sum > 0){
      $count = (int)$connect->getOne("SELECT count_payment FROM reckoning WHERE id=?i", $booking) + 1;
      $connect->query("UPDATE reckoning SET count_payment=?i WHERE id=?i", $count, $booking);
      $bonus = abs($connect->getOne("SELECT sum FROM bonus WHERE sum<0 AND schet=?i", $booking));
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
      $returnUrl = $link."core/payment/return-payment-sberbank.php?id=".$orderNumber;
      //$failUrl = $link."core/payment/fault-payment.php?id=".$booking."&bank=sber";

      $description = "Оплата путевки по заявке №".$booking." (".self::getObject($connect, $object, "type").")";

      $answer = $this->registerOrder($orderNumber,$sum_to_pay*100,$returnUrl,[
        "description" => $description
      ]);

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

}