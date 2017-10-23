<?php

class ObjectAccount{

  private $connect;

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
  }

  public function history_object_account($id){
    $connect = $this->connect;
    $data = $connect->getAll("SELECT DATE_FORMAT(time, '%H:%m:%s %d.%m.%Y') as date, text FROM history_object WHERE object=?i ORDER BY id DESC LIMIT 20", $id);
    return $data;
  }

}

?>
