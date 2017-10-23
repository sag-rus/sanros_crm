<?php

function add_new_reminder(){
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Новое напоминание</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal reminder">
					<div class="form-group">
						<label class="col-sm-4 control-label">Номер заявки</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="id_reminder" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Фамилия</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="surname_reminder" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Имя</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="name_reminder" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Отчество</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="otch_reminder" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Телефон</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="telephone_reminder" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Email</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="email_reminder" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Примечание</label>
						<div class="col-sm-8">
							<textarea class="form-control" id="note_reminder"></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата уведомления</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date_reminder" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="save_new_reminder()"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_new_reminder($connect){
	global $session_login;
	$schet = $_POST["schet"];
	$surname = $_POST["surname"];
	$name = $_POST["name"];
	$otch = $_POST["otch"];
	$telephone = $_POST["telephone"];
	$email = $_POST["email"];
	$date = $_POST["date"];
	$note = $_POST["note"];
	$connect->query("INSERT INTO reminder(user, schet, surname, name, otch, telephone, email, date, note) VALUES (?i, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)", $session_login, $schet, $surname, $name, $otch, $telephone, $email, $date, $note);
}

function select_my_reminder($connect){
	global $session_login, $id_rights;
	$time = time();
	$data = $connect->getAll("SELECT id, schet, name, surname, otch, telephone, email, DATE_FORMAT(date, '%d.%m.%Y') as date, note FROM reminder WHERE user=?i AND active=1 ORDER BY date", $session_login);
	foreach($data as $row){
		$fio = $row["surname"]." ".$row["name"]." ".$row["otch"];
		$telephone = $row["telephone"];
		$email = $row["email"];
		$id = $row["id"];
		$date = $row["date"];
		$note = $row["note"];
		$style = "primary";
		if(strToTime($date) <= $time)
			$style = "danger";
?>
	<div class="form-horizontal panel panel-<?php echo $style; ?>" id="rem_<?php echo $id; ?>">
		<div class="panel-heading">
		<?php if($row["schet"]){ ?>
			Заявка №<?php echo $row["schet"]; ?>
		<?php } ?>
		<?php if($fio){ ?>
			&nbsp;&nbsp;<?php echo $fio; ?>
		<?php } ?>
		</div>
		<div class="list-group">
		<?php if($row["telephone"]){ ?>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Телефон</label>
					<div class="col-sm-8">
						<?php echo $row["telephone"]; ?>
					</div>
				</div>
			</div>
		<?php } ?>
		<?php if($row["email"]){ ?>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Email</label>
					<div class="col-sm-8">
						<?php echo $row["email"]; ?>
					</div>
				</div>
			</div>
		<?php } ?>
		<?php if($row["note"]){ ?>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Примечание</label>
					<div class="col-sm-8">
						<?php echo $row["note"]; ?>
					</div>
				</div>
			</div>
		<?php } ?>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Дата уведомления</label>
					<div class="col-sm-8">
						<?php echo $row["date"]; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer" style="text-align: right">
			<button type="button" class="btn btn-default btn-xs" onclick="edit_reminder('<?php echo $id; ?>')">&nbsp;<i class="fa fa-pencil"></i>&nbsp;</button>
			<button type="button" class="btn btn-danger btn-xs" onclick="delete_reminder('<?php echo $id; ?>')">&nbsp;<i class="fa fa-trash-o"></i>&nbsp;</button>
		</div>
	</div>
<?php
	}
	if($id_rights > 3){
		$date = date("Y-m-d", strToTime("-7 days"));
		$data = $connect->getAll("SELECT id_reck, DATE_FORMAT(date, '%d.%m.%Y') as date_r, sum FROM return_query WHERE active=1 AND date<=?s", $date);
		foreach($data as $row){
			$id = $row["id_reck"];
			$array = $connect->getRow("SELECT agency, turist FROM reckoning WHERE id=?i", $id);
			if($array["agency"])
				$client = $connect->getOne("SELECT name FROM agency WHERE id=?i", $array["agency"]);
			else
				$client = $connect->getOne("SELECT surname FROM klient WHERE id=?i", $array["turist"]);
?>
	<div class="form-horizontal panel panel-danger">
		<div class="panel-heading">
			Ожидание возврата заявка №<?php echo $id; ?>
		</div>
		<div class="list-group">
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Дата заявления</label>
					<div class="col-sm-8">
						<?php echo month_transform($row["date_r"]); ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Клиент</label>
					<div class="col-sm-8">
						<?php echo $client; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Сумма</label>
					<div class="col-sm-8">
						<?php echo $row["sum"]; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
		}
		$date = date("Y-m-d");
		$data = $connect->getAll("SELECT id, turist, agency, DATE_FORMAT(date_z, '%d.%m.%Y') as zaezd, id_obj, sum FROM reckoning WHERE status=4 AND date_z<=?s", $date);
		foreach($data as $row){
			$id = $row["id"];
			if($row["agency"]){
				$client = $connect->getOne("SELECT name FROM agency WHERE id=?i", $row["agency"]);
				$id_client = $row["agency"];
				$type = "agency";
			}else{
				$client = $connect->getOne("SELECT surname FROM klient WHERE id=?i", $row["turist"]);
				$id_client = $row["turist"];
				$type = "";
			}
?>
	<div class="form-horizontal panel panel-danger">
		<div class="panel-heading">
			Частично оплаченая заявка <strong class="pointer" onclick="show_turist('<?php echo $id_client; ?>', '<?php echo $id; ?>', '<?php echo $type; ?>')">№<?php echo $id; ?></strong>
		</div>
		<div class="list-group">
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Дата заезда</label>
					<div class="col-sm-8">
						<?php echo month_transform($row["zaezd"]); ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Клиент</label>
					<div class="col-sm-8">
						<?php echo $client; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Объект</label>
					<div class="col-sm-8">
						<?php echo get_object($connect, $row["id_obj"]); ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Стоимость путевки</label>
					<div class="col-sm-8">
						<?php echo $row["sum"]; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
		}
	}
	$date = date("Y-m-d");
	$future = date("Y-m-d", strToTime("+5 days"));
	$data = $connect->getAll("SELECT reckoning.id, reckoning.turist, reckoning.agency, reckoning.id_obj FROM reckoning, region, object WHERE reckoning.id_obj=object.id AND ((region.id=object.id_reg AND region.id_country!=1) OR (object.id_reg is NULL OR object.id_reg='')) AND reckoning.date_z>?s AND reckoning.date_z<=?s AND status=5 AND id_user=?i GROUP BY reckoning.id", $date, $future, $session_login);
	foreach($data as $row){
		$id = $row["id"];
		$object = get_object($connect, $row["id_obj"]);
		if($row["agency"]){
			$client = $connect->getOne("SELECT name FROM agency WHERE id=?i", $row["agency"]);
			$id_client = $row["agency"];
			$type = "agency";
		}else{
			$client = $connect->getOne("SELECT surname FROM klient WHERE id=?i", $row["turist"]);
			$id_client = $row["turist"];
			$type = "";
		}
?>
	<div class="form-horizontal panel panel-primary">
		<div class="panel-heading">
			Скорый заезд по загранке. Заявка №<?php echo $id; ?>
		</div>
		<div class="list-group">
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Объект</label>
					<div class="col-sm-8">
						<?php echo $object; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer" style="text-align: right">
			<button type="button" class="btn btn-success btn-xs" onclick="show_turist('<?php echo $id_client; ?>', '<?php echo $id; ?>', '<?php echo $type; ?>')"> Перейти в заявку</button>
		</div>
	</div>
<?php
	}
?>
	<div class="text-right">
		<button type="button" onclick="add_new_reminder()" class="btn btn-default btn-sm"><i class="fa fa-plus-circle"></i> Создать новое</button>
	</div>
<?php
}

function edit_reminder($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT schet, surname, name, otch, email, telephone, date, note FROM reminder WHERE id=?i", $id);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить напоминание</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal reminder">
					<div class="form-group">
						<label class="col-sm-4 control-label">Номер заявки</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="id_reminder" value="<?php echo $row['schet']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Фамилия</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="surname_reminder" value="<?php echo $row['surname']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Имя</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="name_reminder" value="<?php echo $row['name']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Отчество</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="otch_reminder" value="<?php echo $row['otch']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Телефон</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="telephone_reminder" value="<?php echo $row['telephone']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Email</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="email_reminder" value="<?php echo $row['email']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Примечание</label>
						<div class="col-sm-8">
							<textarea class="form-control" id="note_reminder"><?php echo $row['note']; ?></textarea>
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Дата уведомления</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date_reminder" value="<?php echo $row['date']; ?>" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="update_reminder('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	echo $html;
}

function update_reminder($connect){
	$id = $_POST["id"];
	$schet = $_POST["schet"];
	$surname = $_POST["surname"];
	$name = $_POST["name"];
	$otch = $_POST["otch"];
	$telephone = $_POST["telephone"];
	$email = $_POST["email"];
	$date = $_POST["date"];
	$note = $_POST["note"];
	$connect->query("UPDATE reminder SET schet=?s, surname=?s, name=?s, otch=?s, telephone=?s, email=?s, date=?s, note=?s WHERE id=?i", $schet, $surname, $name, $otch, $telephone, $email, $date, $note, $id);
}

function delete_reminder($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reminder SET active=2 WHERE id=?i", $id);
}



function show_notification_user($connect){
	global $session_login, $id_rights;
	$data = $connect->getAll("SELECT id, text FROM notification WHERE user=?i AND status=0", $session_login);
	foreach($data as $row){
		$id = $row["id"];
?>
	<div class="list-group-item notification-<?php echo $id; ?>">
		<?php echo $row["text"]; ?>
		<button class="btn btn-danger btn-xs pull-right" onclick="confirm_notification(<?php echo $id; ?>)"><i class="fa fa-paint-brush"></i> Удалить</button>
	</div>
<?php
	}
	if($id_rights > 3){
		$data = $connect->getAll("SELECT id_reck FROM return_query WHERE active=1 AND check_pay=1");
		foreach($data as $row){
	?>
		<div class="list-group-item">
			Разрешен возврат по заявке №<?php echo $row["id_reck"]; ?>
		</div>
	<?php
		}
	}
}

function confirm_notification($connect){
	global $session_login;
	$id = $_POST["id"];
	$connect->query("UPDATE notification SET status=1 WHERE id=?i AND user=?i AND status=0", $id, $session_login);
	return json_encode(check_new_notification($connect));
}

?>
