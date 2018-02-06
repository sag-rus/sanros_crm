<?php

function show_head_page_agency($connect){
	$first = array();
	$func = '';
	$data = $connect->getAll("SELECT id, short_name FROM agency ORDER BY short_name");
	foreach($data as $row){
		$short_name = $row["short_name"];
		$id = $row["id"];
		$first[$id] = mb_strtoupper(mb_substr($short_name, 0, 1, "UTF-8"), "UTF-8");
	}
	$first = array_unique($first);
	$first_symbol = "";
	foreach($first as $symbol){
		$symbol_up = str_replace(" ", "&nbsp", strToUpper($symbol));
		$first_symbol.= "<li onclick='find_agency(event,\"".$symbol."\")'><a>".$symbol_up."</a></li>";
	}
	ob_start();
	$count_all = $connect->getOne("SELECT COUNT(*) FROM agency");
	$count_without_log = $connect->getOne("SELECT COUNT(*) FROM agency WHERE login IS NULL");
	$count_with_log = $connect->getOne("SELECT COUNT(*) FROM agency WHERE login IS NOT NULL");
?>

<ul class="nav nav-tabs agency-status-filter">
	<li class="1-bid-page active" onclick="find_agency(event)" data-login-status="0">
		<a>Все<span class="badge"><?=$count_all;?></span></a>
	</li>
	<li class="1-bid-page" onclick="find_agency(event)" data-login-status="1">
		<a>Логин не выдан<span class="badge"><?=$count_without_log;?></span></a>
	</li>
	<li class="1-bid-page" onclick="find_agency(event)" data-login-status="2">
		<a>Логин выдан<span class="badge"><?=$count_with_log;?></span></a>
	</li>
</ul>
<script type="text/javascript">
	find_agency(null);
</script>
<div class="form-horizontal">
	<div class="form-group">
		<div class="col-sm-4">
			<div class="input-group">
				<span class="input-group-addon"><i class="fa fa-search"></i></span>
				<input type="text" id="name_agency" class="form-control" placeholder="Название агентства" onkeyup="find_klient(event, 'name_agency', 'agency')" placeholder="Введите название агентства" />
			</div>
		</div>
		<div class="col-sm-2">
			<input type="text" class="form-control number-contract-agency" placeholder="Номер договора" />
		</div>
		<div class="col-sm-6">
			<button type="button" class="btn btn-info btn-lt" onclick="find_agency(event)"><i class="fa fa-search"></i> Найти</button>
			<button type="button" class="btn btn-default btn-lt" onclick="add_turist_agency()"><i class="fa fa-plus-circle"></i> Новое агентство</button>
			<button type="button" class="btn btn-default btn-lt" onclick="view_all_questionary()"><i class="fa fa-file-text-o"></i> Анкета</button>
		</div>
	</div>
</div>
<ul class="pagination pagination-sm">
	<?php echo $first_symbol; ?>
</ul>
<div>Всего агенств: <?=count($data)?></div>
<br />
<div id="div_result"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function agency_select($connect,&$post) {
	$stroka = $_POST["stroka"];
	$number = $_POST["number"];
	$login_status = 0;

	$page = 0;

	$agency_limit = 20;


	if(isset($_POST['login_status']) && in_array($_POST['login_status'], array(1,2)))
		$login_status = (int)$_POST['login_status'];

	if(isset($_POST['page']) && is_numeric($_POST['page']) && $_POST['page'] >= 0)
		$page = (int)$_POST['page'];
	
	$limit_str = "";

	if($page > 0) {
		$limit_str = "LIMIT ".($page*$agency_limit).",".$agency_limit;
	}
	else {
		$limit_str = "LIMIT ".$agency_limit;
	}


	$add_number = "";
	$count_all = 0; 


	if($number){
		$data = $connect->getAll("SELECT agency FROM ag_contract WHERE number=?i", $number);
		foreach($data as $row){
			if($add_number)
				$add_number.= " OR ";
			$add_number.= " id=".$row["agency"];
		}
		if($add_number)
			$add_number = " AND ( ".$add_number." ) ";
	}


	if(mb_strlen($stroka) > 0) {
		if($login_status === 0)  {
			$count_all = $connect->getOne("SELECT COUNT(*) FROM agency WHERE short_name LIKE ?s ".$add_number." ORDER BY short_name ", "%".$stroka."%");
			$data = $connect->getAll("SELECT id, name, short_name, login, active, address, questionary FROM agency WHERE short_name LIKE ?s ".$add_number." ORDER BY short_name ".$limit_str, "%".$stroka."%");
		}
		elseif($login_status === 1) {
			$count_all = $connect->getOne("SELECT COUNT(*) FROM agency WHERE short_name LIKE ?s ".$add_number." AND login IS NULL ORDER BY short_name", "%".$stroka."%");
			$data = $connect->getAll("SELECT id, name, short_name, login, active, address, questionary FROM agency WHERE short_name LIKE ?s ".$add_number." AND login IS NULL ORDER BY short_name ".$limit_str, "%".$stroka."%");
		}
		elseif ($login_status === 2) {
			$count_all = $connect->getOne("SELECT COUNT(*) FROM agency WHERE short_name LIKE ?s ".$add_number." AND login IS NOT NULL ORDER BY short_name", "%".$stroka."%");
			$data = $connect->getAll("SELECT id, name, short_name, login, active, address, questionary FROM agency WHERE short_name LIKE ?s ".$add_number." AND login IS NOT NULL ORDER BY short_name ".$limit_str, "%".$stroka."%");
		}
	}
	else {
		if($login_status === 0) {
			$count_all = $connect->getOne("SELECT COUNT(*) FROM agency WHERE 1 ".$add_number." ORDER BY short_name");
			$data = $connect->getAll("SELECT id, name, short_name, login, active, address, questionary FROM agency WHERE 1 ".$add_number." ORDER BY short_name ".$limit_str);
		}
		elseif($login_status === 1) {
			$count_all = $connect->getOne("SELECT COUNT(*) FROM agency WHERE 1 ".$add_number." AND login IS NULL ORDER BY short_name");
			$data = $connect->getAll("SELECT id, name, short_name, login, active, address, questionary FROM agency WHERE 1 ".$add_number." AND login IS NULL ORDER BY short_name ".$limit_str);
		}
		elseif ($login_status === 2) {
			$count_all = $connect->getOne("SELECT COUNT(*) FROM agency WHERE 1 ".$add_number." AND login IS NOT NULL ORDER BY short_name");
			$data = $connect->getAll("SELECT id, name, short_name, login, active, address, questionary FROM agency WHERE 1 ".$add_number." AND login IS NOT NULL ORDER BY short_name ".$limit_str);
		}
	}

	return array('data' => $data, 'page' => $page, 'count_all' => $count_all, 'limit' => $agency_limit, 'pages_count' => ceil($count_all/$agency_limit));
}

function find_agency($connect){
	global $array_color_status;
	$html = "";
	//$stroka = $_POST["stroka"];
	//$number = $_POST["number"];
	$dataAssoc  = agency_select($connect,$_POST);
	$data = $dataAssoc['data'];


	$pagination_block = '<div class="paginaton-block">';
	if($dataAssoc['page'] === 0) {
		$pagination_block .= '<button class="btn" disabled data-page-number="'.($dataAssoc['page']-1).'" onclick="find_agency(event)">Назад</button>';
		$pagination_block .= '<button class="btn" disabled data-page-number="0" onclick="find_agency(event)">1</button>';
	}
	else {
		$pagination_block .= '<button class="btn" data-page-number="'.($dataAssoc['page']-1).'" onclick="find_agency(event)">Назад</button>';
		$pagination_block .= '<button class="btn" data-page-number="0" onclick="find_agency(event)">1</button>';
	}

	if($dataAssoc['page'] > 0 && $dataAssoc['page']-2 > 1)
		$pagination_block .= ' ... ';

	$locaPageAr = array();
	for($i = $dataAssoc['page']-1, $j = 2; $i >= 0 && $j > 0; $i--) {
		if($i != 0) {
			$j--;
			$locaPageAr[] = $i;
		}
	}

	for($k = count($locaPageAr)-1; $k >= 0; $k--) {
		if($locaPageAr[$k] == $dataAssoc['page']) {
			$pagination_block .= '<button class="btn" disabled data-page-number="'.$locaPageAr[$k].'" onclick="find_agency(event)">'.($locaPageAr[$k]+1).'</button>';
		}
		else {
			$pagination_block .= '<button class="btn" data-page-number="'.$locaPageAr[$k].'" onclick="find_agency(event)">'.($locaPageAr[$k]+1).'</button>';
		}
	}

	for($i = $dataAssoc['page'], $j = 2; $i >=0 && $i < $dataAssoc['pages_count']-1 && $j > 0; $i++) {
		if($i != 0) {
			if($i != $dataAssoc['page']) {
				$j--;
			}

			if($i == $dataAssoc['page']) {
				$pagination_block .= '<button class="btn" disabled data-page-number="'.$i.'" onclick="find_agency(event)">'.($i+1).'</button>';
			}
			else {
				$pagination_block .= '<button class="btn" data-page-number="'.$i.'" onclick="find_agency(event)">'.($i+1).'</button>';
			}
		}
	}

	if($dataAssoc['page'] < $dataAssoc['pages_count']-1 && $i < $dataAssoc['pages_count']-2)
		$pagination_block .= ' ... ';

	if($dataAssoc['page'] == $dataAssoc['pages_count']-1) {
		if($dataAssoc['pages_count'] != 1)
			$pagination_block .= '<button class="btn" disabled data-page-number="'.($dataAssoc['pages_count']-1).'" onclick="find_agency(event)">'.($dataAssoc['pages_count']).'</button>';
		
		$pagination_block .= '<button class="btn" disabled data-page-number="'.($dataAssoc['page']+1).'" onclick="find_agency(event)">Вперед</button>';
	}
	else {
		if($dataAssoc['pages_count'] != 1)
			$pagination_block .= '<button class="btn" data-page-number="'.($dataAssoc['pages_count']-1).'" onclick="find_agency(event)">'.($dataAssoc['pages_count']).'</button>';
		$pagination_block .= '<button class="btn" data-page-number="'.($dataAssoc['page']+1).'" onclick="find_agency(event)">Вперед</button>';
	}

	$pagination_block .= '</div>';
	$dataCount = count($dataAssoc['data']);
?>
<div class="form-horizontal">
<?php
	if($dataCount > 0) {
?>
	<div class="pagination-info">Показаны результаты с <?=($dataAssoc['page']*$dataAssoc['limit']+1);?> по <?=min((($dataAssoc['page']+1)*$dataAssoc['limit']+1),$dataAssoc['count_all']);?> из <?=$dataAssoc['count_all'];?></div>
	<?=$pagination_block;?>
<?php	
	}
?>
<?php
	foreach($data as $row){
		$id = $row["id"];
		$active = $row["active"];
		$quest = json_decode($row["questionary"], TRUE);
		$class = "";
		if(!$row["login"])
			$class = " alert-info ";
		if($active == 1)
			$class = " alert-danger ";
		ob_start();
	?>
		<tr class="<?php echo $class; ?>">
			<td width="25%" onclick="select_agency('<?php echo $id; ?>')">
				<?php echo $row["short_name"]; ?>
			</td>
			<td width="25%" onclick="select_agency('<?php echo $id; ?>')">
				<?php echo $row["name"]; ?>
			</td>
			<td width="40%">
				<?php echo $row["address"]; ?>
			</td>
			<td width="10%" class="center">
			<?php if($quest["check-cabinet"] != "" AND $quest["check-find"] != "" AND $quest["check-place"] != ""){ ?>
				<button type="button" class="btn btn-success btn-sm" onclick="show_questionary_agency('<?php echo $id; ?>')"><i class="fa fa-question-circle"></i></button>
			<?php }else{ ?>
				<button type="button" class="btn btn-danger btn-sm" onclick="show_questionary_agency('<?php echo $id; ?>')"><i class="fa fa-question-circle"></i></button>
			<?php }?>
			</td>
		</tr>
	<?php
		$html.= ob_get_clean();
	}
?>
	<?php if(!count($data)){ ?>
		<div class="alert alert-info"><i class="fa fa-info-circle"></i> Ничего не найдено</div>
	<?php }else{ ?>
		<table class="table table-hover">
		<tr>
			<th>Агентство</th>
			<th>Полное название</th>
			<th>Адрес</th>
			<th>Анкета</th>
		</tr>
			<?php echo $html; ?>
		</table>
	<?php } ?>

	<?php
	if($dataCount > 0) {
	?>
		<div class="pagination-info">Показаны результаты с <?=($dataAssoc['page']*$dataAssoc['limit']+1);?> по <?=min((($dataAssoc['page']+1)*$dataAssoc['limit']+1),$dataAssoc['count_all']);?> из <?=$dataAssoc['count_all'];?></div>
		<?=$pagination_block;?>
		<?php	
		}
	?>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function add_new_agency(){
	ob_start();
?>
<div class="form-horizontal new-agency panel panel-default">
	<div class="panel-heading"><i class="fa fa-plus-circle"></i> Новое агентство</div>
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-3 control-label">Сокращенное название</label>
			<div class="col-sm-9">
				<input type="text" id="short_name" class="form-control" onkeyup="find_klient(event, 'short_name', 'agency')" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Полное название</label>
			<div class="col-sm-9">
				<input type="text" id="name" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Email</label>
			<div class="col-sm-9">
				<input type="text" id="email" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Телефон</label>
			<div class="col-sm-9">
				<input type="text" id="telephone" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Адрес</label>
			<div class="col-sm-9">
				<input type="text" id="address" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Юридический адрес</label>
			<div class="col-sm-9">
				<input type="text" id="legal_address" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Факс</label>
			<div class="col-sm-9">
				<input type="text" id="fax" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">ICQ</label>
			<div class="col-sm-9">
				<input type="text" id="icq" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Skype</label>
			<div class="col-sm-9">
				<input type="text" id="skype" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Сайт</label>
			<div class="col-sm-9">
				<input type="text" id="website" class="form-control" />
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-3 control-label">Примечание</label>
			<div class="col-sm-9">
				<textarea id="note_a" class="form-control"></textarea>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="form-group form-group-margin">
			<div class="col-sm-offset-3 col-sm-9">
				<button class="btn btn-success btn-sm" onclick="save_all_agency()"><i class="fa fa-check-circle"></i> Сохранить</button>
				<button class="btn btn-danger btn-sm" onclick="agency()"><i class="fa fa-times-circle"></i> Отмена</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_agency($connect){
	global $directory;
	include_once($directory."/core/lib/mail.php");
	$name = str_replace("plus", "+", $_POST["name"]);
	$short_name = str_replace ('"', "", $_POST["short_name"]);
	$telephone = $_POST["telephone"];
	$email = $_POST["email"];
	$fax = $_POST["fax"];
	$icq = $_POST["icq"];
	$skype = $_POST["skype"];
	$note = $_POST["note_a"];
	$address = $_POST["address"];
	$legal_address = $_POST["legal_address"];
	$website = $_POST["website"];
	$module = gen_password(rand(6, 8));
	while($connect->getOne("SELECT id FROM agency WHERE module=?s", $module))
		$module = gen_password(rand(6, 8));
	$connect->query("INSERT INTO agency(name, short_name, telephone, email, fax, icq, skype, note, address, website, legal_address, module, module_email, created) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)", $name, $short_name, $telephone, $email, $fax, $icq, $skype, $note, $address, $website, $legal_address, $module, $email, gmdate("U"));
	$id = $connect->insertId();
	return $id;
}

function select_agency($connect){
	global $id_rights;
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT * FROM agency WHERE id=?i LIMIT 1", $id);
	$active = $row["active"];
	if($active == 0){
		$button = "<i class='fa fa-ellipsis-h pointer' onclick='show_but_agency(\"".$id."\")' id='agency_active'></i>";
		$cabinet = "<span class='label label-danger'><i class='fa fa-times-circle'></i> Логин не выдан</label>";
		if((string)$row['login'] !== '' && (string)$row['password'] !== '')
			$login = $row['login'];

		if($login){
			$date = $connect->getOne("SELECT date FROM session_agency WHERE login=?s", $login);
			if(!$date)
				$date = "никогда";
			$cabinet = "<span class='label label-success pointer' title='Дата последнего входа: ".$date."'><i class='fa fa-key'></i> Логин выдан</label>";
		}
	}else{
		$button = "<span class='label label-danger'>в ахиве <i class='fa fa-trash'></i></span>";
		$cabinet = "";
	}
	if($row["type_com"] == 1)
		$commis = "Обычная";
	else
		$commis = "Повышенная";
	$agency_contract = select_agency_contract($connect, $id, "all");
	if(!$agency_contract["number"])
			$contract = "<span class='label label-danger'><i class='fa fa-times'></i> Не указан</span>";
	else{
		$contract = "№".$agency_contract["number"]." до ".$agency_contract["date_cont"];
		if($id_rights > 2)
			$contract.= "&nbsp;<button type='button' class='btn btn-default btn-xs' onclick='edit_agency_contract(\"".$agency_contract["id"]."\")'><i class='fa fa-pencil'></i> изменить</button>";
		if($agency_contract["status"] == 1)
			$contract.= "&nbsp;<span class='label label-success'><i class='fa fa-check'></i> Скан получен</span>";
		elseif($agency_contract["status"] == 2)
			$contract.= "&nbsp;<span class='label label-success'><i class='fa fa-check'></i> Оригинал получен</span>";
		else
			$contract.= "&nbsp;<span class='label label-danger'><i class='fa fa-times'></i> Скан не получен</span>";
		if($agency_contract["status"] > 0 AND $id_rights > 2)
			$contract.= "&nbsp;<button type='button' class='btn btn-default btn-xs' style='float: right;' onclick='throw_off_agency_contract(\"".$agency_contract["id"]."\")'><i class='fa fa-times'></i> скан не получен</button>";
	}

	ob_start();
?>
<button type="button" class="btn btn-warning btn-xs" onclick="show_prev_page()"><i class="fa fa-angle-double-left"></i> вернуться назад</button>
<div class="form-horizontal panel panel-primary" style="width: 700px; margin-top: 10px;" id="agency" name="<?php echo $id; ?>">
	<div class="panel-heading">
		Агентство <?php echo $row["short_name"]; ?>
		<?php if($id_rights == 5){
			echo " <strong>(".$id.")</strong>";
		} ?>
		<?php echo $button; ?>
	</div>
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-2 control-label">Название</label>
			<div class="col-sm-6">
				<div class="well well-sm"><?php echo $row["name"]; ?></div>
			</div>
			<div class="col-sm-1"></div>
			<div class="col-sm-2">
				<?php echo $cabinet; ?>
			</div>
		</div>
	<?php if($row["telephone"] != ""){ ?>
		<div class="form-group">
			<label class="col-sm-2 control-label">Телефон</label>
			<div class="col-sm-4">
				<div class="well well-sm"><?php echo $row["telephone"]; ?></div>
			</div>
	<?php } ?>
	<?php if($row["fax"]){ ?>
			<label class="col-sm-2 control-label">Факс</label>
			<div class="col-sm-4">
				<div class="well well-sm"><?php echo $row["fax"]; ?></div>
			</div>
	<?php } ?>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Email</label>
			<div class="col-sm-5">
				<div class="well well-sm"><?php echo $row["email"]; ?>&nbsp;</div>
			</div>
			<label class="col-sm-2 control-label">Тип комиссии</label>
			<div class="col-sm-3">
				<div class="well well-sm"><?php echo $commis; ?></div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Аг.договор</label>
			<div class="col-sm-10">
				<div class="well well-sm"><?php echo $contract; ?></div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-12">
				<span class="label label-default pointer" onclick="$('.hide_info').toggle()">Другая информация <i class="fa fa-angle-double-down"></i></span>
			</div>
		</div>
		<div class="form-group hide_info" style="display: none">
			<label class="col-sm-2 control-label">Адрес</label>
			<div class="col-sm-10">
				<div class="well well-sm"><?php echo $row["address"]; ?></div>
			</div>
		</div>
		<div class="form-group hide_info" style="display: none">
			<label class="col-sm-2 control-label">Юрид.адрес</label>
			<div class="col-sm-10">
				<div class="well well-sm"><?php echo $row["legal_address"]; ?></div>
			</div>
		</div>
		<div class="form-group hide_info" style="display: none">
			<label class="col-sm-2 control-label">ICQ</label>
			<div class="col-sm-4">
				<div class="well well-sm"><?php echo $row["icq"]; ?>&nbsp;</div>
			</div>
			<label class="col-sm-2 control-label">Skype</label>
			<div class="col-sm-4">
				<div class="well well-sm"><?php echo $row["skype"]; ?>&nbsp;</div>
			</div>
		</div>
		<div class="form-group hide_info" style="display: none">
			<label class="col-sm-2 control-label">БИК</label>
			<div class="col-sm-4">
				<div class="well well-sm"><?php echo $row["bik"]; ?>&nbsp;</div>
			</div>
			<label class="col-sm-2 control-label">Банк</label>
			<div class="col-sm-4">
				<div class="well well-sm"><?php echo $row["bank"]; ?>&nbsp;</div>
			</div>
		</div>
		<div class="form-group hide_info" style="display: none">
			<label class="col-sm-2 control-label">К/счет</label>
			<div class="col-sm-4">
				<div class="well well-sm"><?php echo $row["ks"]; ?>&nbsp;</div>
			</div>
			<label class="col-sm-2 control-label">Р/счет</label>
			<div class="col-sm-4">
				<div class="well well-sm"><?php echo $row["rs"]; ?>&nbsp;</div>
			</div>
		</div>
		<div class="form-group hide_info" style="display: none">
			<label class="col-sm-2 control-label">ИНН</label>
			<div class="col-sm-4">
				<div class="well well-sm"><?php echo $row["inn"]; ?>&nbsp;</div>
			</div>
			<label class="col-sm-2 control-label">КПП</label>
			<div class="col-sm-4">
				<div class="well well-sm"><?php echo $row["kpp"]; ?>&nbsp;</div>
			</div>
		</div>
		<div class="form-group hide_info" style="display: none">
			<label class="col-sm-2 control-label">Сайт</label>
			<div class="col-sm-4">
				<div class="well well-sm"><?php echo $row["website"]; ?>&nbsp;</div>
			</div>
		</div>
		<div class="form-group hide_info" style="display: none">
			<label class="col-sm-2 control-label">Примечание</label>
			<div class="col-sm-10">
				<div class="well well-sm"><?php echo $row["note"]; ?>&nbsp;</div>
			</div>
		</div>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-success btn-xs" onclick="klient_schet()"><i class="fa fa-pencil-square-o"></i> Заявки</button>
		<button type="button" class="btn btn-default btn-xs" onclick="show_history_agency('<?php echo $id; ?>')"><i class="fa fa-history"></i> История</button>
	</div>
</div>
<div id="info_turist"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_menu_agency($connect){
	global $id_rights;
	$id = $_POST["id"];
	$ag_contract = select_agency_contract($connect, $id);
	$status = $ag_contract["status"];
	$number = $ag_contract["number"];
	$id_contract = $ag_contract["id"];
	$send_login = $connect->getOne("SELECT id FROM agency WHERE login!='' AND password!='' AND id=?i", $id);
	ob_start();
?>
	<span onclick="edit_agency('<?php echo $id; ?>')">Редактировать</span>
	<span onclick="new_reck('agency')">Новая заявка</span>
	<?php if(!$number AND $id_rights > 2){ ?>
		<span onclick="add_new_contract('<?php echo $id; ?>')">Ввести номер дог-ра</span>
	<?php }elseif($id_rights > 2){ ?>
		<?php if($status == 0){ ?>
			<span onclick="scan_received('<?php echo $id; ?>', '<?php echo $id_contract; ?>')">Скан получен</span>
		<?php }elseif($status == 1){ ?>
			<span onclick="original_received('<?php echo $id; ?>', '<?php echo $id_contract; ?>')">Оригинал получен</span>
		<?php } ?>
	<?php } ?>
	<span onclick="show_agency_dogovor(<?php echo $id; ?>)">Агентский договор</span>
	<?php if($id_rights > 2){ ?>
		<span onclick="edit_agency_sync_info(<?php echo $id; ?>)">Синхронизация 1С</span>
		<span onclick="agency_to_trash(<?php echo $id; ?>)">В архив</span>
	<?php } ?>
	<?php if(!$send_login AND $id_rights > 2){ ?>
		<span onclick="show_send_login_agency(<?php echo $id; ?>)">Выслать логин и пароль</span>
	<?php }elseif($send_login){ ?>
		<span class="alert-danger" onclick="show_send_login_agency(<?php echo $id; ?>)">Выслать логин и пароль</span>
	<?php } ?>
<?php
	$html = ob_get_clean();
	return $html;
}

function edit_agency($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT * FROM agency WHERE id=?i", $id);
	$select_type_com = array();
	$select_type_com[$row["type_com"]] = "SELECTED";
	$row = clear_quotes($row);
	ob_start();
?>
<div class="form-horizontal panel panel-default edit">
	<div class="panel-heading"><i class="fa fa-pencil"></i> Изменить агентство «<?php echo $row["short_name"]; ?>»</div>
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
			<label class="col-sm-3 control-label">Представитель</label>
			<div class="col-sm-9">
				<input type="text" id="present" class="form-control" value="<?php echo $row['present']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Должность</label>
			<div class="col-sm-9">
				<input type="text" id="post" class="form-control" value="<?php echo $row['post']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Документ</label>
			<div class="col-sm-9">
				<input type="text" id="doc" class="form-control" value="<?php echo $row['doc']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Тип комиссии</label>
			<div class="col-sm-9">
				<select id="type_com" class="form-control">
					<option value="1" <?php echo $select_type_com[1]; ?>>Обычная</option>
					<option value="2" <?php echo $select_type_com[2]; ?>>Повышенная</option>
				</select>
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
		<div class="form-group">
			<label class="col-sm-3 control-label">Примечание</label>
			<div class="col-sm-9">
				<textarea id="note_a" class="form-control"><?php echo $row['note']; ?></textarea>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="form-group form-group-margin">
			<div class="col-sm-offset-3 col-sm-9">
				<button class="btn btn-success btn-sm" onclick="update_agency('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
				<button class="btn btn-danger btn-sm" onclick="select_agency('<?php echo $id; ?>')"><i class="fa fa-times-circle"></i> Отмена</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_agency($connect){
	$_POST["name"] = str_replace("plus", "+", $_POST["name"]);
	$connect->query("UPDATE agency SET name=?s, short_name=?s, telephone=?s, email=?s, fax=?s, skype=?s, note=?s, address=?s, website=?s, icq=?s, legal_address=?s, ks=?s, rs=?s, bik=?s, inn=?s, kpp=?s, bank=?s, present=?s, post=?s, doc=?s, type_com=?i WHERE id=?i", $_POST["name"], $_POST["short_name"], $_POST["telephone"], $_POST["email"], $_POST["fax"], $_POST["skype"], $_POST["note"], $_POST["address"], $_POST["website"], $_POST["icq"], $_POST["legal_address"], $_POST["ks"], $_POST["rs"], $_POST["bik"], $_POST["inn"], $_POST["kpp"], $_POST["bank"], $_POST["present"], $_POST["post"], $_POST["doc"], $_POST["type_com"], $_POST["id"]);
}

function throw_off_agency_contract($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE ag_contract SET status=0 WHERE id=?i", $id);
	return $connect->getOne("SELECT agency FROM ag_contract WHERE id=?i", $id);
}

function edit_agency_contract($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT date, number FROM ag_contract WHERE id=?i", $id);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить агентский договор</h4>
			</div>
			<div class="modal-body form-horizontal edit-contract">
				<div class="form-group">
					<label class="col-sm-4 control-label">Номер договора</label>
					<div class="col-sm-8">
						<input type="text" class="form-control number-contract" value="<?php echo $row['number']; ?>" />
					</div>
				</div>
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Действует до</label>
					<div class="col-sm-8">
						<input type="text" class="form-control datepicker" id="date-contract" value="<?php echo $row['date']; ?>" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success btn-sm" onclick="update_agency_contract('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_agency_contract($connect){
	$id = $_POST["id"];
	$number = $_POST["number"];
	$date = $_POST["date"];
	$connect->query("UPDATE ag_contract SET date=?s, number=?i WHERE id=?i", $date, $number, $id);
	return $connect->getOne("SELECT agency FROM ag_contract WHERE id=?i", $id);
}

function add_new_contract_agency($connect){
	$id = $_POST["id"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить агентский договор</h4>
			</div>
			<div class="modal-body form-horizontal new-contract">
				<div class="form-group">
					<label class="col-sm-4 control-label">Номер договора</label>
					<div class="col-sm-8">
						<input type="text" class="form-control number-contract" />
					</div>
				</div>
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Действует до</label>
					<div class="col-sm-8">
						<input type="text" class="form-control datepicker date-contract" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success btn-sm" onclick="save_new_contract('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_contract_agency($connect){
	$id = $_POST["id"];
	$number = $_POST["number"];
	$date = $_POST["date"];
	$connect->query("INSERT INTO ag_contract (agency, date, number) VALUES (?i, ?s, ?i)", $id, $date, $number);
}

function scan_received($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE ag_contract SET status=1 WHERE id=?i", $id);
}

function original_received($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE ag_contract SET status=2 WHERE id=?i", $id);
}

function show_questionary_agency($connect){
	$id = $_POST["id"];
	$row = json_decode($connect->getOne("SELECT questionary FROM agency WHERE id=?i", $id), TRUE);
	$check = array();
	$check["cabinet"][$row["check-cabinet"]] = " CHECKED ";
	$check["place"][$row["check-place"]] = " CHECKED ";
	$check["find"][$row["check-find"]] = " CHECKED ";
	ob_start();
?>
<button type="button" class="btn btn-warning btn-xs" onclick="show_prev_page()"><i class="fa fa-angle-double-left"></i> вернуться назад</button>
<div class="form-horizontal panel panel-default questionary-form" style="margin-top: 10px">
	<div class="panel-heading"><i class="fa fa-question-circle"></i> Анкета агентства</div>
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-4 control-label">Пользуются кабинетом?</label>
			<div class="col-sm-8">
				<label><input type="radio" class="check-cabinet" name="cabinet" value="1" <?php echo $check["cabinet"][1]; ?> /> да</label>
				<label><input type="radio" class="check-cabinet" name="cabinet" value="0" <?php echo $check["cabinet"][0]; ?> /> нет</label>
				<label><input type="radio" class="check-cabinet" name="cabinet" value="2" <?php echo $check["cabinet"][2]; ?> /> мнение</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Все устраивает? Есть ли замечания?</label>
			<div class="col-sm-8">
				<textarea class="form-control cabinet-note"><?php echo $row["cabinet-note"]; ?></textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Нужно наличие мест в санатории?</label>
			<div class="col-sm-8">
				<label><input type="radio" class="check-place" name="place" value="1" <?php echo $check["place"][1]; ?> /> да</label>
				<label><input type="radio" class="check-place" name="place" value="0" <?php echo $check["place"][0]; ?> /> нет</label>
				<label><input type="radio" class="check-place" name="place" value="2" <?php echo $check["place"][2]; ?> /> мнение</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Пожелания для наличия мест</label>
			<div class="col-sm-8">
				<textarea class="form-control place-note"><?php echo $row["place-note"]; ?></textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Нужен модуль поиска для сайта?</label>
			<div class="col-sm-8">
				<label><input type="radio" class="check-find" name="find" value="1" <?php echo $check["find"][1]; ?> /> да</label>
				<label><input type="radio" class="check-find" name="find" value="0" <?php echo $check["find"][0]; ?> /> нет</label>
				<label><input type="radio" class="check-find" name="find" value="2" <?php echo $check["find"][2]; ?> /> мнение</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">Пожелания для модуля поиска</label>
			<div class="col-sm-8">
				<textarea class="form-control find-note"><?php echo $row["find-note"]; ?></textarea>
			</div>
		</div>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-success btn-sm" onclick="update_questionary_agency('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_questionary_agency($connect){
	global $session_login;
	$id = $_POST["id"];
	$data = json_decode($_POST["data"], TRUE);
	$data["manager"] = $session_login;
	$data["time"] = time();
	$connect->query("UPDATE agency SET questionary=?s WHERE id=?i", json_encode($data), $id);
}

function view_all_questionary($connect){
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-question-circle"></i> Заполненные анкеты агентств</div>
	<table class="table">
	<tr>
		<th>Агентство</th>
		<th>Менеджер</th>
		<th>Личный кабинет</th>
		<th>Ганант.места</th>
		<th>Модуль на сайт</th>
	</tr>
<?php
	$check = array(0 => "Нет", 1 => "Да", 2 => "Мнение");
	$data = $connect->getAll("SELECT name, questionary FROM agency WHERE questionary!=''");
	foreach($data as $row){
		$questionary = json_decode($row["questionary"], TRUE);
		if($questionary){
			$time = $questionary["time"];
			$class = "";
			if($time >= strToTime("-2 days"))
				$class = " alert-info ";
			$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $questionary["manager"]);
?>
	<tr class="<?php echo $class; ?>">
		<td width="13%"><?php echo $row["name"]; ?></td>
		<td width="12%"><?php echo $manager; ?></td>
		<td width="25%"><?php echo $check[$questionary["check-cabinet"]]." ; ".$questionary["cabinet-note"]; ?></td>
		<td width="25%"><?php echo $check[$questionary["check-place"]]." ; ".$questionary["place-note"]; ?></td>
		<td width="25%"><?php echo $check[$questionary["check-find"]]." ; ".$questionary["find-note"]; ?></td>
	</tr>
<?php
		}
	}
?>	
	</table>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_history_agency($connect){
	$id = $_POST["id"];
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-history"></i> История</div>
	<table class="table">
	<tr>
		<th width="30%">Время</th>
		<th width="70%">Примечание</th>
	</tr>
<?php
	$data = $connect->getAll("SELECT DATE_FORMAT(time, '%H:%i:%s %d.%m.%Y') as time, text FROM history_agency WHERE agency=?i", $id);
	foreach($data as $row){
?>
	<tr>
		<td width="30%"><?php echo $row["time"]; ?></td>
		<td width="70%"><?php echo $row["text"]; ?></td>
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

function agency_to_trash($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE agency SET active=1 WHERE id=?i", $id);
}

function edit_agency_sync_info($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, short_name, inn, 1C_code FROM agency WHERE id=?i", $id);
	$row = clear_quotes($row);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить информацию <?php echo $row["short_name"]; ?></h4>
			</div>
			<div class="modal-body form-horizontal edit-agency">
				<div class="form-group">
					<label class="col-sm-4 control-label">Полное название</label>
					<div class="col-sm-8">
						<input type="text" class="form-control name-agency" value="<?php echo $row['name']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">ИНН</label>
					<div class="col-sm-8">
						<input type="text" class="form-control inn-agency" value="<?php echo $row['inn']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Код контрагента</label>
					<div class="col-sm-8">
						<input type="text" class="form-control code-1C" value="<?php echo $row['1C_code']; ?>" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_agency_sync_info('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_agency_sync_info($connect){
	$_POST = clear_array($_POST);
	$id = $_POST["id"];
	$name = $_POST["name"];
	$code = $_POST["code"];
	$inn = $_POST["inn"];
	$connect->query("UPDATE agency SET name=?s, inn=?i, 1C_code=?s WHERE id=?i", $name, $code, $inn, $id);
}

?>
