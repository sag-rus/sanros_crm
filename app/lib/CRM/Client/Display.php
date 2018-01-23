<?php
namespace App\lib\CRM\Client;

use App\lib\CRM\Config\Client;

class Display {
  private $connect;
  private $client;


  public static function dateChange($date, $separator = "-", $divider = "-"){
    if($date != "0000-00-00" AND $date != ""){
      $d = explode($divider, $date);
      $date = $d[2].$separator.$d[1].$separator.$d[0];
      return $date;
    }else
      return FALSE;
  }

  public function __construct($client = ""){
    $config = Client::getInstance();
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

  public function selectFio(){
    $connect = $this->connect;
    $client = $this->client;
    $row = $connect->getRow("SELECT id, surname, name, otch FROM klient WHERE id=?i", $client);
    if(!$row["id"])
      return;
    $fio = $row["surname"]." ".$row["name"]." ".$row["otch"];
    return $fio;
  }

  public function selectFioArray(){
    $connect = $this->connect;
    $client = $this->client;
    $row = $connect->getRow("SELECT id, surname, name, otch FROM klient WHERE id=?i", $client);
    if(!$row["id"])
      return;
    return $row;
  }

  public function selectContact(){
    $connect = $this->connect;
    $client = $this->client;
    $row = $connect->getRow("SELECT id, telephone, email FROM klient WHERE id=?i", $client);
    if(!$row["id"])
      return;
    return $row;
  }

  public function selectInfo(){
    $connect = $this->connect;
    $client = $this->client;
    $row = $connect->getRow("SELECT id, date, address FROM klient WHERE id=?i", $client);
    if(!$row["id"])
      return;
    $row["date"] = self::dateChange($row["date"]);
    return $row;
  }
}