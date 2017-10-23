<?php

abstract class AlfaBank{

  protected $connect;
  protected $link;
  protected $booking;
  protected $turist;
  protected $type;
  protected $bookingInfo = array(
    "id",
    "sum",
    "orderId",
    "orderNumber",
    "returnUrl",
    "failUrl",
    "orderId"
  );
  protected $bankInfo = array(
    "userName",
    "password",
    "link",
    "commission"
  );

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->link = $config->clientCabinet["link"];
    $this->bankInfo = $config->onlinePaymentInfo;
    $this->booking = $config->booking;
    $this->turist = $config->account;
  }

  protected function get_status_alfabank(){
    $url = $this->bankInfo["link"]."getOrderStatus.do";
    $data["userName"] = $this->bankInfo["userName"];
    $data["password"] = $this->bankInfo["password"];
    $data["orderId"] = $this->bookingInfo["orderId"];
    $answer = request_to_url($url, $data);
    return $answer;
  }

  protected function registration_payment_alfabank(){
    $url = $this->bankInfo["link"]."registerPreAuth.do";
    $data["userName"] = $this->bankInfo["userName"];
    $data["password"] = $this->bankInfo["password"];
	  $data["amount"] = $this->bookingInfo["sum"] * 100;
    $data["orderNumber"] = $this->bookingInfo["orderNumber"];
    $data["returnUrl"] = $this->bookingInfo["returnUrl"];
    $data["failUrl"] = $this->bookingInfo["failUrl"];
    $data["description"] = $this->bookingInfo["description"];
    $answer = request_to_url($url, $data);
    return $answer;
  }

  protected function deposit_payment_alfabank(){
    $url = $this->bankInfo["link"]."deposit.do";
    $data["userName"] = $this->bankInfo["userName"];
    $data["password"] = $this->bankInfo["password"];
    $data["amount"] = $this->bookingInfo["amount"];
    $data["orderId"] = $this->bookingInfo["orderId"];
    $answer = request_to_url($url, $data);
    return $answer;
  }

  protected function cancel_alfabank(){
    $url = $this->bankInfo["link"]."reverse.do";
    $data["userName"] = $this->bankInfo["userName"];
    $data["password"] = $this->bankInfo["password"];
    $data["orderId"] = $this->bookingInfo["orderId"];
    $answer = request_to_url($url, $data);
    return $answer;
  }

  abstract protected function check_payment();
  abstract protected function registration_payment($type);
  abstract protected function deposit_payment();

}

?>
