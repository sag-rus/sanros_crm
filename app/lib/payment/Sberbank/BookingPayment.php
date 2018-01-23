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

  public function __construct(array $settings)
  {
    $config = \App\lib\CRM\Config\Client::getInstance();
    $this->connect = $config->connect;
    $this->link = $config->clientCabinet["link"];
    $this->bankInfo = $config->onlinePaymentInfo;
    $this->booking = $config->booking;
    $this->turist = $config->account;
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
    return $answer;

  }

}