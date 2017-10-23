<?php

class DisplayClient{

  private $connect;
  private $client;

  public function __construct($client = ""){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    if(!$client){
      $this->client = $config->account;
    }else{
      $this->client = $client;
    }
  }

  public function select_surname(){
    $connect = $this->connect;
    $client = $this->client;
    $surname = $connect->getOne("SELECT surname FROM klient WHERE id=?i", $client);
    return $surname;
  }

  public function select_fio(){
    $connect = $this->connect;
    $client = $this->client;
    $row = $connect->getRow("SELECT id, surname, name, otch FROM klient WHERE id=?i", $client);
    if(!$row["id"])
      return;
    $fio = $row["surname"]." ".$row["name"]." ".$row["otch"];
    return $fio;
  }

  public function select_fio_array(){
    $connect = $this->connect;
    $client = $this->client;
    $row = $connect->getRow("SELECT id, surname, name, otch FROM klient WHERE id=?i", $client);
    if(!$row["id"])
      return;
    return $row;
  }

  public function select_contact(){
    $connect = $this->connect;
    $client = $this->client;
    $row = $connect->getRow("SELECT id, telephone, email FROM klient WHERE id=?i", $client);
    if(!$row["id"])
      return;
    return $row;
  }

  public function select_info(){
    $connect = $this->connect;
    $client = $this->client;
    $row = $connect->getRow("SELECT id, date, address FROM klient WHERE id=?i", $client);
    if(!$row["id"])
      return;
    $row["date"] = date_change($row["date"]);
    return $row;
  }

}

?>
