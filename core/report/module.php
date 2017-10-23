<?php

function get_bid_module_agency($connect){
	$answer = request_to_sync(array("func" => "get_bid_module_agency"));
	$_SESSION["module_agency"] = $answer;
?>
<div class="btn-group small-menu-report">
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-new" onclick="show_bid_module_agency('new')"><i class="fa fa-file-o"></i> Новые</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-work" onclick="show_bid_module_agency('work')"><i class="fa fa-check-circle"></i> В работе</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-delete" onclick="show_bid_module_agency('delete')"><i class="fa fa-times-circle"></i> Удаленные</button>
	</div>
</div>
<div id="panel" style="margin-top: 10px"></div>
<?php
}

function show_bid_module_agency($connect){
	$type = $_POST["type"];
	$data = $_SESSION["module_agency"][$type];
	if($data){
?>
	<table class="table table-bordered table-condensed table-hover">
	<tr>
		<th>Дата</th>
		<th>Агентство</th>
		<th>Турист</th>
		<th>Объект</th>
		<th>Заезд</th>
		<th>Номер в CRM</th>
	</tr>
<?php
		foreach($data as $row){
			$agency = $connect->getOne("SELECT name FROM agency WHERE module=?s", $row["agency"]);
			$booking = json_decode($row["data"], TRUE);
			$object = get_object($connect, $booking["object"]);
?>
	<tr>
		<td width="10%"><?php echo $row["date"]; ?></td>
		<td width="25%"><?php echo $agency; ?></td>
		<td width="30%"><?php echo $booking["surname"]." ".$booking["name"]." ".$booking["otch"]; ?></td>
		<td width="15%"><?php echo $object; ?></td>
		<td width="10%"><?php echo $booking["date_race"]; ?></td>
		<td width="10%"><?php echo $row["CRM_id"]; ?></td>
	</tr>
<?php
		}
?>
	</table>
<?php
	}else{
?>
	<1>
<?php
	}
}

?>
