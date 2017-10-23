<?php

class EditClient{

  private $connect;
  private $id;

  public function __construct($id){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->id = $id;
  }

  public function update_client(array $data){
    $connect = $this->connect;
    $id = $this->id;
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
    $connect->query("UPDATE klient SET surname=?s, name=?s, otch=?s WHERE id=?i", $surname, $name, $otch, $id);
  }

}

?>
