<?php

class DisplayComparisonObject extends ComparisonObject{

  public function select_competitor(){
    $connect = $this->connect;
    $object = $this->object;
    $row = $connect->getRow("SELECT competitor, validity_date, rate FROM comparison_module_object WHERE object=?i", $object);
		return $row;
  }

  public function select_rate_object(){
    $row = $this->select_competitor();
    $rate = $row["rate"];
    $rates = $this->select_rate();
    $rate_object = $rates[$rate];
    return $rate_object;
  }

}

?>
