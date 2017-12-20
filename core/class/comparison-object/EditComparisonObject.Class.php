<?php

class EditComparisonObject extends DisplayComparisonObject{

  public function append_competitor($append){
    $connect = $this->connect;
    $object = $this->object;
    if($object == $append){
      return;
    }
    $answer = array(
      "competitor",
      "message" => "error-append"
    );
    $comp = $connect->getOne("SELECT competitor FROM comparison_module_object WHERE object=?i", $object);
    $competitors = array();
    if($comp){
      $competitors = json_decode($comp, TRUE);
    }
    $rates = $this->select_rate_object();
    $json = json_encode($competitors);
    if(count($competitors) < $rates["max"]){
      $answer["message"] = "";
      if(!isset($competitors[$append])){
        $competitors[$append] = "";
        $json = json_encode($competitors);
      }
      $connect->query("UPDATE comparison_module_object SET competitor=?s WHERE object=?i", $json, $object);
    }
    $answer["competitor"] = $json;
    return $answer;
  }

  public function update_competitor($competitor, $room){
    $connect = $this->connect;
    $object = $this->object;
    if($object == $competitor){
      return;
    }
    $comp = $connect->getOne("SELECT competitor FROM comparison_module_object WHERE object=?i", $object);
    $competitors = array();
    if($comp){
      $competitors = json_decode($comp, TRUE);
      if($competitor AND isset($competitors[$competitor])){
        $competitors[$competitor] = $room;
        $json = json_encode($competitors);
        $connect->query("UPDATE comparison_module_object SET competitor=?s WHERE object=?i", $json, $object);
        return $json;
      }
    }
    return $comp;
  }

  public function update(array $update){
    $connect = $this->connect;
    $object = $this->object;
    foreach($update as $key => $name){
      $connect->query("UPDATE comparison_module_object SET $key=?s WHERE object=?i", $name, $object);
    }
    $connect->query("UPDATE comparison_module_object SET update_info=1 WHERE object=?i", $object);
  }

  public function remove_competitors($remove_competitors) {
    $connect = $this->connect;
    $object = $this->object;
    $answer = [
      "competitor",
      "message" => "error-remove"
    ];
    if(!is_array($remove_competitors))
      $remove_competitors = [$remove_competitors];

    $comp = $connect->getOne("SELECT competitor FROM comparison_module_object WHERE object=?i", $object);
    if($comp){
      $competitors = json_decode($comp, TRUE);
      foreach ($remove_competitors as $remove_competitor) {
        if(isset($competitors[$remove_competitor])) {
          $answer["message"] = "success-remove";
          unset($competitors[$remove_competitor]);
        }
      }
      $json = json_encode($competitors);
      $answer["competitor"] = $json;
      $connect->query("UPDATE comparison_module_object SET competitor=?s WHERE object=?i", $json, $object);
    }

    return $answer;
  }

}

?>
