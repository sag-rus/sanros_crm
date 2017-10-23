<?php

class Object_CRM{

  private $connect;
  private $object;

  public function __construct($connect){
    $this->connect = $connect;
  }

  public function similar_objects($object){
    $connect = $this->connect;
    $region = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $object);
    $type = $connect->getOne("SELECT type FROM object WHERE id=?i", $object);
    $result = array();
    if($type == 1)
      $data = $connect->getAll("SELECT id FROM object WHERE id_reg=?i AND id!=?i AND active!=2 AND type=1", $region, $object);
    else
      $data = $connect->getAll("SELECT id FROM object WHERE id_reg=?i AND id!=?i AND active!=2", $region, $object);
    foreach($data as $row){
      $id = $row["id"];
      $result[] = $id;
    }
    if(count($result) > 3){
      $keys = array_rand($result, 3);
      $random_result = array();
      foreach($keys as $key){
        $random_result[] = $result[$key];
      }
      $result = $random_result;
    }
    return $result;
  }

}

?>
