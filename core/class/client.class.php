<?php

class client_info{

  protected $connect;
  protected $id;

  function __construct(SafeMySQL $connect, $id){
    $this->connect = $connect;
    $this->id = $id;
  }

  function get_name(){
    $connect = $this-connect;
    $row = $connect->getRow("SELECT name, surname, otch, note, service_note, photo FROM klient WHERE id=?i", $this->id);
    return $row;
  }

  function get_contact(){
    $connect = $this-connect;
    $row = $connect->getRow("SELECT email, telephone, address FROM klient WHERE id=?i", $this->id);
    return $row;
  }

  function get_passport(){
    $connect = $this-connect;
    $row = $connect->getRow("SELECT passport, output, DATE_FORMAT(date_pas, '%d.%m.%Y') as date_pas FROM klient WHERE id=?i", $this->id);
    return $row;
  }

  function get_login(){
    $connect = $this-connect;
    $row = $connect->getRow("SELECT login, active FROM klient WHERE id=?i", $this->id);
    $row["send-login"] = 0;
    if($row["login"] != "")
      $row["time"] = $connect->getOne("SELECT DATE_FORMAT(date, '%d.%m.%Y %H:%i:%s') as date FROM session_account WHERE login=?s", $row["login"]);
    elseif($connect->getOne("SELECT id FROM klient WHERE id=?i AND email!='' AND (login='' OR login IS NULL)", $this->id))
			if(!$connect->getOne("SELECT id FROM klient WHERE login=?s", $row["email"]))
        $row["send-login"] = 1;
    return $row;
  }

}

?>
