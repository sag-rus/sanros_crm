<?php

class SyncComparisonObject extends DisplayComparisonObject{

  public function update(){
    $connect = $this->connect;
    $object = $this->object;
    $row = $this->select_competitor();
    $params = array(
      "func" => "update_comparison_object",
      "object" => $object,
      "validity" => $row["validity_date"],
      "rate" => $row["rate"]
    );
    SyncRequest::sync($params);
    $connect->query("UPDATE comparison_module_object SET update_info=0 WHERE object=?i", $object);
  }

}

?>
