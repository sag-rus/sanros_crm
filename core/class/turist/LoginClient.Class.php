<?php

class LoginClient{

  private $connect;
  private $account;
  private $booking;
  private $link;

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->account = $config->turist;
    $this->link = $config->clientCabinet["link"];
  }

  public function create_login_turist(){
    $connect = $this->connect;
    $turist = $this->account;
    $link = $this->link;
    $email = clear_email($connect->getOne("SELECT email FROM klient WHERE id=?i", $turist));
    $check = $connect->getOne("SELECT id FROM klient WHERE login=?s", $email);
    $array = array(
      "create" => 0
    );
    if(!$check){
      $today = date("Y-m-d");
      $array["password"] = gen_password(rand(6, 8));
      $array["hash"] = uniqid();
      $connect->query("UPDATE klient SET login=?s, password=?s, hash=?s, date_reg=?s WHERE id=?i", $email, md5($array["password"]), $array["hash"], $today, $turist);
      $array["create"] = 1;
      $array["link"] = $link;
      $func_turist = new FuncClient;
      $func_turist->save_history_client("Выдача клиенту логина и пароля");
    }
    return $array;
  }
}

?>
