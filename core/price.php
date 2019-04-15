<?php

function show_profile($connect){
	$index = 0;
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-heartbeat"></i> Профили лечения</div>
	<div class="panel-body form-group form-group-margin">
<?php
	$data = $connect->getAll("SELECT id, name FROM profile");
	foreach($data as $row){
		$id = $row["id"];
		$name = $row["name"];
		$index++;
?>
		<div class="col-sm-3" style="margin-bottom: 1px;">
			<?php echo $name; ?></td>
		</div>
		<div class="col-sm-1" style="margin-bottom: 1px;">
			<button type="button" class="btn btn-default btn-xs" onclick="edit_profile(<?php echo $id; ?>)"><i class="fa fa-pencil"></i></button>
		</div>
		<?php if($index == 3){
			$index = 0; ?>
		<div class="clearfix"></div>
		<?php } ?>
<?php
	}
?>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-info btn-sm" onclick="add_new_profile()"><i class="fa fa-plus-circle"></i> Добавить</button>
	</div>
</div>
<?php
}

function new_profile(){
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Новый профиль лечения</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-profile">
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Название</label>
						<div class="col-sm-8">
							<input type="text" class="form-control name">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="save_new_profile()"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_new_profile($connect){
	$name = $_POST["name"];
	$connect->query("INSERT INTO profile(name) VALUES(?s)", $name);
}

function edit_profile($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, description FROM profile WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить профиль лечения</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-profile">
					<div class="form-group">
						<label class="col-sm-4 control-label">Название</label>
						<div class="col-sm-8">
							<input type="text" class="form-control name" value="<?php echo $row['name']; ?>">
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Описание</label>
						<div class="col-sm-8">
							<textarea class="form-control description"><?php echo $row['description']; ?></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="update_profile('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_profile($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$description = $_POST["description"];
	$connect->query("UPDATE profile SET name=?s, description=?s, synchronized = 0 WHERE id=?i", $name, $description, $id);
}

function show_infrastructure($connect){
	$index = 0;
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-building-o"></i> Инфраструктура</div>
	<div class="panel-body form-group form-group-margin">
<?php
	$data = $connect->getAll("SELECT id, name FROM infa");
	foreach($data as $row){
		$id = $row["id"];
		$name = $row["name"];
		$index++;
?>
		<div class="col-sm-3" style="margin-bottom: 1px;">
			<?php echo $name; ?></td>
		</div>
		<div class="col-sm-1" style="margin-bottom: 1px;">
			<button type="button" class="btn btn-default btn-xs" onclick="edit_infrastructure('<?php echo $id; ?>')"><i class="fa fa-pencil"></i></button>
		</div>
		<?php if($index == 3){
			$index = 0; ?>
		<div class="clearfix"></div>
		<?php } ?>
<?php
	}
?>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-info btn-sm" onclick="add_new_infrastructure()"><i class="fa fa-plus-circle"></i> Добавить</button>
	</div>
</div>
<?php
}

function new_infrastructure(){
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Новый объект инфраструктуры</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-infrastructure">
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Название</label>
						<div class="col-sm-8">
							<input type="text" class="form-control name">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="save_new_infrastructure()"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_new_infrastructure($connect){
	$name = $_POST["name"];
	$connect->query("INSERT INTO infa(name) VALUES(?s)", $name);
}

function edit_infrastructure($connect){
	$id = $_POST["id"];
	$name = $connect->getOne("SELECT name FROM infa WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить объект инфраструктуры</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-infrastructure">
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Название</label>
						<div class="col-sm-8">
							<input type="text" class="form-control name" value="<?php echo $name; ?>">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="update_infrastructure('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_infrastructure($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$connect->query("UPDATE infa SET name=?s, synchronized = 0 WHERE id=?i", $name, $id);
}

function see_comfort($connect){
	$index = 0;
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-coffee"></i> Удобства в номерах</div>
	<div class="panel-body form-group form-group-margin">
<?php
	$data = $connect->getAll("SELECT id, name, icon, type FROM comfort ORDER BY type DESC");
	foreach($data as $row){
		$id = $row["id"];
		$icon = $row["icon"];
		$name = $row["name"];
		$star = "info";
		if($row["type"] == 1)
			$star = "warning";
		$index++;
?>
		<div class="col-sm-3" style="margin-bottom: 1px;">
			<?php if($icon){ ?>
			<i class="fa <?php echo $icon; ?>"></i>
			<?php } ?>
			<?php echo $name; ?></td>
		</div>
		<div class="col-sm-1" style="margin-bottom: 1px;">
			<button type="button" class="btn btn-<?php echo $star; ?> btn-xs icon-star-<?php echo $id; ?>" onclick="change_type_comfort('<?php echo $id; ?>')"><i class="fa fa-star"></i></button>
			<button type="button" class="btn btn-default btn-xs" onclick="edit_comfort('<?php echo $id; ?>')"><i class="fa fa-pencil"></i></button>
		</div>
		<?php if($index == 3){
			$index = 0; ?>
		<div class="clearfix"></div>
		<?php } ?>
<?php
	}
?>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-info btn-sm" onclick="add_new_comfort()"><i class="fa fa-plus-circle"></i> Добавить</button>
	</div>
</div>
<?php
}

function new_comfort(){
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Новое удобство</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-comfort">
					<div class="form-group">
						<label class="col-sm-4 control-label">Название</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="name">
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Иконка</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="icon">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="save_new_comfort()"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_new_comfort($connect){
	$name = $_POST["name"];
	$icon = $_POST["icon"];
	$connect->query("INSERT INTO comfort(name, icon) VALUES(?s, ?s)", $name, $icon);
}

function edit_comfort($connect){
	$id = $_POST["id"];
	$comfort = $connect->getRow("SELECT icon, name FROM comfort WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить удобство</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-comfort">
					<div class="form-group">
						<label class="col-sm-4 control-label">Название</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="name" value="<?php echo $comfort['name']; ?>">
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Иконка</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="icon" value="<?php echo $comfort['icon']; ?>">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="update_comfort('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_comfort($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$icon = $_POST["icon"];
	$connect->query("UPDATE comfort SET name=?s, icon=?s, synchronized = 0 WHERE id=?i", $name, $icon, $id);
}

function change_type_comfort($connect){
	$id = $_POST["id"];
	$type = $connect->getOne("SELECT type FROM comfort WHERE id=?i", $id);
	if($type == 0){
		$connect->query("UPDATE comfort SET type=1, synchronized = 0 WHERE id=?i LIMIT 1", $id);
		return 1;
	}else
		$connect->query("UPDATE comfort SET type=0, synchronized = 0 WHERE id=?i LIMIT 1", $id);
	return 0;
}


function see_services($connect){
	$index = 0;
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-cutlery"></i> Услуги</div>
	<div class="panel-body form-group form-group-margin">
<?php
	$data = $connect->getAll("SELECT id, name, icon FROM services");
	foreach($data as $row){
		$id = $row["id"];
		$icon = $row["icon"];
		$name = $row["name"];
		$index++;
?>
		<div class="col-sm-3" style="margin-bottom: 1px;">
			<?php if($icon){ ?>
			<i class="fa <?php echo $icon; ?>"></i>
			<?php } ?>
			<?php echo $name; ?></td>
		</div>
		<div class="col-sm-1" style="margin-bottom: 1px;">
			<button type="button" class="btn btn-default btn-xs" onclick="edit_service('<?php echo $id; ?>')"><i class="fa fa-pencil"></i></button>
		</div>
		<?php if($index == 3){
			$index = 0; ?>
		<div class="clearfix"></div>
		<?php } ?>
<?php
	}
?>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-info btn-sm" onclick="add_new_services()"><i class="fa fa-plus-circle"></i> Добавить</button>
	</div>
</div>
<?php
}

function new_service(){
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Новая услуга</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-service">
					<div class="form-group">
						<label class="col-sm-4 control-label">Название</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="name">
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Иконка</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="icon">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="save_new_services()"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_new_services($connect){
	$name = $_POST["name"];
	$icon = $_POST["icon"];
	$connect->query("INSERT INTO services(name, icon) VALUES (?s, ?s)", $name, $icon);
}

function edit_service($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT icon, name FROM services WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить услугу</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-service">
					<div class="form-group">
						<label class="col-sm-4 control-label">Название</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="name" value="<?php echo $row['name']; ?>">
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Иконка</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="icon" value="<?php echo $row['icon']; ?>">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="update_service('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_service($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$icon = $_POST["icon"];
	$connect->query("UPDATE services SET name=?s, icon=?s WHERE id=?i", $name, $icon, $id);
}

function view_dates_price_object($connect){
	global $id_rights, $session_login;
	$reestablish = 0;
	$id = $_POST["id"];
	$html = "";
	$quota = $connect->getOne("SELECT check_places FROM object WHERE id=?i", $id);

	$today = date("Y-m-d", strToTime("-2 week"));
	$data = $connect->getAll("SELECT id, DATE_FORMAT(start, '%e.%m.%Y') as date_start, DATE_FORMAT(end, '%e.%m.%Y') as end FROM date_price WHERE id_obj=?i AND active=0 AND end>=?s ORDER BY start", $id, $today);
	foreach($data as $row){
		$dates = $row["date_start"]." - ".$row["end"];
		$html.= "<option value='".$row["id"]."'>".$dates."</option>";
	}
	if(!$html AND $session_login == 2){
		$row = $connect->getRow("SELECT id, DATE_FORMAT(start, '%e.%m.%Y') as date_start, DATE_FORMAT(end, '%e.%m.%Y') as end FROM date_price WHERE id_obj=?i ORDER BY start DESC", $id);
		if($row["date_start"]){
			$dates = $row["date_start"]." - ".$row["end"];
			$html.= "<option value='".$row["id"]."'>".$dates."</option>";
			$reestablish = 1;
		}
	}
?>
<div class="object-infa object-infa-price-table"></div>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading">
		<div class="form-group form-group-margin">
			<div class="col-sm-3">
		<?php if($id != 59 && $id != 42 && $quota == 1){ ?>
				<div class="alert alert-success">Цены из квоты! <span class="btn btn-link pull-right" onclick="show_quota_object_card(<?php echo $id; ?>)">Смотреть квоту</span><div class="clearfix"></div></div>
		<?php }elseif($html){ ?>
				<select class="form-control id-date-price" onchange="view_prices_object()"><?php echo $html; ?></select>
		<?php } ?>
			</div>
			<div class="col-sm-9">
		<?php if($id == 59 || $id == 42 || $quota != 1){ ?>
			<?php if($html){ ?>
				<button type="button" class="btn btn-default btn-sm" onclick="edit_date_price_manager()"><i class="fa fa-pencil"></i> Редактировать</button>
			<?php } ?>
				<button type="button" class="btn btn-info btn-sm" onclick="add_new_date_manager(<?php echo $id; ?>)"><i class="fa fa-calendar"></i> Новые даты</button>
		<?php } ?>
				<div class="pull-right">
			<?php if($reestablish == 1){ ?>
					<button type="button" class="btn btn-primary btn-sm" onclick="reestablish_price_date()"><i class="fa fa-angle-double-up"></i> Восстановить цены</button>
			<?php } ?>
					<button type="button" class="btn btn-success btn-sm" onclick="upload_object_price_on_server(<?php echo $id; ?>)" title="Загрузить на сайты цены номеров объекта по датам"><i class="fa fa-cloud-upload"></i> Загрузить цены на сайты</button>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-body html-price">

	</div>
</div>
<?php
}

function edit_date_price_manager($connect){
	if(isset($_POST["id"])){
		$id = $_POST["id"];
		$data = $connect->getRow("SELECT start, end FROM date_price WHERE id=?i", $id);
	}else{
		$data = array("start" => "", "end" => "");
		$object = $_POST["object"];
	}
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Интервал цен</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal date-price">
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата начала</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date-start" value="<?php echo $data['start']; ?>">
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Дата окончания</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date-end" value="<?php echo $data['end']; ?>">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
			<?php if($id){ ?>
				<button type="button" class="btn btn-success" onclick="update_date_price_manager(<?php echo $id; ?>)"><i class="fa fa-check-circle"></i> Сохранить</button>
				<button type="button" class="btn btn-danger btn-delete-range" disabled onclick="delete_date_price_manager(<?php echo $id; ?>)"><i class="fa fa-times-circle"></i> Удалить</button>
				<label class="control-label"><input type="checkbox" class="check-delete" onclick="check_delete_range_manager()"> подтверждаю удаление</label>
			<?php }else{ ?>
				<button type="button" class="btn btn-success" onclick="save_date_price_manager(<?php echo $object; ?>)"><i class="fa fa-check-circle"></i> Сохранить</button>
			<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_date_price_manager($connect){
	$connect->query("INSERT INTO date_price(id_obj, start, end) VALUES (?i, ?s, ?s)", $_POST["id"], $_POST["start"], $_POST["end"]);
}

function update_date_price_manager($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE date_price SET start=?s, end=?s WHERE id=?i", $_POST["start"], $_POST["end"], $id);
	return $connect->getOne("SELECT id_obj FROM date_price WHERE id=?i", $id);
}

function delete_date_price_manager($connect){
	$id = $_POST["id"];
	$object = $connect->getOne("SELECT id_obj FROM date_price WHERE id=?i", $id);
	$data = $connect->getAll("SELECT id FROM ranges WHERE id_date=?i", $id);
	foreach($data as $row){
		$connect->query("DELETE FROM price WHERE id_range=?i", $row["id"]);
		$connect->query("DELETE FROM ranges WHERE id=?i", $row["id"]);
	}
	$connect->query("DELETE FROM date_price WHERE id=?i", $id);
	return $object;
}

function view_prices_object($connect){
	$ranges = array();
	$TH = "";
	$have_price = 0;
	$id_date = $_POST["date"];
	$type_view = $_POST["type"];
	$TC = "";
	$TH = "";
	$object = $connect->getOne("SELECT id_obj FROM date_price WHERE id=?i", $id_date);
	$data = $connect->getAll("SELECT ranges.counter, ranges.id, ranges.name, ranges.type, place.name as place, ranges.treatment, place.type as place_type FROM ranges, place WHERE ranges.id_obj=?i AND (ranges.place=place.id) AND ranges.id_date=?i ORDER BY ranges.counter, place.type", $object, $id_date);
	foreach($data as $row){
		$id = $row["id"];
		$ranges[$id] = 1;
		$place = $row["place"];
		$type = $row["type"];
		if($row['place_type'] == 2)
			$place = "Доп. ".$place;
		else
			$place = "Основное ".$place;
		if($type == 1)
			$type = "за чел/сутки";
		elseif($type == 2)
			$type = "за дом/сутки";
		elseif($type == 3)
			$type = "за номер/сутки";
		elseif($type == 4)
			$type = "за заезд";

		$treatment_str = "";

		if((int)$row['treatment'] === 1) {
			$treatment_str .="<br />с лечением";
		}
		elseif((int)$row['treatment'] === 2) {
			$treatment_str .="<br />без лечения";
		}

		$th_name = $row["name"]." ".$place."<br />".$type.$treatment_str;
		$counter = $row["counter"];
		$TC.= "<td style='width:70px; border: none;' onclick='edit_range_counter(\"".$id."\")' id='tc_".$row["id"]."'>".$counter."</td>";
		$TH.= "<th style='width:70px;' onDblClick='edit_range_manager(\"".$id."\")' id='th_".$id."'>".$th_name."</th>";
	}
	$html = "<tr><td style='width: 250px;border: none;'></td>".$TC."<td style='border: none;'></td></tr><tr><td style='width: 250px;' rowspan='2'>Номера</td>".$TH."<td style='border: none; width: 150px;'><button type='button' class='btn btn-default btn-xs' onclick='add_new_range_manager(\"".$object."\")'><i class='fa fa-plus-circle'></i> Новый интервал</button></td></tr><tr></tr>";
	if($type_view == "priority"){
		$data = $connect->getAll("SELECT id FROM room WHERE id_obj=?i AND active=0 ORDER BY priority", $object);
		$old_housing = "";
		foreach($data as $row){
			$id_room = $row["id"];
			$room = get_room($connect, $id_room, "note");
			$html.= "<tr><td class='td_room name-room-".$id_room."'>".$room."</td>";
			foreach($ranges as $id_range => $range){
				$price = $connect->getRow("SELECT id, price FROM price WHERE id_range=?i AND id_room=?i AND active=0", $id_range, $id_room);
				$have_price = 1;
				$html.= "<td onclick='$(this).find(\"input\").focus();'><input type='text' class='form-control input-sm' value='".$price["price"]."' onblur='update_price_manager(this, \"".$id_room."\", \"".$id_range."\", \"".$price["id"]."\")' /></td>";
			}
			$html.= "<td style='border: none; width: 150px;'></td></tr>";
		}
		$button = "<button type='button' class='btn btn-sm btn-info' onclick='view_prices_object()'><i class='fa fa-home'></i> Сортировка по корпусам</button><br /><br />";
	}else{
		$data = $connect->getAll("SELECT room.id, room.name as room, room.note, housing.name FROM room, housing WHERE room.id_obj=?i AND (room.housing=housing.id) AND room.active=0 ORDER BY housing.name", $object);
		$old_housing = "";
		foreach($data as $row){
			$id_room = $row["id"];
			$housing = $row["name"];
			if(($housing != $old_housing) AND ($housing != '')){
				$old_housing = $housing;
				$html.= "<tr><td colspan='".(count($ranges) + 1)."'>".$housing."</td></tr>";
			}
			$room = get_room($connect, $id_room, "note");
			$html.= "<tr><td class='td_room name-room-".$id_room."'>".$room."</td>";
			foreach($ranges as $id_range => $range){
				$price = $connect->getRow("SELECT id, price FROM price WHERE id_range=?i AND id_room=?i AND active=0", $id_range, $id_room);
				$have_price = 1;
				$html.= "<td onclick='$(this).find(\"input\").focus();'><input type='text' class='form-control input-sm' value='".$price["price"]."' onblur='update_price_manager(this, \"".$id_room."\", \"".$id_range."\", \"".$price["id"]."\")' /></td>";
			}
			$html.= "<td style='border: none; width: 150px;'></td></tr>";
		}
		$data = $connect->getAll("SELECT id FROM room WHERE id_obj=?i AND (room.housing='' OR room.housing is NULL) AND active=0", $object);
		$html.= "<tr><td colspan='".(count($ranges) + 1)."'>Не определены</td></tr>";
		foreach($data as $row){
			$id_room = $row["id"];
			$room = get_room($connect, $id_room, "note");
			$html.= "<tr><td class='td_room name-room-".$id_room."'>".$room."</td>";
			foreach($ranges as $id_range => $range){
				$price = $connect->getRow("SELECT id, price FROM price WHERE id_range=?i AND id_room=?i AND active=0", $id_range, $id_room);
				$have_price = 1;
				$html.= "<td onclick='$(this).find(\"input\").focus();'><input type='text' class='form-control input-sm' value='".$price["price"]."' onblur='update_price_manager(this, \"".$id_room."\", \"".$id_range."\", \"".$price["id"]."\")' /></td>";
			}
			$html.= "<td style='border: none; width: 150px;'></td></tr>";
		}
		$button = "<button type='button' class='btn btn-sm btn-info' onclick='view_prices_object(\"\", \"priority\")'><i class='fa fa-sort-amount-desc'></i> Ручная сортировка</button><br /><br />";
	}
	if($html)
		$html = "<table class='table-price table table-bordered table-condensed'>".$html."</table><br />";
	if($have_price == 1){
		$dates = $connect->getAll("SELECT id FROM date_price WHERE id_obj=?i AND id!=?i AND active=0", $object, $id_date);
		foreach($dates as $date){
			if(!$connect->getOne("SELECT id FROM ranges WHERE id_date=?i AND active=0", $date["id"])){
				$html.= "<button type='button' class='btn btn-sm btn-default' onclick='show_dates_copy_prices()'><i class='fa fa-files-o'></i> Копировать цены на другие даты</button>";
				break;
			}
		}
	}
	$html = $button.$html;
	return $html;
}

function update_price_manager($connect){
	global $name_user;
	$id = $_POST["id"];
	$room = $_POST["room"];
	$range = $_POST["range"];
	$price = $_POST["price"];
	if(is_numeric($price) AND $price != 0){
		$date = date('d.m.Y H:m:s');
		if($connect->getOne("SELECT id FROM price WHERE id=?i", $id)){
			$connect->query("UPDATE price SET price=?s, date_last_save=?s, manager=?s WHERE id=?i", $price, $date, $name_user, $id);
			$html = $connect->getOne("SELECT price FROM price WHERE id=?i", $id);
		}elseif($connect->getOne("SELECT id FROM price WHERE id_range=?i AND id_room=?i AND active=0", $range, $room)){
			$connect->query("UPDATE price SET price=?s, date_last_save=?s, manager=?s WHERE id_range=?i AND id_room=?i AND active=0", $price, $date, $name_user, $range, $room);
			$html = $connect->getOne("SELECT price FROM price WHERE id_range=?i AND id_room=?i AND active=0", $range, $room);
		}else{
			$connect->query("INSERT INTO price(price, id_room, id_range, date_last_save, manager) VALUES(?s, ?i, ?i, ?s, ?s)", $price, $room, $range, $date, $name_user);
			$html = $price;
		}
	}else{
		if($connect->getOne("SELECT id FROM price WHERE id_range=?i AND id_room=?i AND active=0", $range, $room))
			$connect->query("DELETE FROM price WHERE id_range=?i AND id_room=?i AND active=0", $range, $room);
		$html = "";
	}
	return $html;
}

function edit_range_manager($connect){
	if(isset($_POST["id"])){
		$id = $_POST["id"];
		$row = $connect->getRow("SELECT id_obj, name, id_date, place, type, treatment
		 FROM ranges WHERE id=?i", $id);
		$place = get_place_object($connect, $row["id_obj"], $row["place"]);
		$dates = get_dates_object($connect, $row["id_obj"], $row["id_date"]);
	}else{
		$row = array("name" => "", "type" => "", "treatment" => 0);
		$object = $_POST["object"];
		$id_date = $_POST["date"];
		$place = get_place_object($connect, $object);
		$dates = get_dates_object($connect, $object, $id_date);
	}
	$type = get_type_price($row["type"]);
	$treatment = get_treatment_price($row["treatment"]);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Интервал</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal range-price">
					<div class="form-group">
						<label class="col-sm-4 control-label">Название</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="range_name" value="<?php echo $row['name']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата</label>
						<div class="col-sm-8">
							<?php echo $dates; ?>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Место</label>
						<div class="col-sm-8">
							<?php echo $place; ?>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Тип</label>
						<div class="col-sm-8">
							<?php echo $type; ?>
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Лечение</label>
						<div class="col-sm-8">
							<?php echo $treatment; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
			<?php if(isset($id)){ ?>
				<button type="button" class="btn btn-success" onclick="update_range_manager('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>&nbsp;
				<button type="button" class="btn btn-danger btn-delete-range" onclick="delete_range_manager('<?php echo $id; ?>')" disabled="disabled"><i class="fa fa-trash"></i> Удалить</button> <label class="control-label"><input type="checkbox" class="check-delete" onclick="check_delete_range_manager()"> подтверждаю удаление</label>
			<?php }else{ ?>
				<button type="button" class="btn btn-success" onclick="save_range_manager('<?php echo $object; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_range_manager($connect){
	$id = $_POST["id"];
	$date = $_POST["date"];
	$name = $_POST["name"];
	$type = $_POST["type"];
	$place = $_POST["place"];
	$treatment = $_POST["treatment"];
	$name = str_replace("plus", "+", $name);
	$old_date = $connect->getOne("SELECT id_date FROM ranges WHERE id=?i", $id);
	$connect->query("UPDATE ranges SET id_date=?i, name=?s, type=?i, place=?i, treatment=?i WHERE id=?i", $date, $name, $type, $place, $treatment, $id);
	$return = "";
	if($old_date == $date){
		$type_place = $connect->getOne("SELECT type FROM place WHERE id=?i", $place);
		$place = $connect->getOne("SELECT name FROM place WHERE id=?i", $place);
		if($type_place == 2)
			$place = "Доп. ".$place;
		else
			$place = "Основное ".$place;
		if($type == 1)
			$type = "за чел/сутки";
		elseif($type == 2)
			$type = "за дом";
		elseif($type == 3)
			$type = "за номер";
		elseif($type == 4)
			$type = "за заезд";

		$treatment_str = "";

		if((int)$treatment === 1) {
			$treatment_str .="<br />с лечением";
		}
		elseif((int)$treatment === 2) {
			$treatment_str .="<br />без лечения";
		}


		$return = $name." ".$place."<br />".$type.$treatment_str;
	}
	echo $return;
}

function save_range_manager($connect){
	$date = $_POST["date"];
	$name = $_POST["name"];
	$type = $_POST["type"];
	$place = $_POST["place"];
	$id_obj = $_POST["object"];
	$treatment = $_POST['treatment'];
	$connect->query("INSERT INTO ranges(id_date, name, type, place, id_obj, treatment) VALUES (?i, ?s, ?i, ?i, ?i, ?i)", $date, $name, $type, $place, $id_obj, $treatment);
}

function delete_range_manager($connect){
	$id = $_POST["id"];
	$connect->query("DELETE FROM price WHERE id_range=?i", $id);
	$connect->query("DELETE FROM ranges WHERE id=?i", $id);
}

function show_dates_copy_prices($connect){
	$id_date = $_POST["date"];
	$object = $connect->getOne("SELECT id_obj FROM date_price WHERE id=?i", $id_date);
	$dates = $connect->getAll("SELECT id, DATE_FORMAT(start, '%e.%m.%Y') as date_start, DATE_FORMAT(end, '%e.%m.%Y') as end FROM date_price WHERE id_obj=?i AND id!=?i AND active=0 ORDER BY start", $object, $id_date);
	$select = "";
	foreach($dates as $date){
		if(!$connect->getOne("SELECT id FROM ranges WHERE id_date=?i AND active=0", $date["id"])){
			$select.= "<option value='".$date["id"]."'>".$date["date_start"]." - ".$date["end"]."</option>";
		}
	}
	if(!$select)
		return;
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Копировать цены</h4>
			</div>
			<div class="modal-body form-horizontal copy-price">
				<div class="form-group form-group-bottom">
					<label class="col-sm-4 control-label">Выбор даты</label>
					<div class="col-sm-8">
						<select class="form-control new-id-date">
							<?php echo $select; ?>
						</select>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="copy_date_price()"><i class="fa fa-files-o"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function copy_date_price($connect){
	$id_date = $_POST["date"];
	$new_date = $_POST["new_date"];
	$object = $connect->getOne("SELECT id_obj FROM date_price WHERE id=?i", $id_date);
	$dates = $connect->getAll("SELECT id, name, type, place, counter FROM ranges WHERE id_date=?i AND active=0", $id_date);
	$date_last_save = date('d.m.Y H:m:s');
	foreach($dates as $date){
		$id_range = $date["id"];
		$connect->query("INSERT INTO ranges(name, type, place, counter, id_date, id_obj) VALUES(?s, ?i, ?i, ?i, ?i, ?i)", $date["name"], $date["type"], $date["place"], $date["counter"], $new_date, $object);
		$id_new_range = $connect->insertId();
		$prices = $connect->getAll("SELECT id_room, price FROM price WHERE id_range=?i AND active=0", $id_range);
		foreach($prices as $price){
			$connect->query("INSERT INTO price(id_room, price, id_range, date_last_save) VALUES(?i, ?s, ?i, ?s)", $price["id_room"], $price["price"], $id_new_range, $date_last_save);
		}
	}
	return $object;
}

function edit_room_manager($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, id_obj, main_place, add_place, food, square, housing, id_best_comfort, id_comfort, note, square, food FROM room WHERE id=?i", $id);
	$row = clear_quotes($row);
	$object = $row["id_obj"];
?>
<div class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить категорию номера</h4>
			</div>
			<div class="modal-body form-horizontal edit-room">
				<div class="form-group">
					<label class="col-sm-2 control-label">Номер</label>
					<div class="col-sm-10">
						<input type="text" class="form-control name-room" value="<?php echo $row['name']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Основных мест</label>
					<div class="col-sm-4">
						<select type="text" class="form-control main-place">
							<?php echo get_select_options(1, 10, $row["main_place"]); ?>
						</select>
					</div>
					<label class="col-sm-2 control-label">Доп.мест</label>
					<div class="col-sm-4">
						<select type="text" class="form-control add-place">
							<?php echo get_select_options(0, 10, $row["add_place"]); ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Питание</label>
					<div class="col-sm-4">
						<input type="text" class="form-control food-room" value="<?php echo $row['food']; ?>" />
					</div>
					<label class="col-sm-2 control-label">Площадь (кв.м.)</label>
					<div class="col-sm-4">
						<input type="text" class="form-control square-room" value="<?php echo $row['square']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Корпус</label>
					<div class="col-sm-4">
						<?php echo get_select_table($connect, "housing", "id_obj=".$object, $row["housing"], "housing-object", 1); ?>
					</div>
					<label class="col-sm-2 control-label">Примечание</label>
					<div class="col-sm-4">
						<input type="text" class="form-control note-room" value="<?php echo $row['note']; ?>" />
					</div>
				</div>
				<div class="form-group form-group-margin">
					<div class="col-sm-12 check-div">
						<div class="col-sm-12" id="best-comfort">
							<?php echo break_columns($connect, "comfort", 5, $row["id_best_comfort"], "WHERE type=1 ORDER BY name"); ?>
						</div>
						<div class="col-sm-12" id="comfort">
							<?php echo break_columns($connect, "comfort", 5, $row["id_comfort"], "WHERE type=0 ORDER BY name"); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="update_room_manager('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_room_manager($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$main = $_POST["main"];
	$add = $_POST["add"];
	$note = $_POST["note"];
	$food = $_POST["food"];
	$square = $_POST["square"];
	$housing = $_POST["housing"];
	$comfort = $_POST["comfort"];
	$best_comfort = $_POST["best_comfort"];
	$connect->query("UPDATE room SET name=?s, main_place=?i, add_place=?i, housing=?s, note=?s, id_comfort=?s, id_best_comfort=?s, food=?s, square=?s WHERE id=?i", $name, $main, $add, $housing, $note, $comfort, $best_comfort, $food, $square, $id);
	return get_room($connect, $id, "full");
}

function add_new_room_manager(){
	$object = $_POST["object"];
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить новый номер</h4>
			</div>
			<div class="modal-body form-horizontal new-room">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Название</label>
					<div class="col-sm-8">
						<input type="text" class="form-control name-room" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_new_room_manager('<?php echo $object; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_new_room_manager($connect){
	$object = $_POST["object"];
	$name = $_POST["name"];
	$connect->query("INSERT INTO room(name, id_obj) VALUES(?s, ?i)", $name, $object);
}

function reestablish_price_date($connect){
	$date = $_POST["date"];
	$connect->query("UPDATE date_price SET active=0 WHERE id=?i", $date);
	$data = $connect->getAll("SELECT id FROM ranges WHERE id_date=?i", $date);
	foreach($data as $row)
		$connect->query("UPDATE price SET active=0 WHERE id_range=?i", $row["id"]);
	$connect->query("UPDATE ranges SET active=0 WHERE id_date=?i", $date);
	return $connect->getOne("SELECT id_obj FROM date_price WHERE id=?i", $date);
}

function update_range_counter($connect){
	$id = $_POST["id"];
	$counter = $_POST["counter"];
	if($counter > 999)
		$counter = 999;
	elseif($counter < 1)
		$counter = 1;
	$connect->query("UPDATE ranges SET counter=?i WHERE id=?i", $counter, $id);
}

?>
