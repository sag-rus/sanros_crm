<?php

function show_profit($connect){
	global $id_rights, $session_login;
	$select = "";
	$office = $connect->getOne("SELECT office FROM users WHERE id=?i", $session_login);
	if($id_rights > 3 AND $office){
		$data = $connect->getAll("SELECT id, name FROM users WHERE office=?i AND id!=?i", $office, $session_login);
		foreach($data as $row)
			$select.= "<option value='".$row["id"]."'>".$row["name"]."</option>";
	}
	ob_start();
?>
<div class="form-horizontal">
	<div class="panel-body">
		<div class="form-group">
			<div class="col-sm-5">
				<select class="form-control" id="all_manager">
					<option value="<?php echo $session_login; ?>">Мой</option>
					<?php echo $select; ?>
				</select>
			</div>
			<div class="col-sm-5">
				<?php echo get_month_profit(); ?>
			</div>
			<div class="col-sm-2">
				<button type="button" class="btn btn-info btn-sm" onclick="view_my_profit()"><i class="fa fa-check-circle"></i> Показать</button>
			</div>
		</div>
</div>
<div id="result"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function see_managers(){
	ob_start();
?>
<div class="form-horizontal">
	<div class="panel-body">
		<div class="form-group">
			<div class="col-sm-6">
				<?php echo get_month_profit(); ?>
			</div>
			<div class="col-sm-6">
				<button type="button" class="btn btn-info btn-sm" onclick="see_plan_manager()"><i class="fa fa-check-circle"></i> Показать</button>
			</div>
		</div>
</div>
<div id="results"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function see_plan_manager($connect){
	global $array_month;
	if(!$_POST["month"]){
		$year = date("Y");
		$month = date("m");
	}else{
		$arr = explode("-", $_POST["month"]);
		$year = $arr[1];
		$month = $arr[0];
	}
	ob_start();
?>
<div class="panel panel-default">
	<div class="panel-heading"><i class="fa fa-file-text-o"></i> План менеджеров на <?php echo $array_month[(int)$month]." ".$year; ?></div>
	<table class="table table-condensed">
	<tr>
		<th>Имя</th>
		<th>План (руб.)</th>
		<th>Сверх плата (%)</th>
        <th>Сверх плата по специальным регионам (%)</th>
		<th></th>
	</tr>
<?php
	$data = $connect->getAll("SELECT id, name FROM users WHERE class=1 AND dostup=1");
	foreach($data as $row){
		$id_man = $row["id"];
		$manager = $row["name"];
		$row = $connect->getRow("SELECT id, plan, commission, commission_region FROM plan WHERE manager=?i AND year=?i AND month=?i", $id_man, $year, $month);
		$id_plan = $row["id"];
		if($id_plan){
			$plan = $row["plan"];
			$commission = $row["commission"];
			$commission_region = $row["commission_region"];
			$button = "<button type='button' class='btn btn-default btn-xs' onclick='edit_plan_manager(\"".$id_plan."\")'>&nbsp;<i class='fa fa-pencil'></i>&nbsp;</button>";
		}else{
			$button = "<button type='button' class='btn btn-primary btn-xs'  onclick='add_plan_manager(\"".$id_man."\")'>&nbsp;<i class='fa fa-plus-circle'></i>&nbsp;</button>";
			$plan = "-";
			$commission = "-";
            $commission_region = "-";
		}
?>
		<tr>
			<td width="30%"><?php echo $manager; ?></td>
			<td width="20%" class="center"><?php echo $plan; ?></td>
			<td width="20%" class="center"><?php echo $commission; ?></td>
            <td width="20%" class="center"><?php echo $commission_region; ?></td>
			<td width="10%"><?php echo $button; ?></td>
		</tr>
<?php
	}
?>
		</table>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function add_plan_manager(){
	$manager = $_POST["manager"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить план</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal plan">
					<div class="form-group">
						<label class="col-sm-4 control-label">План (руб.)</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="new_plan" />
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Сверх плата (%)</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="new_commis" onkeypress="validate_sum('new_commis')" />
						</div>
					</div>
                    <div class="form-group form-group-margin">
                        <label class="col-sm-4 control-label">Сверх плата по специальным регионам (%)</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="commission_region_new" onkeypress="validate_sum('commission_region_new')" />
                        </div>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="save_plan_manager('<?php echo $manager; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_plan_manager($connect){
	if($_POST["month"]){
		$arr = explode("-", $_POST["month"]);
		$month = $arr[0];
		$year = $arr[1];
	}else{
		$year = date("Y");
		$month = date("m");
	}
	$plan = $_POST["plan"];
	$commis = $_POST["commis"];
	$commission_region = $_POST["commission_region"];
	$manager = $_POST["manager"];
	$connect->query("INSERT INTO plan(plan, commission, commission_region, year, month, manager) VALUES (?i, ?s, ?s, ?i, ?i, ?i)", $plan, $commis, $commission_region, $year, $month, $manager);
}

function edit_plan_manager($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT commission, commission_region, plan FROM plan WHERE id=?i", $id);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить план</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal plan">
					<div class="form-group">
						<label class="col-sm-4 control-label">План (руб.)</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="plan" value="<?php echo $row['plan']; ?>" />
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Сверх плата (%)</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="commis" value="<?php echo $row['commission']; ?>" onkeypress="validate_sum('commis')" />
						</div>
					</div>
                    <div class="form-group form-group-margin">
                        <label class="col-sm-4 control-label">Сверх плата по специальным регионам (%)</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="commission_region" value="<?php echo $row['commission_region']; ?>" onkeypress="validate_sum('commission_region')" />
                        </div>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="update_plan_manager('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	echo $html;
}

function update_plan_manager($connect){
	$id = $_POST["id"];
	$plan = $_POST["plan"];
	$commis = $_POST["commis"];
	$commission_region = $_POST["commission_region"];
	$connect->query("UPDATE plan SET plan=?i, commission=?s, commission_region=?s WHERE id=?i", $plan, $commis, $commission_region, $id);
}

function view_my_profit($connect){
	global $session_login, $id_rights, $array_month;
	$id_user = $_POST["id"];
	$year_month = $_POST["month"];
	$arr = explode("-", $year_month);
	if(!isset($arr[1]))
		$arr[1] = "";
	$year = $arr[1];
	$month = $arr[0];
	if(!$id_user)
		$id_user = $session_login;
	if(!$month){
		$year = date("Y");
		$month = date("m");
		$date_start_month = date("Y-m-1");
		$date_end_month = date("Y-m-d", mktime(0, 0, 0, $month + 1, 0, $year));
	}else{
		$date_start_month = date($year."-".$month."-1");
		$date_end_month = date("Y-m-d", mktime(0, 0, 0, $month + 1, 0, $year));
	}
	$table = "";
	$array = array("raz" => 0, "raz_h" => 0, "reward" => 0, "reward_reg" => 0, "excess_stand" => 0, "excess_reg" => 0, "excess" => 0);
	$data = $connect->getAll("SELECT reckoning.id, reckoning.sum, reckoning.rest, DATE_FORMAT(reckoning.date_z, '%d.%m.%Y') as zaezd, reckoning.id_obj, DATE_FORMAT(reckoning.date_v, '%d.%m.%Y') as vyezd, reckoning.active, region.man_reward_scheme AS man_reward_scheme FROM reckoning INNER JOIN object ON object.id=reckoning.id_obj LEFT OUTER JOIN region ON region.id = object.id_reg WHERE reckoning.id_user=?i AND reckoning.status=5 AND reckoning.date_z>=?s AND reckoning.date_z<=?s ORDER BY reckoning.id", $id_user, $date_start_month, $date_end_month);
	$all_reward = 0;
	foreach($data as $row){
		$id = $row["id"];
		$active = $row["active"];
		$object = get_object($connect, $row["id_obj"]);
		$rest = explode(",", $row["rest"]);
		$rest = array_diff($rest, array(""));
		$index = 0;
		$count = count($rest);
		$turist = "";
		while($turist == "" AND $index <= $count AND $count > 0){
			$turist = $rest[$index];
			$index++;
		}
		if($turist)
			$turist = select_name_klient($connect, $turist, "surname");
		else
			$turist = "не указан";
		$sum = $row["sum"];
		$reward = get_reward_schet($connect, $id);
		$date_z = $row["zaezd"];
		$array["reward"]+= $reward;

		if($row['man_reward_scheme'] == 1 && strtotime($row['zaezd']) >= strtotime("01.11.2017")) {
		    $array["reward_reg"]+= $reward;
        }

		if($reward < 0)
			$color = "class='red-danger'";
		elseif($reward == 0)
			$color = "";
		else
			$color = "class='green-success'";
		if($active == 2)
			$color_active = "class='alert-success'";
		elseif($active != 0)
			$color_active = "class='alert-danger'";
		else
			$color_active = "";
		ob_start();
?>
		<tr>
			<td width="5%" <?php echo $color_active; ?>><?php echo $id; ?></td>
			<td width="30%"><?php echo $turist; ?></td>
			<td width="25%"><?php echo $object; ?></td>
			<td width="10%"><?php echo $sum; ?></td>
			<td width="10%"><?php echo $date_z; ?></td>
			<td width="20%" <?php echo $color; ?>><?php echo $reward; ?></td>
		</tr>
<?php
		$table.= ob_get_clean();
	}
	$row = $connect->getRow("SELECT id, plan, commission, commission_region FROM plan WHERE manager=?i AND year=?i AND month=?i", $id_user, $year, $month);
	if($row["id"]){
		$plan = number_format($row["plan"], 2, ".", " ")." рублей";
		$array["raz"] = $row["plan"] - $array["reward"];
		$raz = abs($array["raz"]);
		$array["raz_h"] = number_format(abs($raz), 2, ".", " ")." рублей";
		$commis_manager = $row["commission"] / 100;
		$commis_manager_reg = $row["commission_region"] / 100;
		$excess_stand = round(abs($raz) * $commis_manager, 2);
        $excess__reg = 0;
		if($raz >= 0) {
          $excess__reg = round(abs($array["reward_reg"]) * $commis_manager_reg, 2);
        }
        $excess = $excess_stand+$excess__reg;
        $array["excess_stand"] = number_format($excess_stand, 2, ".", " ")." рублей";
		$array["excess_reg"] = number_format($excess__reg, 2, ".", " ")." рублей";
		$array["excess"] = number_format($excess, 2, ".", " ")." рублей";
	}else{
		$plan = "не установлен";
		$excess_plan = "-";
		$array["raz"] = 0;
		$excess = 0;
	}
	ob_start();
?>
	<div class="panel panel-default">
		<div class="panel-heading"><i class="fa fa-gift"></i> Доход за <?php echo $array_month[(int)$month]." ".$year; ?></div>
		<div class="list-group">
			<div class="list-group-item"><strong>План:</strong> <?php echo $plan; ?></div>
			<div class="list-group-item"><strong>Комиссия:</strong> <?php echo $row["commission"]; ?>%</div>
            <div class="list-group-item"><strong>Доп. комиссия по спец. регионам:</strong> <?php echo $row["commission_region"]; ?>%</div>
			<div class="list-group-item"><strong>На данный момент:</strong> <?php echo number_format($array["reward"], 2, ".", " "); ?> рублей</div>
            <div class="list-group-item" style="padding-left: 40px;"><strong>По обычным регионам:</strong> <?php echo number_format($array["reward"]-$array["reward_reg"], 2, ".", " "); ?> рублей</div>
            <div class="list-group-item" style="padding-left: 40px;"><strong>По спец. регионам:</strong> <?php echo number_format($array["reward_reg"], 2, ".", " "); ?> рублей</div>
          <?php if($array["raz"] > 0){ ?>
				<div class="list-group-item"><strong>Осталось:</strong> <?php echo $array["raz_h"]; ?></div>
          <?php }elseif($excess > 0){ ?>
				<div class="list-group-item"><strong>Сверх плана:</strong> <?php echo $array["raz_h"]; ?></div>
                <div class="list-group-item"><strong>Комиссия:</strong> <?php echo $array["excess"]; ?></div>
				<div class="list-group-item" style="padding-left: 40px;"><strong>Комиссия сверх плана станд. :</strong> <?php echo $array["excess_stand"]; ?></div>
                <div class="list-group-item" style="padding-left: 40px;"><strong>Комиссия сверх плана по спец. рег. :</strong> <?php echo $array["excess_reg"]; ?></div>
                <?php ?>
                <?php ?>
          <?php } ?>
	<?php if($table){ ?>
		</div>
		<table class="table table-condensed table-bordered">
		<tr>
			<th>№</th>
			<th>Отдыхающий</th>
			<th>Объект</th>
			<th>Сумма</th>
			<th>Заезд</th>
			<th>Воз-ие (руб.)</th>
		</tr>
			<?php echo $table; ?>
		</table>
			<?php if($id_rights > 3){ ?>
		<div class="panel-footer" style="text-align: right">
			<button type="button" class="btn btn-warning btn-sm" onclick="block_reckoning_month('<?php echo $date_start_month; ?>', '<?php echo $date_end_month; ?>', '<?php echo $id_user; ?>')"><i class="fa fa-unlock-alt"></i> Заблокировать</button>
		</div>
			<?php } ?>
	<?php }else{ ?>
			<div class="list-group-item list-group-item-info"><i class="fa fa-info-circle"></i> Заявок за текущий месяц не найдено</div>
		</div>
	<?php } ?>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

?>
