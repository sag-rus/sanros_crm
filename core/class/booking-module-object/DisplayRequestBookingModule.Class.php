<?php

class DisplayRequestBookingModule extends RequestBookingModule{

  private $typeAuth;

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->object = $config->object;
    $this->booking = $config->booking;
    $this->typeAuth = $config->typeAuth;
  }

  public function select_requests_booking(){
    $connect = $this->connect;
    $object = $this->object;
    $typeAuth = $this->typeAuth;
    $source = $this->source;
    $status = StatusBookingModuleObject::select_status();
    $requests = array();
    $data = array();
    $query = "SELECT id, date, DATE_FORMAT(arrival, '%d.%m.%Y') as arrival, status, turist, sum, object, quota, source FROM booking_request_object_module";
    if($typeAuth == "object"){
      $data = $connect->getAll($query." WHERE object=?i", $object);
    }elseif($typeAuth == "turist"){
      $config = ConfigCRM::getInstance();
      $account = $config->account;
      $data = $connect->getAll($query." WHERE turist=?i", $account);
    }
    foreach($data as $row){
      $id = $row["id"];
      $request = array();
      $request["date"] = $row["date"];
      $request["arrival"] = $row["arrival"];
      $request["status"] = $row["status"];
      $request["status-name"] = $status[$row["status"]];
      $request["object"] = get_object($connect, $row["object"], "type");
      $request["sum"] = $row["sum"];
      $request["source"] = $source[$this->source_default];
      if(isset($source[$row["source"]])){
        $request["source"] = $source[$row["source"]];
      }
      $request["notice"] = 0;
      if($row["status"] == 1){
        if($row["quota"] == 1){
          $request["notice"] = 1;
        }
        if($row["quota"] != 2 AND $connect->getOne("SELECT id FROM payment_request WHERE booking=?i AND status=1", $id)){
          $request["notice"] = 2;
        }
      }
      $turist = new DisplayClient($row["turist"]);
      $request["turist"] = $turist->select_surname();
      unset($turist);
      $requests[$id] = $request;
    }
    return $requests;
  }

  public function select_request_booking(){
    $connect = $this->connect;
    $object = $this->object;
    $booking = $this->booking;
    $typeAuth = $this->typeAuth;
    $request = array(
      "positions" => array(),
      "turist" => array(),
      "contact" => array()
    );
    $status = StatusBookingModuleObject::select_status();
    $query = "SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date, DATE_FORMAT(arrival, '%d.%m.%Y') as arrival, DATE_FORMAT(leaving, '%d.%m.%Y') as leaving, status, turist, sum, rest, description, object, payment_method, quota FROM booking_request_object_module WHERE id=?i";
    if($typeAuth == "object"){
      $row = $connect->getRow($query." AND object=?i", $booking, $object);
    }elseif($typeAuth == "turist"){
      $config = ConfigCRM::getInstance();
      $turist = $config->turist;
      $row = $connect->getRow($query." AND turist=?i", $booking, $turist);
    }
    if(!$row["id"])
      return;
    $booking = $row["id"];
    $request["id"] = $row["id"];
    $request["date"] = $row["date"];
    $request["object"] = get_object($connect, $row["object"], "place");
    $request["id-object"] = $row["object"];
    $request["arrival"] = $row["arrival"];
    $request["leaving"] = $row["leaving"];
    $request["status"] = $row["status"];
    $request["status-name"] = $status[$row["status"]];
    $request["description"] = $row["description"];
    $request["payment-method"] = $row["payment_method"];
    $request["quota"] = $row["quota"];
    $request["prepay-percent"] = $connect->getOne("SELECT prepay FROM booking_module_object WHERE object=?i", $row["object"]);
    $client = $row["turist"];
    $request["sum"] = $row["sum"];
    $rest = json_decode($row["rest"], TRUE);
    $data = $connect->getAll("SELECT id, DATE_FORMAT(arrival, '%d.%m.%Y') as arrival, days, room, sum, type, number, note FROM booking_request_object_module_position WHERE booking=?i", $booking);
    foreach($data as $row){
      $position = array();
      $position["arrival"] = $row["arrival"];
      $position["days"] = $row["days"];
      $position["id-room"] = $row["room"];
      $position["room"] = get_room($connect, $row["room"], "full");
      $position["sum"] = $row["sum"];
      $position["type"] = $row["type"];
      $position["number"] = $row["number"];
      $position["note"] = $row["note"];
      $request["positions"][$row["id"]] = $position;
    }
    foreach($rest as $turist){
      $show_client = new DisplayClient($turist);
      $fio = $show_client->select_fio_array();
      if(isset($fio["id"])){
        $request["turist"][$turist] = array();
        $request["turist"][$turist]["surname"] = $fio["surname"];
        $request["turist"][$turist]["name"] = $fio["name"];
        $request["turist"][$turist]["otch"] = $fio["otch"];
        $request["turist"][$turist]["edit"] = 1;
        if($turist == $client)
          $request["turist"][$turist]["edit"] = 0;
      }
      unset($show_client);
    }
    $show_client = new DisplayClient($client);
    $request["contact"] = $show_client->select_contact();
    $request["contact"]["fio"] = $show_client->select_fio();
    unset($show_client);
    $payment = new PaymentRequestBookingModule;
    $request["payment"] = $payment->display_payment();
    $request["sum-to-pay"] = $payment->select_sum_pay();
    $request["request-pay"] = $payment->select_request_payment();
    unset($payment);
    $request["check-edit"] = 0;
    if($this->check_edit_booking()){
      $request["check-edit"] = 1;
    }
    unset($func_booking);
    return $request;
  }

  public function select_info_module(){
    $connect = $this->connect;
    $object = $this->object;
    $row = $connect->getRow("SELECT website, email, telephone FROM booking_module_object WHERE object=?i", $object);
    return $row;
  }

}

?>
