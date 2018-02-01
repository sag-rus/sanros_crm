<?php

function selection_promotion($connect, $id){
	$row = $connect->getRow("SELECT id, type, DATE_FORMAT(date_end, '%d.%m.%Y') as date, active, title, text, id_user, id_obj FROM promotions WHERE id=?i", $id);
	$active = $row["active"];
	$id_prom = $row["id"];
	$object = $row["id_obj"];
	$type = $row["type"];
	if($type == "action")
		$icon = "<i class='fa fa-star'></i>";
	elseif($type == "warranty")
		$icon = "<i class='fa fa-check-circle-o'></i>";
	elseif($type == "fire")
		$icon = "<i class='fa fa-fire'></i>";
	elseif($type == "info")
		$icon = "<i class='fa fa-info-circle'></i>";
	$array = array(1 => array("btn" => "btn-default", "disabled" => ""), 2 => array("btn" => "btn-default", "disabled" => ""), 3 => array("btn" => "btn-default", "disabled" => ""));
	$class = "";
	if($active == 0)
		$class = " list-group-item-danger hidden ";
	elseif($active == 3){
		$class = " list-group-item-success ";
		$array[3]["btn"] = "btn-success";
	}elseif($active == 2){
		$class = " list-group-item-info ";
		$array[2]["btn"] = "btn-success";
	}else
		$array[1]["btn"] = "btn-success";
	$check_star = $connect->getOne("SELECT COUNT(*) FROM promotions WHERE active=2 AND id_obj=?i", $object);
	$check_vip = $connect->getOne("SELECT COUNT(*) FROM promotions WHERE active=3");
	if($check_star)
		$array[2]["disabled"] = " disabled ";
	if($check_vip >= 6)
		$array[3]["disabled"] = " disabled ";
	?>
	<a class="list-group-item <?php echo $class; ?>">
		<div class="form-horizontal">
		<h4 class="list-group-item-heading"><?php echo $icon; ?> <?php echo $row["title"]; ?></h4>
			<div class="list-group-item-text">
				<div class="form-group form-group-margin">
					<div class="col-sm-7">
						<?php echo $row["text"]; ?>
					</div>
					<div class="col-sm-2">
						<?php echo $row["date"]; ?>
					</div>
					<div class="col-sm-3 text-right">
						<button type="button" class="btn btn-default btn-sm" onclick="edit_promotion(<?php echo $id_prom; ?>)">&nbsp;<i class="fa fa-pencil"></i>&nbsp;</button>
						<div class="btn-group">
						<?php if($active == 0){ ?>
							<button type="button" class="btn btn-success btn-sm" onclick="check_status_promotion(<?php echo $id_prom; ?>, 'standart')">&nbsp;<i class="fa fa-arrow-circle-up"></i>&nbsp;</button>
						<?php }else{ ?>
							<button type="button" class="btn <?php echo $array[1]['btn']; ?> btn-sm" onclick="check_status_promotion(<?php echo $id_prom; ?>, 'standart')" title="Обычная акция">&nbsp;<i class="fa fa-star-o"></i>&nbsp;</button>
							<button type="button" class="btn <?php echo $array[2]['btn']; ?> btn-sm" onclick="check_status_promotion(<?php echo $id_prom; ?>, 'star')" title="Главная акция объекта" <?php echo $array[2]["disabled"]; ?>>&nbsp;<i class="fa fa-star"></i>&nbsp;</button>
							<button type="button" class="btn <?php echo $array[3]['btn']; ?> btn-sm" onclick="check_status_promotion(<?php echo $id_prom; ?>, 'VIP')" title="VIP акция" <?php echo $array[3]["disabled"]; ?>>&nbsp;<i class="fa fa-bolt"></i>&nbsp;</button>
							<button type="button" class="btn btn-danger btn-sm" onclick="check_status_promotion(<?php echo $id_prom; ?>, 'trash')" title="В архив">&nbsp;<i class="fa fa-trash"></i>&nbsp;</button>
						<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</a>
	<?php
}

function view_promotions_object($connect){
	global $id_rights;
	$id = $_POST["id"];
?>
<div class="panel panel-default">
	<div class="list-group promotions-object">
<?php
	$data = $connect->getAll("SELECT id FROM promotions WHERE id_obj=?i ORDER BY active", $id);
	foreach($data as $row){
?>
	<div class="promo-<?php echo $row['id']; ?>">
		<?php echo selection_promotion($connect, $row["id"]); ?>
	</div>
<?php
	}
?>
	</div>
	<div class="panel-footer text-right">
		<button type="button" class="btn btn-primary btn-sm" onclick="add_new_promotion('<?php echo $id; ?>')"><i class="fa fa-plus-circle"></i> Добавить</button>
		<button type="button" class="btn btn-success btn-sm" onclick="upload_promotions_object('<?php echo $id; ?>')"><i class="fa fa-cloud-upload"></i> Обновить на сайте</button>
		<button type="button" class="btn btn-danger btn-sm" onclick="$('.list-group-item-danger').removeClass('hidden')"><i class="fa fa-archive"></i> Акции в архиве</button>
	</div>
</div>
<?php
}

function add_new_promotion($connect){
	$id = $_POST["id"];
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить новую акцию</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-promo">
					<div class="form-group">
						<label class="col-sm-3 control-label">Тип</label>
						<div class="col-sm-9">
							<select class="form-control type-promo" onchange="edit_type_promotions('<?php echo $id; ?>')">
								<option value="action">Акция</option>
								<option value="fire">Горящая путевка</option>
								<option value="warranty">Гарантированный номер</option>
								<option value="info">Информация</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Название</label>
						<div class="col-sm-9">
							<input type="text" class="form-control title-promo" />
						</div>
					</div>
					<div class="form-group select-room" style="display: none">
						<label class="col-sm-3 control-label">Номер</label>
						<div class="col-sm-9" id="klient_room">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Описание<br /><span class="label-text">250</span></label>
						<div class="col-sm-9">
							<textarea class="form-control text-promo" onkeypress="check_size_limit('.text-promo', 250, '.label-text')" onchange="check_size_limit('.text-promo', 250, '.label-text')"></textarea>
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label">Окончание</label>
						<div class="col-sm-9">
							<input type="text" class="form-control datepicker" id="date-promo" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_new_promotion('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_new_promotion($connect){
	global $session_login;
	$id = (int)$_POST["id"];
	$end = $_POST["end"];
	$title = $_POST["title"];
	$text = $_POST['text'];
	$room = $_POST['room'];
	if(empty($room))
      $room = 0;
	$type = $_POST['type'];
	$today = date('Y-m-d');
	$connect->query("INSERT INTO promotions(type, date, date_end, title, text, id_obj, id_room, id_user) VALUES(?s, ?s, ?s, ?s, ?s, ?i, ?s, ?i)", $type, $today, $end, $title, $text, $id, $room, $session_login);
	$last = $connect->insertId();
?>
	<div class="promo-<?php echo $last; ?>">
		<?php echo selection_promotion($connect, $last); ?>
	</div>
<?php
}

function edit_promotion($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT type, id_obj, id_room, title, text, date_end FROM promotions WHERE id=?i", $id);
	$row = clear_quotes($row);
	$object = $row["id_obj"];
	$array = array();
	$array[$row["type"]] = "SELECTED";
	$style_room = " style='display: none' ";
	if($row["type"] == "fire" OR $row["type"] == "warranty")
		$style_room = "";
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить акцию</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-promo">
					<div class="form-group">
						<label class="col-sm-3 control-label">Тип</label>
						<div class="col-sm-9">
							<select class="form-control type-promo" onchange="edit_type_promotions('<?php echo $object; ?>')">
								<option <?php echo $array["action"]; ?> value="action">Акция</option>
								<option value="fire" <?php echo $array["fire"]; ?>>Горящая путевка</option>
								<option value="warranty" <?php echo $array["warranty"]; ?>>Гарантированный номер</option>
								<option value="info" <?php echo $array["info"]; ?>>Информация</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Название</label>
						<div class="col-sm-9">
							<input type="text" class="form-control title-promo" value="<?php echo $row['title']; ?>" />
						</div>
					</div>
					<div class="form-group select-room" <?php echo $style_room; ?>>
						<label class="col-sm-3 control-label">Номер</label>
						<div class="col-sm-9" id="klient_room">
							<?php if($row["type"] == "fire" OR $row["type"] == "warranty"){ ?>
							<select class="form-control" id="select-room">
								<?php
									$array = array();
									$array[$row["id_room"]] = " SELECTED ";
									$data = $connect->getAll("SELECT id FROM room WHERE id_obj=?i AND active=0 ORDER BY name", $object);
									foreach($data as $row_room){
										$id_room = $row_room["id"];
										$room = get_room($connect, $id_room, "full");
										echo "<option value='".$id_room."' ".$array[$id_room].">".$room."</option>";
									}
								?>
							</select>
							<?php } ?>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Описание<br /><span class="label-text"><?php echo (250 - mb_strlen($row["text"], "UTF-8")); ?></span></label>
						<div class="col-sm-9">
							<textarea class="form-control text-promo" onkeypress="check_size_limit('.text-promo', 250, '.label-text')" onchange="check_size_limit('.text-promo', 250, '.label-text')"><?php echo $row["text"]; ?></textarea>
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label">Окончание</label>
						<div class="col-sm-9">
							<input type="text" class="form-control datepicker" id="date-promo" value="<?php echo $row['date_end']; ?>" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_promotion('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}


function update_promotion($connect){
	$id = $_POST["id"];
	$end = $_POST["end"];
	$title = $_POST["title"];
	$text = $_POST["text"];
	$type = $_POST["type"];
	$room = $_POST["room"];
	$connect->query("UPDATE promotions SET type=?s, id_room=?s, date_end=?s, title=?s, text=?s WHERE id=?i", $type, $room, $end, $title, $text, $id);
	return selection_promotion($connect, $id);
}

function check_status_promotion($connect){
	$id = $_POST["id"];
	$status = $_POST["status"];
	$object = $connect->getOne("SELECT id_obj FROM promotions WHERE id=?i", $id);
	if($status == "standart")
		$connect->query("UPDATE promotions SET active=1 WHERE id=?i", $id);
	elseif($status == "trash")
		$connect->query("UPDATE promotions SET active=0 WHERE id=?i", $id);
	elseif($status == "star"){
		if(!$connect->getOne("SELECT id FROM promotions WHERE active=2 AND id_obj=?i", $object))
			$connect->query("UPDATE promotions SET active=2 WHERE id=?i", $id);
	}elseif($status == "VIP"){
		$count = $connect->getOne("SELECT COUNT(*) FROM promotions WHERE active=3");
		if($count < 6)
			$connect->query("UPDATE promotions SET active=3 WHERE id=?i", $id);
	}
	return selection_promotion($connect, $id);
}

function menu_all_promotions(){
	ob_start();
?>
<div class="promo-menu" style="font-size: 14pt; margin: 10px;">
	<span class="label label-default pointer all" onclick="view_all_promotions('all')">Всё</span>
	<span class="label label-default pointer VIP" onclick="view_all_promotions('VIP')"><i class="fa fa-bolt"></i> ВИП акции</span>
	<span class="label label-default pointer action" onclick="view_all_promotions('action')"><i class="fa fa-star"></i> Акции</span>
	<span class="label label-default pointer warranty" onclick="view_all_promotions('warranty')"><i class="fa fa-check-circle-o"></i> Гарант. номера</span>
	<span class="label label-default pointer fire" onclick="view_all_promotions('fire')"><i class="fa fa-fire"></i> Горящие путевки</span>
	<span class="label label-default pointer info" onclick="view_all_promotions('info')"><i class="fa fa-info-circle"></i> Информация</span>
</div>
<div class="promo-html"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function view_all_promotions($connect){
	$type = $_POST["type"];
	if($type == "all")
		$type = "";
	elseif($type == "VIP")
		$type = "promotions.active=3 AND ";
	else
		$type = "promotions.type='$type' AND ";
	$old_region = "";
	$old_object = "";
	$index_region = 0;
	$data = $connect->getAll("SELECT id, name FROM region WHERE active=0");
	foreach($data as $row){
		$id_reg = $row["id"];
		$data2 = $connect->getAll("SELECT id, name FROM object WHERE id_reg=?i AND active=0", $id_reg);
		foreach($data2 as $row){
			$id_obj = $row["id"];
			$object = $row["name"];
			$code = "";
			$data3 = $connect->getAll("SELECT id FROM promotions WHERE id_obj=?i AND ".$type." active!=0 ORDER BY active DESC", $id_obj);
			foreach($data3 as $row){
				$id = $row["id"];
				ob_start();
			?>
			<div class="promo-<?php echo $id; ?>">
				<?php echo selection_promotion($connect, $id); ?>
			</div>
			<?php
				$code.= ob_get_clean();
			}
			if($code){
			?>
			<div class="panel panel-default">
				<div class="panel-heading"><?php echo $object; ?></div>
				<div class="list-group">
					<?php echo $code; ?>
				</div>
			</div>
			<?php
			}
		}
	}
?>
	<div class="text-right">
		<button type="button" class="btn btn-success btn-sm" onclick="upload_promotions()"><i class="fa fa-cloud-upload"></i> Загрузить все акции</button>
	</div>
<?php
}

?>
