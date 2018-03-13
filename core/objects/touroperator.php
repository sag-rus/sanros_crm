<?php

function show_head_page_touroperator($connect){
	$first = array();
	$func = '';
	$data = $connect->getAll("SELECT id, short_name FROM tour_operator ORDER BY short_name");
	foreach($data as $row){
		$short_name = $row["short_name"];
		$id = $row["id"];
		$first[$id] = mb_strtoupper(mb_substr($short_name, 0, 1, "UTF-8"), "UTF-8");
	}
	$first = array_unique($first);
	$first_symbol = "";
	foreach($first as $symbol){
		$symbol_up = str_replace(" ", "&nbsp", strToUpper($symbol));
		$first_symbol.= "<li onclick='find_tour_operator(\"".$symbol."\")'><a>".$symbol_up."</a></li>";
	}
?>

<div class="form-horizontal">
	<div class="form-group form-group-margin">
		<div class="col-sm-5">
			<div class="input-group">
				<span class="input-group-addon"><i class="fa fa-search"></i></span>
				<input type="text" id="name_tour_operator" class="form-control" placeholder="Название туроператора" onkeyup="find_klient(event, 'name_tour_operator', 'tour_operator', 'select_tour_operator')" />
			</div>
		</div>
		<div class="col-sm-5">
			<button type="button" class="btn btn-default btn-sm" onclick="add_tour_operator()"><i class="fa fa-plus-circle"></i> Новый туроператор</button>
		</div>
	</div>
</div>
<ul class="pagination pagination-sm">
	<?php echo $first_symbol; ?>
</ul>
<div id="div_result"></div>
<?php
}

function find_tour_operator($connect){
	$stroka = $_POST["stroka"];
	$data = $connect->getAll("SELECT id, name, short_name FROM tour_operator WHERE short_name LIKE ?s ORDER BY short_name", $stroka."%");
	if(!$data)
		return "<div class='alert alert-info'>Ничего не найдено</div>";
?>
	<table class="table table-hover table-condensed">
	<tr>
		<th>Сокр.название</th>
		<th>Полное название</th>
	</tr>
<?php
	foreach($data as $array){
		$id = $array["id"];
?>
	<tr onclick="select_tour_operator(<?php echo $id; ?>)">
		<td width="40%"><?php echo $array["short_name"]; ?></td>
		<td width="60%"><?php echo$array["name"]; ?></td>
	</tr>
<?php
	}
?>
	</table>
<?php
}

function save_new_tour_operator($connect, $data = []){
	$short_name = $_POST["short_name"];
	$name = $_POST["name"];
	$telephone = $_POST["telephone"];
	$email = $_POST["email"];
	$fax = $_POST["fax"];
	$icq = $_POST["icq"];
	$skype = $_POST["skype"];
	$address = $_POST["address"];
	$website = $_POST["website"];
	$legal_address = $_POST["legal_address"];
	$connect->query("INSERT INTO tour_operator(name, short_name, telephone, email, fax, icq, skype, address, website, legal_address) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)", $name, $short_name, $telephone, $email, $fax, $icq, $skype, $address, $website, $legal_address);
	$id = $connect->insertId();
	return json_encode($id);
}

function select_tour_operator($connect){
	global $id_rights;
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT * FROM tour_operator WHERE id=?i", $id);
	$dogovor_tour_operator = select_tour_operator_contract($connect, $id);
	ob_start();
?>
<div class="form-horizontal panel panel-primary tour-operator-info">
	<div class="panel-heading">Туроператор <?php echo $row["short_name"]; ?>&nbsp;&nbsp;<i class="fa fa-ellipsis-h pointer" onclick="show_but_tour('<?php echo $id; ?>')" id="tour_active" /></i></div>
	<div class="list-group">
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Название</label>
				<div class="col-sm-10">
					<?php echo $row["name"]; ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Телефон</label>
				<div class="col-sm-4">
					<?php echo $row["telephone"]; ?>
				</div>
				<label class="col-sm-2 control-label-element">Факс</label>
				<div class="col-sm-4">
					<?php echo $row["fax"]; ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Email</label>
				<div class="col-sm-10">
					<?php echo $row["email"]; ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Адрес</label>
				<div class="col-sm-10">
					<?php echo $row["address"]; ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Юрид.адрес</label>
				<div class="col-sm-10">
					<?php echo $row["legal_address"]; ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Договор</label>
				<div class="col-sm-10">
					<div class="contracts-touroperator">
					<?php foreach($dogovor_tour_operator as $dogovor){ ?>
						<div class="contract-touroperator-<?php echo $dogovor['id']; ?>">
							<?php echo view_tour_operator_contract($dogovor); ?>
						</div>
					<?php } ?>
					</div>
					<?php if($id_rights >= 4){ ?>
					<div class="pull-right">
						<button class="btn btn-sm btn-primary" onclick="add_new_contract_tour_operator(<?php echo $id; ?>)"><i class="fa fa-file-text-o"></i> Новый договор</button>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<div class="col-sm-12 text-right">
					<span class="label label-default pointer" onclick="$('.hidden-info-tour').toggle()">Другая информация <i class="fa fa-angle-double-down"></i></span>
				</div>
			</div>
		</div>
		<div class="hidden-info-tour" style="display: none">
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-2 control-label">ICQ</label>
					<div class="col-sm-4">
						<div class="well well-sm"><?php echo $row["icq"]; ?>&nbsp;</div>
					</div>
					<label class="col-sm-2 control-label">Skype</label>
					<div class="col-sm-4">
						<div class="well well-sm"><?php echo $row["skype"]; ?>&nbsp;</div>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-2 control-label">БИК</label>
					<div class="col-sm-4">
						<div class="well well-sm"><?php echo $row["bik"]; ?>&nbsp;</div>
					</div>
					<label class="col-sm-2 control-label">Банк</label>
					<div class="col-sm-4">
						<div class="well well-sm"><?php echo $row["bank"]; ?>&nbsp;</div>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-2 control-label">К/счет</label>
					<div class="col-sm-4">
						<div class="well well-sm"><?php echo $row["ks"]; ?>&nbsp;</div>
					</div>
					<label class="col-sm-2 control-label">Р/счет</label>
					<div class="col-sm-4">
						<div class="well well-sm"><?php echo $row["rs"]; ?>&nbsp;</div>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-2 control-label">ИНН</label>
					<div class="col-sm-4">
						<div class="well well-sm"><?php echo $row["inn"]; ?>&nbsp;</div>
					</div>
					<label class="col-sm-2 control-label">КПП</label>
					<div class="col-sm-4">
						<div class="well well-sm"><?php echo $row["kpp"]; ?>&nbsp;</div>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-2 control-label">Сайт</label>
					<div class="col-sm-4">
						<div class="well well-sm"><?php echo $row["website"]; ?>&nbsp;</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="info_turist"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_menu_touroperator(){
	$id = $_POST["id"];
?>
	<span onclick="edit_tour_operator(<?php echo $id; ?>)">Редактировать</span>
	<span onclick="add_object_to_tour_operator(<?php echo $id; ?>)">Добавить объект</span>
	<span onclick="edit_tour_operator_sync_info(<?php echo $id; ?>)">Синхронизация 1С</span>
<?php
}

function edit_tour_operator($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT * FROM tour_operator WHERE id=?i", $id);
	$row = clear_quotes($row);
?>
<div class="form-horizontal panel panel-default edit">
	<div class="panel-heading"><i class="fa fa-pencil"></i> Изменить туроператор «<?php echo $row["short_name"]; ?>»</div>
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-3 control-label">Сокращенное название</label>
			<div class="col-sm-9">
				<input type="text" id="short_name" class="form-control" value="<?php echo $row['short_name']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Полное название</label>
			<div class="col-sm-9">
				<input type="text" id="name" class="form-control" value="<?php echo $row['name']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Email</label>
			<div class="col-sm-9">
				<input type="text" id="email" class="form-control" value="<?php echo $row['email']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Телефон</label>
			<div class="col-sm-9">
				<input type="text" id="telephone" class="form-control" value="<?php echo $row['telephone']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Адрес</label>
			<div class="col-sm-9">
				<input type="text" id="address" class="form-control" value="<?php echo $row['address']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Юридический адрес</label>
			<div class="col-sm-9">
				<input type="text" id="legal_address" class="form-control" value="<?php echo $row['legal_address']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Факс</label>
			<div class="col-sm-9">
				<input type="text" id="fax" class="form-control" value="<?php echo $row['fax']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">ICQ</label>
			<div class="col-sm-9">
				<input type="text" id="icq" class="form-control" value="<?php echo $row['icq']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">ИНН</label>
			<div class="col-sm-9">
				<input type="text" id="inn" class="form-control" value="<?php echo $row['inn']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">КПП</label>
			<div class="col-sm-9">
				<input type="text" id="kpp" class="form-control" value="<?php echo $row['kpp']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Р.счет</label>
			<div class="col-sm-9">
				<input type="text" id="rs" class="form-control" value="<?php echo $row['rs']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">К.счет</label>
			<div class="col-sm-9">
				<input type="text" id="ks" class="form-control" value="<?php echo $row['ks']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">БИК</label>
			<div class="col-sm-9">
				<input type="text" id="bik" class="form-control" value="<?php echo $row['bik']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Банк</label>
			<div class="col-sm-9">
				<input type="text" id="bank" class="form-control" value="<?php echo $row['bank']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Skype</label>
			<div class="col-sm-9">
				<input type="text" id="skype" class="form-control" value="<?php echo $row['skype']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Сайт</label>
			<div class="col-sm-9">
				<input type="text" id="website" class="form-control" value="<?php echo $row['website']; ?>" />
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="form-group form-group-margin">
			<div class="col-sm-offset-3 col-sm-9">
				<button class="btn btn-success btn-sm" onclick="update_tour_operator('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
				<button class="btn btn-danger btn-sm" onclick="select_tour_operator('<?php echo $id; ?>')"><i class="fa fa-times-circle"></i> Отмена</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_tour_operator($connect){
	$name = str_replace("plus", "+", $_POST["name"]);
	$connect->query("UPDATE tour_operator SET name=?s, short_name=?s, telephone=?s, email=?s, fax=?s, skype=?s, address=?s, website=?s, icq=?s, legal_address=?s, ks=?s, rs=?s, bik=?s, inn=?s, kpp=?s, bank=?s WHERE id=?i", $name, $_POST["short_name"], $_POST["telephone"], $_POST["email"], $_POST["fax"], $_POST["skype"], $_POST["address"], $_POST["website"], $_POST["icq"], $_POST["legal_address"], $_POST["ks"], $_POST["rs"], $_POST["bik"], $_POST["inn"], $_POST["kpp"], $_POST["bank"], $_POST["id"]);
}

function add_object_to_tour_operator(){
	$id = $_POST["id"];
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить объект к туроператору</h4>
			</div>
			<div class="modal-body form-horizontal add-object">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Объект</label>
					<div class="col-sm-8" id="object_name">
						<input type="text" class="form-control" id="object" onkeyup="find_klient(event, 'object', 'object', 'use_object')">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_object_to_tour_operator('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_object_to_tour_operator($connect){
	$id = $_POST["id"];
	$object = $_POST["object"];
	$ib_obj = $connect->getOne("SELECT id_obj FROM tour_operator WHERE id=?i", $id);
	$objects = explode("_", $ib_obj);
	$objects[] = $object;
	$objects = array_unique($objects);
	$objects = array_diff($objects, array(''));
	$connect->query("UPDATE tour_operator SET id_obj=?s WHERE id=?i", implode("_", $objects), $id);
}

function select_objects_of_tour_operator($connect){
	$id = $_POST["id"];
	$object = $connect->getOne("SELECT id_obj FROM tour_operator WHERE id=?i", $id);
	$objects = explode("_", $object);
	$objects = array_diff($objects, array(''));
	$html = "";
	foreach($objects as $id_obj){
		$id_tour = $connect->getOne("SELECT id_tour FROM object WHERE id=?i", $id_obj);
		$name_object = get_object($connect, $id_obj, "place");
		$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE id_obj=?i AND (id_tour is NULL OR id_tour='')", $id_obj);
		ob_start();
	?>
	<div class="list-group-item">
		<div class="form-group form-group-margin">
			<div class="col-sm-6">
				<?php echo $name_object; ?>
			</div>
			<div class="col-sm-6">
			<?php if($id_tour == $id AND $count > 0){ ?>
				<span class="label label-danger"><?php echo $count; ?></span> <button type="button" class="btn btn-success btn-xs" onclick="add_tour_operator_to_reck('<?php echo $id_obj; ?>', '<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Присвоить</button>
			<?php }elseif($id_tour != $id){ ?>
				<button type="button" class="btn btn-primary btn-xs" onclick="set_default_tour_operator('<?php echo $id_obj; ?>', '<?php echo $id; ?>')"><i class="fa fa-thumb-tack"></i> Туроператор по умолчанию</button>
			<?php } ?>
				<button type="button" class="btn btn-danger btn-xs" onclick="delete_object_tour_operator('<?php echo $id_obj; ?>', '<?php echo $id; ?>')"><i class="fa fa-times"></i> Удалить объект</button>
			</div>
		</div>
	</div>
	<?php
		$html.= ob_get_clean();
	}
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-home"></i> Объекты туроператора</div>
	<div class="list-group">
		<?php echo $html; ?>
		<?php if(!count($objects)){ ?>
			<div class="list-group-item list-group-item-info"><i class="fa fa-info-circle"></i> Объектов пока не добавлено</div>
		<?php } ?>
	</div>
</div>

<?php
	$html = ob_get_clean();
	return $html;
}

function set_default_tour_operator($connect){
	$id_obj = $_POST["id_obj"];
	$id_tour = $_POST["id_tour"];
	$connect->query("UPDATE object SET id_tour=?i WHERE id=?i", $id_tour, $id_obj);
}

function delete_object_tour_operator($connect){
	$id_obj = $_POST["id_obj"];
	$id_tour = $_POST["id_tour"];
	$ib_obj = $connect->getOne("SELECT id_obj FROM tour_operator WHERE id=?i", $id_tour);
	$objects = explode("_", $ib_obj);
	$objects = array_diff($objects, array(''));
	unset($objects[array_search($id_obj, $objects)]);
	$save = implode("_", $objects);
	$connect->query("UPDATE tour_operator SET id_obj=?s WHERE id=?i", $save, $id_tour);
	$connect->query("UPDATE object SET id_tour='' WHERE id=?i AND id_tour=?i", $id_obj, $id_tour);
}

function select_tour_operator_object($connect, $id = ""){
	if(!$id)
		$id = $_POST["id"];
	if(!$id)
		return;
	$html = "";
	$check = "";
	$id_tour = $connect->getOne("SELECT id_tour FROM object WHERE id=?i", $id);
	if($id_tour){
		$tour = $connect->getOne("SELECT short_name FROM tour_operator WHERE id=?i", $id_tour);
		$html = "<option value='".$id_tour."' selected>".$tour."</option>";
	}else
		$check = " selected ";
	$data = $connect->getAll("SELECT id, id_obj, short_name FROM tour_operator WHERE id_obj LIKE '%?i%'", $id);
	foreach($data as $row){
		$array = explode("_", $row["id_obj"]);
		if(array_search($id, $array) !== FALSE AND $row["id"] != $id_tour)
			$html.= "<option value='".$row["id"]."'>".$row["short_name"]."</option>";
	}
	if($html)
		$html = "<select class='form-control id-tour-operator'>".$html."<option value='' ".$check.">Бронирование напрямую</option></select>";
	return $html;
}

function get_name_tour_operator($connect){
	$id = $_POST["id"];
	return $connect->getOne("SELECT name FROM tour_operator WHERE id=?i", $id);
}

function add_tour_operator_to_reck($connect){
	$id_obj = $_POST["id_obj"];
	$id_tour = $_POST["id_tour"];
	$connect->query("UPDATE reckoning SET id_tour=?i WHERE id_obj=?i AND (id_tour IS NULL OR id_tour='')", $id_tour, $id_obj);
}

function edit_tour_operator_sync_info($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT 1C_code, 1C_full_name FROM tour_operator WHERE id=?i", $id);
	$row = clear_quotes($row);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить номер контрагента</h4>
			</div>
			<div class="modal-body form-horizontal edit-code">
				<div class="form-group">
					<label class="col-sm-4 control-label">Полное название</label>
					<div class="col-sm-8">
						<input type="text" class="form-control full-name-tour-operator" value="<?php echo $row['1C_full_name']; ?>" />
					</div>
				</div>
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Код контрагента</label>
					<div class="col-sm-8">
						<input type="text" class="form-control code-tour-operator" value="<?php echo $row['1C_code']; ?>" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_tour_operator_sync_info('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_tour_operator_sync_info($connect){
	$id = $_POST["id"];
	$code = $_POST["code"];
	$full_name = $_POST["full_name"];
	$connect->query("UPDATE tour_operator SET 1C_code=?s, 1C_full_name=?s WHERE id=?i", $code, $full_name, $id);
}

function select_tour_operator_contract($connect, $object){
	$today = date("Y-m-d");
	$data = $connect->getAll("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date_cont, number, active, status FROM tour_operator_contract WHERE tour_operator=?i AND date>=?s ORDER BY date DESC", $object, $today);
	return $data;
}

function view_tour_operator_contract($dogovor){
	global $id_rights;
	$class_label = "danger";
	$label = "Скан не получен";
	if($dogovor["status"] == 1 OR $dogovor["status"] == 2)
		$class_label = "success";
	if($dogovor["status"] == 1)
		$label = "Скан получен";
	if($dogovor["status"] == 2)
		$label = "Оригинал получен";
	ob_start();
?>
	<div class="form-group">
		<div class="col-sm-6">
			<?php echo " № ".$dogovor["number"].", действует до ".$dogovor["date_cont"]; ?>
			<span class="label label-<?php echo $class_label; ?>"><?php echo $label; ?></span>
		</div>
		<div class="col-sm-6">
		<?php if($id_rights >= 4){ ?>
			<button class="btn btn-default btn-xs" onclick="edit_contract_tour_operator(<?php echo $dogovor['id']; ?>)"><i class="fa fa-pencil"></i> изменить</button>
		<?php if($dogovor["status"] == 0){ ?>
			<button class="btn btn-success btn-xs" onclick="update_status_contract_tour_operator(<?php echo $dogovor['id']; ?>, 1)"><i class="fa fa-check"></i> скан получен</button>
		<?php } ?>
		<?php if($dogovor["status"] == 1){ ?>
			<button class="btn btn-success btn-xs" onclick="update_status_contract_tour_operator(<?php echo $dogovor['id']; ?>, 2)"><i class="fa fa-check"></i> оригинал получен</button>
		<?php } ?>
		<?php if($dogovor["status"] == 1 OR $dogovor["status"] == 2){ ?>
			<button class="btn btn-danger btn-xs" onclick="update_status_contract_tour_operator(<?php echo $dogovor['id']; ?>, 0)"><i class="fa fa-times"></i> сбросить статус</button>
		<?php } ?>
		<?php } ?>
		</div>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_contract_tour_operator($connect){
	$id = $_POST["id"];
	$date = $_POST["date"];
	$number = $_POST["number"];
	$connect->query("INSERT INTO tour_operator_contract(tour_operator, number, date) VALUES (?i, ?s, ?s)", $id, $number, $date);
	$id = $connect->insertId();
	$data = array();
	$row = $connect->getRow("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date_cont, number, active, status FROM tour_operator_contract WHERE id=?i", $id);
	$data["id"] = $id;
	$data["html"] = view_tour_operator_contract($row);
	return json_encode($data);
}

function edit_contract_tour_operator($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT number, date FROM tour_operator_contract WHERE id=?i", $id);
	return json_encode($row);
}

function update_contract_tour_operator($connect){
	$id = $_POST["id"];
	$date = $_POST["date"];
	$number = $_POST["number"];
	$connect->query("UPDATE tour_operator_contract SET number=?s, date=?s WHERE id=?i", $number, $date, $id);
	$row = $connect->getRow("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date_cont, number, active, status FROM tour_operator_contract WHERE id=?i", $id);
	$html = view_tour_operator_contract($row);
	return json_encode($html);
}

function update_status_contract_tour_operator($connect){
	$id = $_POST["id"];
	$status = $_POST["status"];
	$connect->query("UPDATE tour_operator_contract SET status=?i WHERE id=?i", $status, $id);
	$row = $connect->getRow("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date_cont, number, active, status FROM tour_operator_contract WHERE id=?i", $id);
	$html = view_tour_operator_contract($row);
	return json_encode($html);
}

?>
