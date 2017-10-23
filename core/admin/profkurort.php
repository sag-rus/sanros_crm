<?php

function select_objects_profkurort($connect){
  $profkurort_sync = new ProfkurortSync;
  $data = $profkurort_sync->get_objects();
  if(!isset($data["ref"])){
         foreach($data as $index => $row){
               $data[$index]["sync"] = 0;
               $id = $connect->getOne("SELECT id FROM object WHERE sync_id=?i AND check_places=3", $row["objid"]);
               if($id > 0){
                 $data[$index]["sync"] = 1;
                 $data[$index]["name"] = get_object($connect, $id, "place");
               }
         }
  }
  return json_encode($data);
}

function select_object_profkurort($connect){
  $array = array();
  $data = $connect->getAll("SELECT id, sync_id FROM object WHERE check_places=3");
  foreach ($data as $row){
    $id = $row["id"];
    $array[$id] = array();
    $array[$id]["name"] = get_object($connect, $id, "place");
    $array[$id]["sync-id"] = $row["sync_id"];
  }
  return json_encode($array);
}

function update_object_profkurort_id($connect){
  $object = $_POST["object"];
  $id = $_POST["id"];
  $connect->query("UPDATE object SET sync_id=?i WHERE id=?i", $id, $object);
  $update = $connect->getOne("SELECT sync_id FROM object WHERE id=?i", $object);
  return json_encode($update);
}

function select_rooms_object_profkurort($connect){
  $object = $_POST["object"];
  $array = array("room" => array(), "profkurort" => array());
  $data = $connect->getAll("SELECT id, sync_id FROM room WHERE id_obj=?i", $object);
  foreach ($data as $row){
    $id = $row["id"];
    $array["room"][$id] = array();
    $array["room"][$id]["name"] = get_room($connect, $id, "full");
    $array["room"][$id]["sync-id"] = $row["sync_id"];
  }
  $object_profkurort = $connect->getOne("SELECT sync_id FROM object WHERE id=?i", $object);
  $profkurort_sync = new ProfkurortSync;
  $data = $profkurort_sync->get_rooms_object($object_profkurort);
  $array["profkurort"] = $data;
  return json_encode($array);
}

function update_rooms_object_profkurort($connect){
  $data = json_decode($_POST["data"], TRUE);
  foreach($data as $room => $sync){
    $connect->query("UPDATE room SET sync_id=?i WHERE id=?i", $sync, $room);
  }
}

?>