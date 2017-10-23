<?php

class FuncClient{

  private $connect;
  private $turist;

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->turist = $config->turist;
  }

  public function save_history_client($note){
    $connect = $this->connect;
    $turist = $this->turist;
    $connect->query("INSERT INTO history_client(client, note) VALUES(?i, ?s)", $turist, $note);
  }

}

?>
