<?php

class CreateRequestBookingModule extends RequestBookingModule{

  public function __construct($module){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->object = $this->select_id_module($module);
  }

  private function select_id_module($module){
    $connect = $this->connect;
    $object = $connect->getOne("SELECT object FROM booking_module_object WHERE uniq_key=?s", $module);
    return $object;
  }

  public function create_new_booking($data){
    $connect = $this->connect;
    $object = $this->object;

    if($object){

      $source = $this->source;

      $rest = array();

      $turist_info = array(
        "surname" => $data["turist"]["surname"],
        "name" => $data["turist"]["name"],
        "otch" => $data["turist"]["otch"],
        "telephone" => $data["turist"]["telephone"],
        "email" => $data["turist"]["email"]
      );

      $today = date("Y-m-d");
      $description = $data["note"];
      $payment_method = (int)$data["payment-method"];
      if($payment_method > 5 OR $payment_method < 1){
        $payment_method = 1;
      }
      $quota = (int)$data["quota"];
      if($quota != 1 AND $quota != 0){
        $quota = 0;
      }
      $turist = $data["turist"];
      $arrival = date_change($data["arrival"], "-", ".");
      $days = $data["days"];
      $create_turist = new CreateClient();
      $turist = $create_turist->create_client($turist_info);
      $rest[1] = $turist;
      unset($create_turist);

      $source_booking = $this->source_default;
      if(isset($data["source"]) AND isset($source[$data["source"]])){
        $source_booking = $data["source"];
      }

      $count = ($connect->getOne("SELECT COUNT(*) FROM booking_request_object_module WHERE object=?i", $object)) + 1;
      $connect->query("INSERT INTO booking_request_object_module(count, object, turist, rest, date, description, payment_method, quota, source) VALUES (?i, ?i, ?i, ?s, ?s, ?s, ?i, ?i, ?s)", $count, $object, $turist, json_encode($rest), $today, $description, $payment_method, $quota, $source_booking);
      $this->booking = $connect->insertId();
      $config = ConfigCRM::getInstance();
      $config->booking = $this->booking;
      $config->turist = $turist;

      if($quota == 1){
        $connect->query("INSERT INTO booking(booking_object) VALUES (?i)", $this->booking);
      }

      foreach($data["positions"] as $position){
        $this->create_position_booking($position, $arrival, $days);
      }
      $this->calculation_sum_booking();
      $this->calculation_arrival_date();
      $this->save_history_booking("Новая заявка с сайта");

      $send = new SendMailTuristModule;
      $send->send_login();
      unset($send);

      $send = new SendMailObjectModule;
      $send->notification_new_booking();
      unset($send);
    }

    //CREATE TABLE `comparison_module_object` (   `id` int(11) NOT NULL AUTO_INCREMENT,  `object` int(11) ,  `date_create` date, competitor text, default_room int(11), validity_date date, rate int(1) default 1, update_info int(1) default 0, PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8

    //CREATE TABLE `booking_module_object` (   `id` int(11) NOT NULL AUTO_INCREMENT,  `object` int(11) ,   `uniq_key` varchar(100) ,   `website` varchar(100),   `payment_methods` varchar(255),   `show_rooms` int(1) DEFAULT '1',   `email` varchar(30) ,   `telephone` varchar(30) ,   `date_create` date, prepay int(2) default 0,   PRIMARY KEY (`id`),   UNIQUE KEY `uniq_key` (`uniq_key`) )DEFAULT CHARSET=utf8

    //CREATE TABLE booking_request_object_module( id int(11) not null auto_increment, count int(11), date date, object int(11), arrival date, leaving date, status int(2) default 1, turist int(11), rest text, sum decimal(8,2), commission decimal(4,2) default 0, payment_method int(1) default 1, quota int(1) default 0, description text, count_payment int(3) default 1, source varchar(100) default 'official', primary key(id) ) DEFAULT CHARSET=utf8

    //CREATE TABLE booking_request_object_module_position( id int(11) not null auto_increment, booking int(11), arrival date, days int(2), room int(11), sum decimal(8,2), type int(1) default 1, number int(2) default 1, note varchar(255), primary key(id) ) DEFAULT CHARSET=utf8

    //CREATE TABLE booking_request_object_module_history( id int(11) not null auto_increment, booking int(11), status int(2), text text, time timestamp default CURRENT_TIMESTAMP, primary key(id) ) DEFAULT CHARSET=utf8

    //CREATE TABLE booking_request_object_module_payment( id int(11) not null auto_increment, booking int(11), sum decimal(8,2), bank_com decimal(4,2), time timestamp DEFAULT CURRENT_TIMESTAMP, method varchar(20), primary key(id) ) DEFAULT CHARSET=utf8
  }

  protected function create_position_booking($data, $arrival, $days){
    $connect = $this->connect;
    $booking = $this->booking;
    $type = 1;
    if(isset($data["type"]) AND $data["type"] > 1){
      $type = (int)$data["type"];
      if($type == 3)
        $type = 2;
    }
    $place = "";
    if(isset($data["place"])){
      $place = $data["place"];
    }
    $number = 1;
    if(isset($data["number"]) AND $data["number"] > 1)
      $number = $data["number"];
    $connect->query("INSERT INTO booking_request_object_module_position(booking, arrival, days, room, sum, type, number, note) VALUES (?i, ?s, ?s, ?i, ?s, ?i, ?i, ?s)", $booking, $arrival, $days, $data["id"], $data["price"], $type, $number, $place);
  }

}

?>
