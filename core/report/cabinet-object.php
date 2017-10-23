<?php

function report_booking_module_cabinet($connect){
  $answer = array();

  $data = $connect->getAll("SELECT id, object, website, email, telephone, DATE_FORMAT(date_create, '%d.%m.%Y') as date FROM booking_module_object");
  foreach($data as $row){
    $id = $row["id"];
    $answer[$id] = array();
    $answer[$id]["date"] = $row["date"];
    $answer[$id]["object"] = get_object($connect, $row["object"], "type");
    $answer[$id]["website"] = $row["website"];
    $answer[$id]["email"] = $row["email"];
    $answer[$id]["telephone"] = $row["telephone"];
  }
  return json_encode($answer);
}

function report_booking_request_module_cabinet($connect){
  $answer = array();
  $status = new StatusBookingModuleObject;
  $status_array = $status->select_status();

  $data = $connect->getAll("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date, object, DATE_FORMAT(arrival, '%d.%m.%Y') as arrival, status, sum FROM booking_request_object_module");
  foreach($data as $row){
    $id = $row["id"];
    $answer[$id] = array();
    $answer[$id]["date"] = $row["date"];
    $answer[$id]["object"] = get_object($connect, $row["object"], "type");
    $answer[$id]["arrival"] = $row["arrival"];
    $answer[$id]["status"] = "";
    if(isset($status_array[$row["status"]])){
      $answer[$id]["status"] = $status_array[$row["status"]];
    }
    $answer[$id]["sum"] = $row["sum"];
  }
  return json_encode($answer);
}

function report_comparison_object($connect){
  $answer = array();
  $comparison = new ComparisonObject;
  $rates = $comparison->select_rate();
  $time = time();
  $data = $connect->getAll("SELECT update_info, object, rate, DATE_FORMAT(validity_date, '%d.%m.%Y') as validity, rate, DATE_FORMAT(date_create, '%d.%m.%Y') as date, competitor FROM comparison_module_object");
  foreach($data as $row){
    $id = $row["object"];
    $answer[$id] = array();
    $answer[$id]["date"] = $row["date"];
    $answer[$id]["validity"] = $row["validity"];
    $answer[$id]["rate"] = $rates[$row["rate"]]["name"];
    $answer[$id]["object"] = get_object($connect, $row["object"], "type");
    $answer[$id]["update"] = $row["update_info"];
    $answer[$id]["class"] = 0;
    if(strToTime($row["validity"]) >= $time){
      $answer[$id]["class"] = 1;
    }
  }
  return json_encode($answer);
}

function edit_comparison_object($connect){
  $data = array();
  $display = new DisplayComparisonObject;
  $data["module"] = $display->select_competitor();
  $data["rate"] = $display->select_rate();
  return json_encode($data);
}

function update_comparison_object(){
  $update = array(
    "rate" => (int)$_POST["rate"],
    "validity_date" => date_change($_POST["date"], "-", ".")
  );
  $edit = new EditComparisonObject;
  $edit->update($update);
}

function sync_comparison_object(){
  $sync = new SyncComparisonObject;
  $sync->update();
}

?>
