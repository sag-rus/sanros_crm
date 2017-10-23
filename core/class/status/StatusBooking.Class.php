<?php

class StatusBooking{

  public static function select_status_name($status){
    $config = ConfigCRM::getInstance();
    $connect = $config->connect;
    $name = $connect->getOne("SELECT name FROM status WHERE id=?i", $status);
    return $name;
  }

}

?>
