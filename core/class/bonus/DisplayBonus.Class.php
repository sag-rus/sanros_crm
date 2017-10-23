<?php

class DisplayBonus{

  private $connect;
  private $turist;

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->turist = $config->turist;
  }

  public function select_bonus(){
    $connect = $this->connect;
    $turist = $this->turist;
    $sum = 0;
  	$data = $connect->getAll("SELECT sum, schet FROM bonus WHERE turist=?i", $turist);
  	foreach($data as $row){
  		$sum+= $row["sum"];
    }
  	return $sum;
  }

}

?>
