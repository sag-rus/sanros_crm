<?php

function set_time_payment($connect){
	$id = $_POST["id"];
	$pay = $connect->getRow("SELECT date FROM time_payment WHERE id_schet=?i AND type=1", $id);
	$prepay = $connect->getRow("SELECT date, sum FROM time_payment WHERE id_schet=?i AND type=2", $id);
	$array = array();
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Установить сроки оплаты. Заявка №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-4 control-label">Полностью оплатить до</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="pay-date" value="<?php echo $pay['date']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Предоплата</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="prepay-sum" value="<?php echo $prepay['sum']; ?>">
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Оплатить до</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="prepay-date" value="<?php echo $prepay['date']; ?>">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_time_payment('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_time_payment($connect){
	$id = $_POST["id"];
	$pay_date = $_POST["pay_date"];
	$prepay_date = $_POST["prepay_date"];
	$sum = $_POST["sum"];
	if($pay_date){
		$id_time = $connect->getOne("SELECT id FROM time_payment WHERE type=1 AND id_schet=?i", $id);
		if(!$id_time)
			$connect->query("INSERT INTO time_payment(id_schet, date) VALUES (?i, ?s)", $id, $pay_date);
		else
			$connect->query("UPDATE time_payment SET date=?s WHERE id=?i", $pay_date, $id_time);
	}
	if($sum){
		$id_time = $connect->getOne("SELECT id FROM time_payment WHERE type=2 AND id_schet=?i", $id);
		if(!$id_time)
			$connect->query("INSERT INTO time_payment(id_schet, date, sum, type) VALUES (?i, ?s, ?s, 2)", $id, $prepay_date, $sum);
		else
			$connect->query("UPDATE time_payment SET date=?s, sum=?s WHERE id=?i", $prepay_date, $sum, $id_time);
	}
}

function show_bron($connect){
	$id = $_POST["id"];
	$status = $connect->getOne("SELECT status FROM reckoning WHERE id=?i", $id);
	if($status == 1){
		$connect->query("UPDATE reckoning SET status=2 WHERE id=?i", $id);
		save_schet_to_history($connect, $id);
	}
}

function show_bill($connect){
	$id = $_POST["id"];
	$payers = get_payer_bill($connect, $id);
	$html_payers = "";
	foreach($payers as $id_payer => $payer){
		$type = "Физ.";
		if($payer["type"] == 2)
			$type = "Юр.";
		if($html_payers)
			$html_payers.= "<br />";
		$html_payers.= "<label class='control-label'>";
		if($payer["id"])
			$html_payers.= "<input type='radio' ".$payer["checked"]." name='payer' value='".$payer["id"]."' />&nbsp;".$type;
		$html_payers.= "&nbsp;".$payer["name"];
		$html_payers.= "</label>";
	}
	$style_status = "style='display: none'";
	$check_status = "";
	$array_status = array();
	$row = $connect->getRow("SELECT status_san, agency, turist FROM reckoning WHERE id=?i AND status!=5", $id);
	$status_san = $row["status_san"];
	if($status_san == 4){
		$array_status["all"] = " CHECKED ";
		$style_status = "";
		$check_status = " CHECKED ";
	}elseif($status_san == 5){
		$array_status["part"] = " CHECKED ";
		$style_status = "";
		$check_status = " CHECKED ";
	}else
		$array_status["all"] = " CHECKED ";
	$button_email = 0;
	if($row["agency"] AND $connect->getOne("SELECT login FROM agency WHERE id=?i", $row["agency"]))
		$button_email = 1;
	elseif($connect->getOne("SELECT login FROM klient WHERE id=?i", $row["turist"]))
		$button_email = 1;
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Счет №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">Счет от</label>
						<div class="col-sm-9 date_checked">
							<label class="control-label"><input type="radio" name="type_date" value="create" CHECKED /> даты заявки</label><br />
							<label class="control-label"><input type="radio" name="type_date" value="today" /> сегодняшнего числа</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Плательщик</label>
						<div class="col-sm-9 payers">
							<?php echo $html_payers; ?>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-6 control-label"><input type="checkbox" id="pay_on_place" <?php echo $check_status; ?> onclick="$('#type_pay').toggle()" /> Клиент оплатит на месте</label>
						<div class="col-sm-6">
						</div>
					</div>
					<div class="form-group form-group-margin" id="type_pay" <?php echo $style_status; ?>>
						<label class="col-sm-3 control-label"></label>
						<div class="col-sm-9">
							<label class="control-label"><input type="radio" <?php echo $array_status["all"]; ?> name="type_pay" value="all" />Всю сумма</label><br />
							<label class="control-label"><input type="radio" <?php echo $array_status["part"]; ?> name="type_pay" value="part" />Часть суммы</label>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-save" onclick="form_bill('<?php echo $id; ?>')"><i class="fa fa-file-pdf-o"></i> Сформировать</button>
				<?php if($button_email == 1){ ?>
					<button type="button" class="btn btn-success" onclick="form_bill('<?php echo $id; ?>', 'send_mail')"><i class="fa fa-share"></i> Отправить</button>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_bill($connect){
	$id = $_POST["id"];
	$payer = $_POST["payer"];
	$pay_on_place = $_POST["status_san"];
	$row = $connect->getRow("SELECT id_obj, id_com, status, status_san, agency, payer, website, turist FROM reckoning WHERE id=?i", $id);
	$status_san = $row["status_san"];
	if($row["status"] == 2){
		$connect->query("UPDATE reckoning SET status=3 WHERE id=?i", $id);
		save_schet_to_history($connect, $id);
	}
	if($pay_on_place){
		if($pay_on_place == "all" AND $status_san != 4){
			$connect->query("UPDATE reckoning SET status_san=4 WHERE id=?i", $id);
			save_schet_to_history($connect, $id, "Изменен статус санатория: вся оплата на месте");
		}elseif($pay_on_place == "part" AND $status_san != 5){
			$connect->query("UPDATE reckoning SET status_san=5 WHERE id=?i", $id);
			save_schet_to_history($connect, $id, "Изменен статус санатория: частичная оплата на месте");
		}
	}elseif($status_san == 4 OR $status_san == 5){
		$connect->query("UPDATE reckoning SET status_san=0 WHERE id=?i", $id);
		save_schet_to_history($connect, $id, "Изменен статус санатория: не оплачен");
	}
	$connect->query("UPDATE reckoning SET payer=?i WHERE id=?i", $payer, $id);
}

function get_payer_bill($connect, $id, $table = ""){
	$array_payer = array();
	if($table == "certificate")
		$row = $connect->getRow("SELECT klient, payer FROM certificate WHERE id=?i", $id);
	else
		$row = $connect->getRow("SELECT turist, agency, payer FROM reckoning WHERE id=?i", $id);
	$payer_schet = $row["payer"];
	if($table == "certificate")
		$turist = $row["klient"];
	else
		$turist = $row["turist"];
	if(!$turist){
		$agency = $row["agency"];
		$array_payer[1]["name"] = $connect->getOne("SELECT name FROM agency WHERE id=?i", $agency);
	}else{
		$payer_turist = $connect->getOne("SELECT payer FROM klient WHERE id=?i", $turist);
		if(!$payer_turist){
			$array = $connect->getRow("SELECT surname, name, otch, date, passport FROM klient WHERE id=?i", $turist);
			$fio = $array["surname"]." ".$array["name"]." ".$array["otch"];
			$date_b = $array["date"];
			$passport = $array["passport"];
			$connect->query("INSERT INTO payer(id_turist, type, name, date_b, passport) VALUES (?i, 1, ?s, ?s, ?s)", $turist, $fio, $date_b, $passport);
			$last_id = $connect->insertId();
			$connect->query("UPDATE reckoning SET payer=?i WHERE id=?i", $last_id, $id);
			$connect->query("UPDATE klient SET payer=?i WHERE id=?i", $last_id, $turist);
			$array_payer[1]["name"] = $fio;
			$array_payer[1]["type"] = 1;
			$array_payer[1]["id"] = $last_id;
			$array_payer[1]["checked"] = "CHECKED";
		}else{
			$payers = explode("_", $payer_turist);
			$payers = array_diff($payers, array());
			foreach($payers as $payer){
				$row = $connect->getRow("SELECT id, name, type FROM payer WHERE id=?i", $payer);
				$array_payer[$row["id"]]["name"] = $row["name"];
				$array_payer[$row["id"]]["id"] = $row["id"];
				$array_payer[$row["id"]]["type"] = $row["type"];
				if($payer == $payer_schet)
					$array_payer[$row["id"]]["checked"] = "CHECKED";
				else
					$array_payer[$row["id"]]["checked"] = "";
			}
		}
	}
	return $array_payer;
}

function show_contract($connect){
	$id = $_POST["id"];
	$version = $_POST["ver"];
	$payers = get_payer_bill($connect, $id);
	$html_payers = "";
	$html_services_1 = "";
	$html_services_2 = "";
	foreach($payers as $id_payer => $payer){
		$type = "Физ.";
		if($payer["type"] == 2)
			$type = "Юр.";
		if($html_payers)
			$html_payers.= "<br />";
		$html_payers.= "<label class='control-label'>";
		if($payer["id"])
			$html_payers.= "<input type='radio' ".$payer["checked"]." name='payer' value='".$payer["id"]."' />&nbsp;".$type;
		$html_payers.= "&nbsp;".$payer["name"];
		$html_payers.= "</label>";
	}
	$reck_row = $connect->getRow("SELECT id_services, type FROM reckoning WHERE id=?i", $id);
	$service_reckoning = explode("_", $reck_row['id_services']);
	$data = $connect->getAll("SELECT id, name, type FROM price_includes ORDER BY head, sort_order, name DESC, type, id");
	foreach($data as $row){
		$checked = "";
		if(in_array($row["id"], $service_reckoning))
			$checked = " CHECKED ";
		if($row["type"] != 2)
			$html_services_1.= "<label class='control-label'><input type='checkbox' ".$checked." class='services' value='".$row["id"]."' />".$row["name"]."</labe><br />";
		else
			$html_services_2.= "<label class='control-label'><input type='checkbox' ".$checked." class='services' value='".$row["id"]."' />".$row["name"]."</label><br />";
	}
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Договор №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">Дата договора</label>
						<div class="col-sm-9 dates">
							<label class="control-label"><input type="radio" name="dates" value="today" CHECKED /> сегодняшняя</label><br />
							<label class="control-label"><input type="radio" name="dates" value="zayvka" /> дата заявки</label>
						</div>
					</div>
                    <?php if($reck_row['type'] == 0) { ?>
					<div class="form-group">
						<label class="col-sm-3 control-label">Услуги</label>
						<div class="col-sm-9">
							<?php echo $html_services_1."<hr />".$html_services_2; ?>
						</div>
					</div>
                    <?php } ?>
					<div class="form-group">
						<label class="col-sm-3 control-label">Плательщик</label>
						<div class="col-sm-9 payers">
							<?php echo $html_payers; ?>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Предоплата</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="prepay_sum" onkeypress="validate_sum('prepay_sum')" />
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label">Остаток оплатить до</label>
						<div class="col-sm-9">
							<input type="text" class="form-control datepicker" id="date_to" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_show_contract('<?php echo $id; ?>', '<?php echo $version; ?>')"><i class="fa fa-file-pdf-o"></i> Сформировать</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_contract($connect){
	$id = $_POST["id"];
	$check = $_POST["check"];
	$payer = $_POST["payer"];
	$connect->query("UPDATE reckoning SET payer=?i, id_services=?s WHERE id=?i", $payer, $check, $id);
}

function show_obmen($connect){
	$id = $_POST["id"];
	$html_services_1 = "";
	$html_services_2 = "";
	$service_reckoning = explode("_", $connect->getOne("SELECT id_services FROM reckoning WHERE id=?i", $id));
	$data = $connect->getAll("SELECT id, name, type FROM price_includes ORDER BY head DESC, type, id");
	foreach($data as $row){
		$checked = "";
		if(in_array($row["id"], $service_reckoning))
			$checked = " CHECKED ";
		if($row["type"] != 2)
			$html_services_1.= "<label class='control-label'><input type='checkbox' ".$checked." class='services' value='".$row["id"]."' />".$row["name"]."</labe><br />";
		else
			$html_services_2.= "<label class='control-label'><input type='checkbox' ".$checked." class='services' value='".$row["id"]."' />".$row["name"]."</label><br />";
	}
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Обменная путевка №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body form-horizontal">
				<div class="form-group">
					<label class="col-sm-4 control-label">Количество путевок</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="dubl" value="1">
					</div>
				</div>
				<!--
				<div class="form-group">
					<div class="col-sm-4">
					</div>
					<div class="col-sm-8">
						<label class="control-label"><input type="checkbox" id="show_pay" /> Не показывать оплату</label>
					</div>
				</div>
				-->
				<div class="form-group">
					<div class="col-sm-4">
					</div>
					<div class="col-sm-8">
						<label class="control-label"><input type="checkbox" id="show-reduced" /> Уменьшенная путевка</label>
					</div>
				</div>
				<!--
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">В стоимость входит</label>
					<div class="col-sm-8">
						<?php echo $html_services_1."<hr />".$html_services_2; ?>
					</div>
				</div>
				-->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="show_obmen_blank(<?php echo $id; ?>)"><i class="fa fa-file-pdf-o"></i> Сформировать</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}



function prepay_schet($connect){
	global $session_login;
	$id = $_POST["id"];

	if(isset($_POST['password'])) {
		include_once('login.php');

		if(check_login($connect)) {
			$status = $connect->getOne("SELECT status_san FROM reckoning WHERE id=?i", $id);
			if($status != 4 AND $status != 5)
				$status_san = "<select id='type_pay' class='form-control'><option selected disabled>Способ оплаты</option><option value='1'>безналичный</option><option value='2'>наличными</option></select>";
			else
				$status_san = "Оплата на месте<input type='hidden' id='type_pay' value='4' />";
			$sum_to_pay = get_sum_for_pay($connect, $id);
		?>
		<div class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
						<h4 class="modal-title">Предоплата. Заявка №<?php echo $id; ?></h4>
					</div>
					<div class="modal-body">
						<div class="form-horizontal prepay-schet">
							<div class="form-group">
								<label class="col-sm-4 control-label">Сумма предоплаты</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="prepay" onkeypress="validate_sum('prepay')">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Способ оплаты</label>
								<div class="col-sm-8">
									<select id="type_pay" class="form-control">
										<option selected disabled>Способ оплаты</option>
										<option value="1">Безналичный</option>
										<option value="2">Наличными</option>
                                        <option value="5-1">Банковской картой</option>
                                        <option value="5-2">Банковской картой через терминал</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Офис</label>
								<div class="col-sm-8">
									<?php echo get_office_for_pay($connect); ?>
								</div>
							</div>
							<div class="form-group form-group-margin">
								<label class="col-sm-4 control-label">Дата</label>
								<div class="col-sm-8">
									<input type="text" class="form-control datepicker" id="date-pay" value="<?php echo date("Y-m-d"); ?>" />
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success btn-sm" onclick="save_prepay('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
					</div>
				</div>
			</div>
		</div>
		<?php
		} else {
			?>
			<div class="alert alert-danger warning-alert"><i class="fa fa-exclamation-triangle"></i>&nbsp;Доступ запрещен!</div>
			<?php
		}
	} else {
	?>
	<div class="modal fade">
		<div class="modal-dialog">
			<form class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
					<h4 class="modal-title">Оплата. Заявка №<?php echo $id; ?></h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-warning text-center">Для выполнения операции подтвердите пароль учетной записи!</div>
					<br />
					<div class="form-horizontal form-check-pass">
						<div class="form-group">
							<label class="col-sm-4 control-label">Ваш пароль</label>
							<div class="col-sm-8">
								<?php global $login; ?>
								<input type="hidden" id="login" value="<?=$login?>" />
								<input type="password" class="form-control" id="password" />
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success btn-sm" onclick="check_pass(<?=$_POST['id']?>, 'prepay_schet'); return false;"><i class="fa fa-check-circle"></i> Подтвердить</button>
				</div>
			</form>
		</div>
	</div>
	<?php
	}

	$html = ob_get_clean();
	return $html;
}

function save_prepay($connect){
	global $id_rights;
	$id = $_POST["id"];
	$sum = $_POST["sum"];
	$type = $_POST["type"];
	$office = (int)$_POST["office"];
	$date = date_change($_POST["date"], "-", ".");
	$row = $connect->getRow("SELECT id_user, sum FROM reckoning WHERE id=?i", $id);
	$all_sum = $row["sum"];
	$user = $row["id_user"];
	if(($id_rights > 3) AND ($all_sum > $sum)){
		$connect->query("UPDATE reckoning SET status=4 WHERE id=?i", $id);
		save_payment($connect, $id, $sum, 1, "", $date, $type, $office);
		save_schet_to_history($connect, $id);
		$data = $connect->getAll("SELECT id FROM reservation WHERE id_reck=?i AND status=3", $id);
		foreach($data as $row){
			$connect->query("UPDATE reservation SET status=4 WHERE id=?i", $row["id"]);
			save_reservation_history($connect, $row["id"], "Автоматическое изменение статуса");
		}
		$connect->query("INSERT INTO notification(text, user) VALUES (?s, ?i)", "Предоплата путевки №".$id, $user);
		return 1;
	}
}

function pay_schet($connect){
	ob_start();
	$id = $_POST["id"];

	if(isset($_POST['password'])) {
		include_once('login.php');

		if(check_login($connect)) {
			$status = $connect->getOne("SELECT status_san FROM reckoning WHERE id=?i", $id);
			if($status != 4 AND $status != 5)
				$status_san = '<select id="type_pay" class="form-control"><option selected disabled>Способ оплаты</option><option value="1">Безналичный</option><option value="2">Наличными</option><option value="5-1">Банковской картой</option><option value="5-2">Банковской картой через терминал</option></select>';
			else
				$status_san = 'Оплата на месте<input type="hidden" id="type_pay" value="4" />';
			$sum_to_pay = get_sum_for_pay($connect, $id);
		?>
		<div class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
						<h4 class="modal-title">Оплата. Заявка №<?php echo $id; ?></h4>
					</div>
					<div class="modal-body">
						<div class="form-horizontal pay-schet">
							<div class="form-group">
								<label class="col-sm-4 control-label">Сумма оплаты</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="pay_sum" onkeypress="validate_sum('pay_sum')" value="<?php echo $sum_to_pay; ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Способ оплаты</label>
								<div class="col-sm-8">
									<?php echo $status_san; ?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Офис</label>
								<div class="col-sm-8">
									<?php echo get_office_for_pay($connect); ?>
								</div>
							</div>
							<div class="form-group form-group-margin">
								<label class="col-sm-4 control-label">Дата</label>
								<div class="col-sm-8">
									<input type="text" class="form-control datepicker" id="date-pay" value="<?php echo date("Y-m-d"); ?>" />
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success btn-sm" onclick="save_pay_schet('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
					</div>
				</div>
			</div>
		</div>
		<?php
		} else {
			?>
			<div class="alert alert-danger warning-alert"><i class="fa fa-exclamation-triangle"></i>&nbsp;Доступ запрещен!</div>
			<?php
		}
	} else {
		?>
		<div class="modal fade">
			<div class="modal-dialog">
				<form class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
						<h4 class="modal-title">Оплата. Заявка №<?php echo $id; ?></h4>
					</div>
					<div class="modal-body">
						<div class="alert alert-warning text-center">Для выполнения операции подтвердите пароль учетной записи!</div>
						<br />
						<div class="form-horizontal form-check-pass">
							<div class="form-group">
								<label class="col-sm-4 control-label">Ваш пароль</label>
								<div class="col-sm-8">
									<?php global $login; ?>
									<input type="hidden" id="login" value="<?=$login?>" />
									<input type="password" class="form-control" id="password" />
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success btn-sm" onclick="check_pass(<?=$_POST['id']?>, 'pay_schet'); return false;"><i class="fa fa-check-circle"></i> Подтвердить</button>
					</div>
				</form>
			</div>
		</div>
	<?php
	}

	$html = ob_get_clean();
	return $html;
}

function save_pay_schet($connect){
	global $id_rights, $bonus_rec, $bonus_ref;
	$id = $_POST["id"];
	$type = $_POST["type"];
	$pay_sum = $_POST["pay_sum"];
	$office = $_POST["office"];
	$date = date_change($_POST["date"], "-", ".");
	$row = $connect->getRow("SELECT status, turist, agency, sum, status_san, website, id_user FROM reckoning WHERE id=?i", $id);
	$status = $row["status"];
	$user = $row["id_user"];
	if($id_rights > 3){
		$today = date("Y-m-d");
		$klient = $row["turist"];
		$agency = $row["agency"];
		$sum = $row["sum"];
		if($klient){
			$bonus = (int)$sum * $bonus_rec;
			$bonus_referral = (int)$sum * $bonus_ref;
			$id_bonus = $connect->getOne("SELECT id FROM bonus WHERE schet=?i AND sum>0 AND turist=?i", $id, $klient);
			if($id_bonus)
				$connect->query("UPDATE bonus SET date=?s, sum=?i WHERE id=?i", $today, $bonus, $id_bonus);
			else{
				$connect->query("INSERT INTO bonus(date, schet, turist, sum) VALUES (?s, ?i, ?i, ?i)", $today, $id, $klient, $bonus);
				$invited = $connect->getOne("SELECT invieted FROM klient WHERE id=?i", $klient);
				if($invited){
					$connect->query("INSERT INTO bonus(date, schet, turist, sum, type) VALUES (?s, ?i, ?i, ?s, 4)", $today, $id, $invited, $bonus_referral);
				}
			}
			if($connect->getOne("SELECT login FROM klient WHERE id=?i AND active=1", $klient))
				$answer = "cabinet";
		}elseif($agency)
			$answer = "mail";

		$connect->query("UPDATE reckoning SET status=5 WHERE id=?i", $id);
		save_payment($connect, $id, $pay_sum, 2, "", $date, $type, $office);
		save_schet_to_history($connect, $id);
		save_notification($connect, "Оплата путевки №".$id, $user);
	}
	return $answer;
}

function permit_pay_schet_san($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reckoning SET status_san=2 WHERE id=?i", $id);
	save_schet_to_history($connect, $id, "Разрешение на оплату в санаторий");
}

function permit_prepay_schet_san($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reckoning SET status_san=6 WHERE id=?i", $id);
	save_schet_to_history($connect, $id, "Разрешение на предоплату в санаторий");
}

function return_schet_san($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reckoning SET status_san=0 WHERE id=?i", $id);
	save_schet_to_history($connect, $id, "Возврат");
}

function pay_schet_san($connect){
	$id = $_POST["id"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Оплата в санаторий. Заявка №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal pay-schet-san">
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата оплаты</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date-opl" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Номер плат.поручения</label>
						<div class="col-sm-8">
							<input type="text" class="form-control pay-number" />
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Сумма платежа</label>
						<div class="col-sm-8">
							<input type="text" class="form-control sum-san" onkeypress="validate_sum('sum_san')" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_pay_schet_san('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>

<?php
	$html = ob_get_clean();
	return $html;
}

function save_pay_schet_san($connect){
	$id = $_POST["id"];
	$date = $_POST["date"];
	$pay_number = $_POST["pay_number"];
	$sum_san = (float)(str_replace(',','.',$_POST["sum_san"]));
	$connect->query("UPDATE reckoning SET status_san=1 WHERE id=?i", $id);
	save_payment($connect, $id, $sum_san, 4, $pay_number, $date, 0, 1);
	save_schet_to_history($connect, $id, "Оплачено в санаторий");
	$data = $connect->getAll("SELECT id FROM reservation WHERE id_reck=?i AND status=5", $id);
	foreach($data as $row){
		$connect->query("UPDATE reservation SET status=6 WHERE id=?i", $row["id"]);
		save_reservation_history($connect, $row["id"], "Автоматическое изменение статуса");
	}
}

function prepay_schet_san($connect){
	$id = $_POST["id"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Предоплата в санаторий. Заявка №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal prepay-schet-san">
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата оплаты</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date-opl" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Номер плат.поручения</label>
						<div class="col-sm-8">
							<input type="text" class="form-control pay-number">
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Сумма платежа</label>
						<div class="col-sm-8">
							<input type="text" class="form-control sum-san" onkeypress="validate_sum('sum_san')">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_prepay_schet_san('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_prepay_schet_san($connect){
	$id = $_POST["id"];
	$date =$_POST["date"];
	$pay_number = $_POST["pay_number"];
	$sum_san = (float)(str_replace(',','.',$_POST["sum_san"]));
	$connect->query("UPDATE reckoning SET status_san=3 WHERE id=?i", $id);
	save_payment($connect, $id, $sum_san, 3, $pay_number, $date, 0);
	save_schet_to_history($connect, $id, "Предоплата в санаторий");
}

function request_pay_schet($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reckoning SET status=7 WHERE id=?i", $id);
	save_schet_to_history($connect, $id);
}

function show_return_oplata_query(){
	$id = $_POST["id"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Заявка на возврат №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal return-oplata">
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата заявления</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker date-return" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Сумма возврата</label>
						<div class="col-sm-8">
							<input type="text" class="form-control sum-return" />
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Способ оплаты</label>
						<div class="col-sm-8">
							<select class="form-control type-pay">
								<option selected disabled>Способ оплаты</option>
								<option value="1">безналичный</option>
								<option value="2">наличными</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" onclick="save_return_oplata_query('<?php echo $id; ?>')" class="btn btn-success"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_return_oplata(){
	$id = $_POST["id"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Возврат №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal return-oplata">
					<div class="form-group">
						<label class="col-sm-4 control-label">Сумма возврата</label>
						<div class="col-sm-8">
							<input type="text" class="form-control sum-return" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Способ оплаты</label>
						<div class="col-sm-8">
							<select class="form-control type-pay">
								<option selected disabled>Способ оплаты</option>
								<option value="1">безналичный</option>
								<option value="2">наличными</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date-return" />
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Номер плат.поручения</label>
						<div class="col-sm-8">
							<input type="text" class="form-control number-return" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" onclick="save_return_oplata('<?php echo $id; ?>')" class="btn btn-success"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_return_oplata($connect){
	global $id_rights, $bonus_rec, $bonus_ref;
	$id = $_POST["id"];
	$sum = $_POST["sum"];
	$type = $_POST["type"];
	$date = $_POST["date"];
	$number = $_POST["number"];
	$row = $connect->getRow("SELECT sum, status, turist FROM reckoning WHERE id=?i", $id);
	$status = $row["status"];
	if(($status == 4 OR $status == 5 OR $status == 6 OR $status == 8) AND ($id_rights > 3)){
		save_payment($connect, $id, $sum, 5, $number, $date, $type);
		recalculation_sum($connect, $id);
		if($row["turist"]){
			$new_bonus = $row["sum"] * $bonus_rec;
			$new_bonus_ref = $row["sum"] * $bonus_ref;
			$connect->query("UPDATE bonus SET sum=?i WHERE schet=?i AND sum > 0 AND turist=?i", $new_bonus, $id, $row["turist"]);
			$invited = $connect->getOne("SELECT invited FROM klient WHERE id=?i", $row["turist"]);
			if($invited)
				$connect->query("UPDATE bonus SET sum=?s WHERE schet=?i AND sum > 0 AND turist=?i", $new_bonus_ref, $id, $invited);
		}
		save_schet_to_history($connect, $id, "Возврат");
		$connect->query("UPDATE return_query SET active=2 WHERE id_reck=?i", $id);
		return 1;
	}
	return FALSE;
}

function show_pay_by_certificate(){
	$id = $_POST["id"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Оплата сертификатом. Заявка №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-4 control-label">Код сертификата</label>
						<div class="col-sm-8">
							<input type="text" class="form-control key-cert" onkeyup="check_key_certificate('<?php echo $id; ?>')" />
						</div>
					</div>
					<div class="form-group form-group-margin">
						<div class="col-sm-12 pay-cert-result"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function check_key_certificate($connect){
	$id = $_POST["id"];
	$key = $_POST["key"];
	$row = $connect->getRow("SELECT id, status, sum FROM certificate WHERE code=?i", $key);
	$id_cert = $row["id"];
	if($id_cert){
		$status = $row["status"];
		if($status < 4)
			$html = "<div class='alert alert-info'>Сертификат не оплачен</div>";
		elseif($status == 4){
			$sum = $row["sum"];
			$html.= "<div style='text-align: right'><button type='button' class='btn btn-success btn-sm' onclick='select_certificate_for_schet(\"".$id_cert."\", \"".$id."\")'>Оплатить ".$sum." <i class='fa fa-rub'></i></button></div>";
		}elseif($status == 5)
			$html = "<div class='alert alert-info'>Сертификат аннулирован</div>";
		elseif($status == 6)
			$html = "<div class='alert alert-info'>Сертификат использован</div>";
	}else
		$html = "<div class='alert alert-info'>Сертификат не найден</div>";
	return $html;
}

function pay_certificate_for_schet($connect){
	global $bonus_rec, $bonus_ref;
	$id = $_POST["id"];
	$id_cert = $_POST["id_cert"];
	$row = $connect->getRow("SELECT status, sum FROM certificate WHERE id=?i", $id_cert);
	$sum_cert = $row["sum"];
	$status = $row["status"];
	if($status == 4){
		$today = date("Y-m-d");
		$sum_to_pay = get_sum_for_pay($connect, $id);
		if($sum_to_pay > $sum_cert){
			$connect->query("UPDATE reckoning SET status=4 WHERE id=?i", $id);
			save_payment($connect, $id, $sum_cert, 1, "", $today, 3);
			save_schet_to_history($connect, $id, "Предоплата подарочным сертификатом");
		}else{
			$sum = $connect->getOne("SELECT sum FROM reckoning WHERE id=?i", $id);
			$bonus = (int)$sum * $bonus_rec;
			$bonus_referral = (int)$sum * $bonus_ref;
			$klient = $connect->getOne("SELECT turist FROM reckoning WHERE id=?i", $id);
			$connect->query("UPDATE reckoning SET status=5 WHERE id=?i", $id);
			save_payment($connect, $id, $sum_to_pay, 2, '', $today, 3);
			save_schet_to_history($connect, $id, "Оплата подарочным сертификатом");
			$connect->query("INSERT INTO bonus(date, schet, turist, sum) VALUES (?s, ?i, ?i, ?s)", $today, $id, $klient, $bonus);
			$invited = $connect->getOne("SELECT invited FROM klient WHERE id=?i", $klient);
			if($invited)
				$connect->query("INSERT INTO bonus(date, schet, turist, sum, type) VALUES (?s, ?i, ?i, ?s, 4)", $today, $id, $invited, $bonus_referral);
		}
		$connect->query("UPDATE certificate SET status=6, schet=?i WHERE id=?i", $id, $id_cert);
		save_certificate_to_history($connect, $id_cert);
	}
}

function remove_payment($connect){
	$id = $_POST["id"];
	$connect->query("DELETE FROM bonus WHERE schet=?i AND sum > 0", $id);
	$connect->query("DELETE FROM payment WHERE schet=?i AND (type=1 OR type=2)", $id);
	$connect->query("UPDATE reckoning SET status=3 WHERE id=?i", $id);
	save_schet_to_history($connect, $id, "Снятие оплаты");
}

function return_schet($connect){
	$id = $_POST["id"];
	$old = $_POST["old"];
	$new = $connect->getOne("SELECT new_status FROM history_schet WHERE id_schet=?i AND new_status!=?i ORDER BY id DESC", $id, $old);
	$connect->query("UPDATE reckoning SET status=?i WHERE id=?i", $new, $id);
	save_schet_to_history($connect, $id, "Возврат");
}

function save_return_oplata_query($connect){
	$id = $_POST["id"];
	$sum = $_POST["sum"];
	$date = $_POST["date"];
	$type = $_POST["type"];
	$connect->query("INSERT INTO return_query(id_reck, date_create, sum, type_pay) VALUES (?i, ?i, ?s, ?i)", $id, time(), $sum, $type);
	if($date != ""){
		$insert = $connect->insertId();
		$connect->query("UPDATE return_query SET date=?s WHERE id=?i", $date, $insert);
	}
}

function sent_report_agent($connect){
	global $id_rights;
	$id = $_POST["id"];
	$status_agent = $connect->getOne("SELECT status_agent FROM reckoning WHERE id=?i", $id);
	if($id_rights > 3 AND $status_agent == 0){
		$connect->query("UPDATE reckoning SET status_agent=1 WHERE id=?i", $id);
		save_schet_to_history($connect, $id, "Отчет агента выслан");
	}
}

function received_report_agent($connect){
	global $id_rights;
	$id = $_POST["id"];
	$status_agent = $connect->getOne("SELECT status_agent FROM reckoning WHERE id=?i", $id);
	if($id_rights > 3 AND $status_agent == 1){
		$connect->query("UPDATE reckoning SET status_agent=2 WHERE id=?i", $id);
		save_schet_to_history($connect, $id, "Отчет агента получен");
	}
}

function return_cancel($connect){
	$id = $_POST["id"];
	$status = $connect->getOne("SELECT status FROM reckoning WHERE id=?i", $id);
	if($status == 6){
		$connect->query("UPDATE reckoning SET status=2 WHERE id=?i", $id);
		save_schet_to_history($connect, $id, "Возврат в работу");
	}
}

function reckoning_put_aside($connect){
	$id = $_POST["id"];
	$status = $connect->getOne("SELECT status FROM reckoning WHERE id=?i", $id);
	if($status < 4){
		$connect->query("UPDATE reckoning SET status=9 WHERE id=?i", $id);
		save_schet_to_history($connect, $id, "Заявка отложена");
	}
}

function reckoning_from_aside($connect){
	$id = $_POST["id"];
	$status = $connect->getOne("SELECT status FROM reckoning WHERE id=?i", $id);
	if($status == 9){
		$last_id = $connect->getOne("SELECT id FROM history_schet WHERE id_schet=?i AND note='Заявка отложена' AND new_status=9 ORDER BY id DESC", $id);
		$status = $connect->getOne("SELECT new_status FROM history_schet WHERE id_schet=?i AND id<?i ORDER BY id DESC", $id, $last_id);
		if(!$status)
			$status = 1;
		$connect->query("UPDATE reckoning SET status=?i WHERE id=?i", $status, $id);
		save_schet_to_history($connect, $id, "Заявка возвращена в работу");
	}
}

function agency_document(){
	$id = $_POST["id"];
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Разрешить распечатать путевку из личного кабинета</h4>
			</div>
			<div class="modal-footer center">
				<button type="button" class="btn btn-success btn-sm" onclick="confirm_agency_document('<?php echo $id; ?>', 'putevka')"><i class="fa fa-check-circle"></i> Разрешить</button>
				<button type="button" class="btn btn-danger btn-sm" onclick="remove_all_windows()"><i class="fa fa-times-circle"></i> Нет</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function confirm_agency_document($connect){
	$id = $_POST["id"];
	$doc = $_POST["doc"];
	if($connect->getOne("SELECT id FROM agency_document WHERE id_reck=?i", $id))
		$connect->query("UPDATE agency_document SET ".$doc."=1 WHERE id_reck=?i", $id);
	else
		$connect->query("INSERT INTO agency_document (id_reck, ".$doc.") VALUES (?i, 1)", $id);
}

function show_form_review_cancel($connect){
	$id = $_POST["id"];
	$status = $_POST["status"];
	$row = $connect->getRow("SELECT cause, note FROM cancellation WHERE schet=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Аннуляция №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body form-horizontal cancel-form">
				<div class="form-group">
					<label class="col-sm-4 control-label">Причина (для санаториев)</label>
					<div class="col-sm-8">
						<input type="text" class="form-control cause" value="<?php echo $row['cause']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Примечание</label>
					<div class="col-sm-8">
						<input type="text" class="form-control note" value="<?php echo $row['note']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Причина аннуляции</label>
					<div class="col-sm-8" id="reason_cancel">
						<?php echo get_select_table($connect, "reason_delete", "", $connect->getOne("SELECT reason_delete FROM reckoning WHERE id=?i", $id), "reason-delete", 1); ?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger btn-sm" onclick="save_cancelation('<?php echo $id; ?>', '<?php echo $status; ?>')"><i class="fa fa-check-circle"></i> Аннулировать</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_cancelation($connect){
	$id = $_POST["id"];
	$status = $_POST["status"];
	$cause = $_POST["cause"];
	$note = $_POST["note"];
	$reason = $_POST["reason"];
	$today = date("Y-m-d");
	$row = $connect->getRow("SELECT turist, status, promo_code FROM reckoning WHERE id=?i", $id);
	$old = $row["status"];
	$turist = $row["turist"];
	$promocode = $row["promo_code"];
	if($old != 6){
		$cause_turist = $connect->getOne("SELECT name FROM reason_delete WHERE id=?i", $reason);
		if(!$connect->getOne("SELECT id FROM cancellation WHERE schet=?i", $id))
			$connect->query("INSERT INTO cancellation(date, cause, cause_turist, schet, note) VALUES(?s, ?s, ?s, ?i, ?s)", $today, $cause, $cause_turist, $id, $note);
		else
			$connect->query("UPDATE cancellation SET date=?s, cause=?s, cause_turist=?s, note=?s WHERE schet=?i", $today, $cause, $cause_turist, $note, $id);
		$connect->query("UPDATE reckoning SET status=?i, reason_delete=?i WHERE id=?i", $status, $reason, $id);
		$connect->query("DELETE from bonus WHERE schet=?i", $id);
		if($promocode){
			$turist = $row["turist"];
			$connect->query("DELETE from bonus WHERE turist=?i AND promocode=?s", $turist, $promocode);
		}
		save_schet_to_history($connect, $id);
		check_status_booking_quota($connect, $id);
		if($turist AND $connect->getOne("SELECT id FROM klient WHERE email!='' AND id=?i", $turist))
			return "cabinet";
	}
}

function save_cancelation_not_writing($connect){
	$id = $_POST["id"];
	$status = $_POST["status"];
	$row = $connect->getRow("SELECT status, promo_code, turist FROM reckoning WHERE id=?i", $id);
	$old = $row["status"];
	$promocode = $row["promo_code"];
	if($old != 6){
		$connect->query("UPDATE reckoning SET status=?i WHERE id=?i", $status, $id);
		$connect->query("DELETE from bonus WHERE schet=?i", $id);
		if($promocode){
			$turist = $row["turist"];
			$connect->query("DELETE from bonus WHERE turist=?i AND promocode=?s", $turist, $promocode);
		}
		save_schet_to_history($connect, $id, "Без письма");
		check_status_booking_quota($connect, $id);
	}
}

function form_reckoning_to_upsorted($connect){
	$id = $_POST["id"];
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Удалить заявку №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body form-horizontal delete-form">
				<div class="form-group">
					<label class="col-sm-4 control-label">Причина удаления</label>
					<div class="col-sm-8">
						<?php echo get_select_table($connect, "reason_delete", "", "", "reason-delete", 1); ?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger btn-sm" onclick="save_reckoning_to_upsorted(<?php echo $id; ?>)"><i class="fa fa-check-circle"></i> Удалить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function reckoning_to_upsorted($connect){
	global $session_login;
	$id = $_POST["id"];
	$reason = $_POST["reason"];
	$connect->query("UPDATE reckoning SET active=3, reason_delete=?i WHERE id=?i", $reason, $id);
	$connect->query("DELETE from bonus WHERE schet=?i", $id);
	$row = $connect->getRow("SELECT promo_code, turist FROM reckoning WHERE id=?i", $id);
	$promocode = $row["promo_code"];
	if($promocode){
		$turist = $row["turist"];
		$connect->query("DELETE from bonus WHERE turist=?i AND promocode=?s", $turist, $promocode);
        $connect->query("DELETE FROM promo_code_using WHERE reck_id = ?i", $id);
	}

	if(!$connect->getOne("SELECT id_user FROM reckoning WHERE id=?i", $id))
		$connect->query("UPDATE reckoning SET id_user=?i WHERE id=?i", $session_login, $id);
	save_schet_to_history($connect, $id, "Заявка удалена");
	check_status_booking_quota($connect, $id);
}

function delete_reckoning($connect){
    global $id_rights;
	$id = $_POST["id"];
	$status = $connect->getOne("SELECT status FROM reckoning WHERE id=?i", $id);
	if(($status == 1 || $id_rights > 5) OR $status == 9){
		$connect->query("DELETE FROM reckoning WHERE id=?i LIMIT 1", $id);
		$connect->query("DELETE FROM position_reck WHERE schet=?i", $id);
		$connect->query("DELETE FROM bonus WHERE schet=?i", $id);
        $connect->query("DELETE FROM promo_code_using WHERE reck_id = ?i", $id);
		$connect->query("DELETE FROM history_schet WHERE id_schet=?i", $id);
		$connect->query("DELETE FROM payment WHERE schet=?i", $id);
		//change_auto_increment($connect, "reckoning");
	}
}

function reestablish_reckoning($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reckoning SET active=0 WHERE id=?i", $id);
	save_schet_to_history($connect, $id, "Заявка восстановлена");
}

function assign_reckoning($connect){
	global $session_login;
	$id = $_POST["id"];
	if($connect->getOne("SELECT id FROM reckoning WHERE (id_user IS NULL OR id_user='') AND id=?i", $id)){
		$connect->query("UPDATE reckoning SET id_user=?i WHERE id=?i", $session_login, $id);
		save_schet_to_history($connect, $id, "Присвоена менеджеру");
	}
}

function block_reckoning($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reckoning SET active=2 WHERE id=?i", $id);
	save_schet_to_history($connect, $id, "Заявка заблокирована");
}

function unblock_reckoning($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reckoning SET active=0 WHERE id=?i", $id);
	save_schet_to_history($connect, $id, "Заявка разблокирована");
}

function postponed_san_reckoning($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reckoning SET active=1 WHERE id=?i", $id);
	save_schet_to_history($connect, $id, "Счет санатория отложен");
}

function return_san_reckoning($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reckoning SET active=0 WHERE id=?i", $id);
	save_schet_to_history($connect, $id, "Счет санатория возвращен");
}

function delete_changes_reckoning($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reckoning SET changes='' WHERE id=?i", $id);
}

function show_form_correction_reckoning($connect){
	$id = $_POST["id"];
	$correction = $connect->getOne("SELECT correction FROM reckoning WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Установить поправку для заявки №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body form-horizontal correction">
				<div class="form-group">
					<label class="col-sm-4 control-label">Поправка</label>
					<div class="col-sm-8">
						<input type="text" class="form-control correction-reckoning" value="<?php echo $correction; ?>" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="set_correction_reckoning('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function set_correction_reckoning($connect){
	$id = $_POST["id"];
	$correction = $_POST["correction"];
	$connect->query("UPDATE reckoning SET correction=?s WHERE id=?i", $correction, $id);
}

function show_form_commission_reckoning($connect){
	$id = $_POST["id"];
	$commission = $connect->getOne("SELECT commission_value FROM reckoning WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Установить комиссию агентству для заявки №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body form-horizontal commission">
				<div class="form-group">
					<label class="col-sm-4 control-label">Комиссия</label>
					<div class="col-sm-8">
						<input type="text" class="form-control commission-reckoning" value="<?php echo $commission; ?>" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="set_commission_reckoning('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function set_commission_reckoning($connect){
	$id = $_POST["id"];
	$commission = $_POST["commission"];
	$connect->query("UPDATE reckoning SET commission_value=?s WHERE id=?i", $commission, $id);
}

?>
