<?php

class DisplayBookingTurist extends DisplayBooking{

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->account = $config->account;
  }

  public function display_booking_turist(){
    $connect = $this->connect;
    $client = $this->account;
    $data = $connect->getAll("SELECT id, id_obj, DATE_FORMAT(date_z, '%d.%m.%Y') as arrival, DATE_FORMAT(date_v, '%d.%m.%Y') as leaving, sum, status FROM reckoning WHERE turist=?i AND active!=3 AND agency is NULL", $client);
    $array = $this->display_booking($data);
    return $array;
  }

}

?>
