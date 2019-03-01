<?php

function show_report_menu(){
	ob_start();
?>
<div class="btn-group btn-group-justified head-menu-report">
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-general" onclick="general_report()"><i class="fa fa-tasks"></i> Общий</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-calendar" onclick="calendar_report()"><i class="fa fa-calendar"></i> Календарь</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-plan" onclick="plan_report()"><i class="fa fa-calculator"></i> План</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-payment" onclick="payment_report()"><i class="fa fa-university"></i> Платежи</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-bonus" onclick="bonus_report()"><i class="fa fa-gift"></i> Бонусы</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-graphics" onclick="graphics_report()"><i class="fa fa-bar-chart"></i> Графики</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-history" onclick="history_report()"><i class="fa fa-history"></i> История</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-module" onclick="module_report()"><i class="fa fa-search"></i> Модуль</button>
	</div>
</div>
<div id="data" style="margin-top: 10px"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_general_report_menu(){
	ob_start();
?>
<div class="btn-group small-menu-report">
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-all" onclick="general_report()"><i class="fa fa-tasks"></i> Заявки</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-call-back" onclick="show_order_call_back_report()"><i class="fa fa-phone"></i> Заказы звонка</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-object" onclick="general_report_by_object()"><i class="fa fa-building-o"></i> Объекты</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-agency" onclick="general_report_by_agency()"><i class="fa fa-user-secret"></i> Агентства</button>
	</div>
</div>
<div id="panel" style="margin-top: 10px"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_filter_manager($connect){
	global $session_login;
	ob_start();
?>
<div class="form-horizontal panel panel-default filter" onkeyup="if(event.keyCode == 13) filter_do()">
	<div class="panel-body">
		<div class="form-group">
			<div class="col-sm-8">
				<div class="form-group">
					<div class="col-sm-3">
						<input type="text" class="form-control" id="id_schet" placeholder="№ заявки" />
					</div>
					<label class="col-sm-3 control-label-left">Дата заявки</label>
					<label class="col-sm-3 control-label-left">Дата заезда</label>
					<label class="col-sm-3 control-label-left">Дата выезда</label>
				</div>
				<div class="form-group">
					<div class="col-sm-3">
						<input type="text" class="form-control" id="surname" placeholder="Фамилия" />
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_op" />
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_z" />
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_v" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-3">
						<select id="place_object" class="form-control">
							<option value="">Все заявки</option>
							<option value="ru">Россия</option>
							<option value="zag">Загранка</option>
							<option value="agency">Агентства</option>
							<option value="turist">Турист</option>
						</select>
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_op2" />
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_z2" />
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_v2" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-6" id="object_name">
						<input type="text" onkeyup="find_klient(event, 'object', 'object', 'use_object')" id="object" class="form-control id-object" placeholder="Объект" name="">
					</div>
					<div class="col-sm-6" id="tour_operator_span">
						<input type="text" onkeyup="find_klient(event, 'tour_operator', 'tour_operator', 'use_tour_operator')" id="tour_operator" class="form-control id-tour-operator" placeholder="Туроператор" name="">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Менеджер</label>
					<div class="col-sm-3">
						<?php echo get_managers($connect, "filter"); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-3"></div>
					<div class="col-sm-9">
						<label><input type="checkbox" id="status_agent" />&nbsp;отчет агента</label>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-3"></div>
					<div class="col-sm-9">
						<label><input type="checkbox" id="show-delete" />&nbsp;учитывать удаленные</label>
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					<label class="col-sm-12 control-label-left">Статус заявок</label>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<?php echo get_checkbox_table($connect, "status"); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="form-group form-group-margin">
			<div class="col-sm-offset-0 col-sm-12 text-right">
				<button type="button" class="btn btn-success btn-sm" onclick="filter_do()"><i class="fa fa-search"></i> Применить</button>
				&nbsp;<span class="button_agent"></span>
			</div>
		</div>
	</div>
</div>
<div id="filter_res"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_filter_report($connect){
	$conf = connect_config();
	$source_array = $conf->source_array;
	ob_start();
?>
<div class="form-horizontal panel panel-default filter" onkeyup="if(event.keyCode == 13) filter_do_report()">
	<div class="panel-body">
		<div class="form-group form-group-margin">
			<div class="col-sm-6">
				<div class="form-group">
					<div class="col-sm-3">
						<input type="text" class="form-control" id="id_schet" placeholder="№ заявки" />
					</div>
					<label class="col-sm-3 control-label-left">Дата заявки</label>
					<label class="col-sm-3 control-label-left">Дата заезда</label>
					<label class="col-sm-3 control-label-left">Дата выезда</label>
				</div>
				<div class="form-group">
					<div class="col-sm-3">
						<input type="text" class="form-control" id="surname" placeholder="Фамилия" />
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_op" />
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_z" />
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_v" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-3">
						<select id="place_object" class="form-control">
							<option value="">Все заявки</option>
							<option value="ru">Россия</option>
							<option value="zag">Загранка</option>
							<option value="agency">Агентства</option>
							<option value="turist">Турист</option>
						</select>
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_op2" />
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_z2" />
					</div>
					<div class="col-sm-3">
						<input type="text" class="form-control datepicker" id="date_v2" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-6" id="object_name">
						<input type="text" onkeyup="find_klient(event, 'object', 'object', 'use_object')" id="object" class="form-control id-object" placeholder="Объект" name="">
					</div>
					<div class="col-sm-6" id="tour_operator_span">
						<input type="text" onkeyup="find_klient(event, 'tour_operator', 'tour_operator', 'use_tour_operator')" id="tour_operator" class="form-control id-tour-operator" placeholder="Туроператор" name="">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Офис</label>
					<div class="col-sm-3">
						<?php echo get_select_table($connect, "office", "", "", "office", 1); ?>
					</div>
					<label class="col-sm-3 control-label">Менеджер</label>
					<div class="col-sm-3">
						<?php echo get_managers($connect, "filter"); ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Регион</label>
					<div class="col-sm-3">
						<?php echo get_select_table($connect, "region", "active=0", "", "regions", 1); ?>
					</div>
					<div class="col-sm-6" id="url_website">
						<input type="text" class="form-control" onkeyup="find_klient(event, 'website', 'st_website', 'sel_website')" id="website" placeholder="Сайт" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-3"></div>
					<div class="col-sm-9">
						<label><input type="checkbox" id="show-delete" /> учитывать удаленные</label>
						<label><input type="checkbox" id="through-tour" /> без туроператора</label>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-3"></div>
					<div class="col-sm-9">
						<label><input type="checkbox" id="show-site-bid" checked /> заявки с сайта</label>
						<label><input type="checkbox" id="show-crm-bid" checked /> заведенные в CRM</label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Источник</label>
					<div class="col-sm-9">
					<?php foreach($source_array as $index_source => $source){ ?>
						<label><input type="checkbox" class="source" value="<?php echo $index_source; ?>" /> <?php echo $source["name"]; ?></label>
					<?php } ?>
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="form-group">
					<label class="col-sm-12 control-label-left">Статус заявок</label>
				</div>
				<div class="form-group form-group-margin">
					<div class="col-sm-12">
						<?php echo get_checkbox_table($connect, "status"); ?>
						<label><input type="checkbox" id="all_status" onclick="check_all('status')"> Выбрать все</label>
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="form-group">
					<label class="col-sm-12 control-label-left">Статус санатория</label>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<?php echo get_checkbox_table($connect, "status_san"); ?>
						<label><input type="checkbox" id="all_status_san" onclick="check_all('status_san')"> Выбрать все</label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-12 control-label-left">Отчет агента</label>
				</div>
				<div class="form-group form-group-margin">
					<div class="col-sm-12">
						<?php echo get_checkbox_table($connect, "status_agent"); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="form-group form-group-margin">
			<div class="col-sm-offset-0 col-sm-12 right">
				<button type="button" class="btn btn-success btn-sm btn-search" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Подождите, идет поиск..." onclick="filter_do_report()"><i class="fa fa-search"></i> Применить</button>
				<button type="button" class="btn btn-info btn-hide btn-sm" id="button_mass" disabled onclick="menu_mass_action()"><i class="fa fa-angle-double-down"></i> Мас.действия</button>
				<button type="button" class="btn btn-warning btn-hide btn-sm" disabled onclick="filter_do_update()"><i class="fa fa-spinner"></i> Обновить</button>
			</div>
		</div>
	</div>
</div>
<div id="filter_res"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function filter_do($connect){
	global $session_login, $id_rights;
	if(isset($_POST["type"]) AND $_POST["type"] == "update" AND $_COOKIE["filter"]){
		$cook = $_COOKIE["filter"];
		$mas = explode(";;;", $cook);
		foreach($mas as $value){
			$arr = explode("===", $value);
			if($arr[0] == "surname")
				$_POST["surname"] = $arr[1];
			elseif($arr[0] == "all_manager")
				$_POST["all_manager"] = $arr[1];
			elseif($arr[0] == "date_z")
				$_POST["date_z"] = $arr[1];
			elseif($arr[0] == "date_z2")
				$_POST["date_z2"] = $arr[1];
			elseif($arr[0] == "date_op")
				$_POST["date_op"] = $arr[1];
			elseif($arr[0] == "date_op2")
				$_POST["date_op2"] = $arr[1];
			elseif($arr[0] == "date_v")
				$_POST["date_v"] = $arr[1];
			elseif($arr[0] == "date_v2")
				$_POST["date_v2"] = $arr[1];
			elseif($arr[0] == "id_schet")
				$_POST["id_schet"] = $arr[1];
			elseif($arr[0] == "status_id")
				$_POST["status_id"] = $arr[1];
			elseif($arr[0] == "st_san")
				$_POST["st_san"] = $arr[1];
			elseif($arr[0] == "st_agent")
				$_POST["st_agent"] = $arr[1];
			elseif($arr[0] == "id_obj")
				$_POST["id_obj"] = $arr[1];
			elseif($arr[0] == "payer")
				$_POST["payer"] = $arr[1];
			elseif($arr[0] == "type_filter")
				$_POST["type_filter"] = $arr[1];
			elseif($arr[0] == "id_tour")
				$_POST["id_tour"] = $arr[1];
			elseif($arr[0] == "region")
				$_POST["region"] = $arr[1];
			elseif($arr[0] == "website")
				$_POST["website"] = $arr[1];
			elseif($arr[0] == "place_object")
				$_POST["place_object"] = $arr[1];
			elseif($arr[0] == "show_delete")
				$_POST["show_delete"] = $arr[1];
			elseif($arr[0] == "office")
				$_POST["office"] = $arr[1];
			elseif($arr[0] == "through_tour")
				$_POST["through_tour"] = $arr[1];
			elseif($arr[0] == "site_bid")
				$_POST["site_bid"] = $arr[1];
			elseif($arr[0] == "crm_bid")
				$_POST["crm_bid"] = $arr[1];
		}
	}
	$surname = $_POST["surname"];
	$manager = $_POST["all_manager"];
	$date_z = $_POST["date_z"];
	$date_z2 = $_POST["date_z2"];
	$date = $_POST["date_op"];
	$date2 = $_POST["date_op2"];
	$date_v = $_POST["date_v"];
	$date_v2 = $_POST["date_v2"];
	$id_object = $_POST["id_obj"];
	$id_schet = $_POST["id_schet"];
	$status = $_POST["status_id"];
	$status_san = $_POST["st_san"];
	$status_agent = $_POST["st_agent"];
	$source_filter = $_POST["source"];
	$id_tour = $_POST["id_tour"];
	$type_filter = $_POST["type_filter"];
	$show_delete = $_POST["show_delete"];
	$through_tour = $_POST["through_tour"];
	$place_object = $_POST["place_object"];
	$site_bid = $_POST["site_bid"];
	$crm_bid = $_POST["crm_bid"];
	$count = $_POST;
	$website = "";
	if(isset($_POST["website"]))
		$website = $_POST["website"];
	$region = "";
	if(isset($_POST["region"]))
		$region = $_POST["region"];
	$office = "";
	if(isset($_POST["office"]))
		$office = $_POST["office"];

	if(!$office)
	    $office = "";

	$str = "";
	$zapros_for_mysql = "";
	$html = "";
	$index = 0;
	foreach($_POST as $key => $value){
		$count--;
		$str.= $key."===".$value;
		if($str)
			$str.= ";;;";
	}
	SetCookie("filter", $str);
	if($date_z != ""){
		if($date_z2)
			$zapros_for_mysql.= " (date_z >= '$date_z' AND date_z <= '$date_z2') ";
		else
			$zapros_for_mysql.= " date_z = '$date_z' ";
	}
	if($date_v != ""){
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		if($date_v2)
			$zapros_for_mysql.= " (date_v >= '$date_v' AND date_v <= '$date_v2') ";
		else
			$zapros_for_mysql.= " date_v = '$date_v' ";
	}
	if($date != ""){
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		if($date2)
			$zapros_for_mysql.= " (date >= '$date' AND date <= '$date2') ";
		else
			$zapros_for_mysql.= " date = '$date' ";
	}
	if($manager != ""){
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		$zapros_for_mysql.= " id_user = $manager ";
	}
	if($office != ""){
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		$zapros = "";
		$data = $connect->getAll("SELECT id FROM users WHERE office=?i", $office);
		foreach($data as $row){
			if($zapros)
				$zapros.= " OR ";
			$zapros.= " id_user = ".$row["id"];
		}
		$zapros_for_mysql.= " ( ".$zapros." ) ";
	}
	if($id_object != ""){
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		$zapros_for_mysql.= " id_obj = $id_object ";
	}
	if($website != ""){
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		$zapros_for_mysql.= " (website = '$website' OR website = 'mobile.".$website."') ";
	}
	if($id_tour != ""){
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		$zapros_for_mysql.= " id_tour = $id_tour";
	}
	if($status != ""){
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		$a = explode("_", $status);
		$a = array_diff($a, array(""));
		foreach($a as $stat){
			if(isset($and_st) && $and_st == 1)
				$zapros_for_mysql .= " OR ";
			else
				$zapros_for_mysql.= " (";
			$zapros_for_mysql.= " status = $stat ";
			$and_st = 1;
		}
		$zapros_for_mysql.= ")";
	}
	if($status_san != ""){
		$and_st = 0;
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		if($status_san == "no_1")
			$zapros_for_mysql.= " status_san != 1 ";
		elseif($type_filter == "report"){
			$a = explode("_", $status_san);
			$a = array_diff($a, array(""));
			foreach($a as $stat){
				if($and_st == 1)
					$zapros_for_mysql .= " OR ";
				else
					$zapros_for_mysql.= " (";
				$zapros_for_mysql.= " status_san = $stat ";
				$and_st = 1;
			}
			$zapros_for_mysql.= ")";
		}else
			$zapros_for_mysql.= " status_san = $status_san ";
	}
	if($status_agent != ""){
		$and_st = 0;
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		$a = explode("_", $status_agent);
		$a = array_diff($a, array(""));
		foreach($a as $stat){
			if($and_st == 1)
				$zapros_for_mysql .= " OR ";
			else
				$zapros_for_mysql.= " (";
			$zapros_for_mysql.= " status_agent = $stat ";
			$and_st = 1;
		}
		$zapros_for_mysql.= ") AND  agency != '' ";
		if($type_filter == "manager")
			$zapros_for_mysql.= " AND status = 5 ";
	}
	if($source_filter != ""){
		$and_st = 0;
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		$a = explode("_", $source_filter);
		$a = array_diff($a, array(""));
		foreach($a as $stat){
			if($and_st == 1)
				$zapros_for_mysql .= " OR ";
			else
				$zapros_for_mysql.= " (";
			$zapros_for_mysql.= " source = $stat ";
			$and_st = 1;
		}
		$zapros_for_mysql.= ")";
	}
	if($show_delete != 1){
		if($zapros_for_mysql) $zapros_for_mysql.= " AND ";
		$zapros_for_mysql.= " active != 3";
	}
	if($through_tour == 1){
		if($zapros_for_mysql) $zapros_for_mysql.= " AND ";
		$zapros_for_mysql.= " (id_tour = '' OR id_tour is NULL) ";
	}
	if($site_bid == 2){
		if($zapros_for_mysql) $zapros_for_mysql.= " AND ";
		$zapros_for_mysql.= " (website is NULL OR website='') ";
	}
	if($crm_bid == 2){
		if($zapros_for_mysql) $zapros_for_mysql.= " AND ";
		$zapros_for_mysql.= " (website != '') ";
	}

	if($region != "")
		$zapros_for_mysql.= " ".get_objects_by_region($connect, $region, "reckoning")." ";
	if($place_object != ""){
		if($zapros_for_mysql)
			$zapros_for_mysql.= " AND ";
		$zapros_for_mysql.= "(";
		$zapros = "";
		if($place_object == "agency")
			$zapros_for_mysql.= "(turist='' OR turist IS NULL) AND agency!=''";
		elseif($place_object == "turist")
			$zapros_for_mysql.= "(agency='' OR agency IS NULL) AND turist!=''";
		else{
			if($place_object == "zag")
				$data = $connect->getAll("SELECT id FROM region WHERE id_country!=1");
			else
				$data = $connect->getAll("SELECT id FROM region WHERE id_country=1");
			foreach($data as $row){
				$id_reg = $row["id"];
				$data2 = $connect->getAll("SELECT id FROM object WHERE id_reg=".$id_reg);
				foreach($data2 as $row){
					if($zapros)
						$zapros.= " OR ";
					$zapros.= "id_obj=".$row["id"];
				}
			}
		}
		$zapros_for_mysql.= $zapros.")";
	}


	if(!$surname AND !$id_schet AND !$date_z AND $id_rights < 4){
		if($zapros_for_mysql)
			$zapros_for_mysql.= " AND ";
		$zapros_for_mysql.= "(";
		$zapros = "";
		$my_office = $connect->getOne("SELECT office FROM users WHERE id=?i", $session_login);
		$data = $connect->getAll("SELECT id FROM users WHERE office=?i", $my_office);
		foreach($data as $row){
			if($zapros)
				$zapros.= " OR ";
			$zapros.= "id_user=".$row["id"];
		}
		$zapros_for_mysql.= $zapros.")";
	}

	if($zapros_for_mysql)
		$zapros_for_mysql = " WHERE ".$zapros_for_mysql;
	$zapros_for_mysql = "SELECT id, rest, turist, agency, date, id_user, sum, id_obj, date_z, date_v, status, status_san, status_agent, active, website, guaranteed, `source`, promo_code, form_booking FROM reckoning " .$zapros_for_mysql." ORDER BY id";

    if($id_schet){
		$zapros_for_mysql = "SELECT id, rest, turist, agency, id_user, date, sum, id_obj, date_z, date_v, status, status_san, status_agent, active, website, guaranteed, source, promo_code, form_booking FROM reckoning WHERE (";
		$id_schet = str_replace(".", "#", $id_schet);
		$id_schet = str_replace(",", "#", $id_schet);
		$id_schet = str_replace(" ", "#", $id_schet);
		$id_schet = str_replace("-", "#", $id_schet);
		$arr_schet = explode("#", $id_schet);
		$i = 0;
		foreach($arr_schet as $reck){
			if((int)$reck){
				$i++;
				$reck = (int)$reck;
				if($i > 1)
					$zapros_for_mysql.= " OR reckoning.id=$reck ";
				else
				$zapros_for_mysql.= " reckoning.id=$reck ";
			}
		}
		$zapros_for_mysql.= ")";
	}
	$data = $connect->getAll($zapros_for_mysql);
	$num = 0;
	$all_id = "";
	$statistics = array("office" => array(), "all_sum" => 0, "num_prepay" => 0, "sum_prepay" => 0, "all_reward" => 0, "all_reward_fact" => 0);
	$office = $connect->getAll("SELECT id, name FROM office");
	foreach($office as $row){
		$id_office = $row["id"];
		$statistics["office"][$id_office]["name"] = $row["name"];
		$statistics["office"][$id_office]["index"] = 0;
		$statistics["office"][$id_office]["all_sum"] = 0;
		$statistics["office"][$id_office]["all_reward"] = 0;
        $statistics["office"][$id_office]["all_reward_fact"] = 0;
		$statistics["office"][$id_office]["num_prepay"] = 0;
		$statistics["office"][$id_office]["sum_prepay"] = 0;
	}
	$statistics["office"][0]["name"] = "without";
	$statistics["office"][0]["index"] = 0;
	$statistics["office"][0]["all_sum"] = 0;
	$statistics["office"][0]["all_reward"] = 0;
    $statistics["office"][0]["all_reward_fact"] = 0;
	$statistics["office"][0]["num_prepay"] = 0;
	$statistics["office"][0]["sum_prepay"] = 0;
	$index_check = 0;
	foreach($data as $row){
		$array_klient = array("");
		$all_fio = "";
		if($row["agency"]){
			$param = "agency";
			$type = $row["agency"];
			$array_klient = explode(",", $row["rest"]);
		}else{
			$param = "turist";
			$array_klient = explode(",", $row["rest"]);
			$type = $row["turist"];
		}
		if($row["rest"] == "" AND !$surname)
			$all_fio = "Отдыхающий не указан";
		$array_klient = array_diff($array_klient, array(""));
		foreach($array_klient as $tur){
			if($surname != "")
				$turist_row = $connect->getRow("SELECT surname, name, otch FROM klient WHERE id=?i AND surname LIKE ?s", $tur, "%".$surname."%");
			else
			    $turist_row = $connect->getRow("SELECT surname, name, otch FROM klient WHERE id=?i", $tur);

			if($all_fio AND ($turist_row["name"] || $turist_row['surname']))
				$all_fio.= "<br />";
			if($turist_row["name"] || $turist_row['surname'])
				$all_fio.= $turist_row["surname"]." ".$turist_row["name"]." ".$turist_row["otch"];
		}
		if($all_fio){
			$index_check++;
			if($index_check >= 100){
				$index_check = 0;
				$login = $connect->getOne("SELECT login FROM users WHERE id=?i", $session_login);
				if($connect->getOne("SELECT id FROM session WHERE login=?s AND request=0", $login)){
					$connect->query("UPDATE session SET request=1 WHERE login=?s AND request=0", $login);
					return "<div class='alert alert-info'><i class='fa fa-info-circle'></i> Запрос принудительно завершен</div>";
				}
			}
			$id_schet = $row["id"];
			$time = $connect->getOne("SELECT time FROM history_schet WHERE id_schet=?i ORDER BY id LIMIT 1", $id_schet);
			if($time)
				$time = "<br />".$time;
			$num++;
			$office = $connect->getOne("SELECT office FROM users WHERE id=?i", $row["id_user"]);
			$summa = $row["sum"];
			$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
			$status = $row["status"];
			$date = date_change($row["date"]);
			$object = get_object($connect, $row["id_obj"]);
			$full_object = get_object($connect, $row["id_obj"], "place");
			$active = $row["active"];
			$website = $row["website"];
			$guaranteed = $row["guaranteed"];
			$source = $row["source"];
			$promo_code = $row["promo_code"];
			$form_booking = $row["form_booking"];
			if($website)
				$object.= "<br />(<span style='text-decoration: underline;'>".$website."</span>)";
			if($row["status_san"] == 0)
				$status_san = "Нет";
			elseif($row["status_san"] == 1)
				$status_san = "Да";
			elseif($row["status_san"] == 3){
				$prepay_san = 0;
				$arr = get_payment($connect, $id_schet, 3);
				foreach($arr as $payments)
					$prepay_san+= $payments["sum"];
				$status_san = "Пред.<br />".$prepay_san;
			}elseif($row["status_san"] == 4)
				$status_san = "Онм";
			elseif($row["status_san"] == 5)
				$status_san = "Понм";
			elseif($row["status_san"] == 2){
				$prepay_san = 0;
				$arr = get_payment($connect, $id_schet, 3);
				foreach($arr as $payments)
					$prepay_san+= $payments["sum"];
				$status_san = "Ож.О.";
				if($prepay_san)
					$status_san.= "<br />".$prepay_san;
			}elseif($row["status_san"] == 6){
				$prepay_san = 0;
				$arr = get_payment($connect, $id_schet, 3);
				foreach($arr as $payments)
					$prepay_san+= $payments["sum"];
				$status_san = "Ож.П.";
				if($prepay_san)
					$status_san.= "<br />".$prepay_san;
			}
			if($row["status_agent"] == 0)
				$st_agent = "Не выс.";
			elseif($row["status_agent"] == 1)
				$st_agent = "Выс.";
			elseif($row["status_agent"] == 2)
				$st_agent = "Получ.";
			if($status == 4 OR $status == 7){
				$sum_prepay = 0;
				$arr = get_payment($connect, $id_schet, 1);
				foreach($arr as $payments){
					$sum_prepay+= $payments["sum"];
					$statistics["num_prepay"]++;
					$statistics["office"][$office]["num_prepay"]++;
				}
				$statistics["sum_prepay"]+= $sum_prepay;
				$statistics["office"][$office]["sum_prepay"]+= $sum_prepay;
				$summa.= "<br />".add_null($sum_prepay);
			}
			$append_status = "";
			if($status == 8){
				$sum = $connect->getOne("SELECT SUM(sum) FROM payment WHERE schet=?i AND (type=1 OR type=2) AND class='schet'", $id_schet);
				if($sum > 0)
					$append_status = "<br /><span class='text-danger'>Оплата туриста - ".$sum."</span>";
			}
			$status = $connect->getOne("SELECT name_menu FROM status WHERE id=?i", $status);
			$all_id.= $id_schet."_";
			$index++;
			$statistics["office"][$office]["index"]++;
			$date_z = date_change($row["date_z"]);
			$date_v = date_change($row["date_v"]);
			$reward = get_reward_schet($connect, $id_schet);
			$reward_fact = get_reward_schet($connect,$id_schet,"",true);
			$statistics["all_sum"]+= $summa;
			$statistics["all_reward"]+= $reward;
			$statistics["all_reward_fact"] += $reward_fact;
			$statistics["office"][$office]["all_sum"]+= $summa;
			$statistics["office"][$office]["all_reward"]+= $reward;
            $statistics["office"][$office]["all_reward_fact"]+= $reward_fact;

			$img_del = "";
			if($active == 3)
				$img_del = " <i class='fa fa-times-circle text-danger'></i> ";
			elseif($active == 2)
				$img_del = " <i class='fa fa-check-circle text-success'></i> ";
			if($guaranteed)
				$img_del.= " <i class='fa fa-star text-warning'></i> ";
			$img_del.= select_source_icon($source);
			if($promo_code)
				$img_del.= " <i class='fa fa-diamond' title='Промокод'></i>";
			if($form_booking == "default-form")
				$img_del.= " <i class='fa fa-list' title='Заявка в свободной форме'></i>";
			elseif($form_booking == "order-call-back")
				$img_del.= " <i class='fa fa-phone' title='Заявка с заказа звонка'></i>";
			elseif($form_booking == "website-call-back")
				$img_del.= " <i class='fa fa-phone-square' title='Заявка с сайта заказ звонка'></i>";
			elseif($form_booking == "quota")
				$img_del.= " <i class='fa fa-calendar-check-o' title='Заявка с квоты мест'></i>";
			elseif($form_booking == "agency" OR $param == "agency"){
				$img_del.= " <i class='fa fa-user-secret' title='Личный кабинет агентства'></i>";
				if($connect->getOne("SELECT id FROM booking_agency WHERE bid=?i", $id_schet))
					$img_del.= " <i class='fa fa-stack-overflow' title='Личный кабинет агентства'></i>";
			}
			elseif($form_booking == "client")
				$img_del.= " <i class='fa fa-user' title='Личный кабинет туриста'></i>";
			if($img_del)
				$img_del = "<span style='position: absolute; right: 1px; top: 1px;'>".$img_del."</span>";
			ob_start();
		?>
			<tr id="tr_<?php echo $id_schet; ?>" class="tr_reck" onclick="show_turist('<?php echo $type; ?>', '<?php echo $id_schet; ?>', '<?php echo $param; ?>')">
			<?php if($type_filter != "manager"){ ?>
				<td width="20" valign="top" onclick="event.stopPropagation();"><input type="checkbox" class="check_mass" value="<?php echo $id_schet; ?>" /></td>
			<?php } ?>
			<td width="25" valign="top"><?php echo $id_schet; ?></td>
<?php if($status_agent != ""){
	$agency = $connect->getOne("SELECT name FROM agency WHERE id=?i", $row["agency"]);
?>
			<td width="80" valign="top"><?php echo $agency; ?></td>
<?php } ?>
			<td width="250" id="rez<?php echo $num; ?>" style="position: relative;" valign="top"><?php echo $all_fio.$img_del; ?></td>
			<td width="80" valign="top"><?php echo $date.$time; ?></td>
			<td width="80" valign="top"><?php echo $date_z; ?></td>
			<td width="80" valign="top"><?php echo $date_v; ?></td>
			<td width="200" valign="top" data-toggle="tooltip" title="<?php echo $full_object; ?>"><?php echo $object; ?></td>
			<td width="70" valign="top"><?php echo $summa; ?></td>
			<?php if($type_filter != "manager"){ ?>
				<td width="70" valign="top"><?php echo $reward; ?></td>
			<?php } ?>
			<td width="80" valign="top"><?php echo $manager; ?></td>
			<td width="120" valign="top"><?php echo $status.$append_status; ?></td>
			<td width="30" valign="top"><?php echo $status_san; ?></td>
			<?php if($type_filter != "manager"){ ?>
				<td width="50" valign="top"><?php echo $st_agent; ?></td>
			<?php } ?>
			</tr>
		<?php
			$html.= ob_get_clean();
		}
	}
	if($html){
		if($status_agent != "" AND $type_filter == "manager"){
			$status_agent_td = "";
			$all_id = "<input type='hidden' id='hide_id' value='".$all_id."' />";
		}elseif($type_filter == "manager"){
			$status_agent_td = "";
			$all_id = "";
		}else{
			$status_agent_td = "<th width='50'>Агент</th>";
			$all_id = "";
		}
		if($type_filter != "manager"){
			$tbl_reward = "<th width='70'>Прибыль</th>";
			$checkbox = "<th width='20'><input type='checkbox' title='Выделить все' onclick='check_all(\"check_mass\")' id='all_check_mass' /></th>";
		}else{
			$tbl_reward = "";
			$checkbox = "";
		}
		ob_start();
	?>
	<?php if($type_filter == "report"){ ?>
	<div class="form-horizontal list-group" style="font-size: 13px;">
		<div class="list-group-item list-group-item-success">
			<div class="form-group form-group-margin">
				<div class="col-sm-2">
					<i class="fa fa-users"></i> По всем
				</div>
				<div class="col-sm-3">
					Всего: <strong><?php echo $index; ?></strong>
					на сумму <strong><?php echo number_format($statistics["all_sum"], 2, ",", " "); ?> <i class="fa fa-rub"></i></strong>
				</div>
				<div class="col-sm-3">
					Предопл. <strong><?php echo $statistics["num_prepay"]; ?></strong>
					на сумму <strong><?php echo number_format($statistics["sum_prepay"], 2, ",", " "); ?> <i class="fa fa-rub"></i></strong>
				</div>
				<div class="col-sm-2">
					Прибыль ожид. <strong><?php echo number_format($statistics["all_reward"], 2, ",", " "); ?> <i class="fa fa-rub"></i></strong>
				</div>

                <div class="col-sm-2">
                    Прибыль факт. <strong><?php echo number_format($statistics["all_reward_fact"], 2, ",", " "); ?> <i class="fa fa-rub"></i></strong>
                </div>
			</div>
		</div>
	<?php
		foreach($statistics["office"] as $id_office => $office){
			if($office["index"] > 0){
				$class = "";
				if($office["name"] == "without"){
					$office["name"] = "-";
					$class = " list-group-item-danger ";
				}
	?>
		<div class="list-group-item <?php echo $class; ?>">
			<div class="form-group form-group-margin">
				<div class="col-sm-2">
					<i class="fa fa-home"></i> Офис <?php echo $office["name"]; ?>
				</div>
				<div class="col-sm-3">
					Всего: <strong><?php echo $office["index"]; ?></strong>
					на сумму <strong><?php echo number_format($office["all_sum"], 2, ",", " "); ?> <i class="fa fa-rub"></i></strong>
				</div>
				<div class="col-sm-3">
					Предопл. <strong><?php echo $office["num_prepay"]; ?></strong>
					на сумму <strong><?php echo number_format($office["sum_prepay"], 2, ",", " "); ?> <i class="fa fa-rub"></i></strong>
				</div>
				<div class="col-sm-2">
					Прибыль ожид. <strong><?php echo number_format($office["all_reward"], 2, ",", " "); ?> <i class="fa fa-rub"></i></strong>
				</div>

                <div class="col-sm-2">
                    Прибыль факт. <strong><?php echo number_format($office["all_reward_fact"], 2, ",", " "); ?> <i class="fa fa-rub"></i></strong>
                </div>
			</div>
		</div>
	<?php
			}
		}
	?>
	</div>
	<?php }else{ ?>
		<div class="well well-sm">Всего результатов поиска: <strong><?php echo $index; ?></strong></div>
	<?php } ?>
		<?php echo $all_id; ?>
		<table id="tbl_filter" class="table table-condensed table-hover">
		<thead>
		<tr id="filter_tr">
			<?php echo $checkbox; ?>
			<th width="25">№</th>
<?php if($status_agent != ""){ ?>
			<th width="80">Агентство</th>
<?php } ?>
			<th width="250">ФИО</th>
			<th class="{dateFormat: 'ddmmyyyy'}" width="80">Заявка</th>
			<th width="80" class="{dateFormat: 'ddmmyyyy'}">Заезд</th>
			<th class="{dateFormat: 'ddmmyyyy'}" width="80">Выезд</th>
			<th width="200">Объект</th>
			<th width="70">Сумма</th>
			<?php echo $tbl_reward; ?>
			<th width="80">Менеджер</th>
			<th width="120">Статус</th>
			<th width="30">Сан</th>
			<?php echo $status_agent_td; ?>
		</tr>
		</thead>
		<tbody>
			<?php echo $html; ?>
		</tbody>
		</table>
	<?php
		$html = ob_get_clean();
	}else
		$html = "<div class='alert alert-info'><i class='fa fa-info-circle'></i> Ничего не найдено</div>";
	return $html;
}

function show_filter_report_object(){
	global $array_month;
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-1 control-label">Месяц</label>
			<div class="col-sm-2">
				<select class="form-control month-filter">
					<option value="">Выбрать месяц</option>
				<?php foreach($array_month as $key => $month){ ?>
					<option value="<?php echo $key; ?>"><?php echo $month; ?></option>
				<?php } ?>
				</select>
			</div>
			<label class="col-sm-1 control-label">Год</label>
			<div class="col-sm-2">
				<select class="form-control year-filter">
					<?php for($year = 2013; $year<= date("Y"); $year++){ ?>
					<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="col-sm-6">
				<label><input type="checkbox" class="compare-prev-year" /> Сравнивать с предыдущим годом</label>
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-1 control-label">Даты</label>
			<div class="col-sm-2">
				<input type="text" class="form-control datepicker" id="date-start" />
			</div>
			<div class="col-sm-3">
				<input type="text" class="form-control datepicker" id="date-end" />
			</div>
		</div>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-success btn-sm btn-search" data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Подождите, идет поиск..." onclick="filter_do_by_object()"><i class="fa fa-search"></i> Применить</button>
	</div>
</div>
<div class="result"></div>
<?php
}

function filter_do_by_object($connect){
	global $session_login;
	$html = "";
	$month = $_POST["month"];
	$year = $_POST["year"];
	$compare = $_POST["compare"];
	$start = $_POST["start"];
	$end = $_POST["end"];
	$array_zapros = array();
	$all = array("all_sum_c" => 0, "all_num_c" => 0, "all_sum_z" => 0, "all_num_z" => 0, "prev_all_sum_c" => 0, "prev_all_num_c" => 0, "prev_all_sum_z" => 0, "prev_all_num_z" => 0);
	if($start AND $end){
		$start = date_change($start, "-", ".");
		$end = date_change($end, "-", ".");
		if($compare == 1){
			$t = explode("-", $start);
			$start_compare = ($t[0]-1)."-".$t[1]."-".$t[2];
			$t = explode("-", $end);
			$end_compare = ($t[0]-1)."-".$t[1]."-".$t[2];
		}
	}elseif($month){
		$start = $year."-".$month."-1";
		$end = $year."-".$month."-".cal_days_in_month(CAL_GREGORIAN, $month, $year);
		if($compare == 1){
			$start_compare = ($year-1)."-".$month."-1";
			$end_compare = ($year-1)."-".$month."-".cal_days_in_month(CAL_GREGORIAN, $month, ($year-1));
		}
	}else{
		$start = $year."-1-1";
		$end = $year."-12-31";
		if($compare == 1){
			$start_compare = ($year-1)."-1-1";
			$end_compare = ($year-1)."-12-31";
		}
	}
	$array_zapros[1] = " (date>='".$start."' AND date<='".$end."') ";
	$array_zapros[2] = " (date_z>='".$start."' AND date_z<='".$end."') ";
	if($compare == 1){
		$array_compare = array();
		$array_compare[1] = " (date>='".$start_compare."' AND date<='".$end_compare."') ";
		$array_compare[2] = " (date_z>='".$start_compare."' AND date_z<='".$end_compare."') ";
	}
	$data = $connect->getAll("SELECT id, name FROM object ORDER BY name");
	$index_check = 0;
	foreach($data as $row){
		$index_check++;
		if($index_check >= 10){
			$index_check = 0;
			$login = $connect->getOne("SELECT login FROM users WHERE id=?i", $session_login);
			if($connect->getOne("SELECT id FROM session WHERE login=?s AND request=0", $login)){
				$connect->query("UPDATE session SET request=1 WHERE login=?s AND request=0", $login);
				return "<div class='alert alert-info'><i class='fa fa-info-circle'></i> Запрос принудительно завершен</div>";
			}
		}
		$sum = array(1 => 0, 2 => 0);
		$num = array(1 => 0, 2 => 0);
		$id = $row["id"];
		$object = $row["name"];
		$object_name = get_object($connect, $id, "place");
		foreach($array_zapros as $key => $zapros){
			$data2 = $connect->getAll("SELECT sum FROM reckoning WHERE id_obj=?i AND ".$zapros." AND active!=3 AND status<=5", $id);
			foreach($data2 as $row){
				$sum[$key]+= $row["sum"];
				$num[$key]++;
			}
		}
		$sum_compare = array(1 => 0, 2 => 0);
		$num_compare = array(1 => 0, 2 => 0);
		$percent_sum_compare = array(1 => 0, 2 => 0);
		$percent_num_compare = array(1 => 0, 2 => 0);
		if($compare == 1){
			foreach($array_compare as $key => $zapros){
				$all_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE id_obj=?i AND ".$zapros." AND active!=3 AND status<=5", $id);
				$all_num = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE id_obj=?i AND ".$zapros." AND active!=3 AND status<=5", $id);
				$sum_compare[$key]+= $all_sum;
				$num_compare[$key]+= $all_num;
				$all["prev_all_sum_c"]+= $sum_compare[1];
				$all["prev_all_num_c"]+= $num_compare[1];
				$all["prev_all_sum_z"]+= $sum_compare[2];
				$all["prev_all_num_z"]+= $num_compare[2];

				if($sum_compare[$key] > 0 AND $num_compare[$key] > 0){
					$percent_sum_compare[$key] = round(($sum[$key] - $sum_compare[$key]) / $sum_compare[$key] * 100);
					$percent_num_compare[$key] = round(($num[$key] - $num_compare[$key]) / $num_compare[$key] * 100);
				}
				if($percent_sum_compare[$key] == 0)
					$percent_sum_compare[$key] = "";
				if($percent_sum_compare[$key] > 0)
					$percent_sum_compare[$key] = "<span class='green-success'><i class='fa fa-long-arrow-up'></i> ".$percent_sum_compare[$key]."%</span>";
				if($percent_sum_compare[$key] < 0)
					$percent_sum_compare[$key] = "<span class='red-danger'><i class='fa fa-long-arrow-down'></i> ".abs($percent_sum_compare[$key])."%</span>";
				if($percent_num_compare[$key] == 0)
					$percent_num_compare[$key] = "";
				if($percent_num_compare[$key] > 0)
					$percent_num_compare[$key] = "<span class='green-success'><i class='fa fa-long-arrow-up'></i> ".$percent_num_compare[$key]."%</span>";
				if($percent_num_compare[$key] < 0)
					$percent_num_compare[$key] = "<span class='red-danger'><i class='fa fa-long-arrow-down'></i> ".abs($percent_num_compare[$key])."%</span>";
			}
		}
		$all_sum = $sum[1] + $sum[2];
		$all_num = $num[1] + $num[2];
		if($all_num){
			$all["all_sum_c"]+= $sum[1];
			$all["all_num_c"]+= $num[1];
			$all["all_sum_z"]+= $sum[2];
			$all["all_num_z"]+= $num[2];
			if($sum[1])
				$sum[1] = add_null($sum[1]);
			if($sum[2])
				$sum[2] = add_null($sum[2]);
			ob_start();
		?>
			<tr>
				<td width="28%"><strong title="<?php echo $object_name; ?>"><?php echo $object; ?></strong></td>
				<td width="6%" class="center"><?php echo $num[1]; ?></td>
				<td width="6%" class="center"><?php echo $percent_num_compare[1]; ?></td>
				<td width="6%"><?php echo $sum[1]; ?></td>
				<td width="6%" class="center"><?php echo $percent_sum_compare[1]; ?></td>
				<td width="6%" class="center"><?php echo $num[2]; ?></td>
				<td width="6%" class="center"><?php echo $percent_num_compare[2]; ?></td>
				<td width="6%"><?php echo $sum[2]; ?></td>
				<td width="6%" class="center"><?php echo $percent_sum_compare[2]; ?></td>
			</tr>
		<?php
			$html.= ob_get_clean();
		}
	}
	if($html){
		$percent = array(1 => array("sum" => 0, "num" => 0), 2 => array("sum" => 0, "num" => 0));
		if($all["prev_all_sum_c"] > 0){
			$percent[1]["sum"] = round(($all["all_sum_c"] - $all["prev_all_sum_c"]) / $all["prev_all_sum_c"] * 100);
			$percent[1]["num"] = round(($all["all_num_c"] - $all["prev_all_num_c"]) / $all["prev_all_num_c"] * 100);
		}
		if($all["prev_all_sum_z"] > 0){
			$percent[2]["sum"] = round(($all["all_sum_z"] - $all["prev_all_sum_z"]) / $all["prev_all_sum_z"] * 100);
			$percent[2]["num"] = round(($all["all_num_z"] - $all["prev_all_num_z"]) / $all["prev_all_num_z"] * 100);
		}
		foreach($percent as $index => $value){
			if($value["sum"] == 0)
				$percent[$index]["sum"] = "";
			if($value["sum"] > 0)
				$percent[$index]["sum"] = "<span class='green-success'><i class='fa fa-long-arrow-up'></i> ".$value["sum"]."%</span>";
			if($value["sum"] < 0)
				$percent[$index]["sum"] = "<span class='red-danger'><i class='fa fa-long-arrow-down'></i> ".abs($value["sum"])."%</span>";
			if($value["num"] == 0)
				$percent[$index]["num"] = "";
			if($value["num"] > 0)
				$percent[$index]["num"] = "<span class='green-success'><i class='fa fa-long-arrow-up'></i> ".$value["num"]."%</span>";
			if($value["num"] < 0)
				$percent[$index]["num"] = "<span class='red-danger'><i class='fa fa-long-arrow-down'></i> ".abs($value["num"])."%</span>";
		}
		ob_start();
	?>
		<table class="tbl-filter table table-bordered table-condensed">
		<thead>
		<tr>
			<th>Объект</th>
			<th colspan="2" class="center">заявок</th>
			<th colspan="2" class="center">на сумму</th>
			<th colspan="2" class="center">заездов</th>
			<th colspan="2" class="center">на сумму</th>
		</tr>
		</thead>
		<tbody>
			<?php echo $html; ?>
		</tbody>
		<tr class="alert-success">
			<td><strong>Итого</strong></td>
			<td class="center"><?php echo $all["all_num_c"]; ?></td>
			<td class="center"><?php echo $percent[1]["num"]; ?></td>
			<td class="center"><?php echo number_format($all["all_sum_c"], 2, ",", " "); ?></td>
			<td class="center"><?php echo $percent[1]["sum"]; ?></td>
			<td class="center"><?php echo $all["all_num_z"]; ?></td>
			<td class="center"><?php echo $percent[2]["num"]; ?></td>
			<td class="center"><?php echo number_format($all["all_sum_z"], 2, ",", " "); ?></td>
			<td class="center"><?php echo $percent[2]["sum"]; ?></td>
		</tr>
		</table>
	<?php
		$html = ob_get_clean();
	}
	return $html;
}

function show_filter_report_agency(){
	global $array_month;
	ob_start();
?>
<div class="form-horizontal panel panel-default filter-agency">
	<div class="panel-body">
		<div class="form-group form-group-margin">
			<label class="col-sm-1 control-label">Месяц</label>
			<div class="col-sm-2">
				<select class="form-control" id="month">
					<option value="">Выбрать месяц</option>
				<?php foreach($array_month as $key => $month){ ?>
					<option value="<?php echo $key; ?>"><?php echo $month; ?></option>
				<?php } ?>
				</select>
			</div>
			<label class="col-sm-1 control-label">Год</label>
			<div class="col-sm-2">
				<select class="form-control" id="year">
					<?php for($year = 2013; $year<= date("Y"); $year++){ ?>
					<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
					<?php } ?>
				</select>
			</div>
			<label class="col-sm-2 control-label">Статистика</label>
			<div class="col-sm-2">
				<select class="form-control" id="select-stat">
					<option value="default">общая</option>
					<option value="detail">детальная</option>
				</select>
			</div>
		</div>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-success btn-sm" onclick="filter_report_agency()"><i class="fa fa-search"></i> Применить</button>
	</div>
</div>
<div class="result"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function filter_report_agency($connect){
	global $month_pad, $array_month;
	$month = $_POST["month"];
	$year = $_POST["year"];
	$stat = $_POST["stat"];
	$itog = array("all" => 0, "pay" => 0, "no_pay" => 0, "cancel" => 0, "all_sum" => 0, "pay_sum" => 0, "no_pay_sum" => 0, "cancel_sum" => 0);
?>
	<table class="table">
<?php
	if($stat == "default"){
			?>
			<tr>
				<th>Дата</th>
				<th>Всего заявок</th>
				<th>На сумму</th>
				<th>Оплаченые</th>
				<th>На сумму</th>
				<th>Неоплаченые</th>
				<th>На сумму</th>
				<th>Аннулированные</th>
				<th>На сумму</th>
			</tr>
			<?php
		if($month){
			$max_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$name_month = $month_pad[$month];
			for($day = 1; $day <= $max_day; $day++){
				$date = $year."-".$month."-".$day;
				$all = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE active!=3 AND date=?s AND agency!=''", $date);
				$pay = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE status=5 AND active!=3 AND date=?s AND agency!=''", $date);
				$no_pay = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE (status!=5 AND status!=6) AND active!=3 AND date=?s AND agency!=''", $date);
				$cancel = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE status=6 AND active!=3 AND date=?s AND agency!=''", $date);
				$all_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE active!=3 AND date=?s AND agency!=''", $date);
				$pay_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE status=5 AND active!=3 AND date=?s AND agency!=''", $date);
				$no_pay_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE (status!=5 AND status!=6) AND active!=3 AND date=?s AND agency!=''", $date);
				$cancel_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE status=6 AND active!=3 AND date=?s AND agency!=''", $date);
				$itog["all"]+= $all;
				$itog["pay"]+= $pay;
				$itog["no_pay"]+= $no_pay;
				$itog["cancel"]+= $cancel;
				$itog["all_sum"]+= $all_sum;
				$itog["pay_sum"]+= $pay_sum;
				$itog["no_pay_sum"]+= $no_pay_sum;
				$itog["cancel_sum"]+= $cancel_sum;
			?>
			<tr>
				<td><?php echo $day." ".$name_month; ?></td>
				<td><?php echo $all; ?></td>
				<td><?php echo $all_sum; ?></td>
				<td><?php echo $pay; ?></td>
				<td><?php echo $pay_sum; ?></td>
				<td><?php echo $no_pay; ?></td>
				<td><?php echo $no_pay_sum; ?></td>
				<td><?php echo $cancel; ?></td>
				<td><?php echo $cancel_sum; ?></td>
			</tr>
			<?php
			}
		}else{
			for($month = 1; $month <= 12; $month++){
				$name_month = $array_month[$month];
				$start = $year."-".$month."-1";
				$end = $year."-".$month."-".cal_days_in_month(CAL_GREGORIAN, $month, $year);
				$all = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE active!=3 AND (date>=?s AND date<=?s) AND agency!=''", $start, $end);
				$all_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE active!=3 AND (date>=?s AND date<=?s) AND agency!=''", $start, $end);
				$pay = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE status=5 AND active!=3 AND (date>=?s AND date<=?s) AND agency!=''", $start, $end);
				$pay_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE status=5 AND active!=3 AND (date>=?s AND date<=?s) AND agency!=''", $start, $end);
				$no_pay = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE (status!=5 AND status!=6) AND active!=3 AND (date>=?s AND date<=?s) AND agency!=''", $start, $end);
				$no_pay_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE (status!=5 AND status!=6) AND active!=3 AND (date>=?s AND date<=?s) AND agency!=''", $start, $end);
				$cancel = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE status=6 AND active!=3 AND (date>=?s AND date<=?s) AND agency!=''", $start, $end);
				$cancel_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE status=6 AND active!=3 AND (date>=?s AND date<=?s) AND agency!=''", $start, $end);
				$itog["all"]+= $all;
				$itog["pay"]+= $pay;
				$itog["no_pay"]+= $no_pay;
				$itog["cancel"]+= $cancel;
				$itog["all_sum"]+= $all_sum;
				$itog["pay_sum"]+= $pay_sum;
				$itog["no_pay_sum"]+= $no_pay_sum;
				$itog["cancel_sum"]+= $cancel_sum;
			?>
			<tr>
				<td><?php echo $name_month; ?></td>
				<td><?php echo $all; ?></td>
				<td><?php echo $all_sum; ?></td>
				<td><?php echo $pay; ?></td>
				<td><?php echo $pay_sum; ?></td>
				<td><?php echo $no_pay; ?></td>
				<td><?php echo $no_pay_sum; ?></td>
				<td><?php echo $cancel; ?></td>
				<td><?php echo $cancel_sum; ?></td>
			</tr>
			<?php
			}
		}
		?>
			<tr>
				<th>Итог</th>
				<th><?php echo $itog["all"]; ?></th>
				<th><?php echo $itog["all_sum"]; ?></th>
				<th><?php echo $itog["pay"]; ?></th>
				<th><?php echo $itog["pay_sum"]; ?></th>
				<th><?php echo $itog["no_pay"]; ?></th>
				<th><?php echo $itog["no_pay_sum"]; ?></th>
				<th><?php echo $itog["cancel"]; ?></th>
				<th><?php echo $itog["cancel_sum"]; ?></th>
			</tr>
		<?php
	}else{
			?>
			<tr>
				<th>Дата</th>
				<th>Всего заявок</th>
				<th>На сумму</th>
				<th>Оплаченые</th>
				<th>На сумму</th>
				<th>Неоплаченые</th>
				<th>На сумму</th>
				<th>Аннулированные</th>
				<th>На сумму</th>
			</tr>
			<?php
		if($month){
			$max_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$start = $year."-".$month."-1";
			$end = $year."-".$month."-".cal_days_in_month(CAL_GREGORIAN, $month, $year);
		}else{
			$start = $year."-1-1";
			$end = $year."-12-31";
		}
		$data = $connect->getAll("SELECT agency, COUNT(*) FROM reckoning WHERE active!=3 AND (date>=?s AND date<=?s) AND agency!='' GROUP BY agency ORDER BY COUNT(*) DESC", $start, $end);
		foreach($data as $row){
			$agency = $row["agency"];
			$all = $row["COUNT(*)"];
			$all_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE active!=3 AND (date>=?s AND date<=?s) AND agency=?i", $start, $end, $agency);
			$pay = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE status=5 AND active!=3 AND (date>=?s AND date<=?s) AND agency=?i", $start, $end, $agency);
			$pay_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE status=5 AND active!=3 AND (date>=?s AND date<=?s) AND agency=?i", $start, $end, $agency);
			$no_pay = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE (status!=5 AND status!=6) AND active!=3 AND (date>=?s AND date<=?s) AND agency=?i", $start, $end, $agency);
			$no_pay_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE (status!=5 AND status!=6) AND active!=3 AND (date>=?s AND date<=?s) AND agency=?i", $start, $end, $agency);
			$cancel = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE status=6 AND active!=3 AND (date>=?s AND date<=?s) AND agency=?i", $start, $end, $agency);
			$cancel_sum = $connect->getOne("SELECT SUM(sum) FROM reckoning WHERE status=6 AND active!=3 AND (date>=?s AND date<=?s) AND agency=?i", $start, $end, $agency);
			$itog["all"]+= $all;
			$itog["pay"]+= $pay;
			$itog["no_pay"]+= $no_pay;
			$itog["cancel"]+= $cancel;
			$itog["all_sum"]+= $all_sum;
			$itog["pay_sum"]+= $pay_sum;
			$itog["no_pay_sum"]+= $no_pay_sum;
			$itog["cancel_sum"]+= $cancel_sum;
		?>
		<tr>
			<td width="30%"><?php echo $connect->getOne("SELECT name FROM agency WHERE id=?i", $agency); ?></td>
			<td><?php echo $all; ?></td>
			<td><?php echo $all_sum; ?></td>
			<td><?php echo $pay; ?></td>
			<td><?php echo $pay_sum; ?></td>
			<td><?php echo $no_pay; ?></td>
			<td><?php echo $no_pay_sum; ?></td>
			<td><?php echo $cancel; ?></td>
			<td><?php echo $cancel_sum; ?></td>
		</tr>
		<?php
		}
		?>
			<tr>
				<th>Итог</th>
				<th><?php echo $itog["all"]; ?></th>
				<th><?php echo $itog["all_sum"]; ?></th>
				<th><?php echo $itog["pay"]; ?></th>
				<th><?php echo $itog["pay_sum"]; ?></th>
				<th><?php echo $itog["no_pay"]; ?></th>
				<th><?php echo $itog["no_pay_sum"]; ?></th>
				<th><?php echo $itog["cancel"]; ?></th>
				<th><?php echo $itog["cancel_sum"]; ?></th>
			</tr>
		<?php
	}
?>
	</table>
<?php
}

function menu_mass_action(){
	global $id_rights;
	if($id_rights <= 3)
		return FALSE;
?>
	<span onclick="show_mass_action('cancel')">Аннуляция</span>
	<span onclick="show_mass_action('permit_san')">Разрешить оплату в сан</span>
	<span onclick="show_mass_action('permit_san_prepay')">Разрешить предоплату в сан</span>
	<span onclick="show_mass_action('return_san')">Не оплачивать в санаторий</span>
	<span onclick="show_mass_action('return_cancel')">Не аннулировать</span>
	<span onclick="show_mass_action('block')">Заблокировать</span>
	<span onclick="calc_payment_to_san()">Подсчет суммы оплаты в сан</span>
	<span onclick="open_schet_san()">Открыть счета санатория</span>
	<span onclick="show_mass_action()">Реестр заявок</span>
<?php
}

function show_order_call_back_report($connect){
?>
<div class="form-horizontal panel panel-default form-search-call-back">
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-4 control-label control-label-left">Дата заказа</label>
			<label class="col-sm-4 control-label control-label-left">Менеджер</label>
			<label class="col-sm-4 control-label control-label-left">Статус</label>
		</div>
		<div class="form-group">
			<div class="col-sm-4">
				<input type="text" class="form-control datepicker" id="date-1" value="<?php echo date('Y-m-d'); ?>" />
			</div>
			<div class="col-sm-4">
				<?php echo get_managers($connect, "filter"); ?>
			</div>
			<div class="col-sm-4">
				<select class="form-control status-call-back">
					<option value="">Не выбран</option>
					<option value="1">Звонок в работе</option>
					<option value="2">Обработанный звонок</option>
					<option value="3">Звонок в архиве</option>
				</select>
			</div>
		</div>
		<div class="form-group form-group-margin">
			<div class="col-sm-4">
				<input type="text" class="form-control datepicker" id="date-2" />
			</div>
		</div>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-success btn-sm btn-search" data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Подождите, идет поиск..." onclick="filter_order_call_back_report()"><i class="fa fa-search"></i> Применить</button>
	</div>
</div>
<div class="result"></div>
<?php
}

function filter_order_call_back_report($connect){
	$date1 = $_POST["date1"];
	$date2 = $_POST["date2"];
	$manager = $_POST["manager"];
	$status = $_POST["status"];
	$zapros_for_mysql = "";
	if($date1 != ""){
		if($date2)
			$zapros_for_mysql.= " (DATE(time) >= '$date1' AND DATE(time) <= '$date2') ";
		else
			$zapros_for_mysql.= " DATE(time) = '$date1' ";
	}
	if($manager != ""){
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		$zapros_for_mysql.= " id_user = ".$manager;
	}
	if($status != ""){
		if($zapros_for_mysql) $zapros_for_mysql .= " AND ";
		$zapros_for_mysql.= " active = ".$status;
	}
	if($zapros_for_mysql)
		$zapros_for_mysql = " WHERE ".$zapros_for_mysql;

	$data = $connect->getAll("SELECT id, turist, website, id_user, active, DATE_FORMAT(time, '%d.%m.%Y') as date, source FROM order_call_back ".$zapros_for_mysql);
	if($data){
?>
	<table class="table table-bordered table-condensed">
<?php
		foreach($data as $row){
			$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
			$source = $row["source"];
			$class = "";
			if($row["active"] == 3)
				$class = "danger";
			elseif($row["active"] == 2)
				$class = "success";
			$icon.= select_source_icon($source);
		?>
			<tr class="<?php echo $class; ?>">
				<td width="30%"><?php echo $row["turist"]; ?></td>
				<td width="30%"><?php echo $row["website"]; ?></td>
				<td width="20%"><?php echo $row["date"]; ?></td>
				<td width="20%"><?php echo $manager; ?></td>
			</tr>
		<?php
		}
?>
	</table>
<?php
	}else{
?>
	<div class="alert alert-info"><i class="fa fa-exclamation-triangle"></i> Заказов звонка не найдено</div>
<?php
	}
}

?>
