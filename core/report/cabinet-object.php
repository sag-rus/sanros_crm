<?php
$directory = dirname(__FILE__);

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

function report_comparison_objects_updates($connect){
  $data = $connect->getOne("SELECT COUNT(id) FROM comparison_module_object WHERE changed_status = 1");
  return json_encode(['updates_count'=> $data]);
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
  $answer = [];
  $comparison = new ComparisonObject;
  $rates = $comparison->select_rate();
  $time = time();
  $data = $connect->getAll("SELECT id, contract_request_info, update_info, changed_status, object, rate, DATE_FORMAT(validity_date, '%d.%m.%Y') as validity, rate, DATE_FORMAT(date_create, '%d.%m.%Y') as date, competitor FROM comparison_module_object ORDER BY changed_status DESC, contract_request_info DESC");
  foreach($data as $row){
    $answer_row = [
      'object_id' => $row["object"],
      'date' => $row["date"],
      'validity' => $row["validity"],
      'rate' => $rates[$row["rate"]]["name"],
      'object' => get_object($connect, $row["object"], "type"),
      'update' => $row["update_info"],
      'class' => 0,
      "changed_status" => $row["changed_status"],
      'contract_request' => ''
    ];

    if(!is_null($row['contract_request_info'])) {
      $data2 = $connect->getRow("SELECT id, rate, month, date FROM comparison_module_payment_invoice WHERE module_id = ?i AND status != '0' ORDER BY date DESC LIMIT 1", $row["id"]);
      if($data2)
        $answer_row["contract_request"] = '<a href="document.php?func=comparison_module_payment&object='.$row["object"].'&rate='.$data2['rate'].'&month='.$data2['month'].'" class="btn btn-info btn-sm" target="_blank">Запрос на оплату!</a>';
    }

    if($row["changed_status"] == 1) {
      $connect->query("UPDATE comparison_module_object SET changed_status=0 WHERE id=?i", $row['id']);
    }

    if(strToTime($row["validity"]) >= $time){
      $answer_row["class"] = 1;
    }
    $answer[] = $answer_row;
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
