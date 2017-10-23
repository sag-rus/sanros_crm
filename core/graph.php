<?php

	$color = array(
			"all" => "green",
			0 => "#2b7239",
			1 => "#e8b4e4",
			2 => "#4ddb35",
			4 => "#1ef4e7",
			4 => "#b328a8",
			5 => "#2626e5",
			6 => "#e32020",
			7 => "#a7acde",
			8 => "#a8f39b"
		);

	$month_array = array(1 => "Янв", 2 => "Фев", 3 => "Март", 4 => "Апр", 5 => "Май", 6 => "Июнь", 7 => "Июль", 8 => "Авг", 9 => "Сен", 10 => "Окт", 11 => "Ноя", 12 => "Дек");

function graph_current(){
	ob_start();
?>
<div class="form-horizontal panel panel-default" style="width: 420px; display: inline-block;">
	<div class="panel-body" style="padding-right: 22px;">
		<div class="form-group">
			<label class="col-sm-3 control-label">Диапазон</label>
			<div class="col-sm-9 well well-xs range-graph" onClick="change_range_graph()">
				<label><input type="radio" CHECKED name="range" value="day" /> дни</label>&nbsp;
				<label><input type="radio" name="range" value="week" /> недели</label>&nbsp;
				<label><input type="radio" name="range" value="month" /> месяцы</label>&nbsp;
				<label><input type="radio" name="range" value="my" /> свой</label>
			</div>
		</div>
		<div class="form-group my-dates-div" style="display: none">
			<label class="col-sm-3 control-label">Мои даты</label>
			<div class="col-sm-4 well-xs">
				<input type="date" class="form-control" id="my-date1" />
			</div>
			<div class="col-sm-4 well-xs">
				<input type="date" class="form-control" id="my-date2" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Дата</label>
			<div class="col-sm-9 well well-xs date-graph">
				<label><input type="radio" CHECKED name="date" value="date" /> заявки</label>&nbsp;
				<label><input type="radio" name="date" value="date_z" /> заезда</label>&nbsp;
				<label><input type="radio" name="date" value="date_v" /> выезда</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Значения</label>
			<div class="col-sm-9 well well-xs value-graph">
				<label><input type="radio" CHECKED name="value" value="allsum" /> суммарные</label>&nbsp;
				<label><input type="radio" name="value" value="middle" /> среднесуточные</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Данные</label>
			<div class="col-sm-9 well well-xs data-graph">
				<label><input type="radio" CHECKED name="data" value="number" /> кол-во</label>&nbsp;
				<label><input type="radio" name="data" value="percent" /> проценты</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Статусы</label>
			<div class="col-sm-9 well well-xs status-graph">
				<label onClick="change_status_graph()"><input type="radio" CHECKED name="status" value="reck" /> заявки</label>&nbsp;
				<label onClick="change_status_graph()"><input type="radio" name="status" value="san" /> санатория</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Выбор</label>
			<div class="col-sm-9 well-xs choice-graph">
				<select class="form-control" onChange="change_choice_graph()">
					<option value="">не важно</option>
					<option value="region">по региону</option>
					<option value="object">по объекту</option>
					<option value="manager">по менеджеру</option>
				</select>
			</div>
		</div>
		<div class="form-group choice-div" style="display: none">
			<label class="col-sm-3 control-label"></label>
			<div class="col-sm-9 well-xs choice-html">
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="form-group form-group-margin">
			<div class="col-sm-offset-8 col-sm-4">
				<button type="button" class="btn btn-success btn-xs" onclick="get_data_for_graph_current()"><i class="fa fa-area-chart"></i> Сформировать</button>
			</div>
		</div>
	</div>
</div>
<div id="status" style="margin-left: 15px; display: inline-block; vertical-align: top;"></div>

<?php
	$html = ob_get_clean();
	return $html;
}

function graph_cancel(){
	ob_start();
?>
<div class="form-horizontal" style="width: 400px; display: inline-block;">
	<div class="form-group">
		<label class="col-sm-3 control-label">Диапазон</label>
		<div class="col-sm-9 well well-xs range-graph">
			<label><input type="radio" CHECKED name="range" value="day" /> по дням</label>&nbsp;
			<label><input type="radio" name="range" value="week" /> по неделям</label>&nbsp;
			<label><input type="radio" name="range" value="month" /> по месяцам</label>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">Выбор</label>
		<div class="col-sm-9 well well-xs choice-graph">
			<label><input type="radio" CHECKED name="choice" value="current" /> общий</label>&nbsp;
			<label><input type="radio" name="choice" value="reason" /> по 10 объектам</label>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">Объект</label>
		<div class="col-sm-9 well-xs" id="object_name">
			<input type="hidden" id="id_obj">
			<input type="hidden" id="sel_room">
			<input type="text" class="form-control" id="object" onkeyup="find_klient('object', 'object', 'sel_object')">
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-12">
			<button type="button" class="btn btn-success btn-xs" onclick="get_data_for_graph_cancel()"><i class="fa fa-area-chart"></i> Сформировать</button>
		</div>
	</div>
</div>

<div id="reason" style="margin-left: 15px; display: inline-block; vertical-align: top;"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function graph_client(){
	ob_start();
?>
<div class="form-horizontal" style="width: 400px; display: inline-block;">
	<div class="form-group">
		<label class="col-sm-3 control-label">Диапазон</label>
		<div class="col-sm-9 well well-xs range-graph">
			<label><input type="radio" CHECKED name="range" value="day" /> по дням</label>&nbsp;
			<label><input type="radio" name="range" value="week" /> по неделям</label>&nbsp;
			<label><input type="radio" name="range" value="month" /> по месяцам</label>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-12">
			<button type="button" class="btn btn-success btn-xs" onclick="get_data_for_graph_client()"><i class="fa fa-area-chart"></i> Сформировать</button>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}
	
	function get_data_for_graph_current($connect){

		global $color;

		$arr = array();
		$status = $_POST["status"];
		$arr_status = explode("_", $status);
		$arr_status = array_diff($arr_status, array(""));
		$type_status = $_POST["type_status"];
		$status_name = get_status_array($connect, $type_status);
		$status_name["all"] = "Всего заявок";
		$i = 0;
		$int = 1;

		$date_params = $_POST["date_params"];
		$span_params = $_POST["span_params"];
		$choice_params = $_POST["choice_params"];
		$value_params = $_POST["value_params"];
		$choice_data = $_POST["choice_data"];
		if($choice_params == "object")
			$parametr = explode("_", $_POST["id_obj"]);
		elseif($choice_params == "region")
			$parametr = explode("_", $_POST["id_reg"]);
		elseif($choice_params == "manager")
			$parametr = explode("_", $_POST["manager"]);
		else
			$parametr = array(1 => 1);
		$parametr = array_diff($parametr, array(''));

		foreach($parametr as $p){
			$place = "";
			if($choice_params == "object"){
				$place = "AND (id_obj='".$p."')";
				$label = get_object($connect, $p);
			}elseif($choice_params == "region"){
				$place = get_objects_by_region($connect, $p);
				$label = $connect->getOne("SELECT name FROM region WHERE id=?i", $p);
			}elseif($choice_params == "manager"){
				$place = " AND (id_user='".$p."')";
				$label = $connect->getOne("SELECT name FROM users WHERE id=?i", $p);
			}	
			$place.= " AND active != '3'";
			if($span_params == "day"){
				$day = date('d');
				$month = date('m');
				$year = date('Y');
				$first_year = $year;
				$first_month = $month - 1;
				if($month == 1){
					$first_month = 12;
					$first_year = $year - 1;
				}
				$max_day = cal_days_in_month(CAL_GREGORIAN, $first_month, $first_year);
				foreach($arr_status as $status){
					$query_status = '';
					if($status != 'all')
						$query_status = " AND ".$type_status."='$status' ";
					$data = array();
					for($i_day = $day; $i_day <= $max_day; $i_day++){
						$date = $first_year."-".$first_month."-".$i_day;
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params."='$date' ".$query_status.$place));
						if($choice_data == 'percent'){
							$all_count = count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params."='$date' ".$place));
							$count = round((($count/$all_count)*100), 2);
						}
						$data[] = array($date, $count);
					}
					for($i_day = 1; $i_day <= $day; $i_day++){
						$date = $year."-".$month."-".$i_day;
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params."='$date' ".$query_status.$place));
						if($choice_data == 'percent'){
							$all_count = count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params."='$date' ".$place));
							$count = round((($count/$all_count)*100), 2);
						}
						$data[] = array($date, $count);
					}
					$index++;
					$arr[] = array("label" => $label." ".$status_name[$status], "color" => $color[$index], "data" => $data);
				}
			}elseif($span_params == "week"){
				$day = date('d');
				$month = date('m');
				$year = date('Y');
				$week = date('w');
				if($week == 0)
					$today = date('d-m-Y', mktime(0, 0, 0, $month, $day-6, $year));
				else
					$today = date('d-m-Y', mktime(0, 0, 0, $month, $day-($week - 1), $year));
				$t = explode("-", $today);
				$day = $t[0];
				$month = $t[1];
				$year = $t[2];
				foreach($arr_status as $status){
					$query_status = '';
					if($status != 'all')
						$query_status = " AND ".$type_status."='$status'";
					$data = array();
					for($days = $day-147; $days < $day; $days = $days+7){
						$date_start = date('Y-m-d', mktime(0, 0, 0, $month, $days, $year));
						$date_end = date('Y-m-d', mktime(0, 0, 0, $month, $days+7, $year));
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start' AND ".$date_params." <= '$date_end' ".$query_status.$place));
						if($value_params == 'middle' AND $choice_data == 'number')
							$count = round($count / 7, 2);
						if($choice_data == 'percent'){
							$all_count = count($connect->getAll("SELECT id reckoning WHERE ".$date_params." >= '$date_start' AND ".$date_params." <= '$date_end' ".$place));
							$count = round((($count/$all_count)*100), 2);
						}
						$data[] = array($date_end, $count);
					}
					$index++;
					$arr[] = array("label" => $label." ".$status_name[$status], "color" => $color[$index], "data" => $data);
				}
			}elseif($span_params == "month"){
				$month = date('m');
				$year = date('Y');
				$first_year = $year - 1;
				foreach($arr_status as $status){
					$data = array();
					$query_status = '';
					if($status != 'all')
						$query_status = " AND ".$type_status."='$status'";
					for($i_month = $month; $i_month <= 12; $i_month++){
						$date_start_month = date($first_year.'-'.$i_month.'-1');
						$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $first_year));
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start_month' AND ".$date_params." <= '$date_end_month' ".$query_status.$place));
						if($value_params == 'middle' AND $choice_data == 'number')
							$count = round($count / cal_days_in_month(CAL_GREGORIAN, $i_month, $first_year), 2);
						if($choice_data == 'percent'){
							$all_count = count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start_month' AND ".$date_params." <= '$date_end_month' ".$place));
							$count = round((($count/$all_count)*100), 2);
						}
						$data[] = array($first_year."-".$i_month, $count);
					}
					for($i_month = 1; $i_month <= $month; $i_month++){
						$date_start_month = date($year.'-'.$i_month.'-1');
						$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $year));
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start_month' AND ".$date_params." <= '$date_end_month' ".$query_status.$place));
						if($value_params == 'middle' AND $choice_data == 'number')
							$count = round($count / cal_days_in_month(CAL_GREGORIAN, $i_month, $first_year), 2);
						if($choice_data == 'percent'){
							$all_count = count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start_month' AND ".$date_params." <= '$date_end_month' ".$place));
							$count = round((($count/$all_count)*100), 2);
						}
						$data[] = array($year."-".$i_month, $count);
					}
					$index++;
					$arr[] = array("label" => $label." ".$status_name[$status], "color" => $color[$index], "data" => $data);
				}
			}elseif($span_params == "my"){
				$date1 = $_POST["date1"];
				$date2 = $_POST["date2"];
				$date1_sec = strToTime($date1);
				$date2_sec = strToTime($date2);
				if($date2_sec <= $date1_sec)
					return FALSE;
				$step = round(($date2_sec - $date1_sec) / 30);
				foreach($arr_status as $status){
					$data = array();
					for($i = $date1_sec; $i <= $date2_sec; $i+= $step){
						$current_date1 = date("Y-m-d", $i);
						$current_date2 = date("Y-m-d", $i + $step);
						$query_status = '';
						if($status != 'all')
							$query_status = " AND ".$type_status."='$status'";
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE (".$date_params." >= '$current_date1' AND ".$date_params." <= '$current_date2') ".$query_status.$place));
						if($choice_data == 'percent'){
							$all_count = count($connect->getAll("SELECT id FROM reckoning WHERE (".$date_params." >= '$current_date1' AND ".$date_params." <= '$current_date2') ".$place));
							$count = round((($count/$all_count)*100), 2);
						}
						$data[] = array($current_date1, $count);
					}
					$index++;
					$arr[] = array("label" => $label." ".$status_name[$status], "color" => $color[$index], "data" => $data);
				}
			}
		}
		if($arr)
			return json_encode($arr);

	}

	function get_data_for_pie_current($connect){

		global $color;

		$arr = array();
		$status = $_POST["status"];
		$arr_status = explode("_", $status);
		$arr_status = array_diff($arr_status, array(''));
		$type_status = $_POST["type_status"];
		$status_name = get_status_array($connect, $type_status);
		$status_name["all"] = 'Всего заявок';
		$i = 0;
		$int = 1;

		$graph = $_POST['graph'];
		$date_params = $_POST['date_params'];
		$span_params = $_POST['span_params'];
		$choice_params = $_POST['choice_params'];
		if($choice_params == 'object')
			$parametr = explode("_", $_POST['id_obj']);
		elseif($choice_params == 'region')
			$parametr = explode("_", $_POST['id_reg']);
		elseif($choice_params == 'manager')
			$parametr = explode("_", $_POST['manager']);
		unset($status_name['all']);
		$parametr = array_diff($parametr, array(''));
		$p = $parametr[0];
		if($choice_params == 'object'){
			$place = "AND (id_obj='".$p."')";
			$label = get_object($connect, $p);
		}elseif($choice_params == 'region'){
			$place = get_objects_by_region($connect, $p);
			$label = $connect->getOne("SELECT name FROM region WHERE id=?i", $p);
		}elseif($choice_params == 'manager'){
			$place = " AND (id_user='".$p."')";
			$label = $connect->getOne("SELECT name FROM users WHERE id=?i", $p);
		}
		$place.= " AND active != '3'";
		if($span_params == "day"){
			$day = date('d');
			$month = date('m');
			$year = date('Y');
			$first_year = $year;
			$first_month = $month - 1;
			if($month == 1){
				$first_month = 12;
				$first_year = $year - 1;
			}
			$max_day = cal_days_in_month(CAL_GREGORIAN, $first_month, $first_year);
			if($graph == 'status'){
				foreach($status_name as $id_status => $status){
					$count = 0;
					$query_status = " AND ".$type_status."='$id_status' ";
					for($i_day = $day; $i_day <= $max_day; $i_day++){
						$date = $first_year."-".$first_month."-".$i_day;
						$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params."='$date' ".$query_status.$place));
					}
					for($i_day = 1; $i_day <= $day; $i_day++){
						$date = $year."-".$month."-".$i_day;
						$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params."='$date' ".$query_status.$place));
					}
					$index++;
					$arr[] = array("label" => $label." ".$status, "color" => $color[$index], "data" => $count);
				}
			}elseif($graph == 'object'){
				$data = $connect->getAll("SELECT id, name FROM object WHERE id_reg=?i", $p);
				foreach($data as $row){
					$id_obj = $row["id"];
					$object = $row["name"];
					$count = 0;
					$query_status = " AND id_obj='$id_obj' ";
					for($i_day = $day; $i_day <= $max_day; $i_day++){
						$date = $first_year."-".$first_month."-".$i_day;
						$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params."='$date' ".$query_status.$place));
					}
					for($i_day = 1; $i_day <= $day; $i_day++){
						$date = $year."-".$month."-".$i_day;
						$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params."='$date' ".$query_status.$place));
					}
					$index++;
					$arr[] = array("label" => $label." ".$object, "color" => $color[$index], "data" => $count);
				}
			}
		}elseif($span_params == "week"){
			$day = date('d');
			$month = date('m');
			$year = date('Y');
			$week = date('w');
			if($week == 0)
				$today = date('d-m-Y', mktime(0, 0, 0, $month, $day-6, $year));
			else
				$today = date('d-m-Y', mktime(0, 0, 0, $month, $day-($week - 1), $year));
			$t = explode("-", $today);
			$day = $t[0];
			$month = $t[1];
			$year = $t[2];
			if($graph == 'status'){
				foreach($status_name as $id_status => $status){
					$query_status = " AND ".$type_status."='$id_status'";
					$count = 0;
					for($days = $day-147; $days < $day; $days = $days+7){
						$date_start = date('Y-m-d', mktime(0, 0, 0, $month, $days, $year));
						$date_end = date('Y-m-d', mktime(0, 0, 0, $month, $days+7, $year));
						$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start' AND ".$date_params." <= '$date_end' ".$query_status.$place));
					}
					$index++;
					$arr[] = array("label" => $label." ".$status, "color" => $color[$index], "data" => $count);
				}
			}elseif($graph == 'object'){
				$data = $connect->getAll("SELECT id, name FROM object WHERE id_reg=?i", $p);
				foreach($data as $row){
					$id_obj = $row["id"];
					$object = $row["name"];
					$count = 0;
					$query_status = " AND id_obj='$id_obj' ";
					for($days = $day-147; $days < $day; $days = $days+7){
						$date_start = date('Y-m-d', mktime(0, 0, 0, $month, $days, $year));
						$date_end = date('Y-m-d', mktime(0, 0, 0, $month, $days+7, $year));
						$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start' AND ".$date_params." <= '$date_end' ".$query_status.$place));
					}
					$index++;
					$arr[] = array("label" => $label." ".$object, "color" => $color[$index], "data" => $count);
				}
			}
		}elseif($span_params == "month"){
			$month = date('m');
			$year = date('Y');
			$first_year = $year - 1;
			if($graph == 'status'){
				foreach($status_name as $id_status => $status){
					$count = 0;
					$query_status = " AND ".$type_status."='$id_status'";
					for($i_month = $month; $i_month <= 12; $i_month++){
						$date_start_month = date($first_year.'-'.$i_month.'-1');
						$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $first_year));
						$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start_month' AND ".$date_params." <= '$date_end_month' ".$query_status.$place));
					}
					for($i_month = 1; $i_month <= $month; $i_month++){
						$date_start_month = date($year.'-'.$i_month.'-1');
						$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $year));
						$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start_month' AND ".$date_params." <= '$date_end_month' ".$query_status.$place));
					}
					$index++;
					$arr[] = array("label" => $label." ".$status, "color" => $color[$index], "data" => $count);
				}
			}elseif($graph == 'object'){
				$data = $connect->getAll("SELECT id, name FROM object WHERE id_reg=?i", $p);
				foreach($data as $row){
					$id_obj = $row["id"];
					$object = $row["name"];
					$count = 0;
					$query_status = " AND id_obj='$id_obj' ";
					for($i_month = $month; $i_month <= 12; $i_month++){
						$date_start_month = date($first_year.'-'.$i_month.'-1');
						$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $first_year));
						$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start_month' AND ".$date_params." <= '$date_end_month' ".$query_status.$place));
					}
					for($i_month = 1; $i_month <= $month; $i_month++){
						$date_start_month = date($year.'-'.$i_month.'-1');
						$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $year));
						$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start_month' AND ".$date_params." <= '$date_end_month' ".$query_status.$place));
					}
					$index++;
					$arr[] = array("label" => $label." ".$object, "color" => $color[$index], "data" => $count);
				}
			}
		}
		return json_encode($arr);
	}

	function get_data_for_graph_cancel($connect){

		global $color;

		$reason_array = get_status_array($connect, "reason_delete");
		$date_params = $_POST["date_params"];
		$type = $_POST["type"];
		$id_obj = $_POST["id_obj"];
		$reason = explode("_", $_POST["reason"]);
		$zapros = " (active=3 OR status=6 OR status=8) ";
		if($id_obj)
			$zapros.= " AND id_obj=$id_obj ";
		if($type == "current"){
			if($date_params == "day"){
				$day = date("d");
				$month = date("m");
				$year = date("Y");
				$first_year = $year;
				$first_month = $month - 1;
				if($month == 1){
					$first_month = 12;
					$first_year = $year - 1;
				}
				$max_day = cal_days_in_month(CAL_GREGORIAN, $first_month, $first_year);
				foreach($reason as $status){
					$status_str =  " AND reason_delete=".$status;
					if($status == 'all')
						$status_str = "";
					$data = array();
					for($i_day = $day; $i_day <= $max_day; $i_day++){
						$date = $first_year."-".$first_month."-".$i_day;
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE date='$date' AND ".$zapros.$status_str));
						$data[] = array($date, $count);
					}
					for($i_day = 1; $i_day <= $day; $i_day++){
						$date = $year."-".$month."-".$i_day;
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE date='$date' AND ".$zapros.$status_str));
						$data[] = array($date, $count);
					}
					$index++;
					$arr[] = array("label" => $reason_array[$status], "color" => $color[$index], "data" => $data);
				}
			}elseif($date_params == "week"){
				$day = date("d");
				$month = date("m");
				$year = date("Y");
				$week = date("w");
				if($week == 0)
					$today = date('d-m-Y', mktime(0, 0, 0, $month, $day-6, $year));
				else
					$today = date('d-m-Y', mktime(0, 0, 0, $month, $day-($week - 1), $year));
				$t = explode("-", $today);
				$day = $t[0];
				$month = $t[1];
				$year = $t[2];
				foreach($reason as $id_reason => $status){
					$status_str =  " AND reason_delete=".$status;
					if($status == "all")
						$status_str = "";
					$data = array();
					for($days = $day-147; $days < $day; $days = $days+7){
						$date_start = date('Y-m-d', mktime(0, 0, 0, $month, $days, $year));
						$date_end = date('Y-m-d', mktime(0, 0, 0, $month, $days+7, $year));
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE date>='$date_start' AND date<='$date_end' AND ".$zapros.$status_str));
						$data[] = array($date_end, $count);
					}
					$index++;
					$arr[] = array("label" => $reason_array[$status], "color" => $color[$index], "data" => $data);
				}
			}elseif($date_params == "month"){
				$month = date('m');
				$year = date('Y');
				$first_year = $year - 1;
				foreach($reason as $id_reason => $status){
					$status_str =  " AND reason_delete=".$status;
					if($status == 'all')
						$status_str = "";
					$data = array();
					for($i_month = $month; $i_month <= 12; $i_month++){
						$date_start_month = date($first_year.'-'.$i_month.'-1');
						$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $first_year));
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE date>='$date_start_month' AND date<='$date_end_month' AND ".$zapros.$status_str));
						$data[] = array($first_year."-".$i_month, $count);
					}
					for($i_month = 1; $i_month <= $month; $i_month++){
						$date_start_month = date($year.'-'.$i_month.'-1');
						$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $year));
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE date>='$date_start_month' AND date<='$date_end_month' AND ".$zapros.$status_str));
						$data[] = array($year."-".$i_month, $count);
					}
					$index++;
					$arr[] = array("label" => $reason_array[$status], "color" => $color[$index], "data" => $data);
				}
			}
		}elseif($type == "reason"){
			$day = date('d');
			$month = date('m');
			$year = date('Y');
			$week = date('w');
			$reason = $reason[0];
			if($date_params == "day"){
				$first_year = $year;
				$first_month = $month - 1;
				if($month == 1){
					$first_month = 12;
					$first_year = $year - 1;
				}
				$max_day = cal_days_in_month(CAL_GREGORIAN, $first_month, $first_year);
				$date_start = $first_year."-".$first_month."-".$day;
				$date_end = $year."-".$month."-".$day;
				$array = $connect->getAll("SELECT COUNT(*), id_obj FROM reckoning WHERE reason_delete=?i AND date>=?s AND date<=?s GROUP BY id_obj ORDER BY count(*) DESC LIMIT 10", $reason, $date_start, $date_end);
				foreach($array as $row){
					$id_obj = $row["id_obj"];
					$object = $connect->getOne("SELECT name FROM object WHERE id=?i", $id_obj);
					$data = array();
					for($i_day = $day; $i_day <= $max_day; $i_day++){
						$date = $first_year."-".$first_month."-".$i_day;
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE date=?s AND id_obj=?i AND reason_delete=?i", $date, $id_obj, $reason));
						$data[] = array($date, $count);
					}
					for($i_day = 1; $i_day <= $day; $i_day++){
						$date = $year."-".$month."-".$i_day;
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE date=?s AND id_obj=?i AND reason_delete=?i", $date, $id_obj, $reason));
						$data[] = array($date, $count);
					}
					$index++;
					$arr[] = array("label" => $object, "color" => $color[$index], "data" => $data);
				}
			}elseif($date_params == "week"){
				if($week == 0)
					$today = date('d-m-Y', mktime(0, 0, 0, $month, $day-6, $year));
				else
					$today = date('d-m-Y', mktime(0, 0, 0, $month, $day-($week - 1), $year));
				$t = explode("-", $today);
				$day = $t[0];
				$month = $t[1];
				$year = $t[2];
				$date_start = date(strToTime("-147 days"), "Y-m-d");
				$date_end = $year."-".$month."-".$day;
				$array = $connect->getAll("SELECT COUNT(*), id_obj FROM reckoning WHERE reason_delete=$reason AND date>='$date_start' AND date<='$date_end' GROUP BY id_obj ORDER BY count(*) DESC LIMIT 10");
				foreach($array as $row){
					$id_obj = $row['id_obj'];
					$object = $connect->getOne("SELECT name FROM object WHERE id=?i", $id_obj);
					$data = array();
					for($days = $day-147; $days < $day; $days = $days+7){
						$date_start = date('Y-m-d', mktime(0, 0, 0, $month, $days, $year));
						$date_end = date('Y-m-d', mktime(0, 0, 0, $month, $days+7, $year));
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE date>='$date_start' AND date<='$date_end' AND id_obj=".$id_obj." AND reason_delete=".$reason));
						$data[] = array($date_end, $count);
					}
					$index++;
					$arr[] = array("label" => $object, "color" => $color[$index], "data" => $data);
				}
			}elseif($date_params == "month"){
				$first_year = $year - 1;
				$date_start = $first_year."-".$month."-".$day;
				$date_end = $year."-".$month."-".$day;
				$array = $connect->getAll("SELECT COUNT(*), id_obj FROM reckoning WHERE reason_delete=$reason AND date>='$date_start' AND date<='$date_end' GROUP BY id_obj ORDER BY count(*) DESC LIMIT 10");
				foreach($array as $row){
					$id_obj = $row['id_obj'];
					$object = $connect->getOne("SELECT name FROM object WHERE id=?i", $id_obj);
					$data = array();
					for($i_month = $month; $i_month <= 12; $i_month++){
						$date_start_month = date($first_year.'-'.$i_month.'-1');
						$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $first_year));
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE date>='$date_start_month' AND date<='$date_end_month' AND id_obj=".$id_obj." AND reason_delete=".$reason));
						$data[] = array($first_year."-".$i_month, $count);
					}
					for($i_month = 1; $i_month <= $month; $i_month++){
						$date_start_month = date($year.'-'.$i_month.'-1');
						$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $year));
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE date>='$date_start_month' AND date<='$date_end_month' AND id_obj=".$id_obj." AND reason_delete=".$reason));
						$data[] = array($year."-".$i_month, $count);
					}
					$index++;
					$arr[] = array("label" => $object, "color" => $color[$index], "data" => $data);
				}
			}
		}
		if($arr)
			return json_encode($arr);
	}

	function get_data_for_graph_client($connect){

		global $color;

		$date_params = $_POST['date_params'];
		if($date_params == "day"){
			$day = date('d');
			$month = date('m');
			$year = date('Y');
			$first_year = $year;
			$first_month = $month - 1;
			if($month == 1){
				$first_month = 12;
				$first_year = $year - 1;
			}
			$max_day = cal_days_in_month(CAL_GREGORIAN, $first_month, $first_year);
			$data = array();
			for($i_day = $day; $i_day <= $max_day; $i_day++){
				$date = $first_year."-".$first_month."-".$i_day;
				$count = count($connect->getAll("SELECT id FROM klient WHERE date_reg=?s AND login!=''", $date));
				$data[] = array($date, $count);
			}
			for($i_day = 1; $i_day <= $day; $i_day++){
				$date = $year."-".$month."-".$i_day;
				$count = count($connect->getAll("SELECT id FROM klient WHERE date_reg=?s AND login!=''", $date));
				$data[] = array($date, $count);
			}
			$arr[] = array("label" => "Всего", "color" => $color[1], "data" => $data);

			$data = array();
			for($i_day = $day; $i_day <= $max_day; $i_day++){
				$date = $first_year."-".$first_month."-".$i_day;
				$count = count($connect->getAll("SELECT id FROM klient WHERE date_reg=?s AND login!='' AND active=1", $date));
				$data[] = array($date, $count);
			}
			for($i_day = 1; $i_day <= $day; $i_day++){
				$date = $year."-".$month."-".$i_day;
				$count = count($connect->getAll("SELECT id FROM klient WHERE date_reg=?s AND login!='' AND active=1", $date));
				$data[] = array($date, $count);
			}

			$arr[] = array("label" => "Активированных", "color" => $color[2], "data" => $data);

		}elseif($date_params == "week"){
			$day = date('d');
			$month = date('m');
			$year = date('Y');
			$week = date('w');
			if($week == 0)
				$today = date('d-m-Y', mktime(0, 0, 0, $month, $day-6, $year));
			else
				$today = date('d-m-Y', mktime(0, 0, 0, $month, $day-($week - 1), $year));
			$t = explode("-", $today);
			$day = $t[0];
			$month = $t[1];
			$year = $t[2];
			$data = array();
			for($days = $day-147; $days < $day; $days = $days+7){
				$date_start = date('Y-m-d', mktime(0, 0, 0, $month, $days, $year));
				$date_end = date('Y-m-d', mktime(0, 0, 0, $month, $days+7, $year));
				$count = count($connect->getAll("SELECT id FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!=''", $date_start, $date_end));
				$data[] = array($date_end, $count);
			}
			$arr[] = array("label" => "Всего", "color" => $color[1], "data" => $data);

			$data = array();
			for($days = $day-147; $days < $day; $days = $days+7){
				$date_start = date('Y-m-d', mktime(0, 0, 0, $month, $days, $year));
				$date_end = date('Y-m-d', mktime(0, 0, 0, $month, $days+7, $year));
				$count = count($connect->getAll("SELECT id FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!='' AND active=1", $date_start, $date_end));
				$data[] = array($date_end, $count);
			}
			$arr[] = array("label" => "Активированных", "color" => $color[2], "data" => $data);

		}elseif($date_params == "month"){
			$month = date('m');
			$year = date('Y');
			$first_year = $year - 1;
			$data = array();
			for($i_month = $month; $i_month <= 12; $i_month++){
				$date_start_month = date($first_year.'-'.$i_month.'-1');
				$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $first_year));
				$count = count($connect->getAll("SELECT id FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!=''", $date_start_month, $date_end_month));
				$data[] = array($first_year."-".$i_month, $count);
			}
			for($i_month = 1; $i_month <= $month; $i_month++){
				$date_start_month = date($year.'-'.$i_month.'-1');
				$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $year));
				$count = count($connect->getAll("SELECT id FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!=''", $date_start_month, $date_end_month));
				$data[] = array($year."-".$i_month, $count);
			}
			$arr[] = array("label" => "Всего", "color" => $color[1], "data" => $data);

			$data = array();
			for($i_month = $month; $i_month <= 12; $i_month++){
				$date_start_month = date($first_year.'-'.$i_month.'-1');
				$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $first_year));
				$count = count($connect->getAll("SELECT id FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!='' AND active=1", $date_start_month, $date_end_month));
				$data[] = array($first_year."-".$i_month, $count);
			}
			for($i_month = 1; $i_month <= $month; $i_month++){
				$date_start_month = date($year.'-'.$i_month.'-1');
				$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $year));
				$count = count($connect->getAll("SELECT id FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!='' AND active=1", $date_start_month, $date_end_month));
				$data[] = array($year."-".$i_month, $count);
			}
			$arr[] = array("label" => "Активированных", "color" => $color[2], "data" => $data);

		}
		$all = count($connect->getAll("SELECT id FROM klient WHERE login!=''"));
		$active = count($connect->getAll("SELECT id FROM klient WHERE login!='' AND active=1"));
		$html = "Всего зарегистрированно: <strong>".$all."</strong><br />";
		$html.= "Из них активированно: <strong>".$active."</strong>";
		$array = array('data' => $arr, 'html' => $html);
		return json_encode($array);

	}

?>
