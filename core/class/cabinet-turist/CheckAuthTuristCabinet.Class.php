<?php

class CheckAuthTuristCabinet{

  private function __construct(){}

  public static function check_authorization(){
    $config = ConfigCRM::getInstance();
    $configNew = App\lib\CRM\Config\Client::getInstance();
    $connect = $config->connect;
    $session = $config->session;
    $login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $session);
  	$client = $connect->getOne("SELECT id FROM klient WHERE login=?s", $login);
    if($client){
      $config->account = $client;
      $configNew->account = $client;
      $config->typeAuth = "turist";
      $configNew->typeAuth =  "turist";
      $config->turist = $client;
      $configNew->turist = $client;
      return TRUE;
    }
    return FALSE;
  }

  public static function check_authorization_booking(){
    if(self::check_authorization()){
      $config = ConfigCRM::getInstance();
      $configNew = App\lib\CRM\Config\Client::getInstance();
      $booking = $config->booking;
      $connect = $config->connect;
      $account = $config->account;
      if(!$booking)
        return FALSE;
      $id = $connect->getOne("SELECT id FROM reckoning WHERE id=?i AND turist=?i", $booking, $account);
      if($id){
        return TRUE;
      }
    }
    return FALSE;
  }

  public static function check_authorization_booking_object(){
    if(self::check_authorization()){
      $config = ConfigCRM::getInstance();
      $configNew = App\lib\CRM\Config\Client::getInstance();
      $booking = $config->booking;
      $connect = $config->connect;
      $account = $config->account;
      if(!$booking)
        return FALSE;
      $id = $connect->getOne("SELECT id FROM booking_request_object_module WHERE id=?i AND turist=?i", $booking, $account);
      if($id){
        $config->bookingId = $id;
        return TRUE;
      }
    }
    return FALSE;
  }

}

?>
