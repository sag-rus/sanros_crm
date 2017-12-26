<?php
class ProfkurortSync{

  protected $login;
  protected $password;
  protected $date;
  protected $url;
  protected $hash;

  function __construct(){
    $config = connect_config();
    $login = $config->login_profkurort;;
    $password = $config->password_profkurort;
    $this->login = $login;
    $this->password = $password;
    $this->url = $config->url_test_profkurort;
    $this->date = date("Y-m-d H:i");
    $this->hash = md5($login."-".date("YmdHi")."-".$password);
    //echo $login."-".date("YmdHi")."-".$password."<br />";
    //echo $this->hash;
  }

  public function get_objects(){
    $server = new SoapClient($this->url);
    $data = $server->getObjects("profkurort", $this->date, $this->hash);
    $data = json_decode($data, TRUE);
    return $data;
  }

  public function get_programms($object){
    $server = new SoapClient($this->url);
    $data = $server->getProgramms("profkurort", $this->date, $object, $this->hash);
    $data = json_decode($data, TRUE);
    return $data;
  }

  public function get_rooms_object($object){
    $server = new SoapClient($this->url);
    $data = $server->getCategs("profkurort", $this->date, $object, $this->hash);
    $data = json_decode($data, TRUE);
    return $data;
  }

  public function get_quota_object($object, $date, $days){
    $server = new SoapClient($this->url);
    $data = $server->getQData("profkurort", $this->date, $object, $date, $days, $this->hash);
    $data = json_decode($data, TRUE);
    return $data;
  }

  public function get_prices_object($object, $date = NULL, $days) {
    $server = new SoapClient($this->url);

    if(is_null($date))
      $data = $server->getPrices("profkurort", $this->date, $object, $days, $this->hash);
    else
      $data = $server->getPrices("profkurort", $date, $object, $days, $this->hash);
    $data = json_decode($data, TRUE);
    return $data;
  }

  public function create_booking($object, $arrival, $leaving, $categs, $clidata, $suppdata){
    $server = new SoapClient($this->url);
    $data = $server->setOrder("profkurort", $this->date, 0, $object, $arrival, $leaving, "", $categs, $clidata, $suppdata, $this->hash);
    $data = json_decode($data, TRUE);
    return $data;
  }

  public function update_booking($object, $arrival, $leaving, $id_profkurort, $clidata){
    $server = new SoapClient($this->url);
    $data = $server->setOrder("profkurort", $this->date, 0, $id_profkurort, $object, $arrival, $leaving, "", $clidata, $this->hash);
    $data = json_decode($data, TRUE);
    return $data;
  }
}
?>