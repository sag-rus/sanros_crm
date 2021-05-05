<?php

namespace App\lib\CRM\Config;

class Client {
  private static $instance = null;
  public $connect;//Коннект к БД
  public $session;//ID сессии
  public $booking;//Номер заявки
  public $object;//ID санатория
  public $account;//ID пользователя
  public $typeAuth;
  public $turist;//ID туриста
  public $directory;//директория
  public $sync = array(
    "link"
  );
  public $bonus = array(
    "bonus-booking",
    "bonus-affiliate"
  );
  public $clientCabinet = array(
    "link",
    "link-payment"
  );
  public $onlinePaymentInfo = array(
    "userName",
    "userName_v2",
    "userName_v3",
    "userName_test",
    "password",
    "password_v2",
    "password_v3",
    "password_test",
    "link",
    "commission"
  );
  public $contactInfo = array(
    "free-line",
    "email",
    "website"
  );
  public $mail = array(
    "default" => array(
      "login",
      "password"
    ),
    "module" => array(
      "login",
      "password"
    )
  );

  public static function getInstance(){
    if(null === self::$instance){
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __clone(){}
  private function __construct(){}
}