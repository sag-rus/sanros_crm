<?php

class CreateClient{

  private $connect;

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
  }

  public function create_client($data){
    $connect = $this->connect;
    $surname = first_symbol_to_title($data["surname"]);
    $name = first_symbol_to_title($data["name"]);
    $otch = first_symbol_to_title($data["otch"]);
    $email = "";
    $telephone = "";
    $address = "";
    if(isset($data["email"]))
      $email = clear_email($data["email"]);
    if(isset($data["telephone"]))
      $telephone = clear_telephone($data["telephone"]);
    if(isset($data["ip"]))
      $address = get_address_by_ip($data["ip"]);
    if($telephone){
      if($email == ""){
        $client = $connect->getOne("SELECT id FROM klient WHERE surname=?s AND name=?s AND telephone=?s", $surname, $name, $telephone);
      }elseif($otch == ""){
        $client = $connect->getOne("SELECT id FROM klient WHERE surname=?s AND name=?s AND (email='' OR email=?s) AND telephone=?s", $surname, $name, $email, $telephone);
      }else{
        $client = $connect->getOne("SELECT id FROM klient WHERE surname=?s AND name=?s AND (otch='' OR otch IS NULL OR otch=?s) AND (email='' OR email IS NULL OR email=?s) AND telephone=?s", $surname, $name, $otch, $email, $telephone);
      }
      if($client){
        $row = $connect->getRow("SELECT email, otch FROM klient WHERE id=?i", $client);
        if($otch != "" AND !$row["otch"]){
          $connect->query("UPDATE klient SET otch=?s WHERE id=?i", $otch, $client);
        }
        if($email != "" AND !$row["email"]){
          $connect->query("UPDATE klient SET email=?s WHERE id=?i", $email, $client);
        }
        return $client;
      }
    }
    $connect->query("INSERT INTO klient(surname, name, otch, telephone, email) VALUES (?s, ?s, ?s, ?s, ?s)", $surname, $name, $otch, $telephone, $email);
    $insertId = $connect->insertId();
    return $insertId;
  }

}

?>
