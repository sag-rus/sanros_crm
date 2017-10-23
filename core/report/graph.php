<?php

function show_graphics_report_menu(){
	ob_start();
?>
<div class="btn-group small-menu-report">
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-bid" onclick="graph_current()"><i class="fa fa-clock-o"></i> Заявки</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-call-back" onclick="graph_call_back()"><i class="fa fa-phone"></i> Заказы звонка</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-cancel" onclick="graph_by_cancel()"><i class="fa fa-times"></i> Аннуляции</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-client" onclick="graph_by_client()"><i class="fa fa-user"></i> Личный кабинет</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-rating" onclick="graph_by_rating()"><i class="fa fa-comments-o"></i> Отзывы</button>
	</div>
</div>
<div id="panel" style="margin-top: 10px"></div>
<div id="placeholder"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function graph_call_back(){
	global $array_month;
	$year_select = "";
	$month_select = "";
	for($i = 2013; $i <= date("Y"); $i++)
		$year_select.= "<option value='".$i."'>".$i."</option>";
	foreach($array_month as $key => $month)
		$month_select.= "<option value='".$key."'>".$month."</option>";
?>
<div class="form-horizontal">
	<div class="form-group">
		<div class="col-sm-5">
			<div class="form-group">
				<label class="col-sm-3 control-label control-label-padding">Диапазон</label>
				<div class="col-sm-9 btn-group range-graph" data-toggle="buttons">
					<label class="btn btn-primary btn-xs active">
						<input type="radio" checked name="range" value="day" onChange="change_status_graph('date')" />&nbsp;дни&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="range" value="week" onChange="change_status_graph('date')" />&nbsp;недели&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="range" value="month" onChange="change_status_graph('date')" />&nbsp;месяцы&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="range" value="my-date" onChange="change_status_graph('date')" />&nbsp;свои даты&nbsp;
					</label>
				</div>
			</div>
			<div class="form-group my-date-ranges" style="display: none">
				<label class="col-sm-3 control-label control-label-padding">Даты</label>
				<div class="col-sm-9">
					<div class="form-inline">
						от <select class="form-control input-sm first-month-date"><?php echo $month_select; ?></select>
						<select class="form-control input-sm first-year-date"><?php echo $year_select; ?></select>
					</div>
					<div class="form-inline">
						до <select class="form-control input-sm second-month-date"><?php echo $month_select; ?></select>
						<select class="form-control input-sm second-year-date"><?php echo $year_select; ?></select>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label control-label-padding">Тип заявки</label>
				<div class="col-sm-9 btn-group type-graph" data-toggle="buttons">
					<label class="btn btn-primary btn-xs active">
						<input type="radio" checked name="type" value="all" />&nbsp;все&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="type" value="site" />&nbsp;сайт&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="type" value="module" />&nbsp;модуль&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="type" value="chat" />&nbsp;чат&nbsp;
					</label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label control-label-padding">Статус</label>
				<div class="col-sm-9 btn-group status-graph" data-toggle="buttons">
					<label class="btn btn-primary btn-xs active">
						<input type="radio" checked name="status" value="all" />&nbsp;суммарные&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="status" value="1" />&nbsp;в работе&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="status" value="3" />&nbsp;удаленные&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="status" value="2" />&nbsp;успешные&nbsp;
					</label>
				</div>
			</div>
			<div class="form-group form-group-margin">
				<div class="col-sm-3"></div>
				<div class="col-sm-9">
					<button type="button" class="btn btn-success btn-form-graph" onclick="get_data_for_graph_call_back()" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Формирование графика..."><i class="fa fa-area-chart"></i> Сформировать</button>
				</div>
			</div>
		</div>
		<div class="col-sm-4"></div>
		<div class="col-sm-3 text-right">
			<div class="btn-group type-chart-graph" data-toggle="buttons">
				<label class="btn btn-danger btn-sm active">
					<input type="radio" checked name="type-chart" value="line" />&nbsp;<i class="fa fa-line-chart"></i>&nbsp;
				</label>
				<label class="btn btn-danger btn-sm">
					<input type="radio" name="type-chart" value="column" />&nbsp;<i class="fa fa-bar-chart"></i>&nbsp;
				</label>
				<label class="btn btn-danger btn-sm">
					<input type="radio" name="type-chart" value="area" />&nbsp;<i class="fa fa-area-chart"></i>&nbsp;
				</label>
			</div>
		</div>
	</div>
</div>
<?php
}

function graph_cancel($connect){
	ob_start();
?>
<div class="form-horizontal">
	<div class="form-group">
		<div class="col-sm-5">
			<div class="form-group">
				<label class="col-sm-3 control-label">Диапазон</label>
				<div class="col-sm-9 btn-group range-graph" data-toggle="buttons">
					<label class="btn btn-primary btn-xs active">
						<input type="radio" checked name="range" value="day" />&nbsp;по дням&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="range" value="week" />&nbsp;по неделям&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="range" value="month" />&nbsp;по месяцам&nbsp;
					</label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Выбор</label>
				<div class="col-sm-9 btn-group choice-graph" data-toggle="buttons">
					<label class="btn btn-primary btn-xs active">
						<input type="radio" checked name="choice" value="current" />&nbsp;общий&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="choice" value="reason" />&nbsp;по 10 объектам&nbsp;
					</label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Объект</label>
				<div class="col-sm-9" id="object_name">
					<input type="text" class="form-control id-object" id="object" onkeyup="find_klient('object', 'object', 'use_object')" onblur="verification_input_data('object', '1');" name="">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-3"></div>
				<div class="col-sm-9">
					<button type="button" class="btn btn-success btn-form-graph" disabled onclick="get_data_for_graph_cancel()"><i class="fa fa-area-chart"></i> Сформировать</button>
				</div>
			</div>
		</div>
		<div class="col-sm-4" id="reason-delete">
			<?php echo get_checkbox_table($connect, "reason_delete"); ?>
		</div>
		<div class="col-sm-3" style="text-align: right">
			<div class="btn-group type-chart-graph" data-toggle="buttons">
				<label class="btn btn-danger btn-sm active">
					<input type="radio" checked name="type-chart" value="line" />&nbsp;<i class="fa fa-line-chart"></i>&nbsp;
				</label>
				<label class="btn btn-danger btn-sm">
					<input type="radio" name="type-chart" value="column" />&nbsp;<i class="fa fa-bar-chart"></i>&nbsp;
				</label>
				<label class="btn btn-danger btn-sm">
					<input type="radio" name="type-chart" value="area" />&nbsp;<i class="fa fa-area-chart"></i>&nbsp;
				</label>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function graph_client($connect){
	$all = $connect->getOne("SELECT COUNT(*) FROM klient WHERE login!=''");
	$active = $connect->getOne("SELECT COUNT(*) FROM klient WHERE login!='' AND active=1");
	ob_start();
?>
<div class="form-horizontal">
	<div class="form-group">
		<div class="col-sm-5">
			<div class="form-group">
				<label class="col-sm-3 control-label">Аккаунтов</label>
				<div class="col-sm-9">
					<div class="well-sm alert-info"><?php echo $all; ?></div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Активиронных</label>
				<div class="col-sm-9">
					<div class="well-sm alert-success"><?php echo $active; ?></div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Диапазон</label>
				<div class="col-sm-9 btn-group range-graph" data-toggle="buttons">
					<label class="btn btn-primary btn-xs active">
						<input type="radio" checked name="range" value="day" />&nbsp;по дням&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="range" value="week" />&nbsp;по неделям&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="range" value="month" />&nbsp;по месяцам&nbsp;
					</label>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-3"></div>
				<div class="col-sm-9">
					<button type="button" class="btn btn-success" onclick="get_data_for_graph_client()"><i class="fa fa-area-chart"></i> Сформировать</button>
				</div>
			</div>
		</div>
		<div class="col-sm-4"></div>
		<div class="col-sm-3 right">
			<div class="btn-group type-chart-graph" data-toggle="buttons">
				<label class="btn btn-danger btn-sm active">
					<input type="radio" checked name="type-chart" value="line" />&nbsp;<i class="fa fa-line-chart"></i>&nbsp;
				</label>
				<label class="btn btn-danger btn-sm">
					<input type="radio" name="type-chart" value="column" />&nbsp;<i class="fa fa-bar-chart"></i>&nbsp;
				</label>
				<label class="btn btn-danger btn-sm">
					<input type="radio" name="type-chart" value="area" />&nbsp;<i class="fa fa-area-chart"></i>&nbsp;
				</label>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}
	
function get_data_for_graph_current($connect){

	global $color, $array_month, $array_short_month;

	$arr = array();
	$arr_status = json_decode($_POST["status"], TRUE);
	$status_delete = json_decode($_POST["status_delete"], TRUE);
	$status_name = get_status_array($connect, "status");
	$status_name["all"] = "Всего заявок";
	$i = 0;
	$int = 1;

	$date_params = $_POST["date_params"];
	$span_params = $_POST["span_params"];
	$choice_params = $_POST["choice_params"];
	$value_params = $_POST["value_params"];
	$choice_data = $_POST["choice_data"];
	$source = $_POST["source"];
	if($choice_params == "object")
		$parametr = explode("_", $_POST["id_obj"]);
	elseif($choice_params == "region")
			$parametr = explode("_", $_POST["id_reg"]);
	elseif($choice_params == "manager")
		$parametr = explode("_", $_POST["manager"]);
	elseif($choice_params == "office")
		$parametr = $connect->getAll("SELECT id FROM office");
	else
		$parametr = array(1 => 1);
	$parametr = array_diff($parametr, array(""));

	foreach($parametr as $p){
		$place = "";
		if($choice_params == "object"){
			$place = "AND (id_obj=".$p.")";
			$label = get_object($connect, $p);
		}elseif($choice_params == "region"){
			$place = get_objects_by_region($connect, $p);
			$label = $connect->getOne("SELECT name FROM region WHERE id=?i", $p);
		}elseif($choice_params == "manager"){
			$place = " AND (id_user=".$p.")";
			$label = $connect->getOne("SELECT name FROM users WHERE id=?i", $p);
		}elseif($choice_params == "office"){
			$id_office = $p["id"];
			$data = $connect->getAll("SELECT id FROM users WHERE office=?i", $id_office);
			$add = "";
			foreach($data as $row){
				if($add)
					$add.= " OR ";
				$add.= " id_user=".$row["id"];
			}
			$place = " AND (".$add.")";
			$label = $connect->getOne("SELECT name FROM office WHERE id=?i", $id_office);
		}
		if(!in_array("delete", $status_delete))
			$place.= " AND active != 3 ";
		elseif(in_array("delete", $status_delete) AND !in_array("no-delete", $status_delete))
			$place.= " AND active = 3 ";
		if($source)
			$place.= " AND source = ".$source;

		if($span_params == "day"){

				$arr["title"] = "Статистика по дням";
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
				foreach($arr_status as $status){
					$query_status = "";
					if($status != "all")
						$query_status = " AND status=$status ";
					$data = array();
					$categories = array();
					for($i_day = $day; $i_day <= $max_day; $i_day++){
						$date = $first_year."-".$first_month."-".$i_day;
						$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params."=?s ".$query_status.$place, $date);
						if($choice_data == "percent"){
							$all_count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params."='$date' ".$place);
							$count = round((($count/$all_count)*100), 2);
							$arr["max"] = 100;
						}
						$categories[] = $date;
						$data[] = (float)$count;
					}
					for($i_day = 1; $i_day <= $day; $i_day++){
						$date = $year."-".$month."-".$i_day;
						$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params."=?s ".$query_status.$place, $date);
						if($choice_data == "percent"){
							$all_count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params."='$date' ".$place);
							$count = round((($count/$all_count)*100), 2);
							$arr["max"] = 100;
						}
						$categories[] = $date;
						$data[] = (float)$count;
					}
					$index++;
					$arr["data"][] = array("name" => $label." ".$status_name[$status], "data" => $data);
				}
				foreach($categories as $index => $date)
					$arr["categories"][$index] = date_transform($date);

			}elseif($span_params == "week"){

				$arr["title"] = "Статистика по неделям";
				$day = date("d");
				$month = date("m");
				$year = date("Y");
				$week = date("w");
				foreach($arr_status as $status){
					$query_status = "";
					if($status != "all")
						$query_status = " AND status=$status ";
					$data = array();
					$categories = array();
					for($days = $day-(147+$week-1); $days < ($day-$week-1); $days = $days+7){
						$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $days, $year));
						$date_end = date("Y-m-d", mktime(0, 0, 0, $month, $days+6, $year));
						$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$query_status.$place, $date_start, $date_end);
						if($value_params == "middle" AND $choice_data == "number")
							$count = round($count / 7, 2);
						if($choice_data == "percent"){
							$all_count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$place, $date_start, $date_end);
							$count = round((($count/$all_count)*100), 2);
							$arr["max"] = 100;
						}
						$data[] = (float)$count;
						$categories[] = $date_start;
					}
					if($week != 0){
						$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $day-$week+1, $year));
						$date_end = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
						$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$query_status.$place, $date_start, $date_end);
						if($value_params == "middle" AND $choice_data == "number")
							$count = round($count / $week, 2);
						if($choice_data == "percent"){
							$all_count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$place, $date_start, $date_end);
							$count = round((($count/$all_count)*100), 2);
							$arr["max"] = 100;
						}
						$data[] = (float)$count;
						$categories[] = $date_start;
					}
					$arr["data"][] = array("name" => $label." ".$status_name[$status], "data" => $data);
				}
				foreach($categories as $index => $date)
					$arr["categories"][$index] = date_transform($date);

			}elseif($span_params == "month"){

				$arr["title"] = "Статистика по месецам";
				$day = date("d");
				$month = date("m");
				$year = date("Y");
				$first_year = $year - 1;
				foreach($arr_status as $status){
					$data = array();
					$categories = array();
					$query_status = "";
					if($status != "all")
						$query_status = " AND status=$status ";
					for($i_month = $month; $i_month <= 12; $i_month++){
						$date_start_month = date($first_year."-".$i_month."-1");
						$date_end_month = date("Y-m-d", mktime(0, 0, 0, $i_month+1, 0, $first_year));
						$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$query_status.$place, $date_start_month, $date_end_month);
						if($value_params == "middle" AND $choice_data == "number")
							$count = round($count / cal_days_in_month(CAL_GREGORIAN, $i_month, $first_year), 2);
						if($choice_data == "percent"){
							$all_count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$place, $date_start_month, $date_end_month);
							$count = round((($count/$all_count)*100), 2);
							$arr["max"] = 100;
						}
						$data[] = (float)$count;
						$categories[] = $array_month[(int)$i_month];
					}
					for($i_month = 1; $i_month <= $month - 1; $i_month++){
						$date_start_month = date($year."-".$i_month."-1");
						$date_end_month = date("Y-m-d", mktime(0, 0, 0, $i_month+1, 0, $year));
						$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$query_status.$place, $date_start_month, $date_end_month);
						if($value_params == "middle" AND $choice_data == "number")
							$count = round($count / cal_days_in_month(CAL_GREGORIAN, $i_month, $first_year), 2);
						if($choice_data == "percent"){
							$all_count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$place, $date_start_month, $date_end_month);
							$count = round((($count/$all_count)*100), 2);
							$arr["max"] = 100;
						}
						$data[] = (float)$count;
						$categories[] = $array_month[(int)$i_month];
					}
					$date_start_month = date($year."-".$month."-1");
					$date_end_month = $year."-".$month."-".$day;
					$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$query_status.$place, $date_start_month, $date_end_month);
					if($value_params == "middle" AND $choice_data == "number")
						$count = round($count / $day, 2);
					if($choice_data == "percent"){
						$all_count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$place, $date_start_month, $date_end_month);
						$count = round((($count/$all_count)*100), 2);
					}
					$data[] = (float)$count;
					$categories[] = $array_month[(int)$i_month];

					$arr["data"][] = array("name" => $label." ".$status_name[$status], "data" => $data);
				}
				foreach($categories as $index => $date)
					$arr["categories"][$index] = $date;

			}elseif($span_params == "my-date"){

				$month = $_POST["month1"];
				$year = $_POST["year1"];
				$month2 = $_POST["month2"];
				$year2 = $_POST["year2"];

				$current_month = date("m");
				$current_year = date("Y");

				foreach($arr_status as $status){
					$i_month = $month;
					$i_year = $year;
					$data = array();
					$categories = array();
					while(($i_year == $year2 AND $i_month <= $month2) OR $i_year < $year2){
						$max_day = cal_days_in_month(CAL_GREGORIAN, $i_month, $i_year);
						$date1 = $i_year."-".$i_month."-1";
						$date2 = $i_year."-".$i_month."-".$max_day;
						$query_status = "";
						if($status != "all")
							$query_status = " AND ".$type_status."=$status ";
						$count = count($connect->getAll("SELECT id FROM reckoning WHERE (".$date_params." >= '$date1' AND ".$date_params." <= '$date2') ".$query_status.$place));
						if($choice_data == "percent"){
							$all_count = count($connect->getAll("SELECT id FROM reckoning WHERE (".$date_params." >= '$date1' AND ".$date_params." <= '$date2') ".$place));
							$count = round((($count/$all_count)*100), 2);
						}
						if($value_params == "middle" AND $choice_data == "number"){
							if($current_month == $i_month AND $current_year == $i_year)
								$count = round($count / date("d"), 2);
							else
								$count = round($count / $max_day, 2);
						}
						$data[] = (float)$count;
						$categories[] = $array_short_month[(int)$i_month]." ".$i_year;
						$i_month++;
						if($i_month > 12){
							$i_month = 1;
							$i_year++;
						}
					}
					$arr["data"][] = array("name" => $label." ".$status_name[$status], "data" => $data);
				}
				foreach($categories as $index => $date)
					$arr["categories"][$index] = $date;
			}
		}
		if($arr)
			return json_encode($arr);

	}

function get_data_for_graph_call_back($connect){

	global $color, $array_month, $array_short_month;

	$arr = array();
	$status = $_POST["status"];
	$type = $_POST["type"];
	$range = $_POST["range"];

	$label = "Заявки";

	$query = "";
	if($type != "all")
		$query = " type='".$type."' ";
	if($status != "all"){
		if($query != "")
			$query.= " AND ";
		$query = $query." active=$status ";
	}

	if($range == "day"){

		$arr["title"] = "Статистика по дням";
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
		$data = array();
		$categories = array();
		for($i_day = $day; $i_day <= $max_day; $i_day++){
			$date = $first_year."-".$first_month."-".$i_day;
			$zapros = "";
			if($query != "")
				$zapros = $query." AND ";
			$zapros.= " DATE(time)='".$date."'";
			$count = $connect->getOne("SELECT COUNT(*) FROM order_call_back WHERE ".$zapros);
			$categories[] = $date;
			$data[] = (float)$count;
		}
		for($i_day = 1; $i_day <= $day; $i_day++){
			$date = $year."-".$month."-".$i_day;
			$zapros = "";
			if($query != "")
				$zapros = $query." AND ";
			$zapros.= " DATE(time)='".$date."'";
			$count = $connect->getOne("SELECT COUNT(*) FROM order_call_back WHERE ".$zapros);
			$categories[] = $date;
			$data[] = (float)$count;
		}
		$index++;
		$arr["data"][] = array("name" => $label, "data" => $data);
		foreach($categories as $index => $date)
			$arr["categories"][$index] = date_transform($date);

	}elseif($range == "week"){

		$arr["title"] = "Статистика по неделям";
		$day = date("d");
		$month = date("m");
		$year = date("Y");
		$week = date("w");
		$data = array();
		$categories = array();
		for($days = $day-(147+$week-1); $days < ($day-$week-1); $days = $days+7){
			$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $days, $year));
			$date_end = date("Y-m-d", mktime(0, 0, 0, $month, $days+6, $year));
			$zapros = "";
			if($query != "")
				$zapros = $query." AND ";
			$zapros.= " (DATE(time)>='".$date_start."' AND DATE(time)<='".$date_end."') ";
			$count = $connect->getOne("SELECT COUNT(*) FROM order_call_back WHERE ".$zapros);
			$data[] = (float)$count;
			$categories[] = $date_start;
		}
		if($week != 0){
			$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $day-$week+1, $year));
			$date_end = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
			$zapros = "";
			if($query != "")
				$zapros = $query." AND ";
			$zapros.= " (DATE(time)>='".$date_start."' AND DATE(time)<='".$date_end."') ";
			$count = $connect->getOne("SELECT COUNT(*) FROM order_call_back WHERE ".$zapros);
			$data[] = (float)$count;
			$categories[] = $date_start;
		}
		$arr["data"][] = array("name" => $label, "data" => $data);
		foreach($categories as $index => $date)
			$arr["categories"][$index] = date_transform($date);

	}elseif($range == "month"){

		$arr["title"] = "Статистика по месецам";
		$day = date("d");
		$month = date("m");
		$year = date("Y");
		$first_year = $year - 1;
		$data = array();
		$categories = array();
		for($i_month = $month; $i_month <= 12; $i_month++){
			$date_start_month = date($first_year."-".$i_month."-1");
			$date_end_month = date("Y-m-d", mktime(0, 0, 0, $i_month+1, 0, $first_year));
			$zapros = "";
			if($query != "")
				$zapros = $query." AND ";
			$zapros.= " (DATE(time)>='".$date_start_month."' AND DATE(time)<='".$date_end_month."') ";
			$count = $connect->getOne("SELECT COUNT(*) FROM order_call_back WHERE ".$zapros);
			$data[] = (float)$count;
			$categories[] = $array_month[(int)$i_month];
		}
		for($i_month = 1; $i_month <= $month - 1; $i_month++){
			$date_start_month = date($year."-".$i_month."-1");
			$date_end_month = date("Y-m-d", mktime(0, 0, 0, $i_month+1, 0, $year));
			$zapros = "";
			if($query != "")
				$zapros = $query." AND ";
			$zapros.= " (DATE(time)>='".$date_start_month."' AND DATE(time)<='".$date_end_month."') ";
			$count = $connect->getOne("SELECT COUNT(*) FROM order_call_back WHERE ".$zapros);
			$data[] = (float)$count;
			$categories[] = $array_month[(int)$i_month];
		}
		$date_start_month = date($year."-".$month."-1");
		$date_end_month = $year."-".$month."-".$day;
		$zapros = "";
		if($query != "")
			$zapros = $query." AND ";
		$zapros.= " (DATE(time)>='".$date_start_month."' AND DATE(time)<='".$date_end_month."') ";
		$count = $connect->getOne("SELECT COUNT(*) FROM order_call_back WHERE ".$zapros);
		$data[] = (float)$count;
		$categories[] = $array_month[(int)$i_month];

		$arr["data"][] = array("name" => $label, "data" => $data);

		foreach($categories as $index => $date)
			$arr["categories"][$index] = $date;

	}elseif($range == "my-date"){

		$month = $_POST["month1"];
		$year = $_POST["year1"];
		$month2 = $_POST["month2"];
		$year2 = $_POST["year2"];

		$current_month = date("m");
		$current_year = date("Y");

		$i_month = $month;
		$i_year = $year;
		$data = array();
		$categories = array();
		while(($i_year == $year2 AND $i_month <= $month2) OR $i_year < $year2){
			$max_day = cal_days_in_month(CAL_GREGORIAN, $i_month, $i_year);
			$date1 = $i_year."-".$i_month."-1";
			$date2 = $i_year."-".$i_month."-".$max_day;
			$zapros = "";
			if($query != "")
				$zapros = $query." AND ";
			$zapros.= " (DATE(time)>='".$date1."' AND DATE(time)<='".$date2."') ";
			$count = $connect->getOne("SELECT COUNT(*) FROM order_call_back WHERE ".$zapros);
			$data[] = (float)$count;
			$categories[] = $array_short_month[(int)$i_month]." ".$i_year;
			$i_month++;
			if($i_month > 12){
				$i_month = 1;
				$i_year++;
			}
		}
		$arr["data"][] = array("name" => $label, "data" => $data);
		foreach($categories as $index => $date)
			$arr["categories"][$index] = $date;
	}
	if($arr)
		return json_encode($arr);

}

function get_data_for_pie_current($connect){

	global $color;

	$day = date("d");
	$month = date("m");
	$year = date("Y");
	$week = date("w");

	$arr = array();
	$status = $_POST["status"];
	$arr_status = explode("_", $status);
	$arr_status = array_diff($arr_status, array(""));
	$type_status = $_POST["type_status"];
	$status_name = get_status_array($connect, $type_status);
	$status_name["all"] = "Всего заявок";
	$i = 0;
	$int = 1;


	$graph = $_POST["graph"];
	$graph = "status";
	$date_params = $_POST["date_params"];
	$span_params = $_POST["span_params"];
	$choice_params = $_POST["choice_params"];
	if($choice_params == "object")
		$parametr = explode("_", $_POST["id_obj"]);
	elseif($choice_params == 'region')
		$parametr = explode("_", $_POST["id_reg"]);
	elseif($choice_params == 'manager')
		$parametr = explode("_", $_POST["manager"]);
	unset($status_name["all"]);

	$parametr = array_diff($parametr, array(""));
	$p = $parametr[0];

	if($choice_params == "object"){
		$place = "AND (id_obj=$p)";
		$label = get_object($connect, $p);
	}elseif($choice_params == "region"){
		$place = get_objects_by_region($connect, $p);
		$label = $connect->getOne("SELECT name FROM region WHERE id=?i", $p);
	}elseif($choice_params == "manager"){
		$place = " AND (id_user=$p)";
		$label = $connect->getOne("SELECT name FROM users WHERE id=?i", $p);
	}
	$place.= " AND active != 3";

	if($span_params == "day"){
		$first_year = $year;
		$first_month = $month - 1;
		if($month == 1){
			$first_month = 12;
			$first_year = $year - 1;
		}
		$max_day = cal_days_in_month(CAL_GREGORIAN, $first_month, $first_year);
		if($graph == "status"){

			foreach($status_name as $id_status => $status){
				$query_status = " AND ".$type_status."=$id_status ";
				$date_start = $first_year."-".$first_month."-".$day;
				$date_end = $year."-".$month."-".$day;
				$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params.">=?s AND ".$date_params."<=?s ".$query_status.$place, $date_start, $date_end);
				$arr["data"][] = array($label." ".$status, (int)$count);
			}

		}elseif($graph == "object"){
			$data = $connect->getAll("SELECT id, name FROM object WHERE id_reg=?i", $p);
			foreach($data as $row){
				$id_obj = $row["id"];
				$object = $row["name"];
				$count = 0;
				$query_status = " AND id_obj=$id_obj ";
				for($i_day = $day; $i_day <= $max_day; $i_day++){
					$date = $first_year."-".$first_month."-".$i_day;
					$count+= $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params."=?s ".$query_status.$place, $date);
				}
				for($i_day = 1; $i_day <= $day; $i_day++){
					$date = $year."-".$month."-".$i_day;
					$count+= $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params."=?s ".$query_status.$place, $date);
				}
				$arr["data"][] = array($label." ".$status, (int)$count);
			}
		}

	}elseif($span_params == "week"){

		if($graph == "status"){

			foreach($status_name as $id_status => $status){
				$query_status = " AND ".$type_status."=$id_status ";
				$count = 0;
				$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $day-147, $year));
				$date_end = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
				$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$query_status.$place, $date_start, $date_end);
				$arr["data"][] = array($label." ".$status, (int)$count);
			}

		}elseif($graph == "object"){
			$data = $connect->getAll("SELECT id, name FROM object WHERE id_reg=?i", $p);
			foreach($data as $row){
				$id_obj = $row["id"];
				$object = $row["name"];
				$count = 0;
				$query_status = " AND id_obj=$id_obj ";
				for($days = $day-147; $days < $day; $days = $days+7){
					$date_start = date('Y-m-d', mktime(0, 0, 0, $month, $days, $year));
					$date_end = date('Y-m-d', mktime(0, 0, 0, $month, $days+7, $year));
					$count+= $connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= '$date_start' AND ".$date_params." <= '$date_end' ".$query_status.$place);
				}
				$index++;
				$arr[] = array("label" => $label." ".$object, "color" => $color[$index], "data" => $count);
			}
		}

	}elseif($span_params == "month"){

		$first_year = $year - 1;
		if($graph == "status"){
			foreach($status_name as $id_status => $status){
				$count = 0;
				$query_status = " AND ".$type_status."=$id_status ";
				$date_start_month = $first_year."-".$month."-1";
				$date_end_month = $year."-".$month."-1";
				$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE (".$date_params." >= ?s AND ".$date_params." <= ?s) ".$query_status.$place, $date_start_month, $date_end_month);
				$arr["data"][] = array($label." ".$status, (int)$count);
			}

		}elseif($graph == "object"){

			$data = $connect->getAll("SELECT id, name FROM object WHERE id_reg=?i", $p);
			foreach($data as $row){
				$id_obj = $row["id"];
				$object = $row["name"];
				$count = 0;
				$query_status = " AND id_obj='$id_obj' ";
				for($i_month = $month; $i_month <= 12; $i_month++){
					$date_start_month = date($first_year.'-'.$i_month.'-1');
					$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $first_year));
					$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$query_status.$place, $date_start_month, $date_end_month));
				}
				for($i_month = 1; $i_month <= $month; $i_month++){
					$date_start_month = date($year.'-'.$i_month.'-1');
					$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $year));
					$count+= count($connect->getAll("SELECT id FROM reckoning WHERE ".$date_params." >= ?s AND ".$date_params." <= ?s ".$query_status.$place, $date_start_month, $date_end_month));
				}
				$index++;
				$arr[] = array("label" => $label." ".$object, "color" => $color[$index], "data" => $count);
			}
		}
	}
	return json_encode($arr);
}

function get_data_for_graph_cancel($connect){

	global $array_month;

	$reason_array = get_status_array($connect, "reason_delete");
	$date_params = $_POST["date_params"];
	$type = $_POST["type"];
	$id_obj = $_POST["id_obj"];
	$reason = array_diff(explode("_", $_POST["reason"]), array(""));
	
	$zapros = " (active=3 OR status=6 OR status=8) ";
	if($id_obj)
		$zapros.= " AND id_obj=$id_obj ";

	if($type == "current"){

		if($date_params == "day"){

			$arr["title"] = "Статистика по аннуляциям";
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
				$data = array();
				$categories = array();
				for($i_day = $day; $i_day <= $max_day; $i_day++){
					$date = $first_year."-".$first_month."-".$i_day;
					$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE date=?s AND ".$zapros.$status_str, $date);
					$data[] = (int)$count;
					$categories[] = $date;
				}
				for($i_day = 1; $i_day <= $day; $i_day++){
					$date = $year."-".$month."-".$i_day;
					$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE date=?s AND ".$zapros.$status_str, $date);
					$data[] = (int)$count;
					$categories[] = $date;
				}
				$arr["data"][] = array("name" => $reason_array[$status], "data" => $data);
			}
			foreach($categories as $index => $date)
				$arr["categories"][$index] = date_transform($date);

		}elseif($date_params == "week"){

			$arr["title"] = "Статистика по аннуляциям";
			$day = date("d");
			$month = date("m");
			$year = date("Y");
			$week = date("w");
			foreach($reason as $id_reason => $status){
				$status_str =  " AND reason_delete=".$status;
				if($status == "all")
					$status_str = "";
				$data = array();
				$categories = array();
				for($days = $day-(147+$week-1); $days < ($day-$week-1); $days = $days+7){
					$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $days, $year));
					$date_end = date("Y-m-d", mktime(0, 0, 0, $month, $days+6, $year));
					$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE date>=?s AND date<=?s AND ".$zapros.$status_str, $date_start, $date_end);
					$categories[] = $date_start;
					$data[] = (int)$count;
				}
				if($week != 0){
					$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $day-$week+1, $year));
					$date_end = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
					$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE date>=?s AND date<=?s AND ".$zapros.$status_str, $date_start, $date_end);
					$categories[] = $date_start;
					$data[] = (int)$count;
					$arr["data"][] = array("name" => $reason_array[$status], "data" => $data);
				}
			}
			foreach($categories as $index => $date)
				$arr["categories"][$index] = date_transform($date);

		}elseif($date_params == "month"){

			$arr["title"] = "Статистика по аннуляциям";
			$month = date("m");
			$year = date("Y");
			$day = date("d");
			$first_year = $year - 1;
			foreach($reason as $id_reason => $status){
				$status_str =  " AND reason_delete=".$status;
				$data = array();
				$categories = array();
				for($i_month = $month; $i_month <= 12; $i_month++){
					$date_start_month = $first_year."-".$i_month."-1";
					$date_end_month = date("Y-m-d", mktime(0, 0, 0, $i_month+1, 0, $first_year));
					$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE date>=?s AND date<=?s AND ".$zapros.$status_str, $date_start_month, $date_end_month);
					$categories[] = $array_month[(int)$i_month];
					$data[] = (int)$count;
				}
				for($i_month = 1; $i_month <= $month-1; $i_month++){
					$date_start_month = $year."-".$i_month."-1";
					$date_end_month = date("Y-m-d", mktime(0, 0, 0, $i_month+1, 0, $year));
					$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE date>=?s AND date<=?s AND ".$zapros.$status_str, $date_start_month, $date_end_month);
					$categories[] = $array_month[(int)$i_month];
					$data[] = (int)$count;
				}
				$date_start_month = $year."-".$month."-1";
				$date_end_month = $year."-".$month."-".$day;
				$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE date>=?s AND date<=?s AND ".$zapros.$status_str, $date_start_month, $date_end_month);
				$categories[] = $array_month[(int)$month];
				$data[] = (int)$count;
				$arr["data"][] = array("name" => $reason_array[$status], "data" => $data);
			}
			foreach($categories as $index => $date)
				$arr["categories"][$index] = $date;
		}

	}elseif($type == "reason"){

		$arr["title"] = "Статистика по аннуляциям";
		$day = date("d");
		$month = date("m");
		$year = date("Y");
		$week = date("w");
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
				$categories = array();
				for($i_day = $day; $i_day <= $max_day; $i_day++){
					$date = $first_year."-".$first_month."-".$i_day;
					$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE date=?s AND id_obj=?i AND reason_delete=?i", $date, $id_obj, $reason);
					$categories[] = $date;
					$data[] = (int)$count;
				}
				for($i_day = 1; $i_day <= $day; $i_day++){
					$date = $year."-".$month."-".$i_day;
					$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE date=?s AND id_obj=?i AND reason_delete=?i", $date, $id_obj, $reason);
					$categories[] = $date;
					$data[] = (int)$count;
				}
				$arr["data"][] = array("name" => $object, "data" => $data);
			}
			foreach($categories as $index => $date)
				$arr["categories"][$index] = date_transform($date);

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

	global $array_month;

	$date_params = $_POST["date_params"];

	$day = date("d");
	$month = date("m");
	$year = date("Y");
	$week = date("w");

	if($date_params == "day"){

		$first_year = $year;
		$first_month = $month - 1;
		if($month == 1){
			$first_month = 12;
			$first_year = $year - 1;
		}
		$max_day = cal_days_in_month(CAL_GREGORIAN, $first_month, $first_year);
		$data = array();
		$categories = array();
		for($i_day = $day; $i_day <= $max_day; $i_day++){
			$date = $first_year."-".$first_month."-".$i_day;
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg=?s AND login!=''", $date);
			$data["all"][] = (int)$count;
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg=?s AND login!='' AND active=1", $date);
			$data["active"][] = (int)$count;
			$categories[] = $date;
		}
		for($i_day = 1; $i_day <= $day; $i_day++){
			$date = $year."-".$month."-".$i_day;
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg=?s AND login!=''", $date);
			$data["all"][] = (int)$count;
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg=?s AND login!='' AND active=1", $date);
			$data["active"][] = (int)$count;
			$categories[] = $date;
		}

		$arr["data"][] = array("name" => "Всего", "data" => $data["all"]);
		$arr["data"][] = array("name" => "Активированных", "data" => $data["active"]);
		foreach($categories as $index => $date)
			$arr["categories"][$index] = date_transform($date);

	}elseif($date_params == "week"){

		$data = array();
		$categories = array();
		for($days = $day-(147+$week-1); $days < ($day-$week-1); $days = $days+7){
			$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $days, $year));
			$date_end = date("Y-m-d", mktime(0, 0, 0, $month, $days+6, $year));
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!=''", $date_start, $date_end);
			$data["all"][] = (int)$count;
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!=''", $date_start, $date_end);
			$data["active"][] = (int)$count;
			$categories[] = $date_start;
		}
		if($week != 0){
			$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $day-$week+1, $year));
			$date_end = $year."-".$month."-".$day;
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!=''", $date_start, $date_end);
			$data["all"][] = (int)$count;
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!=''", $date_start, $date_end);
			$data["active"][] = (int)$count;
			$categories[] = $date_start;
		}
		$arr["data"][] = array("name" => "Всего", "data" => $data["all"]);
		$arr["data"][] = array("name" => "Активированных", "data" => $data["active"]);
		foreach($categories as $index => $date)
			$arr["categories"][$index] = date_transform($date);

	}elseif($date_params == "month"){

		$first_year = $year - 1;
		$data = array();
		for($i_month = $month; $i_month <= 12; $i_month++){
			$date_start_month = date($first_year.'-'.$i_month."-1");
			$date_end_month = date("Y-m-d", mktime(0, 0, 0, $i_month+1, 0, $first_year));
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!=''", $date_start_month, $date_end_month);
			$data["all"][] = (int)$count;
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!='' AND active=1", $date_start_month, $date_end_month);
			$data["active"][] = (int)$count;
			$categories[] = $array_month[(int)$i_month];
		}
		for($i_month = 1; $i_month <= $month; $i_month++){
			$date_start_month = date($year."-".$i_month."-1");
			$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $year));
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!=''", $date_start_month, $date_end_month);
			$data["all"][] = (int)$count;
			$count = $connect->getOne("SELECT COUNT(*) FROM klient WHERE date_reg>=?s AND date_reg<=?s AND login!='' AND active=1", $date_start_month, $date_end_month);
			$data["active"][] = (int)$count;
			$categories[] = $array_month[(int)$i_month];
		}
		$arr["data"][] = array("name" => "Всего", "data" => $data["all"]);
		$arr["data"][] = array("name" => "Активированных", "data" => $data["active"]);
		foreach($categories as $index => $date)
			$arr["categories"][$index] = $date;

	}
	return json_encode($arr);

}

function graph_rating($connect){
	$all = $connect->getOne("SELECT COUNT(*) FROM rating WHERE schet!=''");
	$read = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=1 AND schet!=''");
	$confirm = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=3 AND schet!=''");
	$trash = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=4 AND schet!=''");
	$confirm_text = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=3 AND (positive!='' OR negative!='' OR advice!='') AND schet!=''");
	$confirm_photo = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=3 AND photos!='' AND schet!=''");
	$percent_read = round((($read / $all) * 100), 2);
	$percent_confirm = round((($confirm / $all) * 100), 2);
	$percent_trash = round((($trash / $all) * 100), 2);
	$percent_confirm_text = round((($confirm_text / $confirm) * 100), 2);
	$percent_confirm_photo = round((($confirm_photo / $confirm) * 100), 2);
	ob_start();
?>
<div class="form-horizontal">
	<div class="form-group">
		<div class="col-sm-7">
			<div class="form-group">
				<label class="col-sm-6 control-label">Выслано писем с отзывами</label>
				<div class="col-sm-6">
					<div class="well well-sm"><?php echo $all; ?></div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-6 control-label">Прочитано писем (но нет отзыва)</label>
				<div class="col-sm-6">
					<div class="well well-sm"><?php echo $read; ?> (<?php echo $percent_read; ?>)%</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-6 control-label">Отзывов принято</label>
				<div class="col-sm-6">
					<div class="well well-sm"><?php echo $confirm; ?> (<?php echo $percent_confirm; ?>)%</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-6 control-label">Принятых отзывов с текстом</label>
				<div class="col-sm-6">
					<div class="well well-sm"><?php echo $confirm_text; ?> (<?php echo $percent_confirm_text; ?>)%</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-6 control-label">Принятых отзывов с фото</label>
				<div class="col-sm-6">
					<div class="well well-sm"><?php echo $confirm_photo; ?> (<?php echo $percent_confirm_photo; ?>)%</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-6 control-label">Отзывов в архиве</label>
				<div class="col-sm-6">
					<div class="well well-sm"><?php echo $trash; ?> (<?php echo $percent_trash; ?>)%</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-6 control-label">Диапазон</label>
				<div class="col-sm-6 btn-group range-graph" data-toggle="buttons">
					<label class="btn btn-primary btn-xs active">
						<input type="radio" checked name="range" value="day" />&nbsp;по дням&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="range" value="week" />&nbsp;по неделям&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="range" value="month" />&nbsp;по месяцам&nbsp;
					</label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-6 control-label">Значения</label>
				<div class="col-sm-6 btn-group value-graph" data-toggle="buttons">
					<label class="btn btn-primary btn-xs active">
						<input type="radio" checked name="value" value="value" />&nbsp;отзывы&nbsp;
					</label>
					<label class="btn btn-primary btn-xs">
						<input type="radio" name="value" value="conversion" />&nbsp;конверсия&nbsp;
					</label>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-6"></div>
				<div class="col-sm-6">
					<button type="button" class="btn btn-success btn-graph" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Формирование графика..." onclick="get_data_for_graph_rating()"><i class="fa fa-area-chart"></i> Сформировать</button>
				</div>
			</div>
		</div>
		<div class="col-sm-2"></div>
		<div class="col-sm-3 right">
			<div class="btn-group type-chart-graph" data-toggle="buttons">
				<label class="btn btn-danger btn-sm active">
					<input type="radio" checked name="type-chart" value="line" />&nbsp;<i class="fa fa-line-chart"></i>&nbsp;
				</label>
				<label class="btn btn-danger btn-sm">
					<input type="radio" name="type-chart" value="column" />&nbsp;<i class="fa fa-bar-chart"></i>&nbsp;
				</label>
				<label class="btn btn-danger btn-sm">
					<input type="radio" name="type-chart" value="area" />&nbsp;<i class="fa fa-area-chart"></i>&nbsp;
				</label>
			</div>
		</div>
	</div>
</div>
<?php
}

function get_data_for_graph_rating($connect){

	global $array_month;

	$date_params = $_POST["date"];
	$value_params = $_POST["value"];

	$day = date("d");
	$month = date("m");
	$year = date("Y");
	$week = date("w");

	if($date_params == "day"){

		$first_year = $year;
		$first_month = $month - 1;
		if($month == 1){
			$first_month = 12;
			$first_year = $year - 1;
		}
		$max_day = cal_days_in_month(CAL_GREGORIAN, $first_month, $first_year);
		$data = array();
		$categories = array();
		for($i_day = $day; $i_day <= $max_day; $i_day++){
			$date = $first_year."-".$first_month."-".$i_day;
			$all = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date=?s AND schet!=''", $date);
			$confirm = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date=?s AND schet!='' AND status=3", $date);
			if($value_params == "value"){
				$data["all"][] = $all;
				$data["confirm"][] = $confirm;
			}else
				$data["conversion"][] = ($confirm / $all) * 100;
			$categories[] = $date;
		}
		for($i_day = 1; $i_day <= $day; $i_day++){
			$date = $year."-".$month."-".$i_day;
			$all = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date=?s AND schet!=''", $date);
			$confirm = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date=?s AND schet!='' AND status=3", $date);
			if($value_params == "value"){
				$data["all"][] = $all;
				$data["confirm"][] = $confirm;
			}else
				$data["conversion"][] = ($confirm / $all) * 100;
			$categories[] = $date;
		}

		if($value_params == "value"){
			$arr["data"][] = array("name" => "Всего", "data" => $data["all"]);
			$arr["data"][] = array("name" => "Принятых", "data" => $data["confirm"]);
		}else
			$arr["data"][] = array("name" => "Конверсия %", "data" => $data["conversion"]);
		foreach($categories as $index => $date)
			$arr["categories"][$index] = date_transform($date);

	}elseif($date_params == "week"){

		$data = array();
		$categories = array();
		for($days = $day-(147+$week-1); $days < ($day-$week-1); $days = $days+7){
			$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $days, $year));
			$date_end = date("Y-m-d", mktime(0, 0, 0, $month, $days+6, $year));
			$all = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date>=?s AND date<=?s AND schet!=''", $date_start, $date_end);
			$confirm = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date>=?s AND date<=?s AND schet!='' AND status=3", $date_start, $date_end);
			if($value_params == "value"){
				$data["all"][] = $all;
				$data["confirm"][] = $confirm;
			}else
				$data["conversion"][] = ($confirm / $all) * 100;
			$categories[] = $date_start;
		}
		if($week != 0){
			$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $day-$week+1, $year));
			$date_end = $year."-".$month."-".$day;
			$all = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date>=?s AND date<=?s AND schet!=''", $date_start, $date_end);
			$confirm = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date>=?s AND date<=?s AND schet!='' AND status=3", $date_start, $date_end);
			if($value_params == "value"){
				$data["all"][] = $all;
				$data["confirm"][] = $confirm;
			}else
				$data["conversion"][] = ($confirm / $all) * 100;
			$categories[] = $date_start;
		}
		if($value_params == "value"){
			$arr["data"][] = array("name" => "Всего", "data" => $data["all"]);
			$arr["data"][] = array("name" => "Принятых", "data" => $data["confirm"]);
		}else
			$arr["data"][] = array("name" => "Конверсия %", "data" => $data["conversion"]);
		foreach($categories as $index => $date)
			$arr["categories"][$index] = date_transform($date);

	}elseif($date_params == "month"){

		$first_year = $year - 1;
		$data = array();
		for($i_month = $month; $i_month <= 12; $i_month++){
			$date_start_month = date($first_year.'-'.$i_month."-1");
			$date_end_month = date("Y-m-d", mktime(0, 0, 0, $i_month+1, 0, $first_year));
			$all = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date>=?s AND date<=?s AND schet!=''", $date_start_month, $date_end_month);
			$confirm = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date>=?s AND date<=?s AND schet!='' AND status=3", $date_start_month, $date_end_month);
			if($value_params == "value"){
				$data["all"][] = $all;
				$data["confirm"][] = $confirm;
			}else
				$data["conversion"][] = ($confirm / $all) * 100;
			$categories[] = $array_month[(int)$i_month];
		}
		for($i_month = 1; $i_month <= $month; $i_month++){
			$date_start_month = date($year."-".$i_month."-1");
			$date_end_month = date('Y-m-d', mktime(0, 0, 0, $i_month+1, 0, $year));
			$all = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date>=?s AND date<=?s AND schet!=''", $date_start_month, $date_end_month);
			$confirm = (int)$connect->getOne("SELECT COUNT(*) FROM rating WHERE date>=?s AND date<=?s AND schet!='' AND status=3", $date_start_month, $date_end_month);
			if($value_params == "value"){
				$data["all"][] = $all;
				$data["confirm"][] = $confirm;
			}else
				$data["conversion"][] = ($confirm / $all) * 100;
			$categories[] = $array_month[(int)$i_month];
		}
		if($value_params == "value"){
			$arr["data"][] = array("name" => "Всего", "data" => $data["all"]);
			$arr["data"][] = array("name" => "Принятых", "data" => $data["confirm"]);
		}else
			$arr["data"][] = array("name" => "Конверсия %", "data" => $data["conversion"]);
		foreach($categories as $index => $date)
			$arr["categories"][$index] = $date;

	}
	return json_encode($arr);

}

?>
