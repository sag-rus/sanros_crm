<?php

function show_bonus_report_menu(){
	ob_start();
?>
<div class="btn-group small-menu-report">
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-all" onclick="bonus_report_general()"><i class="fa fa-tasks"></i> Общий</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-month" onclick="bonus_report_month()"><i class="fa fa-calendar"></i> По месяцам</button>
	</div>
</div>
<div id="panel" style="margin-top: 10px"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function bonus_report_general(){
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-4 control-label">Дата</label>
			<div class="col-sm-4">
				<input type="text" class="form-control datepicker" id="date_bonus" />
			</div>
			<div class="col-sm-4">
				<input type="text" class="form-control datepicker" id="date_bonus2" />
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-4 control-label">Тип</label>
			<div class="col-sm-8">
				<select class="form-control" id="type">
					<option value="1">Списание</option>
					<option value="2">Начисление</option>
					<option value="">Все</option>
				</select>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="form-group form-group-margin">
			<div class="col-sm-offset-4 col-sm-8">
				<button type="button" class="btn btn-success btn-sm" onclick="filter_bonus()"><i class="fa fa-search"></i> Применить</button>
			</div>
		</div>
	</div>
</div>
<div id="filter_res"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function bonus_report_month(){
	global $array_month;
	$month_select = "";
	foreach($array_month as $key => $month)
		$month_select.= "<option value='".$key."'>".$month."</option>";
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-4 control-label">Месяц</label>
			<div class="col-sm-8">
				<select class="form-control" id="month">
					<option value="">Выбрать месяц</option>
					<?php echo $month_select; ?>
				</select>
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-4 control-label">Год</label>
			<div class="col-sm-8">
				<select class="form-control" id="year">
					<option value="">Выбрать год</option>
					<option value="2013">2013</option>
					<option value="2014">2014</option>
					<option value="2015">2015</option>
					<option value="2016">2016</option>
					<option value="2017">2017</option>
				</select>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="form-group form-group-margin">
			<div class="col-sm-offset-4 col-sm-8">
				<button type="button" class="btn btn-success btn-sm" onclick="filter_bonus_report_month()"><i class="fa fa-search"></i> Применить</button>
			</div>
		</div>
	</div>
</div>
<div id="filter_res"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function filter_bonus($connect){
	$date1 = $_POST["date1"];
	$date2 = $_POST["date2"];
	$type = $_POST["type"];
	$html = "";
	if($date1){
		if(!$date2)
			$zapros_for_mysql = " date = '$date1'";
		else
			$zapros_for_mysql.= " date >= '$date1' AND date <= '$date2'";
	}
	if($type){
		if($zapros_for_mysql)
			$zapros_for_mysql.= " AND ";
		if($type == 1)
			$zapros_for_mysql.= " sum <= 0";
		elseif($type == 2)
			$zapros_for_mysql.= " sum > 0";
	}
	$zapros_for_mysql.= " AND type=1";
	$zapros_for_mysql = "SELECT DATE_FORMAT(date, '%d.%m.%Y') as date, schet, sum, turist FROM bonus WHERE ".$zapros_for_mysql;
	$array = array();
	$array["bonus_plus"] = 0;
	$array["num_bonus_plus"] = 0;
	$array["bonus_minus"] = 0;
	$array["num_bonus_minus"] = 0;
	$array["num_bonus"] = 0;
	$data = $connect->getAll($zapros_for_mysql);
	foreach($data as $row){
		$bonus = $row["sum"];
		$array["num_bonus"]++;
		if($bonus > 0){
			$array["bonus_plus"]+= $bonus;
			$array["num_bonus_plus"]++;
			$class = "class='text-success'";
		}else{
			$array["bonus_minus"]+= $bonus;
			$array["num_bonus_minus"]++;
			$class = "class='text-danger'";
		}
		$date = $row["date"];
		$id = $row["schet"];
		$klient = $row["turist"];
		$schet = $row["schet"];
		$row = $connect->getRow("SELECT name, surname, otch FROM klient WHERE id=?i", $klient);
		$turist = $row["surname"]." ".$row["name"]." ".$row["otch"];
		$sum = $connect->getOne("SELECT sum FROM reckoning WHERE id=?i", $schet);
		$html.= "<tr class='tr_reck' onclick='show_turist(\"".$klient."\", \"".$id."\", \"turist\")'>";
		$html.= "<td width='25'>".$id."</td>";
		$html.= "<td width='250'>".$turist."</td>";
		$html.= "<td width='80'>".$sum."</td>";
		$html.= "<td width='80' ".$class."><strong>".$bonus."</strong></td>";
		$html.= "<td width='80'>".$date."</td>";
		$html.= "</tr>";
	}
	if($html){
?>
	<div class="list-group">
		<div class="list-group-item">
			<strong>Всего результатов:</strong> <?php $array["num_bonus"]; ?>
		</div>
		<?php if($array["num_bonus_plus"]){ ?>
		<div class="list-group-item">
			<strong>Начисленно бонусов:</strong> <?php echo $array["num_bonus_plus"]; ?> на сумму <strong class="text-success"><?php echo number_format($array["bonus_plus"], 2, ",", " "); ?></strong> рублей
		<?php } ?>
		<?php if($array["num_bonus_minus"]){ ?>
		<div class="list-group-item">
			<strong>Списано бонусов:</strong> <?php echo $array["num_bonus_minus"]; ?> на сумму <strong class="text-danger"><?php echo number_format($array["bonus_minus"], 2, ",", " "); ?></strong> рублей
		</div>
		<?php } ?>
	</div>
	<table class="table table-hover table-condensed">
	<tr>
		<th>№</th>
		<th>ФИО</th>
		<th>Сумма</th>
		<th>Бонусы</th>
		<th>Дата</th>
	</tr>
	<?php echo $html; ?>
	</table>
<?php	}else{ ?>
	<div class="alert alert-info"><i class="fa fa-info-circle"></i> Ничего не найдено</div>
<?php }
}

function filter_bonus_month($connect){
	global $array_week, $array_month;
	$year = $_POST["year"];
	$month = $_POST["month"];
	if($month){
		$max = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		for($day = 1; $day <= $max; $day++){
			$date = $year."-".$month."-".$day;
			$bonus_plus = 0;
			$bonus_minus = 0;
			$data = $connect->getAll("SELECT sum FROM bonus WHERE date=?s AND sum>0", $date);
			foreach($data as $row)
				$bonus_plus+= $row["sum"];
			$data2 = $connect->getAll("SELECT sum FROM bonus WHERE date=?s AND sum<0", $date);
			foreach($data2 as $row)
				$bonus_minus+= $row["sum"];
			$week = date("w", strToTime($date));
			$html.= "<tr>";
			$html.= "<td style='text-align: center; width: 100px;'>".$day.".".$month.".".$year."</td>";
			$html.= "<td style='width: 150px;'>".$array_week[$week]."</td>";
			$html.= "<td style='width: 100px;'>".count($data)."</td>";
			$html.= "<td style='width: 100px;' class='green-success'>".number_format($bonus_plus, 2, ',', ' ')."</td>";
			$html.= "<td style='width: 100px;'>".count($data2)."</td>";
			$html.= "<td style='width: 100px;' class='red-danger'>".number_format($bonus_minus, 2, ',', ' ')."</td>";
			$html.= "</tr>";
		}
		$html = "<table class='table table-condensed'><thead><tr><th rowspan='2'>Дата</th><th rowspan='2'>День недели</th><th colspan='2'>Начислено</th><th colspan='2'>Списано</th></tr><tr><th>Кол-во</th><th>Сумма</th><th>Кол-во</th><th>Сумма</th></tr></thead><tbody>".$html."</tbody></table>";
	}else{
		for($month = 1; $month <= 12; $month++){
			$max = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$first = $year."-".$month."-1";
			$end = $year."-".$month."-".$max;
			$bonus_plus = 0;
			$bonus_minus = 0;
			$data = $connect->getAll("SELECT sum FROM bonus WHERE sum>0 AND (date>=?s AND date<=?s)", $first, $end);
			foreach($data as $row)
				$bonus_plus+= $row["sum"];
			$data2 = $connect->getAll("SELECT sum FROM bonus WHERE sum<0 AND (date>=?s AND date<=?s)", $first, $end);
			foreach($data2 as $row)
				$bonus_minus+= $row["sum"];
			$html.= "<tr>";
			$html.= "<td style='text-align: center; width: 100px;'>".$array_month[$month]."</td>";
			$html.= "<td style='width: 120px;'>".count($data)."</td>";
			$html.= "<td style='width: 120px;' class='green-success'>".number_format($bonus_plus, 2, ',', ' ')."</td>";
			$html.= "<td style='width: 120px;'>".count($data2)."</td>";
			$html.= "<td style='width: 120px;' class='red-danger'>".number_format($bonus_minus, 2, ',', ' ')."</td>";
			$html.= "</tr>";
		}
		$html = "<table class='table table-condensed'><thead><tr><th rowspan='2'>Месяц</th><th colspan='2'>Начислено</th><th colspan='2'>Списано</th></tr><tr><th>Кол-во</th><th>Сумма</th><th>Кол-во</th><th>Сумма</th></tr></thead><tbody>".$html."</tbody></table>";
	}
	return $html;
}

?>
