<?php

function select_all_client_bonus($connect){
	$client = $_POST["id"];
	return all_klient_bonus($connect, $client);
}

function select_client($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id, photo, surname, name, otch, DATE_FORMAT(date, '%d.%m.%Y') as date, address, email, passport, output, DATE_FORMAT(date_pas, '%d.%m.%Y') as date_pas, note, telephone, icq, vk, fb, skype, mail, od_cl, tw, active, login, service_note FROM klient WHERE id=?i", $id);
	$fio = $row["surname"]." ".$row["name"]." ".$row["otch"];
	if(!$row["id"])
		return FALSE;
	$active = $row["active"];
	$cabinet = "&nbsp;";
	if($row["login"]){
		$time = $connect->getOne("SELECT DATE_FORMAT(date, '%d.%m.%Y %H:%i:%s') as date FROM session_account WHERE login=?s", $row["login"]);
		if(!$time)
			$time = "никогда";
		if($active == 1)
			$cabinet = "<i class='fa fa-smile-o fa-3x icon_download' title='Дата последнего входа ".$time."'></i>";
		else
			$cabinet.= "<i class='fa fa-frown-o fa-3x icon_delete'></i>";
		}elseif($connect->getOne("SELECT id FROM klient WHERE id=?i AND email!='' AND (login='' OR login IS NULL)", $id))
			if(!$connect->getOne("SELECT id FROM klient WHERE login=?s", $row["email"]))
				$cabinet = "<button type='button' class='btn btn-info btn-xs' onclick='send_password_klient(\"".$id."\")'><i class='fa fa-key'></i> Пароль</button>";
	$date = month_transform($row["date"]);
	$date_pas = month_transform($row["date_pas"]);
	$photo = "images/NoPicture.jpg";
	if($row["photo"])
		$photo = "data:image/jpg;base64,".$row["photo"];
	$bonus = all_klient_bonus($connect, $id);
	ob_start();
?>
<button type="button" class="btn btn-warning btn-xs" onclick="show_prev_page()"><i class="fa fa-angle-double-left"></i> вернуться назад</button>
<div class="form-horizontal panel panel-primary" style="width: 700px; margin-top: 10px" id="klient" name="<?php echo $id; ?>">
	<div class="panel-heading"><?php echo $fio; ?>&nbsp;&nbsp;<i class="fa fa-ellipsis-h pointer" onclick="show_menu_client('<?php echo $id; ?>')" id="menu-client"></i></div>
	<div class="panel-body">
		<div class="form-group">
			<div class="col-sm-2 text-center">
				<img class="img-thumbnail" src="<?php echo $photo; ?>" />
			</div>
			<div class="col-sm-8">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-4 control-label">Телефон</label>
						<div class="col-sm-8">
							<div class="well well-sm"><?php echo $row["telephone"]; ?>&nbsp;</div>
						</div>
					</div>
				<?php if($row["email"]){ ?>
					<div class="form-group">
						<label class="col-sm-4 control-label">Email</label>
						<div class="col-sm-8">
							<div class="well well-sm"><?php echo $row["email"]; ?></div>
						</div>
					</div>
				<?php } ?>
				<?php if($date){ ?>
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата рождения</label>
						<div class="col-sm-8">
							<div class="well well-sm"><?php echo $date; ?></div>
						</div>
					</div>
				<?php } ?>
				<?php if($row["address"]){ ?>
					<div class="form-group">
						<label class="col-sm-4 control-label">Адрес</label>
						<div class="col-sm-8">
							<div class="well well-sm"><?php echo $row["address"]; ?></div>
						</div>
					</div>
				<?php } ?>
					<div class="form-group">
						<label class="col-sm-4 control-label">Бонусы</label>
						<div class="col-sm-8">
							<div class="well-sm alert alert-success all-bonus"><?php echo $bonus; ?></div>
						</div>
					</div>
					<?php if($row["service_note"]){ ?>
					<div class="form-group">
						<label class="col-sm-4 control-label">Переплаты</label>
						<div class="col-sm-8">
							<div class="well well-sm"><?php echo $row["service_note"]; ?>&nbsp;</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
			<div class="col-sm-2" style="text-align: center">
				<?php echo $cabinet; ?>
				<?php if(search_similar_klients($connect, $id)){ ?>
					<div style="margin-top: 10px">
						<i class="fa fa-exclamation-triangle fa-3x icon_warning pointer" onclick="search_similar_klient('<?php echo $id; ?>')"></i>
					</div>
				<?php } ?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-12">
				<span class="label label-default pointer" onclick="$('.hide_info').toggle()">Другая информация <i class="fa fa-angle-double-down"></i></span>
			</div>
		</div>
		<div class="form-group hide_info" style="display: none">
			<label class="col-sm-3 control-label">Паспорт</label>
			<div class="col-sm-9">
				<div class="well well-sm"><?php echo $row["passport"]; ?>&nbsp;</div>
			</div>
		</div>
		<div class="form-group hide_info" style="display: none">
			<label class="col-sm-3 control-label">Паспорт выдан</label>
			<div class="col-sm-9">
				<div class="well well-sm"><?php echo $date_pas." ".$row["output"]; ?>&nbsp;</div>
			</div>
		</div>
		<div class="form-group hide_info" style="display: none">
			<label class="col-sm-3 control-label">Примечание</label>
			<div class="col-sm-9">
				<div class="well well-sm"><?php echo $row["note"]; ?>&nbsp;</div>
			</div>
		</div>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-default btn-xs" onclick="view_history_klient()"><i class="fa fa-history"></i> История</button>
		<button type="button" class="btn btn-success btn-xs" onclick="klient_schet()"><i class="fa fa-pencil-square-o"></i> Заявки</button>
		<button type="button" class="btn btn-info btn-xs" onclick="klient_bonus()"><i class="fa fa-money"></i> Бонусы</button>
		<?php if($connect->getOne("SELECT id FROM certificate WHERE klient=?i", $id)){ ?>
			<button type="button" class="btn btn-primary btn-xs" onclick="klient_certificate()"><i class="fa fa-file-text-o"></i> Сертификаты</button>
		<?php } ?>
	</div>
</div>
<div id="info_turist"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_menu_client($connect){
	global $id_rights;
	$id = $_POST["id"];
?>
	<span onclick="edit_klient(<?php echo $id; ?>)">Редактировать</span>
	<span onclick="new_reck()">Новая заявка</span>
	<?php if(all_klient_bonus($connect, $id)){ ?>
		<span onclick="transfer_bonuses(<?php echo $id; ?>)">Передать бонусы</span>
	<?php } ?>
	<?php if(!$connect->getOne("SELECT id FROM reckoning WHERE turist=?i OR rest LIKE ?s", $id, "'".$id."'")){ ?>
		<span onclick="delete_klient(<?php echo $id; ?>)">Удалить клиента</span>
	<?php } ?>
	<span onclick="client_payers(<?php echo $id; ?>)">Плательщики</span>
	<span onclick="new_gift_certificate(<?php echo $id; ?>)">Подарочный сертификат</span>
	<?php if($id_rights > 4){ ?>
		<span onclick="counted_bonus_client(<?php echo $id; ?>)">Пересчитать бонусы</span>
		<span onclick="add_bonus_client(<?php echo $id; ?>)">Добавить бонусы</span>
		<span onclick="clear_login_client(<?php echo $id; ?>)">Удалить личный кабинет</span>
	<?php } ?>
	<?php if($connect->getOne("SELECT login FROM klient WHERE id=?i", $id)){ ?>
		<span onclick="send_password_klient(<?php echo $id; ?>)">Выслать пароль повторно</span>
	<?php } ?>
<?php
}

function counted_bonus_client($connect){
	global $bonus_rec;
	$id = $_POST["id"];
	$data = $connect->getAll("SELECT id, status, sum, date FROM reckoning WHERE turist=?i", $id);
	foreach($data as $row){
		$schet = $row["id"];
		$sum = $row["sum"];
		$status = $row["status"];
		$date = $row["date"];
		if($status == 5){
			$count = count($connect->getAll("SELECT id FROM bonus WHERE sum>=0 AND schet=?i", $schet));
			if($count > 1)
				$connect->query("DELETE FROM bonus WHERE schet=?i AND sum>=0 LIMIT ".($count -1), $schet);
			elseif($count == 0)
				$connect->query("INSERT INTO bonus(date, schet, turist, sum) VALUES (?s, ?i, ?i, 0)", $date, $schet, $id);
			$bonus = $bonus_rec * $sum;
			if($bonus > 0)
				$connect->query("UPDATE bonus SET sum=?s WHERE schet=?i AND sum>=0", $bonus, $schet);
		}else
			$connect->query("DELETE FROM bonus WHERE schet=?i AND sum>=0", $schet);
		$connect->query("DELETE FROM bonus WHERE sum=0 AND turist=?i", $id);
	}
}

function search_similar_klients($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT surname, name, otch, email, telephone, unlike FROM klient WHERE id=?i", $id);
	$surname = $row["surname"];
	$name = $row["name"];
	$otch = $row["otch"];
	$email = $row["email"];
	$telephone = $row["telephone"];
	$query = "SELECT id, surname, name, otch, email, telephone FROM klient WHERE id!=?i AND ((surname=?s AND name=?s AND otch=?s)";
	if($email)
		$query.= " OR (email='$email')";
	if($telephone)
		$query.= " OR (telephone='$telephone')";
	$query.= ")";
	if($row["unlike"]){
		$unlike = json_decode($row["unlike"], TRUE);
		$unlike_query = "";
		foreach($unlike as $id_unlike){
			$unlike_query.= " id!=".$id_unlike;
		}
		if($unlike_query)
			$query.= " AND (".$unlike_query.")";
	}
	$data = $connect->getAll($query, $id, $surname, $name, $otch);
	if(!count($data))
		return FALSE;
	ob_start();
?><div class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Похожие туристы</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal unite-klient panel panel-default">
					<div class="panel-heading"><?php echo $surname." ".$name." ".$otch; ?></div>
					<div class="panel-body">
<?php
	$html = ob_get_clean();
	foreach($data as $row){
		$id_klient = $row["id"];
		$surname_other = $row["surname"];
		$name_other = $row["name"];
		$otch_other = $row["otch"];
		$telephone_other = $row["telephone"];
		$email_other = $row["email"];
		$class = array("fio" => "", "telephone" => "", "email" => "");
		if($surname_other == $surname)
			$class["fio"] = " text-danger ";
		if($telephone_other == $telephone)
			$class["telephone"] = " text-danger ";
		if($email_other == $email)
			$class["email"] = " text-danger ";
		ob_start();
?>
	<div class="form-group">
		<label class="col-sm-4 control-label <?php echo $class['fio']; ?>">
			<span class="link" onclick="select_klient('<?php echo $id_klient; ?>')"><?php echo $surname_other." ".$name_other." ".$otch_other; ?></span>
		</label>
		<label class="col-sm-3 control-label <?php echo $class['email']; ?>">
			<?php echo $email_other; ?>
		</label>
		<label class="col-sm-3 control-label <?php echo $class['telephone']; ?>">
			<?php echo $telephone_other; ?>
		</label>
		<label class="col-sm-2 control-label">
			<input type="radio" name="sim_klient" value="<?php echo $id_klient; ?>" /> выбрать
		</label>
	</div>
<?php
		$html.= ob_get_clean();
	}
	ob_start();
?>
				</div>
			</div>
			</div>
		<?php if(!$connect->getOne("SELECT id FROM klient WHERE id=?i AND login!=''", $id)){ ?>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="unite_klients('<?php echo $id; ?>')"><i class="fa fa-paperclip"></i> Объединить</button>
			</div>
		<?php } ?>
		</div>
	</div>
</div>
<?php
	$html.= ob_get_clean();
	return $html;
}

function add_bonus_client(){
	$id = $_POST["id"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить бонусы туристу</h4>
			</div>
			<div class="modal-body form-horizontal add-bonus">
				<div class="form-group">
					<label class="col-sm-4 control-label">Кол-во бонусов</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="bonus" onKeyPress="validate_sum('bonus')" />
					</div>
				</div>
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Примечание</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="note" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_bonus_client('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_bonus_client($connect){
	$id = $_POST["id"];
	$bonus = $_POST["bonus"];
	$note = $_POST["note"];
	$connect->query("INSERT INTO bonus(date, turist, sum, type, note) VALUES (?s, ?i, ?s, 3, ?s)", date("Y-m-d"), $id, $bonus, $note);
}

function new_gift_certificate(){
	$id = $_POST["id"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Новый сертификат</h4>
			</div>
			<div class="modal-body form-horizontal add-certificate">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Сумма</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="sum_cer" onKeyPress="validate_sum('sum_cer')" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_new_gift_certificate('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}


function save_new_gift_certificate($connect){
	$id = $_POST["id"];
	$sum = $_POST["sum"];
	$count = 1;
	while($count == 1){
		$uniq = mt_rand(1000000, 9999999);
		if(!$connect->getOne("SELECT id FROM certificate WHERE key=?i", $uniq))
			$count = 0;
	}
	$connect->query("INSERT INTO certificate(sum, klient, date, code) VALUES(?s, ?i, ?s, ?i)", $sum, $id, date("Y-m-d"), $uniq);
	$last_id = $connect->insertId();
	save_certificate_to_history($connect, $last_id);
}

function view_history_klient($connect){
	$html = "";
	$id = $_POST["id"];
	$data = $connect->getAll("SELECT DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date_history, note FROM history_client WHERE client=?i ORDER BY date", $id);
	foreach($data as $row){
		$html.= "<tr>";
		$html.= "<td width='170'>".$row["date_history"]."</td>";
		$html.= "<td width='400'>".$row["note"]."</td>";
		$html.= "</tr>";
	}
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-history"></i> История туриста</div>
	<?php if($data){ ?>
		<table class="table table-condensed">
		<tr>
			<th>Время</th>
			<th>Действие</th>
		</tr>
		<?php echo $html; ?>
		</table>
	<?php }else{ ?>
		<div class="panel-body">
			Ничего не найдено
		</div>
	<?php } ?>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function see_certificate_client($connect){
	$id = $_POST["id"];
	$data = $connect->getAll("SELECT sum, DATE_FORMAT(date, '%d.%m.%Y') as date, status, DATE_FORMAT(date_pay, '%d.%m.%Y') as date_pay, schet FROM certificate WHERE klient=?i", $id);
	foreach($data as $row){
		$sum = $row["sum"];
		$status = $row["status"];
		$schet = $row["schet"];
		$status_name = $connect->getOne("SELECT name FROM status_cert WHERE id=?i", $status);
		$date = $row["date"];
		$note = "&#151;";
		if($status == 4)
			$note = "Оплачен ".$row["date_pay"];
		elseif($status == 6)
			$note = "Использован в заявке №".$schet;
		$html.= "<tr>";
		$html.= "<td width='100'>".$date."</td>";
		$html.= "<td width='80'>".$sum."</td>";
		$html.= "<td width='150'>".$status_name."</td>";
		$html.= "<td width='150'>".$note."</td>";
		$html.= "</tr>";
	}
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-file-powerpoint-o"></i> Сертификаты туриста</div>
	<?php if(count($data)){ ?>
		<table class="table table-condensed">
		<tr>
			<th>Дата</th>
			<th>Сумма</th>
			<th>Статус</th>
			<th>Примечание</th>
		</tr>
			<?php echo $html; ?>
		</table>
	<?php }else{ ?>
		<div class="panel-body">
			Ничего не найдено
		</div>
	<?php } ?>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function unite_client($connect){
	$note = "";
	$id = $_POST["id"];
	$id_radio = $_POST["id_radio"];
	$login1 = $connect->getOne("SELECT login FROM klient WHERE id=?i", $id);
	$login2 = $connect->getOne("SELECT login FROM klient WHERE id=?i", $id_radio);
	if($login1 AND $login2)
		return FALSE;
	if($login1){
		$a = $id;
		$id = $id_radio;
		$id_radio = $a;
	}
	$data1 = $connect->getRow("SELECT * from klient WHERE id=?i", $id);
	$data2 = $connect->getRow("SELECT * from klient WHERE id=?i", $id_radio);
	if($data1["date_pas"] == "0000-00-00") $data1["date_pas"] = "";
	if($data2["date_pas"] == "0000-00-00") $data2["date_pas"] = "";
	if($data1["date"] == "0000-00-00") $data1["date"] = "";
	if($data2["date"] == "0000-00-00") $data2["date"] = "";
	if($data1["email"] AND !$data2["email"])
		$connect->query("UPDATE klient SET email=?s WHERE id=?i", $data1["email"], $id_radiog);
	elseif($data1["email"] AND ($data1["email"] != $data2["email"]))
		$note.= "Email: ".$data1["email"]."   ";
	if($data1["telephone"] AND !$data2["telephone"])
		$connect->query("UPDATE klient SET telephone=?i WHERE id=?i", $data1["telephone"], $id_radio);
	elseif($data1["telephone"] AND ($data1["telephone"] != $data2["telephone"]))
		$note.= "Телефон: ".$data1["telephone"]."   ";
	if($data1["address"] AND !$data2["address"])
		$connect->query("UPDATE klient SET address=?s WHERE id=?i", $data1["address"], $id_radio);
	elseif($data["address"] AND ($data1["address"] != $data2["address"]))
		$note.= "Адрес: ".$data1["address"]."   ";
	if($data1["date"] AND !$data2["date"])
		$connect->query("UPDATE klient SET date=?s WHERE id=?i", $data1["date"], $id_radio);
	elseif($data1["date"] AND ($data1["date"] != $data2["date"]))
		$note.= "Дата рождения: ".$data1["date"]."   ";
	if($data1["passport"] AND !$data2["passport"])
		$connect->query("UPDATE klient SET passport=?s WHERE id=?i", $data1["passport"], $id_radio);
	elseif($data1["passport"] AND ($data1["passport"] != $data2["passport"]))
		$note.= "Паспорт: ".$data1["passport"]."   ";
	if($data1["output"] AND !$data2["output"])
		$connect->query("UPDATE klient SET output=?s WHERE id=?i", $row["output"], $id_radio);
	elseif($data1["output"] AND ($data1["output"] != $data2["output"]))
		$note.= "Паспорт выдан: ".$data1["output"]."   ";
	if($data1["date_pas"] AND !$data2["date_pas"]){
		$connect->query("UPDATE klient SET date_pas=?s WHERE id=?i", $data1["date_pas"], $id_radio);
	}elseif($data1["date_pas"] AND ($data1["date_pas"] != $data2["date_pas"]))
		$note.= "Паспорт выдан: ".$data1["date_pas"]."   ";
	$data = $connect->getAll("SELECT id, rest FROM reckoning WHERE turist=?i", $id);
	foreach($data as $row){
		$rest = explode(",", $row["rest"]);
		$key = array_search($id, $rest);
		$rest[$key] = $id_radio;
		$rest = array_diff($rest, array(""));
		$new_rest = implode(",", $rest);
		$connect->query("UPDATE reckoning SET rest=?s, turist=?i WHERE id=?i", $new_rest, $id_radio, $row["id"]);
	}
	$connect->query("UPDATE reckoning SET turist=?i WHERE turist=?i", $id_radio, $id);
	$connect->query("UPDATE booking_request_object_module SET turist=?i WHERE turist=?i", $id_radio, $id);
	$connect->query("UPDATE bonus SET turist=?i WHERE turist=?i", $id_radio, $id);
	$connect->query("UPDATE klient SET note=?s WHERE id=?i", $note, $id_radio);
	$connect->query("DELETE FROM klient WHERE id=?i", $id);
}

function unlike_client($connect){
	$id = $_POST["id"];
	$id_radio = $_POST["id_radio"];
	$unlike = json_decode($connect->getOne("SELECT unlike FROM klient WHERE id=?i", $id), TRUE);
	$unlike[] = $id_radio;
	$connect->query("UPDATE klient SET unlike=?s WHERE id=?i", json_encode($unlike), $id);
	$unlike = json_decode($connect->getOne("SELECT unlike FROM klient WHERE id=?i", $id_radio), TRUE);
	$unlike[] = $id;
	$connect->query("UPDATE klient SET unlike=?s WHERE id=?i", json_encode($unlike), $id_radio);
}

function transfer_bonuses($connect){
	$id = $_POST["id"];
	$all_bonus = all_klient_bonus($connect, $id);
	$bonus_to_trans = determine_klient_bonus($connect, $id);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Перевести бонусы туриста</h4>
			</div>
			<div class="modal-body form-horizontal transfer">
				<div class="form-group">
					<label class="col-sm-4 control-label">Всего бонусов</label>
					<div class="col-sm-8">
						<div class="well-sm alert alert-info"><?php echo $all_bonus; ?></div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Можно передать</label>
					<div class="col-sm-8">
						<div class="well-sm alert alert-success bonus-trans"><?php echo $bonus_to_trans; ?></div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Кому</label>
					<div class="col-sm-8">
						<div class="transfet-to-client">
							<input type="text" class="form-control" id="trans_to" onKeyUp="find_klient(event, 'trans_to', 'klient', 'select_klient_id')" />
						</div>
					</div>
				</div>
				<div class="form-group form-group-bottom">
					<label class="col-sm-4 control-label">Кол-во бонусов</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="sum_bonus" onKeyPress="validate_input()" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="transfer_bonuses_to_new_klient('<?php echo $id; ?>')"><i class="fa fa-paper-plane-o"></i> Перевести</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function select_klient_id($connect){
	$id = $_POST["id"];
	return select_name_klient($connect, $id);
}

function transfer_bonuses_to_new_klient($connect){
	global $name_user;
	$old_klient = $_POST["old_klient"];
	$new_klient = $_POST["new_klient"];
	$bonus = $_POST["bonus"];
	$bonus_to_trans = determine_klient_bonus($connect, $old_klient);
	if($bonus <= $bonus_to_trans AND $bonus > 0){
		$today = date("Y-m-d");
		$bonus_minus = -1 * $bonus;
		$manager = " Менеджер: ".$name_user;
		$note = "Перевод на ".select_name_klient($connect, $new_klient).".<br />".$manager;
		$connect->query("INSERT INTO bonus(date, turist, sum, type, note) VALUES (?s, ?i, ?s, 2, ?s)", $today, $old_klient, $bonus_minus, $note);
		$note = "Перевод от ".select_name_klient($connect, $old_klient).".<br />".$manager;
		$connect->query("INSERT INTO bonus(date, turist, sum, type, note) VALUES (?s, ?i, ?s, 2, ?s)", $today, $new_klient, $bonus, $note);
		return 1;
	}
}

function client_payers($connect){
	$id = $_POST["id"];
	$payers = $connect->getOne("SELECT payer FROM klient WHERE id=?i", $id);
	$payers = explode("_", $payers);
	$table = "";
	foreach($payers as $payer){
		$row = $connect->getRow("SELECT * FROM payer WHERE id=?i", $payer);
		if($row["id"]){
			$name = $row["name"];
			$type = $row["type"];
			$date_b = date_change($row["date_b"], ".");
			ob_start();
			if($type == 1){
		?>
			<div class="form-horizontal panel panel-default">
				<div class="panel-body">
					<div class="form-group">
						<label class="col-sm-8 control-label"><span class="label label-warning">Физ.</span> <?php echo $name; ?></label>
						<label class="col-sm-4 control-label"><span class="label label-default pointer" onclick="$('.payer-<?php echo $payer; ?>').toggle()">Информация <i class="fa fa-angle-double-down"></i></span></label>
					</div>
					<div class="form-group payer-<?php echo $payer; ?>" style="display: none">
						<div class="col-sm-12">
							<label class="col-sm-12 control-label-left">Дата рождения: <?php echo $date_b; ?></label>
							<label class="col-sm-12 control-label-left">Паспорт: <?php echo $row["passport"]; ?></label>
						</div>
						<div class="col-sm-offset-8 col-sm-4">
							<button type="button" class="btn btn-default btn-xs" onclick="edit_individual_payer('<?php echo $payer; ?>')"><i class="fa fa-pencil"></i> Редактировать</button>
						</div>
					</div>
				</div>
			</div>
		<?php
			}else{
		?>
			<div class="form-horizontal panel panel-default">
				<div class="panel-body">
					<div class="form-group">
						<label class="col-sm-8 control-label"><span class="label label-primary">Юр.</span> <?php echo $name; ?></label>
						<label class="col-sm-4 control-label"><span class="label label-default pointer" onclick="$('.payer-<?php echo $payer; ?>').toggle()">Информация <i class="fa fa-angle-double-down"></i></span></label>
					</div>
					<div class="form-group payer-<?php echo $payer; ?>" style="display: none">
						<div class="col-sm-12">
							<label class="col-sm-12 control-label-left">Представитель: <?php echo $row['present']; ?> (<?php echo $row['post']; ?>)</label>
							<label class="col-sm-12 control-label-left">Действует на основании: <?php echo $row['doc']; ?></label>
							<label class="col-sm-12 control-label-left">ИНН: <?php echo $row['inn']; ?></label>
							<label class="col-sm-12 control-label-left">БИК: <?php echo $row['bik']; ?></label>
							<label class="col-sm-12 control-label-left">КПП: <?php echo $row['kpp']; ?></label>
							<label class="col-sm-12 control-label-left">Кор.сч.: <?php echo $row['ks']; ?></label>
							<label class="col-sm-12 control-label-left">Рас.сч.: <?php echo $row['rs']; ?></label>
							<label class="col-sm-12 control-label-left">Банк: <?php echo $row['bank']; ?></label>
							<label class="col-sm-12 control-label-left">Адрес: <?php echo $row['address']; ?></label>
							<label class="col-sm-12 control-label-left">Юр.адрес: <?php echo $row['ur_address']; ?></label>
							<label class="col-sm-12 control-label-left">Email: <?php echo $row['email']; ?></label>
						</div>
						<div class="col-sm-offset-8 col-sm-4">
							<button type="button" class="btn btn-default btn-xs" onclick="edit_legal_payer('<?php echo $payer; ?>')"><i class="fa fa-pencil"></i> Редактировать</button>
						</div>
					</div>
				</div>
			</div>
		<?php
			}
			$table.= ob_get_clean();
		}
	}
	if(!$table)
		$table = "<div class='alert alert-danger' role='alert'>Плательщиков не добавлено</div>";
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content modal-new-payer">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Плательщики</h4>
			</div>
			<div class="form-horizontal modal-body">
				<div class="form-group form-group-margin">
					<div class="col-sm-12">
						<?php echo $table; ?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="add_new_payer('<?php echo $id; ?>')"><i class="fa fa-plus-circle"></i> Новый плательщик</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_new_payer_form(){
	$id = $_POST["id"];
	ob_start();
?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
		<h4 class="modal-title">Новый плательщик</h4>
	</div>
	<div class="modal-body center">
		<button type="button" class="btn btn-info btn-sm" onclick="add_new_individual_payer('<?php echo $id; ?>')"><i class="fa fa-user"></i> Физическое лицо</button>
		<button type="button" class="btn btn-info btn-sm" onclick="add_new_legal_payer('<?php echo $id; ?>')"><i class="fa fa-graduation-cap"></i> Юридическое лицо</button>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function add_new_individual_payer($connect){
	$id = $_POST["id"];
	ob_start();
?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
		<h4 class="modal-title">Новый плательщик</h4>
	</div>
	<div class="form-horizontal new-payer modal-body">
		<div class="form-group">
			<label class="col-sm-4 control-label">ФИО</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="name" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Дата рождения</label>
			<div class="col-sm-8">
				<input type="text" class="form-control datepicker" id="date_b" />
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-4 control-label">Паспорт</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="passport" />
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-success btn-sm" onclick="save_new_individual_payer('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_individual_payer($connect){
	$id = $_POST["id"];
	$name = $_POST['name'];
	$date_b = $_POST['date_b'];
	$passport = $_POST["passport"];
	$connect->query("INSERT INTO payer(type, name, date_b, passport) VALUES(1, ?s, ?s, ?s)", $name, $date_b, $passport);
	$last_id = $connect->insertId();
	$payers = $connect->getOne("SELECT payer FROM klient WHERE id=?i", $id);
	$payers = explode("_", $payers);
	$payers = array_diff($payers, array(""));
	$payers[] = $last_id;
	$payer = implode("_", $payers);
	$connect->query("UPDATE klient SET payer=?s WHERE id=?i", $payer, $id);
}

function edit_individual_payer($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, date_b, passport FROM payer WHERE id=?i", $id);
	ob_start();
?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
		<h4 class="modal-title">Изменить плательщика</h4>
	</div>
	<div class="modal-body form-horizontal edit-payer">
		<div class="form-group">
			<label class="col-sm-4 control-label">ФИО</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="name" value="<?php echo $row['name']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Дата рождения</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="date_b" value="<?php echo $row['date_b']; ?>" />
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-4 control-label">Паспорт</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="passport" value="<?php echo $row['passport']; ?>" />
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-success btn-sm" onclick="update_individual_payer('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_individual_payer($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$date_b = $_POST["date_b"];
	if(empty($date_b))
	    $date_b = NULL;
	$passport = $_POST["passport"];
	$connect->query("UPDATE payer SET name=?s, date_b=?s, passport=?s WHERE id=?i", $name, $date_b, $passport, $id);
}

function add_new_legal_payer(){
	$id = $_POST["id"];
	ob_start();
?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
		<h4 class="modal-title">Новый плательщик</h4>
	</div>
	<div class="modal-body form-horizontal new-payer">
		<div class="form-group">
			<label class="col-sm-4 control-label">Полное название</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="name" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Сокр. название</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="short" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Представ. (Род.п.)</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="present" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Представ. (Им.п.)</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="present_im" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Должн. (Род.п.)</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="post" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Должн. (Им.п.)</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="post_im" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Действ. на осн.</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="doc" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">ИНН</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="inn" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">КПП</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="kpp" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">БИК</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="bik" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Расч.счет</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="rs" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Кор.счет</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="ks" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Банк</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="bank" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Юрид.адрес</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="ur_address" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Почт.адрес</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="address" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Email</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="email" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">БИН</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="bin" />
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-4 control-label">ИИК</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="iik" />
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-success btn-sm" onclick="save_new_legal_payer('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_legal_payer($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$short = $_POST['short'];
	$present = $_POST["present"];
	$post = $_POST["post"];
	$doc = $_POST["doc"];
	$post_im = $_POST["post_im"];
	$present_im = $_POST["present_im"];
	$inn = $_POST["inn"];
	$kpp = $_POST["kpp"];
	$bik = $_POST["bik"];
	$ks = $_POST["ks"];
	$bank = $_POST["bank"];
	$rs = $_POST["rs"];
	$address = $_POST["address"];
	$ur_address = $_POST["ur_address"];
	$email = $_POST["email"];
	$bin = $_POST["bin"];
	$iik = $_POST["iik"];
	$connect->query("INSERT INTO payer(type, name, short, present, post, doc, post_im, present_im, inn, kpp, bik, ks, rs, address, ur_address, bank, email, bin, iik) VALUES(2, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)", $name, $short, $present, $post, $doc, $post_im, $present_im, $inn, $kpp, $bik, $ks, $rs, $address, $ur_address, $bank, $email, $bin, $iik);
	$last_id = $connect->insertId();
	$payers = $connect->getOne("SELECT payer FROM klient WHERE id=?i", $id);
	$payers = explode("_", $payers);
	$payers = array_diff($payers, array(""));
	$payers[] = $last_id;
	$payer = implode("_", $payers);
	$connect->query("UPDATE klient SET payer=?s WHERE id=?i", $payer, $id);
}

function edit_legal_payer($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT * FROM payer WHERE id=?i", $id);
	$row = clear_quotes($row);
	ob_start();
?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
		<h4 class="modal-title">Изменить плательщика</h4>
	</div>
	<div class="modal-body form-horizontal edit-payer">
		<div class="form-group">
			<label class="col-sm-4 control-label">Полное название</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="name" value="<?php echo $row['name']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Сокр. название</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="short" value="<?php echo $row['short']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Представ. (Род.п.)</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="present" value="<?php echo $row['present']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Представ. (Им.п.)</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="present_im" value="<?php echo $row['present_im']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Должн. (Род.п.)</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="post" value="<?php echo $row['post']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Должн. (Им.п.)</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="post_im" value="<?php echo $row['post_im']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Действ. на основ.</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="doc" value="<?php echo $row['doc']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">ИНН</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="inn" value="<?php echo $row['inn']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">КПП</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="kpp" value="<?php echo $row['kpp']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">БИК</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="bik" value="<?php echo $row['bik']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Расч.счет</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="rs" value="<?php echo $row['rs']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Кор.счет</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="ks" value="<?php echo $row['ks']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Банк</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="bank" value="<?php echo $row['bank']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Юрид.адрес</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="ur_address" value="<?php echo $row['ur_address']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Почт.адрес</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="address" value="<?php echo $row['address']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Email</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="email" value="<?php echo $row['email']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">БИН</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="bin" value="<?php echo $row['bin']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">ИИК</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="iik" value="<?php echo $row['iik']; ?>" />
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-4 control-label">Код 1С</label>
			<div class="col-sm-8">
				<input type="text" class="form-control input-sm" id="1C_code" value="<?php echo $row['1C_code']; ?>" />
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-success btn-sm" onclick="update_legal_payer('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_legal_payer($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$short = $_POST['short'];
	$present = $_POST["present"];
	$post = $_POST["post"];
	$doc = $_POST["doc"];
	$post_im = $_POST["post_im"];
	$present_im = $_POST["present_im"];
	$inn = $_POST["inn"];
	$kpp = $_POST["kpp"];
	$bik = $_POST["bik"];
	$ks = $_POST["ks"];
	$bank = $_POST["bank"];
	$rs = $_POST["rs"];
	$address = $_POST["address"];
	$ur_address = $_POST["ur_address"];
	$email = $_POST["email"];
	$bin = $_POST["bin"];
	$iik = $_POST["iik"];
	$code = $_POST["code"];
	$connect->query("UPDATE payer SET name=?s, short=?s, present=?s, post=?s, doc=?s, post_im=?s, present_im=?s, inn=?s, kpp=?s, bik=?s, ks=?s, rs=?s, address=?s, ur_address=?s, bank=?s, email=?s, iik=?s, bin=?s, 1C_code=?s WHERE id=?i", $name, $short, $present, $post, $doc, $post_im, $present_im, $inn, $kpp, $bik, $ks, $rs, $address, $ur_address, $bank, $email, $iik, $bin, $code, $id);
}

function clear_login_client($connect){
	$id = $_POST["id"];
	if(!$connect->getOne("SELECT id FROM klient WHERE id=?i AND login!=''", $id))
		return FALSE;
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Удалить личный кабинет туриста</h4>
			</div>
			<div class="modal-footer text-center">
				<button type="button" class="btn btn-danger btn-sm" onclick="confirm_clear_login_client('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Подтвердить удаление</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function confirm_clear_login_client($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE klient SET login='', password='' WHERE id=?i", $id);
}

function delete_client_from_system($connect){
	$id = $_POST["id"];
	if(!$connect->getOne("SELECT id FROM reckoning WHERE turist=?i OR rest LIKE ?s", $id, "%".$id."%")){
		$connect->query("DELETE from klient WHERE id=?i", $id);
		change_auto_increment($connect, "klient");
		return 1;
	}
}

?>
