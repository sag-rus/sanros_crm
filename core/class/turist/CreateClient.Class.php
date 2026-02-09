<?php

class CreateClient{

  private $connect;

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
  }

  public function create_client($data){
    $connect = $this->connect;
    $surname = first_symbol_to_title(clean_user_name_data($data["surname"]));
    $name = first_symbol_to_title(clean_user_name_data($data["name"]));
    $otch = first_symbol_to_title(clean_user_name_data($data["otch"]));
    $email = "";
    $telephone = "";
    $address = "";
    $date = NULL;

    $sex = null;
    if(isset($data['sex'])) {
      $sex = (int)$data['sex'];
      if($sex !== 0 && $sex !== 1) {
        $sex = null;
      }
    }

    if(isset($data["date"]))
      $date = $data["date"];
    if(isset($data["email"]))
      $email = clear_email($data["email"]);
    if(isset($data["telephone"]))
      $telephone = clear_telephone($data["telephone"]);
    if(isset($data["ip"]))
      $address = get_address_by_ip($data["ip"]);
    if($telephone){
      if ($email == "") {
        $client = $connect->getOne("SELECT id FROM klient WHERE telephone=?s ORDER BY id DESC", $telephone);
      } elseif ($email != "") {
        $client = $connect->getOne("SELECT id FROM klient WHERE (email='' OR email=?s) AND telephone=?s ORDER BY id DESC", $email, $telephone);
      } elseif ($date == NULL) {
        $client = $connect->getOne("SELECT id FROM klient WHERE surname=?s AND name=?s AND (otch='' OR otch IS NULL OR otch=?s) AND (email='' OR email IS NULL OR email=?s) AND telephone=?s ORDER BY id DESC", $surname, $name, $otch, $email, $telephone);
      } else {
        $client = $connect->getOne("SELECT id FROM klient WHERE surname=?s AND name=?s AND (otch='' OR otch IS NULL OR otch=?s) AND (email='' OR email IS NULL OR email=?s) AND telephone=?s AND `date`=?s ORDER BY id DESC", $surname, $name, $otch, $email, $telephone, $date);
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
    $original_data = [
      'surname' => $surname,
      'name' => $name,
      'otch' => $otch,
      'telephone' => $telephone,
      'email' => $email,
      'sex' => $sex
    ];

    if(is_null($sex))
      $connect->query("INSERT INTO klient(date, surname, name, otch, telephone, email, original_data) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s)", $date, $surname, $name, $otch, $telephone, $email, json_encode($original_data));
    else
      $connect->query("INSERT INTO klient(date, surname, name, otch, sex, telephone, email, original_data) VALUES (?s, ?s, ?s, ?s, ?i, ?s, ?s, ?s)", $date, $surname, $name, $otch, $sex, $telephone, $email, json_encode($original_data));

    $insertId = $connect->insertId();
    return $insertId;
  }

}

?>
