<?php

class DisplayBooking{

  protected $connect;
  protected $account;

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->account = $config->account;
  }

  public function display_booking($data){
    $connect = $this->connect;
    $array = array();
    foreach($data as $row){
			$id = $row["id"];
			$status = $row["status"];
			$array[$id] = array();
			$array[$id]["object"] = get_object($connect, $row["id_obj"], "type");
			$array[$id]["arrival"] = $row["arrival"];
			$array[$id]["leaving"] = $row["leaving"];
			$array[$id]["sum"] = $row["sum"];
      $array[$id]["id-status"] = $status;
			$array[$id]["status"] = StatusBooking::select_status_name($status);
		}
    return $array;
  }

}

?>
