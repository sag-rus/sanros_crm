<?php

	$array_color_guaranteed = array(1 => "#CCC", 2 => "#C0E8FF" , 3 => "#D7FFDF", 4 => "#F7FF96", 5 => "#FFCACA");

	$array_status_guaranteed = array(1 => "В продаже", 2 => "Выставлен счет" , 3 => "Оплачен", 4 => "Отложен", 5 => "Аннулирован");

	$array_color_status = array("red" => "#FFCFCF", "green" => "#D9FFBf3");

	$array_month = array(1 => "Январь", 2 => "Февраль", 3 => "Март", 4 => "Апрель", 5 => "Май", 6 => "Июнь", 7 => "Июль", 8 => "Август", 9 => "Сентябрь", 10 => "Октябрь", 11 => "Ноябрь", 12 => "Декабрь");

	$array_short_month = array(1 => "Янв", 2 => "Фев", 3 => "Март", 4 => "Апр", 5 => "Май", 6 => "Июнь", 7 => "Июль", 8 => "Авг", 9 => "Сен", 10 => "Окт", 11 => "Ноя", 12 => "Дек");

	$month_pad = array(1 => "января", 2 => "февраля", 3 => "марта", 4 => "апреля", 5 => "мая", 6 => "июня", 7 => "июля", 8 => "августа", 9 => "сентября", 10 => "октября", 11 => "ноября", 12 => "декабря");

	$array_week = array(0 => "Воскресенье", 1 => "Понедельник", 2 => "Вторник", 3 => "Среда", 4 => "Четверг", 5 => "Пятница", 6 => "Суббота");

	$CHAT_GROUP = array(
		1 => array("name" => "Оператор бронирования", "icon" => "fa-user-plus"),
		2 => array("name" => "Тех.поддержка", "icon" => "fa-cogs")
	);

define("EARTH_RADIUS", 6372795);

include_once("mail/form_mail.php");
include_once("mail/send_mail.php");

include_once("admin/admin.php");
include_once("admin/search.php");
include_once("admin/rating.php");
include_once("admin/sights.php");
// include_once("admin/upload_xml.php");
include_once("admin/news.php");
include_once("admin/manual.php");
include_once("admin/profkurort.php");
include_once("admin/sync.php");

include_once("objects/turist.php");
include_once("objects/agency.php");
include_once("objects/touroperator.php");
include_once("objects/object.php");

include_once("report/general.php");
include_once("report/report.php");
include_once("report/bonus.php");
include_once("report/graph.php");
include_once("report/module.php");
include_once("report/advertising.php");
include_once("report/chat-log.php");
include_once("report/cabinet-object.php");

include_once("upload/default.php");
include_once("upload/price.php");
include_once("upload/reserv.php");
include_once("upload/rating.php");
include_once("upload/promo.php");
include_once("upload/image.php");
include_once("upload/sights.php");
// include_once("upload/news.php");
include_once("upload/sync-database.php");

include_once("price/calendar.php");
include_once("price/search-engine.php");

include_once("login.php");
include_once("head.php");
include_once("price.php");
include_once("reckoning.php");
include_once("object.php");
include_once("status.php");
include_once("promo.php");
include_once("reminder.php");
include_once("certificate.php");
include_once("count.php");
include_once("profit.php");
include_once("question.php");
include_once("method.php");
include_once("chat.php");
include_once("my-profile.php");
include_once("panel.php");

function get_value($connect){
	$id = $_POST["id"];
	$table = $_POST["table"];
	$pole = $_POST["pole"];
	return $connect->getOne("SELECT ".$pole." FROM ".$table." WHERE id=?i", $id);
}

function get_rooms_object($connect){
	return select_rooms($connect, $_POST["id"]);
}

function replace_quotes($m)
{
  $pos = 0;
  while ($pos < mb_strlen($m) && FALSE !== ($pos = strpos($m, '"', $pos)))
  {
    $m = substr_replace($m, (!ctype_graph($m[$pos-1]) || $pos == 0) ? '«' : '»', $pos, 1); // Спасибо "Анониму" в комментариях за небольшой исправление
    $pos += 6;
  }
  return $m;
}

function help_search_by_name($connect){
	$poisk = $_POST["poisk"];
	$poisk_quotes = replace_quotes($poisk);
	//echo $poisk_quotes;
	$table = $_POST["table"];
	$func = $_POST["function"];
	if($table == "object")
		$data = $connect->getAll("SELECT id FROM object WHERE name LIKE ?s AND (id != 61 AND id != 62 AND id != 63 AND id != 64 AND id != 71)", "%".$poisk."%");
	elseif($table == "agency")
		$data = $connect->getAll("SELECT id, short_name, name, active FROM agency WHERE name LIKE ?s OR name LIKE ?s OR short_name LIKE ?s OR short_name LIKE ?s OR name LIKE ?s OR name LIKE ?s OR short_name LIKE ?s OR short_name LIKE ?s", "%".$poisk."%", "%".$poisk."%", "%".$poisk."%", "%".$poisk."%", "%".$poisk_quotes."%", "%".$poisk_quotes."%", $poisk_quotes."%", "%".$poisk_quotes."%");
	elseif($table == "tour_operator")
		$data = $connect->getAll("SELECT id, short_name, name FROM tour_operator WHERE name LIKE ?s OR short_name LIKE ?s", $poisk."%", $poisk."%");
	elseif($table == "st_website")
		$data = $connect->getAll("SELECT url FROM st_website WHERE url LIKE ?s", "%".$poisk."%");
	else
		$data = $connect->getAll("SELECT id, surname, name, otch, DATE_FORMAT(date, '%d.%m.%Y') as date FROM klient WHERE surname LIKE ?s", "%".$poisk."%");
	$id = 0;
	foreach($data as $row){
		if($table == "object"){
			$id = $row["id"];
			$object = get_object($connect, $id, "place");
		?>
			<span onclick="<?php echo $func; ?>(<?php echo $id; ?>, <?php echo $id; ?>)">
				<?php echo $object; ?>
			</span>
		<?php
		}elseif($table == "agency"){
			$id = $row["id"];
		?>
			<span onclick="select_klient(<?php echo $id; ?>, 'agency')">
			<?php echo $row["name"]; ?>
			<?php if($row["active"] == 1){ ?>
				 (в архиве)
			<?php } ?>
			</span>
		<?php }elseif($table == "tour_operator"){
			$id = $row["id"];
		?>
			<span onclick="<?php echo $func; ?>(<?php echo $id; ?>)">
				<?php echo $row["name"]; ?>
			</span>
		<?php }elseif($table == "st_website"){
			$id++;
			$url = $row["url"];
		?>
			<span onclick="select_website('<?php echo $url; ?>')">
				<?php echo $row["url"]; ?>
			</span>
		<?php }elseif($func){
			$id = $row["id"];
			$schet = $_POST["reck"];
			if(!$schet)
				$schet = 0;
		?>
			<span onclick="<?php echo $func; ?>(<?php echo $id; ?>, <?php echo $schet; ?>)">
				<?php echo $row["surname"]." ".$row["name"]." ".$row["otch"]; ?>
			</span>
		<?php
		}else{
			$id++;
		?>
			<span onclick="select_klient(<?php echo $id; ?>)">
				<?php echo $row["surname"]." ".$row["name"]." ".$row["otch"]."&emsp;".$date; ?>
			</span>
		<?php
		}
	}
	if($table == "object"){
	?>
		<span onclick="use_object('new')">Добавить новый объект</span>
	<?php
	}
}

function get_checkbox_table($connect, $table = ""){
	if(!$table)
		$table = $_POST["table"];
	$html = "";
	$data = $connect->getAll("SELECT id, name FROM ".$table);
	foreach($data as $row){
		$id = $row["id"];
		$name = $row["name"];
		$html.= "<label><input type='checkbox' value='".$id."' class='".$table."'> ".$name."</label><br />";
	}
	return $html;
}

function find_similar_turist($connect){
	$pole = $_POST["pole"];
	$text = $_POST["text"];
	if(!$text OR !$pole)
		return FALSE;
	if($connect->getOne("SELECT id FROM klient WHERE ".$pole."=?s", $text))
		return 1;
	return FALSE;
}

function show_similar_turist($connect){
	$pole = $_POST["pole"];
	$text = $_POST["text"];
	if(!$text OR !$pole)
		return FALSE;
	$data = $connect->getAll("SELECT id, surname, name, otch, email, telephone FROM klient WHERE ".$pole."=?s", $text);
	if($data){
		ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Похожие туристы</h4>
			</div>
			<div class="form-horizontal list-group">
				<?php foreach($data as $turist){ ?>
				<div class="list-group-item list-hover-item" onclick="select_klient('<?php echo $turist['id']; ?>')">
					<div class="form-group form-group-margin">
						<div class="col-sm-6 label-text">
							<?php echo $turist["surname"]." ".$turist["name"]." ".$turist["otch"]; ?>
						</div>
						<div class="col-sm-3 label-text">
							<?php echo $turist["email"]; ?>
						</div>
						<div class="col-sm-3 label-text">
							<?php echo $turist["telephone"]; ?>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php
		$html = ob_get_clean();
		return $html;
	}
	return FALSE;
}

function add_new_client($connect){
	global $name_user;
	ob_start();
?>
<div class="form-horizontal">
	<div class="form-group">
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading"><i class="fa fa-user-plus"></i> Новый турист</div>
				<div class="panel-body">
					<div class="form-group">
						<div class="col-sm-10">
							<input type="text" class="form-control" id="surname" placeholder="Фамилия" onblur="find_similar_turist('surname', 'surname', '1')">
						</div>
						<div class="col-sm-2 mark-surname"></div>
					</div>
					<div class="form-group">
						<div class="col-sm-10">
							<input type="text" class="form-control" id="name" placeholder="Имя" onblur="verification_input_data('name', '1')">
						</div>
						<div class="col-sm-2 mark-name"></div>
					</div>
					<div class="form-group">
						<div class="col-sm-10">
							<input type="text" class="form-control" id="otch" placeholder="Отчество">
						</div>
						<div class="col-sm-2 mark-otch"></div>
					</div>
					<div class="form-group">
						<div class="col-sm-10">
							<input type="text" class="form-control datepicker" id="date" placeholder="Дата рождения" />
						</div>
						<div class="col-sm-2 mark-date"></div>
					</div>
					<div class="form-group">
						<div class="col-sm-10">
							<hr />
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-10">
							<input type="text" class="form-control" id="email" placeholder="Email" onblur="find_similar_turist('email', 'email')">
						</div>
						<div class="col-sm-2 mark-email"></div>
					</div>
					<div class="form-group">
						<div class="col-sm-10">
							<input type="text" class="form-control" id="telephone" placeholder="Телефон" onblur="find_similar_turist('telephone', 'telephone')">
						</div>
						<div class="col-sm-2 mark-telephone"></div>
					</div>
					<div class="form-group">
						<div class="col-sm-10">
							<input type="text" class="form-control" id="address" placeholder="Адрес">
						</div>
						<div class="col-sm-2 mark-address"></div>
					</div>
					<div class="form-group">
						<div class="col-sm-10">
							<hr />
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-10">
							<input type="text" class="form-control" id="passport" placeholder="Паспорт" onKeyPress="passport_space()" />
						</div>
						<div class="col-sm-2 mark-passport"></div>
					</div>
					<div class="form-group">
						<div class="col-sm-10">
							<input type="text" class="form-control" id="output" placeholder="Кем выдан" />
						</div>
						<div class="col-sm-2 mark-output"></div>
					</div>
					<div class="form-group form-group-margin">
						<div class="col-sm-10">
							<input type="text" class="form-control datepicker" id="date-pass" placeholder="Дата выдачи" />
						</div>
						<div class="col-sm-2 mark-date_pass"></div>
					</div>
				</div>
				<div class="panel-footer text-right">
					<button type="button" class="btn btn-primary btn-sm" onClick="add_new_reminder('klient')">Создать напоминание</button>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-horizontal new-reckoning panel panel-default">
				<div class="panel-heading"><i class="fa fa-file-text-o"></i> Новая заявка</div>
				<div class="panel-body">
					<div class="form-group">
						<label class="col-sm-3 control-label">Заезд</label>
						<div class="col-sm-5" style="padding-right: 0px;">
							<input type="text" class="form-control datepicker" id="arrival" onChange="verification_input_data('date_z', '1')">
						</div>
						<div class="col-sm-2">
							<input type="text" class="form-control" placeholder="Дней" id="days" onKeyPress="validate_input()" onBlur="verification_input_data('days', '1'); view_date_out();">
						</div>
						<div class="col-sm-2 mark-date_z mark-days"></div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Считаем дни</label>
						<div class="col-sm-7">
							<div class="well well-sm" id="add_one_day">&nbsp;</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Выезд</label>
						<div class="col-sm-7">
							<div class="well well-sm" id="view_date_v">&nbsp;</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Объект</label>
						<div class="col-sm-7" id="object_name" name="new-reck">
							<input type="text" class="form-control" id="object" onkeyup="find_klient(event, 'object', 'object', 'use_object')" onBlur="verification_input_data('object', '1');" name="">
						</div>
						<div class="col-sm-2 mark-object"></div>
					</div>
					<div class="form-group tour-operator-html" style="display: none">
						<label class="col-sm-3 control-label">Туроператор</label>
						<div class="col-sm-7 html">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Отдыхающих</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" value="1" id="number_turist" onKeyPress="validate_input()">
						</div>
						<div class="col-sm-2 mark-number_turist"></div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Менеджер</label>
						<div class="col-sm-7">
							<div class="well well-sm" id="manager"><?php echo $name_user; ?></div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Вознаграждение</label>
						<div class="col-sm-7">
							<?php echo get_reward_select(); ?>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Скидка (%)</label>
						<div class="col-sm-7">
							<?php echo get_select_discount($connect); ?>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Номер</label>
						<div class="col-sm-7" id="klient_room">
							<div class="well well-sm">&nbsp;</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Цена</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="sum" onKeyPress="validate_sum()" onBlur="verification_input_data('sum', '1');">
						</div>
						<div class="col-sm-3">
							<select class="form-control" id="type" onchange="change_label_number()">
								<option value="1">за чел/сутки</option>
								<option value="2">за номер (дом)</option>
								<option value="3">за заезд</option>
							</select>
						</div>
						<div class="col-sm-2 mark-sum"></div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label" id="label_number">Кол-во<br /><strong>отдыхающих</strong></label>
						<div class="col-sm-7">
							<input type="text" class="form-control" id="number" value="1" onKeyPress="validate_input()" onBlur="verification_input_data('number', '1');">
						</div>
						<div class="col-sm-2 mark-number"></div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label" id="label_number">Примечание</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" id="note">
						</div>
						<div class="col-sm-2 mark-note"></div>
					</div>
				</div>
				<div class="panel-footer text-right">
					<button type="button" class="btn btn-success btn-sm" onClick="save_all()"><i class="fa fa-check-circle"></i> Сохранить</button>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function edit_klient($connect){
	$id = $_POST["id"];
	$array = $connect->getRow("SELECT surname, name, otch, sex, date, address, email, passport, output, date_pas, note, telephone, icq, vk, fb, skype, mail, od_cl, tw, service_note FROM klient WHERE id=?i", $id);
	ob_start();
?>
<div class="form-horizontal">
	<div class="form-group">
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading"><i class="fa fa-pencil"></i> Редактирование туриста</div>
				<div class="panel-body">
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="surname" placeholder="Фамилия" value="<?php echo $array['surname']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="name" placeholder="Имя" value="<?php echo $array['name']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="otch" placeholder="Отчество" value="<?php echo $array['otch']; ?>">
						</div>
						<div class="col-sm-2 mark-otch"></div>
					</div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <select class="form-control" id="sex">
                                <option value="-1"<?php if(is_null($array['sex'])) echo ' selected';?>>Укажите пол</option>
                                <option value="0"<?php if(!is_null($array['sex']) && $array['sex'] == 0) echo ' selected';?>>Мужской</option>
                                <option value="1"<?php if($array['sex'] == 1) echo ' selected';?>>Женский</option>
                            </select>
                        </div>
                    </div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control datepicker" id="date" value="<?php echo $array['date']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<hr />
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="email" placeholder="Email" value="<?php echo $array['email']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="telephone" placeholder="Телефон" value="<?php echo $array['telephone']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="address" placeholder="Адрес" value="<?php echo $array['address']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<hr />
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="passport" placeholder="Паспорт" onKeyPress="passport_space()" value="<?php echo $array['passport']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="output" placeholder="Кем выдан" value="<?php echo $array['output']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control datepicker" id="date_pas" value="<?php echo $array['date_pas']; ?>">
						</div>
					</div>
				</div>
				<div class="panel-footer">
					<div class="form-group form-group-margin">
						<div class="col-sm-12" style="text-align: right;">
							<button type="button" class="btn btn-success btn-sm" onclick="update_klient('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
							<button type="button" class="btn btn-danger btn-sm" onclick="select_klient('<?php echo $id; ?>')"><i class="fa fa-times-circle"></i> Отмена</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-horizontal panel panel-default edit-turist">
				<div class="panel-body">
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="ICQ" placeholder="ICQ" value="<?php echo $array['icq']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="facebook" placeholder="facebook" value="<?php echo $array['fb']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="vk" placeholder="Вконтакте" value="<?php echo $array['vk']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="od_cl" placeholder="Одноклассники" value="<?php echo $array['od_cl']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="twitter" placeholder="Twitter" value="<?php echo $array['tw']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="skype" placeholder="Skype" value="<?php echo $array['skype']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<input type="text" class="form-control" id="mail" placeholder="Мой мир" value="<?php echo $array['mail']; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<textarea class="form-control" id="service-note" placeholder="Переплаты"><?php echo $array['service_note']; ?></textarea>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<textarea class="form-control" id="note_k" placeholder="Примечание"><?php echo $array['note']; ?></textarea>
						</div>
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

function update_klient($connect){
	$note_update = "";
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT surname, name, otch, sex, telephone, email, address FROM klient WHERE id=?i", $id);
	$surname = $_POST["surname"];
	$name = $_POST["name"];
	$otch = $_POST["otch"];
	$sex = (int)$_POST["sex"];
	$email = $_POST["email"];
	$passport = $_POST["passport"];
	$passport = str_replace(" ", "", $passport);
	$passport = (int)$passport;
	if($passport == 0)
		$passport = "";
	$output = $_POST["output"];
	if(empty($output))
	    $output = NULL;

	$date_pas = $_POST["date_pas"];
	if(empty($date_pas))
	    $date_pas = NULL;

	$date = $_POST["date"];
	if(empty($date))
	    $date = NULL;

	$address = $_POST["address"];
	if(empty($address))
	    $address = NULL;

	$telephone = $_POST["telephone"];
	$note_k = $_POST["note_k"];
	$skype = $_POST["skype"];
	$icq = $_POST["icq"];
	$facebook = $_POST["facebook"];
	$od_cl = $_POST["od_cl"];
	$twitter = $_POST["twitter"];
	$mail = $_POST["mail"];
	$vk = $_POST["vk"];
	$service_note = $_POST["service_note"];
	$connect->query("UPDATE klient SET surname=?s, name=?s, otch=?s, sex=?i, date=?s, telephone=?s, address=?s, passport=?s, output=?s, date_pas=?s, note=?s, mail=?s, email=?s, skype=?s, icq=?s, fb=?s, od_cl=?s, tw=?s, vk=?s, service_note=?s WHERE id=?i", $surname, $name, $otch, $sex, $date, $telephone, $address, $passport, $output, $date_pas, $note_k, $mail, $email, $skype, $icq, $facebook, $od_cl, $twitter, $vk, $service_note, $id);
	if($row["telephone"] != $telephone)
		$note_update.= " Изменен телефон «".$row["telephone"]."» ---> «".$telephone."»; ";
	if($row["email"] != $email)
		$note_update.= " Изменен email «".$row["email"]."» ---> «".$email."»; ";
	if($row["surname"] != $surname)
		$note_update.= " Изменена фамилия «".$row["surname"]."» ---> «".$surname."»; ";
	if($row["name"] != $name)
		$note_update.= " Изменено имя «".$row["name"]."» ---> «".$name."»; ";
	if($row["otch"] != $otch)
		$note_update.= " Изменено отчество «".$row["otch"]."» ---> «".$otch."»; ";
	if(is_null($row['sex']) || (int)$row["sex"] !== $sex) {
      if(is_null($row['sex']))
          $sex_string_old = "не указан";
      elseif ($row['sex'] == 0)
          $sex_string_old = "мужской";
      elseif ($row['sex'] == 1)
          $sex_string_old = "женский";

      if($sex == 0)
          $sex_string_new = "мужской";
      else
          $sex_string_new = "женский";

      $note_update.= " Изменен пол «".$sex_string_old."» ---> «".$sex_string_new."»;";
    }
	if($note_update)
		save_client_to_history($connect, $id, $note_update);
}

function write_body($connect){
	global $id_rights, $session_login, $month_pad, $array_week, $source_array;
	if(!$session_login)
		return FALSE;
	$row = $connect->getRow("SELECT name, photo FROM users WHERE id=?i", $session_login);
	$user = $row["name"];
	if($row["photo"])
		$photo = "data:image/jpg;base64,".$row["photo"];
	else
		$photo = "images/NoPicture.jpg";
	ob_start();
?>
	<div class="wrapper">
		<div class="main-header">
			<span>
				<input type="text" class="form-control input-sm number-reckoning" style="width: 150px; margin: 7px; display: inline" placeholder="Поиск по заявке" onKeyPress="if(event.keyCode == 13) select_by_number_reckoning()" />
			</span>
			<div class="pull-right">
				<ul class="nav navbar-nav">
					<li class="messages-menu chat-menu" title="Новые сообщения" onclick="open_my_chat()">
						<a><i class="fa fa-envelope-o"></i></a>
					</li>
					<li class="messages-menu btn-notification" title="Уведомления">
						<a><i class="fa fa-bell-o"></i></a>
					</li>
					<li class="messages-menu btn-setting" title="Настройки">
						<a><i class="fa fa-cogs"></i></a>
					</li>
					<?php if($id_rights == 5){ ?>
					<li class="messages-menu" title="Инструкция" onclick="show_manual()">
						<a><i class="fa fa-info-circle"></i></a>
					</li>
					<?php } ?>
					<li class="messages-menu">
						<a>
							<img src="<?php echo $photo; ?>" class="chat-avatar-small" />
							<?php echo $user; ?>
						</a>
					</li>
					<li class="messages-menu" onclick="login_exit()">
						<a><i class="fa fa-sign-out"></i></a>
					</li>
				</ul>
			</div>
		</div>

		<div class="body-CRM">
			<div class="menu-sidebar">

				<div class="user-panel">
					<img src="<?php echo $photo; ?>" class="chat-avatar" />
					<p><?php echo $user; ?></p>
					<span class="status-chat"></span>
				</div>

				<ul class="nav nav-pills nav-stacked head-menu">
					<li onclick="head_page()" id="reckoning-menu"><a>Заявки</a></li>
					<li onclick="show_call_back_menu()" id="call-back-menu"><a>Заказы звонка</a></li>
					<li id="new_klient_menu" onclick="add_klient()"><a>Клиент</a></li>
					<li id="agency_menu" onclick="agency()"><a>Агентства</a></li>
					<li id="touroperator_menu" onclick="touroperator()"><a>Туроператоры</a></li>
					<li id="obj_menu" onclick="objects()"><a>Объекты</a></li>
					<li id="reminder_menu" onclick="my_reminder()"><a>Напоминания</a></li>
			<?php if($id_rights > 3 || $session_login == 21){ ?>
					<li id="report_menu" onclick="show_reports()"><a>Отчеты</a></li>
			<?php }else{ ?>
					<li id="filter_menu" onclick="show_filter()"><a>Поиск</a></li>
			<?php } ?>
			<?php if($id_rights > 3){ ?>
					<li class="manager-menu"><a onclick="$('.menu-manager').toggle()">Менеджеры</a>
						<ul class="nav nav-pills nav-stacked second-level-menu menu-manager" style="display: none">
							<li onclick="see_managers()" id="plan-manager"><a><i class="fa fa-rub"></i> План</a></li>
							<li onclick="show_chat_users()" id="chat-manager"><a><i class="fa fa-weixin"></i> Чат</a></li>
						</ul>
					</li>
			<?php } ?>
					<li onclick="show_certificate()" id="certificate_menu"><a>Сертификаты</a></li>
					<li class="question-menu"><a onclick="$('.menu-question').toggle()">Вопросы</a>
						<ul class="nav nav-pills nav-stacked second-level-menu menu-question" style="display: none">
							<li onclick="show_question_client()" id="question-turist" class="question-turist"><a><i class="fa fa-circle-o"></i> Турист</a></li>
							<li onclick="show_question_agency()" id="question-agency" class="question-agency"><a><i class="fa fa-circle-o"></i> Агентство</a></li>
							<li onclick="show_question_object()" id="question-object" class="question-object"><a><i class="fa fa-circle-o"></i> Объект</a></li>
						</ul>
					</li>
					<li><a onclick="$('.menu-profile').toggle()">Профиль</a>
						<ul class="nav nav-pills nav-stacked second-level-menu menu-profile" style="display: none">
							<?php if($id_rights <= 3){ ?>
							<li onclick="show_profit()" id="commission_menu"><a><i class="fa fa-rub"></i> Мой доход</a></li>
							<?php } ?>
							<li onclick="show_change_password()" id="my-password"><a><i class="fa fa-key"></i> Сменить пароль</a></li>
							<li onclick="show_my_chat_log()" id="my-chat-log"><a><i class="fa fa-weixin"></i> Чат</a></li>
						</ul>
					</li>
			<?php if($id_rights > 4){ ?>
					<li id="all-admin-menu"><a onclick="$('.menu-admin').toggle()">Админ</a>
						<ul class="nav nav-pills nav-stacked second-level-menu menu-admin" style="display: none">
							<li onclick="object()" id="price_menu"><a><i class="fa fa-home"></i> Объекты</a></li>
							<li onclick="show_rating_menu()" id="rating_menu"><a><i class="fa fa-comments-o"></i> Отзывы</a></li>
							<li id="office_menu" onclick="see_office()"><a><i class="fa fa-users"></i> Офис</a></li>
							<li id="users_menu" onclick="see_users()"><a><i class="fa fa-user"></i> Пользователь</a></li>
							<li id="group-menu" onclick="see_groups()"><a><i class="fa fa-users"></i> Группы</a></li>
							<li id="sync-reboot-menu" onclick="restart_sync()"><a><i class="fa fa-refresh"></i> Перезапуск синхронизации</a></li>
							<li><hr /></li>
							<li id="profile_open" onclick="profile()"><a><i class="fa fa-heartbeat"></i> Профиль лечения</a></li>
							<li id="methods_open" onclick="methods()"><a><i class="fa fa-user-md"></i> Метод лечения</a></li>
							<li id="infa_open" onclick="infrastructure()"><a><i class="fa fa-building-o"></i> Инфраструктура</a></li>
							<li id="comfort_open" onclick="comfort()"><a><i class="fa fa-bed"></i> Удобства</a></li>
							<li id="service_open" onclick="service()"><a><i class="fa fa-cutlery"></i> Услуги</a></li>
							<li id="sights-open" onclick="sights()"><a><i class="fa fa-university"></i> Места</a></li>
							<li><hr /></li>
							<li id="news-menu1" onclick="show_news()"><a><i class="fa fa-newspaper-o"></i> Новости</a></li>
							<li id="image_menu" onclick="save_image_to_server()"><a><i class="fa fa-cloud-upload"></i> Обновить фото</a></li>
							<li onclick="show_admin()" id="admin_menu"><a><i class="fa fa-search"></i> Поиск</a></li>
						</ul>
					</li>

					<li class="menu-object-cabinet"><a onclick="$('.show-menu-object-cabinet').toggle()">Кабинет объекта</a>
						<ul class="nav nav-pills nav-stacked second-level-menu show-menu-object-cabinet" style="display: none">
							<li onclick="show_cabinet_object()" id="account-object"><a><i class="fa fa-flag-o"></i> Аккаунты</a></li>
							<li onclick="show_request_object()" id="new-request-object"><a><i class="fa fa-plus-circle"></i> Новые</a></li>
							<li onclick="check_changes_cabinet_object()" id="check-object-menu"><a><i class="fa fa-check-circle"></i> Изменения</a></li>
							<li onclick="show_object_qouta_admin()" id="admin-object-qouta"><a><i class="fa fa-calendar"></i> Квота мест</a></li>
							<li onclick="select_object_profkurort()" id="profkurort-menu"><a><i class="fa fa-product-hunt"></i> Профкурорт</a></li>
						</ul>
					</li>
			<?php } ?>
				</ul>

			</div>

			<div class="content-wrapper" id="body"></div>
		</div>

	</div>

<?php
	$data["html"] = ob_get_clean();
	$row = $connect->getRow("SELECT name, photo FROM users WHERE id=?i", $session_login);
	$data["name"] = $row["name"];
	if($row["photo"])
		$data["photo"] = "data:image/jpg;base64,".$row["photo"];
	else
		$data["photo"] = "images/NoPicture.jpg";
	$data["right"] = $id_rights;
	$data["manager"] = select_array_table($connect, "users", " class=1 ");
	$data["reward"] = select_array_reward();
	$data["status-bid"] = select_array_table($connect, "status");
	$conf = connect_config();
	$data["source"] = $conf->source_array;
	setcookie("writing", 1);
	return json_encode($data);
}

function select_regions($connect){
	return get_select_table($connect, "region", "active=0 AND id_country=1", "", "");
}

function select_managers($connect){
	return get_managers($connect);
}

function show_admin_button_reckoning($connect){
	global $id_rights;
	if($id_rights != 5)
		return FALSE;
	$id = $_POST["id"];
?>
	<span onclick="show_form_outweigh_reckoning('<?php echo $id; ?>')">Перевесить на агентство</span>
	<?php if($connect->getOne("SELECT id FROM bonus WHERE schet=?i AND sum<0", $id)){ ?>
	<span onclick="delete_bonus_form_reckoning('<?php echo $id; ?>')">Удалить бонусы из заявки</span>
	<?php } ?>
<?php
}

?>
