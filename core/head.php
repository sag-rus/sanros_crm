<?php

function show_my_bid_menu($connect){
	global $id_rights, $session_login;
	if(!$id_rights)
		return show_warning_session_expired();
?>
	<ul class="nav nav-tabs my-bid-page">
<?php
	$cancel = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE (status=10 OR status=11) AND (active=0 OR reckoning.active=2)");
	$new = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE (id_user='' OR id_user IS NULL OR id_user=0) AND reckoning.active=0");
	$deferred = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE id_user=?i AND reckoning.status=9", $session_login);
?>
	<?php if($new > 0){ ?>
		<li class="new-bid-page" onclick="get_my_reckoning('new')"><a>Новые</a></li>
	<?php } ?>
	<?php if($cancel > 0){ ?>
		<li class="cancel-bid-page" onclick="get_my_reckoning('cancel')"><a>Запросы аннуляции</a></li>
	<?php } ?>
<?php
	$data = $connect->getAll("SELECT id, name_menu FROM status");
	foreach($data as $row){
		$id = $row["id"];
		$name_menu = $row["name_menu"];
		if($id == 3){
			$bgcolor = "#FF0000";
			$color = "#FFF";
			$query = "status=7 AND (id_obj!=95 AND id_obj!=133)";
			$query2 = "status=7 AND (id_obj!=95 AND id_obj!=133)";
		}elseif($id == 6){
			$bgcolor = "#FF0000";
			$color = "#FFF";
			$query = "status=8";
			$query2 = "status=8";
		}elseif($id == 4){
			$bgcolor = "#FF0000";
			$color = "#FFF";
			$query2 = "status=4";
		}elseif($id == 5){
			$bgcolor = "#FF0000";
			$color = "#FFF";
			$query2 = "status=5 AND (status_san!=1 AND status_san!=4)";
		}else{
			$bgcolor = "#FF0";
			$color = "#000";
			$query = "status=$id";
			$query2 = "status=$id";
		}
		$query = " WHERE $query AND active!=3";
		$query2 = " WHERE $query2 AND active!=3";
		if((int)$id_rights <= 3){
			$query.= " AND id_user=$session_login ";
			$query2.= " AND id_user=$session_login ";
		}
		$count = $connect->getOne("SELECT COUNT(*) FROM reckoning $query");
		if(!$count){
			$bgcolor = "#FF0";
			$color = "#000";
			$count = $connect->getOne("SELECT COUNT(*) FROM reckoning $query2");
		}
		if($count == 0)
			$count = "";
		if($id <= 6){
		?>
			<li class="<?php echo $id; ?>-bid-page" onclick="get_my_reckoning(<?php echo $id; ?>)">
				<a><?php echo $name_menu; ?><span class="badge"><?php echo $count; ?></span></a>
			</li>
		<?php
		}
	}
?>
		<li class="special-bid-page" onclick="get_my_reckoning('special')"><a>Свияжск</a></li>
	<?php if($deferred > 0){ ?>
		<li class="9-bid-page" onclick="get_my_reckoning(9)"><a>Отложенные</a></li>
	<?php } ?>
	<?php if($id_rights <= 3){ ?>
		<li class="return-bid-page" onclick="return_query_report_manager()"><a><i class="fa fa-angle-double-left"></i> Возвраты</a></li>
	<?php } ?>
	</ul>
	<div class="my-bid-block"></div>
<?php
}

function get_my_reckoning($connect){
	global $id_rights, $session_login;
	$page = $_POST["page"];
	$arr_menu = array();
	$html = "";
	$menu = "";
	$last_manager = "";
	if($page == "")
		$page = 2;
	$div_hide = "";
	if($page == 3)
		$str = " AND (status=3 OR (status=7 AND (id_obj!=95 AND id_obj!=133))) ";
	elseif($page == 6)
		$str = " AND status=8";
	else
		$str = " AND status = $page ";
	if($page == 5)
		$str.= " AND (status_san!=1 AND status_san!=4) ";
	if((int)$id_rights <= 3)
		$str.= " AND id_user=$session_login ";
	if($page == "cancel"){
		$str = " AND (status=10 OR status=11)";
	}
	if($page == "deferred")
		$str = " AND active=1 AND id_user=$session_login";
	if($page == "new")
		$str = " AND (id_user='' OR id_user IS NULL OR id_user=0)";
	if($page == "special"){
		$str = " AND ((id_obj=95 OR id_obj=133) AND status=7)";
		if((int)$id_rights <= 3)
			$str.= " AND id_user=$session_login ";
	}
	$query = "SELECT id, status, status_san, turist, agency, DATE_FORMAT(date, '%d.%m.%Y') as date, DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, id_obj, id_user, sum, active FROM reckoning WHERE (active=0 OR active=2 OR active=1) $str";
	$data = $connect->getAll($query);
	foreach($data as $row){
		$row["manager"] = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
		$row["date_z"] = $row["date_z"];
		if($row["status"] == 2)
			$date = $connect->getOne("SELECT date FROM history_schet WHERE id_schet=?i AND new_status=?i", $row["id"], $row["status"]);
		else
			$date = $row["date"];
		$row["date"] = $row["date"];
		$row["object"] = get_object($connect, $row["id_obj"]);
		if($row["agency"])
			$row["fio"] = $connect->getOne("SELECT name FROM agency WHERE id=?i", $row["agency"]);
		else
			$row["fio"] = select_name_klient($connect, $row["turist"]);
		$class = "";
		if(strToTime($row["date_z"]) <= time() AND ($row["status_san"] == 4 OR $row["status_san"] == 5) AND $row["status"] < 5)
			$class = " class='alert-danger' ";
		if($page == "new"){
			$check = check_new_reckoning_office($connect, $row["id"]);
			if($check == 0)
				$class = " class='alert-danger' ";
			elseif($check == 1 OR !$check)
				$class = " class='alert-default' ";
			else
				$class = " class='alert-success' ";
		}
		if($page != "new" AND $row["active"] == 1)
			$class = " class='alert-danger' ";
		$row["date_opl"] = "";
		if($row["status"] == 3)
			$row["date_opl"] = $connect->getOne("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date_opl FROM time_payment WHERE id_schet=?i ORDER BY date ASC", $row["id"]);
		elseif($row["status"] == 4)
			$row["date_opl"] = $connect->getOne("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date_opl FROM time_payment WHERE id_schet=?i AND type=1 ORDER BY date DESC", $row["id"]);
		$html.= "<tr ".$class.">".get_stroka_HTML($connect, $row, $page)."</tr>";
	}
	$arr_menu[$page] = "active";

	$f = "get_my_reckoning(\"".$page."\")";
	$menu.= "<li onclick='".$f."'><a><i class='fa fa-repeat'></i></a></li>";
	$date_s = "";
	if($page > 2)
		$date_s = "<th width='9%' class='{dateFormat: \"ddmmyyyy\"}'>Оплата</th>";
	if($html)
		$html = "<thead><tr class='tr_head'><th width='5%'>№</th><th style='text-align: left;' width='25%'>Клиент</th><th width='20%'>Объект</th><th width='9%' class='{dateFormat: \"ddmmyyyy\"}'>Заезд</th><th width='9%' class='{dateFormat: \"ddmmyyyy\"}'>Заявка</th>".$date_s."<th width='7%'>Сан</th><th width='9%'>Сумма</th><th width='4%'>Менеджер</th><th width='5%'></th></tr></thead><tbody>".$html."</tbody>";
	else
		$html = "<tr style='background: white;'><td><div class='alert alert-info'>Заявок нет</div></td></tr>";
?>
	<p class="text-left">
	<?php	if($page == "new"){ ?>
		<button class="btn btn-default" onclick="select_last_manager_assign()" id="last-manager">Последние заявки <i class="fa fa-angle-double-down"></i></button>
	<?php	} ?>
	</p>
	<table class="table table-hover table-condensed my-bid-table">
		<?php echo $html; ?>
	</table>
<?php
}

function get_stroka_HTML($connect, $row, $page){
	$date_schet = "";
	if($page == 3){
		$date_schet = "<td class='td_date'>".$row["date_opl"]."</td>";
		if($row["status"] == 7)
			$row["fio"] = "<span style='color: #FF3434;'>[Запрос оплаты]</span><br />".$row["fio"];
	}elseif($page == 6){
		$date_schet = "<td class='td_date'></td>";
		if($row["status"] == 8)
			$row["fio"] = "<span style='color: #FF3434;'>[Запрос аннуляции]</span><br />".$row["fio"];
	}elseif($page == 5){
		$date_schet = "<td class='td_date'>".$row["date_opl"]."</td>";
	}elseif($page == 4){
		$date_schet = "<td class='td_date'>".$row["date_opl"]."</td>";
	}elseif($page == "new")
		$row["fio"].= "<br />".$connect->getOne("SELECT time FROM history_schet WHERE id_schet=?i ORDER BY id LIMIT 1", $row["id"]);
	$status_san = $row["status_san"];
	if($status_san == 1)
		$status_san = "Да";
	elseif($status_san == 3)
		$status_san = "Пред.";
	elseif($status_san == 4){
		$status_san = "Онм";
		$row["fio"] = $row["fio"]."<br /><span style='color: #FF3434;'>[Оплата на месте]</span>";
	}elseif($status_san == 5){
		$status_san = "Понм";
		$row["fio"] = $row["fio"]."<br /><span style='color: #FF3434;'>[Предоплата на месте]</span>";
	}elseif($status_san == 2){
		$row["fio"] = $row["fio"]."<br /><span style='color: #FF3434;'>[Разрешена оплата в санаторий]</span>";
		$row["status_san"] = "<span style='color: red'>Ож.О.</span>";
	}elseif($status_san == 6){
		$row["fio"] = $row["fio"]."<br /><span style='color: #FF3434;'>[Разрешена предоплата в санаторий]</span>";
		$status_san = "<span style='color: red'>Ож.П.</span>";
	}else
		$status_san = "<span style='color: red'>Нет</span>";
	if($connect->getOne("SELECT guaranteed FROM reckoning WHERE id=", $row["id"]))
		$row["fio"].= " <i class='fa fa-star icon_star'></i>";
	if($row["agency"]){
		$agency = "agency";
		$row["turist"] = $row["agency"];
	}else
		$agency = "";
	$html = "<td>".$row["id"]."</td><td style='text-align: left;'><span style='cursor: pointer;' onclick='show_turist(\"".$row["turist"]."\", \"".$row["id"]."\", \"".$agency."\")'>".$row["fio"]."</td> <td>".$row["object"]."</td><td>".$row["date_z"]."</td><td class='td_date'>".$row["date"]."</td>".$date_schet."<td>".$status_san."</td><td>".$row["sum"]."</td><td>".$row["manager"]."</td><td style='background: #FFF !important;'></td>";
	return $html;
}

function select_last_manager_assign($connect){
	$html = "";
	$data = $connect->getAll("SELECT id_user, id_schet FROM history_schet WHERE note='Присвоена менеджеру' ORDER BY id DESC LIMIT 10");
	foreach($data as $row){
		$id = $row["id_schet"];
		$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
		$html.= "<span>Менеджер ".$manager." №".$id."</span>";
	}
	return $html;
}

function last_manager_assign_call_back($connect){
	$html = "";
	$data = $connect->getAll("SELECT id_user, website FROM order_call_back WHERE id_user!='' ORDER BY id DESC LIMIT 10");
	foreach($data as $row){
		$manager = $connect->getRow("SELECT name, office FROM users WHERE id=?i", $row["id_user"]);
		$office = $connect->getOne("SELECT name FROM office WHERE id=?i", $manager["office"]);
		ob_start();
	?>
		<span><?php echo $manager["name"]; ?> (<?php echo $office; ?>)</span>
	<?php
		$html.= ob_get_clean();
	}
	return $html;
}

function show_call_back($connect){
	global $session_login;
	$type = $_POST["type"];
	$today = date("Y-m-d");
	if($type == "new")
		$zapros = " active=1 AND (id_user='' OR id_user IS NULL OR id_user=0)";
	elseif($type == "work")
		$zapros = " active=1 AND id_user=".$session_login;
	elseif($type == "process")
		$zapros = " active=2 AND id_user=".$session_login." AND (DATE_ADD(DATE(time), INTERVAL (5) DAY)) > '".$today."'";
	elseif($type == "archive")
		$zapros = " active=3 AND id_user=".$session_login." AND (DATE_ADD(DATE(time), INTERVAL (5) DAY)) > '".$today."'";
?>
	<div class="form-horizontal">
		<?php if($type == "new"){ ?>
		<p class="text-left">
			<button class="btn btn-default" onclick="last_manager_assign_call_back()" id="last-manager">Последние заявки <i class="fa fa-angle-double-down"></i></button>
		</p>
		<?php } ?>
<?php
	$data = $connect->getAll("SELECT id, active, id_user, website, turist, telephone, question, address, type, DATE_FORMAT(time, '%H:%i:%s %d.%m.%Y') as date, promo, note, id_bid, source, href FROM order_call_back WHERE ".$zapros);
	foreach($data as $row){
		$id = $row["id"];
		$active = $row["active"];
		$promo = "";
		$class_note = " hidden ";
		if($row["note"])
			$class_note = "";
		if($row["promo"])
			$promo = $connect->getRow("SELECT id, title, text, id_obj FROM promotions WHERE id=?i", $row["promo"]);
		$class = "panel-default";
		if($type == "new"){
			$check = check_new_order_call_back_office($connect, $id);
			if($check == 0)
				$class = " panel-danger ";
			elseif($check == 2)
				$class = " panel-success ";
		}
		$icon = select_source_icon($row["source"]);
?>
	<div class="panel <?php echo $class; ?> order-call-back-<?php echo $id; ?>">
		<div class="panel-heading">
			<?php echo $row["website"]; ?> (<?php echo $row["date"]; ?>)
			<span class="pull-right">
				<?php echo $icon; ?>
			</span>
		</div>
		<div class="list-group">
			<?php if($row["href"]){ ?>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">
						Страница сайта
					</label>
					<div class="col-sm-8 note-text">
						<a class="btn btn-link btn-xs" href="http://<?php echo $row['website'].$row['href']; ?>" target="_blank"><?php echo $row["href"]; ?></a>
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">
						Турист
					</label>
					<div class="col-sm-8">
						<?php echo $row["turist"]; ?> (<?php echo $row["address"]; ?>)
					</div>
				</div>
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">
						Телефон
					</label>
					<div class="col-sm-8">
						<?php echo $row["telephone"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">
						Вопрос
					</label>
					<div class="col-sm-8">
						<?php echo $row["question"]; ?>
					</div>
				</div>
			</div>
			<?php if($row["promo"] AND $promo["id"]){ ?>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">
						Вопрос по акции (<?php echo get_object($connect, $promo["id_obj"], "type"); ?>)
					</label>
					<div class="col-sm-8">
						<?php echo $promo["title"]." - ".$promo["text"]; ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<?php if($row["id_bid"]){ ?>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">
						Номер заявки
					</label>
					<div class="col-sm-8 note-text">
						<?php echo $row["id_bid"]; ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="list-group-item list-hover-item note-call-back-block <?php echo $class_note; ?>">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">
						Примечание
					</label>
					<div class="col-sm-8 note-text">
						<?php echo $row["note"]; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer text-right">
		<?php if(!$row["id_user"]){ ?>
			<button class="btn btn-success btn-sm" onclick="change_status_order_call_back('<?php echo $id; ?>', 1)"><i class="fa fa-check-circle"></i> Забрать заказ</button>
		<?php } ?>
		<?php if($active != 3){ ?>
			<button class="btn btn-default btn-sm" onclick="edit_note_order_call_back('<?php echo $id; ?>')"><i class="fa fa-pencil"></i> Изменить примечание</button>
		<?php } ?>
		<?php if($active == 1 AND $row["id_user"]){ ?>
			<button class="btn btn-success btn-sm" onclick="show_form_create_order_call_back('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Создать заявку</button>
		<?php } ?>
		<?php if($active == 1){ ?>
			<button class="btn btn-danger btn-sm" onclick="change_status_order_call_back('<?php echo $id; ?>', 3)"><i class="fa fa-trash"></i> Заказ в архив</button>
		<?php }elseif($active == 3){ ?>
			<button class="btn btn-success btn-sm" onclick="change_status_order_call_back('<?php echo $id; ?>', 1)"><i class="fa fa-angle-double-up"></i> Восстановить заказ</button>
		<?php } ?>
		</div>
	</div>
<?php
	}
	if(!$data){
?>
	<div class="list-group list-group-margin">
		<div class="list-group-item list-group-item-warning">
			<i class="fa fa-exclamation-triangle"></i> Заказов звонка не найдено
		</div>
	</div>
<?php
	}
?>
	</div>
<?php
}

function change_status_order_call_back($connect){
	global $session_login;
	$id = $_POST["id"];
	$status = $_POST["status"];
	$connect->query("UPDATE order_call_back SET active=?i WHERE id=?i", $status, $id);
	if($connect->getOne("SELECT id FROM order_call_back WHERE id=?i AND (id_user='' OR id_user IS NULL OR id_user=0)", $id))
		$connect->query("UPDATE order_call_back SET id_user=?i WHERE id=?i", $session_login, $id);
}

function edit_note_order_call_back($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id, note FROM order_call_back WHERE id=?i", $id);
	if(!$row["id"])
		return FALSE;
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить заявку</h4>
			</div>
			<div class="modal-body form-horizontal edit-call-back">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Примечание</label>
					<div class="col-sm-8">
						<textarea class="form-control note-call-back"><?php echo $row["note"]; ?></textarea>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_note_order_call_back('<?php echo $id; ?>')"><i class="fa fa-check"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_note_order_call_back($connect){
	$id = $_POST["id"];
	$note = $_POST["note"];
	$connect->query("UPDATE order_call_back SET note=?s WHERE id=?i", $note, $id);
}

function show_form_create_order_call_back($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id, promo, website, turist FROM order_call_back WHERE id=?i AND id_bid is NULL", $id);
	if(!$row["id"])
		return FALSE;
	$promo = $row["promo"];
	$turist = explode(" ", $row["turist"]);
	if(!isset($turist[1])){
		$turist[1] = $turist[0];
		$turist[0] = "";
	}
	if(!isset($turist[2]))
		$turist[2] = "";
	$object = "";
	$id_obj = $connect->getOne("SELECT id_obj FROM st_website WHERE url=?s", $row["website"]);
	if($id_obj)
		$object = get_object($connect, $id_obj, "type");
	elseif($promo){
		$id_obj = $connect->getOne("SELECT id_obj FROM promotions WHERE id=?i", $promo);
		$object = get_object($connect, $id_obj);
	}
?>
<div class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Создание новой заявки</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal create-order-call-back">
					<div class="form-group">
						<label class="col-sm-3 control-label">Объект</label>
						<div class="col-sm-9">
						<?php if($object){ ?>
							<div class="label-text id-object" name="<?php echo $id_obj; ?>">
								<?php echo $object; ?>
							</div>
						<?php }else{ ?>
							<div id="object_name" name="new-reck">
								<input type="text" class="form-control" id="object" onkeyup="find_klient(event, 'object', 'object', 'use_object')" />
							</div>
						<?php } ?>
						</div>
					</div>
					<div class="form-group"><hr /></div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Турист</label>
						<div class="col-sm-3">
							<input type="text" class="form-control surname" placeholder="Фамилия" value="<?php echo $turist[0]; ?>" />
						</div>
						<div class="col-sm-3">
							<input type="text" class="form-control name" placeholder="Имя" value="<?php echo $turist[1]; ?>" />
						</div>
						<div class="col-sm-3">
							<input type="text" class="form-control otch" placeholder="Отчество" value="<?php echo $turist[2]; ?>" />
						</div>
					</div>
					<div class="form-group"><hr /></div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Номер</label>
						<div class="col-sm-3" id="klient_room">
							<div class="well well-sm">&nbsp;</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Данные о заезде</label>
						<div class="col-sm-3">
							<input type="text" class="form-control datepicker" id="date-z" placeholder="Заезд" />
						</div>
						<div class="col-sm-3">
							<input type="text" class="form-control days" placeholder="Дней" />
						</div>
						<div class="col-sm-3">
						</div>
					</div>
					<div class="form-group form-group-margin">
						<div class="col-sm-3"></div>
						<div class="col-sm-3">
							<input type="text" class="form-control price" placeholder="Цена" />
						</div>
						<div class="col-sm-3">
							<select class="form-control type">
								<option value="1">за чел/сутки</option>
								<option value="2">за номер (дом)</option>
								<option value="3">за заезд</option>
							</select>
						</div>
						<div class="col-sm-3">
							<input type="text" class="form-control number" placeholder="Кол-во" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Сохранение..." onclick="create_order_call_back('<?php echo $id; ?>')" class="btn btn-success btn-update"><i class="fa fa-check-circle"></i> Создать заявку</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function create_order_call_back($connect){
	date_default_timezone_set("UTC");
	global $session_login;
	$id = $_POST["id"];
	$surname = $_POST["surname"];
	$name = $_POST["name"];
	$otch = $_POST["otch"];

	$room = $_POST["room"];
	$object = $connect->getOne("SELECT id_obj FROM room WHERE id=?i", $room);
	$reward = $connect->getOne("SELECT reward FROM object WHERE id=?i", $object);
	$price = $_POST["price"];
	$number = $_POST["number"];
	$days = $_POST["days"];
	$date = strToTime($_POST["date"]);
	$type = $_POST["type"];
	if(is_numeric($price) AND is_numeric($number)){
		$date = date("Y-m-d", $date);
		$today = date("Y-m-d");
		$row = $connect->getRow("SELECT telephone, address, website, question, source FROM order_call_back WHERE id=?i", $id);
		$address = $row["address"];
		$telephone = $row["telephone"];
		$website = $row["website"];
		$question = $row["question"];
		$source = $row["source"];
		$connect->query("INSERT INTO klient(surname, name, otch, telephone, address) VALUES (?s, ?s, ?s, ?s, ?s)", $surname, $name, $otch, $telephone, $address);
		$client = $connect->insertId();
		$connect->query("INSERT INTO reckoning(date, turist, id_user, id_obj, rest, website, note, source, form_booking) VALUES (?s, ?i, ?i, ?i, ?i, ?s, ?s, ?i, 'order-call-back')", $today, $client, $session_login, $object, $client, $website, $question, $source);
		$bid = $connect->insertId();
		setCookie("reck", $bid);
		$connect->query("INSERT INTO position_reck(id_room, sum, number, schet, type, days, date_z, reward, add_one_day) VALUES (?i, ?i, ?i, ?i, ?i, ?i, ?s, ?s, 0)", $room, $price, $number, $bid, $type, $days, $date, $reward);
		recalculation_sum($connect, $bid);
		save_schet_to_history($connect, $bid, "Новая заявка из заказа звонка");
		change_arrival_date($connect, $bid);

		$connect->query("UPDATE order_call_back SET active=2, id_bid=?i WHERE id=?i", $bid, $id);
		return $client;
	}
	return FALSE;
}

function select_return_query_manager($connect){
	global $session_login;
	$data = $connect->getAll("SELECT id_reck, DATE_FORMAT(return_query.date, '%d.%m.%Y') as date_stat, return_query.sum, return_query.type_pay, return_query.check_pay, turist, agency FROM return_query, reckoning WHERE return_query.active=1 AND reckoning.id=return_query.id_reck AND reckoning.id_user=?i ORDER BY return_query.date", $session_login);
	if(!$data){ ?>
		<div class="alert alert-info">Ничего не найдено</div>
	<?php }else{ ?>
		<table class="table table-hover table-condensed">
		<tr>
			<th>Заявка</th>
			<th>Клиент</th>
			<th>Заявление</th>
			<th>Сумма</th>
			<th>Способ оплаты</th>
			<th></th>
		</tr>
	<?php
		foreach($data as $row){
			if($row["agency"]){
				$param = "agency";
				$type = $row["agency"];
				$client = $connect->getOne("SELECT name FROM agency WHERE id=?i", $row["agency"]);
			}else{
				$param = "turist";
				$type = $row["turist"];
				$client = select_name_klient($connect, $row["turist"]);
			}
			if($row["type_pay"] == 1)
				$type_pay = "безналичный";
			else
				$type_pay = "наличными";
?>
		<tr onclick="show_turist('<?php echo $type; ?>', '<?php echo $row['id_reck']; ?>', '<?php echo $param; ?>')">
			<td width="15%"><?php echo $row["id_reck"]; ?></td>
			<td width="40%"><?php echo $client; ?></td>
			<td width="15%"><?php echo $row["date_stat"]; ?></td>
			<td width="15%"><?php echo $row["sum"]; ?></td>
			<td width="15%"><?php echo $type_pay; ?></td>
		</tr>
<?php
		}
?>
	</table>
<?php
	}
}

function check_new_reckoning_office($connect, $id){
	global $session_login;
	$row = $connect->getRow("SELECT id_obj, turist, agency FROM reckoning WHERE id=?i", $id);
	$answer = 0;
	$office = $connect->getOne("SELECT office FROM users WHERE id=?i", $session_login);
	$region = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $row["id_obj"]);
	$addr_kazan = $connect->getOne("SELECT id FROM klient WHERE id=?i AND (address LIKE ?s OR address LIKE ?s)", $row["turist"], "%Татарстан%", "%Казань%");
	$addr_ulan = $connect->getOne("SELECT id FROM klient WHERE id=?i AND (address LIKE ?s OR address LIKE ?s OR address LIKE ?s)", $row["turist"], "%Ульяновск%", "%Пенз%", "%Морд%");
	$addr_samar = $connect->getOne("SELECT id FROM klient WHERE id=?i AND (address LIKE ?s OR address LIKE ?s)", $row["turist"], "%Самар%", "%Сарат%");
	$addr_ufa = $connect->getOne("SELECT id FROM klient WHERE id=?i AND (address LIKE ?s OR address LIKE ?s OR address LIKE ?s OR address LIKE ?s)", $row["turist"], "%Башкорт%", "%Уфа%", "%Сверд%", "%Перм%");
	if($row["agency"]){
		if($connect->getOne("SELECT id FROM agency WHERE (address LIKE '%Казань%' OR address LIKE '%Татарстан%') AND id=?i", $row["agency"]) AND $office == 1)
			return 2;
		elseif($connect->getOne("SELECT id FROM agency WHERE (address LIKE '%Казань%' OR address LIKE '%Татарстан%') AND id=?i", $row["agency"]))
			return 0;

		if($connect->getOne("SELECT id FROM agency WHERE (address LIKE '%Ульян%') AND id=?i", $row["agency"]) AND $office == 2)
			return 2;
		elseif($connect->getOne("SELECT id FROM agency WHERE (address LIKE '%Ульян%') AND id=?i", $row["agency"]))
			return 0;

		if($connect->getOne("SELECT id FROM agency WHERE (address LIKE '%Самар%') AND id=?i", $row["agency"]) AND $office == 3)
			return 2;
		elseif($connect->getOne("SELECT id FROM agency WHERE (address LIKE '%Самар%') AND id=?i", $row["agency"]))
			return 0;
	}
	if($office == 2){
		if($region == 6 OR $region == 44 OR $region == 10)
			$answer ++;
		if($addr_ulan)
			$answer ++;
		if(!$addr_ulan AND !$addr_kazan AND !$addr_samar AND !$addr_ufa AND ($region == 6 OR $region == 44 OR $region == 10))
			$answer ++;
	}elseif($office == 3){
		if($region == 3 OR $region == 58)
			$answer ++;
		if($addr_samar)
			$answer ++;
		if(!$addr_ulan AND !$addr_kazan AND !$addr_samar AND !$addr_ufa AND ($region == 3 OR $region == 58))
			$answer ++;
	}elseif($office == 4){
		if($region == 2 OR $region == 13 OR $region == 14 OR $region == 7)
			$answer ++;
		if($addr_ufa)
			$answer ++;
		if(!$addr_ulan AND !$addr_kazan AND !$addr_samar AND !$addr_ufa AND ($region == 2 OR $region == 13 OR $region == 14 OR $region == 7))
			$answer ++;
	}else{
		if(!$addr_samar AND !$addr_ulan AND !$addr_ufa AND $region != 3 AND $region != 2 AND $region != 6 AND $region != 13 AND $region != 14 AND $region != 58 AND $region != 44 AND $region != 7 AND $region != 10)
			$answer = 2;
		if($region == 1)
			$answer++;
		if($addr_kazan)
			$answer++;
	}
	if($answer > 2)
		$answer = 2;
	return $answer;
}

function check_new_order_call_back_office($connect, $id){
	global $session_login;
	$answer = 0;
	$website = $connect->getOne("SELECT website FROM order_call_back WHERE id=?i AND (id_user='' OR id_user IS NULL OR id_user=0)", $id);
	$row = $connect->getRow("SELECT id_obj, id_reg FROM st_website WHERE url=?s", $website);
	if($row["id_obj"])
		$region = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $row["id_obj"]);
	else
		$region = $row["id_reg"];
	$office = $connect->getOne("SELECT office FROM users WHERE id=?i", $session_login);
	$addr_kazan = $connect->getOne("SELECT id FROM order_call_back WHERE id=?i AND (address LIKE ?s OR address LIKE ?s)", $id, "%Татарстан%", "%Казань%");
	$addr_ulan = $connect->getOne("SELECT id FROM order_call_back WHERE id=?i AND address LIKE ?s", $id, "%Ульяновск%");
	$addr_samar = $connect->getOne("SELECT id FROM order_call_back WHERE id=?i AND address LIKE ?s", $id, "%Самар%");
	$addr_ufa = $connect->getOne("SELECT id FROM order_call_back WHERE id=?i AND (address LIKE ?s OR address LIKE ?s OR address LIKE ?s)", $id, "%Башкорт%", "%Уфа%", "%Перм%");
	if($office == 2){
		if($region == 6)
			$answer ++;
		if($addr_ulan)
			$answer ++;
		if(!$addr_ulan AND !$addr_kazan AND !$addr_samar AND !$addr_ufa AND $region == 6)
			$answer ++;
	}elseif($office == 3){
		if($region == 3)
			$answer ++;
		if($addr_samar)
			$answer ++;
		if(!$addr_ulan AND !$addr_kazan AND !$addr_samar AND !$addr_ufa AND $region == 3)
			$answer ++;
	}elseif($office == 4){
		if($region == 2)
			$answer ++;
		if($addr_ufa)
			$answer ++;
		if(!$addr_ulan AND !$addr_kazan AND !$addr_samar AND !$addr_ufa AND ($region == 2 OR $region == 13 OR $region == 14 OR $region == 7))
			$answer ++;
	}else{
		if(!$addr_samar AND !$addr_ulan AND !$addr_ufa AND $region != 3 AND $region != 6 AND $region != 2 AND $region != 6 AND $region != 13 AND $region != 14 AND $region != 7)
			$answer = 2;
		if($region == 1)
			$answer++;
		if($addr_kazan)
			$answer++;
	}
	if($answer > 2)
		$answer = 2;
	return $answer;
}

function return_query_report_manager($connect){
	global $session_login;
	$data = $connect->getAll("SELECT id, id_reck, date_create, DATE_FORMAT(date, '%d.%m.%Y') as date_stat, sum, type_pay, check_pay FROM return_query WHERE active=1 ORDER BY date");
	ob_start();
?>
	<?php if(!$data){ ?>
		<div class="alert alert-info">Ничего не найдено</div>
	<?php }else{ ?>
		<table class="table table-hover table-condensed">
		<thead>
		<tr>
			<th>Заявка</th>
			<th>Клиент</th>
			<th>Заявление</th>
			<th>Сумма</th>
			<th>Способ оплаты</th>
		</tr>
		</thead>
		<tbody>
<?php
	$itog = 0;
	foreach($data as $row){
		$id = $row["id"];
		$reck = $row["id_reck"];
		$user = $connect->getOne("SELECT id_user FROM reckoning WHERE id=?i", $reck);
		if($user == $session_login){
			$sum = $row["sum"];
			$date = $row["date_stat"];
			$check = "";
			$class = "";
			if($row["check_pay"] == 1){
				$check = " checked ";
				$class = " class='success' ";
				$itog+= $sum;
			}
			if($row["type_pay"] == 1)
				$type_pay = "безналичный";
			else
				$type_pay = "наличными";
			$row = $connect->getRow("SELECT turist, agency FROM reckoning WHERE id=?i", $reck);
			if($row["agency"]){
				$param = "agency";
				$type = $row["agency"];
				$klient = $connect->getOne("SELECT name FROM agency WHERE id=?i", $row["agency"]);
			}else{
				$param = "turist";
				$type = $row["turist"];
				$klient = select_name_klient($connect, $row["turist"]);
			}
?>
		<tr <?php echo $class; ?> onclick="show_turist('<?php echo $type; ?>', '<?php echo $reck; ?>', '<?php echo $param; ?>')">
			<td width="10%"><?php echo $reck; ?></td>
			<td width="30%"><?php echo $klient; ?></td>
			<td width="20%"><?php echo $date; ?></td>
			<td width="20%"><?php echo $sum; ?></td>
			<td width="20%"><?php echo $type_pay; ?></td>
		</tr>
<?php
		}
	}
?>
		</tbody>
		</table>
<?php
	}
	$html = ob_get_clean();
	return $html;
}

?>
