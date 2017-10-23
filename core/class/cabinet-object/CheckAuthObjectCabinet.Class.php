<?php

class CheckAuthObjectCabinet{

  private function __construct(){}

  public static function check_authorization(){
    $config = ConfigCRM::getInstance();
    $connect = $config->connect;
    $object = $config->object;
    $session = $config->session;
    $login = $connect->getOne("SELECT login FROM session_object WHERE id_session=?s", $session);
    $account = $connect->getOne("SELECT id FROM object_account WHERE login=?s", $login);
    $true = $connect->getOne("SELECT id FROM object WHERE id_account=?i AND id=?i", $account, $object);
    if($true){
      $config->account = $account;
      $config->typeAuth = "object";
      return TRUE;
    }
    return FALSE;
  }

  public static function check_authorization_booking(){
    if(self::check_authorization()){
      $config = ConfigCRM::getInstance();
      $booking = $config->booking;
      $connect = $config->connect;
      $object = $config->object;
      if(!$booking)
        return FALSE;
      $id = $connect->getOne("SELECT id FROM booking_request_object_module WHERE id=?i AND object=?i", $booking, $object);
      if($id){
        $config->typeAuth = "object";
        return TRUE;
      }
    }
    return FALSE;
  }

}

?>
