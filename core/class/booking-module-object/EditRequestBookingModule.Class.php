<?php

class EditRequestBookingModule extends CreateRequestBookingModule{

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->object = $config->object;
    $this->booking = $config->booking;
  }

  public function add_new_position($data){
    $booking = $this->booking;
    $object = $this->object;
    $connect = $this->connect;
    if($this->check_edit_booking()){
      $arrival = date_change($data["arrival"], "-", ".");
      $days = $data["days"];
      $position = array(
        "id" => $data["room"],
        "price" => $data["price"],
        "type" => $data["type"],
        "number" => $data["number"],
        "place" => $data["note"]
      );
      $this->create_position_booking($position, $arrival, $days);

      $this->calculation_sum_booking();
      $this->calculation_arrival_date();
      $this->update_quota_booking();
      $this->save_history_booking("Добавление новой позиции");
    }
  }

  public function update_position($data){
    $booking = $this->booking;
    $object = $this->object;
    $connect = $this->connect;
    if($this->check_edit_booking()){
      $position = $connect->getOne("SELECT id FROM booking_request_object_module_position WHERE id=?i AND booking=?i", $data["position"], $booking);
      if(!$position)
        return;
      $arrival = date_change($data["arrival"], "-", ".");
      $days = $data["days"];
      $connect->query("UPDATE booking_request_object_module_position SET arrival=?s, days=?i, room=?i, sum=?s, type=?i, number=?i, note=?s WHERE id=?i", $arrival, $days, $data["room"], $data["price"], $data["type"], $data["number"], $data["note"], $position);
      $this->calculation_sum_booking();
      $this->calculation_arrival_date();
      $this->update_quota_booking();
      $this->save_history_booking("Изменение позиции");
    }
  }

  public function delete_position($data){
    $booking = $this->booking;
    $object = $this->object;
    $connect = $this->connect;
    if($this->check_edit_booking()){
      $count = $connect->getOne("SELECT COUNT(*) FROM booking_request_object_module_position WHERE booking=?i", $booking);
      if($count <= 1)
        return;
      $position = $connect->getOne("SELECT id FROM booking_request_object_module_position WHERE id=?i AND booking=?i", $data["position"], $booking);
      if(!$position)
        return;
      $connect->query("DELETE FROM booking_request_object_module_position WHERE id=?i AND booking=?i", $position, $booking);
      $this->calculation_sum_booking();
      $this->calculation_arrival_date();
      $this->update_quota_booking();
      $this->save_history_booking("Удаление позиции");
    }
  }

  public function add_new_turist($data){
    $booking = $this->booking;
    $object = $this->object;
    $connect = $this->connect;
    if($this->check_edit_booking()){
      $rest = json_decode($connect->getOne("SELECT rest FROM booking_request_object_module WHERE id=?i", $booking), TRUE);
      $client_info = array(
        "surname" => $data["surname"],
        "name" => $data["name"],
        "otch" => $data["otch"]
      );
      $create_client = new CreateClient;
      $turist = $create_client->create_client($client_info);
      $rest[] = $turist;
      $connect->query("UPDATE booking_request_object_module SET rest=?s WHERE id=?i", json_encode($rest), $booking);
      unset($create_client);
    }
  }

  public function update_turist($data){
    $booking = $this->booking;
    $object = $this->object;
    $connect = $this->connect;
    if($this->check_edit_booking()){
      $rest = json_decode($connect->getOne("SELECT rest FROM booking_request_object_module WHERE id=?i", $booking), TRUE);
      $check = $connect->getOne("SELECT turist FROM booking_request_object_module WHERE id=?i", $booking);
      if(in_array($data["turist"], $rest) AND $check != $data["turist"]){
        $client_info = array(
          "surname" => $data["surname"],
          "name" => $data["name"],
          "otch" => $data["otch"]
        );
        $update = new EditClient($data["turist"]);
        $turist = $update->update_client($client_info);
        unset($update);
      }
    }
  }

  public function delete_turist($data){
    $booking = $this->booking;
    $object = $this->object;
    $connect = $this->connect;
    if($this->check_edit_booking()){
      $rest = json_decode($connect->getOne("SELECT rest FROM booking_request_object_module WHERE id=?i", $booking), TRUE);
      $key = array_search($data["turist"], $rest);
      if(in_array($data["turist"], $rest) AND count($rest) > 1){
        unset($rest[$key]);
        $connect->query("UPDATE booking_request_object_module SET rest=?s WHERE id=?i", json_encode($rest), $booking);
      }
    }
  }

}

?>
